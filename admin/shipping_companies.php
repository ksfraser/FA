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
$page_security = 'SA_SHIPPING';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");
page(_($help_context = "Shipping Company"));
include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/admin/db/shipping_db.inc");
include_once($path_to_root . "/includes/ui_strings.php");

simple_page_mode(true);
//----------------------------------------------------------------------------------------------

function can_process() 
{
	if (strlen($_POST['shipper_name']) == 0) 
	{
		UiMessageService::displayError(_(UI_TEXT_SHIPPING_COMPANY_NAME_CANNOT_BE_EMPTY_ERROR));
		set_focus('shipper_name');
		return false;
	}
	return true;
}

//----------------------------------------------------------------------------------------------
if ($Mode=='ADD_ITEM' && can_process()) 
{
	add_shipper($_POST['shipper_name'], $_POST['contact'], $_POST['phone'], $_POST['phone2'], $_POST['address']);
	\FA\Services\UiMessageService::displayNotification(_('New shipping company has been added'));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------

if ($Mode=='UPDATE_ITEM' && can_process()) 
{
	update_shipper($selected_id, $_POST['shipper_name'], $_POST['contact'], $_POST['phone'], $_POST['phone2'], $_POST['address']);
	\FA\Services\UiMessageService::displayNotification(_('Selected shipping company has been updated'));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
// PREVENT DELETES IF DEPENDENT RECORDS IN 'sales_orders'

	if (key_in_foreign_table($selected_id, 'sales_orders', 'ship_via'))
	{
		$cancel_delete = 1;
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_SHIPPING_COMPANY_SALES_ORDERS_ERROR));
	} 
	else 
	{
		// PREVENT DELETES IF DEPENDENT RECORDS IN 'debtor_trans'
		if (key_in_foreign_table($selected_id, 'debtor_trans', 'ship_via'))
		{
			$cancel_delete = 1;
			UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_SHIPPING_COMPANY_INVOICES_ERROR));
		} 
		else 
		{
			delete_shipper($selected_id);
			\FA\Services\UiMessageService::displayNotification(_('Selected shipping company has been deleted'));
		}
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
//----------------------------------------------------------------------------------------------

$result = get_shippers(RequestService::checkValueStatic('show_inactive'));

start_form();
start_table(TABLESTYLE);
$th = array(_(UI_TEXT_NAME), _(UI_TEXT_CONTACT_PERSON), _(UI_TEXT_PHONE_NUMBER), _(UI_TEXT_SECONDARY_PHONE), _(UI_TEXT_ADDRESS), "", "");
inactive_control_column($th);
table_header($th);

$k = 0; //row colour counter

while ($myrow = db_fetch($result)) 
{
	alt_table_row_color($k);
	label_cell($myrow["shipper_name"]);
	label_cell($myrow["contact"]);
	label_cell($myrow["phone"]);
	label_cell($myrow["phone2"]);
	label_cell($myrow["address"]);
	inactive_control_cell($myrow["shipper_id"], $myrow["inactive"], 'shippers', 'shipper_id');
 	edit_button_cell("Edit".$myrow["shipper_id"], _(UI_TEXT_EDIT));
 	delete_button_cell("Delete".$myrow["shipper_id"], _(UI_TEXT_DELETE));
	end_row();
}

inactive_control_row($th);
end_table(1);

//----------------------------------------------------------------------------------------------

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
 	if ($Mode == 'Edit') {
		//editing an existing Shipper

		$myrow = get_shipper($selected_id);

		$_POST['shipper_name']	= $myrow["shipper_name"];
		$_POST['contact']	= $myrow["contact"];
		$_POST['phone']	= $myrow["phone"];
		$_POST['phone2']	= $myrow["phone2"];
		$_POST['address'] = $myrow["address"];
	}
	hidden('selected_id', $selected_id);
}

text_row_ex(_(UI_TEXT_NAME).':', 'shipper_name', 40);

text_row_ex(_(UI_TEXT_CONTACT_PERSON_LABEL), 'contact', 30);

text_row_ex(_(UI_TEXT_PHONE_NUMBER_LABEL), 'phone', 32, 30);

text_row_ex(_(UI_TEXT_SECONDARY_PHONE_NUMBER_LABEL), 'phone2', 32, 30);

text_row_ex(_(UI_TEXT_ADDRESS_LABEL), 'address', 50);

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();
end_page();
