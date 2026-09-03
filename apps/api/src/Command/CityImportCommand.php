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
    private const int MIN_POPULATION = 5_000;
    private const int SMALL_MEDIUM_THRESHOLD = 20_000;
    private const int MEDIUM_LARGE_THRESHOLD = 80_000;
    private const int TOP_TIER_SIZE = 30;

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
     * @return list<array{insee_code: string, name: string, population: int, latitude: float, longitude: float, area_km2: float, tier: string}>
     */
    private function parseCsv(SymfonyStyle $io): array
    {
        $reader = Reader::createFromPath($this->cachePath);
        $reader->setHeaderOffset(0);

        $eligible = [];
        foreach ($reader->getRecords() as $record) {
            $inseeCode = $record['code_insee'];
            $population = (int) $record['population'];

            if ($population <= self::MIN_POPULATION || !$this->isMetropolitan($inseeCode)) {
                continue;
            }

            $eligible[] = [
                'insee_code' => $inseeCode,
                'name' => $record['nom_standard'],
                'population' => $population,
                'latitude' => (float) $record['latitude_centre'],
                'longitude' => (float) $record['longitude_centre'],
                'area_km2' => (float) $record['superficie_km2'],
            ];
        }

        // The top tier is a fixed count of the most populous cities, not a population band,
        // so ranking must happen before the remaining rows get banded into the other tiers.
        usort($eligible, static fn (array $a, array $b): int => $b['population'] <=> $a['population']);

        $cities = array_map(
            fn (array $city, int $rank): array => [
                ...$city,
                'tier' => $rank < self::TOP_TIER_SIZE ? 'huge' : $this->tierFor($city['population']),
            ],
            $eligible,
            array_keys($eligible),
        );

        $io->note(sprintf('%d cities eligible (population > %d, metropolitan France only).', count($cities), self::MIN_POPULATION));

        return $cities;
    }

    private function isMetropolitan(string $inseeCode): bool
    {
        // Overseas departments/collectivities (DROM-COM) use a 3-digit department prefix
        // starting with 97 or 98 (e.g. 97411 for Saint-Denis, La Réunion); metropolitan
        // departments, including Corsica's 2A/2B, never do.
        $departmentPrefix = substr($inseeCode, 0, 2);

        return '97' !== $departmentPrefix && '98' !== $departmentPrefix;
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
