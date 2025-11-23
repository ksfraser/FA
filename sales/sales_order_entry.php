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
//	Entry/Modify Sales Quotations
//	Entry/Modify Sales Order
//	Entry Direct Delivery
//	Entry Direct Invoice
//

if (!isset($path_to_root)) $path_to_root = "..";
$page_security = 'SA_SALESORDER';

include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
require_once($path_to_root . "/includes/DateService.php");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/includes/ui_strings.php");

// Modern OOP Services
use FA\Services\BankingService;

set_page_security( @$_SESSION['Items']->trans_type,
	array(	ST_SALESORDER=>'SA_SALESORDER',
			ST_SALESQUOTE => 'SA_SALESQUOTE',
			ST_CUSTDELIVERY => 'SA_SALESDELIVERY',
			ST_SALESINVOICE => 'SA_SALESINVOICE'),
	array(	'NewOrder' => 'SA_SALESORDER',
			'ModifyOrderNumber' => 'SA_SALESORDER',
			'AddedID' => 'SA_SALESORDER',
			'UpdatedID' => 'SA_SALESORDER',
			'NewQuotation' => 'SA_SALESQUOTE',
			'ModifyQuotationNumber' => 'SA_SALESQUOTE',
			'NewQuoteToSalesOrder' => 'SA_SALESQUOTE',
			'AddedQU' => 'SA_SALESQUOTE',
			'UpdatedQU' => 'SA_SALESQUOTE',
			'NewDelivery' => 'SA_SALESDELIVERY',
			'AddedDN' => 'SA_SALESDELIVERY', 
			'NewInvoice' => 'SA_SALESINVOICE',
			'AddedDI' => 'SA_SALESINVOICE'
			)
);

$js = '';

if ($SysPrefs->use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}

if (user_use_date_picker()) {
	$js .= get_js_date_picker();
}

if (isset($_GET['NewDelivery']) && is_numeric($_GET['NewDelivery'])) {

	$_SESSION['page_title'] = _($help_context = "Direct Sales Delivery");
	create_cart(ST_CUSTDELIVERY, $_GET['NewDelivery']);

} elseif (isset($_GET['NewInvoice']) && is_numeric($_GET['NewInvoice'])) {

	create_cart(ST_SALESINVOICE, $_GET['NewInvoice']);

	if (isset($_GET['FixedAsset'])) {
		$_SESSION['page_title'] = _($help_context = "Fixed Assets Sale");
		$_SESSION['Items']->fixed_asset = true;
  	} else
		$_SESSION['page_title'] = _($help_context = "Direct Sales Invoice");

} elseif (isset($_GET['ModifyOrderNumber']) && is_numeric($_GET['ModifyOrderNumber'])) {

	$help_context = 'Modifying Sales Order';
	$_SESSION['page_title'] = sprintf( _(UI_TEXT_MODIFYING_SALES_ORDER), $_GET['ModifyOrderNumber']);
	create_cart(ST_SALESORDER, $_GET['ModifyOrderNumber']);

} elseif (isset($_GET['ModifyQuotationNumber']) && is_numeric($_GET['ModifyQuotationNumber'])) {

	$help_context = 'Modifying Sales Quotation';
	$_SESSION['page_title'] = sprintf( _(UI_TEXT_MODIFYING_SALES_QUOTATION), $_GET['ModifyQuotationNumber']);
	create_cart(ST_SALESQUOTE, $_GET['ModifyQuotationNumber']);

} elseif (isset($_GET['NewOrder'])) {

	$_SESSION['page_title'] = _($help_context = "New Sales Order Entry");
	create_cart(ST_SALESORDER, 0);
} elseif (isset($_GET['NewQuotation'])) {

	$_SESSION['page_title'] = _($help_context = "New Sales Quotation Entry");
	create_cart(ST_SALESQUOTE, 0);
} elseif (isset($_GET['NewQuoteToSalesOrder'])) {
	$_SESSION['page_title'] = _($help_context = "Sales Order Entry");
	create_cart(ST_SALESQUOTE, $_GET['NewQuoteToSalesOrder']);
}

page($_SESSION['page_title'], false, false, "", $js);

if (isset($_GET['ModifyOrderNumber']) && is_prepaid_order_open($_GET['ModifyOrderNumber']))
{
	UiMessageService::displayError(_(UI_TEXT_ORDER_CANNOT_EDIT));
	end_page(); exit;
}
if (isset($_GET['ModifyOrderNumber']))
	check_is_editable(ST_SALESORDER, $_GET['ModifyOrderNumber']);
elseif (isset($_GET['ModifyQuotationNumber']))
	check_is_editable(ST_SALESQUOTE, $_GET['ModifyQuotationNumber']);

//-----------------------------------------------------------------------------

if (list_updated('branch_id')) {
	// when branch is selected via external editor also customer can change
	$br = get_branch(RequestService::getPostStatic('branch_id'));
	$_POST['customer_id'] = $br['debtor_no'];
	$Ajax->activate('customer_id');
}

if (isset($_GET['AddedID'])) {
	$order_no = $_GET['AddedID'];

	display_notification_centered(sprintf( _(UI_TEXT_ORDER_ENTERED),$order_no));

	submenu_view(_(UI_TEXT_VIEW_THIS_ORDER), ST_SALESORDER, $order_no);

	submenu_print(_(UI_TEXT_PRINT_THIS_ORDER), ST_SALESORDER, $order_no, 'prtopt');
	submenu_print(_(UI_TEXT_EMAIL_THIS_ORDER), ST_SALESORDER, $order_no, null, 1);
	set_focus('prtopt');
	
	submenu_option(_(UI_TEXT_MAKE_DELIVERY_AGAINST_ORDER),
		"/sales/customer_delivery.php?OrderNumber=$order_no");

	submenu_option(_(UI_TEXT_WORK_ORDER_ENTRY),	"/manufacturing/work_order_entry.php?");

	submenu_option(_(UI_TEXT_ENTER_NEW_ORDER),	"/sales/sales_order_entry.php?NewOrder=0");

	$order = get_sales_order_header($order_no, ST_SALESORDER);
	$customer_id = $order['debtor_no'];	
	if ($order['prep_amount'] > 0)
	{
		$row = db_fetch(db_query(get_allocatable_sales_orders($customer_id, $order_no, ST_SALESORDER)));
		if ($row === false)
			submenu_option(_(UI_TEXT_RECEIVE_CUSTOMER_PAYMENT), "/sales/customer_payments.php?customer_id=$customer_id");
	}
	submenu_option(_(UI_TEXT_ADD_ATTACHMENT), "/admin/attachments.php?filterType=".ST_SALESORDER."&trans_no=$order_no");

	display_footer_exit();

} elseif (isset($_GET['UpdatedID'])) {
	$order_no = $_GET['UpdatedID'];

	display_notification_centered(sprintf( _(UI_TEXT_ORDER_UPDATED),$order_no));

	submenu_view(_(UI_TEXT_VIEW_THIS_ORDER), ST_SALESORDER, $order_no);

	submenu_print(_(UI_TEXT_PRINT_THIS_ORDER), ST_SALESORDER, $order_no, 'prtopt');
	submenu_print(_(UI_TEXT_EMAIL_THIS_ORDER), ST_SALESORDER, $order_no, null, 1);
	set_focus('prtopt');

	submenu_option(_(UI_TEXT_CONFIRM_QUANTITIES_MAKE_DELIVERY),
		"/sales/customer_delivery.php?OrderNumber=$order_no");

	submenu_option(_(UI_TEXT_SELECT_DIFFERENT_ORDER),
		"/sales/inquiry/sales_orders_view.php?OutstandingOnly=1");

	display_footer_exit();

} elseif (isset($_GET['AddedQU'])) {
	$order_no = $_GET['AddedQU'];
	display_notification_centered(sprintf( _(UI_TEXT_QUOTATION_ENTERED),$order_no));

	submenu_view(_(UI_TEXT_VIEW_THIS_QUOTATION), ST_SALESQUOTE, $order_no);

	submenu_print(_(UI_TEXT_PRINT_THIS_QUOTATION), ST_SALESQUOTE, $order_no, 'prtopt');
	submenu_print(_(UI_TEXT_EMAIL_THIS_QUOTATION), ST_SALESQUOTE, $order_no, null, 1);
	set_focus('prtopt');
	
	submenu_option(_(UI_TEXT_MAKE_SALES_ORDER_AGAINST_QUOTATION),
		"/sales/sales_order_entry.php?NewQuoteToSalesOrder=$order_no");

	submenu_option(_(UI_TEXT_ENTER_NEW_QUOTATION),	"/sales/sales_order_entry.php?NewQuotation=0");

	submenu_option(_(UI_TEXT_ADD_ATTACHMENT), "/admin/attachments.php?filterType=".ST_SALESQUOTE."&trans_no=$order_no");

	display_footer_exit();

} elseif (isset($_GET['UpdatedQU'])) {
	$order_no = $_GET['UpdatedQU'];

	display_notification_centered(sprintf( _(UI_TEXT_QUOTATION_UPDATED),$order_no));

	submenu_view(_(UI_TEXT_VIEW_THIS_QUOTATION), ST_SALESQUOTE, $order_no);

	submenu_print(_(UI_TEXT_PRINT_THIS_QUOTATION), ST_SALESQUOTE, $order_no, 'prtopt');
	submenu_print(_(UI_TEXT_EMAIL_THIS_QUOTATION), ST_SALESQUOTE, $order_no, null, 1);
	set_focus('prtopt');

	submenu_option(_(UI_TEXT_MAKE_SALES_ORDER_AGAINST_QUOTATION),
		"/sales/sales_order_entry.php?NewQuoteToSalesOrder=$order_no");

	submenu_option(_(UI_TEXT_SELECT_DIFFERENT_QUOTATION),
		"/sales/inquiry/sales_orders_view.php?type=".ST_SALESQUOTE);

	display_footer_exit();
} elseif (isset($_GET['AddedDN'])) {
	$delivery = $_GET['AddedDN'];

	display_notification_centered(sprintf(_(UI_TEXT_DELIVERY_ENTERED),$delivery));

	submenu_view(_(UI_TEXT_VIEW_THIS_DELIVERY), ST_CUSTDELIVERY, $delivery);

	submenu_print(_(UI_TEXT_PRINT_DELIVERY_NOTE), ST_CUSTDELIVERY, $delivery, 'prtopt');
	submenu_print(_(UI_TEXT_EMAIL_DELIVERY_NOTE), ST_CUSTDELIVERY, $delivery, null, 1);
	submenu_print(_(UI_TEXT_PRINT_PACKING_SLIP), ST_CUSTDELIVERY, $delivery, 'prtopt', null, 1);
	submenu_print(_(UI_TEXT_EMAIL_PACKING_SLIP), ST_CUSTDELIVERY, $delivery, null, 1, 1);
	set_focus('prtopt');

	display_note(get_gl_view_str(ST_CUSTDELIVERY, $delivery, _(UI_TEXT_VIEW_GL_JOURNAL_DISPATCH)),0, 1);

	submenu_option(_(UI_TEXT_MAKE_INVOICE_AGAINST_DELIVERY),
		"/sales/customer_invoice.php?DeliveryNumber=$delivery");

	if ((isset($_GET['Type']) && $_GET['Type'] == 1))
		submenu_option(_(UI_TEXT_ENTER_NEW_TEMPLATE_DELIVERY),
			"/sales/inquiry/sales_orders_view.php?DeliveryTemplates=Yes");
	else
		submenu_option(_(UI_TEXT_ENTER_NEW_DELIVERY), 
			"/sales/sales_order_entry.php?NewDelivery=0");

	submenu_option(_(UI_TEXT_ADD_ATTACHMENT), "/admin/attachments.php?filterType=".ST_CUSTDELIVERY."&trans_no=$delivery");

	display_footer_exit();

} elseif (isset($_GET['AddedDI'])) {
	$invoice = $_GET['AddedDI'];

	display_notification_centered(sprintf(_(UI_TEXT_INVOICE_ENTERED), $invoice));

	submenu_view(_(UI_TEXT_VIEW_THIS_INVOICE), ST_SALESINVOICE, $invoice);

	submenu_print(_(UI_TEXT_PRINT_SALES_INVOICE), ST_SALESINVOICE, $invoice."-".ST_SALESINVOICE, 'prtopt');
	submenu_print(_(UI_TEXT_EMAIL_SALES_INVOICE), ST_SALESINVOICE, $invoice."-".ST_SALESINVOICE, null, 1);
	set_focus('prtopt');

	$row = db_fetch(get_allocatable_from_cust_transactions(null, $invoice, ST_SALESINVOICE));
	if ($row !== false)
		submenu_print(_(UI_TEXT_PRINT_RECEIPT), $row['type'], $row['trans_no']."-".$row['type'], 'prtopt');

	display_note(get_gl_view_str(ST_SALESINVOICE, $invoice, _(UI_TEXT_VIEW_GL_JOURNAL_INVOICE)),0, 1);

	if ((isset($_GET['Type']) && $_GET['Type'] == 1))
		submenu_option(_(UI_TEXT_ENTER_NEW_TEMPLATE_INVOICE), 
			"/sales/inquiry/sales_orders_view.php?InvoiceTemplates=Yes");
	else
		submenu_option(_(UI_TEXT_ENTER_NEW_DIRECT_INVOICE),
			"/sales/sales_order_entry.php?NewInvoice=0");

	if ($row === false)
		submenu_option(_(UI_TEXT_ENTRY_CUSTOMER_PAYMENT_INVOICE), "/sales/customer_payments.php?SInvoice=".$invoice);

	submenu_option(_(UI_TEXT_ADD_ATTACHMENT), "/admin/attachments.php?filterType=".ST_SALESINVOICE."&trans_no=$invoice");

	display_footer_exit();
} else
	check_edit_conflicts(RequestService::getPostStatic('cart_id'));
//-----------------------------------------------------------------------------

function copy_to_cart()
{
	$cart = &$_SESSION['Items'];

	$cart->reference = RequestService::getPostStatic('ref');

	$cart->Comments =  $_POST['Comments'];

	$cart->document_date = $_POST['OrderDate'];

	$newpayment = false;

	if (isset($_POST['payment']) && ($cart->payment != $_POST['payment'])) {
		$cart->payment = $_POST['payment'];
		$cart->payment_terms = get_payment_terms($_POST['payment']);
		$newpayment = true;
	}
	if ($cart->payment_terms['cash_sale']) {
		if ($newpayment) {
			$cart->due_date = $cart->document_date;
			$cart->phone = $cart->cust_ref = $cart->delivery_address = '';
			$cart->ship_via = 0;
			$cart->deliver_to = '';
			$cart->prep_amount = 0;
		}
	} else {
		$cart->due_date = $_POST['delivery_date'];
		$cart->cust_ref = $_POST['cust_ref'];
		$cart->deliver_to = $_POST['deliver_to'];
		$cart->delivery_address = $_POST['delivery_address'];
		$cart->phone = $_POST['phone'];
		$cart->ship_via = $_POST['ship_via'];
		if (!$cart->trans_no || ($cart->trans_type == ST_SALESORDER && !$cart->is_started()))
			$cart->prep_amount = RequestService::inputNumStatic('prep_amount', 0);
	}
	$cart->Location = $_POST['Location'];
	$cart->freight_cost = RequestService::inputNumStatic('freight_cost');
	if (isset($_POST['email']))
		$cart->email =$_POST['email'];
	else
		$cart->email = '';
	$cart->customer_id	= $_POST['customer_id'];
	$cart->Branch = $_POST['branch_id'];
	$cart->sales_type = $_POST['sales_type'];

	if ($cart->trans_type!=ST_SALESORDER && $cart->trans_type!=ST_SALESQUOTE) { // 2008-11-12 Joe Hunt
		$cart->dimension_id = $_POST['dimension_id'];
		$cart->dimension2_id = $_POST['dimension2_id'];
	}
	$cart->ex_rate = RequestService::inputNumStatic('_ex_rate', null);
}

//-----------------------------------------------------------------------------

function copy_from_cart()
{
	$cart = &$_SESSION['Items'];
	$_POST['ref'] = $cart->reference;
	$_POST['Comments'] = $cart->Comments;

	$_POST['OrderDate'] = $cart->document_date;
	$_POST['delivery_date'] = $cart->due_date;
	$_POST['cust_ref'] = $cart->cust_ref;
	$_POST['freight_cost'] = FormatService::priceFormat($cart->freight_cost);

	$_POST['deliver_to'] = $cart->deliver_to;
	$_POST['delivery_address'] = $cart->delivery_address;
	$_POST['phone'] = $cart->phone;
	$_POST['Location'] = $cart->Location;
	$_POST['ship_via'] = $cart->ship_via;

	$_POST['customer_id'] = $cart->customer_id;

	$_POST['branch_id'] = $cart->Branch;
	$_POST['sales_type'] = $cart->sales_type;
	$_POST['prep_amount'] = FormatService::priceFormat($cart->prep_amount);
	// POS 
	$_POST['payment'] = $cart->payment;
	if ($cart->trans_type!=ST_SALESORDER && $cart->trans_type!=ST_SALESQUOTE) { // 2008-11-12 Joe Hunt
		$_POST['dimension_id'] = $cart->dimension_id;
		$_POST['dimension2_id'] = $cart->dimension2_id;
	}
	$_POST['cart_id'] = $cart->cart_id;
	$_POST['_ex_rate'] = $cart->ex_rate;
}
//--------------------------------------------------------------------------------

function line_start_focus() {
  	global 	$Ajax;

  	$Ajax->activate('items_table');
  	set_focus('_stock_id_edit');
}

//--------------------------------------------------------------------------------
function can_process() {

	global $Refs, $SysPrefs;

	copy_to_cart();

	if (!RequestService::getPostStatic('customer_id')) 
	{
		UiMessageService::displayError(_(UI_TEXT_NO_CUSTOMER_SELECTED));
		set_focus('customer_id');
		return false;
	} 
	
	if (!RequestService::getPostStatic('branch_id')) 
	{
		UiMessageService::displayError(_(UI_TEXT_CUSTOMER_NO_BRANCH));
		set_focus('branch_id');
		return false;
	} 
	
	if (!DateService::isDate($_POST['OrderDate'])) {
		UiMessageService::displayError(_(UI_TEXT_DATE_INVALID));
		set_focus('OrderDate');
		return false;
	}
	if ($_SESSION['Items']->trans_type!=ST_SALESORDER && $_SESSION['Items']->trans_type!=ST_SALESQUOTE && !DateService::isDateInFiscalYear($_POST['OrderDate'])) {
		UiMessageService::displayError(_(UI_TEXT_DATE_OUT_OF_FISCAL_YEAR));
		set_focus('OrderDate');
		return false;
	}
	if (count($_SESSION['Items']->line_items) == 0)	{
		UiMessageService::displayError(_(UI_TEXT_MUST_ENTER_ITEM_LINE));
		set_focus('AddItem');
		return false;
	}
	if (!$SysPrefs->allow_negative_stock() && ($low_stock = $_SESSION['Items']->check_qoh()))
	{
		UiMessageService::displayError(_(UI_TEXT_INSUFFICIENT_QUANTITY));
		return false;
	}
	if ($_SESSION['Items']->payment_terms['cash_sale'] == 0) {
		if (!$_SESSION['Items']->is_started() && ($_SESSION['Items']->payment_terms['days_before_due'] == -1) && ((RequestService::inputNumStatic('prep_amount')<=0) ||
			RequestService::inputNumStatic('prep_amount')>$_SESSION['Items']->get_trans_total())) {
			UiMessageService::displayError(_(UI_TEXT_PREPAYMENT_POSITIVE_LESS_TOTAL));
			set_focus('prep_amount');
			return false;
		}
		if (strlen($_POST['deliver_to']) <= 1) {
			UiMessageService::displayError(_(UI_TEXT_MUST_ENTER_DELIVERY_PERSON));
			set_focus('deliver_to');
			return false;
		}

		if ($_SESSION['Items']->trans_type != ST_SALESQUOTE && strlen($_POST['delivery_address']) <= 1) {
			UiMessageService::displayError( _(UI_TEXT_STREET_ADDRESS_REQUIRED));
			set_focus('delivery_address');
			return false;
		}

		if ($_POST['freight_cost'] == "")
			$_POST['freight_cost'] = FormatService::priceFormat(0);

		if (!check_num('freight_cost',0)) {
			UiMessageService::displayError(_(UI_TEXT_SHIPPING_COST_NUMERIC));
			set_focus('freight_cost');
			return false;
		}
		if (!DateService::isDate($_POST['delivery_date'])) {
			if ($_SESSION['Items']->trans_type==ST_SALESQUOTE)
				UiMessageService::displayError(_(UI_TEXT_VALID_DATE_INVALID));
			else	
				UiMessageService::displayError(_(UI_TEXT_DELIVERY_DATE_INVALID));
			set_focus('delivery_date');
			return false;
		}
		if (DateService::date1GreaterDate2Static($_POST['OrderDate'], $_POST['delivery_date'])) {
			if ($_SESSION['Items']->trans_type==ST_SALESQUOTE)
				UiMessageService::displayError(_(UI_TEXT_VALID_DATE_BEFORE_QUOTATION));
			else	
				UiMessageService::displayError(_(UI_TEXT_DELIVERY_DATE_BEFORE_ORDER));
			set_focus('delivery_date');
			return false;
		}
	}
	else
	{
		if (!db_has_cash_accounts())
		{
			UiMessageService::displayError(_(UI_TEXT_DEFINE_CASH_ACCOUNT));
			return false;
		}	
	}	
	if (!$Refs->is_valid($_POST['ref'], $_SESSION['Items']->trans_type)) {
		UiMessageService::displayError(_(UI_TEXT_MUST_ENTER_REFERENCE));
		set_focus('ref');
		return false;
	}
	if (!db_has_currency_rates($_SESSION['Items']->customer_currency, $_POST['OrderDate']))
		return false;
	
   	if ($_SESSION['Items']->get_items_total() < 0) {
		UiMessageService::displayError("Invoice total amount cannot be less than zero.");
		return false;
	}

	if ($_SESSION['Items']->payment_terms['cash_sale'] && 
		($_SESSION['Items']->trans_type == ST_CUSTDELIVERY || $_SESSION['Items']->trans_type == ST_SALESINVOICE)) 
		$_SESSION['Items']->due_date = $_SESSION['Items']->document_date;
	return true;
}

//-----------------------------------------------------------------------------

if (isset($_POST['update'])) {
	copy_to_cart();
	$Ajax->activate('items_table');
}

if (isset($_POST['ProcessOrder']) && can_process()) {

	$modified = ($_SESSION['Items']->trans_no != 0);
	$so_type = $_SESSION['Items']->so_type;

	$ret = $_SESSION['Items']->write(1);
	if ($ret == -1)
	{
		UiMessageService::displayError(_(UI_TEXT_REFERENCE_ALREADY_IN_USE));
		$ref = $Refs->get_next($_SESSION['Items']->trans_type, null, array('date' => DateService::todayStatic()));
		if ($ref != $_SESSION['Items']->reference)
		{
			unset($_POST['ref']); // force refresh reference
			UiMessageService::displayError(_(UI_TEXT_REFERENCE_FIELD_INCREASED));
		}
		set_focus('ref');
	}
	else
	{
		if (count($messages)) { // abort on failure or error messages are lost
			$Ajax->activate('_page_body');
			display_footer_exit();
		}
		$trans_no = key($_SESSION['Items']->trans_no);
		$trans_type = $_SESSION['Items']->trans_type;
		DateService::newDocDateStatic($_SESSION['Items']->document_date);
		processing_end();
		if ($modified) {
			if ($trans_type == ST_SALESQUOTE)
				meta_forward($_SERVER['PHP_SELF'], "UpdatedQU=$trans_no");
			else	
				meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$trans_no");
		} elseif ($trans_type == ST_SALESORDER) {
			meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
		} elseif ($trans_type == ST_SALESQUOTE) {
			meta_forward($_SERVER['PHP_SELF'], "AddedQU=$trans_no");
		} elseif ($trans_type == ST_SALESINVOICE) {
			meta_forward($_SERVER['PHP_SELF'], "AddedDI=$trans_no&Type=$so_type");
		} else {
			meta_forward($_SERVER['PHP_SELF'], "AddedDN=$trans_no&Type=$so_type");
		}
	}	
}

//--------------------------------------------------------------------------------

function check_item_data()
{
	global $SysPrefs;
	
	$is_inventory_item = InventoryService::isInventoryItem(RequestService::getPostStatic('stock_id'));
	if(!RequestService::getPostStatic('stock_id_text', true)) {
		UiMessageService::displayError( _(UI_TEXT_ITEM_DESCRIPTION_EMPTY));
		set_focus('stock_id_edit');
		return false;
	}
	elseif (!check_num('qty', 0) || !check_num('Disc', 0, 100)) {
		UiMessageService::displayError( _(UI_TEXT_QUANTITY_DISCOUNT_INVALID));
		set_focus('qty');
		return false;
	} elseif (!check_num('price', 0) && (!$SysPrefs->allow_negative_prices() || $is_inventory_item)) {
		UiMessageService::displayError( _(UI_TEXT_PRICE_MUST_BE_ENTERED));
		set_focus('price');
		return false;
	} elseif (isset($_POST['LineNo']) && isset($_SESSION['Items']->line_items[$_POST['LineNo']])
	    && !check_num('qty', $_SESSION['Items']->line_items[$_POST['LineNo']]->qty_done)) {

		set_focus('qty');
		UiMessageService::displayError(_(UI_TEXT_QUANTITY_LESS_THAN_DELIVERED));
		return false;
	}

	$cost_home = get_unit_cost(RequestService::getPostStatic('stock_id')); // Added 2011-03-27 Joe Hunt
	$bankingService = new BankingService();
	$cost = $cost_home / $bankingService->getExchangeRateFromHomeCurrency($_SESSION['Items']->customer_currency, $_SESSION['Items']->document_date);
	if (RequestService::inputNumStatic('price') < $cost)
	{
		$dec = \FA\UserPrefsCache::getPriceDecimals();
		$curr = $_SESSION['Items']->customer_currency;
		$price = FormatService::numberFormat2(RequestService::inputNumStatic('price'), $dec);
		if ($cost_home == $cost)
			$std_cost = FormatService::numberFormat2($cost_home, $dec);
		else
		{
			$price = $curr . " " . $price;
			$std_cost = $curr . " " . FormatService::numberFormat2($cost, $dec);
		}
		\FA\Services\UiMessageService::displayWarning(sprintf(_(UI_TEXT_PRICE_S_IS_BELOW_STANDARD_COST_S), $price, $std_cost));
	}	
	return true;
}

//--------------------------------------------------------------------------------

function handle_update_item()
{
	if ($_POST['UpdateItem'] != '' && check_item_data()) {
		$_SESSION['Items']->update_cart_item($_POST['LineNo'],
		 RequestService::inputNumStatic('qty'), RequestService::inputNumStatic('price'),
		 RequestService::inputNumStatic('Disc') / 100, $_POST['item_description'] );
	}
	page_modified();
  line_start_focus();
}

//--------------------------------------------------------------------------------

function handle_delete_item($line_no)
{
    if ($_SESSION['Items']->some_already_delivered($line_no) == 0) {
	    $_SESSION['Items']->remove_from_cart($line_no);
    } else {
		UiMessageService::displayError(_(UI_TEXT_THIS_ITEM_CANNOT_BE_DELETED_BECAUSE_SOME_OF_IT_HAS_ALREADY_BEEN_DELIVERED));
    }
    line_start_focus();
}

//--------------------------------------------------------------------------------

function handle_new_item()
{

	if (!check_item_data()) {
			return;
	}
	add_to_order($_SESSION['Items'], RequestService::getPostStatic('stock_id'), RequestService::inputNumStatic('qty'),
		RequestService::inputNumStatic('price'), RequestService::inputNumStatic('Disc') / 100, RequestService::getPostStatic('stock_id_text'));

	unset($_POST['_stock_id_edit'], $_POST['stock_id']);
	page_modified();
	line_start_focus();
}

//--------------------------------------------------------------------------------

function  handle_cancel_order()
{
	global $path_to_root, $Ajax;


	if ($_SESSION['Items']->trans_type == ST_CUSTDELIVERY) {
		\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_DIRECT_DELIVERY_ENTRY_HAS_BEEN_CANCELLED_AS_REQUESTED), 1);
		submenu_option(_(UI_TEXT_ENTER_A_NEW_SALES_DELIVERY),	"/sales/sales_order_entry.php?NewDelivery=1");
	} elseif ($_SESSION['Items']->trans_type == ST_SALESINVOICE) {
		\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_DIRECT_INVOICE_ENTRY_HAS_BEEN_CANCELLED_AS_REQUESTED), 1);
		submenu_option(_(UI_TEXT_ENTER_A_NEW_SALES_INVOICE),	"/sales/sales_order_entry.php?NewInvoice=1");
	} elseif ($_SESSION['Items']->trans_type == ST_SALESQUOTE)
	{
		if ($_SESSION['Items']->trans_no != 0) 
			delete_sales_order(key($_SESSION['Items']->trans_no), $_SESSION['Items']->trans_type);
		\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_THIS_SALES_QUOTATION_HAS_BEEN_CANCELLED_AS_REQUESTED), 1);
		submenu_option(_(UI_TEXT_ENTER_A_NEW_SALES_QUOTATION), "/sales/sales_order_entry.php?NewQuotation=Yes");
	} else { // sales order
		if ($_SESSION['Items']->trans_no != 0) {
			$order_no = key($_SESSION['Items']->trans_no);
			if (sales_order_has_deliveries($order_no))
			{
				close_sales_order($order_no);
				\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_UNDELIVERED_PART_OF_ORDER_HAS_BEEN_CANCELLED_AS_REQUESTED), 1);
				submenu_option(_(UI_TEXT_SELECT_ANOTHER_SALES_ORDER_FOR_EDITION), "/sales/inquiry/sales_orders_view.php?type=".ST_SALESORDER);
			} else {
				delete_sales_order(key($_SESSION['Items']->trans_no), $_SESSION['Items']->trans_type);

				\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_THIS_SALES_ORDER_HAS_BEEN_CANCELLED_AS_REQUESTED), 1);
				submenu_option(_(UI_TEXT_ENTER_A_NEW_SALES_ORDER), "/sales/sales_order_entry.php?NewOrder=Yes");
			}
		} else {
			processing_end();
			meta_forward($path_to_root.'/index.php','application=orders');
		}
	}
	processing_end();
	display_footer_exit();
}

//--------------------------------------------------------------------------------

function create_cart($type, $trans_no)
{ 
	global $Refs, $SysPrefs;

	if (!$SysPrefs->db_ok) // create_cart is called before page() where the check is done
		return;

	processing_start();

	if (isset($_GET['NewQuoteToSalesOrder']))
	{
		$trans_no = $_GET['NewQuoteToSalesOrder'];
		$doc = new Cart(ST_SALESQUOTE, $trans_no, true);
		$doc->Comments = _(UI_TEXT_SALES_QUOTATION) . " # " . $trans_no;
		$_SESSION['Items'] = $doc;
	}	
	elseif($type != ST_SALESORDER && $type != ST_SALESQUOTE && $trans_no != 0) { // this is template

		$doc = new Cart(ST_SALESORDER, array($trans_no));
		$doc->trans_type = $type;
		$doc->trans_no = 0;
		$doc->document_date = DateService::newDocDateStatic();
		if ($type == ST_SALESINVOICE) {
			$doc->due_date = get_invoice_duedate($doc->payment, $doc->document_date);
			$doc->pos = get_sales_point(user_pos());
		} else
			$doc->due_date = $doc->document_date;
		$doc->reference = $Refs->get_next($doc->trans_type, null, array('date' => DateService::todayStatic()));
		//$doc->Comments='';
		foreach($doc->line_items as $line_no => $line) {
			$doc->line_items[$line_no]->qty_done = 0;
		}
		$_SESSION['Items'] = $doc;
	} else
		$_SESSION['Items'] = new Cart($type, array($trans_no));
	copy_from_cart();
}

//--------------------------------------------------------------------------------

if (isset($_POST['CancelOrder']))
	handle_cancel_order();

$id = find_submit('Delete');
if ($id!=-1)
	handle_delete_item($id);

if (isset($_POST['UpdateItem']))
	handle_update_item();

if (isset($_POST['AddItem']))
	handle_new_item();

if (isset($_POST['CancelItemChanges'])) {
	line_start_focus();
}

//--------------------------------------------------------------------------------
if ($_SESSION['Items']->fixed_asset)
	check_db_has_disposable_fixed_assets(_("There are no fixed assets defined in the system."));
else
	check_db_has_stock_items(_("There are no inventory items defined in the system."));

check_db_has_customer_branches(_(UI_TEXT_THERE_ARE_NO_CUSTOMERS_OR_THERE_ARE_NO_CUSTOMERS_WITH_BRANCHES_PLEASE_DEFINE_CUSTOMERS_AND_CUSTOMER_BRANCHES));

if ($_SESSION['Items']->trans_type == ST_SALESINVOICE) {
	$idate = _(UI_TEXT_INVOICE_DATE_LABEL);
	$orderitems = _(UI_TEXT_SALES_INVOICE_ITEMS);
	$deliverydetails = _(UI_TEXT_ENTER_DELIVERY_DETAILS_AND_CONFIRM_INVOICE);
	$cancelorder = _(UI_TEXT_CANCEL_INVOICE);
	$porder = _(UI_TEXT_PLACE_INVOICE);
} elseif ($_SESSION['Items']->trans_type == ST_CUSTDELIVERY) {
	$idate = _("Delivery Date:");
	$orderitems = _("Delivery Note Items");
	$deliverydetails = _("Enter Delivery Details and Confirm Dispatch");
	$cancelorder = _("Cancel Delivery");
	$porder = _("Place Delivery");
} elseif ($_SESSION['Items']->trans_type == ST_SALESQUOTE) {
	$idate = _("Quotation Date:");
	$orderitems = _("Sales Quotation Items");
	$deliverydetails = _("Enter Delivery Details and Confirm Quotation");
	$cancelorder = _("Cancel Quotation");
	$porder = _("Place Quotation");
	$corder = _("Commit Quotations Changes");
} else {
	$idate = _("Order Date:");
	$orderitems = _("Sales Order Items");
	$deliverydetails = _("Enter Delivery Details and Confirm Order");
	$cancelorder = _("Cancel Order");
	$porder = _("Place Order");
	$corder = _("Commit Order Changes");
}
start_form();

hidden('cart_id');
$customer_error = display_order_header($_SESSION['Items'], !$_SESSION['Items']->is_started(), $idate);

if ($customer_error == "") {
	start_table(TABLESTYLE, "width='80%'", 10);
	echo "<tr><td>";
	display_order_summary($orderitems, $_SESSION['Items'], true);
	echo "</td></tr>";
	echo "<tr><td>";
	display_delivery_details($_SESSION['Items']);
	echo "</td></tr>";
	end_table(1);

	if ($_SESSION['Items']->trans_no == 0) {

		submit_center_first('ProcessOrder', $porder,
		    _(UI_TEXT_CHECK_ENTERED_DATA_AND_SAVE_DOCUMENT), 'default');
		submit_center_last('CancelOrder', $cancelorder,
	   		_(UI_TEXT_CANCELS_DOCUMENT_ENTRY_OR_REMOVES_SALES_ORDER_WHEN_EDITING_AN_OLD_DOCUMENT));
		submit_js_confirm('CancelOrder', _(UI_TEXT_YOU_ARE_ABOUT_TO_VOID_THIS_DOCUMENT_DO_YOU_WANT_TO_CONTINUE));
	} else {
		submit_center_first('ProcessOrder', $corder,
		    _(UI_TEXT_VALIDATE_CHANGES_AND_UPDATE_DOCUMENT), 'default');
		submit_center_last('CancelOrder', $cancelorder,
	   		_(UI_TEXT_CANCELS_DOCUMENT_ENTRY_OR_REMOVES_SALES_ORDER_WHEN_EDITING_AN_OLD_DOCUMENT));
		if ($_SESSION['Items']->trans_type==ST_SALESORDER)
			submit_js_confirm('CancelOrder', _(UI_TEXT_YOU_ARE_ABOUT_TO_CANCEL_UNDELIVERED_PART_OF_THIS_ORDER_DO_YOU_WANT_TO_CONTINUE));
		else
			submit_js_confirm('CancelOrder', _(UI_TEXT_YOU_ARE_ABOUT_TO_VOID_THIS_DOCUMENT_DO_YOU_WANT_TO_CONTINUE));
	}

} else {
	UiMessageService::displayError($customer_error);
}

end_form();
end_page();
