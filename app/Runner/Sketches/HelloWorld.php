<?php

namespace App\Runner\Sketches;

use Fabricate\Contracts\Sketches\SketchLoopResult;

class HelloWorld extends Sketch
{
    /**
     * The sketch description.
     *
     * @var string
     */
    protected string $description = 'Print a greeting and stop';

    protected int $ticks = 0;

    /**
     * Execute one cooperative tick of the sketch.
     */
    public function loop(): SketchLoopResult
    {
        $this->ticks++;

        if ($this->ticks === 1) {
            $this->info('Hello from ScrapyardIO Runner');
        }

        return SketchLoopResult::STOP;
    }
}
