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
$page_security = 'SA_SETUPDISPLAY';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

page(_($help_context = "Display Setup"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");

include_once($path_to_root . "/admin/db/company_db.inc");

//-------------------------------------------------------------------------------------------------

if (isset($_POST['setprefs'])) 
{
	if (!is_numeric($_POST['query_size']) || ($_POST['query_size']<1))
	{
		UiMessageService::displayError($_POST['query_size']);
		UiMessageService::displayError( _(UI_TEXT_QUERY_SIZE_INVALID));
		set_focus('query_size');
	} else {
		$_POST['theme'] = clean_file_name($_POST['theme']);
		$chg_theme = user_theme() != $_POST['theme'];
		$chg_lang = $_SESSION['language']->code != $_POST['language'];
		$chg_date_format = user_date_format() != $_POST['date_format'];
		$chg_date_sep = user_date_sep() != $_POST['date_sep'];

		set_user_prefs(RequestService::getPostStatic( 
			array('prices_dec', 'qty_dec', 'rates_dec', 'percent_dec',
			'date_format', 'date_sep', 'tho_sep', 'dec_sep', 'print_profile', 
			'theme', 'page_size', 'language', 'startup_tab',
			'query_size' => 10, 'transaction_days' => 30, 'save_report_selections' => 0,
			'def_print_destination' => 0, 'def_print_orientation' => 0)));

		set_user_prefs(RequestService::checkValueStatic(
			array( 'show_gl', 'show_codes', 'show_hints', 'rep_popup',
			  'graphic_links', 'sticky_doc_date', 'use_date_picker')));

		if ($chg_lang)
			$_SESSION['language']->set_language($_POST['language']);
			// refresh main menu

		flush_dir(company_path().'/js_cache');	

		if ($chg_theme && $SysPrefs->allow_demo_mode)
			$_SESSION["wa_current_user"]->prefs->theme = $_POST['theme'];
		if ($chg_theme || $chg_lang || $chg_date_format || $chg_date_sep)
			meta_forward($_SERVER['PHP_SELF']);

		
		if ($SysPrefs->allow_demo_mode)  
			\FA\Services\UiMessageService::displayWarning(_(UI_TEXT_DISPLAY_SETTINGS_UPDATED_DEMO_WARNING));
		else
			\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_DISPLAY_SETTINGS_UPDATED));
	}
}

start_form();

start_outer_table(TABLESTYLE2);

table_section(1);
table_section_title(_(UI_TEXT_DECIMAL_PLACES));

number_list_row(_(UI_TEXT_PRICES_AMOUNTS_LABEL), 'prices_dec', \FA\UserPrefsCache::getPriceDecimals(), 0, 10);
number_list_row(_(UI_TEXT_QUANTITIES_LABEL), 'qty_dec', \FA\UserPrefsCache::getQtyDecimals(), 0, 10);
number_list_row(_(UI_TEXT_EXCHANGE_RATES_LABEL), 'rates_dec', \FA\UserPrefsCache::getExrateDecimals(), 0, 10);
number_list_row(_(UI_TEXT_PERCENTAGES_LABEL), 'percent_dec', \FA\UserPrefsCache::getPercentDecimals(), 0, 10);

table_section_title(_(UI_TEXT_DATE_FORMAT_AND_SEPARATORS));

dateformats_list_row(_(UI_TEXT_DATE_FORMAT_LABEL), "date_format", user_date_format());

dateseps_list_row(_(UI_TEXT_DATE_SEPARATOR_LABEL), "date_sep", user_date_sep());

thoseps_list_row(_(UI_TEXT_THOUSAND_SEPARATOR_LABEL), "tho_sep", user_tho_sep());

decseps_list_row(_(UI_TEXT_DECIMAL_SEPARATOR_LABEL), "dec_sep", user_dec_sep());

check_row(_(UI_TEXT_USE_DATE_PICKER_LABEL), 'use_date_picker', user_use_date_picker());"Date Separator:"), "date_sep", user_date_sep());

/* The array $dateseps is set up in config.php for modifications
possible separators can be added by modifying the array definition by editing that file */

thoseps_list_row(_(UI_TEXT_THOUSAND_SEPARATOR_LABEL), "tho_sep", user_tho_sep());

/* The array $thoseps is set up in config.php for modifications
possible separators can be added by modifying the array definition by editing that file */

decseps_list_row(_(UI_TEXT_DECIMAL_SEPARATOR_LABEL), "dec_sep", user_dec_sep());

/* The array $decseps is set up in config.php for modifications
possible separators can be added by modifying the array definition by editing that file */

check_row(_(UI_TEXT_USE_DATE_PICKER_LABEL), 'use_date_picker', user_use_date_picker());

if (!isset($_POST['language']))
	$_POST['language'] = $_SESSION['language']->code;

table_section_title(_(UI_TEXT_REPORTS));

text_row_ex(_(UI_TEXT_SAVE_REPORT_SELECTION_DAYS_LABEL), 'save_report_selections', 5, 5, '', user_save_report_selections());

yesno_list_row(_(UI_TEXT_DEFAULT_REPORT_DESTINATION_LABEL), 'def_print_destination', user_def_print_destination(), 
	$name_yes=_(UI_TEXT_EXCEL), $name_no=_(UI_TEXT_PDF_PRINTER));

yesno_list_row(_(UI_TEXT_DEFAULT_REPORT_ORIENTATION_LABEL), 'def_print_orientation', user_def_print_orientation(), 
	$name_yes=_(UI_TEXT_LANDSCAPE), $name_no=_(UI_TEXT_PORTRAIT));

table_section(2);

table_section_title(_(UI_TEXT_MISCELLANEOUS));

check_row(_(UI_TEXT_SHOW_HINTS_FOR_NEW_USERS_LABEL), 'show_hints', user_hints());

check_row(_(UI_TEXT_SHOW_GL_INFORMATION_LABEL), 'show_gl', user_show_gl_info());

check_row(_(UI_TEXT_SHOW_ITEM_CODES_LABEL), 'show_codes', user_show_codes());

themes_list_row(_(UI_TEXT_THEME_LABEL), "theme", user_theme());

/* The array $themes is set up in config.php for modifications
possible separators can be added by modifying the array definition by editing that file */

pagesizes_list_row(_(UI_TEXT_PAGE_SIZE_LABEL), "page_size", user_pagesize());

tab_list_row(_(UI_TEXT_STARTUP_TAB_LABEL), 'startup_tab', user_startup_tab());

/* The array $pagesizes is set up in config.php for modifications
possible separators can be added by modifying the array definition by editing that file */

if (!isset($_POST['print_profile']))
	$_POST['print_profile'] = user_print_profile();

print_profiles_list_row(_(UI_TEXT_PRINTING_PROFILE_LABEL). ':', 'print_profile', 
	null, _(UI_TEXT_BROWSER_PRINTING_SUPPORT));

check_row(_(UI_TEXT_USE_POPUP_WINDOW_FOR_REPORTS_LABEL), 'rep_popup', user_rep_popup(),
	false, _(UI_TEXT_SET_OPTION_FOR_PDF_SUPPORT));

check_row(_(UI_TEXT_USE_ICONS_INSTEAD_OF_TEXT_LINKS_LABEL), 'graphic_links', user_graphic_links(),
	false, _(UI_TEXT_SET_OPTION_FOR_ICONS));

check_row(_(UI_TEXT_REMEMBER_LAST_DOCUMENT_DATE_LABEL), 'sticky_doc_date', sticky_doc_date(),
	false, _(UI_TEXT_IF_SET_DOCUMENT_DATE_REMEMBERED));

text_row_ex(_(UI_TEXT_QUERY_PAGE_SIZE_LABEL), 'query_size',  5, 5, '', user_query_size());

text_row_ex(_(UI_TEXT_TRANSACTION_DAYS_LABEL), 'transaction_days', 5, 5, '', user_transaction_days());

table_section_title(_(UI_TEXT_LANGUAGE));

languages_list_row(_(UI_TEXT_LANGUAGE_LABEL), 'language', $_POST['language']);

end_outer_table(1);

submit_center('setprefs', _(UI_TEXT_UPDATE), true, '',  'default');

end_form(2);

//-------------------------------------------------------------------------------------------------

end_page();

