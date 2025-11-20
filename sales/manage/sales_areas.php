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
$page_security = 'SA_SALESAREA';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

page(_($help_context = UI_TEXT_SALES_AREAS));

include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/includes/ui_strings.php");

simple_page_mode(true);

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	$input_error = 0;

	if (strlen($_POST['description']) == 0) 
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THE_AREA_DESCRIPTION_CANNOT_BE_EMPTY));
		set_focus('description');
	}

	if ($input_error != 1)
	{
    	if ($selected_id != -1) 
    	{
    		update_sales_area($selected_id, $_POST['description']);
			$note = _(UI_TEXT_SELECTED_SALES_AREA_HAS_BEEN_UPDATED);
    	} 
    	else 
    	{
    		add_sales_area($_POST['description']);
			$note = _(UI_TEXT_NEW_SALES_AREA_HAS_BEEN_ADDED);
    	}
    
		display_notification($note);    	
		$Mode = 'RESET';
	}
} 

if ($Mode == 'Delete')
{

	$cancel_delete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'debtors_master'

	if (key_in_foreign_table($selected_id, 'cust_branch', 'area'))
	{
		$cancel_delete = 1;
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THIS_AREA_BECAUSE_CUSTOMER_BRANCHES_HAVE_BEEN_CREATED_USING_THIS_AREA));
	} 
	if ($cancel_delete == 0) 
	{
		delete_sales_area($selected_id);

		display_notification(_(UI_TEXT_SELECTED_SALES_AREA_HAS_BEEN_DELETED));
	} //end if Delete area
	$Mode = 'RESET';
} 

if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = RequestService::getPostStatic('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}

//-------------------------------------------------------------------------------------------------

$result = get_sales_areas(RequestService::checkValueStatic('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='30%'");

$th = array(_(UI_TEXT_AREA_NAME), "", "");
inactive_control_column($th);

table_header($th);
$k = 0; 

while ($myrow = db_fetch($result)) 
{
	
	alt_table_row_color($k);
		
	label_cell($myrow["description"]);
	
	inactive_control_cell($myrow["area_code"], $myrow["inactive"], 'areas', 'area_code');

 	edit_button_cell("Edit".$myrow["area_code"], _(UI_TEXT_EDIT));
 	delete_button_cell("Delete".$myrow["area_code"], _(UI_TEXT_DELETE));
	end_row();
}
	
inactive_control_row($th);
end_table();
echo '<br>';

//-------------------------------------------------------------------------------------------------

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
 	if ($Mode == 'Edit') {
		//editing an existing area
		$myrow = get_sales_area($selected_id);

		$_POST['description']  = $myrow["description"];
	}
	hidden("selected_id", $selected_id);
} 

text_row_ex(_(UI_TEXT_AREA_NAME_LABEL), 'description', 30); 

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();
