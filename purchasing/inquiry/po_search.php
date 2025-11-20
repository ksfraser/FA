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
$page_security = 'SA_SUPPTRANSVIEW';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include($path_to_root . "/includes/session.inc");

include($path_to_root . "/purchasing/includes/purchasing_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/includes/ui_strings.php");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = "Search Outstanding Purchase Orders"), false, false, "", $js);

if (isset($_GET['order_number']))
{
	$_POST['order_number'] = $_GET['order_number'];
}
//-----------------------------------------------------------------------------------
// Ajax updates
//
if (RequestService::getPostStatic('SearchOrders')) 
{
	$Ajax->activate('orders_tbl');
} elseif (RequestService::getPostStatic('_order_number_changed')) 
{
	$disable = RequestService::getPostStatic('order_number') !== '';

	$Ajax->addDisable(true, 'OrdersAfterDate', $disable);
	$Ajax->addDisable(true, 'OrdersToDate', $disable);
	$Ajax->addDisable(true, 'StockLocation', $disable);
	$Ajax->addDisable(true, '_SelectStockFromList_edit', $disable);
	$Ajax->addDisable(true, 'SelectStockFromList', $disable);

	if ($disable) {
		$Ajax->addFocus(true, 'order_number');
	} else
		$Ajax->addFocus(true, 'OrdersAfterDate');

	$Ajax->activate('orders_tbl');
}


//---------------------------------------------------------------------------------------------

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();
ref_cells(_(UI_TEXT_NUMBER), 'order_number', '',null, '', true);

date_cells(_(UI_TEXT_FROM_LABEL), 'OrdersAfterDate', '', null, -user_transaction_days());
date_cells(_(UI_TEXT_TO_LABEL), 'OrdersToDate');

locations_list_cells(_(UI_TEXT_LOCATION), 'StockLocation', null, true);
end_row();
end_table();

start_table(TABLESTYLE_NOBORDER);
start_row();

stock_items_list_cells(_(UI_TEXT_ITEM_LABEL), 'SelectStockFromList', null, true);

supplier_list_cells(_(UI_TEXT_SELECT_A_SUPPLIER_LABEL), 'supplier_id', null, true, true);

submit_cells('SearchOrders', _(UI_TEXT_SEARCH),'',_(UI_TEXT_SELECT_DOCUMENTS), 'default');
end_row();
end_table(1);
//---------------------------------------------------------------------------------------------
function trans_view($trans)
{
	return get_trans_view_str(ST_PURCHORDER, $trans["order_no"]);
}

function edit_link($row) 
{
	return trans_editor_link(ST_PURCHORDER, $row["order_no"]);
}

function prt_link($row)
{
	return print_document_link($row['order_no'], _(UI_TEXT_PRINT), true, ST_PURCHORDER, ICON_PRINT);
}

function receive_link($row) 
{
  return pager_link( _(UI_TEXT_RECEIVE),
	"/purchasing/po_receive_items.php?PONumber=" . $row["order_no"], ICON_RECEIVE);
}

function check_overdue($row)
{
	return $row['OverDue']==1;
}
//---------------------------------------------------------------------------------------------

//figure out the sql required from the inputs available
$sql = get_sql_for_po_search(RequestService::getPostStatic('OrdersAfterDate'), RequestService::getPostStatic('OrdersToDate'), RequestService::getPostStatic('supplier_id'), RequestService::getPostStatic('StockLocation'),
	$_POST['order_number'], RequestService::getPostStatic('SelectStockFromList'));

//$result = db_query($sql,"No orders were returned");

/*show a table of the orders returned by the sql */
$cols = array(
		_(UI_TEXT_NUMBER) => array('fun'=>'trans_view', 'ord'=>''), 
		_(UI_TEXT_REFERENCE), 
		_(UI_TEXT_SUPPLIER) => array('ord'=>''),
		_(UI_TEXT_LOCATION),
		_(UI_TEXT_SUPPLIERS_REFERENCE), 
		_(UI_TEXT_ORDER_DATE) => array('name'=>'ord_date', 'type'=>'date', 'ord'=>'desc'),
		_(UI_TEXT_CURRENCY) => array('align'=>'center'), 
		_(UI_TEXT_ORDER_TOTAL) => 'amount',
		array('insert'=>true, 'fun'=>'edit_link'),
		array('insert'=>true, 'fun'=>'receive_link'),
		array('insert'=>true, 'fun'=>'prt_link')
);

if (RequestService::getPostStatic('StockLocation') != ALL_TEXT) {
	$cols[_(UI_TEXT_LOCATION)] = 'skip';
}

$table =& new_db_pager('orders_tbl', $sql, $cols);
$table->set_marker('check_overdue', _(UI_TEXT_MARKED_ORDERS_HAVE_OVERDUE_ITEMS));

$table->width = "80%";

display_db_pager($table);

end_form();
end_page();
