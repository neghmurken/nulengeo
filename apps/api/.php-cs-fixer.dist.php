<?php

use PedroTroller\CS\Fixer\Fixers;
use PedroTroller\CS\Fixer\RuleSetFactory;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return new PhpCsFixer\Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setFinder(PhpCsFixer\Finder::create()
        ->in(__DIR__.'/migrations')
        ->in(__DIR__.'/src')
        ->in(__DIR__.'/tests')
        ->append([
            __FILE__,
            __DIR__.'/bin/console',
            __DIR__.'/config/bundles.php',
            __DIR__.'/public/index.php',
        ])
    )
    ->setRules(RuleSetFactory::create()
        ->php(8.4)
        ->symfony()
        ->enable('yoda_style', [
            'equal' => true,
            'identical' => true,
            'less_and_greater' => false,
        ])
        ->enable('global_namespace_import', [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => false,
        ])
        ->enable('nullable_type_declaration_for_default_null_value')
        ->enable('phpdoc_align', [
            'align' => 'left',
        ])
        ->enable('phpdoc_to_comment', [
            'allow_before_return_statement' => true,
            'ignored_tags' => ['var'],
        ])
        ->getRules()
    )
    ->registerCustomFixers(new Fixers())
    ->setUsingCache(true);
