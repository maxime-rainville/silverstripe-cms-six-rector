<?php

use Rector\Config\RectorConfig;
use Utils\Rector\Rector\HTTPRequestConstructorRector;

return RectorConfig::configure()
    ->withRules([
        HTTPRequestConstructorRector::class,
    ]);
