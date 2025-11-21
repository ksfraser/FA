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
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
require_once($path_to_root . "/includes/DateService.php");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");

include_once($path_to_root . "/includes/ui_strings.php");

$js = "";
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = UI_TEXT_EXCHANGE_RATES_TITLE), false, false, "", $js);

simple_page_mode(false);

//---------------------------------------------------------------------------------------------
function check_data($selected_id)
{
	if (!DateService::isDate($_POST['date_']))
	{
		UiMessageService::displayError( _(UI_TEXT_THE_ENTERED_DATE_IS_INVALID));
		set_focus('date_');
		return false;
	}
	if (RequestService::inputNumStatic('BuyRate') <= 0)
	{
		UiMessageService::displayError( _(UI_TEXT_EXCHANGE_RATE_CANNOT_BE_ZERO_OR_NEGATIVE));
		set_focus('BuyRate');
		return false;
	}
	if (!$selected_id && get_date_exchange_rate($_POST['curr_abrev'], $_POST['date_']))
	{
		UiMessageService::displayError( _(UI_TEXT_EXCHANGE_RATE_FOR_DATE_ALREADY_EXISTS));
		set_focus('date_');
		return false;
	}
	return true;
}

//---------------------------------------------------------------------------------------------

function handle_submit()
{
	global $selected_id;

	if (!check_data($selected_id))
		return false;

	if ($selected_id != "")
	{

		update_exchange_rate($_POST['curr_abrev'], $_POST['date_'],
		RequestService::inputNumStatic('BuyRate'), RequestService::inputNumStatic('BuyRate'));
	}
	else
	{

		add_exchange_rate($_POST['curr_abrev'], $_POST['date_'],
		    RequestService::inputNumStatic('BuyRate'), RequestService::inputNumStatic('BuyRate'));
	}

	$selected_id = '';
	clear_data();
}

//---------------------------------------------------------------------------------------------

function handle_delete()
{
	global $selected_id;

	if ($selected_id == "")
		return;
	delete_exchange_rate($selected_id);
	$selected_id = '';
	clear_data();
}

//---------------------------------------------------------------------------------------------
function edit_link($row) 
{
  return button('Edit'.$row["id"], _(UI_TEXT_EDIT), true, ICON_EDIT);
}

function del_link($row) 
{
  return button('Delete'.$row["id"], _(UI_TEXT_DELETE), true, ICON_DELETE);
}

function display_rates($curr_code)
{

}

//---------------------------------------------------------------------------------------------

function display_rate_edit()
{
	global $selected_id, $Ajax, $SysPrefs;
	$xchg_rate_provider = ((isset($SysPrefs->xr_providers) && isset($SysPrefs->dflt_xr_provider))
		? $SysPrefs->xr_providers[$SysPrefs->dflt_xr_provider] : 'ECB');
	start_table(TABLESTYLE2);

	if ($selected_id != "")
	{
		//editing an existing exchange rate

		$myrow = get_exchange_rate($selected_id);

		$_POST['date_'] = DateService::sql2dateStatic($myrow["date_"]);
		$_POST['BuyRate'] = maxprec_format($myrow["rate_buy"]);

		hidden('selected_id', $selected_id);
		hidden('date_', $_POST['date_']);

		label_row(_(UI_TEXT_DATE_TO_USE_FROM_LABEL), $_POST['date_']);
	}
	else
	{
		$_POST['date_'] = DateService::todayStatic();
		$_POST['BuyRate'] = '';
		date_row(_(UI_TEXT_DATE_TO_USE_FROM_LABEL), 'date_');
	}
	if (isset($_POST['get_rate']))
	{
		$_POST['BuyRate'] = 
			maxprec_format(retrieve_exrate($_POST['curr_abrev'], $_POST['date_']));
		$Ajax->activate('BuyRate');
	}
	amount_row(_(UI_TEXT_EXCHANGE_RATE_LABEL), 'BuyRate', null, '',
	  	submit('get_rate',_(UI_TEXT_GET_BUTTON), false, _(UI_TEXT_GET_CURRENT_RATE_FROM) . ' ' . $xchg_rate_provider , true), 'max');

	end_table(1);

	submit_add_or_update_center($selected_id == '', '', 'both');

	display_note(_(UI_TEXT_EXCHANGE_RATES_ENTERED_AGAINST_COMPANY_CURRENCY), 1);
}

//---------------------------------------------------------------------------------------------

function clear_data()
{
	unset($_POST['selected_id']);
	unset($_POST['date_']);
	unset($_POST['BuyRate']);
}

//---------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
	handle_submit();

//---------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
	handle_delete();


//---------------------------------------------------------------------------------------------

start_form();

if (!isset($_POST['curr_abrev']))
	$_POST['curr_abrev'] = get_global_curr_code();

echo "<center>";
echo _(UI_TEXT_SELECT_A_CURRENCY_LABEL) . "  ";
echo currencies_list('curr_abrev', null, true, true);
echo "</center>";

// if currency sel has changed, clear the form
if ($_POST['curr_abrev'] != get_global_curr_code())
{
	clear_data();
	$selected_id = "";
}

set_global_curr_code(RequestService::getPostStatic('curr_abrev'));

$sql = get_sql_for_exchange_rates(RequestService::getPostStatic('curr_abrev'));

$cols = array(
	_(UI_TEXT_DATE_TO_USE_FROM_HEADER) => 'date', 
	_(UI_TEXT_EXCHANGE_RATE_HEADER) => 'rate',
	array('insert'=>true, 'fun'=>'edit_link'),
	array('insert'=>true, 'fun'=>'del_link'),
);
$table =& new_db_pager('orders_tbl', $sql, $cols);

if (BankingService::isCompanyCurrencyStatic(RequestService::getPostStatic('curr_abrev')))
{

	display_note(_(UI_TEXT_SELECTED_CURRENCY_IS_COMPANY_CURRENCY), 2);
	display_note(_(UI_TEXT_COMPANY_CURRENCY_IS_BASE_CURRENCY), 1);
}
else
{

	br(1);
	$table->width = "40%";
	if ($table->rec_count == 0)
		$table->ready = false;
	display_db_pager($table);
   	br(1);
    display_rate_edit();
}

end_form();

end_page();

