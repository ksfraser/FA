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
$page_security = 'SA_SUPPLIER';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui_strings.php");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(_($help_context = "Suppliers"), @$_REQUEST['popup'], false, "", $js);

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/contacts_view.inc");
include_once($path_to_root . "/includes/ui/attachment.inc");
include_once($path_to_root . "/includes/CompanyPrefsService.php");

check_db_has_tax_groups(_(UI_TEXT_THERE_ARE_NO_TAX_GROUPS_DEFINED_IN_THE_SYSTEM_AT_LEAST_ONE_TAX_GROUP_IS_REQUIRED_BEFORE_PROCEEDING));

if (isset($_GET['supplier_id'])) 
{
	$_POST['supplier_id'] = $_GET['supplier_id'];
}

$supplier_id = RequestService::getPostStatic('supplier_id', ''); 

function can_process()
{
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['supp_name']) == 0 || $_POST['supp_name'] == "") 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_SUPPLIER_NAME_MUST_BE_ENTERED));
		set_focus('supp_name');
		return false;
	}

	if (strlen($_POST['supp_ref']) == 0 || $_POST['supp_ref'] == "") 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_SUPPLIER_SHORT_NAME_MUST_BE_ENTERED));
		set_focus('supp_ref');
		return false;
	}
	return true;
}

function handle_submit(&$supplier_id)
{
	global $path_to_root, $Ajax, $SysPrefs;
	
	if (!can_process())
		return;
	begin_transaction();
	if ($supplier_id) 
	{
		update_supplier($_POST['supplier_id'], $_POST['supp_name'], $_POST['supp_ref'], $_POST['address'],
			$_POST['supp_address'], $_POST['gst_no'],
			$_POST['website'], $_POST['supp_account_no'], $_POST['bank_account'], 
			RequestService::inputNumStatic('credit_limit', 0), $_POST['dimension_id'], $_POST['dimension2_id'], $_POST['curr_code'],
			$_POST['payment_terms'], $_POST['payable_account'], $_POST['purchase_account'], $_POST['payment_discount_account'],
			$_POST['notes'], $_POST['tax_group_id'], RequestService::checkValueStatic('tax_included'));
		update_record_status($_POST['supplier_id'], $_POST['inactive'],
			'suppliers', 'supplier_id');

		$Ajax->activate('supplier_id'); // in case of status change
		display_notification(_(UI_TEXT_SUPPLIER_HAS_BEEN_UPDATED));
	} 
	else 
	{
		add_supplier($_POST['supp_name'], $_POST['supp_ref'], $_POST['address'], $_POST['supp_address'],
			$_POST['gst_no'], $_POST['website'], $_POST['supp_account_no'], $_POST['bank_account'], 
			RequestService::inputNumStatic('credit_limit',0), $_POST['dimension_id'], $_POST['dimension2_id'],
			$_POST['curr_code'], $_POST['payment_terms'], $_POST['payable_account'], $_POST['purchase_account'],
			$_POST['payment_discount_account'], $_POST['notes'], $_POST['tax_group_id'], RequestService::checkValueStatic('tax_included'));

		$supplier_id = $_POST['supplier_id'] = db_insert_id();

		add_crm_person($_POST['supp_ref'], $_POST['contact'], '', $_POST['address'], 
			$_POST['phone'], $_POST['phone2'], $_POST['fax'], $_POST['email'], 
			$_POST['rep_lang'], '');

		add_crm_contact('supplier', 'general', $supplier_id, db_insert_id());

		display_notification(_(UI_TEXT_A_NEW_SUPPLIER_HAS_BEEN_ADDED));
		$Ajax->activate('_page_body');
	}
	commit_transaction();
}

if (isset($_POST['submit'])) 
{
	handle_submit($supplier_id);
}

if (isset($_POST['delete']) && $_POST['delete'] != "") 
{
	//the link to delete a selected record was clicked instead of the submit button

	$cancel_delete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'supp_trans' , purch_orders

	if (key_in_foreign_table($_POST['supplier_id'], 'supp_trans', 'supplier_id'))
	{
		$cancel_delete = 1;
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THIS_SUPPLIER_BECAUSE_THERE_ARE_TRANSACTIONS_THAT_REFER_TO_THIS_SUPPLIER));

	} 
	else 
	{
		if (key_in_foreign_table($_POST['supplier_id'], 'purch_orders', 'supplier_id'))
		{
			$cancel_delete = 1;
			UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THE_SUPPLIER_RECORD_BECAUSE_PURCHASE_ORDERS_HAVE_BEEN_CREATED_AGAINST_THIS_SUPPLIER));
		}

	}
	if ($cancel_delete == 0) 
	{
		delete_supplier($_POST['supplier_id']);

		unset($_SESSION['supplier_id']);
		$supplier_id = '';
		$Ajax->activate('_page_body');
		display_notification("#" . $_POST['supplier_id'] . " " . _(UI_TEXT_SUPPLIER_HAS_BEEN_DELETED));
	} //end if Delete supplier
}

//--------------------------------------------------------------------------------------------
function supplier_settings(&$supplier_id)
{
	global $page_nested;
	
	start_outer_table(TABLESTYLE2);

	table_section(1);

	if ($supplier_id) 
	{
		//SupplierID exists - either passed when calling the form or from the form itself
		$myrow = get_supplier($_POST['supplier_id']);

		$_POST['supp_name'] = $myrow["supp_name"];
		$_POST['supp_ref'] = $myrow["supp_ref"];
		$_POST['address']  = $myrow["address"];
		$_POST['supp_address']  = $myrow["supp_address"];

		$_POST['gst_no']  = $myrow["gst_no"];
		$_POST['website']  = $myrow["website"];
		$_POST['supp_account_no']  = $myrow["supp_account_no"];
		$_POST['bank_account']  = $myrow["bank_account"];
		$_POST['dimension_id']  = $myrow["dimension_id"];
		$_POST['dimension2_id']  = $myrow["dimension2_id"];
		$_POST['curr_code']  = $myrow["curr_code"];
		$_POST['payment_terms']  = $myrow["payment_terms"];
		$_POST['credit_limit']  = FormatService::priceFormat($myrow["credit_limit"]);
		$_POST['tax_group_id'] = $myrow["tax_group_id"];
		$_POST['tax_included'] = $myrow["tax_included"];
		$_POST['payable_account']  = $myrow["payable_account"];
		$_POST['purchase_account']  = $myrow["purchase_account"];
		$_POST['payment_discount_account'] = $myrow["payment_discount_account"];
		$_POST['notes']  = $myrow["notes"];
	 	$_POST['inactive'] = $myrow["inactive"];
	} 
	else 
	{
		if (list_updated('supplier_id') || !isset($_POST['supp_name'])) {
			$_POST['supp_name'] = $_POST['supp_ref'] = $_POST['address'] = $_POST['supp_address'] = 
				$_POST['tax_group_id'] = $_POST['website'] = $_POST['supp_account_no'] = $_POST['notes'] = '';
			$_POST['dimension_id'] = 0;
			$_POST['dimension2_id'] = 0;
			$_POST['tax_included'] = 0;
			$_POST['sales_type'] = -1;
			$_POST['gst_no'] = $_POST['bank_account'] = '';
			$_POST['payment_terms']  = '';
			$_POST['credit_limit'] = FormatService::priceFormat(0);

			$_POST['curr_code']  = \FA\Services\CompanyPrefsService::getCompanyPref('curr_default');
			$_POST['payable_account'] = \FA\Services\CompanyPrefsService::getCompanyPref('creditors_act');
			$_POST['purchase_account'] = ''; // default/item's cogs account
			$_POST['payment_discount_account'] = \FA\Services\CompanyPrefsService::getCompanyPref('pyt_discount_act');
		}
	}

	table_section_title(_(UI_TEXT_BASIC_DATA));

	text_row(_(UI_TEXT_SUPPLIER_NAME_LABEL), 'supp_name', null, 42, 60);
	text_row(_(UI_TEXT_SUPPLIER_SHORT_NAME_LABEL), 'supp_ref', null, 30, 30);

	text_row(_(UI_TEXT_GSTNO_LABEL), 'gst_no', null, 42, 40);
	link_row(_(UI_TEXT_WEBSITE_LABEL), 'website', null, 35, 55);
	if ($supplier_id && !is_new_supplier($supplier_id) && (key_in_foreign_table($_POST['supplier_id'], 'supp_trans', 'supplier_id') ||
		key_in_foreign_table($_POST['supplier_id'], 'purch_orders', 'supplier_id'))) 
	{
		label_row(_(UI_TEXT_SUPPLIERS_CURRENCY_LABEL), $_POST['curr_code']);
		hidden('curr_code', $_POST['curr_code']);
	} 
	else 
	{
		currencies_list_row(_(UI_TEXT_SUPPLIERS_CURRENCY_LABEL), 'curr_code', null);
	}
	tax_groups_list_row(_(UI_TEXT_TAX_GROUP_LABEL), 'tax_group_id', null);
	text_row(_(UI_TEXT_OUR_CUSTOMER_NO_LABEL), 'supp_account_no', null, 42, 40);

	table_section_title(_(UI_TEXT_PURCHASING));
	text_row(_(UI_TEXT_BANK_NAME_ACCOUNT_LABEL), 'bank_account', null, 42, 40);
	amount_row(_(UI_TEXT_CREDIT_LIMIT_LABEL), 'credit_limit', null);
	payment_terms_list_row(_(UI_TEXT_PAYMENT_TERMS_LABEL), 'payment_terms', null);
	//
	// tax_included option from supplier record is used directly in update_average_cost() function,
	// therefore we can't edit the option after any transaction was done for the supplier.
	//
	if (is_new_supplier($supplier_id))
		check_row(_("Prices contain tax included:"), 'tax_included');
	else {
		hidden('tax_included');
		label_row(_("Prices contain tax included:"), $_POST['tax_included'] ? _('Yes') : _('No'));
	}

	if (!$supplier_id) table_section(2);

	table_section_title(_("Accounts"));
	gl_all_accounts_list_row(_("Accounts Payable Account:"), 'payable_account', $_POST['payable_account']);
	gl_all_accounts_list_row(_("Purchase Account:"), 'purchase_account', $_POST['purchase_account'],
		false, false, _("Use Item Inventory/COGS Account"));
	gl_all_accounts_list_row(_("Purchase Discount Account:"), 'payment_discount_account', $_POST['payment_discount_account']);
	if (!$supplier_id) {
		table_section_title(_("Contact Data"));
		text_row(_("Contact Person:"), 'contact', null, 42, 40);
		text_row(_("Phone Number:"), 'phone', null, 32, 30);
		text_row(_("Secondary Phone Number:"), 'phone2', null, 32, 30);
		table_section_title(_("Contact Data"));
		text_row(_("Fax Number:"), 'fax', null, 32, 30);
		email_row(_("E-mail:"), 'email', null, 35, 55);
		languages_list_row(_("Document Language:"), 'rep_lang', null, _('System default'));
	}
	else
		table_section(2);
	$dim = \FA\Services\CompanyPrefsService::getUseDimensions();
	if ($dim >= 1)
	{
		table_section_title(_("Dimension"));
		dimensions_list_row(_("Dimension")." 1:", 'dimension_id', null, true, " ", false, 1);
		if ($dim > 1)
			dimensions_list_row(_("Dimension")." 2:", 'dimension2_id', null, true, " ", false, 2);
	}
	if ($dim < 1)
		hidden('dimension_id', 0);
	if ($dim < 2)
		hidden('dimension2_id', 0);
	if (!$supplier_id)	
		table_section(2);

	table_section_title(_("Addresses"));
	textarea_row(_("Mailing Address:"), 'address', null, 35, 5);
	textarea_row(_("Physical Address:"), 'supp_address', null, 35, 5);

	table_section_title(_("General"));
	textarea_row(_("General Notes:"), 'notes', null, 35, 5);
	if ($supplier_id)
		record_status_list_row(_("Supplier status:"), 'inactive');
	end_outer_table(1);

	div_start('controls');
	if (@$_REQUEST['popup']) hidden('popup', 1);
	if ($supplier_id) 
	{
		submit_center_first('submit', _("Update Supplier"), 
		  _('Update supplier data'), $page_nested ? true : false);
		submit_return('select', RequestService::getPostStatic('supplier_id'), _("Select this supplier and return to document entry."));
		submit_center_last('delete', _("Delete Supplier"), 
		  _('Delete supplier data if have been never used'), true);
	}
	else 
	{
		submit_center('submit', _("Add New Supplier Details"), true, '', false);
	}
	div_end();
}

start_form(true);

if (db_has_suppliers()) 
{
	start_table(false, "", 3);
	start_row();
	supplier_list_cells(_("Select a supplier: "), 'supplier_id', null,
		  _('New supplier'), true, RequestService::checkValueStatic('show_inactive'));
	check_cells(_("Show inactive:"), 'show_inactive', null, true);
	end_row();
	end_table();
	if (RequestService::getPostStatic('_show_inactive_update')) {
		$Ajax->activate('supplier_id');
		set_focus('supplier_id');
	}
} 
else 
{
	hidden('supplier_id', RequestService::getPostStatic('supplier_id'));
}

if (!$supplier_id)
	unset($_POST['_tabs_sel']); // force settings tab for new customer

tabbed_content_start('tabs', array(
		'settings' => array(_('&General settings'), $supplier_id),
		'contacts' => array(_('&Contacts'), $supplier_id),
		'transactions' => array(_('&Transactions'), (user_check_access('SA_SUPPTRANSVIEW') ? $supplier_id : null)),
		'orders' => array(_('Purchase &Orders'), (user_check_access('SA_SUPPTRANSVIEW') ? $supplier_id : null)),
		'attachments' => array(_('Attachments'), (user_check_access('SA_ATTACHDOCUMENT') ? $supplier_id : null)),
	));
	
	switch (RequestService::getPostStatic('_tabs_sel')) {
		default:
		case 'settings':
			supplier_settings($supplier_id); 
			break;
		case 'contacts':
			$contacts = new contacts('contacts', $supplier_id, 'supplier');
			$contacts->show();
			break;
		case 'transactions':
			$_GET['supplier_id'] = $supplier_id;
			include_once($path_to_root."/purchasing/inquiry/supplier_inquiry.php");
			break;
		case 'orders':
			$_GET['supplier_id'] = $supplier_id;
			include_once($path_to_root."/purchasing/inquiry/po_search_completed.php");
			break;
		case 'attachments':
			$_GET['trans_no'] = $supplier_id;
			$_GET['type_no']= ST_SUPPLIER;
			$attachments = new attachments('attachment', $supplier_id, 'suppliers');
			$attachments->show();
	};
br();
tabbed_content_end();
end_form();
end_page(@$_REQUEST['popup']);

