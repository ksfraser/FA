<?php

namespace FA\Tests\Mocks;

use FA\Interfaces\PurchasingRepositoryInterface;

/**
 * Mock Purchasing Repository for Testing
 *
 * @package FA\Tests\Mocks
 */
class MockPurchasingRepository implements PurchasingRepositoryInterface
{
    private array $prices = [];
    private array $suppliers = [];
    private array $purchaseOrders = [];
    private array $orderLines = [];
    private array $purchaseData = [];

    public function setPrice(string $stockId, string $currency, int $supplierId, float $price): void
    {
        $this->prices[$stockId][$currency][$supplierId] = $price;
    }

    public function addSupplier(array $supplier): void
    {
        $this->suppliers[$supplier['supplier_id']] = $supplier;
    }

    public function addPurchaseOrder(array $order): void
    {
        $this->purchaseOrders[$order['order_no']] = $order;
    }

    public function addOrderLine(int $orderId, array $line): void
    {
        $this->orderLines[$orderId][] = $line;
    }

    public function setPurchaseData(string $stockId, int $supplierId, array $data): void
    {
        $this->purchaseData[$stockId][$supplierId] = $data;
    }

    public function getPurchasePrice(string $stockId, string $currency, int $supplierId): ?float
    {
        return $this->prices[$stockId][$currency][$supplierId] ?? null;
    }

    public function getSupplier(int $supplierId): ?array
    {
        return $this->suppliers[$supplierId] ?? null;
    }

    public function getPurchaseOrder(int $orderId): ?array
    {
        return $this->purchaseOrders[$orderId] ?? null;
    }

    public function getPurchaseOrderLines(int $orderId): array
    {
        return $this->orderLines[$orderId] ?? [];
    }

    public function getPurchaseData(string $stockId, int $supplierId): ?array
    {
        return $this->purchaseData[$stockId][$supplierId] ?? null;
    }
}
