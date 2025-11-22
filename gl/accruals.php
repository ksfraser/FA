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
/**********************************************
Author: Joe Hunt
Name: Revenue / Cost Accruals v2.2
Free software under GNU GPL
***********************************************/
$page_security = 'SA_ACCRUALS';
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui/items_cart.inc");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
require_once($path_to_root . "/includes/CompanyPrefsService.php");
use FA\Services\DateService;

$js = get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

// Begin the UI
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");

$_SESSION['page_title'] = _($help_context = UI_TEXT_REVENUE_COST_ACCRUALS);
page($_SESSION['page_title'], false, false,'', $js);

//--------------------------------------------------------------------------------------------------
if (!isset($_POST['freq']))
	$_POST['freq'] = 3;
// If the import button was selected, we'll process the form here.  (If not, skip to actual content below.)
if (isset($_POST['go']) || isset($_POST['show']))
{
	$input_error = 0;
	$dateService = new DateService();
	if (!$dateService->isDate($_POST['date_']))
	{
		UiMessageService::displayError(_("The entered date is invalid."));
		set_focus('date_');
		$input_error = 1;
	}
	elseif (!DateService::isDateInFiscalYearStatic($_POST['date_']))
	{
		UiMessageService::displayError(_("The entered date is out of fiscal year or is closed for further data entry."));
		set_focus('date_');
		$input_error = 1;
	}
	elseif (RequestService::inputNumStatic('amount', 0) == 0.0)
	{
		UiMessageService::displayError(_(UI_TEXT_THE_AMOUNT_CAN_NOT_BE_0));
		set_focus('amount');
		$input_error = 1;
	}
	elseif (RequestService::inputNumStatic('periods', 0) < 1)
	{
		UiMessageService::displayError(_(UI_TEXT_THE_PERIODS_MUST_BE_GREATER_THAN_0));
		set_focus('periods');
		$input_error = 1;
	}
	if ($input_error == 0)
	{
		$periods = RequestService::inputNumStatic('periods');
		$per = $periods - 1;
		$date = $date_ = RequestService::getPostStatic('date_');
		$freq = RequestService::getPostStatic('freq');
		if ($freq == 3 || $freq == 4) {
			$date_ = DateService::beginMonthStatic($date_); // avoid skip on shorter months
			$date  = DateService::endMonthStatic($date_); // avoid skip on shorter months
		}
		
		$lastdate = ($freq == 1 ? DateService::addDaysStatic($date_, 7*$per) :
			($freq == 2 ? DateService::addDaysStatic($date_, 14*$per) :
			($freq == 3 ? DateService::endMonthStatic(DateService::addMonthsStatic($date_, $per)) : 
			DateService::endMonthStatic(DateService::addMonthsStatic($date_, 3*$per)))));
		if (!DateService::isDateInAnyFiscalYearStatic($lastdate, false))
		{
			UiMessageService::displayError(_(UI_TEXT_SOME_OF_THE_PERIOD_DATES_ARE_OUTSIDE_THE_FISCAL_YEAR_OR_ARE_CLOSED_FOR_FURTHER_DATA_ENTRY_CREATE_A_NEW_FISCAL_YEAR_FIRST));
			set_focus('date_');
			$input_error = 1;
		}
		if ($input_error == 0)
		{
			$amount = RequestService::inputNumStatic('amount');
			$am = round2($amount / $periods, \FA\UserPrefsCache::getPriceDecimals());
			if ($am * $periods != $amount)
				$am0 = $am + $amount - $am * $periods;
			else
				$am0 = $am;
			if (RequestService::getPostStatic('memo_') != "")
				$memo = $_POST['memo_'];
			else
				$memo = sprintf(_(UI_TEXT_ACCRUALS_FOR), $amount);
			if (isset($_POST['go']))
				begin_transaction();
			else
			{
				start_table(TABLESTYLE);
				$dim = \FA\Services\CompanyPrefsService::getUseDimensions();

				$first_cols = array(_(UI_TEXT_DATE), _(UI_TEXT_ACCOUNT_COLUMN));
				if ($dim == 2)
					$dim_cols = array(_(UI_TEXT_DIMENSION). " 1", _(UI_TEXT_DIMENSION). " 2");
				elseif ($dim == 1)
					$dim_cols = array(_(UI_TEXT_DIMENSION));
				else
					$dim_cols = array();

				$remaining_cols = array(_(UI_TEXT_DEBIT), _(UI_TEXT_CREDIT), _(UI_TEXT_MEMO));

				$th = array_merge($first_cols, $dim_cols, $remaining_cols);
				table_header($th);
				$k = 0;
			}
			for ($i = 0; $i < $periods; $i++)
			{
				if ($i > 0)
				{
					switch($freq)
					{
						case 1:
							$date = $date_ = DateService::addDaysStatic($date_, 7);
							break;
						case 2:
							$date = $date_ = DateService::addDaysStatic($date_, 14);
							break;
						case 3:
							$date_ = DateService::addMonthsStatic($date_, 1);
							$date = DateService::endMonthStatic($date_);
							break;
						case 4:
							$date_ = DateService::addMonthsStatic($date_, 3);
							$date = DateService::endMonthStatic($date_);
							break;
					}
					$am0 = $am;
				}
				if (isset($_POST['go']))
				{
					$cart = new items_cart(ST_JOURNAL);
					$cart->memo_ = $memo;
					$cart->reference = $Refs->get_next(ST_JOURNAL, null, $date);
					$cart->tran_date = $cart->doc_date = $cart->event_date = $date;
					$cart->add_gl_item(RequestService::getPostStatic('acc_act'), 0, 0, -$am0, $cart->reference);
					$cart->add_gl_item(RequestService::getPostStatic('res_act'), RequestService::getPostStatic('dimension_id'),
						RequestService::getPostStatic('dimension2_id'), $am0, $cart->reference);
					write_journal_entries($cart);
					$cart->clear_items();
				}
				else
				{
					alt_table_row_color($k);
					label_cell($date);
					label_cell($_POST['acc_act'] . " " . get_gl_account_name($_POST['acc_act']));
					if ($dim > 0)
						label_cell("");
					if ($dim > 1)
						label_cell("");
					display_debit_or_credit_cells($am0 * -1);
					label_cell($memo);
					alt_table_row_color($k);
					label_cell($date);
					label_cell($_POST['res_act'] . " " . get_gl_account_name($_POST['res_act']));
					if ($dim > 0)
						label_cell(get_dimension_string($_POST['dimension_id'], true));
					if ($dim > 1)
						label_cell(get_dimension_string($_POST['dimension2_id'], true));
					display_debit_or_credit_cells($am0);
					label_cell($memo);
				}
			}
			if (isset($_POST['go']))
			{
				commit_transaction();
				display_notification_centered(_(UI_TEXT_REVENUE_COST_ACCRUALS_HAVE_BEEN_PROCESSED));
				$_POST['date_'] = $_POST['amount'] = $_POST['periods'] = "";
			}
			else
			{
				end_table(1);
				display_notification_centered(_(UI_TEXT_SHOWING_GL_TRANSACTIONS));
			}
		}
	}
}

function frequency_list_row($label, $name, $selected=null)
{
	echo "<tr>\n";
	label_cell($label, "class='label'");
	echo "<td>\n";
	$freq = array(
		'1'=> _(UI_TEXT_WEEKLY),
		'2'=> _(UI_TEXT_BI_WEEKLY),
		'3' => _(UI_TEXT_MONTHLY),
		'4' => _(UI_TEXT_QUARTERLY),
	);
	echo array_selector($name, $selected, $freq);
	echo "</td>\n";
	echo "</tr\n";
}

$dim = \FA\Services\CompanyPrefsService::getUseDimensions();

start_form(false, false, "", "accrual");
start_table(TABLESTYLE2);

date_row(_("Date"), 'date_', _('First date of Accruals'), true, 0, 0, 0, null, true);
start_row();
	label_cell(_(UI_TEXT_ACCRUED_BALANCE_ACCOUNT), "class='label'");
gl_all_accounts_list_cells(null, 'acc_act', null, true, false, false, true);
end_row();
	gl_all_accounts_list_row(_(UI_TEXT_REVENUE_COST_ACCOUNT), 'res_act', null, true);if ($dim >= 1)
	dimensions_list_row(_("Dimension"), 'dimension_id', null, true, " ", false, 1);
if ($dim > 1)
	dimensions_list_row(_("Dimension")." 2", 'dimension2_id', null, true, " ", false, 2);

$url = "gl/view/accrual_trans.php?act=".RequestService::getPostStatic('acc_act')."&date=".RequestService::getPostStatic('date_');
amount_row(_("Amount"), 'amount', null, null, viewer_link(_("Search Amount"), $url, "", "", ICON_VIEW));

frequency_list_row(_("Frequency"), 'freq', null);

text_row(_("Periods"), 'periods', null, 3, 3);
textarea_row(_("Memo"), 'memo_', null, 35, 3);

end_table(1);
submit_center_first('show', _("Show GL Rows"));//,true,false,'process',ICON_SUBMIT);
submit_center_last('go', _("Process Accruals"));//,true,false,'process',ICON_SUBMIT);
submit_js_confirm('go', _("Are you sure you want to post accruals?"));

end_form();

end_page();
