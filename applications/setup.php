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
include_once($path_to_root . "/includes/ui_strings.php");

class setup_app extends application
{
	function __construct()
	{
		parent::__construct("system", _($this->help_context = _(UI_TEXT_SETUP)));

		$this->add_module(_(UI_TEXT_COMPANY_SETUP_MODULE));
		$this->add_lapp_function(0, _(UI_TEXT_COMPANY_SETUP),
			"admin/company_preferences.php?", 'SA_SETUPCOMPANY', MENU_SETTINGS);
		$this->add_lapp_function(0, _(UI_TEXT_USER_ACCOUNTS_SETUP),
			"admin/users.php?", 'SA_USERS', MENU_SETTINGS);
		$this->add_lapp_function(0, _(UI_TEXT_ACCESS_SETUP),
			"admin/security_roles.php?", 'SA_SECROLES', MENU_SETTINGS);
		$this->add_lapp_function(0, _(UI_TEXT_DISPLAY_SETUP),
			"admin/display_prefs.php?", 'SA_SETUPDISPLAY', MENU_SETTINGS);
		$this->add_lapp_function(0, _(UI_TEXT_TRANSACTION_REFERENCES),
			"admin/forms_setup.php?", 'SA_FORMSETUP', MENU_SETTINGS);
		$this->add_rapp_function(0, _(UI_TEXT_TAXES),
			"taxes/tax_types.php?", 'SA_TAXRATES', MENU_MAINTENANCE);
		$this->add_rapp_function(0, _(UI_TEXT_TAX_GROUPS),
			"taxes/tax_groups.php?", 'SA_TAXGROUPS', MENU_MAINTENANCE);
		$this->add_rapp_function(0, _(UI_TEXT_ITEM_TAX_TYPES),
			"taxes/item_tax_types.php?", 'SA_ITEMTAXTYPE', MENU_MAINTENANCE);
		$this->add_rapp_function(0, _(UI_TEXT_SYSTEM_GL_SETUP),
			"admin/gl_setup.php?", 'SA_GLSETUP', MENU_SETTINGS);
		$this->add_rapp_function(0, _(UI_TEXT_FISCAL_YEARS),
			"admin/fiscalyears.php?", 'SA_FISCALYEARS', MENU_MAINTENANCE);
		$this->add_rapp_function(0, _(UI_TEXT_PRINT_PROFILES),
			"admin/print_profiles.php?", 'SA_PRINTPROFILE', MENU_MAINTENANCE);

		$this->add_module(_(UI_TEXT_MISCELLANEOUS));
		$this->add_lapp_function(1, _(UI_TEXT_PAYMENT_TERMS),
			"admin/payment_terms.php?", 'SA_PAYTERMS', MENU_MAINTENANCE);
		$this->add_lapp_function(1, _(UI_TEXT_SHIPPING_COMPANY),
			"admin/shipping_companies.php?", 'SA_SHIPPING', MENU_MAINTENANCE);
		$this->add_rapp_function(1, _(UI_TEXT_POINTS_OF_SALE),
			"sales/manage/sales_points.php?", 'SA_POSSETUP', MENU_MAINTENANCE);
		$this->add_rapp_function(1, _(UI_TEXT_PRINTERS),
			"admin/printers.php?", 'SA_PRINTERS', MENU_MAINTENANCE);
		$this->add_rapp_function(1, _(UI_TEXT_CONTACT_CATEGORIES),
			"admin/crm_categories.php?", 'SA_CRMCATEGORY', MENU_MAINTENANCE);

		$this->add_module(_(UI_TEXT_MAINTENANCE));
		$this->add_lapp_function(2, _(UI_TEXT_VOID_TRANSACTION),
			"admin/void_transaction.php?", 'SA_VOIDTRANSACTION', MENU_MAINTENANCE);
		$this->add_lapp_function(2, _(UI_TEXT_VIEW_PRINT_TRANSACTIONS),
			"admin/view_print_transaction.php?", 'SA_VIEWPRINTTRANSACTION', MENU_MAINTENANCE);
		$this->add_lapp_function(2, _(UI_TEXT_ATTACH_DOCUMENTS),
			"admin/attachments.php?filterType=20", 'SA_ATTACHDOCUMENT', MENU_MAINTENANCE);
		$this->add_lapp_function(2, _(UI_TEXT_SYSTEM_DIAGNOSTICS),
			"admin/system_diagnostics.php?", 'SA_SOFTWAREUPGRADE', MENU_SYSTEM);

		$this->add_rapp_function(2, _(UI_TEXT_BACKUP_RESTORE),
			"admin/backups.php?", 'SA_BACKUP', MENU_SYSTEM);
		$this->add_rapp_function(2, _(UI_TEXT_CREATE_UPDATE_COMPANIES),
			"admin/create_coy.php?", 'SA_CREATECOMPANY', MENU_UPDATE);
		$this->add_rapp_function(2, _(UI_TEXT_INSTALL_UPDATE_LANGUAGES),
			"admin/inst_lang.php?", 'SA_CREATELANGUAGE', MENU_UPDATE);
		$this->add_rapp_function(2, _(UI_TEXT_INSTALL_ACTIVATE_EXTENSIONS),
			"admin/inst_module.php?", 'SA_CREATEMODULES', MENU_UPDATE);
		$this->add_rapp_function(2, _(UI_TEXT_INSTALL_ACTIVATE_THEMES),
			"admin/inst_theme.php?", 'SA_CREATEMODULES', MENU_UPDATE);
		$this->add_rapp_function(2, _(UI_TEXT_INSTALL_ACTIVATE_CHART_ACCOUNTS),
			"admin/inst_chart.php?", 'SA_CREATEMODULES', MENU_UPDATE);
		$this->add_rapp_function(2, _(UI_TEXT_SOFTWARE_UPGRADE),
			"admin/inst_upgrade.php?", 'SA_SOFTWAREUPGRADE', MENU_UPDATE);

		$this->add_extensions();
	}
}


