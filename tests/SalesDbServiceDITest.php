<?php

namespace FA\Tests;

use PHPUnit\Framework\TestCase;
use FA\SalesDbService;
use FA\Tests\Mocks\MockSalesRepository;

/**
 * Sales Database Service Test with Dependency Injection
 *
 * @package FA\Tests
 */
class SalesDbServiceDITest extends TestCase
{
    private SalesDbService $service;
    private MockSalesRepository $salesRepo;

    protected function setUp(): void
    {
        $this->salesRepo = new MockSalesRepository();
        $this->service = new SalesDbService($this->salesRepo);
    }

    /** @test */
    public function testGetPriceReturnsPriceWhenFound(): void
    {
        $this->salesRepo->setPrice('ITEM001', 'USD', 'RETAIL', 100.00);
        
        $price = $this->salesRepo->getPrice('ITEM001', 'USD', 'RETAIL');
        $this->assertEquals(100.00, $price);
    }

    /** @test */
    public function testGetPriceReturnsNullWhenNotFound(): void
    {
        $price = $this->salesRepo->getPrice('NONEXISTENT', 'USD', 'RETAIL');
        $this->assertNull($price);
    }

    /** @test */
    public function testGetPriceAppliesConversionFactor(): void
    {
        $this->salesRepo->setPrice('ITEM001', 'USD', 'RETAIL', 100.00);
        
        $price = $this->salesRepo->getPrice('ITEM001', 'USD', 'RETAIL', 1.5);
        $this->assertEquals(150.00, $price);
    }

    /** @test */
    public function testGetCustomerReturnsCustomerData(): void
    {
        $this->salesRepo->addCustomer([
            'debtor_no' => 1,
            'name' => 'Test Customer',
            'email' => 'test@example.com'
        ]);

        $customer = $this->salesRepo->getCustomer(1);
        $this->assertNotNull($customer);
        $this->assertEquals('Test Customer', $customer['name']);
        $this->assertEquals('test@example.com', $customer['email']);
    }

    /** @test */
    public function testGetCustomerReturnsNullForInvalidId(): void
    {
        $customer = $this->salesRepo->getCustomer(999);
        $this->assertNull($customer);
    }

    /** @test */
    public function testGetSalesTransaction(): void
    {
        $this->salesRepo->addTransaction(10, 123, [
            'type' => 10,
            'trans_no' => 123,
            'debtor_no' => 1,
            'amount' => 1000.00
        ]);

        $trans = $this->salesRepo->getSalesTransaction(10, 123);
        $this->assertNotNull($trans);
        $this->assertEquals(10, $trans['type']);
        $this->assertEquals(123, $trans['trans_no']);
        $this->assertEquals(1000.00, $trans['amount']);
    }

    /** @test */
    public function testGetSalesOrderLines(): void
    {
        $this->salesRepo->addOrderLine(456, [
            'id' => 1,
            'stock_id' => 'ITEM001',
            'quantity' => 5,
            'unit_price' => 100.00
        ]);
        
        $this->salesRepo->addOrderLine(456, [
            'id' => 2,
            'stock_id' => 'ITEM002',
            'quantity' => 3,
            'unit_price' => 50.00
        ]);

        $lines = $this->salesRepo->getSalesOrderLines(456);
        $this->assertCount(2, $lines);
        $this->assertEquals('ITEM001', $lines[0]['stock_id']);
        $this->assertEquals('ITEM002', $lines[1]['stock_id']);
    }

    /** @test */
    public function testServiceCanBeCreatedWithoutDependencies(): void
    {
        $service = new SalesDbService();
        $this->assertInstanceOf(SalesDbService::class, $service);
    }
}
