<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:city:import',
    description: 'Import the INSEE-derived city dataset (data.gouv.fr) into the city table',
)]
final class CityImportCommand extends Command
{
    private const int MIN_POPULATION = 1_000;
    private const int SMALL_MEDIUM_THRESHOLD = 20_000;
    private const int MEDIUM_LARGE_THRESHOLD = 100_000;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly Connection $connection,
        #[Autowire('%app.city_import_csv_url%')]
        private readonly string $csvUrl,
        #[Autowire('%app.city_import_cache_path%')]
        private readonly string $cachePath,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Re-download the CSV even if a cached copy already exists',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('force') || !is_file($this->cachePath)) {
            $this->downloadCsv($io);
        } else {
            $io->note(sprintf('Using cached CSV at %s (use --force to re-download)', $this->cachePath));
        }

        $cities = $this->parseCsv($io);
        $this->replaceCities($cities);

        $io->success(sprintf('Imported %d cities.', count($cities)));

        return Command::SUCCESS;
    }

    private function downloadCsv(SymfonyStyle $io): void
    {
        $io->note(sprintf('Downloading %s', $this->csvUrl));

        $response = $this->httpClient->request('GET', $this->csvUrl);
        $content = $response->getContent();

        @mkdir(dirname($this->cachePath), 0o775, recursive: true);
        file_put_contents($this->cachePath, $content);
    }

    /**
     * @return list<array{insee_code: string, name: string, population: int, latitude: float, longitude: float, tier: string}>
     */
    private function parseCsv(SymfonyStyle $io): array
    {
        $reader = Reader::createFromPath($this->cachePath);
        $reader->setHeaderOffset(0);

        $cities = [];
        foreach ($reader->getRecords() as $record) {
            $population = (int) $record['population'];

            if ($population <= self::MIN_POPULATION) {
                continue;
            }

            $cities[] = [
                'insee_code' => $record['code_insee'],
                'name' => $record['nom_standard'],
                'population' => $population,
                'latitude' => (float) $record['latitude_centre'],
                'longitude' => (float) $record['longitude_centre'],
                'tier' => $this->tierFor($population),
            ];
        }

        $io->note(sprintf('%d cities eligible (population > %d).', count($cities), self::MIN_POPULATION));

        return $cities;
    }

    private function tierFor(int $population): string
    {
        if ($population <= self::SMALL_MEDIUM_THRESHOLD) {
            return 'small';
        }

        if ($population <= self::MEDIUM_LARGE_THRESHOLD) {
            return 'medium';
        }

        return 'large';
    }

    /**
     * @param list<array{insee_code: string, name: string, population: int, latitude: float, longitude: float, tier: string}> $cities
     */
    private function replaceCities(array $cities): void
    {
        $this->connection->transactional(function (Connection $connection) use ($cities): void {
            $connection->executeStatement('DELETE FROM city');

            foreach ($cities as $city) {
                $connection->insert('city', $city);
            }
        });
    }
}
