<?php
/**
 * Data Checks Architecture Test
 *
 * Demonstrates proper SOLID principles:
 * - Each class has Single Responsibility
 * - Queries separated from Validators separated from Error Handling
 * - Easy to test, easy to extend
 *
 * @package FA\Tests
 */

use PHPUnit\Framework\TestCase;
use FA\DataChecks\Queries\HasCustomersQuery;
use FA\DataChecks\Queries\HasCurrenciesQuery;
use FA\DataChecks\Validators\CustomersExistValidator;
use FA\DataChecks\Validators\CurrenciesExistValidator;
use FA\DataChecks\DataChecksFacade;
use FA\Tests\Mocks\MockDatabaseQuery;
use FA\Tests\Mocks\MockValidationErrorHandler;

class DataChecksArchitectureTest extends TestCase
{
    private MockDatabaseQuery $db;
    private MockValidationErrorHandler $errorHandler;

    protected function setUp(): void
    {
        $this->db = new MockDatabaseQuery();
        $this->errorHandler = new MockValidationErrorHandler();
    }

    /**
     * Test: Single Responsibility - Query only checks existence
     */
    public function testQueryOnlyChecksExistence(): void
    {
        $this->db->setQueryResult('debtors_master', true);
        
        $query = new HasCustomersQuery($this->db);
        $result = $query->exists();
        
        $this->assertTrue($result, "Query should return true when customers exist");
        $this->assertFalse($this->errorHandler->hasErrors(), "Query should NOT handle errors");
    }

    /**
     * Test: Single Responsibility - Validator handles errors, not queries
     */
    public function testValidatorHandlesErrors(): void
    {
        $this->db->setQueryResult('debtors_master', false);
        
        $query = new HasCustomersQuery($this->db);
        $validator = new CustomersExistValidator($query, $this->errorHandler);
        
        $validator->validate("No customers found");
        
        $this->assertTrue($this->errorHandler->hasErrors(), "Validator should handle errors");
        $this->assertEquals("No customers found", $this->errorHandler->getLastError());
    }

    /**
     * Test: Dependency Injection - Query doesn't know about error handler
     */
    public function testQueryDoesntKnowAboutErrorHandler(): void
    {
        $this->db->setQueryResult('debtors_master', false);
        
        $query = new HasCustomersQuery($this->db);
        $result = $query->exists();
        
        $this->assertFalse($result);
        $this->assertFalse($this->errorHandler->hasErrors(), "Query must not trigger error handler");
    }

    /**
     * Test: Facade pattern provides convenient API
     */
    public function testFacadeProvidesConvenientAPI(): void
    {
        $this->db->setQueryResult('debtors_master', true);
        
        $facade = new DataChecksFacade($this->db, $this->errorHandler);
        $result = $facade->dbHasCustomers();
        
        $this->assertTrue($result);
        $this->assertFalse($this->errorHandler->hasErrors());
    }

    /**
     * Test: Facade validator method handles errors properly
     */
    public function testFacadeValidatorHandlesErrors(): void
    {
        $this->db->setQueryResult('currencies', false);
        
        $facade = new DataChecksFacade($this->db, $this->errorHandler);
        $facade->checkDbHasCurrencies("No currencies configured");
        
        $this->assertTrue($this->errorHandler->hasErrors());
        $this->assertEquals("No currencies configured", $this->errorHandler->getLastError());
        $this->assertTrue($this->errorHandler->wasExitCalled(), "Should mark exit for fatal errors");
    }

    /**
     * Test: Multiple validators can share same query instance
     */
    public function testValidatorsCanShareQueryInstance(): void
    {
        $this->db->setQueryResult('debtors_master', true);
        
        $query = new HasCustomersQuery($this->db);
        $validator1 = new CustomersExistValidator($query, $this->errorHandler);
        $validator2 = new CustomersExistValidator($query, $this->errorHandler);
        
        $this->assertTrue($query->exists());
        $validator1->validate("Test message");
        $this->assertFalse($this->errorHandler->hasErrors(), "Should not error when exists");
    }

    /**
     * Test: Easy to extend with new checks
     */
    public function testEasyToExtendWithNewChecks(): void
    {
        $this->db->setQueryResult('currencies', true);
        
        $query = new HasCurrenciesQuery($this->db);
        $result = $query->exists();
        
        $this->assertTrue($result);
        // New checks follow same pattern - no need to modify existing code
    }
}
