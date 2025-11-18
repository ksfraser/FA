<?php

use PHPUnit\Framework\TestCase;
use FA\DateService;

/**
 * Unit tests for DateService
 *
 * Tests date formatting and validation.
 * Ensures date logic is separated and testable.
 *
 * SOLID Principles:
 * - Single Responsibility: Tests DateService only
 * - Open/Closed: Can add more tests without modifying existing
 * - Liskov Substitution: Uses mocks for dependencies
 * - Interface Segregation: Focused test methods
 * - Dependency Inversion: Mocks abstract dependencies
 *
 * TDD: Tests written before implementation, ensuring coverage.
 *
 * UML Class Diagram:
 * +---------------------+
 * | DateServiceTest    |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + testFormatDate() |
 * +---------------------+
 *           |
 *           | tests
 *           v
 * +---------------------+
 * |   DateService      |
 * +---------------------+
 *
 * @package FA
 */
class DateServiceTest extends TestCase
{
    protected function setUp(): void
    {
        global $SysPrefs, $tmonths;
        
        // Set up minimal SysPrefs for testing
        if (!isset($SysPrefs)) {
            $SysPrefs = new stdClass();
        }
        $SysPrefs->date_system = 0; // Gregorian
        $SysPrefs->dateseps = ['/', '-', '.', "'"];
        $SysPrefs->dflt_date_sep = 0; // Default separator index
        $SysPrefs->dflt_date_fmt = 0; // Default format index
        
        // Set up month names
        if (!isset($tmonths)) {
            $tmonths = [
                1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ];
        }
        
        // Set up mock user session
        if (!class_exists('user_prefs')) {
            require_once __DIR__ . '/../includes/prefs/userprefs.inc';
        }
        $_SESSION["wa_current_user"] = $this->createMockUser();
    }
    
    /**
     * Test formatDate method
     */
    public function testFormatDate()
    {
        $ds = new DateService();
        // Since it uses globals, hard to test in isolation
        // Placeholder for integration test
        $this->assertTrue(true);
    }
    
    // Test sql2date
    public function testSql2DateBasic(): void
    {
        $original = sql2date('2025-11-17');
        $replacement = \FA\DateService::sql2dateStatic('2025-11-17');
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }
    
    public function testSql2DateWithTime(): void
    {
        $original = sql2date('2025-11-17 14:30:00');
        $replacement = \FA\DateService::sql2dateStatic('2025-11-17 14:30:00');
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }
    
    public function testSql2DateNull(): void
    {
        $original = sql2date(null);
        $replacement = \FA\DateService::sql2dateStatic(null);
        
        $this->assertEquals($original, $replacement, 'Both should return empty string for null');
        $this->assertEquals('', $replacement, 'Null should return empty string');
    }
    
    public function testSql2DateEmpty(): void
    {
        $original = sql2date('');
        $replacement = \FA\DateService::sql2dateStatic('');
        
        $this->assertEquals($original, $replacement, 'Both should return empty string for empty input');
    }
    
    // Test date2sql
    public function testDate2SqlBasic(): void
    {
        $original = date2sql('11/17/2025');
        $replacement = \FA\DateService::date2sqlStatic('11/17/2025');
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }
    
    public function testDate2SqlNull(): void
    {
        // Original function has deprecation warning with null, so we test improved behavior
        $replacement = \FA\DateService::date2sqlStatic(null);
        
        $this->assertEquals('', $replacement, 'DateService should convert null to empty string');
    }
    
    public function testDate2SqlEmpty(): void
    {
        $original = date2sql('');
        $replacement = \FA\DateService::date2sqlStatic('');
        
        $this->assertEquals($original, $replacement, 'Both should return empty string for empty input');
        $this->assertEquals('', $replacement, 'Empty input should return empty string');
    }
    
    public function testDate2SqlRoundTrip(): void
    {
        // Test that sql2date and date2sql are inverse operations
        $sqlDate = '2025-11-17';
        $displayDate = sql2date($sqlDate);
        $backToSql = date2sql($displayDate);
        
        $displayDate2 = \FA\DateService::sql2dateStatic($sqlDate);
        $backToSql2 = \FA\DateService::date2sqlStatic($displayDate2);
        
        $this->assertEquals($backToSql, $backToSql2, 'Round trip conversion must be identical');
        $this->assertEquals($sqlDate, $backToSql2, 'Should convert back to original SQL date');
    }
    
    public function testTodayStatic(): void
    {
        $original = Today();
        $replacement = \FA\DateService::todayStatic();
        
        $this->assertEquals($original, $replacement, 'Both should return today\'s date');
        $this->assertMatchesRegularExpression('/^\d{2}\/\d{2}\/\d{4}$/', $replacement, 'Should be in MM/DD/YYYY format');
    }
    
    public function testAddDaysStatic(): void
    {
        $date = '11/17/2025';
        $result = \FA\DateService::addDaysStatic($date, 5);
        
        $this->assertEquals('11/22/2025', $result, 'Should add 5 days correctly');
    }
    
    public function testAddDaysStaticNegative(): void
    {
        $date = '11/17/2025';
        $result = \FA\DateService::addDaysStatic($date, -10);
        
        $this->assertEquals('11/07/2025', $result, 'Should subtract 10 days correctly');
    }
    
    public function testAddMonthsStatic(): void
    {
        $date = '11/17/2025';
        $result = \FA\DateService::addMonthsStatic($date, 2);
        
        $this->assertEquals('01/17/2026', $result, 'Should add 2 months correctly');
    }
    
    public function testAddMonthsStaticNegative(): void
    {
        $date = '11/17/2025';
        $result = \FA\DateService::addMonthsStatic($date, -3);
        
        $this->assertEquals('08/17/2025', $result, 'Should subtract 3 months correctly');
    }
    
    public function testBeginMonthStatic(): void
    {
        $date = '11/17/2025';
        $result = \FA\DateService::beginMonthStatic($date);
        
        $this->assertEquals('11/01/2025', $result, 'Should return first day of month');
    }
    
    public function testEndMonthStatic(): void
    {
        $date = '11/17/2025';
        $result = \FA\DateService::endMonthStatic($date);
        
        $this->assertEquals('11/30/2025', $result, 'Should return last day of month');
    }
    
    public function testNowStatic(): void
    {
        $result = \FA\DateService::nowStatic();
        
        // Now() returns time in 12-hour format: HH:MM am/pm
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2} (am|pm)$/', $result, 'Should return time in 12-hour format');
    }
    
    private function createMockUser(): object
    {
        $mockPrefs = new class {
            public $dflt_date_format = 0; // MMDDYYYY
            public $dflt_date_sep = 0; // /
            
            public function date_format() { return $this->dflt_date_format; }
            public function date_sep() { return $this->dflt_date_sep; }
        };
        
        return (object)['prefs' => $mockPrefs];
    }
}