<?php

use PHPUnit\Framework\TestCase;

class inventoryTest extends TestCase
{
    protected function setUp(): void
    {
        global $path_to_root;
        $path_to_root = __DIR__ . '/..';
        require_once __DIR__ . '/../includes/inventory.inc';
    }

    public function testIsManufactured()
    {
        $this->assertTrue(is_manufactured('M'));
        $this->assertFalse(is_manufactured('B'));
        $this->assertFalse(is_manufactured('D'));
    }

    public function testIsPurchased()
    {
        $this->assertTrue(is_purchased('B'));
        $this->assertFalse(is_purchased('M'));
    }

    public function testIsService()
    {
        $this->assertTrue(is_service('D'));
        $this->assertFalse(is_service('M'));
    }

    public function testIsFixedAsset()
    {
        $this->assertTrue(is_fixed_asset('F'));
        $this->assertFalse(is_fixed_asset('M'));
    }

    public function testHasStockHolding()
    {
        $this->assertTrue(has_stock_holding('M'));
        $this->assertTrue(has_stock_holding('B'));
        $this->assertFalse(has_stock_holding('D'));
        $this->assertFalse(has_stock_holding('F'));
    }
}

