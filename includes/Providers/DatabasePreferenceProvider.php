<?php
declare(strict_types=1);

namespace FA\Providers;

use FA\Library\Cache\PreferenceProviderInterface;

/**
 * Database Preference Provider (Example Implementation)
 * 
 * Reads user preferences from a database table.
 * This is a generic example showing how to create providers for different data sources.
 * 
 * Table structure example:
 *   CREATE TABLE user_preferences (
 *     user_id INT,
 *     pref_key VARCHAR(50),
 *     pref_value TEXT,
 *     PRIMARY KEY (user_id, pref_key)
 *   );
 * 
 * This provider can be used in ANY project (not just FA) by providing:
 * - A PDO connection
 * - User ID
 * - Table/column names
 * 
 * Usage:
 *   $pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');
 *   $provider = new DatabasePreferenceProvider($pdo, $userId);
 *   $cache = new PreferenceCache($provider);
 *   $value = $cache->get('theme_color', 'blue');
 * 
 * @package FA\Providers
 */
class DatabasePreferenceProvider implements PreferenceProviderInterface
{
    private \PDO $pdo;
    private int $userId;
    private string $tableName;
    private string $userIdColumn;
    private string $keyColumn;
    private string $valueColumn;
    private ?array $preloadedCache = null;
    
    /**
     * Constructor
     * 
     * @param \PDO $pdo Database connection
     * @param int $userId User ID to load preferences for
     * @param string $tableName Table name (default: 'user_preferences')
     * @param string $userIdColumn User ID column name (default: 'user_id')
     * @param string $keyColumn Key column name (default: 'pref_key')
     * @param string $valueColumn Value column name (default: 'pref_value')
     */
    public function __construct(
        \PDO $pdo,
        int $userId,
        string $tableName = 'user_preferences',
        string $userIdColumn = 'user_id',
        string $keyColumn = 'pref_key',
        string $valueColumn = 'pref_value'
    ) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->tableName = $tableName;
        $this->userIdColumn = $userIdColumn;
        $this->keyColumn = $keyColumn;
        $this->valueColumn = $valueColumn;
    }
    
    /**
     * Get a preference value by key
     * 
     * @param string $key Preference key
     * @param mixed $default Default value if preference not found
     * @return mixed Preference value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Use preloaded cache if available
        if ($this->preloadedCache !== null) {
            return $this->preloadedCache[$key] ?? $default;
        }
        
        // Single query for individual key
        $stmt = $this->pdo->prepare(
            "SELECT {$this->valueColumn} 
             FROM {$this->tableName} 
             WHERE {$this->userIdColumn} = :user_id 
             AND {$this->keyColumn} = :key"
        );
        
        $stmt->execute([
            'user_id' => $this->userId,
            'key' => $key
        ]);
        
        $result = $stmt->fetchColumn();
        
        return $result !== false ? $this->unserialize($result) : $default;
    }
    
    /**
     * Get all preferences at once
     * 
     * Bulk load all preferences in one query for performance.
     * 
     * @return array<string, mixed> All preferences keyed by name
     */
    public function getAll(): array
    {
        if ($this->preloadedCache !== null) {
            return $this->preloadedCache;
        }
        
        $stmt = $this->pdo->prepare(
            "SELECT {$this->keyColumn}, {$this->valueColumn} 
             FROM {$this->tableName} 
             WHERE {$this->userIdColumn} = :user_id"
        );
        
        $stmt->execute(['user_id' => $this->userId]);
        
        $prefs = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $key = $row[$this->keyColumn];
            $value = $row[$this->valueColumn];
            $prefs[$key] = $this->unserialize($value);
        }
        
        $this->preloadedCache = $prefs;
        
        return $prefs;
    }
    
    /**
     * Check if a preference exists
     * 
     * @param string $key Preference key
     * @return bool True if preference exists
     */
    public function has(string $key): bool
    {
        // Use preloaded cache if available
        if ($this->preloadedCache !== null) {
            return array_key_exists($key, $this->preloadedCache);
        }
        
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) 
             FROM {$this->tableName} 
             WHERE {$this->userIdColumn} = :user_id 
             AND {$this->keyColumn} = :key"
        );
        
        $stmt->execute([
            'user_id' => $this->userId,
            'key' => $key
        ]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Unserialize stored value
     * 
     * Attempts JSON decode, falls back to raw value.
     * 
     * @param string $value Serialized value
     * @return mixed Unserialized value
     */
    private function unserialize(string $value): mixed
    {
        // Try JSON decode
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        // Return as-is if not JSON
        return $value;
    }
}
