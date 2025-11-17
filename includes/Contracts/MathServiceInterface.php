<?php
/**
 * Math Service Interface
 *
 * Provides mathematical operations with proper precision handling
 * for financial calculations.
 *
 * @package FA\Contracts
 */

namespace FA\Contracts;

interface MathServiceInterface
{
    /**
     * Round a value to specified decimal places
     *
     * @param float $value Value to round
     * @param int $decimals Number of decimal places
     * @return float Rounded value
     */
    public function round2(float $value, int $decimals): float;

    /**
     * Get user's price decimal places setting
     *
     * @return int Number of decimal places for prices
     */
    public function userPriceDecimals(): int;
}
