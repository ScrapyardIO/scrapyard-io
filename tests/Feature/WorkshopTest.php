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
            ['i2cdetect', 'Scan an I2C bus for devices (like i2cdetect -q -y)'],
            ['list', 'List commands'],
            ['sketch', 'Run a registered Sketch in the foreground'],
            ['wrench', 'Interact with your application'],
            ['cache:clear', 'Flush the application cache'],
            ['cache:forget', 'Remove an item from the cache'],
            ['config:cache', 'Create a cache file for faster configuration loading'],
            ['config:clear', 'Remove the configuration cache file'],
            ['config:main-display', 'Set the main display in config/displays.php'],
            ['config:show', 'Display all of the values for a given configuration file or key'],
            ['install:actuators', 'Install ScrapyardIO Waveforms actuator scaffolding'],
            ['install:fonts', 'Install ScrapyardIO Autopen font scaffolding'],
            ['install:gfx', 'Install ScrapyardIO graphics packages (Tubes and/or desktop GFX backends)'],
            ['install:gpio', 'Install the ScrapyardIO GPIO Framework scaffolding'],
            ['install:sensors', 'Install ScrapyardIO Waveforms sensor scaffolding'],
            ['key:generate', 'Set the application key'],
            ['make:actuator', 'Create a new actuator class'],
            ['make:class', 'Create a new class'],
            ['make:command', 'Create a new Workshop command'],
            ['make:config', 'Create a new configuration file'],
            ['make:enum', 'Create a new enum'],
            ['make:event', 'Create a new event class'],
            ['make:exception', 'Create a new custom exception class'],
            ['make:font', 'Create a new GFX font class'],
            ['make:framebuffer', 'Create a new framebuffer strategy class'],
            ['make:interface', 'Create a new interface'],
            ['make:job', 'Create a new job class'],
            ['make:listener', 'Create a new event listener class'],
            ['make:node', 'Create a new UX node class'],
            ['make:observer', 'Create a new observer class'],
            ['make:provider', 'Create a new service provider class'],
            ['make:sensor', 'Create a new sensor class'],
            ['make:sketch', 'Create a new Sketch class'],
            ['make:trait', 'Create a new trait'],
            ['make:visualization', 'Create a new LED visualization class'],
            ['package:discover', 'Rebuild the cached package manifest'],
            ['sketch:list', 'List registered sketches'],
            ['vendor:publish', 'Publish any publishable assets from vendor packages'],
        ]);
});

it('discovers the I2C controller presentation sketches', function (): void {
    $process = runWorkshop(['sketch:list']);

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getErrorOutput())->toBeEmpty()
        ->and($process->getOutput())
        ->toContain('snes-controller')
        ->toContain('Live SDL3 visualization of an SNES Classic controller over I2C.')
        ->toContain('seesaw-mini-gamepad')
        ->toContain('Live SDL3 button and joystick display for the Adafruit Seesaw Mini Gamepad.')
        ->toContain('seesaw-neo-slider')
        ->toContain('Live SDL3 position meter and four-pixel gradient for the Adafruit Seesaw NeoSlider.');
});

it('resolves SDL input actuators through Wrench without queue-owner recursion', function (): void {
    $expression = <<<'PHP'
foreach (['sdl3-keyboard', 'sdl3-mouse', 'sdl3-touch', 'sdl3-game-controller'] as $alias) {
    print $alias.' => '.Fabricate\NutsAndBolts\MagicAliases\Actuator::type($alias, $alias)::class.PHP_EOL;
}
PHP;
    $process = runWorkshop(['wrench', "--execute={$expression}"]);

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getErrorOutput())->toBeEmpty()
        ->and($process->getOutput())
        ->toContain('sdl3-keyboard => ScrapyardIO\Waveforms\Input\ButtonPad')
        ->toContain('sdl3-mouse => ScrapyardIO\Waveforms\Input\Pointer')
        ->toContain('sdl3-touch => ScrapyardIO\Waveforms\Input\Touch')
        ->toContain('sdl3-game-controller => ScrapyardIO\Waveforms\Input\GameController');
});
