<?php

use PHPUnit\Framework\TestCase;
use FA\ViewCreditNote;

class ViewCreditNoteTest extends TestCase
{
    public function testConstructorLoadsData()
    {
        $model = $this->createMock(\FA\CreditNote::class);
        $view = new ViewCreditNote($model);
        $this->assertInstanceOf(ViewCreditNote::class, $view);
    }

    public function testGetSubTotal()
    {
        $model = $this->createMock(\FA\CreditNote::class);
        $model->method('getSubTotal')->willReturn(150.0);
        $view = new ViewCreditNote($model);
        $this->assertEquals(150.0, $view->getSubTotal());
    }
}