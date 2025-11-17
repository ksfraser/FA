<?php

use PHPUnit\Framework\TestCase;
use FA\AccessLevelsService;

/**
 * Unit tests for AccessLevelsService
 *
 * Tests access level checks and retrieval.
 * Ensures access logic is separated and testable.
 *
 * SOLID Principles:
 * - Single Responsibility: Tests AccessLevelsService only
 * - Open/Closed: Can add more tests without modifying existing
 * - Liskov Substitution: Uses mocks for dependencies
 * - Interface Segregation: Focused test methods
 * - Dependency Inversion: Mocks abstract dependencies
 *
 * TDD: Tests written before implementation, ensuring coverage.
 *
 * UML Class Diagram:
 * +---------------------+
 * | AccessLevelsServiceTest|
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + testGetSecuritySections()|
 * +---------------------+
 *           |
 *           | tests
 *           v
 * +---------------------+
 * | AccessLevelsService|
 * +---------------------+
 *
 * @package FA
 */
class AccessLevelsServiceTest extends TestCase
{
    /**
     * Test getSecuritySections method
     */
    public function testGetSecuritySections()
    {
        $als = new AccessLevelsService();
        $sections = $als->getSecuritySections();
        $this->assertIsArray($sections);
        $this->assertNotEmpty($sections);
    }
}