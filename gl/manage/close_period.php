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
$page_security = 'SA_GLSETUP';
$path_to_root = "../..";
$SysPrefs->show_hints = true;
include_once($path_to_root . "/includes/session.inc");
require_once($path_to_root . "/includes/DateService.php");
use FA\DateService;

$page_security = 'SA_GLCLOSE';

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/ui_strings.php");

$js = "";
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = "Closing GL Transactions"), false, false, "", $js);

//---------------------------------------------------------------------------------------------
function check_data()
{
	global $SysPrefs;
	
	if (!DateService::isDate($_POST['date']) || DateService::date1GreaterDate2Static($_POST['date'], DateService::todayStatic()))
	{
		UiMessageService::displayError( _(UI_TEXT_THE_ENTERED_DATE_IS_INVALID));
		set_focus('date');
		return false;
	}
	if (!DateService::isDateInAnyFiscalYearStatic($_POST['date'], false))
	{
		UiMessageService::displayError(_(UI_TEXT_SELECTED_DATE_NOT_IN_FISCAL_YEAR_OR_CLOSED));
		set_focus('date');
		return false;
	}
	if (DateService::date1GreaterDate2Static(DateService::sql2dateStatic(get_company_pref('gl_closing_date')), $_POST['date']))
	{
		if (!$SysPrefs->allow_gl_reopen) {
			UiMessageService::displayError(_(UI_TEXT_ENTERED_DATE_EARLIER_THAN_CLOSING_DATE));
			set_focus('date');
			return false;
		} elseif (!user_check_access('SA_GLREOPEN')) {
			UiMessageService::displayError(_(UI_TEXT_NOT_ALLOWED_TO_REOPEN_CLOSED_TRANSACTIONS));
			set_focus('date');
			return false;
		}
	}
	return true;
}

//---------------------------------------------------------------------------------------------

function handle_submit()
{
	if (!check_data())
		return;

	if (!close_transactions($_POST['date']))
	{
		display_notification(
			sprintf( _(UI_TEXT_TRANSACTIONS_CLOSED_UP_TO_DATE),
			DateService::sql2dateStatic(get_company_pref('gl_closing_date'))) );
	}

}


//---------------------------------------------------------------------------------------------

function clear_data()
{
	unset($_POST['date_']);
}

//---------------------------------------------------------------------------------------------

if (RequestService::getPostStatic('submit'))
	handle_submit();
else
	display_note(_(UI_TEXT_CLOSE_PERIOD_FEATURE_DESCRIPTION));

//---------------------------------------------------------------------------------------------

br(1);
start_form();
start_table(TABLESTYLE2);
if (!isset($_POST['date'])) {
	$cdate = DateService::sql2dateStatic(get_company_pref('gl_closing_date'));
	$_POST['date'] = $cdate ;// ? DateService::endMonthStatic(DateService::addMonthsStatic($cdate, 1)) : DateService::todayStatic();
}
date_row(_(UI_TEXT_END_DATE_OF_CLOSING_PERIOD_LABEL), 'date');
end_table(1);

submit_center('submit', _(UI_TEXT_CLOSE_TRANSACTIONS_BUTTON), true, false);
end_form();

end_page();

