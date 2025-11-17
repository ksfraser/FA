<?php

namespace FA\Tests\Mocks;

use FA\Interfaces\SecurityRepositoryInterface;

/**
 * Mock Security Repository for Testing
 *
 * @package FA\Tests\Mocks
 */
class MockSecurityRepository implements SecurityRepositoryInterface
{
    private array $userRoles = [];
    private array $areaAccess = [];
    private array $transactionCreators = [];

    public function setUserRoles(int $userId, array $roles): void
    {
        $this->userRoles[$userId] = $roles;
    }

    public function setAreaAccess(string $area, int $userId, int $access): void
    {
        $this->areaAccess[$area][$userId] = $access;
    }

    public function setTransactionCreator(int $type, int $transNo, int $userId): void
    {
        $this->transactionCreators[$type][$transNo] = $userId;
    }

    public function getUserRoles(int $userId): array
    {
        return $this->userRoles[$userId] ?? [];
    }

    public function getAreaAccess(string $area, int $userId): int
    {
        return $this->areaAccess[$area][$userId] ?? 0;
    }

    public function hasEditAccess(int $type, int $transNo, int $userId): bool
    {
        $creator = $this->getTransactionCreator($type, $transNo);
        return $creator === $userId;
    }

    public function getTransactionCreator(int $type, int $transNo): ?int
    {
        return $this->transactionCreators[$type][$transNo] ?? null;
    }
}
