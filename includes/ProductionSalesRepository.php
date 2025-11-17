<?php

namespace FA;

use FA\Interfaces\SalesRepositoryInterface;

/**
 * Production Sales Repository
 *
 * Real implementation that accesses the database for sales data.
 *
 * @package FA
 */
class ProductionSalesRepository implements SalesRepositoryInterface
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
    ): ?float {
        $date = $date ?? \Today();
        
        $sql = "SELECT price FROM " . TB_PREF . "prices 
                WHERE stock_id=" . \db_escape($stockId) . " 
                AND sales_type_id=" . \db_escape($salesType) . " 
                AND curr_abrev=" . \db_escape($currency);
        
        $result = \db_query($sql);
        $row = \db_fetch($result);
        
        if (!$row) {
            return null;
        }
        
        return (float)$row['price'] * $factor;
    }

    /**
     * Get customer data
     *
     * @param int $customerId Customer ID
     * @return array|null Customer record or null
     */
    public function getCustomer(int $customerId): ?array
    {
        $sql = "SELECT * FROM " . TB_PREF . "debtors_master WHERE debtor_no=" . \db_escape($customerId);
        $result = \db_query($sql);
        $row = \db_fetch($result);
        return $row ?: null;
    }

    /**
     * Get sales transaction
     *
     * @param int $type Transaction type
     * @param int $transNo Transaction number
     * @return array|null Transaction record or null
     */
    public function getSalesTransaction(int $type, int $transNo): ?array
    {
        $sql = "SELECT * FROM " . TB_PREF . "debtor_trans 
                WHERE type=" . \db_escape($type) . " 
                AND trans_no=" . \db_escape($transNo);
        $result = \db_query($sql);
        $row = \db_fetch($result);
        return $row ?: null;
    }

    /**
     * Get sales order lines
     *
     * @param int $orderId Order ID
     * @return array Array of line records
     */
    public function getSalesOrderLines(int $orderId): array
    {
        $sql = "SELECT * FROM " . TB_PREF . "sales_order_details 
                WHERE order_no=" . \db_escape($orderId) . " 
                ORDER BY id";
        $result = \db_query($sql);
        $lines = [];
        while ($row = \db_fetch($result)) {
            $lines[] = $row;
        }
        return $lines;
    }
}
