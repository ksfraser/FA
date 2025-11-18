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
$page_security = 'SA_SETUPCOMPANY';
$path_to_root = "..";
include($path_to_root . "/includes/session.inc");

page(_($help_context = UI_TEXT_COMPANY_SETUP_TITLE));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");

include_once($path_to_root . "/admin/db/company_db.inc");
include_once($path_to_root . "/reporting/includes/tcpdf.php");
//-------------------------------------------------------------------------------------------------

if (isset($_POST['update']) && $_POST['update'] != "")
{
	$input_error = 0;

	if (!check_num('login_tout', 10))
	{
		UiMessageService::displayError(_(UI_TEXT_LOGIN_TIMEOUT_ERROR));
		set_focus('login_tout');
		$input_error = 1;
	}
	if (strlen($_POST['coy_name'])==0)
	{
		$input_error = 1;
		UiMessageService::displayError(_(UI_TEXT_COMPANY_NAME_REQUIRED_ERROR));
		set_focus('coy_name');
	}
	if (!check_num('tax_prd', 1))
	{
		UiMessageService::displayError(_(UI_TEXT_TAX_PERIODS_ERROR));
		set_focus('tax_prd');
		$input_error = 1;
	}
	if (!check_num('tax_last', 1))
	{
		UiMessageService::displayError(_(UI_TEXT_TAX_LAST_PERIODS_ERROR));
		set_focus('tax_last');
		$input_error = 1;
	}
	if (!check_num('round_to', 1))
	{
		UiMessageService::displayError(_(UI_TEXT_ROUND_CALCULATED_ERROR));
		set_focus('round_to');
		$input_error = 1;
	}
	if (!check_num('max_days_in_docs', 1))
	{
		UiMessageService::displayError(_(UI_TEXT_MAX_DAY_RANGE_ERROR));
		set_focus('max_days_in_docs');
		$input_error = 1;
	}
	if ($_POST['add_pct'] != "" && !is_numeric($_POST['add_pct']))
	{
		UiMessageService::displayError(_(UI_TEXT_ADD_PRICE_FROM_STD_COST_ERROR));
		set_focus('add_pct');
		$input_error = 1;
	}	
	if (isset($_FILES['pic']) && $_FILES['pic']['name'] != '')
	{
    if ($_FILES['pic']['error'] == UPLOAD_ERR_INI_SIZE) {
			UiMessageService::displayError(_(UI_TEXT_FILE_SIZE_OVER_MAX_ERROR));
			$input_error = 1;
    }
    elseif ($_FILES['pic']['error'] > 0) {
			UiMessageService::displayError(_(UI_TEXT_ERROR_UPLOADING_LOGO_FILE));
			$input_error = 1;
    }
		$result = $_FILES['pic']['error'];
		$filename = company_path()."/images";
		if (!file_exists($filename))
		{
			mkdir($filename);
		}
		$filename .= "/".clean_file_name($_FILES['pic']['name']);

		 //But check for the worst
		if (!in_array( substr($filename,-4), array('.jpg','.JPG','.png','.PNG')))
		{
			UiMessageService::displayError(_('Only jpg and png files are supported - a file extension of .jpg or .png is expected'));
			$input_error = 1;
		}
		elseif ( $_FILES['pic']['size'] > ($SysPrefs->max_image_size * 1024))
		{ //File Size Check
			UiMessageService::displayError(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $SysPrefs->max_image_size);
			$input_error = 1;
		}
		elseif ( $_FILES['pic']['type'] == "text/plain" )
		{  //File type Check
			UiMessageService::displayError( _('Only graphics files can be uploaded'));
			$input_error = 1;
		}
		elseif (file_exists($filename))
		{
			$result = unlink($filename);
			if (!$result)
			{
				UiMessageService::displayError(_('The existing image could not be removed'));
				$input_error = 1;
			}
		}

		if ($input_error != 1) {
			$result  =  move_uploaded_file($_FILES['pic']['tmp_name'], $filename);
			$_POST['coy_logo'] = clean_file_name($_FILES['pic']['name']);
			if(!$result) {
				UiMessageService::displayError(_('Error uploading logo file'));
				$input_error = 1;
			} else {
				$msg = check_image_file($filename);
				if ( $msg) {
					UiMessageService::displayError( $msg);
					unlink($filename);
					$input_error = 1;
				}
			}
		}
	}
	if (RequestService::checkValueStatic('del_coy_logo'))
	{
		$filename = company_path()."/images/".clean_file_name($_POST['coy_logo']);
		if (file_exists($filename))
		{
			$result = unlink($filename);
			if (!$result)
			{
				UiMessageService::displayError(_('The existing image could not be removed'));
				$input_error = 1;
			}
		}
		$_POST['coy_logo'] = "";
	}
	if ($_POST['add_pct'] == "")
		$_POST['add_pct'] = -1;
	if ($_POST['round_to'] <= 0)
		$_POST['round_to'] = 1;
	if ($input_error != 1)
	{
		update_company_prefs(
			RequestService::getPostStatic( array('coy_name','coy_no','gst_no','tax_prd','tax_last',
				'postal_address','phone', 'fax', 'email', 'coy_logo', 'domicile',
				'use_dimension', 'curr_default', 'f_year', 'shortname_name_in_list',
				'no_item_list' => 0, 'no_customer_list' => 0, 'no_supplier_list' => 0, 
				'base_sales', 'ref_no_auto_increase' => 0, 'dim_on_recurrent_invoice' => 0, 'long_description_invoice' => 0, 'max_days_in_docs' => 180, 'company_logo_on_views' => 0,
				'time_zone' => 0, 'company_logo_report' => 0, 'barcodes_on_stock' => 0, 'print_dialog_direct' => 0, 
				'add_pct', 'round_to', 'login_tout', 'auto_curr_reval', 'bcc_email', 'alternative_tax_include_on_docs', 
				'suppress_tax_rates', 'use_manufacturing', 'use_fixed_assets'))
		);

		$_SESSION['wa_current_user']->timeout = $_POST['login_tout'];
		display_notification_centered(_(UI_TEXT_COMPANY_SETUP_UPDATED_NOTICE));
		set_focus('coy_name');
		$Ajax->activate('_page_body');
	}
} /* end of if submit */

start_form(true);

$myrow = get_company_prefs();

$_POST['coy_name'] = $myrow["coy_name"];
$_POST['gst_no'] = $myrow["gst_no"];
$_POST['tax_prd'] = $myrow["tax_prd"];
$_POST['tax_last'] = $myrow["tax_last"];
$_POST['coy_no']  = $myrow["coy_no"];
$_POST['postal_address']  = $myrow["postal_address"];
$_POST['phone']  = $myrow["phone"];
$_POST['fax']  = $myrow["fax"];
$_POST['email']  = $myrow["email"];
$_POST['coy_logo']  = $myrow["coy_logo"];
$_POST['domicile']  = $myrow["domicile"];
$_POST['use_dimension']  = $myrow["use_dimension"];
$_POST['base_sales']  = $myrow["base_sales"];
if (!isset($myrow["shortname_name_in_list"]))
{
	set_company_pref("shortname_name_in_list", "setup.company", "tinyint", 1, '0');
	$myrow["shortname_name_in_list"] = get_company_pref("shortname_name_in_list");
}
$_POST['shortname_name_in_list']  = $myrow["shortname_name_in_list"];
$_POST['no_item_list']  = $myrow["no_item_list"];
$_POST['no_customer_list']  = $myrow["no_customer_list"];
$_POST['no_supplier_list']  = $myrow["no_supplier_list"];
$_POST['curr_default']  = $myrow["curr_default"];
$_POST['f_year']  = $myrow["f_year"];
$_POST['time_zone']  = $myrow["time_zone"];
if (!isset($myrow["max_days_in_docs"]))
{
	set_company_pref("max_days_in_docs", "setup.company", "smallint", 5, '180');
	$myrow["max_days_in_docs"] = get_company_pref("max_days_in_docs");
}
$_POST['max_days_in_docs']  = $myrow["max_days_in_docs"];
if (!isset($myrow["company_logo_report"]))
{
	set_company_pref("company_logo_report", "setup.company", "tinyint", 1, '0');
	$myrow["company_logo_report"] = get_company_pref("company_logo_report");
}
$_POST['company_logo_report']  = $myrow["company_logo_report"];
if (!isset($myrow["ref_no_auto_increase"]))
{
	set_company_pref("ref_no_auto_increase", "setup.company", "tinyint", 1, '0');
	$myrow["ref_no_auto_increase"] = get_company_pref("ref_no_auto_increase");
}
$_POST['ref_no_auto_increase']  = $myrow["ref_no_auto_increase"];
if (!isset($myrow["barcodes_on_stock"]))
{
	set_company_pref("barcodes_on_stock", "setup.company", "tinyint", 1, '0');
	$myrow["barcodes_on_stock"] = get_company_pref("barcodes_on_stock");
}
$_POST['barcodes_on_stock']  = $myrow["barcodes_on_stock"];
if (!isset($myrow["print_dialog_direct"]))
{
	set_company_pref("print_dialog_direct", "setup.company", "tinyint", 1, '0');
	$myrow["print_dialog_direct"] = get_company_pref("print_dialog_direct");
}
$_POST['print_dialog_direct']  = $myrow["print_dialog_direct"];
if (!isset($myrow["dim_on_recurrent_invoice"]))
{
	set_company_pref("dim_on_recurrent_invoice", "setup.company", "tinyint", 1, '0');
	$myrow["dim_on_recurrent_invoice"] = get_company_pref("dim_on_recurrent_invoice");
}
$_POST['dim_on_recurrent_invoice']  = $myrow["dim_on_recurrent_invoice"];
if (!isset($myrow["long_description_invoice"]))
{
	set_company_pref("long_description_invoice", "setup.company", "tinyint", 1, '0');
	$myrow["long_description_invoice"] = get_company_pref("long_description_invoice");
}
$_POST['long_description_invoice']  = $myrow["long_description_invoice"];
if (!isset($myrow["company_logo_on_views"]))
{
	set_company_pref("company_logo_on_views", "setup.company", "tinyint", 1, '0');
	$myrow["company_logo_on_views"] = get_company_pref("company_logo_on_views");
}
$_POST['company_logo_on_views']  = $myrow["company_logo_on_views"];
$_POST['version_id']  = $myrow["version_id"];
$_POST['add_pct'] = $myrow['add_pct'];
$_POST['login_tout'] = $myrow['login_tout'];
if ($_POST['add_pct'] == -1)
	$_POST['add_pct'] = "";
$_POST['round_to'] = $myrow['round_to'];	
$_POST['auto_curr_reval'] = $myrow['auto_curr_reval'];	
$_POST['del_coy_logo']  = 0;
$_POST['bcc_email']  = $myrow["bcc_email"];
$_POST['alternative_tax_include_on_docs']  = $myrow["alternative_tax_include_on_docs"];
$_POST['suppress_tax_rates']  = $myrow["suppress_tax_rates"];
$_POST['use_manufacturing']  = $myrow["use_manufacturing"];
$_POST['use_fixed_assets']  = $myrow["use_fixed_assets"];

start_outer_table(TABLESTYLE2);

table_section(1);
table_section_title(_(UI_TEXT_GENERAL_SETTINGS));

text_row_ex(_(UI_TEXT_COMPANY_NAME_LABEL), 'coy_name', 50, 50);
textarea_row(_(UI_TEXT_ADDRESS_LABEL), 'postal_address', $_POST['postal_address'], 34, 5);
text_row_ex(_(UI_TEXT_DOMICILE_LABEL), 'domicile', 25, 55);

text_row_ex(_(UI_TEXT_PHONE_NUMBER_LABEL), 'phone', 25, 55);
text_row_ex(_(UI_TEXT_FAX_NUMBER_LABEL), 'fax', 25);
email_row_ex(_(UI_TEXT_EMAIL_ADDRESS_LABEL), 'email', 50, 55);

email_row_ex(_(UI_TEXT_BCC_EMAIL_LABEL), 'bcc_email', 50, 55);

text_row_ex(_(UI_TEXT_OFFICIAL_COMPANY_NUMBER_LABEL), 'coy_no', 25);
text_row_ex(_(UI_TEXT_GST_NO_LABEL), 'gst_no', 25);
currencies_list_row(_(UI_TEXT_HOME_CURRENCY_LABEL), 'curr_default', $_POST['curr_default']);

label_row(_(UI_TEXT_COMPANY_LOGO_LABEL), $_POST['coy_logo']);
file_row(_(UI_TEXT_NEW_COMPANY_LOGO_LABEL) . ":", 'pic', 'pic');
check_row(_(UI_TEXT_DELETE_COMPANY_LOGO_LABEL), 'del_coy_logo', $_POST['del_coy_logo']);

check_row(_(UI_TEXT_TIME_ZONE_ON_REPORTS_LABEL), 'time_zone', $_POST['time_zone']);
check_row(_(UI_TEXT_COMPANY_LOGO_ON_REPORTS_LABEL), 'company_logo_report', $_POST['company_logo_report']);
check_row(_(UI_TEXT_USE_BARCODES_ON_STOCKS_LABEL), 'barcodes_on_stock', $_POST['barcodes_on_stock']);
check_row(_(UI_TEXT_AUTO_INCREASE_DOC_REFS_LABEL), 'ref_no_auto_increase', $_POST['ref_no_auto_increase']);
check_row(_(UI_TEXT_USE_DIMENSIONS_ON_RECURRENT_INVOICES_LABEL), 'dim_on_recurrent_invoice', $_POST['dim_on_recurrent_invoice']);
check_row(_(UI_TEXT_USE_LONG_DESCRIPTIONS_ON_INVOICES_LABEL), 'long_description_invoice', $_POST['long_description_invoice']);
check_row(_(UI_TEXT_COMPANY_LOGO_ON_VIEWS_LABEL), 'company_logo_on_views', $_POST['company_logo_on_views']);
label_row(_(UI_TEXT_DATABASE_SCHEME_VERSION_LABEL), $_POST['version_id']);

table_section(2);

table_section_title(_(UI_TEXT_GENERAL_LEDGER_SETTINGS));
fiscalyears_list_row(_(UI_TEXT_FISCAL_YEAR_LABEL), 'f_year', $_POST['f_year']);
text_row_ex(_(UI_TEXT_TAX_PERIODS_LABEL), 'tax_prd', 10, 10, '', null, null, _(UI_TEXT_MONTHS));
text_row_ex(_(UI_TEXT_TAX_LAST_PERIOD_LABEL), 'tax_last', 10, 10, '', null, null, _(UI_TEXT_MONTHS_BACK));
check_row(_(UI_TEXT_PUT_ALTERNATIVE_TAX_INCLUDE_ON_DOCS_LABEL), 'alternative_tax_include_on_docs', null);
check_row(_(UI_TEXT_SUPPRESS_TAX_RATES_ON_DOCS_LABEL), 'suppress_tax_rates', null);
check_row(_(UI_TEXT_AUTOMATIC_REVALUATION_CURRENCY_ACCOUNTS_LABEL), 'auto_curr_reval', $_POST['auto_curr_reval']);

table_section_title(_(UI_TEXT_SALES_PRICING));
sales_types_list_row(_(UI_TEXT_BASE_FOR_AUTO_PRICE_CALCULATIONS_LABEL), 'base_sales', $_POST['base_sales'], false,
    _(UI_TEXT_NO_BASE_PRICE_LIST) );

text_row_ex(_(UI_TEXT_ADD_PRICE_FROM_STD_COST_LABEL), 'add_pct', 10, 10, '', null, null, "%");
$curr = get_currency($_POST['curr_default']);
text_row_ex(_(UI_TEXT_ROUND_CALCULATED_PRICES_LABEL), 'round_to', 10, 10, '', null, null, $curr['hundreds_name']);
label_row("", "&nbsp;");


table_section_title(_(UI_TEXT_OPTIONAL_MODULES));
check_row(_(UI_TEXT_MANUFACTURING_LABEL), 'use_manufacturing', null);
check_row(_(UI_TEXT_FIXED_ASSETS_LABEL), 'use_fixed_assets', null);
number_list_row(_(UI_TEXT_USE_DIMENSIONS_LABEL), 'use_dimension', null, 0, 2);

table_section_title(_(UI_TEXT_USER_INTERFACE_OPTIONS));

check_row(_(UI_TEXT_SHORT_NAME_AND_NAME_IN_LIST_LABEL), 'shortname_name_in_list', $_POST['shortname_name_in_list']);
check_row(_(UI_TEXT_OPEN_PRINT_DIALOG_DIRECT_ON_REPORTS_LABEL), 'print_dialog_direct', null);
check_row(_(UI_TEXT_SEARCH_ITEM_LIST_LABEL), 'no_item_list', null);
check_row(_(UI_TEXT_SEARCH_CUSTOMER_LIST_LABEL), 'no_customer_list', null);
check_row(_(UI_TEXT_SEARCH_SUPPLIER_LIST_LABEL), 'no_supplier_list', null);
text_row_ex(_(UI_TEXT_LOGIN_TIMEOUT_LABEL), 'login_tout', 10, 10, '', null, null, _(UI_TEXT_SECONDS));
text_row_ex(_(UI_TEXT_MAX_DAY_RANGE_IN_DOCUMENTS_LABEL), 'max_days_in_docs', 10, 10, '', null, null, _(UI_TEXT_DAYS));

end_outer_table(1);

hidden('coy_logo', $_POST['coy_logo']);
submit_center('update', _(UI_TEXT_UPDATE_BUTTON), true, '',  'default');

end_form(2);
//-------------------------------------------------------------------------------------------------

end_page();

