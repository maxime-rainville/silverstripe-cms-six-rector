<?php

use Rector\Config\RectorConfig;
use MaximeRainville\SilverstripeCmsSixRector\HTTPRequestConstructorRector;

return RectorConfig::configure()
    ->withRules([
        HTTPRequestConstructorRector::class,
    ]);
