<?php

use Fabricate\Chassis\Chassis;
use Fabricate\Core\Bootstrap\BootProviders;
use Fabricate\Core\Bootstrap\LoadConfiguration;
use Fabricate\Core\Bootstrap\RegisterMagicAliases;
use Fabricate\Core\Bootstrap\RegisterProviders;
use Fabricate\Core\Machine;
use Symfony\Component\Process\Process;

pest()->group('integration')->in('Feature');

function applicationBasePath(): string
{
    return dirname(__DIR__);
}

function runWorkshop(array $arguments = []): Process
{
    $process = new Process(
        [PHP_BINARY, 'workshop', ...$arguments],
        applicationBasePath(),
        [
            'APP_ENV' => 'testing',
            'NO_COLOR' => '1',
            'TERM' => 'dumb',
        ],
        null,
        10,
    );

    $process->run();

    return $process;
}

function bootstrapApplication(): Machine
{
    Chassis::setInstance(null);

    /** @var Machine $app */
    $app = require applicationBasePath().'/bootstrap/app.php';

    // Skip LoadEnvironmentVariables: no .env in the skeleton yet; phpunit.xml sets APP_ENV.
    // Skip HandleExceptions so PHPUnit retains control of error handlers.
    $app->bootstrapWith([
        LoadConfiguration::class,
        RegisterMagicAliases::class,
        RegisterProviders::class,
        BootProviders::class,
    ]);

    return $app;
}
