<?php

use App\Sketches\BouncingShapes;
use App\Sketches\SeesawNeoSlider;
use App\Sketches\Welcome;
use Fabricate\Console\OutputStyle;
use Fabricate\Contracts\Core\VisualPresentation;
use Fabricate\Contracts\Sketches\SketchLoopResult;
use Fabricate\NutsAndBolts\Geometry\Rect;
use Fabricate\UX\Color;
use ScrapyardIO\UX\Tests\Support\StageHarness;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * The three rewritten sketches, driven headless.
 *
 * They normally need an SSD1306 on an I2C bus or an SDL window, so what is
 * checked here is the part that used to be untestable: that the tree composes,
 * lays out against a real surface and puts ink down. Each double replaces only
 * the display, and the sketch's own boot/loop/shutdown runs unchanged.
 */
final class HeadlessWelcome extends Welcome
{
    public function __construct(private readonly VisualPresentation $surface) {}

    protected function presentation(): ?VisualPresentation
    {
        return $this->surface;
    }
}

final class HeadlessBouncingShapes extends BouncingShapes
{
    public function __construct(private readonly VisualPresentation $surface) {}

    protected function presentation(): ?VisualPresentation
    {
        return $this->surface;
    }

    /**
     * Where a named shape's node actually sits, so a test can watch one shape
     * rather than the union of all the ink on the panel.
     */
    public function boxOf(string $name): Rect
    {
        return $this->shapes[$name]['node']->globalBounds();
    }
}

/**
 * The NeoSlider needs an I2C circuit to sample, so this double keeps the tree
 * and skips the hardware.
 */
final class HeadlessNeoSlider extends SeesawNeoSlider
{
    public function __construct(private readonly VisualPresentation $surface) {}

    protected function presentation(): ?VisualPresentation
    {
        return $this->surface;
    }

    protected function booted(): void {}

    protected function sample(float $dt): void {}

    protected function teardown(): void {}
}

function headless(object $sketch): object
{
    $sketch->configureIO(new ArrayInput([]), new OutputStyle(new ArrayInput([]), new BufferedOutput()));

    return $sketch;
}

it('paints the welcome splash without a single coordinate', function (): void {
    $harness = new StageHarness(128, 64);
    $sketch = headless(new HeadlessWelcome($harness->stage->presentation()));

    $sketch->boot();

    expect($sketch->loop())->toBe(SketchLoopResult::CONTINUE)
        ->and($harness->countOf(Color::white()))->toBeGreaterThan(0);

    $ink = $harness->boundsOf(Color::white());

    // Centred by Align, and auto-fitted down to something that fits 128px.
    expect(abs(($ink->x + intdiv($ink->width, 2)) - 64))->toBeLessThanOrEqual(4)
        ->and($ink->width)->toBeLessThanOrEqual(128);

    $sketch->shutdown();
});

it('bounces shapes on a paged monochrome panel and keeps them inside it', function (): void {
    $harness = StageHarness::mono(128, 64);
    $sketch = headless(new HeadlessBouncingShapes($harness->stage->presentation()));

    $sketch->boot();
    $sketch->loop();

    expect($harness->boundsOf(Color::white()))->not->toBeNull();

    $started = $sketch->boxOf('circle');
    $moved = false;

    for ($tick = 0; $tick < 24; $tick++) {
        expect($sketch->loop())->toBe(SketchLoopResult::CONTINUE);

        $now = $sketch->boxOf('circle');
        $moved = $moved || ($now->x !== $started->x) || ($now->y !== $started->y);

        // Bouncing, not drifting off the edge — asserted every tick, since a
        // shape that escapes and comes back would slip past an end-state check.
        expect($now->x)->toBeGreaterThanOrEqual(0)
            ->and($now->y)->toBeGreaterThanOrEqual(0)
            ->and($now->right())->toBeLessThan(128)
            ->and($now->bottom())->toBeLessThan(64);
    }

    expect($moved)->toBeTrue('The shape never moved, so nothing here was testing motion.');

    $sketch->shutdown();
});

/**
 * The claim the whole rewrite rests on: one sketch, one tree, two targets that
 * share nothing — 1-bit paged 128x64 against 32-bit row-major 1024x768 — and no
 * branch anywhere in the sketch to tell them apart.
 */
it('runs the same welcome tree on a 1-bit panel and an RGBA window', function (): void {
    $panel = StageHarness::mono(128, 64);
    $window = new StageHarness(1024, 768);

    foreach ([$panel, $window] as $harness) {
        $sketch = headless(new HeadlessWelcome($harness->stage->presentation()));

        $sketch->boot();

        expect($sketch->loop())->toBe(SketchLoopResult::CONTINUE);

        $ink = $harness->boundsOf(Color::white());
        $centre = $ink->x + intdiv($ink->width, 2);

        expect($ink)->not->toBeNull()
            ->and(abs($centre - intdiv($harness->width, 2)))->toBeLessThanOrEqual(4)
            ->and($ink->right())->toBeLessThan($harness->width)
            ->and($ink->bottom())->toBeLessThan($harness->height);

        $sketch->shutdown();
    }
});

it('lays the neoslider dashboard out on a windowed surface', function (): void {
    $harness = new StageHarness(1024, 768);
    $sketch = headless(new HeadlessNeoSlider($harness->stage->presentation()));

    $sketch->boot();

    expect($sketch->loop())->toBe(SketchLoopResult::CONTINUE);

    $accent = $harness->boundsOf(Color::fromHex('#63E6C2'));

    // The accent only appears on the rail's fill and the normalised readout, so
    // finding it means the column, the card and the readout row all resolved.
    expect($accent)->not->toBeNull()
        ->and($accent->right())->toBeLessThan(1024);

    $sketch->shutdown();
});
