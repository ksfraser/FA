<?php

namespace FA;

use FA\Interfaces\PurchasingRepositoryInterface;

/**
 * Production Purchasing Repository
 *
 * Real implementation that accesses the database for purchasing data.
 *
 * @package FA
 */
class ProductionPurchasingRepository implements PurchasingRepositoryInterface
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
    ): ?float {
        $sql = "SELECT price FROM " . TB_PREF . "purch_data 
                WHERE stock_id=" . \db_escape($stockId) . " 
                AND supplier_id=" . \db_escape($supplierId);
        
        $result = \db_query($sql);
        $row = \db_fetch($result);
        
        return $row ? (float)$row['price'] : null;
    }

    /**
     * Get supplier data
     *
     * @param int $supplierId Supplier ID
     * @return array|null Supplier record or null
     */
    public function getSupplier(int $supplierId): ?array
    {
        $sql = "SELECT * FROM " . TB_PREF . "suppliers WHERE supplier_id=" . \db_escape($supplierId);
        $result = \db_query($sql);
        $row = \db_fetch($result);
        return $row ?: null;
    }

    /**
     * Get purchase order
     *
     * @param int $orderId Order ID
     * @return array|null Order record or null
     */
    public function getPurchaseOrder(int $orderId): ?array
    {
        $sql = "SELECT * FROM " . TB_PREF . "purch_orders WHERE order_no=" . \db_escape($orderId);
        $result = \db_query($sql);
        $row = \db_fetch($result);
        return $row ?: null;
    }

    /**
     * Get purchase order lines
     *
     * @param int $orderId Order ID
     * @return array Array of line records
     */
    public function getPurchaseOrderLines(int $orderId): array
    {
        $sql = "SELECT * FROM " . TB_PREF . "purch_order_details 
                WHERE order_no=" . \db_escape($orderId) . " 
                ORDER BY po_detail_item";
        $result = \db_query($sql);
        $lines = [];
        while ($row = \db_fetch($result)) {
            $lines[] = $row;
        }
        return $lines;
    }

    /**
     * Get purchase data for item
     *
     * @param string $stockId Stock ID
     * @param int $supplierId Supplier ID
     * @return array|null Purchase data or null
     */
    public function getPurchaseData(string $stockId, int $supplierId): ?array
    {
        $sql = "SELECT * FROM " . TB_PREF . "purch_data 
                WHERE stock_id=" . \db_escape($stockId) . " 
                AND supplier_id=" . \db_escape($supplierId);
        $result = \db_query($sql);
        $row = \db_fetch($result);
        return $row ?: null;
    }
}
