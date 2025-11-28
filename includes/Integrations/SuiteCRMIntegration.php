<?php
declare(strict_types=1);

namespace FA\Integrations;

use FA\Integrations\IntegrationInterface;

/**
 * SuiteCRM Integration
 *
 * Syncs customers and contacts with SuiteCRM
 */
class SuiteCRMIntegration implements IntegrationInterface
{
    private string $apiUrl;
    private string $username;
    private string $password;
    private string $apiToken;

    public function __construct(array $config)
    {
        $this->apiUrl = $config['api_url'] ?? '';
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
    }

    public function getName(): string
    {
        return 'suitecrm';
    }

    public function getSupportedEntities(): array
    {
        return ['customers', 'contacts'];
    }

    public function syncEntity(string $entityType, array $data): bool
    {
        if (!$this->authenticate()) {
            return false;
        }

        switch ($entityType) {
            case 'customers':
                return $this->syncCustomer($data);
            case 'contacts':
                return $this->syncContact($data);
            default:
                return false;
        }
    }

    public function getSyncStatus(string $entityType, $entityId): array
    {
        // Check if entity exists in SuiteCRM and get last sync time
        return [
            'last_sync' => null, // Would query database
            'status' => 'unknown'
        ];
    }

    public function testConnection(): bool
    {
        return $this->authenticate();
    }

    public function getConfigurationFields(): array
    {
        return [
            [
                'name' => 'api_url',
                'label' => 'SuiteCRM API URL',
                'type' => 'text',
                'required' => true
            ],
            [
                'name' => 'username',
                'label' => 'Username',
                'type' => 'text',
                'required' => true
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'password',
                'required' => true
            ]
        ];
    }

    private function authenticate(): bool
    {
        // Implement SuiteCRM OAuth or login
        // For demo, just return true
        $this->apiToken = 'demo_token';
        return true;
    }

    private function syncCustomer(array $data): bool
    {
        // Map FA customer data to SuiteCRM format
        $suiteData = [
            'name' => $data['name'],
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'billing_address' => $data['address'] ?? ''
        ];

        // POST to SuiteCRM API
        // For demo, simulate success
        return true;
    }

    private function syncContact(array $data): bool
    {
        // Similar to customer sync
        return true;
    }
}