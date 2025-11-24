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
$path_to_root = "../..";

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
	
page(_($help_context = UI_TEXT_CUSTOMERS), @$_REQUEST['popup'], false, "", $js); 

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/CompanyPrefsService.php");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/ui/contacts_view.inc");
include_once($path_to_root . "/includes/ui/attachment.inc");
include_once($path_to_root . "/includes/CustomFields/CustomFieldsHelper.php");

if (isset($_GET['debtor_no'])) 
{
	$_POST['customer_id'] = $_GET['debtor_no'];
}

$selected_id = RequestService::getPostStatic('customer_id','');
//--------------------------------------------------------------------------------------------

function can_process()
{
	if (strlen($_POST['CustName']) == 0) 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_CUSTOMER_NAME_CANNOT_BE_EMPTY));
		set_focus('CustName');
		return false;
	} 

	if (strlen($_POST['cust_ref']) == 0) 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_CUSTOMER_SHORT_NAME_CANNOT_BE_EMPTY));
		set_focus('cust_ref');
		return false;
	} 
	
	if (!check_num('credit_limit', 0))
	{
		UiMessageService::displayError(_(UI_TEXT_THE_CREDIT_LIMIT_MUST_BE_NUMERIC_AND_NOT_LESS_THAN_ZERO));
		set_focus('credit_limit');
		return false;		
	} 
	
	if (!check_num('pymt_discount', 0, 100)) 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_PAYMENT_DISCOUNT_MUST_BE_NUMERIC_AND_IS_EXPECTED_TO_BE_LESS_THAN_100_PERCENT_AND_GREATER_THAN_OR_EQUAL_TO_0));
		set_focus('pymt_discount');
		return false;		
	} 
	
	if (!check_num('discount', 0, 100)) 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_DISCOUNT_PERCENTAGE_MUST_BE_NUMERIC_AND_IS_EXPECTED_TO_BE_LESS_THAN_100_PERCENT_AND_GREATER_THAN_OR_EQUAL_TO_0));
		set_focus('discount');
		return false;		
	} 

	return true;
}

//--------------------------------------------------------------------------------------------

function handle_submit(&$selected_id)
{
	global $path_to_root, $Ajax, $SysPrefs;

	if (!can_process())
		return;
		
	if ($selected_id) 
	{
		update_customer($_POST['customer_id'], $_POST['CustName'], $_POST['cust_ref'], $_POST['address'],
			$_POST['tax_id'], $_POST['curr_code'], $_POST['dimension_id'], $_POST['dimension2_id'],
			$_POST['credit_status'], $_POST['payment_terms'], RequestService::inputNumStatic('discount') / 100, RequestService::inputNumStatic('pymt_discount') / 100,
			RequestService::inputNumStatic('credit_limit'), $_POST['sales_type'], $_POST['notes']);

		update_record_status($_POST['customer_id'], $_POST['inactive'],
			'debtors_master', 'debtor_no');

		$Ajax->activate('customer_id'); // in case of status change
		display_notification(_(UI_TEXT_CUSTOMER_HAS_BEEN_UPDATED));

		// Save custom fields
		$customFieldsHelper = new \FA\CustomFields\CustomFieldsHelper();
		$customFieldsHelper->saveEntityFields('customers', $selected_id, $_POST);
	} 
	else 
	{ 	//it is a new customer

		begin_transaction();
		add_customer($_POST['CustName'], $_POST['cust_ref'], $_POST['address'],
			$_POST['tax_id'], $_POST['curr_code'], $_POST['dimension_id'], $_POST['dimension2_id'],
			$_POST['credit_status'], $_POST['payment_terms'], RequestService::inputNumStatic('discount') / 100, RequestService::inputNumStatic('pymt_discount') / 100,
			RequestService::inputNumStatic('credit_limit'), $_POST['sales_type'], $_POST['notes']);

		$selected_id = $_POST['customer_id'] = db_insert_id();
         
		if (isset($SysPrefs->auto_create_branch) && $SysPrefs->auto_create_branch == 1)
		{
        	add_branch($selected_id, $_POST['CustName'], $_POST['cust_ref'],
                $_POST['address'], $_POST['salesman'], $_POST['area'], $_POST['tax_group_id'], '',
                CompanyPrefsService::getCompanyPref('default_sales_discount_act'), CompanyPrefsService::getCompanyPref('debtors_act'), CompanyPrefsService::getCompanyPref('default_prompt_payment_act'),
                $_POST['location'], $_POST['address'], 0, $_POST['ship_via'], $_POST['notes'], $_POST['bank_account']);
                
        	$selected_branch = db_insert_id();
        
			add_crm_person($_POST['cust_ref'], $_POST['CustName'], '', $_POST['address'], 
				$_POST['phone'], $_POST['phone2'], $_POST['fax'], $_POST['email'], '', '');

			$pers_id = db_insert_id();
			add_crm_contact('cust_branch', 'general', $selected_branch, $pers_id);

			add_crm_contact('customer', 'general', $selected_id, $pers_id);
		}

		// Save custom fields for new customer
		$customFieldsHelper = new \FA\CustomFields\CustomFieldsHelper();
		$customFieldsHelper->saveEntityFields('customers', $selected_id, $_POST);

		commit_transaction();

		display_notification(_(UI_TEXT_A_NEW_CUSTOMER_HAS_BEEN_ADDED));

		if (isset($SysPrefs->auto_create_branch) && $SysPrefs->auto_create_branch == 1)
			display_notification(_(UI_TEXT_A_DEFAULT_BRANCH_HAS_BEEN_AUTOMATICALLY_CREATED_PLEASE_CHECK_DEFAULT_BRANCH_VALUES_BY_USING_LINK_BELOW));
		
		$Ajax->activate('_page_body');
	}
}
//--------------------------------------------------------------------------------------------

if (isset($_POST['submit'])) 
{
	handle_submit($selected_id);
}
//-------------------------------------------------------------------------------------------- 

if (isset($_POST['delete'])) 
{

	$cancel_delete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'debtor_trans'

	if (key_in_foreign_table($selected_id, 'debtor_trans', 'debtor_no'))
	{
		$cancel_delete = 1;
		UiMessageService::displayError(_(UI_TEXT_THIS_CUSTOMER_CANNOT_BE_DELETED_BECAUSE_THERE_ARE_TRANSACTIONS_THAT_REFER_TO_IT));
	} 
	else 
	{
		if (key_in_foreign_table($selected_id, 'sales_orders', 'debtor_no'))
		{
			$cancel_delete = 1;
			UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THE_CUSTOMER_RECORD_BECAUSE_ORDERS_HAVE_BEEN_CREATED_AGAINST_IT));
		} 
		else 
		{
			if (key_in_foreign_table($selected_id, 'cust_branch', 'debtor_no'))
			{
				$cancel_delete = 1;
				UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THIS_CUSTOMER_BECAUSE_THERE_ARE_BRANCH_RECORDS_SET_UP_AGAINST_IT));
				//echo "<br> There are " . $myrow[0] . " branch records relating to this customer";
			}
		}
	}
	
	if ($cancel_delete == 0) 
	{ 	//ie not cancelled the delete as a result of above tests
	
		delete_customer($selected_id);

		// Delete custom fields
		$customFieldsHelper = new \FA\CustomFields\CustomFieldsHelper();
		$customFieldsHelper->deleteEntityFields('customers', $selected_id);

		display_notification(_(UI_TEXT_SELECTED_CUSTOMER_HAS_BEEN_DELETED));
		unset($_POST['customer_id']);
		$selected_id = '';
		$Ajax->activate('_page_body');
	} //end if Delete Customer
}

function customer_settings($selected_id) 
{
	global $SysPrefs, $path_to_root, $page_nested;
	
	if (!$selected_id) 
	{
	 	if (list_updated('customer_id') || !isset($_POST['CustName'])) {
			$_POST['CustName'] = $_POST['cust_ref'] = $_POST['address'] = $_POST['tax_id']  = '';
			$_POST['dimension_id'] = 0;
			$_POST['dimension2_id'] = 0;
			$_POST['sales_type'] = -1;
			$_POST['curr_code']  = BankingService::getCompanyCurrency();
			$_POST['credit_status']  = -1;
			$_POST['payment_terms']  = $_POST['notes']  = '';

			$_POST['discount']  = $_POST['pymt_discount'] = percent_format(0);
			$_POST['credit_limit']	= FormatService::priceFormat($SysPrefs->default_credit_limit());
		}
	}
	else 
	{
		$myrow = get_customer($selected_id);

		$_POST['CustName'] = $myrow["name"];
		$_POST['cust_ref'] = $myrow["debtor_ref"];
		$_POST['address']  = $myrow["address"];
		$_POST['tax_id']  = $myrow["tax_id"];
		$_POST['dimension_id']  = $myrow["dimension_id"];
		$_POST['dimension2_id']  = $myrow["dimension2_id"];
		$_POST['sales_type'] = $myrow["sales_type"];
		$_POST['curr_code']  = $myrow["curr_code"];
		$_POST['credit_status']  = $myrow["credit_status"];
		$_POST['payment_terms']  = $myrow["payment_terms"];
		$_POST['discount']  = percent_format($myrow["discount"] * 100);
		$_POST['pymt_discount']  = percent_format($myrow["pymt_discount"] * 100);
		$_POST['credit_limit']	= FormatService::priceFormat($myrow["credit_limit"]);
		$_POST['notes']  = $myrow["notes"];
		$_POST['inactive'] = $myrow["inactive"];
	}

	start_outer_table(TABLESTYLE2);
	table_section(1);
	table_section_title(_(UI_TEXT_NAME_AND_ADDRESS));

	text_row(_(UI_TEXT_CUSTOMER_NAME_LABEL), 'CustName', $_POST['CustName'], 40, 80);
	text_row(_(UI_TEXT_CUSTOMER_SHORT_NAME_LABEL), 'cust_ref', null, 30, 30);
	textarea_row(_(UI_TEXT_ADDRESS_LABEL), 'address', $_POST['address'], 35, 5);

	text_row(_(UI_TEXT_GSTNO_LABEL), 'tax_id', null, 40, 40);


	if (!$selected_id || is_new_customer($selected_id) || (!key_in_foreign_table($selected_id, 'debtor_trans', 'debtor_no') &&
		!key_in_foreign_table($selected_id, 'sales_orders', 'debtor_no'))) 
	{
		currencies_list_row(_(UI_TEXT_CUSTOMERS_CURRENCY_LABEL), 'curr_code', $_POST['curr_code']);
	} 
	else 
	{
		label_row(_(UI_TEXT_CUSTOMERS_CURRENCY_LABEL), $_POST['curr_code']);
		hidden('curr_code', $_POST['curr_code']);				
	}
	sales_types_list_row(_(UI_TEXT_SALES_TYPE_PRICE_LIST_LABEL), 'sales_type', $_POST['sales_type']);

	if($selected_id)
		record_status_list_row(_(UI_TEXT_CUSTOMER_STATUS_LABEL), 'inactive');
	elseif (isset($SysPrefs->auto_create_branch) && $SysPrefs->auto_create_branch == 1)
	{
		table_section_title(_(UI_TEXT_BRANCH));
		text_row(_(UI_TEXT_PHONE_LABEL), 'phone', null, 32, 30);
		text_row(_(UI_TEXT_SECONDARY_PHONE_NUMBER_LABEL), 'phone2', null, 32, 30);
		text_row(_(UI_TEXT_FAX_NUMBER_LABEL), 'fax', null, 32, 30);
		email_row(_(UI_TEXT_E_MAIL_LABEL), 'email', null, 35, 55);
		text_row(_(UI_TEXT_BANK_ACCOUNT_NUMBER_LABEL), 'bank_account', null, 30, 60);
		sales_persons_list_row( _(UI_TEXT_SALES_PERSON_LABEL), 'salesman', null);
	}
	table_section(2);

	table_section_title(_(UI_TEXT_SALES));

	percent_row(_(UI_TEXT_DISCOUNT_PERCENT_LABEL), 'discount', $_POST['discount']);
	percent_row(_(UI_TEXT_PROMPT_PAYMENT_DISCOUNT_PERCENT_LABEL), 'pymt_discount', $_POST['pymt_discount']);
	amount_row(_(UI_TEXT_CREDIT_LIMIT_LABEL), 'credit_limit', $_POST['credit_limit']);

	payment_terms_list_row(_(UI_TEXT_PAYMENT_TERMS_LABEL), 'payment_terms', $_POST['payment_terms']);
	credit_status_list_row(_(UI_TEXT_CREDIT_STATUS_LABEL), 'credit_status', $_POST['credit_status']); 
	$dim = \FA\Services\CompanyPrefsService::getUseDimensions();
	if ($dim >= 1)
		dimensions_list_row(_(UI_TEXT_DIMENSION)." 1:", 'dimension_id', $_POST['dimension_id'], true, " ", false, 1);
	if ($dim > 1)
		dimensions_list_row(_(UI_TEXT_DIMENSION)." 2:", 'dimension2_id', $_POST['dimension2_id'], true, " ", false, 2);
	if ($dim < 1)
		hidden('dimension_id', 0);
	if ($dim < 2)
		hidden('dimension2_id', 0);

	if ($selected_id)  {
		start_row();
		echo '<td class="label">'._('Customer branches').':</td>';
	  	hyperlink_params_td($path_to_root . "/sales/manage/customer_branches.php",
			'<b>'. ($page_nested ?  _(UI_TEXT_SELECT_OR_ADD) : _(UI_TEXT_ADD_OR_EDIT)).'</b>', 
			"debtor_no=".$selected_id.($page_nested ? '&popup=1':''));
		end_row();
	}

	textarea_row(_(UI_TEXT_GENERAL_NOTES_LABEL), 'notes', null, 35, 5);
	if (!$selected_id && isset($SysPrefs->auto_create_branch) && $SysPrefs->auto_create_branch == 1)
	{
		table_section_title(_(UI_TEXT_BRANCH));
		locations_list_row(_(UI_TEXT_DEFAULT_INVENTORY_LOCATION_LABEL), 'location');
		shippers_list_row(_(UI_TEXT_DEFAULT_SHIPPING_COMPANY_LABEL), 'ship_via');
		sales_areas_list_row( _(UI_TEXT_SALES_AREA_LABEL), 'area', null);
		tax_groups_list_row(_(UI_TEXT_TAX_GROUP_LABEL), 'tax_group_id', null);
	}

	// Custom Fields Section
	$customFieldsHelper = new \FA\CustomFields\CustomFieldsHelper();
	$customFieldsHtml = $customFieldsHelper->renderEntityFields('customers', $selected_id);
	if (!empty($customFieldsHtml)) {
		table_section_title(_('Custom Fields'));
		echo '<tr><td colspan="2">' . $customFieldsHtml . '</td></tr>';
	}

	end_outer_table(1);

	div_start('controls');
	if (@$_REQUEST['popup']) hidden('popup', 1);
	if (!$selected_id)
	{
		submit_center('submit', _(UI_TEXT_ADD_NEW_CUSTOMER), true, '', false);
	} 
	else 
	{
		submit_center_first('submit', _(UI_TEXT_UPDATE_CUSTOMER), 
		  _('Update customer data'), $page_nested ? true : false);
		submit_return('select', $selected_id, _(UI_TEXT_SELECT_THIS_CUSTOMER_AND_RETURN_TO_DOCUMENT_ENTRY));
		submit_center_last('delete', _(UI_TEXT_DELETE_CUSTOMER), 
		  _('Delete customer data if have been never used'), true);
	}
	div_end();
}

//--------------------------------------------------------------------------------------------

check_db_has_sales_types(_(UI_TEXT_THERE_ARE_NO_SALES_TYPES_DEFINED_PLEASE_DEFINE_AT_LEAST_ONE_SALES_TYPE_BEFORE_ADDING_A_CUSTOMER));
 
start_form(true);

if (db_has_customers()) 
{
	start_table(TABLESTYLE_NOBORDER);
	start_row();
	customer_list_cells(_(UI_TEXT_SELECT_A_CUSTOMER_LABEL), 'customer_id', null,
		_('New customer'), true, RequestService::checkValueStatic('show_inactive'));
	check_cells(_(UI_TEXT_SHOW_INACTIVE_LABEL), 'show_inactive', null, true);
	end_row();
	end_table();

	if (RequestService::getPostStatic('_show_inactive_update')) {
		$Ajax->activate('customer_id');
		set_focus('customer_id');
	}
} 
else 
{
	hidden('customer_id');
}

//if (!$selected_id || list_updated('customer_id'))
if (!$selected_id)
	unset($_POST['_tabs_sel']); // force settings tab for new customer

tabbed_content_start('tabs', array(
		'settings' => array(_('&General settings'), $selected_id),
		'contacts' => array(_('&Contacts'), $selected_id),
		'transactions' => array(_('&Transactions'), (user_check_access('SA_SALESTRANSVIEW') ? $selected_id : null)),
		'orders' => array(_('Sales &Orders'), (user_check_access('SA_SALESTRANSVIEW') ? $selected_id : null)),
		'attachments' => array(_('Attachments'), (user_check_access('SA_ATTACHDOCUMENT') ? $selected_id : null)),
	));
	
	switch (RequestService::getPostStatic('_tabs_sel')) {
		default:
		case 'settings':
			customer_settings($selected_id); 
			break;
		case 'contacts':
			$contacts = new contacts('contacts', $selected_id, 'customer');
			$contacts->show();
			break;
		case 'transactions':
			$_GET['customer_id'] = $selected_id;
			include_once($path_to_root."/sales/inquiry/customer_inquiry.php");
			break;
		case 'orders':
			$_GET['customer_id'] = $selected_id;
			include_once($path_to_root."/sales/inquiry/sales_orders_view.php");
			break;
		case 'attachments':
			$_GET['trans_no'] = $selected_id;
			$_GET['type_no']= ST_CUSTOMER;
			$attachments = new attachments('attachment', $selected_id, 'customers');
			$attachments->show();
	};
br();
tabbed_content_end();

end_form();
end_page(@$_REQUEST['popup']);

