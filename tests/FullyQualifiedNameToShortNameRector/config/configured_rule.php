<?php

use Rector\Config\RectorConfig;
use MaximeRainville\SilverstripeCmsSixRector\FullyQualifiedNameToShortNameRector;
use SilverStripe\Control\HTTPRequest;

return RectorConfig::configure()
    ->withRules([
        FullyQualifiedNameToShortNameRector::class
    ]);
