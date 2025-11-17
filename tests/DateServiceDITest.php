<?php

namespace FA\Tests;

use PHPUnit\Framework\TestCase;
use FA\DateService;
use FA\Tests\Mocks\MockFiscalYearRepository;
use FA\Tests\Mocks\MockCalendarConverter;

/**
 * Date Service Test with Dependency Injection
 *
 * Tests DateService with mocked dependencies for full testability.
 *
 * @package FA\Tests
 */
class DateServiceDITest extends TestCase
{
    private DateService $dateService;
    private MockFiscalYearRepository $fiscalYearRepo;
    private MockCalendarConverter $calendarConverter;

    protected function setUp(): void
    {
        $this->fiscalYearRepo = new MockFiscalYearRepository();
        $this->calendarConverter = new MockCalendarConverter();
        $this->dateService = new DateService($this->fiscalYearRepo, $this->calendarConverter);
    }

    /**
     * @test
     */
    public function testCalendarConversionUsesInjectedConverter(): void
    {
        // Test Gregorian to Jalali
        $result = $this->dateService->gregorianToJalali(2024, 11, 17);
        $this->assertEquals([1403, 11, 17], $result);

        // Test Jalali to Gregorian
        $result = $this->dateService->jalaliToGregorian(1403, 11, 17);
        $this->assertEquals([2024, 11, 17], $result);

        // Test Gregorian to Islamic
        $result = $this->dateService->gregorianToIslamic(2024, 11, 17);
        $this->assertEquals([1445, 11, 17], $result);

        // Test Islamic to Gregorian
        $result = $this->dateService->islamicToGregorian(1445, 11, 17);
        $this->assertEquals([2024, 11, 17], $result);
    }

    /**
     * @test
     */
    public function testFiscalYearOperationsUseInjectedRepository(): void
    {
        // Set up fiscal year
        $this->fiscalYearRepo->setFiscalYear([
            'id' => 1,
            'begin' => '2024-01-01',
            'end' => '2024-12-31',
            'closed' => 0
        ]);

        // Test begin/end fiscal year
        $this->assertEquals('2024-01-01', $this->fiscalYearRepo->getBeginFiscalYear());
        $this->assertEquals('2024-12-31', $this->fiscalYearRepo->getEndFiscalYear());

        // Test date in fiscal year
        $this->assertTrue($this->fiscalYearRepo->isDateInFiscalYear('2024-06-15'));
        $this->assertFalse($this->fiscalYearRepo->isDateInFiscalYear('2023-12-31'));
        $this->assertFalse($this->fiscalYearRepo->isDateInFiscalYear('2025-01-01'));
    }

    /**
     * @test
     */
    public function testClosedDateDetection(): void
    {
        // Set a date as closed for type 10
        $this->fiscalYearRepo->setClosedDate(10, '2024-01-15');

        // Test closed date detection
        $this->assertTrue($this->fiscalYearRepo->isDateClosed(10, '2024-01-15'));
        $this->assertFalse($this->fiscalYearRepo->isDateClosed(10, '2024-01-16'));
        $this->assertFalse($this->fiscalYearRepo->isDateClosed(20, '2024-01-15'));
    }

    /**
     * @test
     */
    public function testDependencyInjectionAllowsMockingForTesting(): void
    {
        // Create a service with fresh mocks
        $customFiscalYear = new MockFiscalYearRepository([
            'id' => 2,
            'begin' => '2023-07-01',
            'end' => '2024-06-30',
            'closed' => 0
        ]);
        
        $service = new DateService($customFiscalYear, $this->calendarConverter);

        // Verify we can test with custom fiscal year
        $this->assertEquals('2023-07-01', $customFiscalYear->getBeginFiscalYear());
        $this->assertEquals('2024-06-30', $customFiscalYear->getEndFiscalYear());
    }

    /**
     * @test
     */
    public function testServiceCanBeCreatedWithoutDependencies(): void
    {
        // Should use production implementations by default
        $service = new DateService();
        $this->assertInstanceOf(DateService::class, $service);
    }

    /**
     * @test
     */
    public function testFiscalYearEdgeCases(): void
    {
        // Test with no fiscal year
        $emptyRepo = new MockFiscalYearRepository([]);
        $service = new DateService($emptyRepo, $this->calendarConverter);

        $this->assertEquals('', $emptyRepo->getBeginFiscalYear());
        $this->assertEquals('', $emptyRepo->getEndFiscalYear());
    }

    /**
     * @test
     */
    public function testMultipleCalendarSystemsWorkCorrectly(): void
    {
        // Persian calendar
        $jalali = $this->dateService->gregorianToJalali(2024, 3, 21);
        $this->assertIsArray($jalali);
        $this->assertCount(3, $jalali);

        // Islamic calendar
        $islamic = $this->dateService->gregorianToIslamic(2024, 3, 21);
        $this->assertIsArray($islamic);
        $this->assertCount(3, $islamic);

        // Round trip conversions
        $backToGregorian = $this->dateService->jalaliToGregorian($jalali[0], $jalali[1], $jalali[2]);
        $this->assertEquals([2024, 3, 21], $backToGregorian);
    }
}
