<?php
declare(strict_types=1);

namespace FA\Integrations;

use FA\Integrations\IntegrationInterface;

/**
 * Integration Manager
 *
 * Manages external system integrations
 */
class IntegrationManager
{
    private static ?IntegrationManager $instance = null;
    private array $integrations = [];

    /**
     * Get singleton instance
     */
    public static function getInstance(): IntegrationManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register an integration
     */
    public function registerIntegration(IntegrationInterface $integration): void
    {
        $this->integrations[$integration->getName()] = $integration;
    }

    /**
     * Get registered integrations
     */
    public function getIntegrations(): array
    {
        return $this->integrations;
    }

    /**
     * Get integration by name
     */
    public function getIntegration(string $name): ?IntegrationInterface
    {
        return $this->integrations[$name] ?? null;
    }

    /**
     * Sync entity across all registered integrations
     */
    public function syncEntity(string $entityType, $entityId, array $data): array
    {
        $results = [];

        foreach ($this->integrations as $name => $integration) {
            if (in_array($entityType, $integration->getSupportedEntities())) {
                try {
                    $success = $integration->syncEntity($entityType, $data);
                    $results[$name] = [
                        'success' => $success,
                        'status' => $integration->getSyncStatus($entityType, $entityId)
                    ];
                } catch (\Exception $e) {
                    $results[$name] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Test all integrations
     */
    public function testAllIntegrations(): array
    {
        $results = [];

        foreach ($this->integrations as $name => $integration) {
            $results[$name] = $integration->testConnection();
        }

        return $results;
    }
}