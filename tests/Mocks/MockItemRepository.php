<?php

namespace FA\Tests\Mocks;

use FA\Interfaces\ItemRepositoryInterface;

/**
 * Mock Item Repository for Testing
 *
 * @package FA\Tests\Mocks
 */
class MockItemRepository implements ItemRepositoryInterface
{
    private array $items = [];

    public function addItem(array $item): void
    {
        $this->items[$item['stock_id']] = $item;
    }

    public function getItem(string $stockId): ?array
    {
        return $this->items[$stockId] ?? null;
    }

    public function getManufacturingFlag(string $stockId): ?string
    {
        $item = $this->getItem($stockId);
        return $item ? ($item['mb_flag'] ?? null) : null;
    }

    public function getAllItems(): array
    {
        return array_values($this->items);
    }
}
