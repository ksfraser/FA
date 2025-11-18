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

class customers_app extends application 
{
	function __construct() 
	{
		parent::__construct("orders", _($this->help_context = _(UI_TEXT_SALES)));
	
		$this->add_module(_(UI_TEXT_TRANSACTIONS));
		$this->add_lapp_function(0, _(UI_TEXT_SALES_QUOTATION_ENTRY),
			"sales/sales_order_entry.php?NewQuotation=Yes", 'SA_SALESQUOTE', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_SALES_ORDER_ENTRY),
			"sales/sales_order_entry.php?NewOrder=Yes", 'SA_SALESORDER', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_DIRECT_DELIVERY),
			"sales/sales_order_entry.php?NewDelivery=0", 'SA_SALESDELIVERY', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_DIRECT_INVOICE),
			"sales/sales_order_entry.php?NewInvoice=0", 'SA_SALESINVOICE', MENU_TRANSACTION);
		$this->add_lapp_function(0, "","");
		$this->add_lapp_function(0, _(UI_TEXT_DELIVERY_AGAINST_SALES_ORDERS),
			"sales/inquiry/sales_orders_view.php?OutstandingOnly=1", 'SA_SALESDELIVERY', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_INVOICE_AGAINST_SALES_DELIVERY),
			"sales/inquiry/sales_deliveries_view.php?OutstandingOnly=1", 'SA_SALESINVOICE', MENU_TRANSACTION);

		$this->add_rapp_function(0, _(UI_TEXT_TEMPLATE_DELIVERY),
			"sales/inquiry/sales_orders_view.php?DeliveryTemplates=Yes", 'SA_SALESDELIVERY', MENU_TRANSACTION);
		$this->add_rapp_function(0, _(UI_TEXT_TEMPLATE_INVOICE),
			"sales/inquiry/sales_orders_view.php?InvoiceTemplates=Yes", 'SA_SALESINVOICE', MENU_TRANSACTION);
		$this->add_rapp_function(0, _(UI_TEXT_CREATE_PRINT_RECURRENT_INVOICES),
			"sales/create_recurrent_invoices.php?", 'SA_SALESINVOICE', MENU_TRANSACTION);
		$this->add_rapp_function(0, "","");
		$this->add_rapp_function(0, _(UI_TEXT_CUSTOMER_PAYMENTS),
			"sales/customer_payments.php?", 'SA_SALESPAYMNT', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_INVOICE_PREPAID_ORDERS),
			"sales/inquiry/sales_orders_view.php?PrepaidOrders=Yes", 'SA_SALESINVOICE', MENU_TRANSACTION);
		$this->add_rapp_function(0, _(UI_TEXT_CUSTOMER_CREDIT_NOTES),
			"sales/credit_note_entry.php?NewCredit=Yes", 'SA_SALESCREDIT', MENU_TRANSACTION);
		$this->add_rapp_function(0, _(UI_TEXT_ALLOCATE_CUSTOMER_PAYMENTS_CREDIT_NOTES),
			"sales/allocations/customer_allocation_main.php?", 'SA_SALESALLOC', MENU_TRANSACTION);

		$this->add_module(_(UI_TEXT_INQUIRIES_AND_REPORTS));
		$this->add_lapp_function(1, _(UI_TEXT_SALES_QUOTATION_INQUIRY),
			"sales/inquiry/sales_orders_view.php?type=32", 'SA_SALESTRANSVIEW', MENU_INQUIRY);
		$this->add_lapp_function(1, _(UI_TEXT_SALES_ORDER_INQUIRY),
			"sales/inquiry/sales_orders_view.php?type=30", 'SA_SALESTRANSVIEW', MENU_INQUIRY);
		$this->add_lapp_function(1, _(UI_TEXT_CUSTOMER_TRANSACTION_INQUIRY),
			"sales/inquiry/customer_inquiry.php?", 'SA_SALESTRANSVIEW', MENU_INQUIRY);
		$this->add_lapp_function(1, _(UI_TEXT_CUSTOMER_ALLOCATION_INQUIRY),
			"sales/inquiry/customer_allocation_inquiry.php?", 'SA_SALESALLOC', MENU_INQUIRY);

		$this->add_rapp_function(1, _(UI_TEXT_CUSTOMER_SALES_REPORTS),
			"reporting/reports_main.php?Class=0", 'SA_SALESTRANSVIEW', MENU_REPORT);

		$this->add_module(_(UI_TEXT_MAINTENANCE));
		$this->add_lapp_function(2, _(UI_TEXT_ADD_MANAGE_CUSTOMERS),
			"sales/manage/customers.php?", 'SA_CUSTOMER', MENU_ENTRY);
		$this->add_lapp_function(2, _(UI_TEXT_CUSTOMER_BRANCHES),
			"sales/manage/customer_branches.php?", 'SA_CUSTOMER', MENU_ENTRY);
		$this->add_lapp_function(2, _(UI_TEXT_SALES_GROUPS),
			"sales/manage/sales_groups.php?", 'SA_SALESGROUP', MENU_MAINTENANCE);
		$this->add_lapp_function(2, _(UI_TEXT_RECURRENT_INVOICES),
			"sales/manage/recurrent_invoices.php?", 'SA_SRECURRENT', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_SALES_TYPES),
			"sales/manage/sales_types.php?", 'SA_SALESTYPES', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_SALES_PERSONS),
			"sales/manage/sales_people.php?", 'SA_SALESMAN', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_SALES_AREAS),
			"sales/manage/sales_areas.php?", 'SA_SALESAREA', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_CREDIT_STATUS_SETUP),
			"sales/manage/credit_status.php?", 'SA_CRSTATUS', MENU_MAINTENANCE);

		$this->add_extensions();
	}
}


