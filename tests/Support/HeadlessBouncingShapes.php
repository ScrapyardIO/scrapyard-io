<?php

namespace Tests\Support;

use App\Sketches\BouncingShapes;
use Fabricate\Contracts\Core\VisualPresentation;
use Fabricate\NutsAndBolts\Geometry\Rect;

/**
 * Headless double for BouncingShapes: replaces only the display so motion and
 * layout can be asserted without hardware.
 */
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
