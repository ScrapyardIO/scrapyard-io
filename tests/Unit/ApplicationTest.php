<?php

use Fabricate\Core\Machine;

it('creates a Machine from bootstrap/app.php', function (): void {
    Fabricate\Chassis\Chassis::setInstance(null);

    $app = require applicationBasePath().'/bootstrap/app.php';

    expect($app)->toBeInstanceOf(Machine::class)
        ->and($app->version())->toBe('0.7.0')
        ->and($app->basePath())->toBe(applicationBasePath());
});

it('registers the application MachineServiceProvider from bootstrap/providers.php', function (): void {
    $providers = require applicationBasePath().'/bootstrap/providers.php';

    expect($providers)->toContain(App\Providers\MachineServiceProvider::class);
});
