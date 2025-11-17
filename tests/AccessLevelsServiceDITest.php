<?php

namespace FA\Tests;

use PHPUnit\Framework\TestCase;
use FA\AccessLevelsService;
use FA\Tests\Mocks\MockSecurityRepository;

/**
 * Access Levels Service Test with Dependency Injection
 *
 * Tests AccessLevelsService with mocked dependencies for full testability.
 *
 * @package FA\Tests
 */
class AccessLevelsServiceDITest extends TestCase
{
    private AccessLevelsService $service;
    private MockSecurityRepository $securityRepo;

    protected function setUp(): void
    {
        $this->securityRepo = new MockSecurityRepository();
        $this->service = new AccessLevelsService($this->securityRepo);
    }

    /**
     * @test
     */
    public function testGetSecuritySections(): void
    {
        $sections = $this->service->getSecuritySections();
        $this->assertIsArray($sections);
        $this->assertNotEmpty($sections);
    }

    /**
     * @test
     */
    public function testGetSecurityAreas(): void
    {
        $areas = $this->service->getSecurityAreas();
        $this->assertIsArray($areas);
    }

    /**
     * @test
     */
    public function testUserRoleManagement(): void
    {
        // Set up user roles
        $this->securityRepo->setUserRoles(1, [1, 2, 3]);
        $this->securityRepo->setUserRoles(2, [2, 3]);

        // Verify roles
        $this->assertEquals([1, 2, 3], $this->securityRepo->getUserRoles(1));
        $this->assertEquals([2, 3], $this->securityRepo->getUserRoles(2));
        $this->assertEquals([], $this->securityRepo->getUserRoles(999));
    }

    /**
     * @test
     */
    public function testAreaAccessControl(): void
    {
        // Set up area access
        $this->securityRepo->setAreaAccess('SA_SALES', 1, 2);  // User 1: read/write
        $this->securityRepo->setAreaAccess('SA_SALES', 2, 1);  // User 2: read only

        // Verify access levels
        $this->assertEquals(2, $this->securityRepo->getAreaAccess('SA_SALES', 1));
        $this->assertEquals(1, $this->securityRepo->getAreaAccess('SA_SALES', 2));
        $this->assertEquals(0, $this->securityRepo->getAreaAccess('SA_SALES', 999));
    }

    /**
     * @test
     */
    public function testTransactionEditAccess(): void
    {
        // Set transaction creator
        $this->securityRepo->setTransactionCreator(10, 123, 5);

        // User who created it has access
        $this->assertTrue($this->securityRepo->hasEditAccess(10, 123, 5));

        // Other users don't have access
        $this->assertFalse($this->securityRepo->hasEditAccess(10, 123, 6));

        // Non-existent transaction
        $this->assertFalse($this->securityRepo->hasEditAccess(10, 999, 5));
    }

    /**
     * @test
     */
    public function testGetTransactionCreator(): void
    {
        // Set up creators
        $this->securityRepo->setTransactionCreator(20, 456, 10);

        // Verify creator retrieval
        $this->assertEquals(10, $this->securityRepo->getTransactionCreator(20, 456));
        $this->assertNull($this->securityRepo->getTransactionCreator(20, 999));
        $this->assertNull($this->securityRepo->getTransactionCreator(999, 456));
    }

    /**
     * @test
     */
    public function testServiceCanBeCreatedWithoutDependencies(): void
    {
        // Should use production implementation by default
        $service = new AccessLevelsService();
        $this->assertInstanceOf(AccessLevelsService::class, $service);
    }

    /**
     * @test
     */
    public function testDependencyInjectionAllowsFullTestability(): void
    {
        // Create service with custom mock
        $customRepo = new MockSecurityRepository();
        $customRepo->setUserRoles(100, [10, 20, 30]);
        $customRepo->setAreaAccess('CUSTOM_AREA', 100, 3);

        $customService = new AccessLevelsService($customRepo);

        // Verify custom data
        $this->assertEquals([10, 20, 30], $customRepo->getUserRoles(100));
        $this->assertEquals(3, $customRepo->getAreaAccess('CUSTOM_AREA', 100));
    }

    /**
     * @test
     */
    public function testMultipleUsersAndTransactions(): void
    {
        // Set up complex scenario
        $this->securityRepo->setUserRoles(1, [1, 2]);
        $this->securityRepo->setUserRoles(2, [2, 3]);
        $this->securityRepo->setUserRoles(3, [3, 4]);

        $this->securityRepo->setTransactionCreator(10, 1, 1);
        $this->securityRepo->setTransactionCreator(10, 2, 2);
        $this->securityRepo->setTransactionCreator(20, 1, 1);

        // Verify access patterns
        $this->assertTrue($this->securityRepo->hasEditAccess(10, 1, 1));
        $this->assertFalse($this->securityRepo->hasEditAccess(10, 1, 2));
        $this->assertTrue($this->securityRepo->hasEditAccess(10, 2, 2));
        $this->assertTrue($this->securityRepo->hasEditAccess(20, 1, 1));
    }

    /**
     * @test
     */
    public function testAccessLevelsAreIsolatedByArea(): void
    {
        // Different areas have different access
        $this->securityRepo->setAreaAccess('AREA_A', 1, 3);
        $this->securityRepo->setAreaAccess('AREA_B', 1, 1);

        $this->assertEquals(3, $this->securityRepo->getAreaAccess('AREA_A', 1));
        $this->assertEquals(1, $this->securityRepo->getAreaAccess('AREA_B', 1));
        $this->assertNotEquals(
            $this->securityRepo->getAreaAccess('AREA_A', 1),
            $this->securityRepo->getAreaAccess('AREA_B', 1)
        );
    }
}
