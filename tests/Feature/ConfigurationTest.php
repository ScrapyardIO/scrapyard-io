<?php

beforeEach(function (): void {
    $this->app = bootstrapApplication();
});

it('loads merged framework and application configuration', function (): void {
    expect(config('machine.name'))->toBe('ScrapyardIO')
        ->and(config('logging.default'))->toBe('stack')
        ->and(config('logging.channels.single.path'))->toEndWith('storage/logs/scrapyard-io.log')
        ->and(config('filesystems.default'))->toBe('local')
        ->and(config('filesystems.disks.local.driver'))->toBe('local');
});

it('loads application config files from the config directory', function (): void {
    $configFiles = glob(applicationBasePath().'/config/*.php');

    expect($configFiles)->not->toBeEmpty()
        ->and(basename((string) head($configFiles)))->toEndWith('.php');
});
