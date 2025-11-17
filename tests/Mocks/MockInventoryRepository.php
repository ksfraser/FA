<?php

namespace FA\Tests\Mocks;

use FA\Interfaces\InventoryRepositoryInterface;

/**
 * Mock Inventory Repository for Testing
 *
 * @package FA\Tests\Mocks
 */
class MockInventoryRepository implements InventoryRepositoryInterface
{
    private array $movements = [];
    private array $images = [];
    private array $stockLevels = [];
    private array $reorderLevels = [];

    public function addMovement(array $movement): void
    {
        $this->movements[] = $movement;
    }

    public function setImage(string $stockId, string $image): void
    {
        $this->images[$stockId] = $image;
    }

    public function setStockLevel(string $stockId, ?string $location, float $quantity): void
    {
        $key = $location ? "{$stockId}_{$location}" : $stockId;
        $this->stockLevels[$key] = $quantity;
    }

    public function setReorderLevel(string $stockId, string $location, float $level): void
    {
        $this->reorderLevels["{$stockId}_{$location}"] = $level;
    }

    public function getStockMovements(
        string $stockId,
        ?string $location = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        $filtered = array_filter($this->movements, function($movement) use ($stockId, $location, $fromDate, $toDate) {
            if ($movement['stock_id'] !== $stockId) return false;
            if ($location !== null && ($movement['loc_code'] ?? null) !== $location) return false;
            if ($fromDate !== null && ($movement['tran_date'] ?? '') < $fromDate) return false;
            if ($toDate !== null && ($movement['tran_date'] ?? '') > $toDate) return false;
            return true;
        });
        return array_values($filtered);
    }

    public function getItemImageName(string $stockId): ?string
    {
        return $this->images[$stockId] ?? null;
    }

    public function getStockLevels(string $stockId, ?string $location = null): array
    {
        $key = $location ? "{$stockId}_{$location}" : $stockId;
        $quantity = $this->stockLevels[$key] ?? 0.0;
        
        return [
            'stock_id' => $stockId,
            'location' => $location,
            'quantity' => $quantity
        ];
    }

    public function getReorderLevel(string $stockId, string $location): float
    {
        return $this->reorderLevels["{$stockId}_{$location}"] ?? 0.0;
    }
}
