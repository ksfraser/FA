<?php
/**
 * Serial Tracking Database Functions
 *
 * Functions for managing serial number tracking in FrontAccounting
 * Inspired by WebERP's serial tracking implementation
 */

/**
 * Check if an item is serialised
 */
function is_serialised_item($stock_id)
{
    $sql = "SELECT serialised FROM 0_stock_master WHERE stock_id = ?";
    $result = db_query($sql, "Could not check if item is serialised");
    $row = db_fetch_row($result);
    return $row[0] == 1;
}

/**
 * Check if an item is controlled
 */
function is_controlled_item($stock_id)
{
    $sql = "SELECT controlled FROM 0_stock_master WHERE stock_id = ?";
    $result = db_query($sql, "Could not check if item is controlled");
    $row = db_fetch_row($result);
    return $row[0] == 1;
}

/**
 * Get serial items for a stock item and location
 */
function get_serial_items($stock_id, $loc_code = null, $serial_no = null)
{
    $sql = "SELECT * FROM 0_stock_serial_items WHERE stock_id = ?";
    $params = array($stock_id);

    if ($loc_code !== null) {
        $sql .= " AND loc_code = ?";
        $params[] = $loc_code;
    }

    if ($serial_no !== null) {
        $sql .= " AND serial_no LIKE ?";
        $params[] = '%' . $serial_no . '%';
    }

    $sql .= " ORDER BY serial_no";

    return db_query($sql, "Could not get serial items");
}

/**
 * Get available serial numbers for an item at a location
 */
function get_available_serial_items($stock_id, $loc_code, $exclude_zero_qty = true)
{
    $sql = "SELECT * FROM 0_stock_serial_items
            WHERE stock_id = ? AND loc_code = ?";

    $params = array($stock_id, $loc_code);

    if ($exclude_zero_qty) {
        $sql .= " AND quantity > 0";
    }

    $sql .= " ORDER BY serial_no";

    return db_query($sql, "Could not get available serial items");
}

/**
 * Add a serial item
 */
function add_serial_item($stock_id, $loc_code, $serial_no, $quantity = 1, $expiration_date = null, $quality_text = '')
{
    if (empty($expiration_date)) {
        $expiration_date = '0000-00-00 00:00:00';
    }

    $sql = "INSERT INTO 0_stock_serial_items
            (stock_id, loc_code, serial_no, expiration_date, quantity, quality_text, createdate)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $result = db_query($sql, "Could not add serial item",
        array($stock_id, $loc_code, $serial_no, $expiration_date, $quantity, $quality_text));

    return $result !== false;
}

/**
 * Update serial item quantity
 */
function update_serial_item_quantity($stock_id, $loc_code, $serial_no, $quantity)
{
    $sql = "UPDATE 0_stock_serial_items
            SET quantity = ?
            WHERE stock_id = ? AND loc_code = ? AND serial_no = ?";

    $result = db_query($sql, "Could not update serial item quantity",
        array($quantity, $stock_id, $loc_code, $serial_no));

    return $result !== false;
}

/**
 * Move serial item to different location
 */
function move_serial_item($stock_id, $from_loc, $to_loc, $serial_no, $quantity)
{
    // Check if serial exists at source location
    $sql = "SELECT quantity FROM 0_stock_serial_items
            WHERE stock_id = ? AND loc_code = ? AND serial_no = ?";
    $result = db_query($sql, "Could not check serial item", array($stock_id, $from_loc, $serial_no));

    if (db_num_rows($result) == 0) {
        return false;
    }

    $row = db_fetch($result);
    $current_qty = $row['quantity'];

    if ($current_qty < $quantity) {
        return false; // Insufficient quantity
    }

    // Start transaction
    begin_transaction();

    // Reduce quantity at source location
    if ($current_qty == $quantity) {
        // Remove from source location
        $sql = "DELETE FROM 0_stock_serial_items
                WHERE stock_id = ? AND loc_code = ? AND serial_no = ?";
        db_query($sql, "Could not remove serial from source", array($stock_id, $from_loc, $serial_no));
    } else {
        // Reduce quantity at source
        update_serial_item_quantity($stock_id, $from_loc, $serial_no, $current_qty - $quantity);
    }

    // Check if serial exists at destination
    $sql = "SELECT quantity FROM 0_stock_serial_items
            WHERE stock_id = ? AND loc_code = ? AND serial_no = ?";
    $result = db_query($sql, "Could not check destination serial", array($stock_id, $to_loc, $serial_no));

    if (db_num_rows($result) > 0) {
        // Update existing serial at destination
        $row = db_fetch($result);
        $dest_qty = $row['quantity'];
        update_serial_item_quantity($stock_id, $to_loc, $serial_no, $dest_qty + $quantity);
    } else {
        // Add new serial at destination
        add_serial_item($stock_id, $to_loc, $serial_no, $quantity);
    }

    commit_transaction();
    return true;
}

/**
 * Add serial movement record
 */
function add_serial_movement($stockmoveno, $stock_id, $serial_no, $moveqty)
{
    $sql = "INSERT INTO 0_stock_serial_moves
            (stockmoveno, stock_id, serial_no, moveqty)
            VALUES (?, ?, ?, ?)";

    $result = db_query($sql, "Could not add serial movement",
        array($stockmoveno, $stock_id, $serial_no, $moveqty));

    return $result !== false;
}

/**
 * Get serial movements for a serial number
 */
function get_serial_movements($serial_no, $stock_id = null)
{
    $sql = "SELECT sm.*, m.tran_date, m.type, m.trans_no, m.reference, m.qty, m.loc_code, m.loc_code2
            FROM 0_stock_serial_moves sm
            LEFT JOIN 0_stock_moves m ON sm.stockmoveno = m.trans_id
            WHERE sm.serial_no = ?";

    $params = array($serial_no);

    if ($stock_id !== null) {
        $sql .= " AND sm.stock_id = ?";
        $params[] = $stock_id;
    }

    $sql .= " ORDER BY m.tran_date DESC, m.trans_id DESC";

    return db_query($sql, "Could not get serial movements");
}

/**
 * Validate serial numbers for a transaction
 */
function validate_serial_numbers($stock_id, $serial_nos, $quantity, $loc_code)
{
    if (!is_serialised_item($stock_id)) {
        return true; // Not serialised, no validation needed
    }

    if (count($serial_nos) != $quantity) {
        return false; // Must have one serial per unit
    }

    // Check all serials exist and are available
    foreach ($serial_nos as $serial_no) {
        $sql = "SELECT quantity FROM 0_stock_serial_items
                WHERE stock_id = ? AND loc_code = ? AND serial_no = ? AND quantity > 0";
        $result = db_query($sql, "Could not validate serial number",
            array($stock_id, $loc_code, $serial_no));

        if (db_num_rows($result) == 0) {
            return false; // Serial not available
        }
    }

    return true;
}

/**
 * Get serial number summary for reporting
 */
function get_serial_summary($stock_id = null, $loc_code = null, $from_date = null, $to_date = null)
{
    $sql = "SELECT s.stock_id, i.description, s.loc_code, l.location_name,
                   COUNT(*) as total_serials,
                   SUM(s.quantity) as total_quantity,
                   COUNT(DISTINCT CASE WHEN s.quantity > 0 THEN s.serial_no END) as available_serials
            FROM 0_stock_serial_items s
            LEFT JOIN 0_stock_master i ON s.stock_id = i.stock_id
            LEFT JOIN 0_locations l ON s.loc_code = l.loc_code
            WHERE 1=1";

    $params = array();

    if ($stock_id !== null) {
        $sql .= " AND s.stock_id = ?";
        $params[] = $stock_id;
    }

    if ($loc_code !== null) {
        $sql .= " AND s.loc_code = ?";
        $params[] = $loc_code;
    }

    if ($from_date !== null) {
        $sql .= " AND s.createdate >= ?";
        $params[] = date2sql($from_date);
    }

    if ($to_date !== null) {
        $sql .= " AND s.createdate <= ?";
        $params[] = date2sql($to_date);
    }

    $sql .= " GROUP BY s.stock_id, s.loc_code ORDER BY s.stock_id, s.loc_code";

    return db_query($sql, "Could not get serial summary");
}

/**
 * Get expired serial items
 */
function get_expired_serial_items($days_ahead = 30)
{
    $sql = "SELECT s.*, i.description, l.location_name
            FROM 0_stock_serial_items s
            LEFT JOIN 0_stock_master i ON s.stock_id = i.stock_id
            LEFT JOIN 0_locations l ON s.loc_code = l.loc_code
            WHERE s.expiration_date != '0000-00-00 00:00:00'
            AND s.expiration_date <= DATE_ADD(NOW(), INTERVAL ? DAY)
            AND s.quantity > 0
            ORDER BY s.expiration_date";

    return db_query($sql, "Could not get expired serial items", array($days_ahead));
}