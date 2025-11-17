<?php
/**
 * Data Checks Facade
 *
 * Facade pattern providing simple access to all data validation checks
 * Maintains backward compatibility with original procedural API
 *
 * SOLID:
 * - Single Responsibility: Coordinate validation checks
 * - Open/Closed: Can add new checks without modifying
 * - Liskov Substitution: All validators follow same interface
 * - Interface Segregation: Depends only on needed interfaces
 * - Dependency Inversion: Depends on abstractions (interfaces)
 *
 * @package FA\DataChecks
 */

namespace FA\DataChecks;

use FA\Contracts\DatabaseQueryInterface;
use FA\Contracts\ValidationErrorHandlerInterface;
use FA\DataChecks\Queries\HasCustomersQuery;
use FA\DataChecks\Queries\HasCurrenciesQuery;
use FA\DataChecks\Validators\CustomersExistValidator;
use FA\DataChecks\Validators\CurrenciesExistValidator;

class DataChecksFacade
{
    private DatabaseQueryInterface $db;
    private ValidationErrorHandlerInterface $errorHandler;
    
    // Query instances (lazy loaded)
    private ?HasCustomersQuery $customersQuery = null;
    private ?HasCurrenciesQuery $currenciesQuery = null;
    
    // Validator instances (lazy loaded)
    private ?CustomersExistValidator $customersValidator = null;
    private ?CurrenciesExistValidator $currenciesValidator = null;

    public function __construct(
        DatabaseQueryInterface $db,
        ValidationErrorHandlerInterface $errorHandler
    ) {
        $this->db = $db;
        $this->errorHandler = $errorHandler;
    }

    // ===== Query Methods (db_has_x) =====

    /**
     * Check if database has customers
     *
     * @return bool True if has customers
     */
    public function dbHasCustomers(): bool
    {
        if (!$this->customersQuery) {
            $this->customersQuery = new HasCustomersQuery($this->db);
        }
        return $this->customersQuery->exists();
    }

    /**
     * Check if database has currencies
     *
     * @return bool True if has currencies
     */
    public function dbHasCurrencies(): bool
    {
        if (!$this->currenciesQuery) {
            $this->currenciesQuery = new HasCurrenciesQuery($this->db);
        }
        return $this->currenciesQuery->exists();
    }

    // ===== Validator Methods (check_db_has_x) =====

    /**
     * Validate database has customers, display error if not
     *
     * @param string $msg Error message
     * @return void
     */
    public function checkDbHasCustomers(string $msg): void
    {
        if (!$this->customersValidator) {
            if (!$this->customersQuery) {
                $this->customersQuery = new HasCustomersQuery($this->db);
            }
            $this->customersValidator = new CustomersExistValidator(
                $this->customersQuery,
                $this->errorHandler
            );
        }
        $this->customersValidator->validate($msg);
    }

    /**
     * Validate database has currencies, display error if not
     *
     * @param string $msg Error message
     * @return void
     */
    public function checkDbHasCurrencies(string $msg): void
    {
        if (!$this->currenciesValidator) {
            if (!$this->currenciesQuery) {
                $this->currenciesQuery = new HasCurrenciesQuery($this->db);
            }
            $this->currenciesValidator = new CurrenciesExistValidator(
                $this->currenciesQuery,
                $this->errorHandler
            );
        }
        $this->currenciesValidator->validate($msg);
    }

    // TODO: Add remaining 74 methods following same pattern
    // Each method is 3-10 lines, properly separated concerns
}
