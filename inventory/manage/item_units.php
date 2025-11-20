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
$page_security = 'SA_UOM';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

page(_($help_context = "Units of Measure"));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");

include_once($path_to_root . "/inventory/includes/db/items_units_db.inc");

simple_page_mode(false);
//----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['abbr']) == 0)
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THE_UNIT_OF_MEASURE_CODE_CANNOT_BE_EMPTY));
		set_focus('abbr');
	}
	if (strlen(db_escape($_POST['abbr']))>(20+2))
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THE_UNIT_OF_MEASURE_CODE_IS_TOO_LONG));
		set_focus('abbr');
	}
	if (strlen($_POST['description']) == 0)
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THE_UNIT_OF_MEASURE_DESCRIPTION_CANNOT_BE_EMPTY));
		set_focus('description');
	}

	if ($input_error !=1) {
    	write_item_unit($selected_id, $_POST['abbr'], $_POST['description'], $_POST['decimals'] );
		if($selected_id != '')
			display_notification(_(UI_TEXT_SELECTED_UNIT_HAS_BEEN_UPDATED));
		else
			display_notification(_(UI_TEXT_NEW_UNIT_HAS_BEEN_ADDED));
		$Mode = 'RESET';
	}
}

//----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'stock_master'

	if (item_unit_used($selected_id))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THIS_UNIT_OF_MEASURE_BECAUSE_ITEMS_HAVE_BEEN_CREATED_USING_THIS_UNIT));

	}
	else
	{
		delete_item_unit($selected_id);
		display_notification(_(UI_TEXT_SELECTED_UNIT_HAS_BEEN_DELETED));
	}
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = '';
	$sav = RequestService::getPostStatic('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}

//----------------------------------------------------------------------------------

$result = get_all_item_units(RequestService::checkValueStatic('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='40%'");
$th = array(_(UI_TEXT_UNIT_LABEL), _(UI_TEXT_DESCRIPTION_LABEL), _(UI_TEXT_DECIMALS_LABEL), "", "");
inactive_control_column($th);

table_header($th);
$k = 0; //row colour counter

while ($myrow = db_fetch($result))
{

	alt_table_row_color($k);

	label_cell($myrow["abbr"]);
	label_cell($myrow["name"]);
	label_cell(($myrow["decimals"]==-1?_(UI_TEXT_USER_QUANTITY_DECIMALS):$myrow["decimals"]));
	$id = html_specials_encode($myrow["abbr"]);
	inactive_control_cell($id, $myrow["inactive"], 'item_units', 'abbr');
 	edit_button_cell("Edit".$id, _(UI_TEXT_EDIT));
 	delete_button_cell("Delete".$id, _(UI_TEXT_DELETE));
	end_row();
}

inactive_control_row($th);
end_table(1);

//----------------------------------------------------------------------------------

start_table(TABLESTYLE2);

if ($selected_id != '') 
{
 	if ($Mode == 'Edit') {
		//editing an existing item category

		$myrow = get_item_unit($selected_id);

		$_POST['abbr'] = $myrow["abbr"];
		$_POST['description']  = $myrow["name"];
		$_POST['decimals']  = $myrow["decimals"];
	}
	hidden('selected_id', $myrow["abbr"]);
}
if ($selected_id != '' && item_unit_used($selected_id)) {
    label_row(_(UI_TEXT_UNIT_ABBREVIATION_LABEL), $_POST['abbr']);
    hidden('abbr', $_POST['abbr']);
} else
    text_row(_(UI_TEXT_UNIT_ABBREVIATION_LABEL), 'abbr', null, 20, 20);
text_row(_(UI_TEXT_DESCRIPTIVE_NAME_LABEL), 'description', null, 40, 40);

number_list_row(_(UI_TEXT_DECIMAL_PLACES_LABEL), 'decimals', null, 0, 6, _(UI_TEXT_USER_QUANTITY_DECIMALS));

end_table(1);

submit_add_or_update_center($selected_id == '', '', 'both');

end_form();

end_page();

