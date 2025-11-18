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
$page_security = 'SA_CRMCATEGORY';
$path_to_root = '..';
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/db/crm_contacts_db.inc");

page(_($help_context = "Contact Categories"));

include($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");

simple_page_mode(true);

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	$input_error = 0;

	if (strlen($_POST['description']) == 0) 
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_CATEGORY_DESCRIPTION_CANNOT_BE_EMPTY));
		set_focus('description');
	}

	if ($input_error != 1)
	{
    	if ($selected_id != -1) 
    	{
    		update_crm_category($selected_id, RequestService::getPostStatic('type'), RequestService::getPostStatic('subtype'), 
    			RequestService::getPostStatic('name'), RequestService::getPostStatic('description'));
			$note = _('Selected contact category has been updated');
    	} 
    	else 
    	{
    		add_crm_category(RequestService::getPostStatic('type'), RequestService::getPostStatic('subtype'), RequestService::getPostStatic('name'),
    			RequestService::getPostStatic('description'));
			$note = _('New contact category has been added');
    	}

		\FA\Services\UiMessageService::displayNotification($note);
		$Mode = 'RESET';
	}
} 

if ($Mode == 'Delete')
{
	$cancel_delete = 0;

	if (is_crm_category_used($selected_id))
	{
		$cancel_delete = 1;
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_CATEGORY_WITH_CONTACTS));
	} 
	if ($cancel_delete == 0) 
	{
		delete_crm_category($selected_id);

		\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_CATEGORY_HAS_BEEN_DELETED));
	}
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

$result = get_crm_categories(RequestService::checkValueStatic('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='70%'");

$th = array(_(UI_TEXT_CATEGORY_TYPE), _(UI_TEXT_CATEGORY_SUBTYPE), _(UI_TEXT_SHORT_NAME), _(UI_TEXT_DESCRIPTION),  "", "&nbsp;");
inactive_control_column($th);

table_header($th);
$k = 0; 

while ($myrow = db_fetch($result)) 
{
	
	alt_table_row_color($k);
		
	label_cell($myrow["type"]);
	label_cell($myrow["action"]);
	label_cell($myrow["name"]);
	label_cell($myrow["description"]);
	
	inactive_control_cell($myrow["id"], $myrow["inactive"], 'crm_categories', 'id');

 	edit_button_cell("Edit".$myrow["id"], _(UI_TEXT_EDIT));
 	if ($myrow["system"])
		label_cell('');
	else
		delete_button_cell("Delete".$myrow["id"], _(UI_TEXT_DELETE));
	end_row();
}
	
inactive_control_row($th);
end_table(1);

//-------------------------------------------------------------------------------------------------
start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
 	if ($Mode == 'Edit') {
		//editing an existing area
		$myrow = get_crm_category($selected_id);

		$_POST['name']  = $myrow["name"];
		$_POST['type']  = $myrow["type"];
		$_POST['subtype']  = $myrow["action"];
		$_POST['description']  = $myrow["description"];
	}
	hidden("selected_id", $selected_id);
} 

if ($Mode == 'Edit' && $myrow['system']) {
	label_row(_(UI_TEXT_CONTACT_CATEGORY_TYPE_LABEL), $_POST['type']);
	label_row(_(UI_TEXT_CONTACT_CATEGORY_SUBTYPE_LABEL), $_POST['subtype']);
} else {
//	crm_category_type_list_row(_("Contact Category Type:"), 'type', null, _('Other'));
	text_row_ex(_(UI_TEXT_CONTACT_CATEGORY_TYPE_LABEL), 'type', 30); 
	text_row_ex(_(UI_TEXT_CONTACT_CATEGORY_SUBTYPE_LABEL), 'subtype', 30); 
}

text_row_ex(_(UI_TEXT_CATEGORY_SHORT_NAME_LABEL), 'name', 30); 
textarea_row(_(UI_TEXT_CATEGORY_DESCRIPTION_LABEL), 'description', null, 60, 4);

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();
