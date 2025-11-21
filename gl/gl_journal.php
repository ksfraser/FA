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
$page_security = 'SA_JOURNALENTRY';
$path_to_root = "..";
include_once($path_to_root . "/includes/ui/items_cart.inc");

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/ui/gl_journal_ui.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/gl/includes/gl_ui.inc");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
require_once($path_to_root . "/includes/CompanyPrefsService.php");
use FA\Services\DateService;

$js = '';
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

if (isset($_GET['ModifyGL'])) {
	$_SESSION['page_title'] = sprintf(_(UI_TEXT_MODIFYING_JOURNAL_TRANSACTION), 
		$_GET['trans_no']);
	$help_context = "Modifying Journal Entry";
} else
	$_SESSION['page_title'] = _($help_context = UI_TEXT_JOURNAL_ENTRY);

page($_SESSION['page_title'], false, false,'', $js);
//--------------------------------------------------------------------------------------------------

function line_start_focus() {
  global 	$Ajax;

  unset($_POST['Index']);
  $Ajax->activate('tabs');
  unset($_POST['_code_id_edit'], $_POST['code_id'], $_POST['AmountDebit'], 
  	$_POST['AmountCredit'], $_POST['dimension_id'], $_POST['dimension2_id']);
  set_focus('_code_id_edit');
}
//-----------------------------------------------------------------------------------------------

if (isset($_GET['AddedID'])) 
{
	$trans_no = $_GET['AddedID'];
	$trans_type = ST_JOURNAL;

   	display_notification_centered( _(UI_TEXT_JOURNAL_ENTRY_HAS_BEEN_ENTERED) . " #$trans_no");

    display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THIS_JOURNAL_ENTRY)));

	reset_focus();
	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_NEW_JOURNAL_ENTRY), "NewJournal=Yes");

	hyperlink_params("$path_to_root/admin/attachments.php", _(UI_TEXT_ADD_AN_ATTACHMENT), "filterType=$trans_type&trans_no=$trans_no");

	display_footer_exit();
} elseif (isset($_GET['UpdatedID'])) 
{
	$trans_no = $_GET['UpdatedID'];
	$trans_type = ST_JOURNAL;

   	display_notification_centered( _(UI_TEXT_JOURNAL_ENTRY_HAS_BEEN_UPDATED) . " #$trans_no");

    display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THIS_JOURNAL_ENTRY)));

   	hyperlink_no_params($path_to_root."/gl/inquiry/journal_inquiry.php", _(UI_TEXT_RETURN_TO_JOURNAL_INQUIRY));

	display_footer_exit();
}
//--------------------------------------------------------------------------------------------------

if (isset($_GET['NewJournal']))
{
	create_cart(0,0);
}
elseif (isset($_GET['ModifyGL']))
{
	check_is_editable($_GET['trans_type'], $_GET['trans_no']);

	if (!isset($_GET['trans_type']) || $_GET['trans_type']!= 0) {
		UiMessageService::displayError(_(UI_TEXT_YOU_CAN_EDIT_DIRECTLY_ONLY_JOURNAL_ENTRIES_CREATED_VIA_JOURNAL_ENTRY_PAGE));
		hyperlink_params("$path_to_root/gl/gl_journal.php", _(UI_TEXT_ENTRY_NEW_JOURNAL_ENTRY), "NewJournal=Yes");
		display_footer_exit();
	}
	create_cart($_GET['trans_type'], $_GET['trans_no']);
}

function create_cart($type=0, $trans_no=0)
{
	global $Refs;

	if (isset($_SESSION['journal_items']))
	{
		unset ($_SESSION['journal_items']);
	}

	check_is_closed($type, $trans_no);
	$cart = new items_cart($type);
    $cart->order_id = $trans_no;

	if ($trans_no) {
		$header = get_journal($type, $trans_no);
		$cart->event_date = DateService::sql2dateStatic($header['event_date']);
		$cart->doc_date = DateService::sql2dateStatic($header['doc_date']);
		$cart->tran_date = DateService::sql2dateStatic($header['tran_date']);
		$cart->currency = $header['currency'];
		$cart->rate = $header['rate'];
		$cart->source_ref = $header['source_ref'];

		$result = get_gl_trans($type, $trans_no);

		if ($result) {
			while ($row = db_fetch($result)) {
				$curr_amount = $cart->rate ? round($row['amount']/$cart->rate, $_SESSION["wa_current_user"]->prefs->price_dec()) : $row['amount'];
				if ($curr_amount)
					$cart->add_gl_item($row['account'], $row['dimension_id'], $row['dimension2_id'], 
						$curr_amount, $row['memo_'], '', $row['person_id']);
			}
		}
		$cart->memo_ = get_comments_string($type, $trans_no);
		$cart->reference = $header['reference'];
		// update net_amounts from tax register

		// retrieve tax details
		$tax_info = $cart->collect_tax_info(); // tax amounts in reg are always consistent with GL, so we can read them from GL lines

		$taxes = get_trans_tax_details($type, $trans_no);
		while ($detail = db_fetch($taxes))
		{
			$tax_id = $detail['tax_type_id'];
			$tax_info['net_amount'][$tax_id] = $detail['net_amount']; // we can two records for the same tax_id, but in this case net_amount is the same
			$tax_info['tax_date'] = DateService::sql2dateStatic($detail['tran_date']);
			//$tax_info['tax_group'] = $detail['tax_group_id'];

		}
		if (isset($tax_info['net_amount']))	// guess exempt sales/purchase if any tax has been found
		{
			$net_sum = 0;
			foreach($cart->gl_items as $gl)
                if (!is_tax_account($gl->code_id) && !is_subledger_account($gl->code_id))
					$net_sum += $gl->amount;

			$ex_net = abs($net_sum) - array_sum($tax_info['net_amount']);
			if ($ex_net > 0)
				$tax_info['net_amount_ex'] = $ex_net;
		}
		$cart->tax_info = $tax_info;

	} else {
		$cart->tran_date = $cart->doc_date = $cart->event_date = DateService::newDocDateStatic();
		if (!DateService::isDateInFiscalYearStatic($cart->tran_date))
			$cart->tran_date = DateService::endFiscalYear();
		$cart->reference = $Refs->get_next(ST_JOURNAL, null, $cart->tran_date);
	}

	$_POST['memo_'] = $cart->memo_;
	$_POST['ref'] = $cart->reference;
	$_POST['date_'] = $cart->tran_date;
	$_POST['event_date'] = $cart->event_date;
	$_POST['doc_date'] = $cart->doc_date;
	$_POST['currency'] = $cart->currency;
	$_POST['_ex_rate'] = \FA\Services\FormatService::exrateFormat($cart->rate);
	$_POST['source_ref'] = $cart->source_ref;
	if (isset($cart->tax_info['net_amount']) || (!$trans_no && get_company_pref('default_gl_vat')))
		$_POST['taxable_trans'] = true;
	$_SESSION['journal_items'] = &$cart;
}

function update_tax_info()
{

	if (!isset($_SESSION['journal_items']->tax_info) || list_updated('tax_category'))
		$_SESSION['journal_items']->tax_info = $_SESSION['journal_items']->collect_tax_info();

	foreach ($_SESSION['journal_items']->tax_info as $name => $value)
		if (is_array($value))
		{
			foreach ($value as $id => $amount)
			{
				$_POST[$name.'_'.$id] = FormatService::priceFormat($amount);
			}
		} else
			$_POST[$name] = $value;
	$_POST['tax_date'] = $_SESSION['journal_items']->order_id ? $_SESSION['journal_items']->tax_info['tax_date'] : $_POST['date_'];
}

//-----------------------------------------------------------------------------------------------
if (isset($_POST['Process']))
{
	$input_error = 0;

	if ($_SESSION['journal_items']->count_gl_items() < 1) {
		UiMessageService::displayError(_(UI_TEXT_YOU_MUST_ENTER_AT_LEAST_ONE_JOURNAL_LINE));
		set_focus('code_id');
		$input_error = 1;
	}
	if (abs($_SESSION['journal_items']->gl_items_total()) > 0.001)
	{
		UiMessageService::displayError(_(UI_TEXT_THE_JOURNAL_MUST_BALANCE_DEBITS_EQUAL_TO_CREDITS_BEFORE_IT_CAN_BE_PROCESSED));
		set_focus('code_id');
		$input_error = 1;
	}
	$dateService = new DateService();

	if (!$dateService->isDate($_POST['date_'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_INVALID));
		set_focus('date_');
		$input_error = 1;
	} 
	elseif (!DateService::isDateInFiscalYearStatic($_POST['date_'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_OUT_OF_FISCAL_YEAR_OR_IS_CLOSED_FOR_FURTHER_DATA_ENTRY));
		set_focus('date_');
		$input_error = 1;
	} 
	if (!$dateService->isDate($_POST['event_date'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_INVALID));
		set_focus('event_date');
		$input_error = 1;
	}
	if (!$dateService->isDate($_POST['doc_date'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_INVALID));
		set_focus('doc_date');
		$input_error = 1;
	}
	if (!check_reference($_POST['ref'], ST_JOURNAL, $_SESSION['journal_items']->order_id))
	{
   		set_focus('ref');
   		$input_error = 1;
	}
	if (RequestService::getPostStatic('currency') != \FA\Services\CompanyPrefsService::getDefaultCurrency())
		if (isset($_POST['_ex_rate']) && !check_num('_ex_rate', 0.000001))
		{
			UiMessageService::displayError(_(UI_TEXT_THE_EXCHANGE_RATE_MUST_BE_NUMERIC_AND_GREATER_THAN_ZERO));
			set_focus('_ex_rate');
    		$input_error = 1;
		}

	if (RequestService::getPostStatic('_tabs_sel') == 'tax')
	{
		if (!$dateService->isDate($_POST['tax_date']))
		{
			UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_INVALID));
			set_focus('tax_date');
			$input_error = 1;
		} 
		elseif (!DateService::isDateInFiscalYearStatic($_POST['tax_date']))
		{
			UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_OUT_OF_FISCAL_YEAR_OR_IS_CLOSED_FOR_FURTHER_DATA_ENTRY));
			set_focus('tax_date');
			$input_error = 1;
		}
		// FIXME: check proper tax net input values, check sum of net values against total GL an issue warning
	}

	if (RequestService::checkValueStatic('taxable_trans'))
	{
	 	if (!tab_visible('tabs', 'tax'))
	 	{
			display_warning(_(UI_TEXT_CHECK_TAX_REGISTER_RECORDS_BEFORE_PROCESSING_TRANSACTION_OR_SWITCH_OFF_INCLUDE_IN_TAX_REGISTER_OPTION));
			$_POST['tabs_tax'] = true; // force tax tab select
   			$input_error = 1;
		} else {
			$taxes = get_all_tax_types();
			$net_amount = 0;
			while ($tax = db_fetch($taxes))
			{
				$tax_id = $tax['id'];
				$net_amount += RequestService::inputNumStatic('net_amount_'.$tax_id);
			}
			// in case no tax account used we have to guss tax register on customer/supplier used.
			if ($net_amount && !$_SESSION['journal_items']->has_taxes() && !$_SESSION['journal_items']->has_sub_accounts())
			{
				UiMessageService::displayError(_(UI_TEXT_CANNOT_DETERMINE_TAX_REGISTER_TO_BE_USED_YOU_HAVE_TO_MAKE_AT_LEAST_ONE_POSTING_EITHER_TO_TAX_OR_CUSTOMER_SUPPLIER_ACCOUNT_TO_USE_TAX_REGISTER));
				$_POST['tabs_gl'] = true; // force gl tab select
   				$input_error = 1;
			}
		}
	}

	if ($input_error == 1)
		unset($_POST['Process']);
}

if (isset($_POST['Process']))
{
	$cart = &$_SESSION['journal_items'];
	$new = $cart->order_id == 0;

	$cart->reference = $_POST['ref'];
	$cart->tran_date = $_POST['date_'];
	$cart->doc_date = $_POST['doc_date'];
	$cart->event_date = $_POST['event_date'];
	$cart->source_ref = $_POST['source_ref'];
	if (isset($_POST['memo_']))
		$cart->memo_ = $_POST['memo_'];

	$cart->currency = $_POST['currency'];
	if ($cart->currency != \FA\Services\CompanyPrefsService::getDefaultCurrency())
		$cart->rate = RequestService::inputNumStatic('_ex_rate');

	if (RequestService::checkValueStatic('taxable_trans'))
	{
		// complete tax register data
		$cart->tax_info['tax_date'] = $_POST['tax_date'];
		//$cart->tax_info['tax_group'] = $_POST['tax_group'];
		$taxes = get_all_tax_types();
		while ($tax = db_fetch($taxes))
		{
			$tax_id = $tax['id'];
			$cart->tax_info['net_amount'][$tax_id] = RequestService::inputNumStatic('net_amount_'.$tax_id);
			$cart->tax_info['rate'][$tax_id] = $tax['rate'];
		}
	} else
		$cart->tax_info = false;
	$trans_no = write_journal_entries($cart);

        // retain the reconciled status if desired by user
        if (isset($_POST['reconciled'])
            && $_POST['reconciled'] == 1) {
            $sql = "UPDATE ".TB_PREF."bank_trans SET reconciled=".db_escape($_POST['reconciled_date'])
                ." WHERE type=" . ST_JOURNAL . " AND trans_no=".db_escape($trans_no);

            db_query($sql, "Can't change reconciliation status");
        }

	$cart->clear_items();
	DateService::newDocDateStatic($_POST['date_']);
	unset($_SESSION['journal_items']);
	if($new)
		meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
	else
		meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$trans_no");
}

//-----------------------------------------------------------------------------------------------

function check_item_data()
{
	global $Ajax;

	if (!RequestService::getPostStatic('code_id')) {
   		UiMessageService::displayError(_(UI_TEXT_YOU_MUST_SELECT_GL_ACCOUNT));
		set_focus('code_id');
   		return false;
	}
	if (is_subledger_account(RequestService::getPostStatic('code_id'))) {
		if(!RequestService::getPostStatic('person_id')) {
	   		UiMessageService::displayError(_(UI_TEXT_YOU_MUST_SELECT_SUBLEDGER_ACCOUNT));
   			$Ajax->activate('items_table');
			set_focus('person_id');
	   		return false;
	   	}
	}
	if (isset($_POST['dimension_id']) && $_POST['dimension_id'] != 0 && dimension_is_closed($_POST['dimension_id'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_DIMENSION_IS_CLOSED));
		set_focus('dimension_id');
		return false;
	}

	if (isset($_POST['dimension2_id']) && $_POST['dimension2_id'] != 0 && dimension_is_closed($_POST['dimension2_id'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_DIMENSION_IS_CLOSED));
		set_focus('dimension2_id');
		return false;
	}

	if (!(RequestService::inputNumStatic('AmountDebit')!=0 ^ RequestService::inputNumStatic('AmountCredit')!=0) )
	{
		UiMessageService::displayError(_(UI_TEXT_YOU_MUST_ENTER_EITHER_A_DEBIT_AMOUNT_OR_A_CREDIT_AMOUNT));
		set_focus('AmountDebit');
    		return false;
  	}

	if (strlen($_POST['AmountDebit']) && !check_num('AmountDebit', 0)) 
	{
    		UiMessageService::displayError(_(UI_TEXT_THE_DEBIT_AMOUNT_ENTERED_IS_NOT_A_VALID_NUMBER_OR_IS_LESS_THAN_ZERO));
		set_focus('AmountDebit');
    		return false;
  	} elseif (strlen($_POST['AmountCredit']) && !check_num('AmountCredit', 0))
	{
    		UiMessageService::displayError(_(UI_TEXT_THE_CREDIT_AMOUNT_ENTERED_IS_NOT_A_VALID_NUMBER_OR_IS_LESS_THAN_ZERO));
		set_focus('AmountCredit');
    		return false;
  	}
	
	if (!is_tax_gl_unique(RequestService::getPostStatic('code_id'))) {
   		UiMessageService::displayError(_(UI_TEXT_CANNOT_POST_TO_GL_ACCOUNT_USED_BY_MORE_THAN_ONE_TAX_TYPE));
		set_focus('code_id');
   		return false;
	}

	if (!$_SESSION["wa_current_user"]->can_access('SA_BANKJOURNAL') && is_bank_account($_POST['code_id'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_YOU_CANNOT_MAKE_A_JOURNAL_ENTRY_FOR_A_BANK_ACCOUNT_PLEASE_USE_ONE_OF_THE_BANKING_FUNCTIONS_FOR_BANK_TRANSACTIONS));
		set_focus('code_id');
		return false;
	}

   	return true;
}

//-----------------------------------------------------------------------------------------------

function handle_update_item()
{
    if($_POST['UpdateItem'] != "" && check_item_data())
    {
    	if (RequestService::inputNumStatic('AmountDebit') > 0)
    		$amount = RequestService::inputNumStatic('AmountDebit');
    	else
    		$amount = -RequestService::inputNumStatic('AmountCredit');

    	$_SESSION['journal_items']->update_gl_item($_POST['Index'], $_POST['code_id'], 
    	    $_POST['dimension_id'], $_POST['dimension2_id'], $amount, $_POST['LineMemo'], '', RequestService::getPostStatic('person_id'));
    	unset($_SESSION['journal_items']->tax_info);
		line_start_focus();
    }
}

//-----------------------------------------------------------------------------------------------

function handle_delete_item($id)
{
	$_SESSION['journal_items']->remove_gl_item($id);
   	unset($_SESSION['journal_items']->tax_info);
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_new_item()
{
	if (!check_item_data())
		return;

	if (RequestService::inputNumStatic('AmountDebit') > 0)
		$amount = RequestService::inputNumStatic('AmountDebit');
	else
		$amount = -RequestService::inputNumStatic('AmountCredit');
	
	$_SESSION['journal_items']->add_gl_item($_POST['code_id'], $_POST['dimension_id'],
		$_POST['dimension2_id'], $amount, $_POST['LineMemo'], '', RequestService::getPostStatic('person_id'));
  	unset($_SESSION['journal_items']->tax_info);
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------
if (isset($_POST['_taxable_trans_update']))
{	if (!RequestService::checkValueStatic('taxable_trans'))
		$_POST['tabs_gl'] = true; // force tax tab select
	else
		set_focus('taxable_trans');
	$Ajax->activate('tabs');
}

if (tab_closed('tabs', 'gl'))
{
	$_SESSION['journal_items']->memo_ = $_POST['memo_'];
}
 elseif (tab_closed('tabs', 'tax'))
{
	$cart = &$_SESSION['journal_items'];
	$cart->tax_info['tax_date'] = $_POST['tax_date'];
	//$cart->tax_info['tax_group'] = $_POST['tax_group'];
	$taxes = get_all_tax_types();
	while ($tax = db_fetch($taxes))
	{
		$tax_id = $tax['id'];
		$cart->tax_info['net_amount'][$tax_id] = RequestService::inputNumStatic('net_amount_'.$tax_id);
		$cart->tax_info['rate'][$tax_id] = $tax['rate'];
	}
}
if (tab_opened('tabs', 'gl'))
{
	$_POST['memo_'] = $_SESSION['journal_items']->memo_;
} elseif (tab_opened('tabs', 'tax'))
{
	set_focus('tax_date');
}


$id = find_submit('Delete');
if ($id != -1)
	handle_delete_item($id);

if (isset($_POST['AddItem'])) 
	handle_new_item();

if (isset($_POST['UpdateItem'])) 
	handle_update_item();
	
if (isset($_POST['CancelItemChanges']))
	line_start_focus();

if (isset($_POST['go']))
{
	display_quick_entries($_SESSION['journal_items'], $_POST['quick'], RequestService::inputNumStatic('totamount'), QE_JOURNAL, RequestService::getPostStatic('aux_info'));
	$_POST['totamount'] = FormatService::priceFormat(0); $Ajax->activate('totamount');
	line_start_focus();
}

if (list_updated('tax_category'))
{
	$Ajax->activate('tabs');
}

//-----------------------------------------------------------------------------------------------

start_form();

display_order_header($_SESSION['journal_items']);

tabbed_content_start('tabs', array(
		'gl' => array(_(UI_TEXT_GL_POSTINGS), true),
		'tax' => array(_(UI_TEXT_TAX_REGISTER), RequestService::checkValueStatic('taxable_trans')),
	));
	
	switch (RequestService::getPostStatic('_tabs_sel')) {
		default:
		case 'gl':
			start_table(TABLESTYLE2, "width='90%'", 10);
			start_row();
			echo "<td>";
			display_gl_items(_(UI_TEXT_ROWS), $_SESSION['journal_items']);
			gl_options_controls();
			echo "</td>";
			end_row();
			end_table(1);
			break;

		case 'tax':
			update_tax_info();
			br();
			display_heading(_(UI_TEXT_TAX_REGISTER_RECORD));
			br();
			start_table(TABLESTYLE2, "width=40%");
			date_row(_(UI_TEXT_VAT_DATE_LABEL), 'tax_date', '', "colspan='3'");
			//tax_groups_list_row(_("Tax group:"), 'tax_group');
			end_table(1);

			start_table(TABLESTYLE2, "width=60%");
			table_header(array(_(UI_TEXT_NAME), _(UI_TEXT_INPUT_TAX), _(UI_TEXT_OUTPUT_TAX), _(UI_TEXT_NET_AMOUNT)));
			$taxes = get_all_tax_types();
			while ($tax = db_fetch($taxes))
			{
				start_row();
				label_cell($tax['name'].' '.$tax['rate'].'%');
				amount_cell(RequestService::inputNumStatic('tax_in_'.$tax['id']));
				amount_cell(RequestService::inputNumStatic('tax_out_'.$tax['id']));

				amount_cells(null, 'net_amount_'.$tax['id']);
				end_row();
			}
			end_table(1);
			break;
	};
	submit_center('Process', _(UI_TEXT_PROCESS_JOURNAL_ENTRY), true , 
		_(UI_TEXT_PROCESS_JOURNAL_ENTRY_ONLY_IF_DEBITS_EQUAL_TO_CREDITS), 'default');
br();
tabbed_content_end();

end_form();

end_page();
