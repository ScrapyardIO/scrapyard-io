<?php

namespace App\Sketches;

use Fabricate\UX\Color;
use Fabricate\UX\Layout\Align;
use Fabricate\UX\Node;
use ScrapyardIO\UX\Chrome\Panel;
use ScrapyardIO\UX\Support\Fonts;
use ScrapyardIO\UX\Text\Label;

class Welcome extends UXSketch
{
    /**
     * The sketch description.
     *
     * @var string
     */
    protected string $description = 'Centered ScrapyardIO splash on displays.main (console, windowed, or embedded).';

    protected string $label = 'ScrapyardIO';

    /**
     * Splash scale ceiling — classic 5x7 at size 3+ reads as a brick wall on
     * 240x320. The label picks the largest size up to this that fits the panel,
     * which is why the same tree is legible on a 128x64 OLED.
     */
    protected int $maxTextSize = 2;

    protected Panel $backdrop;

    protected Label $splash;

    /**
     * Registered font names available for cycling.
     *
     * @var array<int, string>
     */
    protected array $fontNames = [];

    protected int $fontIndex = 0;

    protected float $fontSwitchedAt = 0.0;

    protected float $fontCycleSeconds = 10.0;

    /**
     * Centre the splash on an opaque backdrop.
     *
     * There are no coordinates here at all. Align asks the label how big it wants
     * to be and places it in the middle, so nothing in this sketch has to know
     * the surface size — and the backdrop being opaque is what lets the stage
     * erase the previous splash by repainting from it when the font changes.
     */
    protected function build(): Node
    {
        $this->splash = Label::of($this->label, Color::white())->fitTextTo($this->maxTextSize);
        $this->backdrop = Panel::of(Color::black())->add(Align::centered($this->splash));

        return $this->backdrop;
    }

    protected function booted(): void
    {
        $this->fontNames = Fonts::drawableNames();
        $this->fontIndex = 0;
        $this->fontSwitchedAt = hrtime(true) / 1e9;
        $this->applyActiveFont();

        $width = $this->screen->width();
        $height = $this->screen->height();

        $this->info('Booting ScrapyardIO welcome…');
        $this->info("Display ready: {$width}x{$height}");
        $this->info('Cycling fonts: '.($this->fontNames === [] ? '(none)' : implode(', ', $this->fontNames)));
        $this->info('Press Ctrl+C to stop.');
    }

    /**
     * The wash is offered every frame and the panel decides what it costs.
     *
     * Consecutive frames often land on the same packed colour, and a 1-bit panel
     * packs the whole wash to the same unlit pixel, so the backdrop treats those
     * as no change at all. That is why this asks nothing about the target: a
     * still splash on an SSD1306 transmits nothing without the sketch knowing it
     * is talking to one.
     */
    protected function sample(float $dt): void
    {
        $now = hrtime(true) / 1e9;

        $this->maybeAdvanceFont($now);
        $this->backdrop->setColor($this->washAt($now));
    }

    protected function bootWithoutDisplay(): void
    {
        $this->info('Booting ScrapyardIO welcome (console — no main display)…');
        $this->writeCenteredCli($this->label);
        $this->info('Press Ctrl+C to stop.');
    }

    protected function maybeAdvanceFont(float $nowSeconds): void
    {
        if (count($this->fontNames) <= 1) {
            return;
        }

        if (($nowSeconds - $this->fontSwitchedAt) < $this->fontCycleSeconds) {
            return;
        }

        $this->fontIndex = ($this->fontIndex + 1) % count($this->fontNames);
        $this->fontSwitchedAt = $nowSeconds;
        $this->applyActiveFont();
    }

    /**
     * Changing the font remeasures the label, which re-runs the fit and re-centres
     * it. Both used to be this sketch's problem.
     */
    protected function applyActiveFont(): void
    {
        if ($this->fontNames === []) {
            return;
        }

        $this->splash->setFont($this->fontNames[$this->fontIndex]);
    }

    /**
     * A slow hue wash, declared as RGB and packed by whatever the surface is.
     * The per-depth packing this used to carry now lives in Color.
     */
    protected function washAt(float $nowSeconds): Color
    {
        [$r, $g, $b] = $this->hsvToRgb(fmod(max(0.0, $nowSeconds) * 0.08, 1.0), 0.55, 0.35);

        return Color::rgb($r, $g, $b);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    protected function hsvToRgb(float $h, float $s, float $v): array
    {
        $i = (int) floor($h * 6);
        $f = ($h * 6) - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - ($f * $s));
        $t = $v * (1 - ((1 - $f) * $s));

        [$rf, $gf, $bf] = match ($i % 6) {
            0 => [$v, $t, $p],
            1 => [$q, $v, $p],
            2 => [$p, $v, $t],
            3 => [$p, $q, $v],
            4 => [$t, $p, $v],
            default => [$v, $p, $q],
        };

        return [
            (int) round($rf * 255),
            (int) round($gf * 255),
            (int) round($bf * 255),
        ];
    }

    protected function writeCenteredCli(string $text): void
    {
        $width = 40;
        $pad = max(0, intdiv($width - strlen($text), 2));

        $this->info(str_repeat('=', $width));
        $this->info(str_repeat(' ', $pad).$text);
        $this->info(str_repeat('=', $width));
    }
}
