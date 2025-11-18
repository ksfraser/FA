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

class inventory_app extends application
{
	function __construct()
	{
		parent::__construct("stock", _($this->help_context = _(UI_TEXT_ITEMS_AND_INVENTORY)));

		$this->add_module(_(UI_TEXT_TRANSACTIONS));
		$this->add_lapp_function(0, _(UI_TEXT_INVENTORY_LOCATION_TRANSFERS),
			"inventory/transfers.php?NewTransfer=1", 'SA_LOCATIONTRANSFER', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_INVENTORY_ADJUSTMENTS),
			"inventory/adjustments.php?NewAdjustment=1", 'SA_INVENTORYADJUSTMENT', MENU_TRANSACTION);

		$this->add_module(_(UI_TEXT_INQUIRIES_AND_REPORTS));
		$this->add_lapp_function(1, _(UI_TEXT_INVENTORY_ITEM_MOVEMENTS),
			"inventory/inquiry/stock_movements.php?", 'SA_ITEMSTRANSVIEW', MENU_INQUIRY);
		$this->add_lapp_function(1, _(UI_TEXT_INVENTORY_ITEM_STATUS),
			"inventory/inquiry/stock_status.php?", 'SA_ITEMSSTATVIEW', MENU_INQUIRY);
		$this->add_rapp_function(1, _(UI_TEXT_INVENTORY_REPORTS),
			"reporting/reports_main.php?Class=2", 'SA_ITEMSTRANSVIEW', MENU_REPORT);

		$this->add_module(_(UI_TEXT_MAINTENANCE));
		$this->add_lapp_function(2, _(UI_TEXT_ITEMS),
			"inventory/manage/items.php?", 'SA_ITEM', MENU_ENTRY);
		$this->add_lapp_function(2, _(UI_TEXT_FOREIGN_ITEM_CODES),
			"inventory/manage/item_codes.php?", 'SA_FORITEMCODE', MENU_MAINTENANCE);
		$this->add_lapp_function(2, _(UI_TEXT_SALES_KITS),
			"inventory/manage/sales_kits.php?", 'SA_SALESKIT', MENU_MAINTENANCE);
		$this->add_lapp_function(2, _(UI_TEXT_ITEM_CATEGORIES),
			"inventory/manage/item_categories.php?", 'SA_ITEMCATEGORY', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_INVENTORY_LOCATIONS),
			"inventory/manage/locations.php?", 'SA_INVENTORYLOCATION', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_UNITS_OF_MEASURE),
			"inventory/manage/item_units.php?", 'SA_UOM', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_REORDER_LEVELS),
			"inventory/reorder_level.php?", 'SA_REORDER', MENU_MAINTENANCE);

		$this->add_module(_(UI_TEXT_PRICING_AND_COSTS));
		$this->add_lapp_function(3, _(UI_TEXT_SALES_PRICING),
			"inventory/prices.php?", 'SA_SALESPRICE', MENU_MAINTENANCE);
		$this->add_lapp_function(3, _(UI_TEXT_PURCHASING_PRICING),
			"inventory/purchasing_data.php?", 'SA_PURCHASEPRICING', MENU_MAINTENANCE);
		$this->add_rapp_function(3, _(UI_TEXT_STANDARD_COSTS),
			"inventory/cost_update.php?", 'SA_STANDARDCOST', MENU_MAINTENANCE);

		$this->add_extensions();
	}
}


