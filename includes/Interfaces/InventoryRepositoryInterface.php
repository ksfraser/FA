<?php

namespace FA\Interfaces;

/**
 * Inventory Repository Interface
 *
 * Abstracts access to inventory data for dependency injection.
 *
 * @package FA\Interfaces
 */
interface InventoryRepositoryInterface
{
    /**
     * Get stock movements for item
     *
     * @param string $stockId Stock ID
     * @param string|null $location Location code
     * @param string|null $fromDate From date
     * @param string|null $toDate To date
     * @return array Array of movement records
     */
    public function getStockMovements(
        string $stockId,
        ?string $location = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array;

    /**
     * Get item image name
     *
     * @param string $stockId Stock ID
     * @return string|null Image filename or null
     */
    public function getItemImageName(string $stockId): ?string;

    /**
     * Get stock levels
     *
     * @param string $stockId Stock ID
     * @param string|null $location Location code
     * @return array Stock level data
     */
    public function getStockLevels(string $stockId, ?string $location = null): array;

    /**
     * Get reorder level
     *
     * @param string $stockId Stock ID
     * @param string $location Location code
     * @return float Reorder level
     */
    public function getReorderLevel(string $stockId, string $location): float;
}
