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
$page_security = 'SA_SUPPLIERPAYMNT';
$path_to_root = "..";
include_once($path_to_root . "/includes/ui/allocation_cart.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/CompanyPrefsService.php");
include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
use FA\Services\DateService;

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

add_js_file('payalloc.js');

page(_($help_context = "Supplier Payment Entry"), false, false, "", $js);

if (isset($_GET['supplier_id']))
{
	$_POST['supplier_id'] = $_GET['supplier_id'];
}

//----------------------------------------------------------------------------------------

check_db_has_suppliers(_(UI_TEXT_NO_SUPPLIERS_DEFINED));

check_db_has_bank_accounts(_(UI_TEXT_NO_BANK_ACCOUNTS_DEFINED));

//----------------------------------------------------------------------------------------

if (!isset($_POST['supplier_id']))
	$_POST['supplier_id'] = get_global_supplier(false);

if (!isset($_POST['DatePaid']))
{
	$_POST['DatePaid'] = DateService::newDocDateStatic();
	if (!DateService::isDateInFiscalYear($_POST['DatePaid']))
		$_POST['DatePaid'] = DateService::endFiscalYear();
}

if (isset($_POST['_DatePaid_changed'])) {
  $Ajax->activate('_ex_rate');
}

//----------------------------------------------------------------------------------------

if (!isset($_POST['bank_account'])) { // first page call
	$_SESSION['alloc'] = new allocation(ST_SUPPAYMENT, 0, RequestService::getPostStatic('supplier_id'));

	if (isset($_GET['PInvoice'])) {
		$supp = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : null;
		//  get date and supplier
		$inv = get_supp_trans($_GET['PInvoice'], $_GET['trans_type'], $supp);
		if ($inv) {
			$_SESSION['alloc']->person_id = $_POST['supplier_id'] = $inv['supplier_id'];
			$_SESSION['alloc']->read();
			$_POST['DatePaid'] = DateService::sql2dateStatic($inv['tran_date']);
			$_POST['memo_'] = $inv['supp_reference'];
			foreach($_SESSION['alloc']->allocs as $line => $trans) {
				if ($trans->type == $_GET['trans_type'] && $trans->type_no == $_GET['PInvoice']) {
					$un_allocated = abs($trans->amount) - $trans->amount_allocated;
					$_SESSION['alloc']->amount = $_SESSION['alloc']->allocs[$line]->current_allocated = $un_allocated;
					$_POST['amount'] = $_POST['amount'.$line] = FormatService::priceFormat($un_allocated);
					break;
				}
			}
			unset($inv);
		} else
			UiMessageService::displayError(_(UI_TEXT_INVALID_PURCHASE_INVOICE_NUMBER));
	}
}
if (isset($_GET['AddedID'])) {
	$payment_id = $_GET['AddedID'];

   	display_notification_centered( _(UI_TEXT_PAYMENT_HAS_BEEN_SUCCESSFULLY_ENTERED));

	submenu_print(_(UI_TEXT_PRINT_THIS_REMITTANCE), ST_SUPPAYMENT, $payment_id."-".ST_SUPPAYMENT, 'prtopt');
	submenu_print(_(UI_TEXT_EMAIL_THIS_REMITTANCE), ST_SUPPAYMENT, $payment_id."-".ST_SUPPAYMENT, null, 1);

	submenu_view(_(UI_TEXT_VIEW_THIS_PAYMENT), ST_SUPPAYMENT, $payment_id);
    display_note(get_gl_view_str(ST_SUPPAYMENT, $payment_id, _(UI_TEXT_VIEW_GL_JOURNAL_ENTRIES_FOR_THIS_PAYMENT)), 0, 1);

	submenu_option(_(UI_TEXT_ENTER_ANOTHER_SUPPLIER_PAYMENT), "/purchasing/supplier_payment.php?supplier_id=".$_POST['supplier_id']);

	submenu_option(_(UI_TEXT_ENTER_SUPPLIER_INVOICE), "/purchasing/supplier_invoice.php?New=1");
	submenu_option(_(UI_TEXT_ENTER_DIRECT_INVOICE), "/purchasing/po_entry_items.php?NewInvoice=Yes");

	submenu_option(_(UI_TEXT_ENTER_OTHER_PAYMENT), "/gl/gl_bank.php?NewPayment=Yes");
	submenu_option(_(UI_TEXT_ENTER_CUSTOMER_PAYMENT), "/sales/customer_payments.php");
	submenu_option(_(UI_TEXT_ENTER_OTHER_DEPOSIT), "/gl/gl_bank.php?NewDeposit=Yes");
	submenu_option(_(UI_TEXT_BANK_ACCOUNT_TRANSFER), "/gl/bank_transfer.php");
	submenu_option(_(UI_TEXT_ADD_AN_ATTACHMENT), "/admin/attachments.php?filterType=".ST_SUPPAYMENT."&trans_no=$payment_id");

	display_footer_exit();
}

//----------------------------------------------------------------------------------------

function get_default_supplier_payment_bank_account($supplier_id, $date)
{
	$previous_payment = get_supp_payment_before($supplier_id, DateService::date2sqlStatic($date));
	if ($previous_payment)
	{
		return $previous_payment['bank_id'];
	}
	return get_default_supplier_bank_account($supplier_id);
}
//----------------------------------------------------------------------------------------

function check_inputs()
{
	global $Refs;

	if (!RequestService::getPostStatic('supplier_id')) 
	{
		UiMessageService::displayError(_(UI_TEXT_NO_SUPPLIER_SELECTED));
		set_focus('supplier_id');
		return false;
	} 
	
	if (@$_POST['amount'] == "") 
	{
		$_POST['amount'] = FormatService::priceFormat(0);
	}

	if (!check_num('amount', 0))
	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_AMOUNT_INVALID_OR_LESS_THAN_ZERO));
		set_focus('amount');
		return false;
	}

	if (isset($_POST['charge']) && !check_num('charge', 0)) {
		UiMessageService::displayError(_(UI_TEXT_ENTERED_AMOUNT_INVALID_OR_LESS_THAN_ZERO));
		set_focus('charge');
		return false;
	}

	if (isset($_POST['charge']) && RequestService::inputNumStatic('charge') > 0) {
		$charge_acct = get_bank_charge_account($_POST['bank_account']);
		if (get_gl_account($charge_acct) == false) {
			UiMessageService::displayError(_(UI_TEXT_BANK_CHARGE_ACCOUNT_NOT_SET));
			set_focus('charge');
			return false;
		}	
	}

	if (@$_POST['discount'] == "") 
	{
		$_POST['discount'] = 0;
	}

	if (!check_num('discount', 0))
	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_DISCOUNT_INVALID_OR_LESS_THAN_ZERO));
		set_focus('amount');
		return false;
	}

	//if (RequestService::inputNumStatic('amount') - RequestService::inputNumStatic('discount') <= 0) 
	if (RequestService::inputNumStatic('amount') <= 0) 
	{
		UiMessageService::displayError(_(UI_TEXT_TOTAL_AMOUNT_AND_DISCOUNT_ZERO_OR_NEGATIVE));
		set_focus('amount');
		return false;
	}

	if (isset($_POST['bank_amount']) && RequestService::inputNumStatic('bank_amount')<=0)
	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_BANK_AMOUNT_ZERO_OR_NEGATIVE));
		set_focus('bank_amount');
		return false;
	}
	$dateService = new DateService();


   	if (!$dateService->isDate($_POST['DatePaid']))
   	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_DATE_INVALID));
		set_focus('DatePaid');
		return false;
	} 
	elseif (!DateService::isDateInFiscalYear($_POST['DatePaid'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_DATE_OUT_OF_FISCAL_YEAR_OR_CLOSED));
		set_focus('DatePaid');
		return false;
	}

	$limit = get_bank_account_limit($_POST['bank_account'], $_POST['DatePaid']);

	if (($limit !== null) && (floatcmp($limit, RequestService::inputNumStatic('amount')) < 0))
	{
		UiMessageService::displayError(sprintf(_(UI_TEXT_TOTAL_BANK_AMOUNT_EXCEEDS_ALLOWED_LIMIT), FormatService::priceFormat($limit)));
		set_focus('amount');
		return false;
	}

	if (!check_reference($_POST['ref'], ST_SUPPAYMENT))
	{
		set_focus('ref');
		return false;
	}

	if (!db_has_currency_rates(get_supplier_currency($_POST['supplier_id']), $_POST['DatePaid'], true))
		return false;

	$_SESSION['alloc']->amount = -RequestService::inputNumStatic('amount');

	if (isset($_POST["TotalNumberOfAllocs"]))
		return check_allocations();
	else
		return true;
}

//----------------------------------------------------------------------------------------

function handle_add_payment()
{
	$payment_id = write_supp_payment(0, $_POST['supplier_id'], $_POST['bank_account'],
		$_POST['DatePaid'], $_POST['ref'], RequestService::inputNumStatic('amount'),	RequestService::inputNumStatic('discount'), $_POST['memo_'], 
		RequestService::inputNumStatic('charge'), RequestService::inputNumStatic('bank_amount', RequestService::inputNumStatic('amount')), $_POST['dimension_id'], $_POST['dimension2_id']);
	DateService::newDocDateStatic($_POST['DatePaid']);

	$_SESSION['alloc']->trans_no = $payment_id;
	$_SESSION['alloc']->date_ = $_POST['DatePaid'];
	$_SESSION['alloc']->write();

   	unset($_POST['bank_account']);
   	unset($_POST['DatePaid']);
   	unset($_POST['currency']);
   	unset($_POST['memo_']);
   	unset($_POST['amount']);
   	unset($_POST['discount']);
   	unset($_POST['ProcessSuppPayment']);

	meta_forward($_SERVER['PHP_SELF'], "AddedID=$payment_id&supplier_id=".$_POST['supplier_id']);
}

//----------------------------------------------------------------------------------------

if (isset($_POST['ProcessSuppPayment']))
{
	 /*First off  check for valid inputs */
    if (check_inputs() == true) 
    {
    	handle_add_payment();
    	end_page();
     	exit;
    }
}

//----------------------------------------------------------------------------------------

start_form();

	start_outer_table(TABLESTYLE2, "width='60%'", 5);

	table_section(1);

    supplier_list_row(_(UI_TEXT_PAYMENT_TO), 'supplier_id', null, false, true);

	if (list_updated('supplier_id')) {
		$_POST['amount'] = FormatService::priceFormat(0);
		$_SESSION['alloc']->person_id = RequestService::getPostStatic('supplier_id');
		$Ajax->activate('amount');
	} elseif (list_updated('bank_account'))
		$Ajax->activate('alloc_tbl');

	if (list_updated('supplier_id') || list_updated('bank_account')) {
	  $_SESSION['alloc']->read();
	  $_POST['memo_'] = $_POST['amount'] = '';
	  $Ajax->activate('alloc_tbl');
	}

	set_global_supplier($_POST['supplier_id']);

	if (!list_updated('bank_account') && !RequestService::getPostStatic('__ex_rate_changed'))
	{
		$_POST['bank_account'] = get_default_supplier_payment_bank_account($_POST['supplier_id'], $_POST['DatePaid']);
	} else
	{
		$_POST['amount'] = FormatService::priceFormat(0);
	}

    bank_accounts_list_row(_(UI_TEXT_FROM_BANK_ACCOUNT), 'bank_account', null, true);

	bank_balance_row($_POST['bank_account']);

	table_section(2);

    date_row(_(UI_TEXT_DATE_PAID) . ":", 'DatePaid', '', true, 0, 0, 0, null, true);

    ref_row(_(UI_TEXT_REFERENCE), 'ref', '', $Refs->get_next(ST_SUPPAYMENT, null, 
    	array('supplier'=>RequestService::getPostStatic('supplier_id'), 'date'=>RequestService::getPostStatic('DatePaid'))), false, ST_SUPPAYMENT);


	table_section(3);

	$comp_currency = BankingService::getCompanyCurrency();
	$supplier_currency = $_SESSION['alloc']->set_person($_POST['supplier_id'], PT_SUPPLIER);
	if (!$supplier_currency)
			$supplier_currency = $comp_currency;
	$_SESSION['alloc']->currency = $bank_currency = get_bank_account_currency($_POST['bank_account']);

	if ($bank_currency != $supplier_currency) 
	{
		amount_row(_(UI_TEXT_BANK_AMOUNT), 'bank_amount', null, '', $bank_currency);
	}

	amount_row(_(UI_TEXT_BANK_CHARGE), 'charge', null, '', $bank_currency);

	$row = get_supplier($_POST['supplier_id']);
	$_POST['dimension_id'] = @$row['dimension_id'];
	$_POST['dimension2_id'] = @$row['dimension2_id'];
	$dim = \FA\Services\CompanyPrefsService::getUseDimensions();
	if ($dim > 0)
		dimensions_list_row(_(UI_TEXT_DIMENSION).":", 'dimension_id',
			null, true, ' ', false, 1, false);
	else
		hidden('dimension_id', 0);
	if ($dim > 1)
		dimensions_list_row(_(UI_TEXT_DIMENSION)." 2:", 'dimension2_id',
			null, true, ' ', false, 2, false);
	else
		hidden('dimension2_id', 0);

	end_outer_table(1);

	div_start('alloc_tbl');
	show_allocatable(false);
	div_end();

	start_table(TABLESTYLE, "width='60%'");
	amount_row(_(UI_TEXT_AMOUNT_OF_DISCOUNT), 'discount', null, '', $supplier_currency);
	amount_row(_(UI_TEXT_AMOUNT_OF_PAYMENT), 'amount', null, '', $supplier_currency);
	textarea_row(_(UI_TEXT_MEMO), 'memo_', null, 22, 4);
	end_table(1);

	submit_center('ProcessSuppPayment',_(UI_TEXT_ENTER_PAYMENT), true, '', 'default');

end_form();

end_page();
