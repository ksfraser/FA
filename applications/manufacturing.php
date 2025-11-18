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

class manufacturing_app extends application
{
	function __construct()
	{
		parent::__construct("manuf", _($this->help_context = _(UI_TEXT_MANUFACTURING)));

		$this->add_module(_(UI_TEXT_TRANSACTIONS));
		$this->add_lapp_function(0, _(UI_TEXT_WORK_ORDER_ENTRY),
			"manufacturing/work_order_entry.php?", 'SA_WORKORDERENTRY', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_OUTSTANDING_WORK_ORDERS),
			"manufacturing/search_work_orders.php?outstanding_only=1", 'SA_MANUFTRANSVIEW', MENU_TRANSACTION);

		$this->add_module(_(UI_TEXT_INQUIRIES_AND_REPORTS));
		$this->add_lapp_function(1, _(UI_TEXT_COSTED_BILL_OF_MATERIAL_INQUIRY),
			"manufacturing/inquiry/bom_cost_inquiry.php?", 'SA_WORKORDERCOST', MENU_INQUIRY);
		$this->add_lapp_function(1, _(UI_TEXT_INVENTORY_ITEM_WHERE_USED_INQUIRY),
			"manufacturing/inquiry/where_used_inquiry.php?", 'SA_WORKORDERANALYTIC', MENU_INQUIRY);
		$this->add_lapp_function(1, _(UI_TEXT_WORK_ORDER_INQUIRY),
			"manufacturing/search_work_orders.php?", 'SA_MANUFTRANSVIEW', MENU_INQUIRY);
		$this->add_rapp_function(1, _(UI_TEXT_MANUFACTURING_REPORTS),
			"reporting/reports_main.php?Class=3", 'SA_MANUFTRANSVIEW', MENU_REPORT);

		$this->add_module(_(UI_TEXT_MAINTENANCE));
		$this->add_lapp_function(2, _(UI_TEXT_BILLS_OF_MATERIAL),
			"manufacturing/manage/bom_edit.php?", 'SA_BOM', MENU_ENTRY);
		$this->add_lapp_function(2, _(UI_TEXT_WORK_CENTRES),
			"manufacturing/manage/work_centres.php?", 'SA_WORKCENTRES', MENU_MAINTENANCE);

		$this->add_extensions();
	}
}


