<?php

namespace FA;

use Ksfraser\HTML\Elements\HtmlTable;
use Ksfraser\HTML\Elements\HtmlTableRow;
use Ksfraser\HTML\Elements\HtmlTd;
use Ksfraser\HTML\Elements\HtmlString;
use Ksfraser\HTML\HtmlElementInterface;
use Ksfraser\HTML\HtmlAttribute;

/**
 * View for displaying customer transaction tax details
 *
 * Follows MVC pattern with dependency injection.
 * Uses HTML element composition for recursive rendering.
 *
 * SOLID Principles:
 * - Single Responsibility: Renders tax details only
 * - Open/Closed: Can be extended for additional tax display elements
 * - Liskov Substitution: Compatible with HtmlElementInterface
 * - Interface Segregation: Minimal, focused interface
 * - Dependency Inversion: Depends on data arrays, no tight coupling
 *
 * DRY: Reuses HTML element classes, avoids code duplication
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | TaxDetailsView     |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + render(taxItems, |
 * |   columns): HtmlEle|
 * | - createLabelRow(..)|
 * +---------------------+
 *
 * @package FA
 */
class TaxDetailsView
{
    /**
     * Render tax details table
     *
     * @param array $taxItems Tax items data array
     * @param int $columns Number of columns to span
     * @return HtmlElementInterface
     */
    public function render(array $taxItems, int $columns = 6): HtmlElementInterface
    {
        global $SysPrefs;

        $fragment = new \Ksfraser\HTML\HtmlFragment();
        $first = true;

        foreach ($taxItems as $tax_item) {
            if (!$tax_item['amount']) continue;

            $tax = number_format2($tax_item['amount'], user_price_dec());
            if ($SysPrefs->suppress_tax_rates() == 1)
                $tax_type_name = $tax_item['tax_type_name'];
            else
                $tax_type_name = $tax_item['tax_type_name'] . " (" . $tax_item['rate'] . "%) ";

            if ($tax_item['included_in_price']) {
                if ($SysPrefs->alternative_tax_include_on_docs() == 1) {
                    if ($first) {
                        $fragment->addChild($this->createLabelRow(_("Total Tax Excluded"), number_format2($tax_item['net_amount'], user_price_dec()), $columns));
                        $first = false;
                    }
                    $fragment->addChild($this->createLabelRow($tax_type_name, $tax, $columns));
                } else {
                    $fragment->addChild($this->createLabelRow(_("Included") . " " . $tax_type_name . ": $tax", "", $columns));
                }
            } else {
                $fragment->addChild($this->createLabelRow($tax_type_name, $tax, $columns));
            }
        }

        return $fragment;
    }

    /**
     * Create a label row element
     *
     * @param string $label Label text
     * @param string $content Content text
     * @param int $columns Number of columns to span
     * @return HtmlTableRow
     */
    private function createLabelRow(string $label, string $content, int $columns): HtmlTableRow
    {
        $row = new HtmlTableRow(new HtmlString(''));

        if ($content === "") {
            // Single cell spanning all columns
            $td = new HtmlTd(new HtmlString($label));
            $td->addAttribute(new HtmlAttribute('colspan', (string)$columns));
            $td->addAttribute(new HtmlAttribute('align', 'right'));
            $row->addNested($td);
        } else {
            // Two cells: label and content
            $labelTd = new HtmlTd(new HtmlString($label));
            $labelTd->addAttribute(new HtmlAttribute('align', 'right'));
            $row->addNested($labelTd);

            $contentTd = new HtmlTd(new HtmlString($content));
            $contentTd->addAttribute(new HtmlAttribute('colspan', (string)$columns));
            $contentTd->addAttribute(new HtmlAttribute('align', 'right'));
            $row->addNested($contentTd);
        }

        return $row;
    }
}