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
$page_security = 'SA_PRINTERS';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

page(_($help_context = UI_TEXT_PRINTER_LOCATIONS_TITLE));

include($path_to_root . "/admin/db/printers_db.inc");
include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/includes/ui_strings.php");

simple_page_mode(true);
//-------------------------------------------------------------------------------------------
if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	$error = 0;

	if (empty($_POST['name']))
	{
		$error = 1;
		UiMessageService::displayError(_(UI_TEXT_PRINTER_NAME_EMPTY_ERROR));
		set_focus('name');
	} 
	elseif (empty($_POST['host'])) 
	{
		display_notification_centered(_(UI_TEXT_PRINTING_TO_SERVER_AT_USER_IP_NOTICE));
	} 
	elseif (!check_num('tout', 0, 60)) 
	{
		$error = 1;
		UiMessageService::displayError(_(UI_TEXT_TIMEOUT_INVALID_ERROR));
		set_focus('tout');
	} 

	if ($error != 1)
	{
		write_printer_def($selected_id, RequestService::getPostStatic('name'), RequestService::getPostStatic('descr'),
			RequestService::getPostStatic('queue'), RequestService::getPostStatic('host'), RequestService::inputNumStatic('port',0),
			RequestService::inputNumStatic('tout',0));

		display_notification_centered($selected_id==-1? 
			_('New printer definition has been created') 
			:_('Selected printer definition has been updated'));
 		$Mode = 'RESET';
	}
}

if ($Mode == 'Delete')
{
	// PREVENT DELETES IF DEPENDENT RECORDS IN print_profiles

	if (key_in_foreign_table($selected_id, 'print_profiles', 'printer'))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_PRINTER_ERROR));
	} 
	else 
	{
		delete_printer($selected_id);
		\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_SELECTED_PRINTER_DELETED_NOTICE));
	}
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	unset($_POST);
}
//-------------------------------------------------------------------------------------------------

$result = get_all_printers();
start_form();
start_table(TABLESTYLE);
$th = array(_(UI_TEXT_NAME), _(UI_TEXT_DESCRIPTION), _(UI_TEXT_HOST), _(UI_TEXT_PRINTER_QUEUE),'','');
table_header($th);

$k = 0; //row colour counter
while ($myrow = db_fetch($result)) 
{
	alt_table_row_color($k);

    label_cell($myrow['name']);
    label_cell($myrow['description']);
    label_cell($myrow['host']);
    label_cell($myrow['queue']);
 	edit_button_cell("Edit".$myrow['id'], _(UI_TEXT_EDIT));
 	delete_button_cell("Delete".$myrow['id'], _(UI_TEXT_DELETE));
    end_row();


} //END WHILE LIST LOOP

end_table();
end_form();
echo '<br>';

//-------------------------------------------------------------------------------------------------

start_form();

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
	if ($Mode == 'Edit') {
		$myrow = get_printer($selected_id);
		$_POST['name'] = $myrow['name'];
		$_POST['descr'] = $myrow['description'];
		$_POST['queue'] = $myrow['queue'];
		$_POST['tout'] = $myrow['timeout'];
		$_POST['host'] = $myrow['host'];
		$_POST['port'] = $myrow['port'];
	}
	hidden('selected_id', $selected_id);
} else {
	if(!isset($_POST['host']))
		$_POST['host'] = 'localhost';
	if(!isset($_POST['port']))
		$_POST['port'] = '515';
}

text_row(_(UI_TEXT_PRINTER_NAME).':', 'name', null, 20, 20);
text_row(_(UI_TEXT_PRINTER_DESCRIPTION).':', 'descr', null, 40, 60);
text_row(_(UI_TEXT_HOST_NAME_OR_IP).':', 'host', null, 30, 40);
text_row(_(UI_TEXT_PORT).':', 'port', null, 5, 5);
text_row(_(UI_TEXT_PRINTER_QUEUE).':', 'queue', null, 20, 20);
text_row(_(UI_TEXT_TIMEOUT).':', 'tout', null, 5, 5);

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();

