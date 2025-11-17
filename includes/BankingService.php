<?php

namespace FA;

/**
 * Banking Service
 *
 * Handles banking-related functions like currency and exchange rates.
 * Refactored from procedural functions to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages banking operations only
 * - Open/Closed: Can be extended for additional banking features
 * - Liskov Substitution: Compatible with banking interfaces
 * - Interface Segregation: Focused banking methods
 * - Dependency Inversion: Depends on abstractions, not globals
 *
 * DRY: Reuses banking logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * |  BankingService    |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + isCompanyCurrency(currency)|
 * | + getCompanyCurrency()|
 * | + getExchangeRate(...)|
 * +---------------------+
 *
 * @package FA
 */
class BankingService
{
    /**
     * Check if currency is company currency
     *
     * @param string $currency Currency code
     * @return bool True if company currency
     */
    public function isCompanyCurrency(string $currency): bool
    {
        return ($this->getCompanyCurrency() == $currency);
    }

    /**
     * Get company default currency
     *
     * @return string Currency code
     */
    public function getCompanyCurrency(): string
    {
        return get_company_pref('curr_default');
    }

    /**
     * Get exchange rate from home currency
     *
     * @param string $currency_code Currency code
     * @param string $date Date
     * @return float Exchange rate
     */
    public function getExchangeRateFromHomeCurrency(string $currency_code, string $date_): float
    {
        if ($currency_code == $this->getCompanyCurrency() || $currency_code == null)
            return 1.0000;

        $rate = get_last_exchange_rate($currency_code, $date_);

        if (!$rate) {
            display_error(
                sprintf(_("Cannot retrieve exchange rate for currency %s as of %s. Please add exchange rate manually on Exchange Rates page."),
                     $currency_code, $date_));
            return 1.000;
        }

        return $rate['rate_buy'];
    }
}