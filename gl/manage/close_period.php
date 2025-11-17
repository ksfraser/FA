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
***********************************************************************/

$page_security = 'SA_GLCLOSE';
if (!isset($path_to_root)) $path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
require_once($path_to_root . "/includes/DateService.php");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

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
		display_error( _("The entered date is invalid."));
		set_focus('date');
		return false;
	}
	if (!is_date_in_fiscalyears($_POST['date'], false))
	{
		display_error(_("Selected date is not in fiscal year or the year is closed."));
		set_focus('date');
		return false;
	}
	if (DateService::date1GreaterDate2Static(DateService::sql2dateStatic(get_company_pref('gl_closing_date')), $_POST['date']))
	{
		if (!$SysPrefs->allow_gl_reopen) {
			display_error(_("The entered date is earlier than date already selected as closing date."));
			set_focus('date');
			return false;
		} elseif (!user_check_access('SA_GLREOPEN')) {
			display_error(_("You are not allowed to reopen already closed transactions."));
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
			sprintf( _("All transactions resulting in GL accounts changes up to %s has been closed for further edition."),
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
	display_note(_("Using this feature you can prevent entering new transactions <br>
	and disable edition of already entered transactions up to specified date.<br>
	Only transactions which can generate GL postings are subject to the constraint."));

//---------------------------------------------------------------------------------------------

br(1);
start_form();
start_table(TABLESTYLE2);
if (!isset($_POST['date'])) {
	$cdate = DateService::sql2dateStatic(get_company_pref('gl_closing_date'));
	$_POST['date'] = $cdate ;// ? DateService::endMonthStatic(DateService::addMonthsStatic($cdate, 1)) : DateService::todayStatic();
}
date_row(_("End date of closing period:"), 'date');
end_table(1);

submit_center('submit', _("Close Transactions"), true, false);
end_form();

end_page();

