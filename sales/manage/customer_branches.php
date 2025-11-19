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
$page_security = 'SA_CUSTOMER';
$path_to_root="../..";

include($path_to_root . "/includes/db_pager.inc");
include($path_to_root . "/includes/session.inc");

$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);

page(_($help_context = "Customer Branches"), @$_REQUEST['popup'], false, "", $js);

include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/includes/ui_strings.php");
include($path_to_root . "/includes/ui/contacts_view.inc");

//-----------------------------------------------------------------------------------------------

check_db_has_customers(_(UI_TEXT_THERE_ARE_NO_CUSTOMERS_DEFINED_IN_THE_SYSTEM_PLEASE_DEFINE_A_CUSTOMER_TO_ADD_CUSTOMER_BRANCHES));

check_db_has_sales_people(_(UI_TEXT_THERE_ARE_NO_SALES_PEOPLE_DEFINED_IN_THE_SYSTEM_AT_LEAST_ONE_SALES_PERSON_IS_REQUIRED_BEFORE_PROCEEDING));

check_db_has_sales_areas(_(UI_TEXT_THERE_ARE_NO_SALES_AREAS_DEFINED_IN_THE_SYSTEM_AT_LEAST_ONE_SALES_AREA_IS_REQUIRED_BEFORE_PROCEEDING));

check_db_has_shippers(_(UI_TEXT_THERE_ARE_NO_SHIPPING_COMPANIES_DEFINED_IN_THE_SYSTEM_AT_LEAST_ONE_SHIPPING_COMPANY_IS_REQUIRED_BEFORE_PROCEEDING));

check_db_has_tax_groups(_(UI_TEXT_THERE_ARE_NO_TAX_GROUPS_DEFINED_IN_THE_SYSTEM_AT_LEAST_ONE_TAX_GROUP_IS_REQUIRED_BEFORE_PROCEEDING));

simple_page_mode(true);
//-----------------------------------------------------------------------------------------------

if (isset($_GET['debtor_no']))
{
	$_POST['customer_id'] = strtoupper($_GET['debtor_no']);
}

$_POST['branch_code'] = $selected_id;

if (isset($_GET['SelectedBranch']))
{
	$br = get_branch($_GET['SelectedBranch']);
	$_POST['customer_id'] = $br['debtor_no'];
	$selected_id = $_POST['branch_code'] = $br['branch_code'];
	$Mode = 'Edit';
}
//-----------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['br_name']) == 0)
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THE_BRANCH_NAME_CANNOT_BE_EMPTY));
		set_focus('br_name');
	}

	if (strlen($_POST['br_ref']) == 0)
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_THE_BRANCH_SHORT_NAME_CANNOT_BE_EMPTY));
		set_focus('br_ref');
	}

	if ($input_error != 1)
	{

		begin_transaction();
    	if ($selected_id != -1)
		{
			update_branch($_POST['customer_id'], $_POST['branch_code'], $_POST['br_name'], $_POST['br_ref'],
				$_POST['br_address'], $_POST['salesman'], $_POST['area'], $_POST['tax_group_id'], $_POST['sales_account'],
				$_POST['sales_discount_account'], $_POST['receivables_account'], $_POST['payment_discount_account'],
				$_POST['default_location'], $_POST['br_post_address'], $_POST['group_no'],
				$_POST['default_ship_via'], $_POST['notes'], $_POST['bank_account']);

			$note =_('Selected customer branch has been updated');
  		}
		else
		{
			add_branch($_POST['customer_id'], $_POST['br_name'], $_POST['br_ref'],
				$_POST['br_address'], $_POST['salesman'], $_POST['area'], $_POST['tax_group_id'], $_POST['sales_account'],
				$_POST['sales_discount_account'], $_POST['receivables_account'], $_POST['payment_discount_account'],
				$_POST['default_location'], $_POST['br_post_address'], $_POST['group_no'],
				$_POST['default_ship_via'], $_POST['notes'], $_POST['bank_account']);
			$selected_id = db_insert_id();

			add_crm_person($_POST['contact_name'], $_POST['contact_name'], '', $_POST['br_post_address'], 
				$_POST['phone'], $_POST['phone2'], $_POST['fax'], $_POST['email'], 
				$_POST['rep_lang'], '');

			add_crm_contact('cust_branch', 'general', $selected_id, db_insert_id());


			$note = _('New customer branch has been added');
		}
		commit_transaction();
		display_notification($note);

		if (@$_REQUEST['popup']) {
			set_focus("Select".($_POST['branch_code'] == -1 ? $selected_id: $_POST['branch_code']));
		}
	}

}
elseif ($Mode == 'Delete')
{

	if (branch_in_foreign_table($_POST['customer_id'], $_POST['branch_code'], 'debtor_trans'))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THIS_BRANCH_BECAUSE_CUSTOMER_TRANSACTIONS_HAVE_BEEN_CREATED_TO_THIS_BRANCH));

	}
	else
	{
		if (branch_in_foreign_table($_POST['customer_id'], $_POST['branch_code'], 'sales_orders'))
		{
			UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THIS_BRANCH_BECAUSE_SALES_ORDERS_EXIST_FOR_IT_PURGE_OLD_SALES_ORDERS_FIRST));
		}
		else
		{
			delete_branch($_POST['customer_id'], $_POST['branch_code']);
			display_notification(_('Selected customer branch has been deleted'));
		}
	}
	$Mode = 'RESET';
}

if ($Mode == 'RESET' || RequestService::getPostStatic('_customer_id_update'))
{
	$selected_id = -1;
	$cust_id = $_POST['customer_id'];
	$inact = RequestService::getPostStatic('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $inact;
	$_POST['customer_id'] = $cust_id;
	$Ajax->activate('_page_body');
}

function branch_email($row) {
	return	'<a href = "mailto:'.$row["email"].'">'.$row["email"].'</a>';
}

function edit_link($row) {
	return button("Edit".$row["branch_code"],_(UI_TEXT_EDIT), '', ICON_EDIT);
}

function del_link($row) {
	return button("Delete".$row["branch_code"],_(UI_TEXT_DELETE), '', ICON_DELETE);
}

function select_link($row) {
	return button("Select".$row["branch_code"], $row["branch_code"], '', ICON_ADD, 'selector');
}

function branch_settings($selected_id, $num_branches) {
	global $Mode;

	start_outer_table(TABLESTYLE2);

	table_section(1);

	$_POST['email'] = "";
	if ($selected_id != -1)
	{
	 	if ($Mode == 'Edit' || !isset($_POST['br_name'])) {
			//editing an existing branch
			$myrow = get_cust_branch($_POST['customer_id'], $_POST['branch_code']);
			set_focus('br_name');
	    	$_POST['branch_code'] = $myrow["branch_code"];
		    $_POST['br_name']  = $myrow["br_name"];
		    $_POST['br_ref']  = $myrow["branch_ref"];
		    $_POST['br_address']  = $myrow["br_address"];
		    $_POST['br_post_address']  = $myrow["br_post_address"];
		    $_POST['salesman'] =$myrow["salesman"];
		    $_POST['area'] =$myrow["area"];
		    $_POST['tax_group_id'] = $myrow["tax_group_id"];
		    $_POST['default_location'] = $myrow["default_location"];
		    $_POST['default_ship_via'] = $myrow['default_ship_via'];
		    $_POST['sales_account'] = $myrow["sales_account"];
		    $_POST['sales_discount_account'] = $myrow['sales_discount_account'];
		    $_POST['receivables_account'] = $myrow['receivables_account'];
		    $_POST['payment_discount_account'] = $myrow['payment_discount_account'];
			$_POST['group_no']  = $myrow["group_no"];
			$_POST['notes']  = $myrow["notes"];
			$_POST['bank_account']  = $myrow["bank_account"];

		}
	}
	elseif ($Mode != 'ADD_ITEM')
	{
		$myrow = get_default_info_for_branch($_POST['customer_id']);
		if($myrow && !$num_branches) {
			$_POST['br_name'] = $myrow["name"];
			$_POST['br_ref'] = $myrow["debtor_ref"];
			$_POST['contact_name'] = _('Main Branch');
			$_POST['br_address'] = $_POST['br_post_address'] = $myrow["address"];
		}
		$_POST['branch_code'] = "";
		if (!isset($_POST['sales_account']) || !isset($_POST['sales_discount_account']))
		{
			$company_record = get_company_prefs();

			// We use the Item Sales Account as default!
		    // $_POST['sales_account'] = $company_record["default_sales_act"];
		    $_POST['sales_account'] = $_POST['notes']  = $_POST['bank_account'] = '';
		    $_POST['sales_discount_account'] = $company_record['default_sales_discount_act'];
		    $_POST['receivables_account'] = $company_record['debtors_act'];
		    $_POST['payment_discount_account'] = $company_record['default_prompt_payment_act'];
		}

	}
	hidden('popup', @$_REQUEST['popup']);

	table_section_title(_(UI_TEXT_NAME_AND_CONTACT));
	text_row(_(UI_TEXT_BRANCH_NAME_LABEL), 'br_name', null, 50, 60);
	text_row(_(UI_TEXT_BRANCH_SHORT_NAME_LABEL), 'br_ref', null, 30, 30);

	table_section_title(_(UI_TEXT_SALES));
	sales_persons_list_row( _(UI_TEXT_SALES_PERSON_LABEL), 'salesman', null);
	sales_areas_list_row( _(UI_TEXT_SALES_AREA_LABEL), 'area', null);
	sales_groups_list_row(_(UI_TEXT_SALES_GROUP_LABEL), 'group_no', null, true);
	locations_list_row(_(UI_TEXT_DEFAULT_INVENTORY_LOCATION_LABEL), 'default_location', null);
	shippers_list_row(_(UI_TEXT_DEFAULT_SHIPPING_COMPANY_LABEL), 'default_ship_via', null);
	tax_groups_list_row(_(UI_TEXT_TAX_GROUP_LABEL), 'tax_group_id', null);

	table_section_title(_(UI_TEXT_GL_ACCOUNTS));

	gl_all_accounts_list_row(_(UI_TEXT_SALES_ACCOUNT_LABEL), 'sales_account', null, false, false, true);
	gl_all_accounts_list_row(_(UI_TEXT_SALES_DISCOUNT_ACCOUNT_LABEL), 'sales_discount_account');
	gl_all_accounts_list_row(_(UI_TEXT_ACCOUNTS_RECEIVABLE_ACCOUNT_LABEL), 'receivables_account', null, true);
	gl_all_accounts_list_row(_(UI_TEXT_PROMPT_PAYMENT_DISCOUNT_ACCOUNT_LABEL), 'payment_discount_account');
	text_row(_(UI_TEXT_BANK_ACCOUNT_NUMBER_LABEL), 'bank_account', null, 30, 60);

	table_section(2);

	if($selected_id==-1) {
		table_section_title(_(UI_TEXT_GENERAL_CONTACT_DATA));
		text_row(_(UI_TEXT_CONTACT_PERSON_LABEL), 'contact_name', null, 35, 40);
		text_row(_(UI_TEXT_PHONE_NUMBER_LABEL), 'phone', null, 32, 30);
		text_row(_(UI_TEXT_SECONDARY_PHONE_NUMBER_LABEL), 'phone2', null, 32, 30);
		text_row(_(UI_TEXT_FAX_NUMBER_LABEL), 'fax', null, 32, 30);
		email_row(_(UI_TEXT_EMAIL_LABEL), 'email', null, 35, 55);
		languages_list_row( _(UI_TEXT_DOCUMENT_LANGUAGE_LABEL), 'rep_lang', null, _(UI_TEXT_CUSTOMER_DEFAULT));
	}
	table_section_title(_(UI_TEXT_ADDRESSES));
	textarea_row(_(UI_TEXT_MAILING_ADDRESS_LABEL), 'br_post_address', null, 35, 4);
	textarea_row(_(UI_TEXT_BILLING_ADDRESS_LABEL), 'br_address', null, 35, 4);
	textarea_row(_(UI_TEXT_GENERAL_NOTES_LABEL), 'notes', null, 35, 4);

	end_outer_table(1);
	submit_add_or_update_center($selected_id == -1, '', 'both');
}

start_form();

echo "<center>" . _(UI_TEXT_SELECT_A_CUSTOMER_LABEL) . "&nbsp;&nbsp;";
echo customer_list('customer_id', null, false, true);
echo "</center><br>";

$num_branches = db_customer_has_branches(RequestService::getPostStatic('customer_id'));

$sql = get_sql_for_customer_branches(RequestService::getPostStatic('customer_id'));

//------------------------------------------------------------------------------------------------
if ($num_branches)
{
$cols = array(
	'branch_code' => 'skip',
	_(UI_TEXT_SHORT_NAME),
	_(UI_TEXT_NAME),
	_(UI_TEXT_CONTACT),
	_(UI_TEXT_SALES_PERSON),
	_(UI_TEXT_AREA),
	_(UI_TEXT_PHONE_NO),
	_(UI_TEXT_FAX_NO),
	_(UI_TEXT_EMAIL) => 'email',
	_(UI_TEXT_TAX_GROUP),
	_(UI_TEXT_INACTIVE) => 'inactive',
		' '=> array('insert'=>true, 'fun'=>'select_link'),
		array('insert'=>true, 'fun'=>'edit_link'),
		array('insert'=>true, 'fun'=>'del_link')
	);

	if (!@$_REQUEST['popup']) {
		$cols[' '] = 'skip';
	}
$table =& new_db_pager('branch_tbl', $sql, $cols, 'cust_branch');
$table->set_inactive_ctrl('cust_branch', 'branch_code');

display_db_pager($table);
}
else
	display_note(_(UI_TEXT_THE_SELECTED_CUSTOMER_DOES_NOT_HAVE_ANY_BRANCHES_PLEASE_CREATE_AT_LEAST_ONE_BRANCH));

tabbed_content_start('tabs', array(
		_(UI_TEXT_GENERAL_SETTINGS) => array(_(UI_TEXT_GENERAL_SETTINGS), $selected_id!=-1),
		_(UI_TEXT_CONTACTS) => array(_(UI_TEXT_CONTACTS), $selected_id!=-1),
//		'orders' => array('S&ales orders', $selected_id!=-1) // not implemented
	));
	
	switch (RequestService::getPostStatic('_tabs_sel')) {
		default:
		case 'settings':
			branch_settings($selected_id, $num_branches); 
			break;
		case 'contacts':
			$contacts = new contacts('contacts', $selected_id, 'cust_branch');
			$contacts->show();
			break;
		case 'orders':
	};
	hidden('branch_code');
	hidden('selected_id', $selected_id);
br();
tabbed_content_end();


end_form();

end_page(@$_REQUEST['popup']);

