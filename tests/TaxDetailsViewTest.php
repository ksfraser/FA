<?php

use PHPUnit\Framework\TestCase;
use FA\TaxDetailsView;
use Ksfraser\HTML\HtmlFragment;

class TaxDetailsViewTest extends TestCase
{
    public function testRenderWithEmptyTaxItems()
    {
        $view = new TaxDetailsView();
        $result = $view->render([], 6);
        $this->assertInstanceOf(HtmlFragment::class, $result);
        // Empty result should be empty fragment
    }
}