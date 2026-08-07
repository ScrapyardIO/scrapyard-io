<?php

it('discovers wrench through package discovery', function (): void {
    $packages = require applicationBasePath().'/bootstrap/cache/packages.php';

    expect($packages)->toHaveKey('scrapyard-io/wrench')
        ->and($packages['scrapyard-io/wrench']['providers'])
        ->toContain(ScrapyardIO\Wrench\WrenchServiceProvider::class);
});

it('rebuilds the package manifest via workshop package:discover', function (): void {
    $process = runWorkshop(['package:discover', '--ansi']);

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getErrorOutput())->toBeEmpty()
        ->and($process->getOutput())
        ->toContain('Discovering packages')
        ->toContain('scrapyard-io/wrench');

    $packages = require applicationBasePath().'/bootstrap/cache/packages.php';

    expect($packages)->toHaveKey('scrapyard-io/wrench');
});
