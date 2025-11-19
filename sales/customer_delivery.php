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
//-----------------------------------------------------------------------------
//
//	Entry/Modify Delivery Note against Sales Order
//
$page_security = 'SA_SALESDELIVERY';
$path_to_root = "..";

include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");
include_once($path_to_root . "/includes/CompanyPrefsService.php");
include_once($path_to_root . "/includes/ui_strings.php");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
use FA\Services\DateService;

$js = "";
if ($SysPrefs->use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}
if (user_use_date_picker()) {
	$js .= get_js_date_picker();
}

if (isset($_GET['ModifyDelivery'])) {
	$_SESSION['page_title'] = sprintf(_(UI_TEXT_MODIFYING_DELIVERY_NOTE), $_GET['ModifyDelivery']);
	$help_context = "Modifying Delivery Note";
	processing_start();
} elseif (isset($_GET['OrderNumber'])) {
	$_SESSION['page_title'] = _($help_context = "Deliver Items for a Sales Order");
	processing_start();
}

page($_SESSION['page_title'], false, false, "", $js);

if (isset($_GET['AddedID'])) {
	$dispatch_no = $_GET['AddedID'];

	display_notification_centered(sprintf(_(UI_TEXT_DELIVERY_ENTERED),$dispatch_no));

	display_note(get_customer_trans_view_str(ST_CUSTDELIVERY, $dispatch_no, _(UI_TEXT_VIEW_THIS_DELIVERY)), 0, 1);

	display_note(print_document_link($dispatch_no, _(UI_TEXT_PRINT_DELIVERY_NOTE), true, ST_CUSTDELIVERY));
	display_note(print_document_link($dispatch_no, _(UI_TEXT_EMAIL_DELIVERY_NOTE), true, ST_CUSTDELIVERY, false, "printlink", "", 1), 1, 1);
	display_note(print_document_link($dispatch_no, _(UI_TEXT_PRINT_AS_PACKING_SLIP), true, ST_CUSTDELIVERY, false, "printlink", "", 0, 1));
	display_note(print_document_link($dispatch_no, _(UI_TEXT_EMAIL_AS_PACKING_SLIP), true, ST_CUSTDELIVERY, false, "printlink", "", 1, 1), 1);

	display_note(get_gl_view_str(13, $dispatch_no, _(UI_TEXT_VIEW_GL_JOURNAL_ENTRIES_FOR_DISPATCH)),1);

	if (!isset($_GET['prepaid']))
		hyperlink_params("$path_to_root/sales/customer_invoice.php", _(UI_TEXT_INVOICE_THIS_DELIVERY), "DeliveryNumber=$dispatch_no");

	hyperlink_params("$path_to_root/sales/inquiry/sales_orders_view.php", _(UI_TEXT_SELECT_ANOTHER_ORDER_FOR_DISPATCH), "OutstandingOnly=1");

	hyperlink_params("$path_to_root/admin/attachments.php", _(UI_TEXT_ADD_AN_ATTACHMENT), "filterType=".ST_CUSTDELIVERY."&trans_no=$dispatch_no");

	display_footer_exit();

} elseif (isset($_GET['UpdatedID'])) {

	$delivery_no = $_GET['UpdatedID'];

	display_notification_centered(sprintf(_('Delivery Note # %d has been updated.'),$delivery_no));

	display_note(get_trans_view_str(ST_CUSTDELIVERY, $delivery_no, _(UI_TEXT_VIEW_DELIVERY)), 0, 1);

	display_note(print_document_link($delivery_no, _(UI_TEXT_PRINT_DELIVERY_NOTE), true, ST_CUSTDELIVERY));
	display_note(print_document_link($delivery_no, _(UI_TEXT_EMAIL_DELIVERY_NOTE), true, ST_CUSTDELIVERY, false, "printlink", "", 1), 1, 1);
	display_note(print_document_link($delivery_no, _(UI_TEXT_PRINT_AS_PACKING_SLIP), true, ST_CUSTDELIVERY, false, "printlink", "", 0, 1));
	display_note(print_document_link($delivery_no, _(UI_TEXT_EMAIL_AS_PACKING_SLIP), true, ST_CUSTDELIVERY, false, "printlink", "", 1, 1), 1);

	if (!isset($_GET['prepaid']))
		hyperlink_params($path_to_root . "/sales/customer_invoice.php", _(UI_TEXT_CONFIRM_DELIVERY_AND_INVOICE), "DeliveryNumber=$delivery_no");

	hyperlink_params($path_to_root . "/sales/inquiry/sales_deliveries_view.php", _(UI_TEXT_SELECT_A_DIFFERENT_DELIVERY), "OutstandingOnly=1");

	display_footer_exit();
}
//-----------------------------------------------------------------------------

if (isset($_GET['OrderNumber']) && $_GET['OrderNumber'] > 0) {

	$ord = new Cart(ST_SALESORDER, $_GET['OrderNumber'], true);
	if ($ord->is_prepaid())
		check_deferred_income_act(_(UI_TEXT_SET_DEFERRED_INCOME_ACCOUNT));

	if ($ord->count_items() == 0) {
		hyperlink_params($path_to_root . "/sales/inquiry/sales_orders_view.php",
			_(UI_TEXT_SELECT_DIFFERENT_SALES_ORDER_TO_DELIVERY), "OutstandingOnly=1");
		echo "<br><center><b>" . _(UI_TEXT_ORDER_HAS_NO_ITEMS_NOTHING_TO_DELIVERY) .
			"</center></b>";
		display_footer_exit();
	} else if (!$ord->is_released()) {
		hyperlink_params($path_to_root . "/sales/inquiry/sales_orders_view.php",_(UI_TEXT_SELECT_DIFFERENT_SALES_ORDER_TO_DELIVERY),
			"OutstandingOnly=1");
		echo "<br><center><b>"._(UI_TEXT_PREPAYMENT_ORDER_NOT_READY)
			."</center></b>";
		display_footer_exit();
	}
 	// Adjust Shipping Charge based upon previous deliveries TAM
	adjust_shipping_charge($ord, $_GET['OrderNumber']);
 
	$_SESSION['Items'] = $ord;
	copy_from_cart();

} elseif (isset($_GET['ModifyDelivery']) && $_GET['ModifyDelivery'] > 0) {

	check_is_editable(ST_CUSTDELIVERY, $_GET['ModifyDelivery']);
	$_SESSION['Items'] = new Cart(ST_CUSTDELIVERY,$_GET['ModifyDelivery']);

	if (!$_SESSION['Items']->prepaid && $_SESSION['Items']->count_items() == 0) {
		hyperlink_params($path_to_root . "/sales/inquiry/sales_orders_view.php",
			_(UI_TEXT_SELECT_DIFFERENT_DELIVERY), "OutstandingOnly=1");
		echo "<br><center><b>" . _(UI_TEXT_DELIVERY_ALL_ITEMS_INVOICED) .
			"</center></b>";
		display_footer_exit();
	}

	copy_from_cart();
	
} elseif ( !processing_active() ) {
	/* This page can only be called with an order number for invoicing*/

	UiMessageService::displayError(_(UI_TEXT_PAGE_OPEN_IF_ORDER_SELECTED));

	hyperlink_params("$path_to_root/sales/inquiry/sales_orders_view.php", _(UI_TEXT_SELECT_SALES_ORDER_TO_DELIVERY), "OutstandingOnly=1");

	end_page();
	exit;

} else {
	check_edit_conflicts(RequestService::getPostStatic('cart_id'));

	if (!check_quantities()) {
		UiMessageService::displayError(_(UI_TEXT_SELECTED_QUANTITY_LESS_THAN_INVOICED));

	} elseif(!check_num('ChargeFreightCost', 0)) {
		UiMessageService::displayError(_(UI_TEXT_FREIGHT_COST_CANNOT_BE_LESS_THAN_ZERO));
		set_focus('ChargeFreightCost');
	}
}

//-----------------------------------------------------------------------------

function check_data()
{
	global $Refs, $SysPrefs;
	$dateService = new DateService();

	if (!isset($_POST['DispatchDate']) || !$dateService->isDate($_POST['DispatchDate']))	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_DATE_OF_DELIVERY_INVALID));
		set_focus('DispatchDate');
		return false;
	}

	if (!DateService::isDateInFiscalYear($_POST['DispatchDate'])) {
		UiMessageService::displayError(_(UI_TEXT_ENTERED_DATE_OUT_OF_FISCAL_YEAR));
		set_focus('DispatchDate');
		return false;
	}

	if (!isset($_POST['due_date']) || !$dateService->isDate($_POST['due_date']))	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_DEADLINE_FOR_INVOICE_INVALID));
		set_focus('due_date');
		return false;
	}

	if ($_SESSION['Items']->trans_no==0) {
		if (!$Refs->is_valid($_POST['ref'], ST_CUSTDELIVERY)) {
			UiMessageService::displayError(_(UI_TEXT_MUST_ENTER_REFERENCE));
			set_focus('ref');
			return false;
		}
	}
	if ($_POST['ChargeFreightCost'] == "") {
		$_POST['ChargeFreightCost'] = FormatService::priceFormat(0);
	}

	if (!check_num('ChargeFreightCost',0)) {
		UiMessageService::displayError(_(UI_TEXT_ENTERED_SHIPPING_VALUE_NOT_NUMERIC));
		set_focus('ChargeFreightCost');
		return false;
	}

	if ($_SESSION['Items']->has_items_dispatch() == 0 && RequestService::inputNumStatic('ChargeFreightCost') == 0) {
		UiMessageService::displayError(_(UI_TEXT_NO_ITEM_QUANTITIES_ON_DELIVERY_NOTE));
		return false;
	}

	if (!check_quantities()) {
		return false;
	}

	copy_to_cart();

	if (!$SysPrefs->allow_negative_stock() && ($low_stock = $_SESSION['Items']->check_qoh()))
	{
		UiMessageService::displayError(_(UI_TEXT_INSUFFICIENT_QUANTITY_FOR_ITEMS));
		return false;
	}

	return true;
}
//------------------------------------------------------------------------------
function copy_to_cart()
{
	$cart = &$_SESSION['Items'];
	$cart->ship_via = $_POST['ship_via'];
	$cart->freight_cost = RequestService::inputNumStatic('ChargeFreightCost');
	$cart->document_date = $_POST['DispatchDate'];
	$cart->due_date =  $_POST['due_date'];
	$cart->Location = $_POST['Location'];
	$cart->Comments = $_POST['Comments'];
	$cart->dimension_id = $_POST['dimension_id'];
	$cart->dimension2_id = $_POST['dimension2_id'];
	if ($cart->trans_no == 0)
		$cart->reference = $_POST['ref'];

}
//------------------------------------------------------------------------------

function copy_from_cart()
{
	$cart = &$_SESSION['Items'];
	$_POST['ship_via'] = $cart->ship_via;
	$_POST['ChargeFreightCost'] = FormatService::priceFormat($cart->freight_cost);
	$_POST['DispatchDate'] = $cart->document_date;
	$_POST['due_date'] = $cart->due_date;
	$_POST['Location'] = $cart->Location;
	$_POST['Comments'] = $cart->Comments;
	$_POST['dimension_id'] = $cart->dimension_id;
	$_POST['dimension2_id'] = $cart->dimension2_id;
	$_POST['cart_id'] = $cart->cart_id;
	$_POST['ref'] = $cart->reference;
}
//------------------------------------------------------------------------------

function check_quantities()
{
	$ok =1;
	// Update cart delivery quantities/descriptions
	foreach ($_SESSION['Items']->line_items as $line=>$itm) {
		if (isset($_POST['Line'.$line])) {
			if($_SESSION['Items']->trans_no) {
				$min = $itm->qty_done;
				$max = $itm->quantity;
			} else {
				$min = 0;
				// Fixing floating point problem in PHP.
				$max = round2($itm->quantity - $itm->qty_done, get_qty_dec($itm->stock_id));
			}

			if (check_num('Line'.$line, $min, $max)) {
				$_SESSION['Items']->line_items[$line]->qty_dispatched =
				  RequestService::inputNumStatic('Line'.$line);
			} else {
				set_focus('Line'.$line);
				$ok = 0;
			}
		}

		if (isset($_POST['Line'.$line.'Desc'])) {
			$line_desc = $_POST['Line'.$line.'Desc'];
			if (strlen($line_desc) > 0) {
				$_SESSION['Items']->line_items[$line]->item_description = $line_desc;
			}
		}
	}
	return $ok;
}

//------------------------------------------------------------------------------

if (isset($_POST['process_delivery']) && check_data()) {
	$dn = &$_SESSION['Items'];

	if ($_POST['bo_policy']) {
		$bo_policy = 0;
	} else {
		$bo_policy = 1;
	}
	$newdelivery = ($dn->trans_no == 0);

	if ($newdelivery)
		DateService::newDocDateStatic($dn->document_date);

	$delivery_no = $dn->write($bo_policy);

	if ($delivery_no == -1)
	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_REFERENCE_ALREADY_IN_USE));
		set_focus('ref');
	}
	else
	{
		$is_prepaid = $dn->is_prepaid() ? "&prepaid=Yes" : '';

		processing_end();
		if ($newdelivery) {
			meta_forward($_SERVER['PHP_SELF'], "AddedID=$delivery_no$is_prepaid");
		} else {
			meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$delivery_no$is_prepaid");
		}
	}
}

if (isset($_POST['Update']) || isset($_POST['_Location_update']) || isset($_POST['qty']) || isset($_POST['process_delivery'])) {
	$Ajax->activate('Items');
}
//------------------------------------------------------------------------------
start_form();
hidden('cart_id');

start_table(TABLESTYLE2, "width='80%'", 5);
echo "<tr><td>"; // outer table

start_table(TABLESTYLE, "width='100%'");
start_row();
label_cells(_(UI_TEXT_CUSTOMER), $_SESSION['Items']->customer_name, "class='tableheader2'");
label_cells(_(UI_TEXT_BRANCH), get_branch_name($_SESSION['Items']->Branch), "class='tableheader2'");
label_cells(_(UI_TEXT_CURRENCY), $_SESSION['Items']->customer_currency, "class='tableheader2'");
end_row();
start_row();

if ($_SESSION['Items']->trans_no==0) {
	ref_cells(_(UI_TEXT_REFERENCE), 'ref', '', null, "class='tableheader2'", false, ST_CUSTDELIVERY,
	array('customer' => $_SESSION['Items']->customer_id,
			'branch' => $_SESSION['Items']->Branch,
			'date' => RequestService::getPostStatic('DispatchDate')));
} else {
	label_cells(_(UI_TEXT_REFERENCE), $_SESSION['Items']->reference, "class='tableheader2'");
}

label_cells(_(UI_TEXT_FOR_SALES_ORDER), get_customer_trans_view_str(ST_SALESORDER, $_SESSION['Items']->order_no), "class='tableheader2'");

label_cells(_(UI_TEXT_SALES_TYPE), $_SESSION['Items']->sales_type_name, "class='tableheader2'");
end_row();
start_row();

if (!isset($_POST['Location'])) {
	$_POST['Location'] = $_SESSION['Items']->Location;
}
label_cell(_(UI_TEXT_DELIVERY_FROM), "class='tableheader2'");
locations_list_cells(null, 'Location', null, false, true);

if (!isset($_POST['ship_via'])) {
	$_POST['ship_via'] = $_SESSION['Items']->ship_via;
}
label_cell(_(UI_TEXT_SHIPPING_COMPANY), "class='tableheader2'");
shippers_list_cells(null, 'ship_via', $_POST['ship_via']);

// set this up here cuz it's used to calc qoh
$dateService = new DateService();
if (!isset($_POST['DispatchDate']) || !$dateService->isDate($_POST['DispatchDate'])) {
	$_POST['DispatchDate'] = DateService::newDocDateStatic();
	if (!DateService::isDateInFiscalYear($_POST['DispatchDate'])) {
		$_POST['DispatchDate'] = DateService::endFiscalYear();
	}
}
date_cells(_(UI_TEXT_DATE), 'DispatchDate', '', $_SESSION['Items']->trans_no==0, 0, 0, 0, "class='tableheader2'");
end_row();

end_table();

echo "</td><td>";// outer table

start_table(TABLESTYLE, "width='90%'");

if (!isset($_POST['due_date']) || !$dateService->isDate($_POST['due_date'])) {
	$_POST['due_date'] = get_invoice_duedate($_SESSION['Items']->payment, $_POST['DispatchDate']);
}
customer_credit_row($_SESSION['Items']->customer_id, $_SESSION['Items']->credit, "class='tableheader2'");

$dim = \FA\Services\CompanyPrefsService::getUseDimensions();
if ($dim > 0) {
	start_row();
	label_cell(_(UI_TEXT_DIMENSION).":", "class='tableheader2'");
	dimensions_list_cells(null, 'dimension_id', null, true, ' ', false, 1, false);
	end_row();
}		
else
	hidden('dimension_id', 0);
if ($dim > 1) {
	start_row();
	label_cell(_(UI_TEXT_DIMENSION)." 2:", "class='tableheader2'");
	dimensions_list_cells(null, 'dimension2_id', null, true, ' ', false, 2, false);
	end_row();
}		
else
	hidden('dimension2_id', 0);
//---------
start_row();
date_cells(_(UI_TEXT_INVOICE_DEADLINE), 'due_date', '', null, 0, 0, 0, "class='tableheader2'");
end_row();
end_table();

echo "</td></tr>";
end_table(1); // outer table

$row = get_customer_to_order($_SESSION['Items']->customer_id);
if ($row['dissallow_invoices'] == 1)
{
	UiMessageService::displayError(_(UI_TEXT_SELECTED_CUSTOMER_ACCOUNT_ON_HOLD));
	end_form();
	end_page();
	exit();
}	
display_heading(_(UI_TEXT_DELIVERY_ITEMS));
div_start('Items');
start_table(TABLESTYLE, "width='80%'");

$new = $_SESSION['Items']->trans_no==0;
$th = array(_(UI_TEXT_ITEM_CODE), _(UI_TEXT_ITEM_DESCRIPTION), 
	$new ? _(UI_TEXT_ORDERED) : _(UI_TEXT_MAX_DELIVERY), _(UI_TEXT_UNITS), $new ? _(UI_TEXT_DELIVERED) : _(UI_TEXT_INVOICED),
	_(UI_TEXT_THIS_DELIVERY), _(UI_TEXT_PRICE), _(UI_TEXT_TAX_TYPE), _(UI_TEXT_DISCOUNT), _(UI_TEXT_TOTAL));

table_header($th);
$k = 0;
$has_marked = false;

foreach ($_SESSION['Items']->line_items as $line=>$ln_itm) {
	if ($ln_itm->quantity==$ln_itm->qty_done) {
		continue; //this line is fully delivered
	}
	if(isset($_POST['_Location_update']) || isset($_POST['clear_quantity']) || isset($_POST['reset_quantity'])) {
		// reset quantity
		$ln_itm->qty_dispatched = $ln_itm->quantity-$ln_itm->qty_done;
	}
	// if it's a non-stock item (eg. service) don't show qoh
	$row_classes = null;
	if (InventoryService::hasStockHolding($ln_itm->mb_flag) && $ln_itm->qty_dispatched) {
		// It's a stock : call get_dispatchable_quantity hook  to get which quantity to preset in the
		// quantity input box. This allows for example a hook to modify the default quantity to what's dispatchable
		// (if there is not enough in hand), check at other location or other order people etc ...
		// This hook also returns a 'reason' (css classes) which can be used to theme the row.
		//
		// FIXME: hook_get_dispatchable definition does not allow qoh checks on transaction level
		// (but anyway dispatch is checked again later before transaction is saved)

		$qty = $ln_itm->qty_dispatched;
		if ($check = check_negative_stock($ln_itm->stock_id, $ln_itm->qty_done-$ln_itm->qty_dispatched, $_POST['Location'], $_POST['DispatchDate']))
			$qty = $check['qty'];

		$q_class =  hook_get_dispatchable_quantity($ln_itm, $_POST['Location'], $_POST['DispatchDate'], $qty);

		// Skip line if needed
		if($q_class === 'skip')  continue;
		if(is_array($q_class)) {
		  list($ln_itm->qty_dispatched, $row_classes) = $q_class;
			$has_marked = true;
		}
	}

	alt_table_row_color($k, $row_classes);
	view_stock_status_cell($ln_itm->stock_id);

	if ($ln_itm->descr_editable)
		text_cells(null, 'Line'.$line.'Desc', $ln_itm->item_description, 30, 50);
	else
		label_cell($ln_itm->item_description);

	$dec = get_qty_dec($ln_itm->stock_id);
	qty_cell($ln_itm->quantity, false, $dec);
	label_cell($ln_itm->units);
	qty_cell($ln_itm->qty_done, false, $dec);

	if(isset($_POST['clear_quantity'])) {
		$ln_itm->qty_dispatched = 0;
	}
	$_POST['Line'.$line]=$ln_itm->qty_dispatched; /// clear post so value displayed in the fiel is the 'new' quantity
	small_qty_cells(null, 'Line'.$line, qty_format($ln_itm->qty_dispatched, $ln_itm->stock_id, $dec), null, null, $dec);

	$display_discount_percent = \FA\Services\FormatService::percentFormat($ln_itm->discount_percent*100) . "%";

	$line_total = ($ln_itm->qty_dispatched * $ln_itm->price * (1 - $ln_itm->discount_percent));

	amount_cell($ln_itm->price);
	label_cell($ln_itm->tax_type_name);
	label_cell($display_discount_percent, "nowrap align=right");
	amount_cell($line_total);

	end_row();
}

$_POST['ChargeFreightCost'] =  RequestService::getPostStatic('ChargeFreightCost', 
	FormatService::priceFormat($_SESSION['Items']->freight_cost));

$colspan = 9;

start_row();
label_cell(_(UI_TEXT_SHIPPING_COST), "colspan=$colspan align=right");
small_amount_cells(null, 'ChargeFreightCost', $_SESSION['Items']->freight_cost);
end_row();

$inv_items_total = $_SESSION['Items']->get_items_total_dispatch();

$display_sub_total = FormatService::priceFormat($inv_items_total + RequestService::inputNumStatic('ChargeFreightCost'));

label_row(_(UI_TEXT_SUB_TOTAL), $display_sub_total, "colspan=$colspan align=right","align=right");

$taxes = $_SESSION['Items']->get_taxes(RequestService::inputNumStatic('ChargeFreightCost'));
$tax_total = display_edit_tax_items($taxes, $colspan, $_SESSION['Items']->tax_included);

$display_total = FormatService::priceFormat(($inv_items_total + RequestService::inputNumStatic('ChargeFreightCost') + $tax_total));

label_row(_(UI_TEXT_AMOUNT_TOTAL), $display_total, "colspan=$colspan align=right","align=right");

end_table(1);

if ($has_marked) {
	display_note(_(UI_TEXT_MARKED_ITEMS_INSUFFICIENT_QUANTITIES), 0, 1, "class='stockmankofg'");
}
start_table(TABLESTYLE2);

policy_list_row(_(UI_TEXT_ACTION_FOR_BALANCE), "bo_policy", null);

textarea_row(_(UI_TEXT_MEMO), 'Comments', null, 50, 4);

end_table(1);
div_end();
submit_center_first('Update', _(UI_TEXT_UPDATE_BUTTON),
	_('Refresh document page'), true);
if(isset($_POST['clear_quantity'])) {
	submit('reset_quantity', _('Reset quantity'), true, _('Refresh document page'));
}
else  {
	submit('clear_quantity', _('Clear quantity'), true, _('Refresh document page'));
}
submit_center_last('process_delivery', _(UI_TEXT_PROCESS_DISPATCH),
	_('Check entered data and save document'), 'default');

end_form();


end_page();

