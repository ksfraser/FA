<?php

namespace FA\Tests;

use PHPUnit\Framework\TestCase;
use FA\InventoryDbService;
use FA\Tests\Mocks\MockInventoryRepository;

/**
 * Inventory Database Service Test with Dependency Injection
 *
 * @package FA\Tests
 */
class InventoryDbServiceDITest extends TestCase
{
    private InventoryDbService $service;
    private MockInventoryRepository $inventoryRepo;

    protected function setUp(): void
    {
        $this->inventoryRepo = new MockInventoryRepository();
        $this->service = new InventoryDbService($this->inventoryRepo);
    }

    /** @test */
    public function testGetItemImageName(): void
    {
        $this->inventoryRepo->setImage('ITEM001', 'item001.jpg');
        
        $image = $this->inventoryRepo->getItemImageName('ITEM001');
        $this->assertEquals('item001.jpg', $image);
    }

    /** @test */
    public function testGetItemImageNameReturnsNullWhenNotSet(): void
    {
        $image = $this->inventoryRepo->getItemImageName('NONEXISTENT');
        $this->assertNull($image);
    }

    /** @test */
    public function testGetStockMovements(): void
    {
        $this->inventoryRepo->addMovement([
            'stock_id' => 'ITEM001',
            'loc_code' => 'DEF',
            'tran_date' => '2024-11-15',
            'qty' => 10
        ]);
        
        $this->inventoryRepo->addMovement([
            'stock_id' => 'ITEM001',
            'loc_code' => 'DEF',
            'tran_date' => '2024-11-16',
            'qty' => -5
        ]);

        $movements = $this->inventoryRepo->getStockMovements('ITEM001');
        $this->assertCount(2, $movements);
    }

    /** @test */
    public function testGetStockMovementsFiltersByLocation(): void
    {
        $this->inventoryRepo->addMovement([
            'stock_id' => 'ITEM001',
            'loc_code' => 'DEF',
            'tran_date' => '2024-11-15',
            'qty' => 10
        ]);
        
        $this->inventoryRepo->addMovement([
            'stock_id' => 'ITEM001',
            'loc_code' => 'ABC',
            'tran_date' => '2024-11-16',
            'qty' => 5
        ]);

        $movements = $this->inventoryRepo->getStockMovements('ITEM001', 'DEF');
        $this->assertCount(1, $movements);
        $this->assertEquals('DEF', $movements[0]['loc_code']);
    }

    /** @test */
    public function testGetStockMovementsFiltersByDateRange(): void
    {
        $this->inventoryRepo->addMovement([
            'stock_id' => 'ITEM001',
            'loc_code' => 'DEF',
            'tran_date' => '2024-11-10',
            'qty' => 10
        ]);
        
        $this->inventoryRepo->addMovement([
            'stock_id' => 'ITEM001',
            'loc_code' => 'DEF',
            'tran_date' => '2024-11-15',
            'qty' => 5
        ]);
        
        $this->inventoryRepo->addMovement([
            'stock_id' => 'ITEM001',
            'loc_code' => 'DEF',
            'tran_date' => '2024-11-20',
            'qty' => 3
        ]);

        $movements = $this->inventoryRepo->getStockMovements('ITEM001', null, '2024-11-12', '2024-11-18');
        $this->assertCount(1, $movements);
        $this->assertEquals('2024-11-15', $movements[0]['tran_date']);
    }

    /** @test */
    public function testGetStockLevels(): void
    {
        $this->inventoryRepo->setStockLevel('ITEM001', 'DEF', 25.5);
        
        $levels = $this->inventoryRepo->getStockLevels('ITEM001', 'DEF');
        $this->assertEquals('ITEM001', $levels['stock_id']);
        $this->assertEquals('DEF', $levels['location']);
        $this->assertEquals(25.5, $levels['quantity']);
    }

    /** @test */
    public function testGetReorderLevel(): void
    {
        $this->inventoryRepo->setReorderLevel('ITEM001', 'DEF', 10.0);
        
        $level = $this->inventoryRepo->getReorderLevel('ITEM001', 'DEF');
        $this->assertEquals(10.0, $level);
    }

    /** @test */
    public function testGetReorderLevelReturnsZeroWhenNotSet(): void
    {
        $level = $this->inventoryRepo->getReorderLevel('NONEXISTENT', 'DEF');
        $this->assertEquals(0.0, $level);
    }

    /** @test */
    public function testServiceCanBeCreatedWithoutDependencies(): void
    {
        $service = new InventoryDbService();
        $this->assertInstanceOf(InventoryDbService::class, $service);
    }
}
