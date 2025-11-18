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

class assets_app extends application
{
	function __construct()
	{
		parent::__construct("assets", _($this->help_context = _(UI_TEXT_FIXED_ASSETS)));
			
		$this->add_module(_(UI_TEXT_TRANSACTIONS));
		$this->add_lapp_function(0, _(UI_TEXT_FIXED_ASSETS_PURCHASE),
			"purchasing/po_entry_items.php?NewInvoice=Yes&FixedAsset=1", 'SA_SUPPLIERINVOICE', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_FIXED_ASSETS_LOCATION_TRANSFERS),
			"inventory/transfers.php?NewTransfer=1&FixedAsset=1", 'SA_ASSETTRANSFER', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_FIXED_ASSETS_DISPOSAL),
			"inventory/adjustments.php?NewAdjustment=1&FixedAsset=1", 'SA_ASSETDISPOSAL', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_FIXED_ASSETS_SALE),
			"sales/sales_order_entry.php?NewInvoice=0&FixedAsset=1", 'SA_SALESINVOICE', MENU_TRANSACTION);
		$this->add_rapp_function(0, _(UI_TEXT_PROCESS_DEPRECIATION),
			"fixed_assets/process_depreciation.php", 'SA_DEPRECIATION', MENU_MAINTENANCE);
    // TODO: needs work
		//$this->add_rapp_function(0, _("Fixed Assets &Revaluation"),
	//		"inventory/cost_update.php?FixedAsset=1", 'SA_STANDARDCOST', MENU_MAINTENANCE);

		$this->add_module(_(UI_TEXT_INQUIRIES_AND_REPORTS));
		$this->add_lapp_function(1, _(UI_TEXT_FIXED_ASSETS_MOVEMENTS),
			"inventory/inquiry/stock_movements.php?FixedAsset=1", 'SA_ASSETSTRANSVIEW', MENU_INQUIRY);
		$this->add_lapp_function(1, _(UI_TEXT_FIXED_ASSETS_INQUIRY),
			"fixed_assets/inquiry/stock_inquiry.php?", 'SA_ASSETSANALYTIC', MENU_INQUIRY);


		$this->add_rapp_function(1, _(UI_TEXT_FIXED_ASSETS_REPORTS),
			"reporting/reports_main.php?Class=7", 'SA_ASSETSANALYTIC', MENU_REPORT);

		$this->add_module(_(UI_TEXT_MAINTENANCE));
		
		$this->add_lapp_function(2, _(UI_TEXT_FIXED_ASSETS_MENU),
			"inventory/manage/items.php?FixedAsset=1", 'SA_ASSET', MENU_ENTRY);
		$this->add_rapp_function(2, _(UI_TEXT_FIXED_ASSETS_LOCATIONS),
			"inventory/manage/locations.php?FixedAsset=1", 'SA_INVENTORYLOCATION', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_FIXED_ASSETS_CATEGORIES),
			"inventory/manage/item_categories.php?FixedAsset=1", 'SA_ASSETCATEGORY', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_FIXED_ASSETS_CLASSES),
			"fixed_assets/fixed_asset_classes.php", 'SA_ASSETCLASS', MENU_MAINTENANCE);

		$this->add_extensions();
	}
}


?>
