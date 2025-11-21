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
$page_security = 'SA_EXCHANGERATE';
if (!isset($path_to_root)) $path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
require_once($path_to_root . "/includes/DateService.php");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/ui_strings.php");

$js = "";
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = UI_TEXT_REVALUATION_OF_CURRENCY_ACCOUNTS), false, false, "", $js);

if (isset($_GET['BA'])) 
{
	$BA = $_GET['BA'];
	$JE = $_GET['JE'];

	if ($BA != 0 || $JE !=0)
	{
		display_notification_centered(sprintf(_(UI_TEXT_JOURNAL_ENTRIES_FOR_BANK_ACCOUNTS_ADDED), $BA));
		display_notification_centered(sprintf(_(UI_TEXT_JOURNAL_ENTRIES_FOR_AR_AP_ACCOUNTS_ADDED), $JE));
	}
	else
   		display_notification_centered( _(UI_TEXT_NO_REVALUATION_WAS_NEEDED));
}


//---------------------------------------------------------------------------------------------
function check_data()
{
	if (!DateService::isDate($_POST['date']))
	{
		UiMessageService::displayError( _(UI_TEXT_THE_ENTERED_DATE_IS_INVALID));
		set_focus('date');
		return false;
	}
	if (!DateService::isDateInFiscalYearStatic($_POST['date']))
	{
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_OUT_OF_FISCAL_YEAR_OR_IS_CLOSED_FOR_FURTHER_DATA_ENTRY));
		set_focus('date');
		return false;
	}

	return true;
}

//---------------------------------------------------------------------------------------------

function handle_submit()
{
	if (!check_data())
		return;

	$trans = add_exchange_variation_all($_POST['date'], $_POST['memo_']);

	meta_forward($_SERVER['PHP_SELF'], "BA=".$trans[0]."&JE=".$trans[1]);
	//clear_data();
}


//---------------------------------------------------------------------------------------------

function display_reval()
{
	start_form();
	start_table(TABLESTYLE2);

	if (!isset($_POST['date']))
		$_POST['date'] = DateService::todayStatic();
    date_row(_(UI_TEXT_DATE_FOR_REVALUATION), 'date', '', null, 0, 0, 0, null, true);
    textarea_row(_(UI_TEXT_MEMO_LABEL), 'memo_', null, 40,4);
	end_table(1);

	submit_center('submit', _(UI_TEXT_REVALUATE_CURRENCIES), true, false);
	end_form();
}

//---------------------------------------------------------------------------------------------

function clear_data()
{
	unset($_POST['date_']);
	unset($_POST['memo_']);
}

//---------------------------------------------------------------------------------------------

if (RequestService::getPostStatic('submit'))
	handle_submit();

//---------------------------------------------------------------------------------------------

display_reval();

end_page();

