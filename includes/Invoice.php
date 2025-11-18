<?php

namespace FA;

/**
 * Model for invoice data
 *
 * Encapsulates business logic for invoice transactions.
 * Loads and provides access to invoice data.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages invoice data only
 * - Open/Closed: Can be extended for additional invoice types
 * - Liskov Substitution: Compatible with other transaction models
 * - Interface Segregation: Minimal, focused interface
 * - Dependency Inversion: Depends on abstractions, not concretions
 *
 * DRY: Reuses common transaction loading logic
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * |     Invoice        |
 * +---------------------+
 * | - transId: int     |
 * | - transData: array |
 * | - branchData: array|
 * | - orderData: array |
 * | - lineItems: array |
 * | - taxItems: array  |
 * +---------------------+
 * | + __construct(id)  |
 * | + getSubTotal():float|
 * +---------------------+
 *
 * @package FA
 */
class Invoice
{
    public int $transId;
    public array $transData;
    public array $branchData;
    public array $orderData;
    public array $lineItems;
    public array $taxItems;

    /**
     * Constructor loads invoice data
     *
     * @param int $transId Transaction ID
     */
    public function __construct(int $transId)
    {
        $this->transId = $transId;
        $this->loadData();
    }

    /**
     * Load invoice data from database
     */
    private function loadData(): void
    {
        $this->transData = get_customer_trans($this->transId, ST_SALESINVOICE);
        $this->branchData = get_branch($this->transData["branch_code"]);
        $this->orderData = get_sales_order_header($this->transData["order_"], ST_SALESORDER);
        $this->lineItems = get_customer_trans_details(ST_SALESINVOICE, $this->transId);
        $this->taxItems = get_trans_tax_details(ST_SALESINVOICE, $this->transId);
    }

    /**
     * Get subtotal of line items
     *
     * @return float Subtotal amount
     */
    public function getSubTotal(): float
    {
        $subTotal = 0.0;
        foreach ($this->lineItems as $item) {
            if ($item["quantity"] == 0) continue;
            $value = round2(((1 - $item["discount_percent"]) * $item["unit_price"] * $item["quantity"]), \FA\UserPrefsCache::getPriceDecimals());
            $subTotal += $value;
        }
        return $subTotal;
    }
}