<?php

use Rector\Config\RectorConfig;
use MaximeRainville\SilverstripeCmsSixRector\SessionConstructorRector;
use MaximeRainville\SilverstripeCmsSixRector\ReplaceSessionConstructorVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;

return RectorConfig::configure()
    // ->withRules([
    //     SessionConstructorRector::class,
    // ])
    ->registerService(ReplaceSessionConstructorVisitor::class, null, ScopeResolverNodeVisitorInterface::class);;
