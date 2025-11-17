<?php

namespace FA;

use Ksfraser\HTML\FaUiFunctions;
use Ksfraser\HTML\Elements\HtmlHeading1;
use Ksfraser\HTML\Elements\HtmlBr;
use Ksfraser\HTML\Elements\HtmlTable;
use Ksfraser\HTML\Elements\HtmlTableRow;
use Ksfraser\HTML\Elements\HtmlTd;
use Ksfraser\HTML\Elements\HtmlTh;
use Ksfraser\HTML\HtmlFragment;
use Ksfraser\HTML\HtmlAttribute;
use Ksfraser\HTML\Elements\HtmlString;

class ViewCreditNote
{
    private CreditNote $model;

    public function __construct(CreditNote $model)
    {
        $this->model = $model;
    }

    public function getSubTotal(): float
    {
        return $this->model->getSubTotal();
    }

    public function render(): string
    {
        $fragment = new HtmlFragment();

        // Heading
        $heading = new HtmlHeading1(new HtmlString("CREDIT NOTE #{$this->model->transId}"));
        $heading->addAttribute(new HtmlAttribute('style', 'color: red;'));
        $fragment->addChild($heading);
        $fragment->addChild(new HtmlBr());

        // Outer table
        $outerTable = new HtmlTable(new HtmlString(''));
        $outerTable->addAttribute(new HtmlAttribute('class', 'tablestyle2'));
        $outerTable->addAttribute(new HtmlAttribute('width', '95%'));

        $row = new HtmlTableRow(new HtmlString(''));
        $row->addAttribute(new HtmlAttribute('valign', 'top'));

        // Customer cell
        $cell1 = new HtmlTd(new HtmlString(''));
        $customerTable = new HtmlTable(new HtmlString(''));
        $thRow = new HtmlTableRow(new HtmlString(''));
        $th = new HtmlTh(new HtmlString(_("Customer")));
        $thRow->addNested($th);
        $customerTable->addNested($thRow);
        $labelRow = new HtmlTableRow(new HtmlString(''));
        $contentTd = new HtmlTd(new HtmlString($this->model->transData["DebtorName"] . "<br>" . nl2br($this->model->transData["address"])));
        $contentTd->addAttribute(new HtmlAttribute('nowrap', 'nowrap'));
        $labelRow->addNested($contentTd);
        $customerTable->addNested($labelRow);
        $cell1->addNested($customerTable);
        $row->addNested($cell1);

        // Branch cell
        $cell2 = new HtmlTd(new HtmlString(''));
        $branchTable = new HtmlTable(new HtmlString(''));
        $thRow2 = new HtmlTableRow(new HtmlString(''));
        $th2 = new HtmlTh(new HtmlString(_("Branch")));
        $thRow2->addNested($th2);
        $branchTable->addNested($thRow2);
        $labelRow2 = new HtmlTableRow(new HtmlString(''));
        $contentTd2 = new HtmlTd(new HtmlString($this->model->branchData["br_name"] . "<br>" . nl2br($this->model->branchData["br_address"])));
        $contentTd2->addAttribute(new HtmlAttribute('nowrap', 'nowrap'));
        $labelRow2->addNested($contentTd2);
        $branchTable->addNested($labelRow2);
        $cell2->addNested($branchTable);
        $row->addNested($cell2);

        // Transaction cell
        $cell3 = new HtmlTd(new HtmlString(''));
        $transTable = new HtmlTable(new HtmlString(''));
        $row1 = new HtmlTableRow(new HtmlString(''));
        $td1 = new HtmlTd(new HtmlString(_("Ref")));
        $td1->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row1->addNested($td1);
        $td2 = new HtmlTd(new HtmlString(DateService::sql2dateStatic($this->model->transData["tran_date"])));
        $td2->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row1->addNested($td2);
        $td3 = new HtmlTd(new HtmlString($this->model->transData["curr_code"]));
        $td3->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row1->addNested($td3);
        $transTable->addNested($row1);
        $row2 = new HtmlTableRow(new HtmlString(''));
        $td4 = new HtmlTd(new HtmlString(_("Sales Type")));
        $td4->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row2->addNested($td4);
        $td5 = new HtmlTd(new HtmlString($this->model->transData["shipper_name"]));
        $td5->addAttribute(new HtmlAttribute('class', 'tableheader2'));
        $row2->addNested($td5);
        $transTable->addNested($row2);
        // comments_display_row(\ST_CUSTCREDIT, $this->model->transId);
        $cell3->addNested($transTable);
        $row->addNested($cell3);

        $outerTable->addNested($row);
        $fragment->addChild($outerTable);

        // Line items table
        $lineTable = new HtmlTable(new HtmlString(''));
        $lineTable->addAttribute(new HtmlAttribute('class', 'tablestyle'));
        $lineTable->addAttribute(new HtmlAttribute('width', '95%'));

        $result = $this->model->lineItems;

        if (count($result) > 0) {
            $thRow = new HtmlTableRow(new HtmlString(''));
            $ths = array(_("Item Code"), _("Item Description"), _("Quantity"), _("Unit"), _("Price"), _("Discount %"), _("Total"));
            foreach ($ths as $thText) {
                $th = new HtmlTh(new HtmlString($thText));
                $thRow->addNested($th);
            }
            $lineTable->addNested($thRow);

            $k = 0;
            $sub_total = 0;

            foreach ($result as $myrow2) {
                if ($myrow2["quantity"] == 0) continue;
                $row = new HtmlTableRow(new HtmlString(''));
                if ($k % 2 == 0) {
                    $row->addAttribute(new HtmlAttribute('class', 'odd'));
                }
                $value = round2(((1 - $myrow2["discount_percent"]) * $myrow2["unit_price"] * $myrow2["quantity"]), user_price_dec());
                $sub_total += $value;

                $display_discount = ($myrow2["discount_percent"] == 0) ? "" : percent_format($myrow2["discount_percent"]*100) . "%";

                $tdContents = array(
                    $myrow2["stock_id"],
                    $myrow2["StockDescription"],
                    qty_cell($myrow2["quantity"], false, get_qty_dec($myrow2["stock_id"])),
                    $myrow2["units"],
                    amount_cell($myrow2["unit_price"]),
                    $display_discount,
                    amount_cell($value)
                );

                foreach ($tdContents as $index => $content) {
                    $td = new HtmlTd(new HtmlString($content));
                    if (in_array($index, [3, 5, 6])) { // Unit, Discount, Total
                        $td->addAttribute(new HtmlAttribute('align', 'right'));
                    }
                    $row->addNested($td);
                }

                $lineTable->addNested($row);
                $k++;
            }
        } else {
            $row = new HtmlTableRow(new HtmlString(''));
            $td = new HtmlTd(new HtmlString(_("There are no line items on this credit note.")));
            $td->addAttribute(new HtmlAttribute('colspan', '7'));
            $row->addNested($td);
            $lineTable->addNested($row);
        }

        $display_sub_tot = FormatService::priceFormat($sub_total);
        $credit_total = $this->model->transData["ov_freight"] + $this->model->transData["ov_gst"] + $this->model->transData["ov_amount"] + $this->model->transData["ov_freight_tax"];
        $display_total = FormatService::priceFormat($credit_total);

        if ($sub_total != 0) {
            $labelRow = new HtmlTableRow(new HtmlString(''));
            $labelTd = new HtmlTd(new HtmlString(_("Sub Total")));
            $contentTd = new HtmlTd(new HtmlString($display_sub_tot));
            $contentTd->addAttribute(new HtmlAttribute('colspan', '6'));
            $contentTd->addAttribute(new HtmlAttribute('align', 'right'));
            $contentTd->addAttribute(new HtmlAttribute('width', '15%'));
            $contentTd->addAttribute(new HtmlAttribute('nowrap', ''));
            $labelRow->addNested($labelTd);
            $labelRow->addNested($contentTd);
            $lineTable->addNested($labelRow);
        }

        if ($this->model->transData["ov_freight"] != 0.0) {
            $display_freight = FormatService::priceFormat($this->model->transData["ov_freight"]);
            $labelRow = new HtmlTableRow(new HtmlString(''));
            $labelTd = new HtmlTd(new HtmlString(_("Shipping")));
            $contentTd = new HtmlTd(new HtmlString($display_freight));
            $contentTd->addAttribute(new HtmlAttribute('colspan', '6'));
            $contentTd->addAttribute(new HtmlAttribute('align', 'right'));
            $contentTd->addAttribute(new HtmlAttribute('nowrap', ''));
            $labelRow->addNested($labelTd);
            $labelRow->addNested($contentTd);
            $lineTable->addNested($labelRow);
        }

        $tax_items = $this->model->taxItems;
        // display_customer_trans_tax_details($tax_items, 6);

        $labelRow = new HtmlTableRow(new HtmlString(''));
        $labelTd = new HtmlTd(new HtmlString("<font color=red>" . _("TOTAL CREDIT") . "</font>"));
        $contentTd = new HtmlTd(new HtmlString("<font color=red>$display_total</font>"));
        $contentTd->addAttribute(new HtmlAttribute('colspan', '6'));
        $contentTd->addAttribute(new HtmlAttribute('align', 'right'));
        $contentTd->addAttribute(new HtmlAttribute('nowrap', ''));
        $labelRow->addNested($labelTd);
        $labelRow->addNested($contentTd);
        $lineTable->addNested($labelRow);

        $fragment->addChild($lineTable);

        // $voided = $this->isVoided;
        // if ($voided) display_note(_("This credit note has been voided."), 0, 0);
        // if (!$voided) display_allocations_from(PT_CUSTOMER, $this->transData['debtor_no'], ST_CUSTCREDIT, $this->transId, $credit_total);

        return $fragment->getHtml();
    }
}