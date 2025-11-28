<?php
declare(strict_types=1);

namespace FA\Integrations;

/**
 * Integration Interface
 *
 * Defines the contract for external system integrations
 */
interface IntegrationInterface
{
    /**
     * Get the integration name
     */
    public function getName(): string;

    /**
     * Get supported entity types
     */
    public function getSupportedEntities(): array;

    /**
     * Sync an entity to the external system
     */
    public function syncEntity(string $entityType, array $data): bool;

    /**
     * Get sync status for an entity
     */
    public function getSyncStatus(string $entityType, $entityId): array;

    /**
     * Test connection to external system
     */
    public function testConnection(): bool;

    /**
     * Get integration configuration fields
     */
    public function getConfigurationFields(): array;
}