<?php

use PHPUnit\Framework\TestCase;
use FA\VoidedView;
use Ksfraser\HTML\Elements\HtmlTable;

class VoidedViewTest extends TestCase
{
    public function testRenderWithNoVoidedEntry()
    {
        $view = new VoidedView();
        $result = $view->render(null, "Voided message");
        $this->assertNull($result);
    }
}