<?php

namespace Tests\Support;

use App\Sketches\Welcome;
use Fabricate\Contracts\Core\VisualPresentation;

/**
 * Headless double for Welcome: replaces only the display so boot/loop/shutdown
 * can run without an SSD1306 or SDL window.
 */
final class HeadlessWelcome extends Welcome
{
    public function __construct(private readonly VisualPresentation $surface) {}

    protected function presentation(): ?VisualPresentation
    {
        return $this->surface;
    }
}
