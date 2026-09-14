<?php

namespace Tests\Support;

use App\Sketches\SeesawNeoSlider;
use Fabricate\Contracts\Core\VisualPresentation;

/**
 * Headless double for SeesawNeoSlider: keeps the tree and skips I2C sampling.
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
