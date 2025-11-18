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
$page_security = 'SA_FISCALYEARS';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/admin/db/company_db.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/sales/includes/db/cust_trans_db.inc");
include_once($path_to_root . "/admin/db/maintenance_db.inc");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
use FA\Services\DateService;
$js = "";
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = UI_TEXT_FISCAL_YEARS_TITLE), false, false, "", $js);

simple_page_mode(true);
//---------------------------------------------------------------------------------------------

function check_data()
{
	$dateService = new DateService();
	if (!$dateService->isDate($_POST['from_date']) || is_date_in_fiscalyears($_POST['from_date']))
	{
		UiMessageService::displayError(_(UI_TEXT_INVALID_BEGIN_DATE_ERROR));
		set_focus('from_date');
		return false;
	}
	if (!$dateService->isDate($_POST['to_date']) || is_date_in_fiscalyears($_POST['to_date']))
	{
		UiMessageService::displayError(_(UI_TEXT_INVALID_END_DATE_ERROR));
		set_focus('to_date');
		return false;
	}
	if (!check_begin_end_date($_POST['from_date'], $_POST['to_date']))
	{
		UiMessageService::displayError(_(UI_TEXT_INVALID_BEGIN_OR_END_DATE_ERROR));
		set_focus('from_date');
		return false;
	}
	if (DateService::date1GreaterDate2Static($_POST['from_date'], $_POST['to_date']))
	{
		UiMessageService::displayError(_(UI_TEXT_BEGIN_DATE_BIGGER_THAN_END_ERROR));
		set_focus('from_date');
		return false;
	}
	return true;
}

function handle_submit()
{
	global $selected_id, $Mode;

	$ok = true;
	if ($selected_id != -1)
	{
		if ($_POST['closed'] == 1)
		{
			if (check_years_before($_POST['from_date'], false))
			{
				UiMessageService::displayError(_(UI_TEXT_CANNOT_CLOSE_YEAR_ERROR));
				set_focus('closed');
				return false;
			}	
			$ok = close_year($selected_id);
		}	
		else
			open_year($selected_id);
		if ($ok)
		{
   			update_fiscalyear($selected_id, $_POST['closed']);
			\FA\Services\UiMessageService::displayNotification(_('Selected fiscal year has been updated'));
		}	
	}
	else
	{
		if (!check_data())
			return false;
   		add_fiscalyear($_POST['from_date'], $_POST['to_date'], $_POST['closed']);
		\FA\Services\UiMessageService::displayNotification(_('New fiscal year has been added'));
	}
	$Mode = 'RESET';
}

//---------------------------------------------------------------------------------------------

function check_can_delete($selected_id)
{
	$myrow = get_fiscalyear($selected_id);
	// PREVENT DELETES IF DEPENDENT RECORDS IN gl_trans
	if (check_years_before(DateService::sql2dateStatic($myrow['begin']), true))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_FISCAL_YEAR_BEFORE_ERROR));
		return false;
	}
	if ($myrow['closed'] == 0)
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_FISCAL_YEAR_NOT_CLOSED_ERROR));
		return false;
	}
	return true;
}

function handle_delete()
{
	global $selected_id, $Mode;

	if (check_can_delete($selected_id)) {
	//only delete if used in neither customer or supplier, comp prefs, bank trans accounts
		delete_this_fiscalyear($selected_id);
		\FA\Services\UiMessageService::displayNotification(_('Selected fiscal year has been deleted'));
	}
	$Mode = 'RESET';
}

//---------------------------------------------------------------------------------------------

function display_fiscalyears()
{
	$company_year = get_company_pref('f_year');

	$result = get_all_fiscalyears();
	start_form();
	display_note(_(UI_TEXT_WARNING_DELETING_FISCAL_YEAR . 
		' are removed and converted into relevant balances. This process is irreversible!'), 
		0, 1, "class='currentfg'");
	start_table(TABLESTYLE);

	$th = array(_(UI_TEXT_FISCAL_YEAR_BEGIN), _(UI_TEXT_FISCAL_YEAR_END), _(UI_TEXT_CLOSED), "", "");
	table_header($th);

	$k=0;
	while ($myrow=db_fetch($result))
	{
    	if ($myrow['id'] == $company_year)
    	{
    		start_row("class='stockmankobg'");
    	}
    	else
    		alt_table_row_color($k);

		$from = DateService::sql2dateStatic($myrow["begin"]);
		$to = DateService::sql2dateStatic($myrow["end"]);
		if ($myrow["closed"] == 0)
		{
			$closed_text = _(UI_TEXT_NO);
		}
		else
		{
			$closed_text = _(UI_TEXT_YES);
		}
		label_cell($from);
		label_cell($to);
		label_cell($closed_text);
	 	edit_button_cell("Edit".$myrow['id'], _(UI_TEXT_EDIT));
		if ($myrow["id"] != $company_year) {
 			delete_button_cell("Delete".$myrow['id'], _(UI_TEXT_DELETE));
			submit_js_confirm("Delete".$myrow['id'],
				sprintf(_(UI_TEXT_CONFIRM_DELETE_FISCAL_YEAR), $from, $to));
		} else
			label_cell('');
		end_row();
	}

	end_table();
	end_form();
	display_note(_(UI_TEXT_CURRENT_FISCAL_YEAR_CANNOT_DELETE), 0, 0, "class='currentfg'");
}

//---------------------------------------------------------------------------------------------

function display_fiscalyear_edit($selected_id)
{
	global $Mode;

	start_form();
	start_table(TABLESTYLE2);

	if ($selected_id != -1)
	{
		if($Mode =='Edit')
		{
			$myrow = get_fiscalyear($selected_id);

			$_POST['from_date'] = DateService::sql2dateStatic($myrow["begin"]);
			$_POST['to_date']  = DateService::sql2dateStatic($myrow["end"]);
			$_POST['closed']  = $myrow["closed"];
		}
		hidden('from_date');
		hidden('to_date');
		label_row(_(UI_TEXT_FISCAL_YEAR_BEGIN_LABEL), $_POST['from_date']);
		label_row(_(UI_TEXT_FISCAL_YEAR_END_LABEL), $_POST['to_date']);
	}
	else
	{
		$begin = next_begin_date();
		if ($begin && $Mode != 'ADD_ITEM')
		{
			$_POST['from_date'] = $begin;
			$_POST['to_date'] = DateService::endMonthStatic(DateService::addMonthsStatic($begin, 11));
		}
		date_row(_(UI_TEXT_FISCAL_YEAR_BEGIN_LABEL), 'from_date', '', null, 0, 0, 1001);
		date_row(_(UI_TEXT_FISCAL_YEAR_END_LABEL), 'to_date', '', null, 0, 0, 1001);
	}
	hidden('selected_id', $selected_id);

	yesno_list_row(_(UI_TEXT_IS_CLOSED_LABEL), 'closed', null, "", "", false);

	end_table(1);

	submit_add_or_update_center($selected_id == -1, '', 'both');

	end_form();
}

//---------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{
	handle_submit();
}

//---------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	global $selected_id;
	handle_delete($selected_id);
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
}
//---------------------------------------------------------------------------------------------

display_fiscalyears();

echo '<br>';

display_fiscalyear_edit($selected_id);

//---------------------------------------------------------------------------------------------

end_page();

