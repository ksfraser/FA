<?php
// Define constants for testing
if (!defined('ST_JOURNAL')) define('ST_JOURNAL', 0);

/**
 * Unit tests for AppEntriesService
 *
 * SOLID Principles:
 * - Single Responsibility: Tests AppEntriesService only
 * - Open/Closed: Can add new tests without modifying existing
 * - Liskov Substitution: Compatible with PHPUnit test framework
 * - Interface Segregation: Focused test methods
 * - Dependency Inversion: Depends on abstractions (PHPUnit)
 *
 * DRY: Reuses test setup and assertions
 * TDD: Written with TDD in mind, testing behavior
 *
 * UML Class Diagram:
 * +---------------------+
 * | AppEntriesServiceTest |
 * +---------------------+
 * |                      |
 * +---------------------+
 * | + testGetEditorUrl() |
 * | + testHasEditor()    |
 * | + testGetAllEditors()|
 * +---------------------+
 *           |
 *           | extends
 *           v
 * +---------------------+
 * |   PHPUnit\TestCase |
 * +---------------------+
 *
 * @package FA
 */

use PHPUnit\Framework\TestCase;
use FA\AppEntriesService;

class AppEntriesServiceTest extends TestCase {

    private AppEntriesService $service;

    protected function setUp(): void {
        require_once __DIR__ . '/../includes/types.inc';
        $this->service = new AppEntriesService();
    }

    public function testGetEditorUrl(): void {
        // Test existing editor
        $url = $this->service->getEditorUrl(ST_JOURNAL, 1);
        $this->assertTrue(strpos($url, '/gl/gl_journal.php') !== false);
        $this->assertTrue(strpos($url, 'trans_no=1') !== false);
    }

    public function testGetEditorUrlNonExistent(): void {
        // Test non-existent editor
        $url = $this->service->getEditorUrl(999, 1);
        $this->assertNull($url);
    }

    public function testHasEditor(): void {
        // Test existing editor
        $this->assertTrue($this->service->hasEditor(ST_JOURNAL));
        // Test non-existent
        $this->assertFalse($this->service->hasEditor(999));
    }

    public function testGetAllEditors(): void {
        // Test get all
        $editors = $this->service->getAllEditors();
        $this->assertIsArray($editors);
        $this->assertArrayHasKey(ST_JOURNAL, $editors);
    }
}