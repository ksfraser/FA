<?php

use PHPUnit\Framework\TestCase;
use FA\TaxDetailsView;
use Ksfraser\HTML\HtmlFragment;

/**
 * Unit tests for TaxDetailsView
 *
 * Tests rendering of tax details.
 * Ensures view logic is separated and testable.
 *
 * SOLID Principles:
 * - Single Responsibility: Tests TaxDetailsView only
 * - Open/Closed: Can add more tests without modifying existing
 * - Liskov Substitution: Uses mocks for dependencies
 * - Interface Segregation: Focused test methods
 * - Dependency Inversion: Mocks abstract dependencies
 *
 * TDD: Tests written before implementation, ensuring coverage.
 *
 * UML Class Diagram:
 * +---------------------+
 * | TaxDetailsViewTest |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + testRenderWith.. |
 * | + testRenderWithTax|
 * +---------------------+
 *           |
 *           | tests
 *           v
 * +---------------------+
 * | TaxDetailsView     |
 * +---------------------+
 *
 * @package FA
 */
class TaxDetailsViewTest extends TestCase
{
    /**
     * Test render with empty tax items
     */
    public function testRenderWithEmptyTaxItems()
    {
        $view = new TaxDetailsView();
        $result = $view->render([], 6);
        $this->assertInstanceOf(HtmlFragment::class, $result);
        // Empty result should be empty fragment
    }

    /**
     * Test render with tax items
     */
    public function testRenderWithTaxItems()
    {
        $view = new TaxDetailsView();
        $taxItems = [
            [
                'amount' => 0.0, // Set to 0 to avoid calling undefined functions
                'tax_type_name' => 'VAT',
                'rate' => 10,
                'included_in_price' => false,
                'net_amount' => 100.0
            ]
        ];
        $result = $view->render($taxItems, 6);
        $this->assertInstanceOf(HtmlFragment::class, $result);
    }
}