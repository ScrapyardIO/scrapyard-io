<?php

namespace App\Sketches;

use Fabricate\Contracts\Core\VisualPresentation;
use Fabricate\Contracts\Sketches\SketchLoopResult;
use Fabricate\NutsAndBolts\MagicAliases\Visual;
use Fabricate\UX\Color;
use Fabricate\UX\Input\InputRouter;
use Fabricate\UX\Node;
use Fabricate\UX\Stage;

/**
 * A sketch whose display is a node tree rather than a sequence of draw calls.
 *
 * Two responsibilities, and deliberately no more. It owns the presentation and
 * the {@see Stage} — opening, binding, presenting and closing them — and it owns
 * the frame loop, so a subclass writes {@see build()} once and then only mutates
 * node state in {@see sample()}.
 *
 * The loop never calls a draw method. {@see Stage::render()} repaints the damaged
 * subtrees and returns false when nothing changed, so a screen that is standing
 * still costs one clock read and a sleep. That is the whole reason to be here: on
 * an SSD1306 a full transmit is 20-30 ms, which caps an unconditionally redrawn
 * sketch at roughly 30 fps no matter how little of the screen actually moved.
 *
 * Subclasses override {@see presentation()} to say which display they want, and
 * may override {@see bootWithoutDisplay()} to degrade to the console rather than
 * idling.
 */
abstract class UXSketch extends Sketch
{
    protected ?VisualPresentation $screen = null;

    protected ?Stage $stage = null;

    protected ?InputRouter $router = null;

    protected int $targetFps = 60;

    /**
     * Frames actually painted, which is not the number of loop ticks: an idle
     * tick paints nothing and is not counted.
     */
    protected int $frame = 0;

    protected int $ticks = 0;

    protected ?float $last_tick_at = null;

    /**
     * The tree to display. Called once, after the presentation is open, so the
     * surface's size and pixel format are already known.
     */
    abstract protected function build(): Node;

    /**
     * Which display to open. The main display by default, which is whatever
     * `config('displays.main')` names — console included, in which case there is
     * no presentation and {@see bootWithoutDisplay()} runs instead.
     */
    protected function presentation(): ?VisualPresentation
    {
        return Visual::main();
    }

    /**
     * What the stage erases with where no opaque node covers the damage. Null
     * accepts ghosting in exchange for never clearing.
     */
    protected function background(): ?Color
    {
        return Color::black();
    }

    /**
     * Advance the tree by $dt seconds. Setters invalidate, so a subclass changes
     * state here and never asks for a repaint.
     */
    protected function sample(float $dt): void
    {
        //
    }

    /**
     * After the tree is built and bound, for logging and one-off setup.
     */
    protected function booted(): void
    {
        //
    }

    /**
     * No main display: a console run, not a failure. The loop idles unless this
     * is overridden to stop.
     */
    protected function bootWithoutDisplay(): void
    {
        $this->info('No display is configured for this sketch — nothing to draw.');
    }

    public function boot(): void
    {
        $this->screen = $this->presentation();

        if (is_null($this->screen)) {
            $this->bootWithoutDisplay();

            return;
        }

        $this->stage = new Stage($this->screen, $this->background());
        $this->stage->setRoot($this->build());

        // Measure once up front so the first sample() sees real sizes rather than
        // a tree of zero-extent nodes waiting for its first paint.
        $this->stage->settleLayout();

        $this->last_tick_at = hrtime(true) / 1e9;

        $this->booted();
    }

    public function loop(): SketchLoopResult
    {
        if (is_null($this->screen) || is_null($this->stage)) {
            usleep(100_000);

            return SketchLoopResult::CONTINUE;
        }

        if ($this->screen->shouldClose()) {
            return SketchLoopResult::STOP;
        }

        $started = hrtime(true);

        $this->ticks++;
        $this->sample($this->elapsedSince($started));

        // Input and window events are pumped during sampling on some targets, so
        // a close arriving mid-tick is checked again before painting into a
        // surface that is on its way out.
        if ($this->screen->shouldClose()) {
            return SketchLoopResult::STOP;
        }

        if ($this->stage->render()) {
            $this->frame++;
        }

        $this->paceFrame($started);

        return SketchLoopResult::CONTINUE;
    }

    public function shutdown(): void
    {
        try {
            $this->teardown();
        } finally {
            $this->stage = null;
            $this->router = null;

            if (! is_null($this->screen)) {
                if (! $this->screen->shouldClose()) {
                    $this->screen->clear(0)->present();
                }

                $this->screen->close();
                $this->screen = null;
            }
        }

        $this->info("Stopped after {$this->frame} painted frames of {$this->ticks} ticks.");
    }

    /**
     * Release anything the sketch itself opened — a circuit, a file — before the
     * display is closed. Runs even when the loop threw.
     */
    protected function teardown(): void
    {
        //
    }

    /**
     * Built on first use because most sketches never take input, and a focus ring
     * walks the tree to find focusable nodes.
     *
     * Not called input(), which {@see \Fabricate\Console\Concerns\InteractsWithIO}
     * already owns for console arguments.
     */
    protected function router(): InputRouter
    {
        return $this->router ??= new InputRouter($this->stage);
    }

    /**
     * Seconds since the previous tick, clamped so that a stall — a slow I2C
     * transmit, a debugger pause — cannot teleport an animation across the
     * screen.
     */
    protected function elapsedSince(int $nowNs): float
    {
        $now = $nowNs / 1e9;
        $elapsed = is_null($this->last_tick_at)
            ? (1.0 / $this->targetFps)
            : min(0.05, max(0.0, $now - $this->last_tick_at));

        $this->last_tick_at = $now;

        return $elapsed;
    }

    /**
     * Sleep only the leftover budget when a tick finishes early.
     */
    protected function paceFrame(int $loopStartNs): void
    {
        $budgetNs = (int) (1_000_000_000 / $this->targetFps);
        $remainingNs = $budgetNs - (hrtime(true) - $loopStartNs);

        if ($remainingNs > 1_000) {
            usleep((int) ($remainingNs / 1_000));
        }
    }
}
