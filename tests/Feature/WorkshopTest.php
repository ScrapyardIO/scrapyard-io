<?php

it('boots the application and displays the available commands', function (): void {
    $process = runWorkshop();

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getErrorOutput())->toBeEmpty()
        ->and($process->getOutput())
        ->toMatch('/ScrapyardIO Framework 0\.7\.0/')
        ->toContain('Usage:')
        ->toContain('Available commands:')
        ->toMatch('/completion\s+Dump the shell completion script/')
        ->toMatch('/help\s+Display help for a command/')
        ->toMatch('/list\s+List commands/')
        ->toContain('package:discover')
        ->toContain('config:show')
        ->toContain('cache:clear')
        ->toContain('schedule:list')
        ->toContain('wrench');
});

it('lists the baseline 0.7 workshop commands', function (): void {
    $process = runWorkshop(['list', '--raw']);
    $names = collect(explode(PHP_EOL, trim($process->getOutput())))
        ->filter()
        ->map(fn (string $line): string => preg_split('/\s{2,}/', trim($line), 2)[0])
        ->values()
        ->all();

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getErrorOutput())->toBeEmpty()
        ->and($names)
        ->toContain('completion')
        ->toContain('help')
        ->toContain('list')
        ->toContain('wrench')
        ->toContain('package:discover')
        ->toContain('config:show')
        ->toContain('config:cache')
        ->toContain('about')
        ->toContain('config:clear')
        ->toContain('optimize')
        ->toContain('optimize:clear')
        ->toContain('env')
        ->toContain('key:generate')
        ->toContain('cache:clear')
        ->toContain('cache:forget')
        ->toContain('schedule:list')
        ->toContain('schedule:run')
        ->toContain('schedule:work')
        ->toContain('schedule:test')
        ->toContain('schedule:clear-cache')
        ->toContain('schedule:interrupt')
        ->toContain('schedule:pause')
        ->toContain('schedule:resume')
        ->toContain('make:class')
        ->toContain('make:config')
        ->toContain('make:console')
        ->toContain('make:enum')
        ->toContain('make:event')
        ->toContain('make:exception')
        ->toContain('make:middleware')
        ->toContain('make:node')
        ->toContain('make:observer')
        ->toContain('make:sketch')
        ->toContain('make:trait')
        ->toContain('vendor:publish');
});

it('executes PHP through wrench after bootstrap', function (): void {
    $process = runWorkshop(['wrench', '--execute=echo config(\'machine.name\');']);

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getErrorOutput())->toBeEmpty()
        ->and(trim($process->getOutput()))->toBe('ScrapyardIO');
});

it('shows machine config and environment', function (): void {
    $show = runWorkshop(['config:show', 'machine.name']);
    $env = runWorkshop(['env']);

    expect($show->isSuccessful())->toBeTrue()
        ->and($show->getOutput())->toContain('ScrapyardIO')
        ->and($env->isSuccessful())->toBeTrue()
        ->and($env->getOutput())->toMatch('/environment/i');
});

it('reports wrench installed true via about extension', function (): void {
    $about = runWorkshop(['about', '--json', '--only=environment']);

    expect($about->isSuccessful())->toBeTrue();

    $json = json_decode($about->getOutput(), true);

    expect($json['environment']['wrench_installed'] ?? null)->toBeTrue();
});
