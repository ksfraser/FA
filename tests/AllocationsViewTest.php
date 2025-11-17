<?php

use PHPUnit\Framework\TestCase;
use FA\AllocationsView;
use Ksfraser\HTML\HtmlFragment;

class AllocationsViewTest extends TestCase
{
    public function testRenderWithEmptyAllocations()
    {
        $view = new AllocationsView();
        $result = $view->render([], 100.0, "Allocations");
        $this->assertInstanceOf(HtmlFragment::class, $result);
        // Should be empty
    }
}