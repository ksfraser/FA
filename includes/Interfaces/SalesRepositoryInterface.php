<?php

namespace FA\Interfaces;

/**
 * Sales Repository Interface
 *
 * Abstracts access to sales data for dependency injection.
 *
 * @package FA\Interfaces
 */
interface SalesRepositoryInterface
{
    /**
     * Get price for item and customer
     *
     * @param string $stockId Stock ID
     * @param string $currency Currency code
     * @param string $salesType Sales type ID
     * @param float $factor Conversion factor
     * @param string|null $date Date for price
     * @return float|null Price or null
     */
    public function getPrice(
        string $stockId,
        string $currency,
        string $salesType,
        float $factor = 1.0,
        ?string $date = null
    ): ?float;

    /**
     * Get customer data
     *
     * @param int $customerId Customer ID
     * @return array|null Customer record or null
     */
    public function getCustomer(int $customerId): ?array;

    /**
     * Get sales transaction
     *
     * @param int $type Transaction type
     * @param int $transNo Transaction number
     * @return array|null Transaction record or null
     */
    public function getSalesTransaction(int $type, int $transNo): ?array;

    /**
     * Get sales order lines
     *
     * @param int $orderId Order ID
     * @return array Array of line records
     */
    public function getSalesOrderLines(int $orderId): array;
}
