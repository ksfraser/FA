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
$page_security = 'SA_BANKTRANSFER';
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/ui_strings.php");

include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/gl/includes/gl_ui.inc");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
require_once($path_to_root . "/includes/CompanyPrefsService.php");
use FA\Services\DateService;

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

if (isset($_GET['ModifyTransfer'])) {
	$_SESSION['page_title'] = _($help_context = UI_TEXT_MODIFY_BANK_ACCOUNT_TRANSFER);
} else {
	$_SESSION['page_title'] = _($help_context = UI_TEXT_BANK_ACCOUNT_TRANSFER_ENTRY);
}

page($_SESSION['page_title'], false, false, "", $js);

check_db_has_bank_accounts(_(UI_TEXT_THERE_ARE_NO_BANK_ACCOUNTS_DEFINED_IN_THE_SYSTEM));

//----------------------------------------------------------------------------------------

if (isset($_GET['AddedID'])) 
{
	$trans_no = $_GET['AddedID'];
	$trans_type = ST_BANKTRANSFER;

   	display_notification_centered( _(UI_TEXT_TRANSFER_HAS_BEEN_ENTERED));

	display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THE_GL_JOURNAL_ENTRIES_FOR_THIS_TRANSFER)));

   	hyperlink_no_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_TRANSFER));

	display_footer_exit();
}

if (isset($_POST['_DatePaid_changed'])) {
	$Ajax->activate('_ex_rate');
}

//----------------------------------------------------------------------------------------

function gl_payment_controls($trans_no)
{
	global $Refs;
	
	if (!in_ajax()) {
		if ($trans_no) {
			$result = get_bank_trans(ST_BANKTRANSFER, $trans_no);

			if (db_num_rows($result) != 2)
				display_db_error("Bank transfer does not contain two records");

			$trans1 = db_fetch($result);
			$trans2 = db_fetch($result);

			if ($trans1["amount"] < 0) {
				$from_trans = $trans1; // from trans is the negative one
				$to_trans = $trans2;
			} else {
			$from_trans = $trans2;
				$to_trans = $trans1;
			}
			$_POST['DatePaid'] = DateService::sql2dateStatic($to_trans['trans_date']);
			$_POST['ref'] = $to_trans['ref'];
			$_POST['memo_'] = get_comments_string($to_trans['type'], $trans_no);
			$_POST['FromBankAccount'] = $from_trans['bank_act'];
			$_POST['ToBankAccount'] = $to_trans['bank_act'];
			$_POST['target_amount'] = FormatService::priceFormat($to_trans['amount']);
			$_POST['amount'] = FormatService::priceFormat(-$from_trans['amount']);
			$_POST['dimension_id'] = $to_trans['dimension_id'];
			$_POST['dimension2_id'] = $to_trans['dimension2_id'];
		} else {
			$_POST['ref'] = $Refs->get_next(ST_BANKTRANSFER, null, RequestService::getPostStatic('DatePaid'));
			$_POST['memo_'] = '';
			$_POST['FromBankAccount'] = 0;
			$_POST['ToBankAccount'] = 0;
			$_POST['amount'] = 0;
			$_POST['dimension_id'] = 0;
			$_POST['dimension2_id'] = 0;
		}
	}

	start_form();

	start_outer_table(TABLESTYLE2);

	table_section(1);

	bank_accounts_list_row(_(UI_TEXT_FROM_ACCOUNT), 'FromBankAccount', null, true);

	bank_balance_row($_POST['FromBankAccount']);

    bank_accounts_list_row(_(UI_TEXT_TO_ACCOUNT), 'ToBankAccount', null, true);

	if (!isset($_POST['DatePaid'])) { // init page
		$_POST['DatePaid'] = DateService::newDocDateStatic();
		if (!DateService::isDateInFiscalYearStatic($_POST['DatePaid']))
			$_POST['DatePaid'] = DateService::endFiscalYear();
	}
    date_row(_(UI_TEXT_TRANSFER_DATE), 'DatePaid', '', true, 0, 0, 0, null, true);

    ref_row(_(UI_TEXT_REFERENCE_LABEL), 'ref', '', $Refs->get_next(ST_BANKTRANSFER, null, RequestService::getPostStatic('DatePaid')), false, ST_BANKTRANSFER,
    	array('date' => RequestService::getPostStatic('DatePaid')));
	$dim = \FA\Services\CompanyPrefsService::getUseDimensions();
	if ($dim > 0)
		dimensions_list_row(_(UI_TEXT_DIMENSION).":", 'dimension_id', 
			null, true, ' ', false, 1, false);
	else
		hidden('dimension_id', 0);

	table_section(2);

	$from_currency = get_bank_account_currency($_POST['FromBankAccount']);
	$to_currency = get_bank_account_currency($_POST['ToBankAccount']);
	if ($from_currency != "" && $to_currency != "" && $from_currency != $to_currency) 
	{
		amount_row(_(UI_TEXT_AMOUNT), 'amount', null, null, $from_currency);
		amount_row(_(UI_TEXT_BANK_CHARGE), 'charge', null, null, $from_currency);

		amount_row(_(UI_TEXT_INCOMING_AMOUNT), 'target_amount', null, '', $to_currency, 2);
	} 
	else 
	{
		amount_row(_(UI_TEXT_AMOUNT), 'amount');
		amount_row(_(UI_TEXT_BANK_CHARGE), 'charge');
	}
	if ($dim > 1)
		dimensions_list_row(_(UI_TEXT_DIMENSION)." 2:", 'dimension2_id', 
			null, true, ' ', false, 2, false);
	else
		hidden('dimension2_id', 0);

    textarea_row(_(UI_TEXT_MEMO_LABEL), 'memo_', null, 40,4);

	end_outer_table(1); // outer table

	if ($trans_no) {
		hidden('_trans_no', $trans_no);
		submit_center('submit', _(UI_TEXT_MODIFY_TRANSFER), true, '', 'default');
	} else {
		submit_center('submit', _(UI_TEXT_ENTER_TRANSFER), true, '', 'default');
	}

	end_form();
}

//----------------------------------------------------------------------------------------

function check_valid_entries($trans_no)
{
	global $Refs, $systypes_array;
	$dateService = new DateService();
	
	if (!$dateService->isDate($_POST['DatePaid'])) 
	{
		UiMessageService::displayError(_("The entered date is invalid."));
		set_focus('DatePaid');
		return false;
	}
	if (!DateService::isDateInFiscalYearStatic($_POST['DatePaid']))
	{
		UiMessageService::displayError(_("The entered date is out of fiscal year or is closed for further data entry."));
		set_focus('DatePaid');
		return false;
	}

	if (!check_num('amount', 0)) 
	{
		UiMessageService::displayError(_("The entered amount is invalid or less than zero."));
		set_focus('amount');
		return false;
	}
	if (RequestService::inputNumStatic('amount') == 0) {
		UiMessageService::displayError(_("The total bank amount cannot be 0."));
		set_focus('amount');
		return false;
	}

	$limit = get_bank_account_limit($_POST['FromBankAccount'], $_POST['DatePaid']);

	$amnt_tr = RequestService::inputNumStatic('charge') + RequestService::inputNumStatic('amount');

	$problemTransaction = null;
	if ($trans_no) {
		$problemTransaction = check_bank_transfer( $trans_no, $_POST['FromBankAccount'], $_POST['ToBankAccount'], $_POST['DatePaid'],
			$amnt_tr, RequestService::inputNumStatic('target_amount', $amnt_tr));

	if ($problemTransaction != null	) {
		if (!array_key_exists('trans_no', $problemTransaction)) {
			UiMessageService::displayError(sprintf(
				_("This bank transfer change would result in exceeding authorized overdraft limit (%s) of the account '%s'"),
				FormatService::priceFormat(-$problemTransaction['amount']), $problemTransaction['bank_account_name']
			));
		} else {
			UiMessageService::displayError(sprintf(
				_("This bank transfer change would result in exceeding authorized overdraft limit on '%s' for transaction: %s #%s on %s."),
				$problemTransaction['bank_account_name'], $systypes_array[$problemTransaction['type']],
				$problemTransaction['trans_no'], DateService::sql2dateStatic($problemTransaction['trans_date'])
			));
		}
		set_focus('amount');
		return false;
		}
	} else {
		if (null != ($problemTransaction = check_bank_account_history(-$amnt_tr, $_POST['FromBankAccount'], $_POST['DatePaid']))) {
			if (!array_key_exists('trans_no', $problemTransaction)) {
				UiMessageService::displayError(sprintf(
					_("This bank transfer would result in exceeding authorized overdraft limit of the account (%s)"),
					FormatService::priceFormat(-$problemTransaction['amount'])
				));
			} else {
				UiMessageService::displayError(sprintf(
					_("This bank transfer would result in exceeding authorized overdraft limit for transaction: %s #%s on %s."),
					$systypes_array[$problemTransaction['type']], $problemTransaction['trans_no'], DateService::sql2dateStatic($problemTransaction['trans_date'])
				));
			}
			set_focus('amount');
			return false;
		}
	}

	if (isset($_POST['charge']) && !check_num('charge', 0)) 
	{
		UiMessageService::displayError(_("The entered amount is invalid or less than zero."));
		set_focus('charge');
		return false;
	}
	if (isset($_POST['charge']) && RequestService::inputNumStatic('charge') > 0 && get_bank_charge_account($_POST['FromBankAccount']) == '') {
		UiMessageService::displayError(_("The Bank Charge Account has not been set in System and General GL Setup."));
		set_focus('charge');
		return false;
	}

	if (!check_reference($_POST['ref'], ST_BANKTRANSFER, $trans_no)) {
		set_focus('ref');
		return false;
	}

	if ($_POST['FromBankAccount'] == $_POST['ToBankAccount']) 
	{
		UiMessageService::displayError(_("The source and destination bank accouts cannot be the same."));
		set_focus('ToBankAccount');
		return false;
	}

	if (isset($_POST['target_amount']) && !check_num('target_amount', 0)) 
	{
		UiMessageService::displayError(_("The entered amount is invalid or less than zero."));
		set_focus('target_amount');
		return false;
	}
	if (isset($_POST['target_amount']) && RequestService::inputNumStatic('target_amount') == 0) {
		UiMessageService::displayError(_("The incomming bank amount cannot be 0."));
		set_focus('target_amount');
		return false;
	}

	if (!db_has_currency_rates(get_bank_account_currency($_POST['FromBankAccount']), $_POST['DatePaid']))
		return false;

	if (!db_has_currency_rates(get_bank_account_currency($_POST['ToBankAccount']), $_POST['DatePaid']))
		return false;

    return true;
}

//----------------------------------------------------------------------------------------

function bank_transfer_handle_submit()
{
	$trans_no = array_key_exists('_trans_no', $_POST) ?  $_POST['_trans_no'] : null;
	if ($trans_no) {
		$trans_no = update_bank_transfer($trans_no, $_POST['FromBankAccount'], $_POST['ToBankAccount'], $_POST['DatePaid'],	RequestService::inputNumStatic('amount'), 
			$_POST['ref'], $_POST['memo_'], $_POST['dimension_id'], $_POST['dimension2_id'], RequestService::inputNumStatic('charge'), RequestService::inputNumStatic('target_amount'));
	} else {
		DateService::newDocDateStatic($_POST['DatePaid']);
		$trans_no = add_bank_transfer($_POST['FromBankAccount'], $_POST['ToBankAccount'], $_POST['DatePaid'], RequestService::inputNumStatic('amount'), $_POST['ref'], 
			$_POST['memo_'], $_POST['dimension_id'], $_POST['dimension2_id'], RequestService::inputNumStatic('charge'), RequestService::inputNumStatic('target_amount'));
	}

	meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
}

//----------------------------------------------------------------------------------------

$trans_no = '';
if (!$trans_no && isset($_POST['_trans_no'])) {
	$trans_no = $_POST['_trans_no'];
}
if (!$trans_no && isset($_GET['trans_no'])) {
	$trans_no = $_GET["trans_no"];
}

if (isset($_POST['submit'])) {
    if (check_valid_entries($trans_no) == true) {
        bank_transfer_handle_submit();
	}
}

gl_payment_controls($trans_no);

end_page();
