<?php
/**
 * Mock Exchange Rate Repository
 *
 * Test double for exchange rate data access
 * Implements ExchangeRateRepositoryInterface for testing
 *
 * @package FA\Tests\Mocks
 */

namespace FA\Tests\Mocks;

use FA\Contracts\ExchangeRateRepositoryInterface;

class MockExchangeRateRepository implements ExchangeRateRepositoryInterface
{
    private array $rates = [];
    
    public function setRate(string $currencyCode, string $date, float $rateBuy, ?float $rateSell = null): void
    {
        $key = $currencyCode . '_' . $date;
        $this->rates[$key] = [
            'curr_code' => $currencyCode,
            'date_' => $date,
            'rate_buy' => $rateBuy,
            'rate_sell' => $rateSell ?? $rateBuy
        ];
    }
    
    public function getLastExchangeRate(string $currencyCode, string $date): ?array
    {
        $key = $currencyCode . '_' . $date;
        return $this->rates[$key] ?? null;
    }
    
    public function clear(): void
    {
        $this->rates = [];
    }
}
