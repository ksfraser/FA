<?php

namespace FA\Interfaces;

/**
 * Item Repository Interface
 *
 * Abstracts access to inventory item data for dependency injection.
 *
 * @package FA\Interfaces
 */
interface ItemRepositoryInterface
{
    /**
     * Get item by stock ID
     *
     * @param string $stockId Stock ID
     * @return array|null Item record or null if not found
     */
    public function getItem(string $stockId): ?array;

    /**
     * Get item manufacturing flag
     *
     * @param string $stockId Stock ID
     * @return string|null Manufacturing flag or null
     */
    public function getManufacturingFlag(string $stockId): ?string;

    /**
     * Get all items
     *
     * @return array Array of item records
     */
    public function getAllItems(): array;
}
