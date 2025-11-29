<?php
/**
 * SerialNumber Repository
 *
 * Data access layer for serial number operations including CRUD operations
 * for serial items, movements, and attributes.
 *
 * @package Database
 * @author FrontAccounting Refactoring Team
 * @license GPL-3.0
 */

namespace Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * SerialNumber Repository Class
 *
 * Handles all database operations for serial number tracking including:
 * - Serial item management (CRUD)
 * - Movement tracking
 * - Custom attributes
 * - Status updates
 */
class SerialNumberRepository
{
    /**
     * Database connection
     */
    private Connection $db;

    /**
     * Table prefix
     */
    private string $prefix;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $db_connections;
        $this->db = $db_connections[user_company()]['connection'] ?? db();
        $this->prefix = $db_connections[user_company()]['tbpref'] ?? '';
    }

    /**
     * Create database tables
     */
    public function createTables(): void
    {
        $sqls = [
            "CREATE TABLE IF NOT EXISTS {$this->prefix}serial_items (
                id int(11) NOT NULL AUTO_INCREMENT,
                stock_id varchar(20) NOT NULL,
                serial_no varchar(50) NOT NULL,
                status enum('active','sold','returned','scrapped','loaned','disposed') NOT NULL DEFAULT 'active',
                location varchar(5) NOT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY unique_serial (stock_id, serial_no),
                KEY idx_stock_id (stock_id),
                KEY idx_status (status),
                KEY idx_location (location)
            ) ENGINE=InnoDB",

            "CREATE TABLE IF NOT EXISTS {$this->prefix}serial_movements (
                id int(11) NOT NULL AUTO_INCREMENT,
                serial_item_id int(11) NOT NULL,
                trans_type int(11) NOT NULL,
                trans_no int(11) NOT NULL,
                stock_id varchar(20) NOT NULL,
                serial_no varchar(50) NOT NULL,
                location_from varchar(5) DEFAULT NULL,
                location_to varchar(5) DEFAULT NULL,
                qty decimal(10,4) NOT NULL DEFAULT 1,
                reference varchar(100) DEFAULT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY (id),
                KEY idx_serial_item (serial_item_id),
                KEY idx_trans (trans_type, trans_no),
                KEY idx_stock_serial (stock_id, serial_no),
                CONSTRAINT fk_serial_movement_item FOREIGN KEY (serial_item_id)
                    REFERENCES {$this->prefix}serial_items (id) ON DELETE CASCADE
            ) ENGINE=InnoDB",

            "CREATE TABLE IF NOT EXISTS {$this->prefix}serial_attributes (
                id int(11) NOT NULL AUTO_INCREMENT,
                serial_item_id int(11) NOT NULL,
                attribute_name varchar(50) NOT NULL,
                attribute_value text,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (id),
                KEY idx_serial_item (serial_item_id),
                KEY idx_attribute (attribute_name),
                CONSTRAINT fk_serial_attribute_item FOREIGN KEY (serial_item_id)
                    REFERENCES {$this->prefix}serial_items (id) ON DELETE CASCADE
            ) ENGINE=InnoDB"
        ];

        foreach ($sqls as $sql) {
            db_query($sql, "Cannot create serial number tables");
        }
    }

    /**
     * Get serial item by stock ID and serial number
     *
     * @param string $stockId Stock item ID
     * @param string $serialNo Serial number
     * @return array|null Serial item data or null if not found
     */
    public function getSerialItem(string $stockId, string $serialNo): ?array
    {
        $sql = "SELECT * FROM {$this->prefix}serial_items
                WHERE stock_id = " . db_escape($stockId) . "
                AND serial_no = " . db_escape($serialNo);

        $result = db_query($sql, "Cannot get serial item");
        return db_fetch_assoc($result) ?: null;
    }

    /**
     * Get serial item by ID
     *
     * @param int $id Serial item ID
     * @return array|null Serial item data or null if not found
     */
    public function getSerialItemById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->prefix}serial_items WHERE id = " . (int)$id;
        $result = db_query($sql, "Cannot get serial item by ID");
        return db_fetch_assoc($result) ?: null;
    }

    /**
     * Get serial item by serial number only
     *
     * @param string $serialNo Serial number
     * @return array|null Serial item data or null if not found
     */
    public function getSerialItemBySerialNo(string $serialNo): ?array
    {
        $sql = "SELECT * FROM {$this->prefix}serial_items WHERE serial_no = " . db_escape($serialNo);
        $result = db_query($sql, "Cannot get serial item by serial number");
        return db_fetch_assoc($result) ?: null;
    }

    /**
     * Get serial items by stock ID
     *
     * @param string $stockId Stock item ID
     * @param string $status Optional status filter
     * @return array List of serial items
     */
    public function getSerialItemsByStock(string $stockId, string $status = ''): array
    {
        $sql = "SELECT * FROM {$this->prefix}serial_items
                WHERE stock_id = " . db_escape($stockId);

        if (!empty($status)) {
            $sql .= " AND status = " . db_escape($status);
        }

        $sql .= " ORDER BY created_at DESC";

        $result = db_query($sql, "Cannot get serial items by stock");
        return db_fetch_all($result);
    }

    /**
     * Create new serial item
     *
     * @param array $data Serial item data
     * @return int Created serial item ID
     */
    public function createSerialItem(array $data): int
    {
        $sql = "INSERT INTO {$this->prefix}serial_items (
                    stock_id, serial_no, status, location, created_at, updated_at
                ) VALUES (
                    " . db_escape($data['stock_id']) . ",
                    " . db_escape($data['serial_no']) . ",
                    " . db_escape($data['status'] ?? 'active') . ",
                    " . db_escape($data['location']) . ",
                    NOW(), NOW()
                )";

        db_query($sql, "Cannot create serial item");
        return db_insert_id();
    }

    /**
     * Update serial item
     *
     * @param int $id Serial item ID
     * @param array $data Update data
     * @return bool True if updated successfully
     */
    public function updateSerialItem(int $id, array $data): bool
    {
        $updates = [];
        $allowedFields = ['status', 'location', 'updated_at'];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                if ($field === 'updated_at') {
                    $updates[] = "updated_at = NOW()";
                } else {
                    $updates[] = "$field = " . db_escape($value);
                }
            }
        }

        if (empty($updates)) {
            return true; // Nothing to update
        }

        $sql = "UPDATE {$this->prefix}serial_items
                SET " . implode(', ', $updates) . "
                WHERE id = " . (int)$id;

        db_query($sql, "Cannot update serial item");
        return db_affected_rows() > 0;
    }

    /**
     * Update serial item status
     *
     * @param int $id Serial item ID
     * @param string $status New status
     * @return bool True if updated successfully
     */
    public function updateSerialStatus(int $id, string $status): bool
    {
        return $this->updateSerialItem($id, ['status' => $status]);
    }

    /**
     * Delete serial item
     *
     * @param int $id Serial item ID
     * @return bool True if deleted successfully
     */
    public function deleteSerialItem(int $id): bool
    {
        $sql = "DELETE FROM {$this->prefix}serial_items WHERE id = " . (int)$id;
        db_query($sql, "Cannot delete serial item");
        return db_affected_rows() > 0;
    }

    /**
     * Create serial movement
     *
     * @param array $data Movement data
     * @return int Created movement ID
     */
    public function createMovement(array $data): int
    {
        $sql = "INSERT INTO {$this->prefix}serial_movements (
                    serial_item_id, trans_type, trans_no, stock_id, serial_no,
                    location_from, location_to, qty, reference, created_at
                ) VALUES (
                    " . (int)$data['serial_item_id'] . ",
                    " . (int)$data['trans_type'] . ",
                    " . (int)$data['trans_no'] . ",
                    " . db_escape($data['stock_id']) . ",
                    " . db_escape($data['serial_no']) . ",
                    " . ($data['location_from'] ? db_escape($data['location_from']) : 'NULL') . ",
                    " . ($data['location_to'] ? db_escape($data['location_to']) : 'NULL') . ",
                    " . (float)$data['qty'] . ",
                    " . ($data['reference'] ? db_escape($data['reference']) : 'NULL') . ",
                    NOW()
                )";

        db_query($sql, "Cannot create serial movement");
        return db_insert_id();
    }

    /**
     * Get movements by transaction
     *
     * @param int $transType Transaction type
     * @param int $transNo Transaction number
     * @return array List of movements
     */
    public function getMovementsByTransaction(int $transType, int $transNo): array
    {
        $sql = "SELECT * FROM {$this->prefix}serial_movements
                WHERE trans_type = " . (int)$transType . "
                AND trans_no = " . (int)$transNo . "
                ORDER BY created_at DESC";

        $result = db_query($sql, "Cannot get movements by transaction");
        return db_fetch_all($result);
    }

    /**
     * Get movements by serial item
     *
     * @param int $serialItemId Serial item ID
     * @return array List of movements
     */
    public function getMovementsBySerial(int $serialItemId): array
    {
        $sql = "SELECT * FROM {$this->prefix}serial_movements
                WHERE serial_item_id = " . (int)$serialItemId . "
                ORDER BY created_at DESC";

        $result = db_query($sql, "Cannot get movements by serial");
        return db_fetch_all($result);
    }

    /**
     * Get latest movement for serial item
     *
     * @param int $serialItemId Serial item ID
     * @return array|null Latest movement data or null
     */
    public function getLatestMovement(int $serialItemId): ?array
    {
        $sql = "SELECT * FROM {$this->prefix}serial_movements
                WHERE serial_item_id = " . (int)$serialItemId . "
                ORDER BY created_at DESC LIMIT 1";

        $result = db_query($sql, "Cannot get latest movement");
        return db_fetch_assoc($result) ?: null;
    }

    /**
     * Create serial attribute
     *
     * @param array $data Attribute data
     * @return int Created attribute ID
     */
    public function createAttribute(array $data): int
    {
        $sql = "INSERT INTO {$this->prefix}serial_attributes (
                    serial_item_id, attribute_name, attribute_value, created_at, updated_at
                ) VALUES (
                    " . (int)$data['serial_item_id'] . ",
                    " . db_escape($data['attribute_name']) . ",
                    " . db_escape($data['attribute_value']) . ",
                    NOW(), NOW()
                )";

        db_query($sql, "Cannot create serial attribute");
        return db_insert_id();
    }

    /**
     * Get attributes by serial item
     *
     * @param int $serialItemId Serial item ID
     * @return array List of attributes
     */
    public function getAttributesBySerial(int $serialItemId): array
    {
        $sql = "SELECT * FROM {$this->prefix}serial_attributes
                WHERE serial_item_id = " . (int)$serialItemId . "
                ORDER BY attribute_name";

        $result = db_query($sql, "Cannot get attributes by serial");
        return db_fetch_all($result);
    }

    /**
     * Update serial attribute
     *
     * @param int $id Attribute ID
     * @param string $value New value
     * @return bool True if updated successfully
     */
    public function updateAttribute(int $id, string $value): bool
    {
        $sql = "UPDATE {$this->prefix}serial_attributes
                SET attribute_value = " . db_escape($value) . ", updated_at = NOW()
                WHERE id = " . (int)$id;

        db_query($sql, "Cannot update serial attribute");
        return db_affected_rows() > 0;
    }

    /**
     * Delete serial attribute
     *
     * @param int $id Attribute ID
     * @return bool True if deleted successfully
     */
    public function deleteAttribute(int $id): bool
    {
        $sql = "DELETE FROM {$this->prefix}serial_attributes WHERE id = " . (int)$id;
        db_query($sql, "Cannot delete serial attribute");
        return db_affected_rows() > 0;
    }

    /**
     * Get serial items by location
     *
     * @param string $location Location code
     * @param string $status Optional status filter
     * @return array List of serial items
     */
    public function getSerialItemsByLocation(string $location, string $status = ''): array
    {
        $sql = "SELECT * FROM {$this->prefix}serial_items
                WHERE location = " . db_escape($location);

        if (!empty($status)) {
            $sql .= " AND status = " . db_escape($status);
        }

        $sql .= " ORDER BY stock_id, serial_no";

        $result = db_query($sql, "Cannot get serial items by location");
        return db_fetch_all($result);
    }

    /**
     * Get serial number statistics
     *
     * @param string $stockId Optional stock ID filter
     * @return array Statistics data
     */
    public function getStatistics(string $stockId = ''): array
    {
        $where = '';
        if (!empty($stockId)) {
            $where = "WHERE stock_id = " . db_escape($stockId);
        }

        $sql = "SELECT
                    status,
                    COUNT(*) as count
                FROM {$this->prefix}serial_items
                $where
                GROUP BY status";

        $result = db_query($sql, "Cannot get serial statistics");
        $stats = db_fetch_all($result);

        // Convert to associative array
        $statistics = [];
        foreach ($stats as $stat) {
            $statistics[$stat['status']] = (int)$stat['count'];
        }

        return $statistics;
    }

    /**
     * Search serial items
     *
     * @param array $criteria Search criteria
     * @param int $limit Optional limit
     * @param int $offset Optional offset
     * @return array Search results
     */
    public function searchSerialItems(array $criteria, int $limit = 50, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($criteria['stock_id'])) {
            $where[] = "stock_id LIKE " . db_escape('%' . $criteria['stock_id'] . '%');
        }

        if (!empty($criteria['serial_no'])) {
            $where[] = "serial_no LIKE " . db_escape('%' . $criteria['serial_no'] . '%');
        }

        if (!empty($criteria['status'])) {
            $where[] = "status = " . db_escape($criteria['status']);
        }

        if (!empty($criteria['location'])) {
            $where[] = "location = " . db_escape($criteria['location']);
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT * FROM {$this->prefix}serial_items
                $whereClause
                ORDER BY stock_id, serial_no
                LIMIT $limit OFFSET $offset";

        $result = db_query($sql, "Cannot search serial items");
        return db_fetch_all($result);
    }
}