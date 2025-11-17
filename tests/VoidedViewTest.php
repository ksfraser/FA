<?php

use PHPUnit\Framework\TestCase;
use FA\VoidedView;
use Ksfraser\HTML\Elements\HtmlTable;

/**
 * Unit tests for VoidedView
 *
 * Tests rendering of voided transaction information.
 * Ensures view logic is separated and testable.
 *
 * SOLID Principles:
 * - Single Responsibility: Tests VoidedView only
 * - Open/Closed: Can add more tests without modifying existing
 * - Liskov Substitution: Uses mocks for dependencies
 * - Interface Segregation: Focused test methods
 * - Dependency Inversion: Mocks abstract dependencies
 *
 * TDD: Tests written before implementation, ensuring coverage.
 *
 * UML Class Diagram:
 * +---------------------+
 * | VoidedViewTest     |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + testRenderWith.. |
 * | + testRenderVoided.|
 * +---------------------+
 *           |
 *           | tests
 *           v
 * +---------------------+
 * |   VoidedView       |
 * +---------------------+
 *
 * @package FA
 */
class VoidedViewTest extends TestCase
{
    /**
     * Test render returns null when no voided entry
     */
    /**
     * Test render returns null when no voided entry
     */
    public function testRenderWithNoVoidedEntry()
    {
        $view = new VoidedView();
        $result = $view->render(null, "Voided message");
        $this->assertNull($result);
    }

    /**
     * Test render returns HtmlTable when voided
     */
    public function testRenderReturnsTableWhenVoided()
    {
        $view = new VoidedView();
        $voidEntry = [
            'date_' => '2023-01-01',
            'memo_' => 'Test memo'
        ];
        $result = $view->render($voidEntry, "Voided message");
        $this->assertInstanceOf(HtmlTable::class, $result);
    }
}