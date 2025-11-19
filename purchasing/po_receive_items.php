<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_GRN';
$path_to_root = "..";
include_once($path_to_root . "/purchasing/includes/po_class.inc");

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
use FA\Services\DateService;

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = "Receive Purchase Order Items"), false, false, "", $js);

//---------------------------------------------------------------------------------------------------------------

if (isset($_GET['AddedID']))
{
	$grn = $_GET['AddedID'];
	$trans_type = ST_SUPPRECEIVE;

	display_notification_centered(_(UI_TEXT_PURCHASE_ORDER_DELIVERY_PROCESSED));

	display_note(get_trans_view_str($trans_type, $grn, _(UI_TEXT_VIEW_THIS_DELIVERY)));
	
    $clearing_act = get_company_pref('grn_clearing_act');
	if ($clearing_act)	
		display_note(get_gl_view_str($trans_type, $grn, _(UI_TEXT_VIEW_GL_JOURNAL_ENTRIES_FOR_THIS_DELIVERY)), 1);

	hyperlink_params("$path_to_root/purchasing/supplier_invoice.php", _(UI_TEXT_ENTRY_PURCHASE_INVOICE_FOR_THIS_RECEIVAL), "New=1");

	hyperlink_no_params("$path_to_root/purchasing/inquiry/po_search.php", _(UI_TEXT_SELECT_DIFFERENT_PURCHASE_ORDER_FOR_RECEIVING));

	hyperlink_params("$path_to_root/admin/attachments.php", _(UI_TEXT_ADD_AN_ATTACHMENT), 
		"filterType=$trans_type&trans_no=$grn");

	display_footer_exit();
}

//--------------------------------------------------------------------------------------------------

if ((!isset($_GET['PONumber']) || $_GET['PONumber'] == 0) && !isset($_SESSION['PO']))
{
	die (_(UI_TEXT_PAGE_CAN_ONLY_OPEN_IF_PO_SELECTED));
}

//--------------------------------------------------------------------------------------------------

function display_po_receive_items()
{
	div_start('grn_items');
    start_table(TABLESTYLE, "colspan=7 width='90%'");
    $th = array(_(UI_TEXT_ITEM_CODE), _(UI_TEXT_DESCRIPTION), _(UI_TEXT_ORDERED), _(UI_TEXT_UNITS), _(UI_TEXT_RECEIVED),
    	_(UI_TEXT_OUTSTANDING), _(UI_TEXT_THIS_DELIVERY), _(UI_TEXT_PRICE), _(UI_TEXT_TOTAL));
    table_header($th);

    /*show the line items on the order with the quantity being received for modification */

    $total = 0;
    $k = 0; //row colour counter

    if (count($_SESSION['PO']->line_items)> 0 )
    {
       	foreach ($_SESSION['PO']->line_items as $ln_itm)
       	{

			alt_table_row_color($k);

    		$qty_outstanding = $ln_itm->quantity - $ln_itm->qty_received;

 			if (!isset($_POST['Update']) && !isset($_POST['ProcessGoodsReceived']) && $ln_itm->receive_qty == 0)
    	  	{   //If no quantites yet input default the balance to be received
    	    	$ln_itm->receive_qty = $qty_outstanding;
    		}

    		$line_total = ($ln_itm->receive_qty * $ln_itm->price);
    		$total += $line_total;

			label_cell($ln_itm->stock_id);
			if ($qty_outstanding > 0)
				text_cells(null, $ln_itm->stock_id . "Desc", $ln_itm->item_description, 30, 50);
			else
				label_cell($ln_itm->item_description);
			$dec = get_qty_dec($ln_itm->stock_id);
			qty_cell($ln_itm->quantity, false, $dec);
			label_cell($ln_itm->units);
			qty_cell($ln_itm->qty_received, false, $dec);
			qty_cell($qty_outstanding, false, $dec);

			if ($qty_outstanding > 0)
				qty_cells(null, $ln_itm->line_no, FormatService::numberFormat2($ln_itm->receive_qty, $dec), "align=right", null, $dec);
			else
				label_cell(FormatService::numberFormat2($ln_itm->receive_qty, $dec), "align=right");

			amount_decimal_cell($ln_itm->price);
			amount_cell($line_total);
			end_row();
       	}
    }

	$colspan = count($th)-1;

	$display_sub_total = FormatService::priceFormat($total/* + RequestService::inputNumStatic('freight_cost')*/);

	label_row(_(UI_TEXT_SUB_TOTAL), $display_sub_total, "colspan=$colspan align=right","align=right");
	$taxes = $_SESSION['PO']->get_taxes(RequestService::inputNumStatic('freight_cost'), true);
	
	$tax_total = display_edit_tax_items($taxes, $colspan, $_SESSION['PO']->tax_included);

	$display_total = FormatService::priceFormat(($total + RequestService::inputNumStatic('freight_cost') + $tax_total));

	start_row();
	label_cells(_(UI_TEXT_AMOUNT_TOTAL), $display_total, "colspan=$colspan align='right'","align='right'");
	end_row();
    end_table();
	div_end();
}

//--------------------------------------------------------------------------------------------------

function check_po_changed()
{
	/*Now need to check that the order details are the same as they were when they were read
	into the Items array. If they've changed then someone else must have altered them */
	// Compare against COMPLETED items only !!
	// Otherwise if you try to fullfill item quantities separately will give error.
	$result = get_po_items($_SESSION['PO']->order_no);

	$line_no = 0;
	while ($myrow = db_fetch($result))
	{
		$ln_item = $_SESSION['PO']->line_items[$line_no];
		// only compare against items that are outstanding
		$qty_outstanding = $ln_item->quantity - $ln_item->qty_received;
		if ($qty_outstanding > 0)
		{
    		if ($ln_item->qty_inv != $myrow["qty_invoiced"]	||
    			$ln_item->stock_id != $myrow["item_code"] ||
    			$ln_item->quantity != $myrow["quantity_ordered"] ||
    			$ln_item->qty_received != $myrow["quantity_received"])
    		{
    			return true;
    		}
		}
	 	$line_no++;
	} /*loop through all line items of the order to ensure none have been invoiced */

	return false;
}

//--------------------------------------------------------------------------------------------------

function can_process()
{
	global $SysPrefs;
	
	if (count($_SESSION['PO']->line_items) <= 0)
	{
        UiMessageService::displayError(_(UI_TEXT_NOTHING_TO_PROCESS_ENTER_VALID_QUANTITIES));
    	return false;
	}
	$dateService = new DateService();

	if (!$dateService->isDate($_POST['DefaultReceivedDate']))
	{
		UiMessageService::displayError(_(UI_TEXT_DATE_ENTERED_INVALID_FORMAT));
		set_focus('DefaultReceivedDate');
		return false;
	}
	if (!DateService::isDateInFiscalYear($_POST['DefaultReceivedDate'])) {
		UiMessageService::displayError(_(UI_TEXT_ENTERED_DATE_OUT_OF_FISCAL_YEAR));
		set_focus('DefaultReceivedDate');
		return false;
	}

	if (!check_reference($_POST['ref'], ST_SUPPRECEIVE))
	{
		set_focus('ref');
		return false;
	}

	$something_received = 0;
	foreach ($_SESSION['PO']->line_items as $order_line)
	{
	  	if ($order_line->receive_qty > 0)
	  	{
			$something_received = 1;
			break;
	  	}
	}

    // Check whether trying to deliver more items than are recorded on the actual purchase order (+ overreceive allowance)
    $delivery_qty_too_large = 0;
	foreach ($_SESSION['PO']->line_items as $order_line)
	{
	  	if ($order_line->receive_qty+$order_line->qty_received >
	  		$order_line->quantity * (1+ ($SysPrefs->over_receive_allowance() / 100)))
	  	{
			$delivery_qty_too_large = 1;
			break;
	  	}
	}

    if ($something_received == 0)
    { 	/*Then dont bother proceeding cos nothing to do ! */
        UiMessageService::displayError(_(UI_TEXT_NOTHING_TO_PROCESS_ENTER_VALID_QUANTITIES));
    	return false;
    }
    elseif ($delivery_qty_too_large == 1)
    {
    	UiMessageService::displayError(_(UI_TEXT_ENTERED_QUANTITIES_CANNOT_BE_GREATER_THAN_ORDERED) . " (" . $SysPrefs->over_receive_allowance() ."%)."
    		. "<br>" .
    	 	_(UI_TEXT_MODIFY_ORDERED_ITEMS_TO_INCREASE_QUANTITIES));
    	return false;
    }

	return true;
}

//--------------------------------------------------------------------------------------------------

function process_receive_po()
{
	global $path_to_root, $Ajax;

	if (!can_process())
		return;

	if (check_po_changed())
	{
		UiMessageService::displayError(_(UI_TEXT_ORDER_CHANGED_OR_INVOICED_SINCE_DELIVERY_STARTED));

		hyperlink_no_params("$path_to_root/purchasing/inquiry/po_search.php",
		 _(UI_TEXT_SELECT_DIFFERENT_PURCHASE_ORDER_FOR_RECEIVING_GOODS));

		hyperlink_params("$path_to_root/purchasing/po_receive_items.php", 
			 _(UI_TEXT_RE_READ_UPDATED_PURCHASE_ORDER_FOR_RECEIVING),
			 "PONumber=" . $_SESSION['PO']->order_no);

		unset($_SESSION['PO']->line_items);
		unset($_SESSION['PO']);
		unset($_POST['ProcessGoodsReceived']);
		$Ajax->activate('_page_body');
		display_footer_exit();
	}
	
	$grn = &$_SESSION['PO'];
	$grn->orig_order_date = $_POST['DefaultReceivedDate'];
	$grn->reference = $_POST['ref'];
	$grn->Location = $_POST['Location'];
	$grn->ex_rate = RequestService::inputNumStatic('_ex_rate', null);

	$grn_no = add_grn($grn);

	DateService::newDocDateStatic($_POST['DefaultReceivedDate']);
	unset($_SESSION['PO']->line_items);
	unset($_SESSION['PO']);

	meta_forward($_SERVER['PHP_SELF'], "AddedID=$grn_no");
}

//--------------------------------------------------------------------------------------------------

if (isset($_GET['PONumber']) && $_GET['PONumber'] > 0 && !isset($_POST['Update']))
{
	create_new_po(ST_PURCHORDER, $_GET['PONumber']);
	$_SESSION['PO']->trans_type = ST_SUPPRECEIVE;
	$_SESSION['PO']->reference = $Refs->get_next(ST_SUPPRECEIVE, null,
		array('date' => DateService::todayStatic(), 'supplier' => $_SESSION['PO']->supplier_id));
	copy_from_cart();
}

//--------------------------------------------------------------------------------------------------

if (isset($_POST['Update']) || isset($_POST['ProcessGoodsReceived']))
{

	/* if update quantities button is hit page has been called and ${$line->line_no} would have be
 	set from the post to the quantity to be received in this receival*/
	foreach ($_SESSION['PO']->line_items as $line)
	{
	 if( ($line->quantity - $line->qty_received)>0) {
		$_POST[$line->line_no] = max($_POST[$line->line_no], 0);
		if (!check_num($line->line_no))
			$_POST[$line->line_no] = FormatService::numberFormat2(0, get_qty_dec($line->stock_id));

		if (!isset($_POST['DefaultReceivedDate']) || $_POST['DefaultReceivedDate'] == "")
			$_POST['DefaultReceivedDate'] = DateService::newDocDateStatic();

		$_SESSION['PO']->line_items[$line->line_no]->receive_qty = RequestService::inputNumStatic($line->line_no);

		if (isset($_POST[$line->stock_id . "Desc"]) && strlen($_POST[$line->stock_id . "Desc"]) > 0)
		{
			$_SESSION['PO']->line_items[$line->line_no]->item_description = $_POST[$line->stock_id . "Desc"];
		}
	 }
	}
	$Ajax->activate('grn_items');
}

//--------------------------------------------------------------------------------------------------

if (isset($_POST['ProcessGoodsReceived']))
{
	process_receive_po();
}

//--------------------------------------------------------------------------------------------------

start_form();

edit_grn_summary($_SESSION['PO'], true);
display_heading(_(UI_TEXT_ITEMS_TO_RECEIVE));
display_po_receive_items();

echo '<br>';
submit_center_first('Update', _(UI_TEXT_UPDATE), '', true);
submit_center_last('ProcessGoodsReceived', _(UI_TEXT_PROCESS_RECEIVE_ITEMS), _(UI_TEXT_CLEAR_ALL_GL_ENTRY_FIELDS), 'default');

end_form();

//--------------------------------------------------------------------------------------------------
end_page();
