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
//---------------------------------------------------------------------------
//
//	Entry/Modify Sales Invoice against single delivery
//	Entry/Modify Batch Sales Invoice against batch of deliveries
//
$page_security = 'SA_SALESINVOICE';
$path_to_root = "..";
include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");
include_once($path_to_root . "/admin/db/shipping_db.inc");
include_once($path_to_root . "/includes/CompanyPrefsService.php");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
include_once($path_to_root . "/includes/ui_strings.php");
use FA\Services\DateService;

$js = "";
if ($SysPrefs->use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}
if (user_use_date_picker()) {
	$js .= get_js_date_picker();
}

if (isset($_GET['ModifyInvoice'])) {
	$_SESSION['page_title'] = sprintf(_(UI_TEXT_MODIFYING_SALES_INVOICE) ,$_GET['ModifyInvoice']);
	$help_context = "Modifying Sales Invoice";
} elseif (isset($_GET['DeliveryNumber'])) {
	$_SESSION['page_title'] = _($help_context = "Issue an Invoice for Delivery Note");
} elseif (isset($_GET['BatchInvoice'])) {
	$_SESSION['page_title'] = _($help_context = "Issue Batch Invoice for Delivery Notes");
} elseif (isset($_GET['AllocationNumber']) || isset($_GET['InvoicePrepayments'])) {
	$_SESSION['page_title'] = _($help_context = "Prepayment or Final Invoice Entry");
}
page($_SESSION['page_title'], false, false, "", $js);

//-----------------------------------------------------------------------------

check_edit_conflicts(RequestService::getPostStatic('cart_id'));

if (isset($_GET['AddedID'])) {

	$invoice_no = $_GET['AddedID'];
	$trans_type = ST_SALESINVOICE;

	display_notification(_(UI_TEXT_SELECTED_DELIVERIES_PROCESSED), true);

	display_note(get_customer_trans_view_str($trans_type, $invoice_no, _(UI_TEXT_VIEW_THIS_INVOICE)), 0, 1);

	display_note(print_document_link($invoice_no."-".$trans_type, _(UI_TEXT_PRINT_SALES_INVOICE), true, ST_SALESINVOICE));
	display_note(print_document_link($invoice_no."-".$trans_type, _(UI_TEXT_EMAIL_SALES_INVOICE), true, ST_SALESINVOICE, false, "printlink", "", 1),1);

	display_note(get_gl_view_str($trans_type, $invoice_no, _(UI_TEXT_VIEW_GL_JOURNAL_INVOICE)),1);

	hyperlink_params("$path_to_root/sales/inquiry/sales_deliveries_view.php", _(UI_TEXT_SELECT_ANOTHER_DELIVERY_FOR_INVOICING), "OutstandingOnly=1");

	if (!db_num_rows(get_allocatable_from_cust_transactions(null, $invoice_no, $trans_type)))
		hyperlink_params("$path_to_root/sales/customer_payments.php", _(UI_TEXT_ENTRY_CUSTOMER_PAYMENT_INVOICE),
		"SInvoice=".$invoice_no);

	hyperlink_params("$path_to_root/admin/attachments.php", _(UI_TEXT_ADD_ATTACHMENT), "filterType=$trans_type&trans_no=$invoice_no");

	display_footer_exit();

} elseif (isset($_GET['UpdatedID']))  {

	$invoice_no = $_GET['UpdatedID'];
	$trans_type = ST_SALESINVOICE;

	display_notification_centered(sprintf(_(UI_TEXT_SALES_INVOICE_HAS_BEEN_UPDATED),$invoice_no));

	display_note(get_trans_view_str(ST_SALESINVOICE, $invoice_no, _(UI_TEXT_VIEW_THIS_INVOICE)));
	echo '<br>';
	display_note(print_document_link($invoice_no."-".$trans_type, _(UI_TEXT_PRINT_SALES_INVOICE), true, ST_SALESINVOICE));
	display_note(print_document_link($invoice_no."-".$trans_type, _(UI_TEXT_EMAIL_SALES_INVOICE), true, ST_SALESINVOICE, false, "printlink", "", 1),1);

	hyperlink_no_params($path_to_root . "/sales/inquiry/customer_inquiry.php", _(UI_TEXT_SELECT_ANOTHER_INVOICE_TO_MODIFY));

	display_footer_exit();

} elseif (isset($_GET['RemoveDN'])) {

	for($line_no = 0; $line_no < count($_SESSION['Items']->line_items); $line_no++) {
		$line = &$_SESSION['Items']->line_items[$line_no];
		if ($line->src_no == $_GET['RemoveDN']) {
			$line->quantity = $line->qty_done;
			$line->qty_dispatched=0;
		}
	}
	unset($line);

    // Remove also src_doc delivery note
    $sources = &$_SESSION['Items']->src_docs;
    unset($sources[$_GET['RemoveDN']]);
}

//-----------------------------------------------------------------------------

if ( (isset($_GET['DeliveryNumber']) && ($_GET['DeliveryNumber'] > 0) )
	|| isset($_GET['BatchInvoice'])) {

	processing_start();

	if (isset($_GET['BatchInvoice'])) {
		$src = $_SESSION['DeliveryBatch'];
		unset($_SESSION['DeliveryBatch']);
	} else {
		$src = array($_GET['DeliveryNumber']);
	}

	/*read in all the selected deliveries into the Items cart  */
	$dn = new Cart(ST_CUSTDELIVERY, $src, true);

	if ($dn->count_items() == 0) {
		hyperlink_params($path_to_root . "/sales/inquiry/sales_deliveries_view.php",
			_(UI_TEXT_SELECT_DIFFERENT_DELIVERY_TO_INVOICE), "OutstandingOnly=1");
		die ("<br><b>" . _(UI_TEXT_NO_DELIVERED_ITEMS_LEFT_TO_INVOICE) . "</b>");
	}

	$_SESSION['Items'] = $dn;
	copy_from_cart();

} elseif (isset($_GET['ModifyInvoice']) && $_GET['ModifyInvoice'] > 0) {

	check_is_editable(ST_SALESINVOICE, $_GET['ModifyInvoice']);

	processing_start();
	$_SESSION['Items'] = new Cart(ST_SALESINVOICE, $_GET['ModifyInvoice']);

	if ($_SESSION['Items']->count_items() == 0) {
		echo"<center><br><b>" . _(UI_TEXT_ALL_QUANTITIES_CREDITED) . "</b></center>";
		display_footer_exit();
	}
	copy_from_cart();
} elseif (isset($_GET['AllocationNumber']) || isset($_GET['InvoicePrepayments'])) {

	check_deferred_income_act(_(UI_TEXT_SET_DEFERRED_INCOME_ACCOUNT));

	if (isset($_GET['AllocationNumber']))
	{
		$payments = array(get_cust_allocation($_GET['AllocationNumber']));

		if (!$payments || ($payments[0]['trans_type_to'] != ST_SALESORDER))
		{
			UiMessageService::displayError(_(UI_TEXT_SELECT_CORRECT_PREPAYMENT));
			display_footer_exit();
		}
		$order_no = $payments[0]['trans_no_to'];
	}
	else {
		$order_no = $_GET['InvoicePrepayments'];
	}
	processing_start();

	$_SESSION['Items'] = new cart(ST_SALESORDER, $order_no, ST_SALESINVOICE);
	$_SESSION['Items']->order_no = $order_no;
	$_SESSION['Items']->src_docs = array($order_no);
	$_SESSION['Items']->trans_no = 0;
	$_SESSION['Items']->trans_type = ST_SALESINVOICE;

	$_SESSION['Items']->update_payments();

	copy_from_cart();
}
elseif (!processing_active()) {
	/* This page can only be called with a delivery for invoicing or invoice no for edit */
	UiMessageService::displayError(_(UI_TEXT_PAGE_OPEN_AFTER_DELIVERY_SELECTION));

	hyperlink_no_params("$path_to_root/sales/inquiry/sales_deliveries_view.php", _(UI_TEXT_SELECT_DELIVERY_TO_INVOICE));

	end_page();
	exit;
} elseif (!isset($_POST['process_invoice']) && (!$_SESSION['Items']->is_prepaid() && !check_quantities())) {
	UiMessageService::displayError(_(UI_TEXT_SELECTED_QUANTITY_INVALID));
}

if (isset($_POST['Update'])) {
	$Ajax->activate('Items');
}
if (isset($_POST['_InvoiceDate_changed'])) {
	$_POST['due_date'] = get_invoice_duedate($_SESSION['Items']->payment, $_POST['InvoiceDate']);
	$Ajax->activate('due_date');
}

//-----------------------------------------------------------------------------
function check_quantities()
{
	$ok =1;
	foreach ($_SESSION['Items']->line_items as $line_no=>$itm) {
		if (isset($_POST['Line'.$line_no])) {
			if($_SESSION['Items']->trans_no) {
				$min = $itm->qty_done;
				$max = $itm->quantity;
			} else {
				$min = 0;
				// Fixing floating point problem in PHP.
				$max = round2($itm->quantity - $itm->qty_done, get_qty_dec($itm->stock_id));
			}
			if (check_num('Line'.$line_no, $min, $max)) {
				$_SESSION['Items']->line_items[$line_no]->qty_dispatched =
				    RequestService::inputNumStatic('Line'.$line_no);
			}
			else {
				$ok = 0;
			}
				
		}

		if (isset($_POST['Line'.$line_no.'Desc'])) {
			$line_desc = $_POST['Line'.$line_no.'Desc'];
			if (strlen($line_desc) > 0) {
				$_SESSION['Items']->line_items[$line_no]->item_description = $line_desc;
			}
		}
	}
 return $ok;
}

function set_delivery_shipping_sum($delivery_notes) 
{
    
    $shipping = 0;
    
    foreach($delivery_notes as $delivery_num) 
    {
        $myrow = get_customer_trans($delivery_num, ST_CUSTDELIVERY);

        $shipping += $myrow['ov_freight'];
    }
    $_POST['ChargeFreightCost'] = FormatService::priceFormat($shipping);
}


function copy_to_cart()
{
	$cart = &$_SESSION['Items'];
	$cart->due_date = $cart->document_date =  $_POST['InvoiceDate'];
	$cart->Comments = $_POST['Comments'];
	$cart->due_date =  $_POST['due_date'];
	if (($cart->pos['cash_sale'] || $cart->pos['credit_sale']) && isset($_POST['payment'])) {
		$cart->payment = $_POST['payment'];
		$cart->payment_terms = get_payment_terms($_POST['payment']);
	}
	if ($_SESSION['Items']->trans_no == 0)
		$cart->reference = $_POST['ref'];
	if (!$cart->is_prepaid())
	{
		$cart->ship_via = $_POST['ship_via'];
		$cart->freight_cost = RequestService::inputNumStatic('ChargeFreightCost');
	}

	$cart->update_payments();

	$cart->dimension_id =  $_POST['dimension_id'];
	$cart->dimension2_id =  $_POST['dimension2_id'];
}
//-----------------------------------------------------------------------------

function copy_from_cart()
{
	$cart = &$_SESSION['Items'];
 	$_POST['Comments']= $cart->Comments;
	$_POST['InvoiceDate']= $cart->document_date;
 	$_POST['ref'] = $cart->reference;
	$_POST['cart_id'] = $cart->cart_id;
	$_POST['due_date'] = $cart->due_date;
 	$_POST['payment'] = $cart->payment;
	if (!$_SESSION['Items']->is_prepaid())
	{
		$_POST['ship_via'] = $cart->ship_via;
		$_POST['ChargeFreightCost'] = FormatService::priceFormat($cart->freight_cost);
	}
	$_POST['dimension_id'] = $cart->dimension_id;
	$_POST['dimension2_id'] = $cart->dimension2_id;
}

//-----------------------------------------------------------------------------

function check_data()
{
	global $Refs;

	$prepaid = $_SESSION['Items']->is_prepaid();
	$dateService = new DateService();

	if (!isset($_POST['InvoiceDate']) || !$dateService->isDate($_POST['InvoiceDate'])) {
		UiMessageService::displayError(_(UI_TEXT_ENTERED_INVOICE_DATE_INVALID));
		set_focus('InvoiceDate');
		return false;
	}

	if (!DateService::isDateInFiscalYear($_POST['InvoiceDate'])) {
		UiMessageService::displayError(_(UI_TEXT_ENTERED_DATE_OUT_OF_FISCAL_YEAR));
		set_focus('InvoiceDate');
		return false;
	}


	if (!$prepaid &&(!isset($_POST['due_date']) || !$dateService->isDate($_POST['due_date'])))	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_INVOICE_DUE_DATE_INVALID));
		set_focus('due_date');
		return false;
	}

	if ($_SESSION['Items']->trans_no == 0) {
		if (!$Refs->is_valid($_POST['ref'], ST_SALESINVOICE)) {
			UiMessageService::displayError(_(UI_TEXT_MUST_ENTER_REFERENCE));
			set_focus('ref');
			return false;
		}
	}

	if(!$prepaid) 
	{
		if ($_POST['ChargeFreightCost'] == "") {
			$_POST['ChargeFreightCost'] = FormatService::priceFormat(0);
		}

		if (!check_num('ChargeFreightCost', 0)) {
			UiMessageService::displayError(_(UI_TEXT_ENTERED_SHIPPING_VALUE_NOT_NUMERIC));
			set_focus('ChargeFreightCost');
			return false;
		}

		if ($_SESSION['Items']->has_items_dispatch() == 0 && RequestService::inputNumStatic('ChargeFreightCost') == 0) {
			UiMessageService::displayError(_(UI_TEXT_NO_ITEM_QUANTITIES_ON_INVOICE));
			return false;
		}

		if (!check_quantities()) {
			UiMessageService::displayError(_(UI_TEXT_SELECTED_QUANTITY_INVALID));
			return false;
		}
	} else {
		if (($_SESSION['Items']->payment_terms['days_before_due'] == -1) && !count($_SESSION['Items']->prepayments)) {
			UiMessageService::displayError(_(UI_TEXT_NO_NON_INVOICED_PAYMENTS));
			return false;
		}
	}

	return true;
}

//-----------------------------------------------------------------------------
if (isset($_POST['process_invoice']) && check_data()) {
	$newinvoice=  $_SESSION['Items']->trans_no == 0;
	copy_to_cart();

	if ($newinvoice) 
		DateService::newDocDateStatic($_SESSION['Items']->document_date);

	$invoice_no = $_SESSION['Items']->write();
	if ($invoice_no == -1)
	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_REFERENCE_ALREADY_IN_USE));
		set_focus('ref');
	}
	else
	{
		processing_end();

		if ($newinvoice) {
			meta_forward($_SERVER['PHP_SELF'], "AddedID=$invoice_no");
		} else {
			meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$invoice_no");
		}
	}	
}

if(list_updated('payment')) {
	$order = &$_SESSION['Items']; 
	copy_to_cart();
	$order->payment = RequestService::getPostStatic('payment');
	$order->payment_terms = get_payment_terms($order->payment);
	$_POST['due_date'] = $order->due_date = get_invoice_duedate($order->payment, $order->document_date);
	$_POST['Comments'] = '';
	$Ajax->activate('due_date');
	$Ajax->activate('options');
	if ($order->payment_terms['cash_sale']) {
		$_POST['Location'] = $order->Location = $order->pos['pos_location'];
		$order->location_name = $order->pos['location_name'];
	}
}

// find delivery spans for batch invoice display
$dspans = array();
$lastdn = ''; $spanlen=1;

for ($line_no = 0; $line_no < count($_SESSION['Items']->line_items); $line_no++) {
	$line = $_SESSION['Items']->line_items[$line_no];
	if ($line->quantity == $line->qty_done) {
		continue;
	}
	if ($line->src_no == $lastdn) {
		$spanlen++;
	} else {
		if ($lastdn != '') {
			$dspans[] = $spanlen;
			$spanlen = 1;
		}
	}
	$lastdn = $line->src_no;
}
$dspans[] = $spanlen;

//-----------------------------------------------------------------------------

$is_batch_invoice = count($_SESSION['Items']->src_docs) > 1;
$prepaid = $_SESSION['Items']->is_prepaid();

$is_edition = $_SESSION['Items']->trans_type == ST_SALESINVOICE && $_SESSION['Items']->trans_no != 0;
start_form();
hidden('cart_id');

start_table(TABLESTYLE2, "width='80%'", 5);

start_row();
$colspan = 1;
$dim = \FA\Services\CompanyPrefsService::getUseDimensions();
if ($dim > 0) 
	$colspan = 3;
label_cells(_(UI_TEXT_CUSTOMER), $_SESSION['Items']->customer_name, "class='tableheader2'");
label_cells(_(UI_TEXT_BRANCH), get_branch_name($_SESSION['Items']->Branch), "class='tableheader2'");
if (($_SESSION['Items']->pos['credit_sale'] || $_SESSION['Items']->pos['cash_sale'])) {
	$paymcat = !$_SESSION['Items']->pos['cash_sale'] ? PM_CREDIT :
		(!$_SESSION['Items']->pos['credit_sale'] ? PM_CASH : PM_ANY);
	label_cells(_(UI_TEXT_PAYMENT_TERMS), sale_payment_list('payment', $paymcat),
		"class='tableheader2'", "colspan=$colspan");
} else
	label_cells(_(UI_TEXT_PAYMENT_LABEL), $_SESSION['Items']->payment_terms['terms'], "class='tableheader2'", "colspan=$colspan");

end_row();
start_row();

if ($_SESSION['Items']->trans_no == 0) {
	ref_cells(_(UI_TEXT_REFERENCE), 'ref', '', null, "class='tableheader2'", false, ST_SALESINVOICE,
		array('customer' => $_SESSION['Items']->customer_id,
			'branch' => $_SESSION['Items']->Branch,
			'date' => RequestService::getPostStatic('InvoiceDate')));
} else {
	label_cells(_(UI_TEXT_REFERENCE), $_SESSION['Items']->reference, "class='tableheader2'");
}

label_cells(_(UI_TEXT_SALES_TYPE), $_SESSION['Items']->sales_type_name, "class='tableheader2'");

label_cells(_(UI_TEXT_CURRENCY), $_SESSION['Items']->customer_currency, "class='tableheader2'");
if ($dim > 0) {
	label_cell(_(UI_TEXT_DIMENSION).":", "class='tableheader2'");
	$_POST['dimension_id'] = $_SESSION['Items']->dimension_id;
	dimensions_list_cells(null, 'dimension_id', null, true, ' ', false, 1, false);
}		
else
	hidden('dimension_id', 0);

end_row();
start_row();

if (!isset($_POST['ship_via'])) {
	$_POST['ship_via'] = $_SESSION['Items']->ship_via;
}
label_cell(_(UI_TEXT_SHIPPING_COMPANY), "class='tableheader2'");
if ($prepaid)
{
	$shipper = get_shipper($_SESSION['Items']->ship_via);
	label_cells(null, $shipper['shipper_name']);
} else
	shippers_list_cells(null, 'ship_via', $_POST['ship_via']);

$dateService = new DateService();
if (!isset($_POST['InvoiceDate']) || !$dateService->isDate($_POST['InvoiceDate'])) {
	$_POST['InvoiceDate'] = DateService::newDocDateStatic();
	if (!DateService::isDateInFiscalYear($_POST['InvoiceDate'])) {
		$_POST['InvoiceDate'] = DateService::endFiscalYear();
	}
}

date_cells(_(UI_TEXT_DATE), 'InvoiceDate', '', $_SESSION['Items']->trans_no == 0, 
	0, 0, 0, "class='tableheader2'", true);

if (!isset($_POST['due_date']) || !$dateService->isDate($_POST['due_date'])) {
	$_POST['due_date'] = get_invoice_duedate($_SESSION['Items']->payment, $_POST['InvoiceDate']);
}

date_cells(_(UI_TEXT_DUE_DATE), 'due_date', '', null, 0, 0, 0, "class='tableheader2'");
if ($dim > 1) {
	label_cell(_(UI_TEXT_DIMENSION)." 2:", "class='tableheader2'");
	$_POST['dimension2_id'] = $_SESSION['Items']->dimension2_id;
	dimensions_list_cells(null, 'dimension2_id', null, true, ' ', false, 2, false);
}		
else
	hidden('dimension2_id', 0);
end_row();
end_table();

$row = get_customer_to_order($_SESSION['Items']->customer_id);
if ($row['dissallow_invoices'] == 1)
{
	UiMessageService::displayError(_(UI_TEXT_SELECTED_CUSTOMER_ACCOUNT_ON_HOLD));
	end_form();
	end_page();
	exit();
}	

display_heading($prepaid ? _(UI_TEXT_SALES_ORDER_ITEMS) : _(UI_TEXT_INVOICE_ITEMS));

div_start('Items');

start_table(TABLESTYLE, "width='80%'");
if ($prepaid)
	$th = array(_(UI_TEXT_ITEM_CODE), _(UI_TEXT_ITEM_DESCRIPTION), _(UI_TEXT_UNITS), _(UI_TEXT_QUANTITY),
		_(UI_TEXT_PRICE), _(UI_TEXT_TAX_TYPE), _(UI_TEXT_DISCOUNT), _(UI_TEXT_TOTAL));
else
	$th = array(_(UI_TEXT_ITEM_CODE), _(UI_TEXT_ITEM_DESCRIPTION), _(UI_TEXT_DELIVERED), _(UI_TEXT_UNITS), _(UI_TEXT_INVOICED),
		_(UI_TEXT_THIS_INVOICE), _(UI_TEXT_PRICE), _(UI_TEXT_TAX_TYPE), _(UI_TEXT_DISCOUNT), _(UI_TEXT_TOTAL));

if ($is_batch_invoice) {
    $th[] = _(UI_TEXT_DN);
    $th[] = "";
}

if ($is_edition) {
    $th[4] = _(UI_TEXT_CREDITED);
}

table_header($th);
$k = 0;
$has_marked = false;
$show_qoh = true;

$dn_line_cnt = 0;

foreach ($_SESSION['Items']->line_items as $line=>$ln_itm) {
	if (!$prepaid && ($ln_itm->quantity == $ln_itm->qty_done)) {
		continue; // this line was fully invoiced
	}
	alt_table_row_color($k);
	view_stock_status_cell($ln_itm->stock_id);

	if ($prepaid)
		label_cell($ln_itm->item_description);
	else
		text_cells(null, 'Line'.$line.'Desc', $ln_itm->item_description, 30, 50);
	$dec = get_qty_dec($ln_itm->stock_id);
	if (!$prepaid)
		qty_cell($ln_itm->quantity, false, $dec);
	label_cell($ln_itm->units);
	if (!$prepaid)
		qty_cell($ln_itm->qty_done, false, $dec);

	if ($is_batch_invoice || $prepaid) {
		// for batch invoices we can only remove whole deliveries
		echo '<td nowrap align=right>';
		hidden('Line' . $line, $ln_itm->qty_dispatched );
		echo FormatService::numberFormat2($ln_itm->qty_dispatched, $dec).'</td>';
	} else {
		small_qty_cells(null, 'Line'.$line, qty_format($ln_itm->qty_dispatched, $ln_itm->stock_id, $dec), null, null, $dec);
	}
	$display_discount_percent = \FA\Services\FormatService::percentFormat($ln_itm->discount_percent*100) . " %";

	$line_total = ($ln_itm->qty_dispatched * $ln_itm->price * (1 - $ln_itm->discount_percent));

	amount_cell($ln_itm->price);
	label_cell($ln_itm->tax_type_name);
	label_cell($display_discount_percent, "nowrap align=right");
	amount_cell($line_total);

	if ($is_batch_invoice) {
		if ($dn_line_cnt == 0) {
			$dn_line_cnt = $dspans[0];
			$dspans = array_slice($dspans, 1);
			label_cell($ln_itm->src_no, "rowspan=$dn_line_cnt class='oddrow'");
			label_cell("<a href='" . $_SERVER['PHP_SELF'] . "?RemoveDN=".
				$ln_itm->src_no."'>" . _(UI_TEXT_REMOVE) . "</a>", "rowspan=$dn_line_cnt class='oddrow'");
		}
		$dn_line_cnt--;
	}
	end_row();
}

/*Don't re-calculate freight if some of the order has already been delivered -
depending on the business logic required this condition may not be required.
It seems unfair to charge the customer twice for freight if the order
was not fully delivered the first time ?? */

if (!isset($_POST['ChargeFreightCost']) || $_POST['ChargeFreightCost'] == "") {
	if ($_SESSION['Items']->any_already_delivered() == 1) {
		$_POST['ChargeFreightCost'] = FormatService::priceFormat(0);
	} else {
		$_POST['ChargeFreightCost'] = FormatService::priceFormat($_SESSION['Items']->freight_cost);
	}

	if (!check_num('ChargeFreightCost')) {
		$_POST['ChargeFreightCost'] = FormatService::priceFormat(0);
	}
}

$accumulate_shipping = get_company_pref('accumulate_shipping');
if ($is_batch_invoice && $accumulate_shipping)
	set_delivery_shipping_sum(array_keys($_SESSION['Items']->src_docs));

$colspan = $prepaid ? 7:9;
start_row();
label_cell(_(UI_TEXT_SHIPPING_COST), "colspan=$colspan align=right");
if ($prepaid)
	label_cell($_POST['ChargeFreightCost'], 'align=right');
else
	small_amount_cells(null, 'ChargeFreightCost', null);
if ($is_batch_invoice) {
label_cell('', 'colspan=2');
}

end_row();
$inv_items_total = $_SESSION['Items']->get_items_total_dispatch();

$display_sub_total = FormatService::priceFormat($inv_items_total + RequestService::inputNumStatic('ChargeFreightCost'));

label_row(_(UI_TEXT_SUB_TOTAL), $display_sub_total, "colspan=$colspan align=right","align=right", $is_batch_invoice ? 2 : 0);

$taxes = $_SESSION['Items']->get_taxes(RequestService::inputNumStatic('ChargeFreightCost'));
$tax_total = display_edit_tax_items($taxes, $colspan, $_SESSION['Items']->tax_included, $is_batch_invoice ? 2 : 0);

$display_total = FormatService::priceFormat(($inv_items_total + RequestService::inputNumStatic('ChargeFreightCost') + $tax_total));

label_row(_(UI_TEXT_INVOICE_TOTAL), $display_total, "colspan=$colspan align=right","align=right", $is_batch_invoice ? 2 : 0);

end_table(1);
div_end();
div_start('options');
start_table(TABLESTYLE2);
if ($prepaid)
{

	label_row(_(UI_TEXT_SALES_ORDER), get_trans_view_str(ST_SALESORDER, $_SESSION['Items']->order_no, get_reference(ST_SALESORDER, $_SESSION['Items']->order_no)));

	$list = array(); $allocs = 0;
	if (count($_SESSION['Items']->prepayments))
	{
		foreach($_SESSION['Items']->prepayments as $pmt)
		{
			$list[] = get_trans_view_str($pmt['trans_type_from'], $pmt['trans_no_from'], get_reference($pmt['trans_type_from'], $pmt['trans_no_from']));
			$allocs += $pmt['amt'];
		}
	}
	label_row(_(UI_TEXT_PAYMENTS_RECEIVED), implode(',', $list));
	label_row(_(UI_TEXT_INVOICED_HERE), FormatService::priceFormat($_SESSION['Items']->prep_amount), 'class=label');
	label_row($_SESSION['Items']->payment_terms['days_before_due'] == -1 ? _(UI_TEXT_LEFT_TO_BE_INVOICED) : _(UI_TEXT_INVOICED_SO_FAR),
		FormatService::priceFormat($_SESSION['Items']->get_trans_total()-max($_SESSION['Items']->prep_amount, $allocs)), 'class=label');
}

textarea_row(_(UI_TEXT_MEMO), 'Comments', null, 50, 4);

end_table(1);
div_end();
submit_center_first('Update', _(UI_TEXT_UPDATE_BUTTON),
  _(UI_TEXT_REFRESH_DOCUMENT_PAGE), true);
submit_center_last('process_invoice', _(UI_TEXT_PROCESS_INVOICE),
  _(UI_TEXT_CHECK_ENTERED_DATA_AND_SAVE_DOCUMENT), 'default');

end_form();

end_page();

