<?php
/**
 * Enhanced Customer Management with WebERP-Style CRM Features
 *
 * Extends the standard FA customer management with advanced CRM capabilities:
 * - Customer types and segmentation
 * - Enhanced contact management
 * - Geographic mapping
 * - EDI configuration
 * - Customer analytics
 * - Sales opportunities
 */

$page_security = 'SA_CUSTOMER';
$path_to_root = "../..";

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

// Add CRM-specific JavaScript
$js .= "
function showCustomerAnalytics(customerId) {
    $('#customer_analytics_' + customerId).toggle();
}

function showCustomerContacts(customerId) {
    $('#customer_contacts_' + customerId).toggle();
}

function showSalesOpportunities(customerId) {
    $('#sales_opportunities_' + customerId).toggle();
}
";

page(_($help_context = "Enhanced Customer Management"), @$_REQUEST['popup'], false, "", $js);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/CompanyPrefsService.php");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/ui/contacts_view.inc");
include_once($path_to_root . "/includes/ui/attachment.inc");
include_once($path_to_root . "/includes/CustomFields/CustomFieldsHelper.php");

// Include CRM database functions
include_once($path_to_root . "/modules/CRM/includes/crm_db.inc");

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
		UiMessageService::displayError(_("The customer name cannot be empty"));
		set_focus('CustName');
		return false;
	}

	if (strlen($_POST['cust_ref']) == 0)
	{
		UiMessageService::displayError(_("The customer short name cannot be empty"));
		set_focus('cust_ref');
		return false;
	}

	if (!check_num('credit_limit', 0))
	{
		UiMessageService::displayError(_("The credit limit must be numeric and not less than zero"));
		set_focus('credit_limit');
		return false;
	}

	if (!check_num('pymt_discount', 0, 100))
	{
		UiMessageService::displayError(_("The payment discount must be numeric and is expected to be less than 100% and greater than or equal to 0"));
		set_focus('pymt_discount');
		return false;
	}

	if (!check_num('discount', 0, 100))
	{
		UiMessageService::displayError(_("The discount percentage must be numeric and is expected to be less than 100% and greater than or equal to 0"));
		set_focus('discount');
		return false;
	}

	// CRM-specific validations
	if (!empty($_POST['website']) && !filter_var($_POST['website'], FILTER_VALIDATE_URL)) {
		UiMessageService::displayError(_("Please enter a valid website URL"));
		set_focus('website');
		return false;
	}

	if (!empty($_POST['latitude']) && (!is_numeric($_POST['latitude']) || $_POST['latitude'] < -90 || $_POST['latitude'] > 90)) {
		UiMessageService::displayError(_("Latitude must be a number between -90 and 90"));
		set_focus('latitude');
		return false;
	}

	if (!empty($_POST['longitude']) && (!is_numeric($_POST['longitude']) || $_POST['longitude'] < -180 || $_POST['longitude'] > 180)) {
		UiMessageService::displayError(_("Longitude must be a number between -180 and 180"));
		set_focus('longitude');
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
		// Update standard customer data
		update_customer($_POST['customer_id'], $_POST['CustName'], $_POST['cust_ref'], $_POST['address'],
			$_POST['tax_id'], $_POST['curr_code'], $_POST['dimension_id'], $_POST['dimension2_id'],
			$_POST['credit_status'], $_POST['payment_terms'], RequestService::inputNumStatic('discount') / 100,
			RequestService::inputNumStatic('pymt_discount') / 100, RequestService::inputNumStatic('credit_limit'),
			$_POST['sales_type'], $_POST['notes']);

		// Update CRM-specific data
		update_customer_crm_data($_POST['customer_id'], [
			'customer_type_id' => $_POST['customer_type_id'],
			'territory_id' => $_POST['territory_id'],
			'customer_since' => $_POST['customer_since'] ? date2sql($_POST['customer_since']) : null,
			'website' => $_POST['website'],
			'industry' => $_POST['industry'],
			'employee_count' => $_POST['employee_count'] ? (int)$_POST['employee_count'] : null,
			'annual_revenue' => $_POST['annual_revenue'] ? RequestService::inputNumStatic('annual_revenue') : null,
			'account_manager' => $_POST['account_manager'],
			'credit_rating' => $_POST['credit_rating']
		]);

		update_record_status($_POST['customer_id'], $_POST['inactive'],
			'debtors_master', 'debtor_no');

		$Ajax->activate('customer_id');
		display_notification(_("Customer has been updated"));
	}
	else
	{
		// Add new customer
		$selected_id = add_customer($_POST['CustName'], $_POST['cust_ref'], $_POST['address'],
			$_POST['tax_id'], $_POST['curr_code'], $_POST['dimension_id'], $_POST['dimension2_id'],
			$_POST['credit_status'], $_POST['payment_terms'], RequestService::inputNumStatic('discount') / 100,
			RequestService::inputNumStatic('pymt_discount') / 100, RequestService::inputNumStatic('credit_limit'),
			$_POST['sales_type'], $_POST['notes']);

		if ($selected_id)
		{
			// Add CRM-specific data for new customer
			update_customer_crm_data($selected_id, [
				'customer_type_id' => $_POST['customer_type_id'],
				'customer_segment_id' => $_POST['customer_segment_id'],
				'territory_id' => $_POST['territory_id'],
				'customer_since' => $_POST['customer_since'] ? date2sql($_POST['customer_since']) : date('Y-m-d'),
				'website' => $_POST['website'],
				'industry' => $_POST['industry'],
				'employee_count' => $_POST['employee_count'] ? (int)$_POST['employee_count'] : null,
				'annual_revenue' => $_POST['annual_revenue'] ? RequestService::inputNumStatic('annual_revenue') : null,
				'parent_company' => $_POST['parent_company'],
				'latitude' => $_POST['latitude'] ? (float)$_POST['latitude'] : null,
				'longitude' => $_POST['longitude'] ? (float)$_POST['longitude'] : null,
				'edi_enabled' => isset($_POST['edi_enabled']) ? 1 : 0,
				'marketing_opt_out' => isset($_POST['marketing_opt_out']) ? 1 : 0,
				'preferred_contact_method' => $_POST['preferred_contact_method'],
				'account_manager' => $_POST['account_manager'],
				'credit_rating' => $_POST['credit_rating'],
				'payment_reliability' => 100.00
			]);

			display_notification(_("A new customer has been added"));
			$Ajax->activate('_page_body');
		}
	}

	if (isset($_POST['add_contact']) && $_POST['add_contact'])
	{
		$Ajax->activate('contacts');
	}

	$Ajax->activate('customer_id');
}

//--------------------------------------------------------------------------------------------

if (isset($_POST['submit']))
{
	handle_submit($selected_id);
}

if (isset($_POST['delete']))
{
	if (!can_delete_customer($selected_id))
	{
		display_error(_("Cannot delete the customer record because transactions exist against it"));
	}
	else
	{
		delete_customer($selected_id);
		display_notification(_("Customer has been deleted"));
		$Ajax->activate('_page_body');
		$selected_id = '';
	}
}

if (isset($_POST['add_contact']))
{
	$Ajax->activate('contacts');
}

//--------------------------------------------------------------------------------------------

start_form();

if (db_has_customers())
{
	start_table(TABLESTYLE_NOBORDER);
	start_row();

	customer_list_cells(_("Select a customer: "), 'customer_id', $selected_id,
		_('New Customer'), true, check_value('show_inactive'));

	check_cells(_("Show inactive:"), 'show_inactive', null, true);
	end_row();
	end_table();

	if (get_post('show_inactive') == 1)
		$show_inactive = 1;
	else
		$show_inactive = 0;
}
else
{
	hidden('customer_id', $selected_id);
}

echo '<br>';

div_start('customer_details');

if (!$selected_id)
{
	table_section_title(_("Basic Information"));
}
else
{
	table_section_title(_("Basic Information"));

	// Display customer analytics summary
	$analytics = get_customer_analytics($selected_id);
	if ($analytics) {
		echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">';
		echo '<h4>' . _("Customer Analytics") . '</h4>';
		echo '<div style="display: flex; gap: 20px;">';
		echo '<span><strong>' . _("Lifetime Value:") . '</strong> ' . FormatService::priceFormat($analytics['customer_lifetime_value']) . '</span>';
		echo '<span><strong>' . _("Outstanding:") . '</strong> ' . FormatService::priceFormat($analytics['outstanding_balance']) . '</span>';
		echo '<span><strong>' . _("Avg Payment Days:") . '</strong> ' . number_format($analytics['payment_days_avg'], 1) . '</span>';
		echo '</div>';
		echo '</div>';
	}
}

table_section_title(_("Basic Information"));

if (!$selected_id) {
	start_table(TABLESTYLE2);
} else {
	start_table(TABLESTYLE2);
	hidden('customer_id', $selected_id);
	hidden('selected_id', $selected_id);
}

text_row(_("Customer Name:"), 'CustName', @$_POST['CustName'], 40, 40);
text_row(_("Customer Short Name:"), 'cust_ref', @$_POST['cust_ref'], 30, 30);

textarea_row(_("Address:"), 'address', @$_POST['address'], 35, 5);

text_row(_("GST No:"), 'tax_id', null, 40, 40);

currencies_list_row(_("Customer's Currency:"), 'curr_code', @$_POST['curr_code']);

dimensions_list_row(_("Dimension 1:"), 'dimension_id', @$_POST['dimension_id'], true, " ", false, 1);
dimensions_list_row(_("Dimension 2:"), 'dimension2_id', @$_POST['dimension2_id'], true, " ", false, 2);

credit_status_list_row(_("Credit Status:"), 'credit_status', @$_POST['credit_status']);

payment_terms_list_row(_("Payment Terms:"), 'payment_terms', @$_POST['payment_terms']);

percent_row(_("Discount Percent:"), 'discount', @$_POST['discount']);
percent_row(_("Prompt Payment Discount Percent:"), 'pymt_discount', @$_POST['pymt_discount']);

amount_row(_("Credit Limit:"), 'credit_limit', @$_POST['credit_limit']);

sales_types_list_row(_("Sales Type/Price List:"), 'sales_type', @$_POST['sales_type'], false);

textarea_row(_("General Notes:"), 'notes', null, 35, 5);

if ($selected_id) {
	record_status_list_row(_("Customer status:"), 'inactive');
}

end_table(1);

// CRM Enhanced Features Section
if ($selected_id || !$selected_id) {
	table_section_title(_("CRM Information"));

	start_table(TABLESTYLE2);

	// Customer Type
	customer_types_list_row(_("Customer Type:"), 'customer_type_id', @$_POST['customer_type_id']);

	// Territory
	territories_list_row(_("Sales Territory:"), 'territory_id', @$_POST['territory_id']);

	// Customer Since
	date_row(_("Customer Since:"), 'customer_since', @$_POST['customer_since'], false, 0, 0, -10, null, true);

	// Website
	text_row(_("Website:"), 'website', @$_POST['website'], 50, 100);

	// Industry
	text_row(_("Industry:"), 'industry', @$_POST['industry'], 40, 50);

	// Company Size
	text_row(_("Employee Count:"), 'employee_count', @$_POST['employee_count'], 10, 10);

	// Annual Revenue
	amount_row(_("Annual Revenue:"), 'annual_revenue', @$_POST['annual_revenue']);

	// Account Manager
	text_row(_("Account Manager:"), 'account_manager', @$_POST['account_manager'], 40, 50);

	// Credit Rating
	credit_rating_row(_("Credit Rating:"), 'credit_rating', @$_POST['credit_rating']);

	end_table(1);
}

div_end();

// Contacts section
if ($selected_id) {
	div_start('contacts');
	table_section_title(_("Customer Contacts"));

	// Display existing contacts
	display_customer_contacts($selected_id);

	// Add new contact form
	if (isset($_POST['add_contact'])) {
		start_table(TABLESTYLE2);
		contact_roles_list_row(_("Contact Role:"), 'contact_role', null);
		text_row(_("Contact Name:"), 'contact_name', null, 40, 40);
		text_row(_("Phone:"), 'contact_phone', null, 30, 30);
		email_row(_("Email:"), 'contact_email', null, 40, 100);
		textarea_row(_("Notes:"), 'contact_notes', null, 35, 3);
		check_row(_("Statement Address:"), 'statement_address', null);
		end_table(1);

		submit_row('add_contact_submit', _("Add Contact"), false, 'colspan=2', '', 'medium');
	} else {
		echo '<center>' . submit('add_contact', _("Add New Contact"), false, '', 'default') . '</center>';
	}
	div_end();
}

// Sales Opportunities section
if ($selected_id) {
	div_start('opportunities');
	table_section_title(_("Sales Opportunities"));

	// Display existing opportunities
	display_customer_opportunities($selected_id);

	// Quick add opportunity form
	echo '<center>' . submit('add_opportunity', _("Add New Opportunity"), false, '', 'default') . '</center>';

	div_end();
}

div_end();

end_form();

end_page();