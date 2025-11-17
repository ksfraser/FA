<?php

namespace FA;

use FA\Interfaces\ItemRepositoryInterface;

/**
 * Production Item Repository
 *
 * Real implementation that accesses the database for inventory item data.
 *
 * @package FA
 */
class ProductionItemRepository implements ItemRepositoryInterface
{
    /**
     * Get item by stock ID
     *
     * @param string $stockId Stock ID
     * @return array|null Item record or null if not found
     */
    public function getItem(string $stockId): ?array
    {
        $sql = "SELECT * FROM " . TB_PREF . "stock_master WHERE stock_id=" . \db_escape($stockId);
        $result = \db_query($sql);
        $row = \db_fetch($result);
        return $row ?: null;
    }

    /**
     * Get item manufacturing flag
     *
     * @param string $stockId Stock ID
     * @return string|null Manufacturing flag or null
     */
    public function getManufacturingFlag(string $stockId): ?string
    {
        $item = $this->getItem($stockId);
        return $item ? ($item['mb_flag'] ?? null) : null;
    }

    /**
     * Get all items
     *
     * @return array Array of item records
     */
    public function getAllItems(): array
    {
        $sql = "SELECT * FROM " . TB_PREF . "stock_master ORDER BY stock_id";
        $result = \db_query($sql);
        $items = [];
        while ($row = \db_fetch($result)) {
            $items[] = $row;
        }
        return $items;
    }
}
