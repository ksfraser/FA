<?php

namespace FA;

/**
 * Dispatch Model
 *
 * Represents a customer dispatch transaction.
 * Handles data loading and basic calculations.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages dispatch data and calculations
 * - Open/Closed: Can be extended for additional calculations
 * - Liskov Substitution: Implements standard model interface
 * - Interface Segregation: Focused methods
 * - Dependency Inversion: Depends on abstractions (data access functions)
 *
 * UML Class Diagram:
 * +---------------------+
 * |      Dispatch      |
 * +---------------------+
 * | - transId: int     |
 * | - transData: array |
 * | - branchData: array|
 * | - orderData: array |
 * | - lineItems: array |
 * | - taxItems: array  |
 * +---------------------+
 * | + __construct(id)  |
 * | + getSubTotal(): float |
 * +---------------------+
 *
 * @package FA
 */
class Dispatch
{
    public int $transId;
    public array $transData;
    public array $branchData;
    public array $orderData;
    public array $lineItems;
    public array $taxItems;

    /**
     * Constructor
     *
     * @param int $transId Transaction ID
     */
    public function __construct(int $transId)
    {
        $this->transId = $transId;
        $this->loadData();
    }

    /**
     * Load all necessary data for the dispatch
     */
    private function loadData(): void
    {
        $this->transData = get_customer_trans($this->transId, ST_CUSTDELIVERY);
        $this->branchData = get_branch($this->transData["branch_code"]);
        $this->orderData = get_sales_order_header($this->transData["order_"], ST_SALESORDER);
        $this->loadLineItems();
        $this->taxItems = get_trans_tax_details(ST_CUSTDELIVERY, $this->transId);
    }

    /**
     * Load line items
     */
    private function loadLineItems(): void
    {
        $result = get_customer_trans_details(ST_CUSTDELIVERY, $this->transId);
        $this->lineItems = [];
        while ($row = db_fetch($result)) {
            $this->lineItems[] = $row;
        }
    }

    /**
     * Calculate sub total
     *
     * @return float
     */
    public function getSubTotal(): float
    {
        $subTotal = 0;
        foreach ($this->lineItems as $item) {
            if ($item['quantity'] == 0) continue;
            $value = round2(((1 - $item["discount_percent"]) * $item["unit_price"] * $item["quantity"]), user_price_dec());
            $subTotal += $value;
        }
        return $subTotal;
    }
}