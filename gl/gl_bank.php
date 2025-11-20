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
$path_to_root = "..";
include_once($path_to_root . "/includes/ui/items_cart.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui_strings.php");
$page_security = isset($_GET['NewPayment']) || 
	@($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT)
 ? 'SA_PAYMENT' : 'SA_DEPOSIT';

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/ui/gl_bank_ui.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/gl/includes/gl_ui.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
use FA\Services\DateService;

$js = '';
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

if (isset($_GET['NewPayment'])) {
	$_SESSION['page_title'] = _($help_context = UI_TEXT_BANK_ACCOUNT_PAYMENT_ENTRY);
	create_cart(ST_BANKPAYMENT, 0);
} else if(isset($_GET['NewDeposit'])) {
	$_SESSION['page_title'] = _($help_context = UI_TEXT_BANK_ACCOUNT_DEPOSIT_ENTRY);
	create_cart(ST_BANKDEPOSIT, 0);
} else if(isset($_GET['ModifyPayment'])) {
	$_SESSION['page_title'] = _($help_context = UI_TEXT_MODIFY_BANK_ACCOUNT_ENTRY)." #".$_GET['trans_no'];
	create_cart(ST_BANKPAYMENT, $_GET['trans_no']);
} else if(isset($_GET['ModifyDeposit'])) {
	$_SESSION['page_title'] = _($help_context = UI_TEXT_MODIFY_BANK_DEPOSIT_ENTRY)." #".$_GET['trans_no'];
	create_cart(ST_BANKDEPOSIT, $_GET['trans_no']);
}
page($_SESSION['page_title'], false, false, '', $js);

//-----------------------------------------------------------------------------------------------
check_db_has_bank_accounts(_(UI_TEXT_THERE_ARE_NO_BANK_ACCOUNTS_DEFINED_IN_THE_SYSTEM));

if (isset($_GET['ModifyDeposit']) || isset($_GET['ModifyPayment']))
	check_is_editable($_SESSION['pay_items']->trans_type, $_SESSION['pay_items']->order_id);

//----------------------------------------------------------------------------------------
if (list_updated('PersonDetailID')) {
	$br = get_branch(RequestService::getPostStatic('PersonDetailID'));
	$_POST['person_id'] = $br['debtor_no'];
	$Ajax->activate('person_id');
}

//--------------------------------------------------------------------------------------------------
function line_start_focus() {
  	global 	$Ajax;

    unset($_POST['amount']);
    unset($_POST['dimension_id']);
    unset($_POST['dimension2_id']);
    unset($_POST['LineMemo']);
  	$Ajax->activate('items_table');
  	$Ajax->activate('footer');
  	set_focus('_code_id_edit');
}

//-----------------------------------------------------------------------------------------------

if (isset($_GET['AddedID']))
{
	$trans_no = $_GET['AddedID'];
	$trans_type = ST_BANKPAYMENT;

   	display_notification_centered(sprintf(_(UI_TEXT_PAYMENT_HAS_BEEN_ENTERED), $trans_no));

	display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THE_GL_POSTINGS_FOR_THIS_PAYMENT)));

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_PAYMENT), "NewPayment=yes");

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_A_DEPOSIT), "NewDeposit=yes");

	hyperlink_params("$path_to_root/admin/attachments.php", _(UI_TEXT_ADD_AN_ATTACHMENT), "filterType=$trans_type&trans_no=$trans_no");

	display_footer_exit();
}

if (isset($_GET['UpdatedID']))
{
	$trans_no = $_GET['UpdatedID'];
	$trans_type = ST_BANKPAYMENT;

   	display_notification_centered(sprintf(_(UI_TEXT_PAYMENT_HAS_BEEN_MODIFIED), $trans_no));

	display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THE_GL_POSTINGS_FOR_THIS_PAYMENT)));

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_PAYMENT), "NewPayment=yes");

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_A_DEPOSIT), "NewDeposit=yes");

	display_footer_exit();
}

if (isset($_GET['AddedDep']))
{
	$trans_no = $_GET['AddedDep'];
	$trans_type = ST_BANKDEPOSIT;

   	display_notification_centered(sprintf(_(UI_TEXT_DEPOSIT_HAS_BEEN_ENTERED), $trans_no));

	display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THE_GL_POSTINGS_FOR_THIS_DEPOSIT)));

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_DEPOSIT), "NewDeposit=yes");

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_A_PAYMENT), "NewPayment=yes");

	display_footer_exit();
}
if (isset($_GET['UpdatedDep']))
{
	$trans_no = $_GET['UpdatedDep'];
	$trans_type = ST_BANKDEPOSIT;

   	display_notification_centered(sprintf(_(UI_TEXT_DEPOSIT_HAS_BEEN_MODIFIED), $trans_no));

	display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THE_GL_POSTINGS_FOR_THIS_DEPOSIT)));

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_DEPOSIT), "NewDeposit=yes");

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_A_PAYMENT), "NewPayment=yes");

	display_footer_exit();
}

//--------------------------------------------------------------------------------------------------

function create_cart($type, $trans_no)
{
	global $Refs;

	if (isset($_SESSION['pay_items']))
	{
		unset ($_SESSION['pay_items']);
	}

	$cart = new items_cart($type);
    $cart->order_id = $trans_no;

	if ($trans_no) {

		$bank_trans = db_fetch(get_bank_trans($type, $trans_no));
		$_POST['bank_account'] = $bank_trans["bank_act"];
		$_POST['PayType'] = $bank_trans["person_type_id"];
		$cart->reference = $bank_trans["ref"];

		if ($bank_trans["person_type_id"] == PT_CUSTOMER)
		{
			$trans = get_customer_trans($trans_no, $type);	
			$_POST['person_id'] = $trans["debtor_no"];
			$_POST['PersonDetailID'] = $trans["branch_code"];
		}
		elseif ($bank_trans["person_type_id"] == PT_SUPPLIER)
		{
			$trans = get_supp_trans($trans_no, $type);
			$_POST['person_id'] = $trans["supplier_id"];
		}
		elseif ($bank_trans["person_type_id"] == PT_MISC)
			$_POST['person_id'] = $bank_trans["person_id"];
		elseif ($bank_trans["person_type_id"] == PT_QUICKENTRY)
			$_POST['person_id'] = $bank_trans["person_id"];
		else 
			$_POST['person_id'] = $bank_trans["person_id"];

		$cart->memo_ = get_comments_string($type, $trans_no);
		$cart->tran_date = DateService::sql2dateStatic($bank_trans['trans_date']);

		$cart->original_amount = $bank_trans['amount'];
		$result = get_gl_trans($type, $trans_no);
		if ($result) {
			while ($row = db_fetch($result)) {
				if (is_bank_account($row['account'])) {
					// date exchange rate is currenly not stored in bank transaction,
					// so we have to restore it from original gl amounts
					$ex_rate = $bank_trans['amount']/$row['amount'];
				} else {
					$cart->add_gl_item( $row['account'], $row['dimension_id'],
						$row['dimension2_id'], $row['amount'], $row['memo_']);
				}
			}
		}

		// apply exchange rate
		foreach($cart->gl_items as $line_no => $line)
			$cart->gl_items[$line_no]->amount *= $ex_rate;

	} else {
		$cart->reference = $Refs->get_next($cart->trans_type, null, $cart->tran_date);
		$cart->tran_date = DateService::newDocDateStatic();
		if (!DateService::isDateInFiscalYearStatic($cart->tran_date))
			$cart->tran_date = DateService::endFiscalYear();
	}

	$_POST['memo_'] = $cart->memo_;
	$_POST['ref'] = $cart->reference;
	$_POST['date_'] = $cart->tran_date;

	$_SESSION['pay_items'] = &$cart;
}
//-----------------------------------------------------------------------------------------------

function check_trans()
{
	global $Refs, $systypes_array;

	$input_error = 0;

	if ($_SESSION['pay_items']->count_gl_items() < 1) {
		UiMessageService::displayError(_(UI_TEXT_YOU_MUST_ENTER_AT_LEAST_ONE_PAYMENT_LINE));
		set_focus('code_id');
		$input_error = 1;
	}

	if ($_SESSION['pay_items']->gl_items_total() == 0.0) {
		UiMessageService::displayError(_(UI_TEXT_THE_TOTAL_BANK_AMOUNT_CANNOT_BE_0));
		set_focus('code_id');
		$input_error = 1;
	}

	$limit = get_bank_account_limit($_POST['bank_account'], $_POST['date_']);

	$amnt_chg = -$_SESSION['pay_items']->gl_items_total()-$_SESSION['pay_items']->original_amount;

	if ($limit !== null && floatcmp($limit, -$amnt_chg) < 0)
	{
		UiMessageService::displayError(sprintf(_(UI_TEXT_THE_TOTAL_BANK_AMOUNT_EXCEEDS_ALLOWED_LIMIT), FormatService::priceFormat($limit-$_SESSION['pay_items']->original_amount)));
		set_focus('code_id');
		$input_error = 1;
	}
	if ($trans = check_bank_account_history($amnt_chg, $_POST['bank_account'], $_POST['date_'])) {

		if (isset($trans['trans_no'])) {
			UiMessageService::displayError(sprintf(_(UI_TEXT_THE_BANK_TRANSACTION_WOULD_RESULT_IN_EXCEED_OF_AUTHORIZED_OVERDRAFT_LIMIT_FOR_TRANSACTION),
				$systypes_array[$trans['type']], $trans['trans_no'], DateService::sql2dateStatic($trans['trans_date'])));
			set_focus('amount');
			$input_error = 1;
		}	
	}
	if (!check_reference($_POST['ref'], $_SESSION['pay_items']->trans_type, $_SESSION['pay_items']->order_id))
	{
		set_focus('ref');
		$input_error = 1;
	}
	$dateService = new DateService();
	if (!$dateService->isDate($_POST['date_']))
	{
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_FOR_THE_PAYMENT_IS_INVALID));
		set_focus('date_');
		$input_error = 1;
	}
	elseif (!DateService::isDateInFiscalYearStatic($_POST['date_']))
	{
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_OUT_OF_FISCAL_YEAR_OR_IS_CLOSED_FOR_FURTHER_DATA_ENTRY));
		set_focus('date_');
		$input_error = 1;
	} 

	if (RequestService::getPostStatic('PayType')==PT_CUSTOMER && (!RequestService::getPostStatic('person_id') || !RequestService::getPostStatic('PersonDetailID'))) {
		UiMessageService::displayError(_(UI_TEXT_YOU_HAVE_TO_SELECT_CUSTOMER_AND_CUSTOMER_BRANCH));
		set_focus('person_id');
		$input_error = 1;
	} elseif (RequestService::getPostStatic('PayType')==PT_SUPPLIER && (!RequestService::getPostStatic('person_id'))) {
		UiMessageService::displayError(_(UI_TEXT_YOU_HAVE_TO_SELECT_SUPPLIER));
		set_focus('person_id');
		$input_error = 1;
	}
	if (!db_has_currency_rates(get_bank_account_currency($_POST['bank_account']), $_POST['date_'], true))
		$input_error = 1;

	if (isset($_POST['settled_amount']) && in_array(RequestService::getPostStatic('PayType'), array(PT_SUPPLIER, PT_CUSTOMER)) && (RequestService::inputNumStatic('settled_amount') <= 0)) {
		UiMessageService::displayError(_(UI_TEXT_SETTLED_AMOUNT_HAVE_TO_BE_POSITIVE_NUMBER));
		set_focus('person_id');
		$input_error = 1;
	}
	return $input_error;
}

if (isset($_POST['Process']) && !check_trans())
{
	begin_transaction();

	$_SESSION['pay_items'] = &$_SESSION['pay_items'];
	$new = $_SESSION['pay_items']->order_id == 0;

	add_new_exchange_rate(get_bank_account_currency(RequestService::getPostStatic('bank_account')), RequestService::getPostStatic('date_'), RequestService::inputNumStatic('_ex_rate'));

	$trans = write_bank_transaction(
		$_SESSION['pay_items']->trans_type, $_SESSION['pay_items']->order_id, $_POST['bank_account'],
		$_SESSION['pay_items'], $_POST['date_'],
		$_POST['PayType'], $_POST['person_id'], RequestService::getPostStatic('PersonDetailID'),
		$_POST['ref'], $_POST['memo_'], true, RequestService::inputNumStatic('settled_amount', null));

	$trans_type = $trans[0];
   	$trans_no = $trans[1];
	DateService::newDocDateStatic($_POST['date_']);

	$_SESSION['pay_items']->clear_items();
	unset($_SESSION['pay_items']);

	commit_transaction();

	if ($new)
		meta_forward($_SERVER['PHP_SELF'], $trans_type==ST_BANKPAYMENT ?
			"AddedID=$trans_no" : "AddedDep=$trans_no");
	else
		meta_forward($_SERVER['PHP_SELF'], $trans_type==ST_BANKPAYMENT ?
			"UpdatedID=$trans_no" : "UpdatedDep=$trans_no");

}

//-----------------------------------------------------------------------------------------------

function check_item_data()
{
	if (!check_num('amount', 0))
	{
		UiMessageService::displayError( _(UI_TEXT_THE_AMOUNT_ENTERED_IS_NOT_A_VALID_NUMBER_OR_IS_LESS_THAN_ZERO));
		set_focus('amount');
		return false;
	}
	if (isset($_POST['_ex_rate']) && RequestService::inputNumStatic('_ex_rate') <= 0)
	{
		UiMessageService::displayError( _(UI_TEXT_THE_EXCHANGE_RATE_CANNOT_BE_ZERO_OR_A_NEGATIVE_NUMBER));
		set_focus('_ex_rate');
		return false;
	}

	return true;
}

//-----------------------------------------------------------------------------------------------

function handle_update_item()
{
	$amount = ($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ? 1:-1) * RequestService::inputNumStatic('amount');
    if($_POST['UpdateItem'] != "" && check_item_data())
    {
    	$_SESSION['pay_items']->update_gl_item($_POST['Index'], $_POST['code_id'], 
    	    $_POST['dimension_id'], $_POST['dimension2_id'], $amount , $_POST['LineMemo']);
    }
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_delete_item($id)
{
	$_SESSION['pay_items']->remove_gl_item($id);
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_new_item()
{
	if (!check_item_data())
		return;
	$amount = ($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ? 1:-1) * RequestService::inputNumStatic('amount');

	$_SESSION['pay_items']->add_gl_item($_POST['code_id'], $_POST['dimension_id'],
		$_POST['dimension2_id'], $amount, $_POST['LineMemo']);
	line_start_focus();
}
//-----------------------------------------------------------------------------------------------
$id = find_submit('Delete');
if ($id != -1)
	handle_delete_item($id);

if (isset($_POST['AddItem']))
	handle_new_item();

if (isset($_POST['UpdateItem']))
	handle_update_item();

if (isset($_POST['CancelItemChanges']) || isset($_POST['Index']))
	line_start_focus();

if (isset($_POST['go']))
{
	display_quick_entries($_SESSION['pay_items'], $_POST['person_id'], RequestService::inputNumStatic('totamount'), 
		$_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ? QE_PAYMENT : QE_DEPOSIT);
	$_POST['totamount'] = FormatService::priceFormat(0); $Ajax->activate('totamount');
	line_start_focus();
}
//-----------------------------------------------------------------------------------------------

start_form();

display_bank_header($_SESSION['pay_items']);

start_table(TABLESTYLE2, "width='90%'", 10);
start_row();
echo "<td>";
display_gl_items($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ?
	_(UI_TEXT_PAYMENT_ITEMS):_(UI_TEXT_DEPOSIT_ITEMS), $_SESSION['pay_items']);
gl_options_controls($_SESSION['pay_items']);
echo "</td>";
end_row();
end_table(1);

submit_center_first('Update', _(UI_TEXT_UPDATE), '', null);
submit_center_last('Process', $_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ?
	_(UI_TEXT_PROCESS_PAYMENT):_(UI_TEXT_PROCESS_DEPOSIT), '', 'default');

end_form();

//------------------------------------------------------------------------------------------------

end_page();

