<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/config', isDev: false)
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // Root-namespace test fixtures, loaded via require_once instead of PSR-4 autoloading.
    ->ignoreUnknownClasses(['FakeController', 'Fake8Controller'])
    // Optional integration with yiisoft/yii-debug: used only if the package is installed.
    ->ignoreErrorsOnPackages(['yiisoft/yii-debug'], [ErrorType::DEV_DEPENDENCY_IN_PROD]);
