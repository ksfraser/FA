<?php

namespace FA\Interfaces;

/**
 * Security Repository Interface
 *
 * Abstracts access to security and access control data for dependency injection.
 *
 * @package FA\Interfaces
 */
interface SecurityRepositoryInterface
{
    /**
     * Get user roles
     *
     * @param int $userId User ID
     * @return array Array of role IDs
     */
    public function getUserRoles(int $userId): array;

    /**
     * Get area access level for user
     *
     * @param string $area Security area
     * @param int $userId User ID
     * @return int Access level
     */
    public function getAreaAccess(string $area, int $userId): int;

    /**
     * Check if user has edit access to transaction
     *
     * @param int $type Transaction type
     * @param int $transNo Transaction number
     * @param int $userId User ID
     * @return bool True if has access
     */
    public function hasEditAccess(int $type, int $transNo, int $userId): bool;

    /**
     * Get transaction creator user
     *
     * @param int $type Transaction type
     * @param int $transNo Transaction number
     * @return int|null User ID or null
     */
    public function getTransactionCreator(int $type, int $transNo): ?int;
}
