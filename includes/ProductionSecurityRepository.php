<?php

namespace FA;

use FA\Interfaces\SecurityRepositoryInterface;

/**
 * Production Security Repository
 *
 * Real implementation that accesses the database for security data.
 *
 * @package FA
 */
class ProductionSecurityRepository implements SecurityRepositoryInterface
{
    /**
     * Get user roles
     *
     * @param int $userId User ID
     * @return array Array of role IDs
     */
    public function getUserRoles(int $userId): array
    {
        $sql = "SELECT role_id FROM " . TB_PREF . "user_roles WHERE user_id=" . \db_escape($userId);
        $result = \db_query($sql);
        $roles = [];
        while ($row = \db_fetch($result)) {
            $roles[] = (int)$row['role_id'];
        }
        return $roles;
    }

    /**
     * Get area access level for user
     *
     * @param string $area Security area
     * @param int $userId User ID
     * @return int Access level
     */
    public function getAreaAccess(string $area, int $userId): int
    {
        $roles = $this->getUserRoles($userId);
        $sql = "SELECT MAX(access) as access FROM " . TB_PREF . "security_roles 
                WHERE role_id IN (" . \implode(',', $roles) . ") 
                AND area=" . \db_escape($area);
        $result = \db_query($sql);
        $row = \db_fetch($result);
        return $row ? (int)$row['access'] : 0;
    }

    /**
     * Check if user has edit access to transaction
     *
     * @param int $type Transaction type
     * @param int $transNo Transaction number
     * @param int $userId User ID
     * @return bool True if has access
     */
    public function hasEditAccess(int $type, int $transNo, int $userId): bool
    {
        $creator = $this->getTransactionCreator($type, $transNo);
        if ($creator === null) {
            return false;
        }
        
        // User can edit own transactions or if admin
        return $creator === $userId || \user_is_admin($userId);
    }

    /**
     * Get transaction creator user
     *
     * @param int $type Transaction type
     * @param int $transNo Transaction number
     * @return int|null User ID or null
     */
    public function getTransactionCreator(int $type, int $transNo): ?int
    {
        $sql = "SELECT user_id FROM " . TB_PREF . "trans_audit_trail 
                WHERE type=" . \db_escape($type) . " 
                AND trans_no=" . \db_escape($transNo) . " 
                ORDER BY stamp ASC LIMIT 1";
        $result = \db_query($sql);
        $row = \db_fetch($result);
        return $row ? (int)$row['user_id'] : null;
    }
}
