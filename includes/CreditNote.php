<?php

namespace FA;

class CreditNote
{
    public int $transId;
    public array $transData;
    public array $branchData;
    public array $lineItems;
    public array $taxItems;
    public bool $isVoided;
    public float $subTotal;

    public function __construct(int $transId)
    {
        $this->transId = $transId;
        $this->loadData();
    }

    protected function loadData()
    {
        $this->transData = $this->getCustomerTrans($this->transId, \ST_CUSTCREDIT);
        $this->branchData = $this->getBranch($this->transData['branch_code']);
        $this->lineItems = $this->getCustomerTransDetails(\ST_CUSTCREDIT, $this->transId);
        $this->taxItems = $this->getTransTaxDetails(\ST_CUSTCREDIT, $this->transId);
        $this->isVoided = $this->isVoided(\ST_CUSTCREDIT, $this->transId);
        $this->calculateSubTotal();
    }

    protected function getCustomerTrans(int $transId, int $type): array
    {
        return get_customer_trans($transId, $type);
    }

    protected function getBranch(int $branchCode): array
    {
        return get_branch($branchCode);
    }

    protected function getCustomerTransDetails(int $type, int $transId): array
    {
        return get_customer_trans_details($type, $transId);
    }

    protected function getTransTaxDetails(int $type, int $transId): array
    {
        return get_trans_tax_details($type, $transId);
    }

    protected function isVoided(int $type, int $transId): bool
    {
        return is_voided_display($type, $transId, "");
    }

    protected function calculateSubTotal()
    {
        $this->subTotal = 0;
        foreach ($this->lineItems as $item) {
            $this->subTotal += $item['quantity'] * $item['unit_price'] * (1 - $item['discount_percent']);
        }
    }

    public function getSubTotal(): float
    {
        return $this->subTotal;
    }
}