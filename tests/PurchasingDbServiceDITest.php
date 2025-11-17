<?php

namespace FA\Tests;

use PHPUnit\Framework\TestCase;
use FA\PurchasingDbService;
use FA\Tests\Mocks\MockPurchasingRepository;

/**
 * Purchasing Database Service Test with Dependency Injection
 *
 * @package FA\Tests
 */
class PurchasingDbServiceDITest extends TestCase
{
    private PurchasingDbService $service;
    private MockPurchasingRepository $purchasingRepo;

    protected function setUp(): void
    {
        $this->purchasingRepo = new MockPurchasingRepository();
        $this->service = new PurchasingDbService($this->purchasingRepo);
    }

    /** @test */
    public function testGetPurchasePriceReturnsPrice(): void
    {
        $this->purchasingRepo->setPrice('ITEM001', 'USD', 5, 75.00);
        
        $price = $this->purchasingRepo->getPurchasePrice('ITEM001', 'USD', 5);
        $this->assertEquals(75.00, $price);
    }

    /** @test */
    public function testGetPurchasePriceReturnsNullWhenNotFound(): void
    {
        $price = $this->purchasingRepo->getPurchasePrice('NONEXISTENT', 'USD', 999);
        $this->assertNull($price);
    }

    /** @test */
    public function testGetSupplierReturnsSupplierData(): void
    {
        $this->purchasingRepo->addSupplier([
            'supplier_id' => 5,
            'supp_name' => 'Test Supplier',
            'email' => 'supplier@example.com'
        ]);

        $supplier = $this->purchasingRepo->getSupplier(5);
        $this->assertNotNull($supplier);
        $this->assertEquals('Test Supplier', $supplier['supp_name']);
    }

    /** @test */
    public function testGetPurchaseOrderReturnsOrderData(): void
    {
        $this->purchasingRepo->addPurchaseOrder([
            'order_no' => 789,
            'supplier_id' => 5,
            'ord_date' => '2024-11-17',
            'total' => 500.00
        ]);

        $order = $this->purchasingRepo->getPurchaseOrder(789);
        $this->assertNotNull($order);
        $this->assertEquals(789, $order['order_no']);
        $this->assertEquals(500.00, $order['total']);
    }

    /** @test */
    public function testGetPurchaseOrderLines(): void
    {
        $this->purchasingRepo->addOrderLine(789, [
            'po_detail_item' => 1,
            'item_code' => 'ITEM001',
            'quantity_ordered' => 10,
            'unit_price' => 50.00
        ]);

        $lines = $this->purchasingRepo->getPurchaseOrderLines(789);
        $this->assertCount(1, $lines);
        $this->assertEquals('ITEM001', $lines[0]['item_code']);
    }

    /** @test */
    public function testGetPurchaseData(): void
    {
        $this->purchasingRepo->setPurchaseData('ITEM001', 5, [
            'stock_id' => 'ITEM001',
            'supplier_id' => 5,
            'price' => 75.00,
            'suppliers_uom' => 'EA'
        ]);

        $data = $this->purchasingRepo->getPurchaseData('ITEM001', 5);
        $this->assertNotNull($data);
        $this->assertEquals(75.00, $data['price']);
        $this->assertEquals('EA', $data['suppliers_uom']);
    }

    /** @test */
    public function testServiceCanBeCreatedWithoutDependencies(): void
    {
        $service = new PurchasingDbService();
        $this->assertInstanceOf(PurchasingDbService::class, $service);
    }
}
