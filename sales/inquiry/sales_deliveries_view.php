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
$page_security = 'SA_SALESINVOICE';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include($path_to_root . "/includes/session.inc");

include($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/includes/ui_strings.php");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 600);
if (user_use_date_picker())
	$js .= get_js_date_picker();

if (isset($_GET['OutstandingOnly']) && ($_GET['OutstandingOnly'] == true))
{
	$_POST['OutstandingOnly'] = true;
	page(_($help_context = "Search Not Invoiced Deliveries"), false, false, "", $js);
}
else
{
	$_POST['OutstandingOnly'] = false;
	page(_($help_context = "Search All Deliveries"), false, false, "", $js);
}

if (isset($_GET['selected_customer']))
{
	$_POST['customer_id'] = $_GET['selected_customer'];
}
elseif (isset($_POST['selected_customer']))
{
	$_POST['customer_id'] = $_POST['selected_customer'];
}

if (isset($_POST['BatchInvoice']))
{
	// checking batch integrity
    $del_count = 0;
    if (isset($_POST['Sel_'])) {
		foreach($_POST['Sel_'] as $delivery => $branch) {
			$checkbox = 'Sel_'.$delivery;
			if (RequestService::checkValueStatic($checkbox))	{
				if (!$del_count) {
					$del_branch = $branch;
				}
				else {
					if ($del_branch != $branch)	{
						$del_count=0;
						break;
					}
				}
				$selected[] = $delivery;
				$del_count++;
			}
		}
	}
    if (!$del_count) {
		UiMessageService::displayError(_('For batch invoicing you should
		    select at least one delivery. All items must be dispatched to
		    the same customer branch.'));
    } else {
		$_SESSION['DeliveryBatch'] = $selected;
		meta_forward($path_to_root . '/sales/customer_invoice.php','BatchInvoice=Yes');
    }
}

//-----------------------------------------------------------------------------------
if (RequestService::getPostStatic('_DeliveryNumber_changed')) 
{
	$disable = RequestService::getPostStatic('DeliveryNumber') !== '';

	$Ajax->addDisable(true, 'DeliveryAfterDate', $disable);
	$Ajax->addDisable(true, 'DeliveryToDate', $disable);
	$Ajax->addDisable(true, 'StockLocation', $disable);
	$Ajax->addDisable(true, '_SelectStockFromList_edit', $disable);
	$Ajax->addDisable(true, 'SelectStockFromList', $disable);
	// if search is not empty rewrite table
	if ($disable) {
		$Ajax->addFocus(true, 'DeliveryNumber');
	} else
		$Ajax->addFocus(true, 'DeliveryAfterDate');
	$Ajax->activate('deliveries_tbl');
}

//-----------------------------------------------------------------------------------

start_form(false, false, $_SERVER['PHP_SELF'] ."?OutstandingOnly=".$_POST['OutstandingOnly']);

start_table(TABLESTYLE_NOBORDER);
start_row();
ref_cells(_(UI_TEXT_DELIVERY_NUMBER_LABEL), 'DeliveryNumber', '',null, '', true);
date_cells(_(UI_TEXT_FROM_LABEL), 'DeliveryAfterDate', '', null, -user_transaction_days());
date_cells(_(UI_TEXT_TO_LABEL), 'DeliveryToDate', '', null, 1);

locations_list_cells(_(UI_TEXT_LOCATION_LABEL), 'StockLocation', null, true);
end_row();

end_table();
start_table(TABLESTYLE_NOBORDER);
start_row();

stock_items_list_cells(_(UI_TEXT_ITEM_LABEL), 'SelectStockFromList', null, true);

customer_list_cells(_(UI_TEXT_SELECT_A_CUSTOMER_LABEL), 'customer_id', null, true, true);

submit_cells('SearchOrders', _(UI_TEXT_SEARCH),'',_(UI_TEXT_SELECT_DOCUMENTS), 'default');

hidden('OutstandingOnly', $_POST['OutstandingOnly']);

end_row();

end_table(1);
//---------------------------------------------------------------------------------------------

function trans_view($trans, $trans_no)
{
	return get_customer_trans_view_str(ST_CUSTDELIVERY, $trans['trans_no']);
}

function batch_checkbox($row)
{
	$name = "Sel_" .$row['trans_no'];
	return $row['Done'] ? '' :
		"<input type='checkbox' name='$name' value='1' >"
// add also trans_no => branch code for checking after 'Batch' submit
	 ."<input name='Sel_[".$row['trans_no']."]' type='hidden' value='"
	 .$row['branch_code']."'>\n";
}

function edit_link($row)
{
	return $row["Outstanding"]==0 ? '' :
		trans_editor_link(ST_CUSTDELIVERY, $row['trans_no']);
}

function prt_link($row)
{
	return print_document_link($row['trans_no'], _(UI_TEXT_PRINT), true, ST_CUSTDELIVERY, ICON_PRINT);
}

function invoice_link($row)
{
	return $row["Outstanding"]==0 ? '' :
		pager_link(_('Invoice'), "/sales/customer_invoice.php?DeliveryNumber=" 
			.$row['trans_no'], ICON_DOC);
}

function check_overdue($row)
{
   	return DateService::date1GreaterDate2Static(DateService::todayStatic(), DateService::sql2dateStatic($row["due_date"])) && 
			$row["Outstanding"]!=0;
}
//------------------------------------------------------------------------------------------------
$sql = get_sql_for_sales_deliveries_view(RequestService::getPostStatic('DeliveryAfterDate'), RequestService::getPostStatic('DeliveryToDate'), RequestService::getPostStatic('customer_id'),	
	RequestService::getPostStatic('SelectStockFromList'), RequestService::getPostStatic('StockLocation'), RequestService::getPostStatic('DeliveryNumber'), RequestService::getPostStatic('OutstandingOnly'));

$cols = array(
		_(UI_TEXT_DELIVERY_NUMBER) => array('fun'=>'trans_view', 'align'=>'right'), 
		_(UI_TEXT_CUSTOMER), 
		'branch_code' => 'skip',
		_(UI_TEXT_BRANCH) => array('ord'=>''), 
		_(UI_TEXT_CONTACT),
		_(UI_TEXT_REFERENCE), 
		_(UI_TEXT_CUST_REF), 
		_(UI_TEXT_DELIVERY_DATE) => array('type'=>'date', 'ord'=>''),
		_(UI_TEXT_DUE_BY) => 'date', 
		_(UI_TEXT_DELIVERY_TOTAL) => array('type'=>'amount', 'ord'=>''),
		_(UI_TEXT_CURRENCY) => array('align'=>'center'),
		submit('BatchInvoice',_(UI_TEXT_BATCH), false, _(UI_TEXT_BATCH_INVOICING)) 
			=> array('insert'=>true, 'fun'=>'batch_checkbox', 'align'=>'center'),
		array('insert'=>true, 'fun'=>'edit_link'),
		array('insert'=>true, 'fun'=>'invoice_link'),
		array('insert'=>true, 'fun'=>'prt_link')
);

//-----------------------------------------------------------------------------------
if (isset($_SESSION['Batch']))
{
    foreach($_SESSION['Batch'] as $trans=>$del)
    	unset($_SESSION['Batch'][$trans]);
    unset($_SESSION['Batch']);
}

$table =& new_db_pager('deliveries_tbl', $sql, $cols);
	$table->set_marker('check_overdue', _(UI_TEXT_MARKED_ITEMS_ARE_OVERDUE));//$table->width = "92%";

display_db_pager($table);

end_form();
end_page();

