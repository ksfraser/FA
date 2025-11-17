<?php

namespace FA;

use Ksfraser\HTML\Elements\HtmlTable;
use Ksfraser\HTML\Elements\HtmlTableRow;
use Ksfraser\HTML\Elements\HtmlTd;
use Ksfraser\HTML\Elements\HtmlTh;
use Ksfraser\HTML\Elements\HtmlString;
use Ksfraser\HTML\HtmlElementInterface;
use Ksfraser\HTML\HtmlAttribute;
use Ksfraser\HTML\HtmlFragment;

/**
 * View for displaying allocations
 */
class AllocationsView
{
    /**
     * Render allocations table
     * 
     * @param array $allocRows Array of allocation rows
     * @param float $total Total amount
     * @param string $title Title for the section
     * @param bool $showSummary Whether to show summary
     * @return HtmlElementInterface
     */
    public function render(array $allocRows, float $total, string $title, bool $showSummary = true): HtmlElementInterface
    {
        global $systypes_array;

        if (empty($allocRows)) {
            return new HtmlFragment(); // Empty fragment
        }

        $fragment = new HtmlFragment();

        // Heading
        $heading = new \Ksfraser\HTML\Elements\HtmlHeading2(new HtmlString($title));
        $fragment->addChild($heading);

        // Table
        $table = new HtmlTable(new HtmlString(''));
        $table->addAttribute(new HtmlAttribute('class', 'tablestyle'));
        $table->addAttribute(new HtmlAttribute('width', '80%'));

        // Header
        $thRow = new HtmlTableRow(new HtmlString(''));
        $ths = [_("Type"), _("Number"), _("Date"), _("Total Amount"), _("Left to Allocate"), _("This Allocation")];
        foreach ($ths as $thText) {
            $th = new HtmlTh(new HtmlString($thText));
            $thRow->addNested($th);
        }
        $table->addNested($thRow);

        $k = $totalAllocated = 0;

        foreach ($allocRows as $allocRow) {
            $row = new HtmlTableRow(new HtmlString(''));
            if ($k % 2 == 0) {
                $row->addAttribute(new HtmlAttribute('class', 'odd'));
            }

            $cells = [
                $systypes_array[$allocRow['type']] ?? 'Unknown',
                get_trans_view_str($allocRow['type'], $allocRow['trans_no']),
                DateService::sql2dateStatic($allocRow['tran_date']),
                '', // amount_cell will be handled
                '', // amount_cell
                ''  // amount_cell
            ];

            $allocRow['Total'] = round2($allocRow['Total'], user_price_dec());
            $allocRow['amt'] = round2($allocRow['amt'], user_price_dec());
            if (in_array($allocRow['type'], [ST_SUPPAYMENT, ST_BANKPAYMENT, ST_SUPPCREDIT])) {
                $allocRow['Total'] = -$allocRow['Total'];
            }

            // For simplicity, use HtmlString with formatted content
            $cells[3] = FormatService::numberFormat2($allocRow['Total'], user_price_dec());
            $cells[4] = FormatService::numberFormat2($allocRow['Total'] - $allocRow['alloc'], user_price_dec());
            $cells[5] = FormatService::numberFormat2($allocRow['amt'], user_price_dec());

            foreach ($cells as $cellContent) {
                $td = new HtmlTd(new HtmlString($cellContent));
                $row->addNested($td);
            }

            $table->addNested($row);
            $totalAllocated += $allocRow['amt'];
            $k++;
        }

        // Total Allocated row
        $totalRow = new HtmlTableRow(new HtmlString(''));
        $labelTd = new HtmlTd(new HtmlString(_("Total Allocated:")));
        $labelTd->addAttribute(new HtmlAttribute('align', 'right'));
        $labelTd->addAttribute(new HtmlAttribute('colspan', '5'));
        $totalRow->addNested($labelTd);
        $amountTd = new HtmlTd(new HtmlString(FormatService::numberFormat2($totalAllocated, user_price_dec())));
        $totalRow->addNested($amountTd);
        $table->addNested($totalRow);

        if ($showSummary) {
            $summaryRow = new HtmlTableRow(new HtmlString(''));
            $labelTd2 = new HtmlTd(new HtmlString(_("Left to Allocate:")));
            $labelTd2->addAttribute(new HtmlAttribute('align', 'right'));
            $labelTd2->addAttribute(new HtmlAttribute('colspan', '5'));
            $summaryRow->addNested($labelTd2);
            $totalFormatted = round2($total, user_price_dec());
            $amountTd2 = new HtmlTd(new HtmlString(FormatService::numberFormat2($totalFormatted - $totalAllocated, user_price_dec())));
            $summaryRow->addNested($amountTd2);
            $table->addNested($summaryRow);
        }

        $fragment->addChild($table);

        return $fragment;
    }
}