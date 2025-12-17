<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;

try {
    return RectorConfig::configure()
        ->withPaths([
            __DIR__.'/config',
            __DIR__.'/routes',
            __DIR__.'/src',
            __DIR__.'/tests',
        ])
        ->withPhpSets()
        ->withTypeCoverageLevel(0)
        ->withDeadCodeLevel(0)
        ->withCodeQualityLevel(0);
} catch (InvalidConfigurationException $e) {

}
