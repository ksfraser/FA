<?php
/**
 * Mock Math Service
 *
 * Test double for MathServiceInterface
 *
 * @package FA\Tests\Mocks
 */

namespace FA\Tests\Mocks;

use FA\Contracts\MathServiceInterface;

class MockMathService implements MathServiceInterface
{
    private int $priceDecimals = 2;

    /**
     * Round a value to specified decimal places
     *
     * @param float $value Value to round
     * @param int $decimals Number of decimal places
     * @return float Rounded value
     */
    public function round2(float $value, int $decimals): float
    {
        return round($value, $decimals);
    }

    /**
     * Get user's price decimal places setting
     *
     * @return int Number of decimal places for prices
     */
    public function userPriceDecimals(): int
    {
        return $this->priceDecimals;
    }

    /**
     * Set price decimal places for testing
     *
     * @param int $decimals Number of decimal places
     */
    public function setPriceDecimals(int $decimals): void
    {
        $this->priceDecimals = $decimals;
    }
}
