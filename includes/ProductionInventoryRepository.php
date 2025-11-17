<?php

namespace FA;

use FA\Interfaces\InventoryRepositoryInterface;

/**
 * Production Inventory Repository
 *
 * Real implementation that accesses the database for inventory data.
 *
 * @package FA
 */
class ProductionInventoryRepository implements InventoryRepositoryInterface
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
    ): array {
        $sql = "SELECT * FROM " . TB_PREF . "stock_moves 
                WHERE stock_id=" . \db_escape($stockId);
        
        if ($location !== null) {
            $sql .= " AND loc_code=" . \db_escape($location);
        }
        
        if ($fromDate !== null) {
            $sql .= " AND tran_date >= " . \db_escape($fromDate);
        }
        
        if ($toDate !== null) {
            $sql .= " AND tran_date <= " . \db_escape($toDate);
        }
        
        $sql .= " ORDER BY tran_date DESC, trans_id DESC";
        
        $result = \db_query($sql);
        $movements = [];
        while ($row = \db_fetch($result)) {
            $movements[] = $row;
        }
        return $movements;
    }

    /**
     * Get item image name
     *
     * @param string $stockId Stock ID
     * @return string|null Image filename or null
     */
    public function getItemImageName(string $stockId): ?string
    {
        $sql = "SELECT image FROM " . TB_PREF . "stock_master WHERE stock_id=" . \db_escape($stockId);
        $result = \db_query($sql);
        $row = \db_fetch($result);
        return $row ? ($row['image'] ?: null) : null;
    }

    /**
     * Get stock levels
     *
     * @param string $stockId Stock ID
     * @param string|null $location Location code
     * @return array Stock level data
     */
    public function getStockLevels(string $stockId, ?string $location = null): array
    {
        $sql = "SELECT * FROM " . TB_PREF . "stock_moves 
                WHERE stock_id=" . \db_escape($stockId);
        
        if ($location !== null) {
            $sql .= " AND loc_code=" . \db_escape($location);
        }
        
        $result = \db_query($sql);
        $total_qty = 0;
        
        while ($row = \db_fetch($result)) {
            $total_qty += (float)$row['qty'];
        }
        
        return [
            'stock_id' => $stockId,
            'location' => $location,
            'quantity' => $total_qty
        ];
    }

    /**
     * Get reorder level
     *
     * @param string $stockId Stock ID
     * @param string $location Location code
     * @return float Reorder level
     */
    public function getReorderLevel(string $stockId, string $location): float
    {
        $sql = "SELECT reorder_level FROM " . TB_PREF . "loc_stock 
                WHERE stock_id=" . \db_escape($stockId) . " 
                AND loc_code=" . \db_escape($location);
        
        $result = \db_query($sql);
        $row = \db_fetch($result);
        
        return $row ? (float)$row['reorder_level'] : 0.0;
    }
}
