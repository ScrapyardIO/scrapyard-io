<?php

use Fabricate\Core\Machine;
use Fabricate\Events\Dispatcher;
use Fabricate\Filesystem\Filesystem;
use Fabricate\Log\LogManager;
use Psr\Log\LoggerInterface;

beforeEach(function (): void {
    $this->app = bootstrapApplication();
});

it('bootstraps the Machine through the application bootstrappers', function (): void {
    expect($this->app)->toBeInstanceOf(Machine::class)
        ->and($this->app->hasBeenBootstrapped())->toBeTrue()
        ->and($this->app->bound('config'))->toBeTrue();
});

it('binds log and filesystem services after bootstrap', function (): void {
    expect(app('log'))->toBeInstanceOf(LogManager::class)
        ->and(app('files'))->toBeInstanceOf(Filesystem::class)
        ->and(app(LoggerInterface::class))->toBeInstanceOf(LogManager::class);
});

it('binds the event dispatcher as events', function (): void {
    expect(app('events'))->toBeInstanceOf(Dispatcher::class);
});

it('loads default framework providers from the cached services manifest', function (): void {
    $services = require applicationBasePath().'/bootstrap/cache/services.php';

    expect($services['providers'])->toContain(
        'Fabricate\\Core\\Providers\\ConsoleSupportServiceProvider',
        'Fabricate\\Core\\Providers\\FilesystemServiceProvider',
        'Fabricate\\Core\\Providers\\LogServiceProvider',
        'Fabricate\\Core\\Providers\\CoreServiceProvider',
        'ScrapyardIO\\Wrench\\WrenchServiceProvider',
        'App\\Providers\\MachineServiceProvider',
    );
});
