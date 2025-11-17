<?php
/**
 * Exchange Rate Repository Interface
 *
 * Abstraction for exchange rate data access
 * Follows Repository pattern for data persistence abstraction
 *
 * @package FA\Contracts
 */

namespace FA\Contracts;

interface ExchangeRateRepositoryInterface
{
    /**
     * Get the last exchange rate for a currency on or before a date
     *
     * @param string $currencyCode Currency code
     * @param string $date Date
     * @return array|null Exchange rate data or null if not found
     */
    public function getLastExchangeRate(string $currencyCode, string $date): ?array;
}
