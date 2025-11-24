<?php
declare(strict_types=1);

namespace Ksfraser\PluginSystem\Database;

use Ksfraser\PluginSystem\Interfaces\PluginDatabaseInterface;

/**
 * FA Database Implementation for Plugin System
 *
 * Implementation of PluginDatabaseInterface using FrontAccounting's database functions.
 * This allows the plugin system to be independent while still working with FA.
 */
class FADatabaseAdapter implements PluginDatabaseInterface
{
    /**
     * Execute a query
     */
    public function query(string $sql, ?string $errorMsg = null)
    {
        if (!function_exists('db_query')) {
            throw new \RuntimeException('Database functions not available');
        }
        return \db_query($sql, $errorMsg);
    }

    /**
     * Fetch associative array from result
     */
    public function fetchAssoc($result): ?array
    {
        if (!function_exists('db_fetch_assoc')) {
            throw new \RuntimeException('Database functions not available');
        }
        return \db_fetch_assoc($result);
    }

    /**
     * Escape a string for SQL
     */
    public function escape(string $value): string
    {
        if (!function_exists('db_escape')) {
            throw new \RuntimeException('Database functions not available');
        }
        return \db_escape($value);
    }

    /**
     * Get table prefix
     */
    public function getTablePrefix(): string
    {
        if (!defined('TB_PREF')) {
            throw new \RuntimeException('Database constants not available');
        }
        return \TB_PREF;
    }

    /**
     * Get last insert ID
     */
    public function insertId(): string
    {
        if (!function_exists('db_insert_id')) {
            throw new \RuntimeException('Database functions not available');
        }
        return \db_insert_id();
    }
}