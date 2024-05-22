<?php

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use SilverStripe\Control\HTTPRequest as SilverstripeRequest;
use Symfony\Component\HttpFoundation\Request;
use Utils\Rector\Rector\HTTPRequestConstructorRector;
use Utils\Rector\Rector\MyFirstRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app/src',
        __DIR__ . '/public/index.php',
        __DIR__ . '/vendor/silverstripe/framework',
        // __DIR__ . '/vendor/dnadesign',
        // __DIR__ . '/vendor/bringyourownideas',
        // __DIR__ . '/vendor/colymba',
        // __DIR__ . '/vendor/silverstripe-themes',
        // __DIR__ . '/vendor/sminnee',
        // __DIR__ . '/vendor/symbiote',
        // __DIR__ . '/vendor/tractorcow',

    ])
    // register single rule
    ->withRules([
        // TypedPropertyFromStrictConstructorRector::class,
        HTTPRequestConstructorRector::class,
    ])
    // ->withConfiguredRule(RenameClassRector::class, [
    //     SilverstripeRequest::class => Request::class
    // ])
    // here we can define, what prepared sets of rules will be applied
    ->withPreparedSets(
        // deadCode: true,
        // codeQuality: true
    );
    // ->withImportNames();
