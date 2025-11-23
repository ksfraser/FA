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
$page_security = 'SA_GLSETUP';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);

page(_($help_context = "System and General GL Setup"), false, false, "", $js);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/CompanyPrefsService.php");

include_once($path_to_root . "/admin/db/company_db.inc");

//-------------------------------------------------------------------------------------------------

function can_process()
{
    if (!check_num('past_due_days', 0, 100))
    {
        UiMessageService::displayError(_(UI_TEXT_PAST_DUE_DAYS_INTERVAL_ALLOWANCE_ERROR));
        set_focus('past_due_days');
        return false;
    }

    if (!check_num('default_quote_valid_days', 0))
    {
        UiMessageService::displayError(_(UI_TEXT_QUOTE_VALID_DAYS_INVALID_ERROR));
        set_focus('default_quote_valid_days');
        return false;
    }

    if (!check_num('default_delivery_required', 0))
    {
        UiMessageService::displayError(_(UI_TEXT_DELIVERY_REQUIRED_BY_INVALID_ERROR));
        set_focus('default_delivery_required');
        return false;
    }

    if (!check_num('default_receival_required', 0))
    {
        UiMessageService::displayError(_(UI_TEXT_RECEIVAL_REQUIRED_BY_INVALID_ERROR));
        set_focus('default_receival_required');
        return false;
    }

    if (!check_num('default_workorder_required', 0))
    {
        UiMessageService::displayError(_(UI_TEXT_WORK_ORDER_REQUIRED_BY_AFTER_INVALID_ERROR));
        set_focus('default_workorder_required');
        return false;
    }	

    if (!check_num('po_over_receive', 0, 100))
	{
		UiMessageService::displayError(_(UI_TEXT_DELIVERY_OVER_RECEIVE_ALLOWANCE_ERROR));
		set_focus('po_over_receive');
		return false;
	}

	if (!check_num('po_over_charge', 0, 100))
	{
		UiMessageService::displayError(_(UI_TEXT_INVOICE_OVER_CHARGE_ALLOWANCE_ERROR));
		set_focus('po_over_charge');
		return false;
	}

	if (!check_num('past_due_days', 0, 100))
	{
		UiMessageService::displayError(_(UI_TEXT_PAST_DUE_DAYS_INTERVAL_ALLOWANCE_ERROR));
		set_focus('past_due_days');
		return false;
	}

	$grn_act = CompanyPrefsService::getCompanyPref('grn_clearing_act');
	$post_grn_act = RequestService::getPostStatic('grn_clearing_act');
	if ($post_grn_act == null)
		$post_grn_act = 0;
	if (($post_grn_act != $grn_act) && db_num_rows(get_grn_items(0, '', true)))
	{
		UiMessageService::displayError(_(UI_TEXT_GRN_CLEARING_ACCOUNT_CHANGE_ERROR));
		$_POST['grn_clearing_act'] = $grn_act;
		set_focus('grn_clearing_account');
		return false;
	}
	if (!is_account_balancesheet(RequestService::getPostStatic('retained_earnings_act')) || is_account_balancesheet(RequestService::getPostStatic('profit_loss_year_act')))
	{
		UiMessageService::displayError(_(UI_TEXT_RETAINED_EARNINGS_ACCOUNT_ERROR));
		return false;
	}
	return true;
}

//-------------------------------------------------------------------------------------------------

if (isset($_POST['submit']) && can_process())
{
	update_company_prefs( RequestService::getPostStatic( array( 'retained_earnings_act', 'profit_loss_year_act',
		'debtors_act', 'pyt_discount_act', 'creditors_act', 'freight_act', 'deferred_income_act',
		'exchange_diff_act', 'bank_charge_act', 'default_sales_act', 'default_sales_discount_act',
		'default_prompt_payment_act', 'default_inventory_act', 'default_cogs_act', 'depreciation_period',
		'default_loss_on_asset_disposal_act', 'default_adj_act', 'default_inv_sales_act', 'default_wip_act', 'legal_text',
		'past_due_days', 'default_workorder_required', 'default_dim_required', 'default_receival_required',
		'default_delivery_required', 'default_quote_valid_days', 'grn_clearing_act', 'tax_algorithm',
		'no_zero_lines_amount', 'show_po_item_codes', 'accounts_alpha', 'loc_notification', 'print_invoice_no',
		'allow_negative_prices', 'print_item_images_on_quote', 
		'allow_negative_stock'=> 0, 'accumulate_shipping'=> 0,
		'po_over_receive' => 0.0, 'po_over_charge' => 0.0, 'default_credit_limit'=>0.0
)));

	\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_GENERAL_GL_SETUP_UPDATED));

} /* end of if submit */

//-------------------------------------------------------------------------------------------------

start_form();

start_outer_table(TABLESTYLE2);

table_section(1);

$myrow = get_company_prefs();

$_POST['retained_earnings_act']  = \FA\Services\CompanyPrefsService::getCompanyPref('retained_earnings_act');
$_POST['profit_loss_year_act']  = \FA\Services\CompanyPrefsService::getCompanyPref('profit_loss_year_act');
$_POST['debtors_act']  = \FA\Services\CompanyPrefsService::getCompanyPref('debtors_act');
$_POST['creditors_act']  = \FA\Services\CompanyPrefsService::getCompanyPref('creditors_act');
$_POST['freight_act'] = \FA\Services\CompanyPrefsService::getCompanyPref('freight_act');
$_POST['deferred_income_act'] = \FA\Services\CompanyPrefsService::getCompanyPref('deferred_income_act');
$_POST['pyt_discount_act']  = \FA\Services\CompanyPrefsService::getCompanyPref('pyt_discount_act');

$_POST['exchange_diff_act'] = \FA\Services\CompanyPrefsService::getCompanyPref('exchange_diff_act');
$_POST['bank_charge_act'] = \FA\Services\CompanyPrefsService::getCompanyPref('bank_charge_act');
$_POST['tax_algorithm'] = \FA\Services\CompanyPrefsService::getCompanyPref('tax_algorithm');
$_POST['default_sales_act'] = \FA\Services\CompanyPrefsService::getCompanyPref('default_sales_act');
$_POST['default_sales_discount_act']  = \FA\Services\CompanyPrefsService::getCompanyPref('default_sales_discount_act');
$_POST['default_prompt_payment_act']  = \FA\Services\CompanyPrefsService::getCompanyPref('default_prompt_payment_act');

$_POST['default_inventory_act'] = \FA\Services\CompanyPrefsService::getCompanyPref('default_inventory_act');
$_POST['default_cogs_act'] = \FA\Services\CompanyPrefsService::getCompanyPref('default_cogs_act');
$_POST['default_adj_act'] = \FA\Services\CompanyPrefsService::getCompanyPref('default_adj_act');
$_POST['default_inv_sales_act'] = \FA\Services\CompanyPrefsService::getCompanyPref('default_inv_sales_act');
$_POST['default_wip_act'] = \FA\Services\CompanyPrefsService::getCompanyPref('default_wip_act');

$_POST['allow_negative_stock'] = \FA\Services\CompanyPrefsService::getCompanyPref('allow_negative_stock');

$_POST['po_over_receive'] = percent_format(\FA\Services\CompanyPrefsService::getCompanyPref('po_over_receive'));
$_POST['po_over_charge'] = percent_format(\FA\Services\CompanyPrefsService::getCompanyPref('po_over_charge'));
$_POST['past_due_days'] = \FA\Services\CompanyPrefsService::getCompanyPref('past_due_days');

$_POST['grn_clearing_act'] = $myrow['grn_clearing_act'];

$_POST['default_credit_limit'] = FormatService::priceFormat($myrow['default_credit_limit']);
$_POST['legal_text'] = $myrow['legal_text'];
$_POST['accumulate_shipping'] = $myrow['accumulate_shipping'];

$_POST['default_workorder_required'] = $myrow['default_workorder_required'];
$_POST['default_dim_required'] = $myrow['default_dim_required'];
$_POST['default_delivery_required'] = $myrow['default_delivery_required'];
$_POST['default_receival_required'] = $myrow['default_receival_required'];
$_POST['default_quote_valid_days'] = $myrow['default_quote_valid_days'];
$_POST['no_zero_lines_amount'] = $myrow['no_zero_lines_amount'];
$_POST['show_po_item_codes'] = $myrow['show_po_item_codes'];
$_POST['accounts_alpha'] = $myrow['accounts_alpha'];
$_POST['loc_notification'] = $myrow['loc_notification'];
$_POST['print_invoice_no'] = $myrow['print_invoice_no'];
$_POST['allow_negative_prices'] = $myrow['allow_negative_prices'];
$_POST['print_item_images_on_quote'] = $myrow['print_item_images_on_quote'];
$_POST['default_loss_on_asset_disposal_act'] = $myrow['default_loss_on_asset_disposal_act'];
$_POST['depreciation_period'] = $myrow['depreciation_period'];

//---------------


table_section_title(_(UI_TEXT_GENERAL_GL));

text_row(_(UI_TEXT_PAST_DUE_DAYS_INTERVAL_LABEL), 'past_due_days', $_POST['past_due_days'], 6, 6, '', "", _(UI_TEXT_DAYS));

accounts_type_list_row(_(UI_TEXT_ACCOUNTS_TYPE_LABEL), 'accounts_alpha', $_POST['accounts_alpha']); 

gl_all_accounts_list_row(_(UI_TEXT_RETAINED_EARNINGS_LABEL), 'retained_earnings_act', $_POST['retained_earnings_act']);

gl_all_accounts_list_row(_(UI_TEXT_PROFIT_LOSS_YEAR_LABEL), 'profit_loss_year_act', $_POST['profit_loss_year_act']);

gl_all_accounts_list_row(_(UI_TEXT_EXCHANGE_VARIANCES_ACCOUNT_LABEL), 'exchange_diff_act', $_POST['exchange_diff_act']);

gl_all_accounts_list_row(_(UI_TEXT_BANK_CHARGES_ACCOUNT_LABEL), 'bank_charge_act', $_POST['bank_charge_act']);

tax_algorithm_list_row(_(UI_TEXT_TAX_ALGORITHM_LABEL), 'tax_algorithm', $_POST['tax_algorithm']);

//---------------

table_section_title(_(UI_TEXT_DIMENSION_DEFAULTS));

text_row(_(UI_TEXT_DIMENSION_REQUIRED_BY_AFTER_LABEL), 'default_dim_required', $_POST['default_dim_required'], 6, 6, '', "", _(UI_TEXT_DAYS));

//----------------

table_section_title(_(UI_TEXT_CUSTOMERS_AND_SALES));

amount_row(_(UI_TEXT_DEFAULT_CREDIT_LIMIT_LABEL), 'default_credit_limit', $_POST['default_credit_limit']);

yesno_list_row(_(UI_TEXT_INVOICE_IDENTIFICATION_LABEL), 'print_invoice_no', $_POST['print_invoice_no'], $name_yes=_(UI_TEXT_NUMBER_OPTION), $name_no=_(UI_TEXT_REFERENCE));

check_row(_(UI_TEXT_ACCUMULATE_BATCH_SHIPPING_LABEL), 'accumulate_shipping', null);

check_row(_(UI_TEXT_PRINT_ITEM_IMAGE_ON_QUOTE_LABEL), 'print_item_images_on_quote', null);

textarea_row(_(UI_TEXT_LEGAL_TEXT_ON_INVOICE_LABEL), 'legal_text', $_POST['legal_text'], 32, 4);

gl_all_accounts_list_row(_(UI_TEXT_SHIPPING_CHARGED_ACCOUNT_LABEL), 'freight_act', $_POST['freight_act']);

gl_all_accounts_list_row(_(UI_TEXT_DEFERRED_INCOME_ACCOUNT_LABEL), 'deferred_income_act', $_POST['deferred_income_act'], true, false, _(UI_TEXT_NOT_USED), false, false, false);

//---------------

table_section_title(_(UI_TEXT_CUSTOMERS_AND_SALES_DEFAULTS));
// default for customer branch
gl_all_accounts_list_row(_(UI_TEXT_RECEIVABLE_ACCOUNT_LABEL), 'debtors_act');

gl_all_accounts_list_row(_(UI_TEXT_SALES_ACCOUNT_LABEL), 'default_sales_act', null,
	false, false, true);

gl_all_accounts_list_row(_(UI_TEXT_SALES_DISCOUNT_ACCOUNT_LABEL), 'default_sales_discount_act');

gl_all_accounts_list_row(_(UI_TEXT_PROMPT_PAYMENT_DISCOUNT_ACCOUNT_LABEL), 'default_prompt_payment_act');

text_row(_(UI_TEXT_QUOTE_VALID_DAYS_LABEL), 'default_quote_valid_days', $_POST['default_quote_valid_days'], 6, 6, '', "", _(UI_TEXT_DAYS));

text_row(_(UI_TEXT_DELIVERY_REQUIRED_BY_LABEL), 'default_delivery_required', $_POST['default_delivery_required'], 6, 6, '', "", _(UI_TEXT_DAYS));

//---------------

table_section(2);

table_section_title(_(UI_TEXT_SUPPLIERS_AND_PURCHASING));

percent_row(_(UI_TEXT_DELIVERY_OVER_RECEIVE_ALLOWANCE_LABEL), 'po_over_receive');

percent_row(_(UI_TEXT_INVOICE_OVER_CHARGE_ALLOWANCE_LABEL), 'po_over_charge');

table_section_title(_(UI_TEXT_SUPPLIERS_AND_PURCHASING_DEFAULTS));

gl_all_accounts_list_row(_(UI_TEXT_PAYABLE_ACCOUNT_LABEL), 'creditors_act', $_POST['creditors_act']);

gl_all_accounts_list_row(_(UI_TEXT_PURCHASE_DISCOUNT_ACCOUNT_LABEL), 'pyt_discount_act', $_POST['pyt_discount_act']);

gl_all_accounts_list_row(_(UI_TEXT_GRN_CLEARING_ACCOUNT_LABEL), 'grn_clearing_act', RequestService::getPostStatic('grn_clearing_act'), true, false, _(UI_TEXT_NO_POSTINGS_ON_GRN));

text_row(_(UI_TEXT_RECEIVAL_REQUIRED_BY_LABEL), 'default_receival_required', $_POST['default_receival_required'], 6, 6, '', "", _(UI_TEXT_DAYS));

check_row(_(UI_TEXT_SHOW_PO_ITEM_CODES_LABEL), 'show_po_item_codes', null);

table_section_title(_(UI_TEXT_INVENTORY));

check_row(_(UI_TEXT_ALLOW_NEGATIVE_INVENTORY_LABEL), 'allow_negative_stock', null);
label_row(null, _(UI_TEXT_WARNING_DELAY_IN_GL_POSTINGS), "", "class='stockmankofg' colspan=2"); 

check_row(_(UI_TEXT_NO_ZERO_AMOUNTS_SERVICE_LABEL), 'no_zero_lines_amount', null);

check_row(_(UI_TEXT_LOCATION_NOTIFICATIONS_LABEL), 'loc_notification', null);

check_row(_(UI_TEXT_ALLOW_NEGATIVE_PRICES_LABEL), 'allow_negative_prices', null);

table_section_title(_(UI_TEXT_ITEMS_DEFAULTS));
gl_all_accounts_list_row(_(UI_TEXT_SALES_ACCOUNT_LABEL), 'default_inv_sales_act', $_POST['default_inv_sales_act']);

gl_all_accounts_list_row(_(UI_TEXT_INVENTORY_ACCOUNT_LABEL), 'default_inventory_act', $_POST['default_inventory_act']);
// this one is default for items and suppliers (purchase account)
gl_all_accounts_list_row(_(UI_TEXT_COGS_ACCOUNT_LABEL), 'default_cogs_act', $_POST['default_cogs_act']);

gl_all_accounts_list_row(_(UI_TEXT_INVENTORY_ADJUSTMENTS_ACCOUNT_LABEL), 'default_adj_act', $_POST['default_adj_act']);

gl_all_accounts_list_row(_(UI_TEXT_WIP_ACCOUNT_LABEL), 'default_wip_act', $_POST['default_wip_act']);

//----------------

table_section_title(_(UI_TEXT_FIXED_ASSETS_DEFAULTS));

gl_all_accounts_list_row(_(UI_TEXT_LOSS_ON_ASSET_DISPOSAL_ACCOUNT_LABEL), 'default_loss_on_asset_disposal_act', $_POST['default_loss_on_asset_disposal_act']);

array_selector_row (_(UI_TEXT_DEPRECIATION_PERIOD_LABEL), 'depreciation_period', $_POST['depreciation_period'], array(FA_MONTHLY => _(UI_TEXT_MONTHLY), FA_YEARLY => _(UI_TEXT_YEARLY)));

//----------------

table_section_title(_(UI_TEXT_MANUFACTURING_DEFAULTS));

text_row(_(UI_TEXT_WORK_ORDER_REQUIRED_BY_AFTER_LABEL), 'default_workorder_required', $_POST['default_workorder_required'], 6, 6, '', "", _(UI_TEXT_DAYS));

//----------------

end_outer_table(1);

submit_center('submit', _(UI_TEXT_UPDATE), true, '', 'default');

end_form(2);

//-------------------------------------------------------------------------------------------------

end_page();

