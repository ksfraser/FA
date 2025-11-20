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
$page_security = 'SA_POSSETUP';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

page(_($help_context = UI_TEXT_POS_SETTINGS));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/sales/includes/db/sales_points_db.inc");

simple_page_mode(true);
//----------------------------------------------------------------------------------------------------

function can_process()
{
	if (strlen($_POST['name']) == 0)
	{
		UiMessageService::displayError(_(UI_TEXT_THE_POS_NAME_CANNOT_BE_EMPTY));
		set_focus('pos_name');
		return false;
	}
	return true;
}

//----------------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' && can_process())
{
	add_sales_point($_POST['name'], $_POST['location'], $_POST['account'],
		RequestService::checkValueStatic('cash'), RequestService::checkValueStatic('credit'));
	display_notification(_(UI_TEXT_NEW_POINT_OF_SALE_HAS_BEEN_ADDED));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------------

if ($Mode=='UPDATE_ITEM' && can_process())
{

	update_sales_point($selected_id, $_POST['name'], $_POST['location'],
		$_POST['account'], RequestService::checkValueStatic('cash'), RequestService::checkValueStatic('credit'));
	display_notification(_(UI_TEXT_SELECTED_POINT_OF_SALE_HAS_BEEN_UPDATED));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	if (key_in_foreign_table($selected_id, 'users', 'pos'))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THIS_POS_BECAUSE_IT_IS_USED_IN_USERS_SETUP));
	} else {
		delete_sales_point($selected_id);
		display_notification(_(UI_TEXT_SELECTED_POINT_OF_SALE_HAS_BEEN_DELETED));
		$Mode = 'RESET';
	}
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = RequestService::getPostStatic('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}
//----------------------------------------------------------------------------------------------------

$result = get_all_sales_points(RequestService::checkValueStatic('show_inactive'));

start_form();
start_table(TABLESTYLE);

$th = array (_(UI_TEXT_POS_NAME), _(UI_TEXT_CREDIT_SALE), _(UI_TEXT_CASH_SALE), _(UI_TEXT_LOCATION), _(UI_TEXT_DEFAULT_ACCOUNT), 
	 '','');
inactive_control_column($th);
table_header($th);
$k = 0;

while ($myrow = db_fetch($result))
{
    alt_table_row_color($k);
	label_cell($myrow["pos_name"], "nowrap");
	label_cell($myrow['credit_sale'] ? _(UI_TEXT_YES) : _(UI_TEXT_NO));
	label_cell($myrow['cash_sale'] ? _(UI_TEXT_YES) : _(UI_TEXT_NO));
	label_cell($myrow["location_name"], "");
	label_cell($myrow["bank_account_name"], "");
	inactive_control_cell($myrow["id"], $myrow["inactive"], "sales_pos", 'id');
 	edit_button_cell("Edit".$myrow['id'], _(UI_TEXT_EDIT));
 	delete_button_cell("Delete".$myrow['id'], _(UI_TEXT_DELETE));
	end_row();
}

inactive_control_row($th);
end_table(1);
//----------------------------------------------------------------------------------------------------

$cash = db_has_cash_accounts();

if (!$cash) display_note(_(UI_TEXT_TO_HAVE_CASH_POS_FIRST_DEFINE_AT_LEAST_ONE_CASH_BANK_ACCOUNT));

start_table(TABLESTYLE2);

if ($selected_id != -1)
{

 	if ($Mode == 'Edit') {
		$myrow = get_sales_point($selected_id);

		$_POST['name']  = $myrow["pos_name"];
		$_POST['location']  = $myrow["pos_location"];
		$_POST['account']  = $myrow["pos_account"];
		if ($myrow["credit_sale"]) $_POST['credit_sale']  = 1;
		if ($myrow["cash_sale"]) $_POST['cash_sale'] = 1;
	}
	hidden('selected_id', $selected_id);
} 

text_row_ex(_(UI_TEXT_POINT_OF_SALE_NAME).':', 'name', 20, 30);
if($cash) {
	check_row(_(UI_TEXT_ALLOWED_CREDIT_SALE_TERMS_SELECTION), 'credit', RequestService::checkValueStatic('credit_sale'));
	check_row(_(UI_TEXT_ALLOWED_CASH_SALE_TERMS_SELECTION), 'cash',  RequestService::checkValueStatic('cash_sale'));
	cash_accounts_list_row(_(UI_TEXT_DEFAULT_CASH_ACCOUNT_LABEL), 'account');
} else {
	hidden('credit', 1);
	hidden('account', 0);
}

locations_list_row(_(UI_TEXT_POS_LOCATION_LABEL), 'location');
end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();

