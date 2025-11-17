<?php

namespace FA;

use Ksfraser\HTML\Elements\HtmlHeading1;
use Ksfraser\HTML\Elements\HtmlBr;
use Ksfraser\HTML\Elements\HtmlTable;
use Ksfraser\HTML\Elements\HtmlTableRow;
use Ksfraser\HTML\Elements\HtmlTd;
use Ksfraser\HTML\Elements\HtmlTh;
use Ksfraser\HTML\Composites\HtmlLabelRow;
use Ksfraser\HTML\HtmlFragment;
use Ksfraser\HTML\HtmlAttribute;
use Ksfraser\HTML\Elements\HtmlString;

/**
 * View for rendering dispatch notes
 *
 * Follows MVC pattern with dependency injection.
 * Uses HTML element composition for recursive rendering.
 *
 * SOLID Principles:
 * - Single Responsibility: Renders dispatch views only
 * - Open/Closed: Can be extended for additional view elements
 * - Liskov Substitution: Compatible with HtmlElementInterface
 * - Interface Segregation: Minimal, focused interface
 * - Dependency Inversion: Depends on Dispatch abstraction
 *
 * DRY: Reuses HTML element classes, avoids code duplication
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * |   ViewDispatch     |
 * +---------------------+
 * | - model: Dispatch  |
 * +---------------------+
 * | + __construct(model)|
 * | + render(): string  |
 * | + getSubTotal(): float|
 * +---------------------+
 *           |
 *           | uses
 *           v
 * +---------------------+
 * |     Dispatch       |
 * +---------------------+
 *
 * @package FA
 */
class ViewDispatch
{
    private Dispatch $model;

    /**
     * Constructor with dependency injection
     *
     * @param Dispatch $model The dispatch model
     */
    public function __construct(Dispatch $model)
    {
        $this->model = $model;
    }

    /**
     * Render the complete dispatch view
     *
     * @return string HTML output
     */
    public function render(): string
    {
        $fragment = new HtmlFragment();

        // Heading
        $headingText = _("DISPATCH NOTE") . " #" . $this->model->transId;
        $heading = new HtmlHeading1(new HtmlString($headingText));
        $fragment->addChild($heading);
        $fragment->addChild(new HtmlBr());

        // Outer table
        $outerTable = new HtmlTable(new HtmlString(''));
        $outerTable->addAttribute(new HtmlAttribute('class', 'tablestyle2'));
        $outerTable->addAttribute(new HtmlAttribute('width', '95%'));

        $row = new HtmlTableRow(new HtmlString(''));
        $row->addAttribute(new HtmlAttribute('valign', 'top'));

        // Charge To
        $cell1 = new HtmlTd(new HtmlString(''));
        $table1 = new HtmlTable(new HtmlString(''));
        $table1->addAttribute(new HtmlAttribute('class', 'tablestyle'));
        $table1->addAttribute(new HtmlAttribute('width', '100%'));
        $thRow = new HtmlTableRow(new HtmlString(''));
        $th = new HtmlTh(new HtmlString(_("Charge To")));
        $thRow->addNested($th);
        $table1->addNested($thRow);
        $labelRow = new HtmlTableRow(new HtmlString(''));
        $td = new HtmlTd(new HtmlString($this->model->transData["DebtorName"] . "<br>" . nl2br($this->model->transData["address"])));
        $td->addAttribute(new HtmlAttribute('nowrap', 'nowrap'));
        $labelRow->addNested($td);
        $table1->addNested($labelRow);
        $cell1->addNested($table1);
        $row->addNested($cell1);

        // Charge Branch
        $cell2 = new HtmlTd(new HtmlString(''));
        $table2 = new HtmlTable(new HtmlString(''));
        $table2->addAttribute(new HtmlAttribute('class', 'tablestyle'));
        $table2->addAttribute(new HtmlAttribute('width', '100%'));
        $thRow2 = new HtmlTableRow(new HtmlString(''));
        $th2 = new HtmlTh(new HtmlString(_("Charge Branch")));
        $thRow2->addNested($th2);
        $table2->addNested($thRow2);
        $labelRow2 = new HtmlTableRow(new HtmlString(''));
        $td2 = new HtmlTd(new HtmlString($this->model->branchData["br_name"] . "<br>" . nl2br($this->model->branchData["br_address"])));
        $td2->addAttribute(new HtmlAttribute('nowrap', 'nowrap'));
        $labelRow2->addNested($td2);
        $table2->addNested($labelRow2);
        $cell2->addNested($table2);
        $row->addNested($cell2);

        // Delivered To
        $cell3 = new HtmlTd(new HtmlString(''));
        $table3 = new HtmlTable(new HtmlString(''));
        $table3->addAttribute(new HtmlAttribute('class', 'tablestyle'));
        $table3->addAttribute(new HtmlAttribute('width', '100%'));
        $thRow3 = new HtmlTableRow(new HtmlString(''));
        $th3 = new HtmlTh(new HtmlString(_("Delivered To")));
        $thRow3->addNested($th3);
        $table3->addNested($thRow3);
        $labelRow3 = new HtmlTableRow(new HtmlString(''));
        $td3 = new HtmlTd(new HtmlString($this->model->orderData["deliver_to"] . "<br>" . nl2br($this->model->orderData["delivery_address"])));
        $td3->addAttribute(new HtmlAttribute('nowrap', 'nowrap'));
        $labelRow3->addNested($td3);
        $table3->addNested($labelRow3);
        $cell3->addNested($table3);
        $row->addNested($cell3);

        // Transaction details
        $cell4 = new HtmlTd(new HtmlString(''));
        $table4 = new HtmlTable(new HtmlString(''));
        $table4->addAttribute(new HtmlAttribute('class', 'tablestyle'));
        $table4->addAttribute(new HtmlAttribute('width', '100%'));

        $row1 = new HtmlTableRow(new HtmlString(''));
        $td1 = new HtmlTd(new HtmlString(_("Reference")));
        $td1->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row1->addNested($td1);
        $td2 = new HtmlTd(new HtmlString($this->model->transData["reference"]));
        $td2->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row1->addNested($td2);
        $td3 = new HtmlTd(new HtmlString($this->model->orderData["curr_code"]));
        $td3->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row1->addNested($td3);
        $table4->addNested($row1);

        // Add more rows for transaction details
        $row2 = new HtmlTableRow(new HtmlString(''));
        $td4 = new HtmlTd(new HtmlString(_("Customer Order Ref.")));
        $td4->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row2->addNested($td4);
        $td5 = new HtmlTd(new HtmlString($this->model->orderData["customer_ref"]));
        $td5->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row2->addNested($td5);
        $td6 = new HtmlTd(new HtmlString($this->model->transData["shipper_name"]));
        $td6->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row2->addNested($td6);
        $table4->addNested($row2);

        $row3 = new HtmlTableRow(new HtmlString(''));
        $td7 = new HtmlTd(new HtmlString(_("Sales Type")));
        $td7->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row3->addNested($td7);
        $td8 = new HtmlTd(new HtmlString($this->model->transData["sales_type"]));
        $td8->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row3->addNested($td8);
        $td9 = new HtmlTd(new HtmlString(get_customer_trans_view_str(ST_SALESORDER, $this->model->orderData["order_no"])));
        $td9->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row3->addNested($td9);
        $table4->addNested($row3);

        $row4 = new HtmlTableRow(new HtmlString(''));
        $td10 = new HtmlTd(new HtmlString(_("Dispatch Date")));
        $td10->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row4->addNested($td10);
        $td11 = new HtmlTd(new HtmlString(sql2date($this->model->transData["tran_date"])));
        $td11->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $td11->addAttribute(new HtmlAttribute('nowrap', ''));
        $row4->addNested($td11);
        $td12 = new HtmlTd(new HtmlString(_("Due Date")));
        $td12->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row4->addNested($td12);
        $table4->addNested($row4);

        $row5 = new HtmlTableRow(new HtmlString(''));
        $td13 = new HtmlTd(new HtmlString(sql2date($this->model->transData["due_date"])));
        $td13->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $td13->addAttribute(new HtmlAttribute('nowrap', ''));
        $row5->addNested($td13);
        $table4->addNested($row5);

        // Comments
        // comments_display_row(ST_CUSTDELIVERY, $this->model->transId);

        $cell4->addNested($table4);
        $row->addNested($cell4);

        $outerTable->addNested($row);
        $fragment->addChild($outerTable);

        // Line items table
        $lineTable = new HtmlTable(new HtmlString(''));
        $lineTable->addAttribute(new HtmlAttribute('class', 'tablestyle'));
        $lineTable->addAttribute(new HtmlAttribute('width', '95%'));

        if (!empty($this->model->lineItems)) {
            $thRow = new HtmlTableRow(new HtmlString(''));
            $ths = [_("Item Code"), _("Item Description"), _("Quantity"), _("Unit"), _("Price"), _("Discount %"), _("Total")];
            foreach ($ths as $thText) {
                $th = new HtmlTh(new HtmlString($thText));
                $thRow->addNested($th);
            }
            $lineTable->addNested($thRow);

            $k = 0;
            $subTotal = 0;
            foreach ($this->model->lineItems as $item) {
                if ($item["quantity"] == 0) continue;
                $row = new HtmlTableRow(new HtmlString(''));
                if ($k % 2 == 0) {
                    $row->addAttribute(new HtmlAttribute('class', 'odd'));
                }

                $value = round2(((1 - $item["discount_percent"]) * $item["unit_price"] * $item["quantity"]), user_price_dec());
                $subTotal += $value;

                $displayDiscount = ($item["discount_percent"] == 0) ? "" : percent_format($item["discount_percent"] * 100) . "%";

                $cells = [
                    $item["stock_id"],
                    $item["StockDescription"],
                    qty_cell($item["quantity"], false, get_qty_dec($item["stock_id"])),
                    $item["units"],
                    amount_cell($item["unit_price"]),
                    $displayDiscount,
                    amount_cell($value)
                ];

                foreach ($cells as $index => $cellContent) {
                    $td = new HtmlTd(new HtmlString($cellContent));
                    if (in_array($index, [3, 5, 6])) {
                        $td->addAttribute(new HtmlAttribute('align', 'right'));
                    }
                    if ($index == 5) {
                        $td->addAttribute(new HtmlAttribute('nowrap', ''));
                    }
                    $row->addNested($td);
                }

                $lineTable->addNested($row);
                $k++;
            }

            $displaySubTot = price_format($subTotal);
            $labelRow = new HtmlTableRow(new HtmlString(''));
            $labelTd = new HtmlTd(new HtmlString(_("Sub-total")));
            $contentTd = new HtmlTd(new HtmlString($displaySubTot));
            $contentTd->addAttribute(new HtmlAttribute('colspan', '6'));
            $contentTd->addAttribute(new HtmlAttribute('align', 'right'));
            $contentTd->addAttribute(new HtmlAttribute('nowrap', ''));
            $contentTd->addAttribute(new HtmlAttribute('width', '15%'));
            $labelRow->addNested($labelTd);
            $labelRow->addNested($contentTd);
            $lineTable->addNested($labelRow);
        } else {
            $row = new HtmlTableRow(new HtmlString(''));
            $td = new HtmlTd(new HtmlString(_("There are no line items on this dispatch.")));
            $td->addAttribute(new HtmlAttribute('colspan', '7'));
            $row->addNested($td);
            $lineTable->addNested($row);
        }

        if ($this->model->transData['ov_freight'] != 0.0) {
            $displayFreight = price_format($this->model->transData["ov_freight"]);
            $labelRow = new HtmlTableRow(new HtmlString(''));
            $labelTd = new HtmlTd(new HtmlString(_("Shipping")));
            $contentTd = new HtmlTd(new HtmlString($displayFreight));
            $contentTd->addAttribute(new HtmlAttribute('colspan', '6'));
            $contentTd->addAttribute(new HtmlAttribute('align', 'right'));
            $contentTd->addAttribute(new HtmlAttribute('nowrap', ''));
            $labelRow->addNested($labelTd);
            $labelRow->addNested($contentTd);
            $lineTable->addNested($labelRow);
        }

        // Tax details
        $taxView = new TaxDetailsView();
        $fragment->addChild($taxView->render($this->model->taxItems, 6));

        $displayTotal = price_format($this->model->transData["ov_freight"] + $this->model->transData["ov_amount"] + $this->model->transData["ov_freight_tax"] + $this->model->transData["ov_gst"]);
        $labelRow = new HtmlTableRow(new HtmlString(''));
        $labelTd = new HtmlTd(new HtmlString(_("TOTAL VALUE")));
        $contentTd = new HtmlTd(new HtmlString($displayTotal));
        $contentTd->addAttribute(new HtmlAttribute('colspan', '6'));
        $contentTd->addAttribute(new HtmlAttribute('align', 'right'));
        $contentTd->addAttribute(new HtmlAttribute('nowrap', ''));
        $labelRow->addNested($labelTd);
        $labelRow->addNested($contentTd);
        $lineTable->addNested($labelRow);

        $fragment->addChild($lineTable);

        // Voided
        $voidEntry = get_voided_entry(ST_CUSTDELIVERY, $this->model->transId);
        $voidedView = new VoidedView();
        $voidedElement = $voidedView->render($voidEntry, _("This dispatch has been voided."));
        if ($voidedElement) {
            $fragment->addChild($voidedElement);
        }

        return $fragment->getHtml();
    }

    /**
     * Get sub total
     *
     * @return float
     */
    public function getSubTotal(): float
    {
        return $this->model->getSubTotal();
    }
}