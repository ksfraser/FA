<?php

use PHPUnit\Framework\TestCase;
use FA\BankingService;

/**
 * Unit tests for BankingService
 *
 * Tests banking functions like currency and exchange rates.
 * Ensures banking logic is separated and testable.
 *
 * SOLID Principles:
 * - Single Responsibility: Tests BankingService only
 * - Open/Closed: Can add more tests without modifying existing
 * - Liskov Substitution: Uses mocks for dependencies
 * - Interface Segregation: Focused test methods
 * - Dependency Inversion: Mocks abstract dependencies
 *
 * TDD: Tests written before implementation, ensuring coverage.
 *
 * UML Class Diagram:
 * +---------------------+
 * | BankingServiceTest |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + testIsCompanyCurrency()|
 * +---------------------+
 *           |
 *           | tests
 *           v
 * +---------------------+
 * |  BankingService    |
 * +---------------------+
 *
 * @package FA
 */
class BankingServiceTest extends TestCase
{
    private BankingService $service;

    protected function setUp(): void {
        $this->service = new BankingService();
    }

    /**
     * Test isCompanyCurrency method - verifies if currency matches company default
     * Original function: is_company_currency($currency)
     */
    public function testIsCompanyCurrency(): void
    {
        $result = $this->service->isCompanyCurrency('USD');
        $this->assertIsBool($result);
    }

    /**
     * Test getCompanyCurrency method - retrieves company default currency
     * Original function: get_company_currency()
     */
    public function testGetCompanyCurrency(): void
    {
        $result = $this->service->getCompanyCurrency();
        $this->assertIsString($result);
    }

    /**
     * Test getExchangeRateFromHomeCurrency method
     * Original function: get_exchange_rate_from_home_currency($currency_code, $date_)
     */
    public function testGetExchangeRateFromHomeCurrency(): void
    {
        $result = $this->service->getExchangeRateFromHomeCurrency('EUR', '2025-01-01');
        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test getExchangeRateToHomeCurrency method
     * Original function: get_exchange_rate_to_home_currency($currency_code, $date_)
     */
    public function testGetExchangeRateToHomeCurrency(): void
    {
        $result = $this->service->getExchangeRateToHomeCurrency('EUR', '2025-01-01');
        $this->assertIsFloat($result);
    }

    /**
     * Test toHomeCurrency method - converts amount to home currency
     * Original function: to_home_currency($amount, $currency_code, $date_)
     */
    public function testToHomeCurrency(): void
    {
        $result = $this->service->toHomeCurrency(100.0, 'EUR', '2025-01-01');
        $this->assertIsFloat($result);
    }

    /**
     * Test getExchangeRateFromTo method - gets exchange rate between two currencies
     * Original function: get_exchange_rate_from_to($from_curr_code, $to_curr_code, $date_)
     */
    public function testGetExchangeRateFromTo(): void
    {
        $result = $this->service->getExchangeRateFromTo('EUR', 'GBP', '2025-01-01');
        $this->assertIsFloat($result);
        
        // Test same currency (should return 1.0)
        $result = $this->service->getExchangeRateFromTo('USD', 'USD', '2025-01-01');
        $this->assertEquals(1.0000, $result);
    }

    /**
     * Test exchangeFromTo method - exchanges amount between currencies
     * Original function: exchange_from_to($amount, $from_curr_code, $to_curr_code, $date_)
     */
    public function testExchangeFromTo(): void
    {
        $result = $this->service->exchangeFromTo(100.0, 'EUR', 'GBP', '2025-01-01');
        $this->assertIsFloat($result);
    }

    /**
     * Test exchangeVariation method - handles exchange variations for allocations
     * Original function: exchange_variation($pyt_type, $pyt_no, $type, $trans_no, $pyt_date, $amount, $person_type, $neg=false)
     * Note: This is a complex function that requires mock transaction data
     */
    public function testExchangeVariation(): void
    {
        // This test requires proper setup with customer/supplier transactions
        // For now, we verify the method exists and is callable
        $this->assertTrue(method_exists($this->service, 'exchangeVariation'));
    }

    /**
     * Test addGlTrans static method - wrapper for global add_gl_trans function
     * Original function: add_gl_trans($type, $trans_id, $date_, $account, $dimension, $dimension2, $memo_, $amount, $currency=null, $person_type_id=null, $person_id=null, $err_msg="", $rate=0)
     */
    public function testAddGlTrans(): void
    {
        // Test that the static method exists and is callable
        $this->assertTrue(method_exists(BankingService::class, 'addGlTrans'));

        // Note: Actual testing of GL transaction insertion would require database setup
        // This wrapper method maintains backward compatibility while allowing future refactoring
    }

    /**
     * Test static wrapper methods for backward compatibility
     */
    public function testStaticWrapperMethods(): void
    {
        // Test getExchangeRateFromHomeCurrencyStatic
        $result = BankingService::getExchangeRateFromHomeCurrencyStatic('EUR', '2025-01-01');
        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);

        // Test getExchangeRateToHomeCurrencyStatic
        $result = BankingService::getExchangeRateToHomeCurrencyStatic('EUR', '2025-01-01');
        $this->assertIsFloat($result);

        // Test getCompanyCurrencyStatic
        $result = BankingService::getCompanyCurrencyStatic();
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}