<?php

namespace FA\Interfaces;

/**
 * Purchasing Repository Interface
 *
 * Abstracts access to purchasing data for dependency injection.
 *
 * @package FA\Interfaces
 */
interface PurchasingRepositoryInterface
{
    /**
     * Get purchase price for item and supplier
     *
     * @param string $stockId Stock ID
     * @param string $currency Currency code
     * @param int $supplierId Supplier ID
     * @return float|null Price or null
     */
    public function getPurchasePrice(
        string $stockId,
        string $currency,
        int $supplierId
    ): ?float;

    /**
     * Get supplier data
     *
     * @param int $supplierId Supplier ID
     * @return array|null Supplier record or null
     */
    public function getSupplier(int $supplierId): ?array;

    /**
     * Get purchase order
     *
     * @param int $orderId Order ID
     * @return array|null Order record or null
     */
    public function getPurchaseOrder(int $orderId): ?array;

    /**
     * Get purchase order lines
     *
     * @param int $orderId Order ID
     * @return array Array of line records
     */
    public function getPurchaseOrderLines(int $orderId): array;

    /**
     * Get purchase data for item
     *
     * @param string $stockId Stock ID
     * @param int $supplierId Supplier ID
     * @return array|null Purchase data or null
     */
    public function getPurchaseData(string $stockId, int $supplierId): ?array;
}
