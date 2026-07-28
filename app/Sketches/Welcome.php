<?php

namespace App\Sketches;

use Fabricate\Contracts\Core\VisualPresentation;
use Fabricate\Contracts\Framebuffers\Enums\BitDepth;
use Fabricate\Contracts\Sketches\SketchLoopResult;
use Fabricate\NutsAndBolts\MagicAliases\Font;
use Fabricate\NutsAndBolts\MagicAliases\Visual;
use Fabricate\Rendering\Fonts\ClassicFont;

class Welcome extends Sketch
{
    /**
     * The sketch description.
     *
     * @var string
     */
    protected string $description = 'Centered ScrapyardIO splash on displays.main (console, windowed, or embedded).';

    protected string $label = 'ScrapyardIO';

    protected int $targetFps = 60;

    protected ?VisualPresentation $screen = null;

    protected bool $animateBackground = false;

    protected int $textSize = 1;

    protected int $textColor = 1;

    protected int $frame = 0;

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
     * Splash scale ceiling — classic 5x7 at size 3+ reads as a brick wall on 240x320.
     */
    protected int $maxTextSize = 2;

    /**
     * Prepare the sketch before the first loop tick.
     */
    public function boot(): void
    {
        $this->screen = Visual::main();

        if (is_null($this->screen)) {
            $this->bootConsole();

            return;
        }

        $this->fontNames = $this->drawableFontNames();
        $this->fontIndex = 0;
        $this->fontSwitchedAt = hrtime(true) / 1e9;

        $this->animateBackground = $this->screen->formatSpec()->bit_depth !== BitDepth::B1;
        $this->textColor = $this->defaultInk();
        $this->applyActiveFont();

        $width = $this->screen->width();
        $height = $this->screen->height();

        $this->info('Booting ScrapyardIO welcome…');
        $this->info("Display ready: {$width}x{$height}".($this->animateBackground ? ' (animated background)' : ''));
        $this->info('Cycling fonts: '.($this->fontNames === [] ? '(none)' : implode(', ', $this->fontNames)));
        $this->info('Press Ctrl+C to stop.');

        $this->drawFrame(0.0);
    }

    /**
     * Execute one cooperative tick of the sketch.
     */
    public function loop(): SketchLoopResult
    {
        if (is_null($this->screen)) {
            usleep(100_000);

            return SketchLoopResult::CONTINUE;
        }

        if ($this->screen->shouldClose()) {
            $this->info('Window closed — stopping welcome.');

            return SketchLoopResult::STOP;
        }

        $loopStart = hrtime(true);
        $nowSeconds = $loopStart / 1e9;
        $this->maybeAdvanceFont($nowSeconds);
        $this->drawFrame($nowSeconds);
        $this->frame++;
        $this->paceFrame($loopStart);

        if ($this->screen->shouldClose()) {
            $this->info('Window closed — stopping welcome.');

            return SketchLoopResult::STOP;
        }

        return SketchLoopResult::CONTINUE;
    }

    /**
     * Release resources after the loop ends or fails.
     */
    public function shutdown(): void
    {
        if (! is_null($this->screen)) {
            if (! $this->screen->shouldClose()) {
                $this->screen->clear(0)->present();
            }

            $this->screen->close();
            $this->screen = null;
        }

        $this->info('Welcome closed. Welcome to ScrapyardIO.');
    }

    protected function bootConsole(): void
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

    protected function applyActiveFont(): void
    {
        if (is_null($this->screen) || $this->fontNames === []) {
            return;
        }

        $name = $this->fontNames[$this->fontIndex];
        $this->screen->setFont($name);
        $this->textSize = $this->largestFittingTextSize();
    }

    /**
     * Skip empty stubs (U8g2 / make:font scaffolding) that have no bitmap payload.
     *
     * @return array<int, string>
     */
    protected function drawableFontNames(): array
    {
        $names = [];

        foreach (array_keys(Font::listFonts()) as $name) {
            if ($name === 'classic') {
                $names[] = $name;

                continue;
            }

            $font = Font::font($name);

            if ($font instanceof ClassicFont || $font->hasBitmapData()) {
                $names[] = $name;
            }
        }

        return $names;
    }

    protected function drawFrame(float $nowSeconds): void
    {
        if (is_null($this->screen)) {
            return;
        }

        if ($this->animateBackground) {
            $this->screen->fill($this->packBackgroundColor($nowSeconds));
        } else {
            $this->screen->clear(0);
        }

        $this->screen->setTextSize($this->textSize);
        $this->screen->setTextColor($this->textColor);
        $this->screen->setTextWrap(false);

        // Adafruit custom fonts report bounds relative to the baseline cursor;
        // subtract x1/y1 so the visual box (not the cursor) is centered.
        $bounds = $this->screen->getTextBounds($this->label, 0, 0);
        $x = (int) intdiv($this->screen->width() - $bounds['w'], 2) - $bounds['x1'];
        $y = (int) intdiv($this->screen->height() - $bounds['h'], 2) - $bounds['y1'];

        $this->screen
            ->setCursor($x, $y)
            ->print($this->label)
            ->present();
    }

    protected function largestFittingTextSize(): int
    {
        if (is_null($this->screen)) {
            return 1;
        }

        $width = $this->screen->width();
        $height = $this->screen->height();
        $size = 1;
        $ceiling = max(1, $this->maxTextSize);

        while ($size < $ceiling) {
            $next = $size + 1;
            $this->screen->setTextSize($next);
            $bounds = $this->screen->getTextBounds($this->label, 0, 0);

            if ($bounds['w'] <= 0 || $bounds['h'] <= 0 || $bounds['w'] > $width || $bounds['h'] > $height) {
                $this->screen->setTextSize($size);

                return $size;
            }

            $size = $next;
        }

        $this->screen->setTextSize($size);

        return $size;
    }

    protected function defaultInk(): int
    {
        return match ($this->screen->formatSpec()->bit_depth) {
            BitDepth::B1 => 1,
            BitDepth::B12 => 0x0FFF,
            BitDepth::B16 => 0xFFFF,
            BitDepth::B18 => 0xFCFCFC,
            default => 0xFFFFFFFF,
        };
    }

    /**
     * Slow hue wash packed for the active pixel format.
     */
    protected function packBackgroundColor(float $nowSeconds): int
    {
        $hue = fmod(max(0.0, $nowSeconds) * 0.08, 1.0);
        [$r, $g, $b] = $this->hsvToRgb($hue, 0.55, 0.35);

        return match ($this->screen->formatSpec()->bit_depth) {
            BitDepth::B12 => $this->packRgb444($r, $g, $b),
            BitDepth::B16 => $this->packRgb565($r, $g, $b),
            BitDepth::B18 => $this->packRgb666($r, $g, $b),
            default => (($r & 0xFF) << 24) | (($g & 0xFF) << 16) | (($b & 0xFF) << 8) | 0xFF,
        };
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

    protected function packRgb444(int $r, int $g, int $b): int
    {
        return (($r & 0xF0) << 4) | ($g & 0xF0) | (($b & 0xF0) >> 4);
    }

    protected function packRgb565(int $r, int $g, int $b): int
    {
        return (($r & 0xF8) << 8) | (($g & 0xFC) << 3) | (($b & 0xF8) >> 3);
    }

    /**
     * ST77xx COLOR18 left-justified RGB666 word (`RRRRRRxx GGGGGGxx BBBBBBxx`).
     */
    protected function packRgb666(int $r, int $g, int $b): int
    {
        return (($r & 0xFC) << 16) | (($g & 0xFC) << 8) | ($b & 0xFC);
    }

    protected function writeCenteredCli(string $text): void
    {
        $width = 40;
        $pad = max(0, intdiv($width - strlen($text), 2));
        $line = str_repeat(' ', $pad).$text;

        $this->info(str_repeat('=', $width));
        $this->info($line);
        $this->info(str_repeat('=', $width));
    }

    protected function paceFrame(int $loopStartNs): void
    {
        $budgetNs = (int) (1_000_000_000 / $this->targetFps);
        $remainingNs = $budgetNs - (hrtime(true) - $loopStartNs);

        if ($remainingNs > 1_000) {
            usleep((int) ($remainingNs / 1_000));
        }
    }
}
