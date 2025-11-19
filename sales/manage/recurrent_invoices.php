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
$page_security = 'SA_SRECURRENT';
if (!isset($path_to_root)) $path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
require_once($path_to_root . "/includes/DateService.php");
include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/sales/includes/sales_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 600);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(_($help_context = "Recurrent Invoices"), false, false, "", $js);

check_db_has_template_orders(_(UI_TEXT_THERE_IS_NO_TEMPLATE_ORDER_IN_DATABASE_YOU_HAVE_TO_CREATE_AT_LEAST_ONE_SALES_ORDER_MARKED_AS_TEMPLATE_TO_BE_ABLE_TO_DEFINE_RECURRENT_INVOICES));

simple_page_mode(true);

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	$input_error = 0;

	if (!RequestService::getPostStatic('group_no'))
	{
		$input_error = 1;
		if (RequestService::getPostStatic('debtor_no'))
			UiMessageService::displayError(_(UI_TEXT_THIS_CUSTOMER_HAS_NO_BRANCHES_PLEASE_DEFINE_AT_LEAST_ONE_BRANCH_FOR_THIS_CUSTOMER_FIRST));
		else
			UiMessageService::displayError(_(UI_TEXT_THERE_ARE_NO_TAX_GROUPS_DEFINED_IN_THE_SYSTEM_AT_LEAST_ONE_TAX_GROUP_IS_REQUIRED_BEFORE_PROCEEDING));
		set_focus('debtor_no');
	}
	if (strlen($_POST['description']) == 0) 
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THE_INVOICE_DESCRIPTION_CANNOT_BE_EMPTY));
		set_focus('description');
	}
	if (!check_recurrent_invoice_description($_POST['description'], $selected_id))
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THIS_RECURRENT_INVOICE_DESCRIPTION_IS_ALREADY_IN_USE));
		set_focus('description');
	}
	if (!DateService::isDate($_POST['begin']))
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_INVALID));
		set_focus('begin');
	}
	if (!DateService::isDate($_POST['end']))
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_INVALID));
		set_focus('end');
	}
	if (isset($_POST['last_sent']) && !DateService::isDate($_POST['last_sent'])) {
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_INVALID));
		set_focus('last_sent');
	}
	if (!$_POST['days'] && !$_POST['monthly'])
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_NO_RECURENCE_INTERVAL_HAS_BEEN_ENTERED));
		set_focus('days');
	}

	if ($input_error != 1)
	{
    	if ($selected_id != -1) 
    	{
    		update_recurrent_invoice($selected_id, $_POST['description'], $_POST['order_no'], RequestService::inputNumStatic('debtor_no'), 
    			RequestService::inputNumStatic('group_no'), RequestService::inputNumStatic('days', 0), RequestService::inputNumStatic('monthly', 0), $_POST['begin'], $_POST['end']);
    		if (isset($_POST['last_sent']))	
				update_last_sent_recurrent_invoice($selected_id, $_POST['last_sent']);
			$note = _('Selected recurrent invoice has been updated');
    	} 
    	else 
    	{
    		add_recurrent_invoice($_POST['description'], $_POST['order_no'], RequestService::inputNumStatic('debtor_no'), RequestService::inputNumStatic('group_no'),
    			RequestService::inputNumStatic('days', 0), RequestService::inputNumStatic('monthly', 0), $_POST['begin'], $_POST['end']);
			$note = _('New recurrent invoice has been added');
    	}
    
		display_notification($note);
		$Mode = 'RESET';
	}
} 

if ($Mode == 'Delete')
{

	$cancel_delete = 0;

	if ($cancel_delete == 0) 
	{
		delete_recurrent_invoice($selected_id);

		display_notification(_('Selected recurrent invoice has been deleted'));
	} //end if Delete area
	$Mode = 'RESET';
} 

if ($Mode == 'RESET')
{
	$selected_id = -1;
	unset($_POST);
}
//-------------------------------------------------------------------------------------------------

$result = get_recurrent_invoices();

start_form();
start_table(TABLESTYLE, "width=70%");
$th = array(_(UI_TEXT_DESCRIPTION), _(UI_TEXT_TEMPLATE_NO),_(UI_TEXT_CUSTOMER),_(UI_TEXT_BRANCH)."/"._(UI_TEXT_GROUP),_(UI_TEXT_DAYS),_(UI_TEXT_MONTHLY),_(UI_TEXT_BEGIN),_(UI_TEXT_END),_(UI_TEXT_LAST_CREATED),"", "");
table_header($th);
$k = 0;
while ($myrow = db_fetch($result)) 
{
	$begin = DateService::sql2dateStatic($myrow["begin"]);
	$end = DateService::sql2dateStatic($myrow["end"]);
	$last_sent = $myrow["last_sent"] == '0000-00-00' ? '' : DateService::sql2dateStatic($myrow["last_sent"]);
	
	alt_table_row_color($k);
		
	label_cell($myrow["description"]);
	label_cell(get_customer_trans_view_str(ST_SALESORDER, $myrow["order_no"]), "nowrap align='right'");
	if ($myrow["debtor_no"] == 0)
	{
		label_cell("");
		label_cell(get_sales_group_name($myrow["group_no"]));
	}	
	else
	{
		label_cell(get_customer_name($myrow["debtor_no"]));
		label_cell(get_branch_name($myrow['group_no']));
	}	
	label_cell($myrow["days"]);
	label_cell($myrow['monthly']);
	label_cell($begin);
	label_cell($end);
	label_cell($last_sent);
 	edit_button_cell("Edit".$myrow["id"], _(UI_TEXT_EDIT));
 	delete_button_cell("Delete".$myrow["id"], _(UI_TEXT_DELETE));
 	end_row();
}
end_table();

end_form();
echo '<br>';

//-------------------------------------------------------------------------------------------------

start_form();

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
 	if ($Mode == 'Edit') {
		//editing an existing area
		$myrow = get_recurrent_invoice($selected_id);

		$_POST['description']  = $myrow["description"];
		$_POST['order_no']  = $myrow["order_no"];
		$_POST['debtor_no']  = $myrow["debtor_no"];
		$_POST['group_no']  = $myrow["group_no"];
		$_POST['days']  = $myrow["days"];
		$_POST['monthly']  = $myrow["monthly"];
		$_POST['begin']  = DateService::sql2dateStatic($myrow["begin"]);
		$_POST['end']  = DateService::sql2dateStatic($myrow["end"]);
		$_POST['last_sent']  = ($myrow['last_sent']=="0000-00-00"?"":DateService::sql2dateStatic($myrow["last_sent"]));
	} 
	hidden("selected_id", $selected_id);
}


text_row_ex(_(UI_TEXT_DESCRIPTION_LABEL), 'description', 50); 

templates_list_row(_(UI_TEXT_TEMPLATE_LABEL), 'order_no');

customer_list_row(_(UI_TEXT_CUSTOMER_LABEL), 'debtor_no', null, " ", true);

if ($_POST['debtor_no'] > 0)
	customer_branches_list_row(_(UI_TEXT_BRANCH_LABEL), $_POST['debtor_no'], 'group_no', null, false);
else	
	sales_groups_list_row(_(UI_TEXT_SALES_GROUP_LABEL), 'group_no', null);

small_amount_row(_(UI_TEXT_DAYS_LABEL), 'days', 0, null, null, 0);

small_amount_row(_(UI_TEXT_MONTHLY_LABEL), 'monthly', 0, null, null, 0);

date_row(_(UI_TEXT_BEGIN_LABEL), 'begin');

date_row(_(UI_TEXT_END_LABEL), 'end', null, null, 0, 0, 5);

if ($selected_id != -1 && @$_POST['last_sent'] != "")
	date_row(_(UI_TEXT_LAST_CREATED_LABEL), 'last_sent');

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();
?>
