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
$page_security = 'SA_SALESTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/includes/ui_strings.php");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = UI_TEXT_CUSTOMER_TRANSACTIONS), isset($_GET['customer_id']), false, "", $js);

//------------------------------------------------------------------------------------------------

function systype_name($dummy, $type)
{
	global $systypes_array;

	return $systypes_array[$type];
}

function order_view($row)
{
	return $row['order_']>0 ?
		get_customer_trans_view_str(ST_SALESORDER, $row['order_'])
		: "";
}

function trans_view($trans)
{
	return get_trans_view_str($trans["type"], $trans["trans_no"]);
}

function due_date($row)
{
	return	$row["type"] == ST_SALESINVOICE	? $row["due_date"] : '';
}

function gl_view($row)
{
	return get_gl_view_str($row["type"], $row["trans_no"]);
}

function fmt_amount($row)
{
	$value =
	    $row['type']==ST_CUSTCREDIT || $row['type']==ST_CUSTPAYMENT || $row['type']==ST_BANKDEPOSIT ? -$row["TotalAmount"] : $row["TotalAmount"];
    return FormatService::priceFormat($value);
}

function credit_link($row)
{
	global $page_nested;

	if ($page_nested)
		return '';
	if ($row["Outstanding"] > 0)
	{
		if ($row['type'] == ST_CUSTDELIVERY)
			return pager_link(_(UI_TEXT_INVOICE), "/sales/customer_invoice.php?DeliveryNumber=" 
				.$row['trans_no'], ICON_DOC);
		else if ($row['type'] == ST_SALESINVOICE)
			return pager_link(_(UI_TEXT_CREDIT_THIS) ,
			"/sales/customer_credit_invoice.php?InvoiceNumber=". $row['trans_no'], ICON_CREDIT);
	}	
}

function edit_link($row)
{
	global $page_nested;

	if ($page_nested)
		return '';

	return $row['type'] == ST_CUSTCREDIT && $row['order_'] ? '' : 	// allow  only free hand credit notes edition
			trans_editor_link($row['type'], $row['trans_no']);
}

function copy_link($row)
{
    global $page_nested;

    if ($page_nested)
        return '';
    if ($row['type'] == ST_CUSTDELIVERY)
        return pager_link(_(UI_TEXT_COPY_DELIVERY), "/sales/sales_order_entry.php?NewDelivery=" 
            .$row['order_'], ICON_DOC);
    elseif ($row['type'] == ST_SALESINVOICE)
        return pager_link(_(UI_TEXT_COPY_INVOICE),    "/sales/sales_order_entry.php?NewInvoice="
            . $row['order_'], ICON_DOC);
}

function prt_link($row)
{
  	if ($row['type'] == ST_CUSTPAYMENT || $row['type'] == ST_BANKDEPOSIT) 
		return print_document_link($row['trans_no']."-".$row['type'], _(UI_TEXT_PRINT_RECEIPT), true, ST_CUSTPAYMENT, ICON_PRINT);
  	elseif ($row['type'] == ST_BANKPAYMENT) // bank payment printout not defined yet.
		return '';
 	else
 		return print_document_link($row['trans_no']."-".$row['type'], _(UI_TEXT_PRINT), true, $row['type'], ICON_PRINT);
}

function check_overdue($row)
{
	return $row['OverDue'] == 1
		&& floatcmp(ABS($row["TotalAmount"]), $row["Allocated"]) != 0;
}
//------------------------------------------------------------------------------------------------

function display_customer_summary($customer_record)
{
	$past1 = get_company_pref('past_due_days');
	$past2 = 2 * $past1;
    if ($customer_record && $customer_record["dissallow_invoices"] != 0)
    {
    	echo "<center><font color=red size=4><b>" . _(UI_TEXT_CUSTOMER_ACCOUNT_IS_ON_HOLD) . "</font></b></center>";
    }

	$nowdue = "1-" . $past1 . " " . _(UI_TEXT_DAYS);
	$pastdue1 = $past1 + 1 . "-" . $past2 . " " . _(UI_TEXT_DAYS);
	$pastdue2 = _(UI_TEXT_OVER) . " " . $past2 . " " . _(UI_TEXT_DAYS);

    start_table(TABLESTYLE, "width='80%'");
    $th = array(_(UI_TEXT_CURRENCY), _(UI_TEXT_TERMS), _(UI_TEXT_CURRENT), $nowdue,
    	$pastdue1, $pastdue2, _(UI_TEXT_TOTAL_BALANCE));
    table_header($th);
    if ($customer_record != false)
    {
		start_row();
	    label_cell($customer_record["curr_code"]);
	    label_cell($customer_record["terms"]);
		amount_cell($customer_record["Balance"] - $customer_record["Due"]);
		amount_cell($customer_record["Due"] - $customer_record["Overdue1"]);
		amount_cell($customer_record["Overdue1"] - $customer_record["Overdue2"]);
		amount_cell($customer_record["Overdue2"]);
		amount_cell($customer_record["Balance"]);
		end_row();
	}

	end_table();
}

if (isset($_GET['customer_id']))
{
	$_POST['customer_id'] = $_GET['customer_id'];
}

//------------------------------------------------------------------------------------------------

start_form();

if (!isset($_POST['customer_id']))
	$_POST['customer_id'] = get_global_customer();

start_table(TABLESTYLE_NOBORDER);
start_row();

ref_cells(_(UI_TEXT_REFERENCE_LABEL), 'Ref', '', NULL, _(UI_TEXT_ENTER_REFERENCE_FRAGMENT));

if (!$page_nested)
	customer_list_cells(_(UI_TEXT_SELECT_A_CUSTOMER_LABEL), 'customer_id', null, true, true, false, true);

cust_allocations_list_cells(null, 'filterType', null, true, true);

if ($_POST['filterType'] != '2')
{
	date_cells(_(UI_TEXT_FROM_LABEL), 'TransAfterDate', '', null, -user_transaction_days());
	date_cells(_(UI_TEXT_TO_LABEL), 'TransToDate', '', null);
}
check_cells(_(UI_TEXT_ZERO_VALUES), 'show_voided');

submit_cells('RefreshInquiry', _(UI_TEXT_SEARCH),'',_(UI_TEXT_REFRESH_INQUIRY), 'default');
end_row();
end_table();

set_global_customer($_POST['customer_id']);

//------------------------------------------------------------------------------------------------

div_start('totals_tbl');
if ($_POST['customer_id'] != "" && $_POST['customer_id'] != ALL_TEXT)
{
	$customer_record = get_customer_details(RequestService::getPostStatic('customer_id'), RequestService::getPostStatic('TransToDate'), false);
    display_customer_summary($customer_record);
    echo "<br>";
}
div_end();

if (RequestService::getPostStatic('RefreshInquiry') || list_updated('filterType'))
{
	$Ajax->activate('_page_body');
}
//------------------------------------------------------------------------------------------------
$sql = get_sql_for_customer_inquiry(RequestService::getPostStatic('TransAfterDate'), RequestService::getPostStatic('TransToDate'),
	RequestService::getPostStatic('customer_id'), RequestService::getPostStatic('filterType'), RequestService::checkValueStatic('show_voided'), RequestService::getPostStatic('Ref'));

//------------------------------------------------------------------------------------------------
//db_query("set @bal:=0");

$cols = array(
	_(UI_TEXT_TYPE) => array('fun'=>'systype_name', 'ord'=>''),
	_(UI_TEXT_NUMBER) => array('fun'=>'trans_view', 'ord'=>'', 'align'=>'right'),
	_(UI_TEXT_ORDER) => array('fun'=>'order_view', 'align'=>'right'), 
	_(UI_TEXT_REFERENCE), 
	_(UI_TEXT_DATE) => array('name'=>'tran_date', 'type'=>'date', 'ord'=>'desc'),
	_(UI_TEXT_DUE_DATE) => array('type'=>'date', 'fun'=>'due_date'),
	_(UI_TEXT_CUSTOMER) => array('ord'=>''), 
	_(UI_TEXT_BRANCH) => array('ord'=>''), 
	_(UI_TEXT_CURRENCY) => array('align'=>'center'),
	_(UI_TEXT_AMOUNT) => array('align'=>'right', 'fun'=>'fmt_amount'), 
	_(UI_TEXT_BALANCE) => array('align'=>'right', 'type'=>'amount'),
		array('insert'=>true, 'fun'=>'gl_view'),
		array('insert'=>true, 'fun'=>'edit_link'),
		array('insert'=>true, 'fun'=>'copy_link'),
		array('insert'=>true, 'fun'=>'credit_link'),
		array('insert'=>true, 'fun'=>'prt_link')
	);


if ($_POST['customer_id'] != ALL_TEXT) {
	$cols[_(UI_TEXT_CUSTOMER)] = 'skip';
	$cols[_(UI_TEXT_CURRENCY)] = 'skip';
}
if ($_POST['filterType'] != '2')
	$cols[_(UI_TEXT_BALANCE)] = 'skip';

$table =& new_db_pager('trans_tbl', $sql, $cols);
$table->set_marker('check_overdue', _(UI_TEXT_MARKED_ITEMS_ARE_OVERDUE));

$table->width = "85%";

display_db_pager($table);

end_form();
end_page();
