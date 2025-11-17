<?php

namespace FA\Tests;

use PHPUnit\Framework\TestCase;
use FA\InventoryService;
use FA\Tests\Mocks\MockItemRepository;

/**
 * Inventory Service Test with Dependency Injection
 *
 * @package FA\Tests
 */
class InventoryServiceDITest extends TestCase
{
    private InventoryService $service;
    private MockItemRepository $itemRepo;

    protected function setUp(): void
    {
        $this->itemRepo = new MockItemRepository();
        $this->service = new InventoryService($this->itemRepo);
    }

    /** @test */
    public function testIsManufactured(): void
    {
        $this->assertTrue($this->service->isManufactured('M'));
        $this->assertFalse($this->service->isManufactured('B'));
        $this->assertFalse($this->service->isManufactured('D'));
        $this->assertFalse($this->service->isManufactured('F'));
    }

    /** @test */
    public function testIsPurchased(): void
    {
        $this->assertTrue($this->service->isPurchased('B'));
        $this->assertFalse($this->service->isPurchased('M'));
        $this->assertFalse($this->service->isPurchased('D'));
        $this->assertFalse($this->service->isPurchased('F'));
    }

    /** @test */
    public function testIsService(): void
    {
        $this->assertTrue($this->service->isService('D'));
        $this->assertFalse($this->service->isService('M'));
        $this->assertFalse($this->service->isService('B'));
        $this->assertFalse($this->service->isService('F'));
    }

    /** @test */
    public function testIsFixedAsset(): void
    {
        $this->assertTrue($this->service->isFixedAsset('F'));
        $this->assertFalse($this->service->isFixedAsset('M'));
        $this->assertFalse($this->service->isFixedAsset('B'));
        $this->assertFalse($this->service->isFixedAsset('D'));
    }

    /** @test */
    public function testHasStockHolding(): void
    {
        $this->assertTrue($this->service->hasStockHolding('M'));
        $this->assertTrue($this->service->hasStockHolding('B'));
        $this->assertFalse($this->service->hasStockHolding('D'));
        $this->assertFalse($this->service->hasStockHolding('F'));
    }

    /** @test */
    public function testServiceCanBeCreatedWithoutDependencies(): void
    {
        $service = new InventoryService();
        $this->assertInstanceOf(InventoryService::class, $service);
    }

    /** @test */
    public function testItemRepositoryCanBeInjected(): void
    {
        $repo = new MockItemRepository();
        $repo->addItem([
            'stock_id' => 'ITEM001',
            'mb_flag' => 'M',
            'description' => 'Test Item'
        ]);

        $service = new InventoryService($repo);
        $this->assertInstanceOf(InventoryService::class, $service);
    }
}
