<?php

use Symfony\Component\Process\Process;

function runWorkshop(array $arguments = []): Process
{
    $process = new Process(
        [PHP_BINARY, 'workshop', ...$arguments],
        dirname(__DIR__, 2),
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

it('boots the application and displays the available commands', function (): void {
    $process = runWorkshop();

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getErrorOutput())->toBeEmpty()
        ->and($process->getOutput())
        ->toMatch('/ScrapyardIO Framework \d+\.\d+\.\d+/')
        ->toContain('Usage:')
        ->toContain('Available commands:')
            ->toMatch('/completion\s+Dump the shell completion script/')
            ->toMatch('/help\s+Display help for a command/')
            ->toMatch('/list\s+List commands/')
            ->toContain('package:discover');
});

it('lists the baseline application and package discovery commands', function (): void {
    $process = runWorkshop(['list', '--raw']);
    $commands = array_map(
        fn (string $line): array => preg_split('/\s{2,}/', trim($line), 2),
        array_values(array_filter(explode(PHP_EOL, trim($process->getOutput())))),
    );

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getErrorOutput())->toBeEmpty()
        ->and($commands)
        ->toBe([
            ['about', 'Display basic information about your application'],
            ['completion', 'Dump the shell completion script'],
            ['env', 'Display the current framework environment'],
            ['help', 'Display help for a command'],
            ['list', 'List commands'],
            ['sketch', 'Run a registered Sketch in the foreground'],
            ['wrench', 'Interact with your application'],
            ['cache:clear', 'Flush the application cache'],
            ['cache:forget', 'Remove an item from the cache'],
            ['config:cache', 'Create a cache file for faster configuration loading'],
            ['config:clear', 'Remove the configuration cache file'],
            ['config:show', 'Display all of the values for a given configuration file or key'],
            ['key:generate', 'Set the application key'],
            ['make:class', 'Create a new class'],
            ['make:command', 'Create a new Workshop command'],
            ['make:config', 'Create a new configuration file'],
            ['make:enum', 'Create a new enum'],
            ['make:event', 'Create a new event class'],
            ['make:exception', 'Create a new custom exception class'],
            ['make:framebuffer', 'Create a new framebuffer strategy class'],
            ['make:interface', 'Create a new interface'],
            ['make:job', 'Create a new job class'],
            ['make:listener', 'Create a new event listener class'],
            ['make:observer', 'Create a new observer class'],
            ['make:provider', 'Create a new service provider class'],
            ['make:sketch', 'Create a new Sketch class'],
            ['make:trait', 'Create a new trait'],
            ['package:discover', 'Rebuild the cached package manifest'],
            ['sketch:list', 'List registered sketches'],
            ['vendor:publish', 'Publish any publishable assets from vendor packages'],
        ]);
});
