<?php

namespace FA;

/**
 * References Service
 *
 * Handles reference generation and management.
 * Refactored from procedural functions to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages references only
 * - Open/Closed: Can be extended for additional reference features
 * - Liskov Substitution: Compatible with reference interfaces
 * - Interface Segregation: Focused reference methods
 * - Dependency Inversion: Depends on abstractions, not globals
 *
 * DRY: Reuses reference logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | ReferencesService  |
 * +---------------------+
 * | - refline_options: array|
 * +---------------------+
 * | + getNextReference(type)|
 * | + isNewReference(ref) |
 * | ...                 |
 * +---------------------+
 *
 * @package FA
 */
class ReferencesService
{
    private array $refline_options = [
        ST_JOURNAL => ['date', 'user'],
        ST_COSTUPDATE => ['date', 'user'],
        ST_BANKPAYMENT => ['date', 'user'],
        ST_BANKDEPOSIT => ['date', 'user'],
        ST_BANKTRANSFER => ['date', 'user'],
        ST_SUPPAYMENT => ['date', 'user'],
        ST_CUSTPAYMENT => ['date', 'user'],
        ST_SALESORDER => ['date', 'customer', 'branch', 'user', 'pos'],
        ST_SALESQUOTE => ['date', 'customer', 'branch', 'user', 'pos'],
        ST_SALESINVOICE => ['date', 'customer', 'branch', 'user', 'pos'],
        ST_CUSTCREDIT => ['date', 'customer', 'branch', 'user', 'pos'],
        ST_CUSTDELIVERY => ['date', 'customer', 'branch', 'user', 'pos'],
        ST_LOCTRANSFER => ['date', 'location', 'user'],
        ST_INVADJUST => ['date', 'location', 'user'],
        ST_PURCHORDER => ['date', 'location', 'supplier', 'user'],
        ST_SUPPINVOICE => ['date', 'location', 'supplier', 'user'],
        ST_SUPPCREDIT => ['date', 'location', 'supplier', 'user'],
        ST_SUPPRECEIVE => ['date', 'location', 'supplier', 'user'],
    ];

    /**
     * Get next reference for transaction type
     *
     * @param int $type Transaction type
     * @return string Next reference
     */
    public function getNextReference(int $type): string
    {
        return get_next_reference($type);
    }

    /**
     * Check if reference is new
     *
     * @param string $ref Reference
     * @return bool True if new
     */
    public function isNewReference(string $ref): bool
    {
        return is_new_reference($ref);
    }

    // Add more methods as needed
}