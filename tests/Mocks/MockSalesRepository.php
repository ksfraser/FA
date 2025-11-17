<?php

namespace FA\Tests\Mocks;

use FA\Interfaces\SalesRepositoryInterface;

/**
 * Mock Sales Repository for Testing
 *
 * @package FA\Tests\Mocks
 */
class MockSalesRepository implements SalesRepositoryInterface
{
    private array $prices = [];
    private array $customers = [];
    private array $transactions = [];
    private array $orderLines = [];

    public function setPrice(string $stockId, string $currency, string $salesType, float $price): void
    {
        $this->prices[$stockId][$currency][$salesType] = $price;
    }

    public function addCustomer(array $customer): void
    {
        $this->customers[$customer['debtor_no']] = $customer;
    }

    public function addTransaction(int $type, int $transNo, array $transaction): void
    {
        $this->transactions[$type][$transNo] = $transaction;
    }

    public function addOrderLine(int $orderId, array $line): void
    {
        $this->orderLines[$orderId][] = $line;
    }

    public function getPrice(
        string $stockId,
        string $currency,
        string $salesType,
        float $factor = 1.0,
        ?string $date = null
    ): ?float {
        $price = $this->prices[$stockId][$currency][$salesType] ?? null;
        return $price !== null ? $price * $factor : null;
    }

    public function getCustomer(int $customerId): ?array
    {
        return $this->customers[$customerId] ?? null;
    }

    public function getSalesTransaction(int $type, int $transNo): ?array
    {
        return $this->transactions[$type][$transNo] ?? null;
    }

    public function getSalesOrderLines(int $orderId): array
    {
        return $this->orderLines[$orderId] ?? [];
    }
}
