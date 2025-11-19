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
$page_security = 'SA_SALESALLOC';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/includes/ui_strings.php");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
page(_($help_context = "Customer Allocations"), false, false, "", $js);

//--------------------------------------------------------------------------------

start_form();
/* show all outstanding receipts and credits to be allocated */

if (!isset($_POST['customer_id']))
	$_POST['customer_id'] = get_global_customer();

echo "<center>" . _(UI_TEXT_SELECT_A_CUSTOMER_LABEL) . "&nbsp;&nbsp;";
echo customer_list('customer_id', $_POST['customer_id'], true, true);
echo "<br>";
check(_(UI_TEXT_SHOW_SETTLED_ITEMS), 'ShowSettled', null, true);
echo "</center><br><br>";

set_global_customer($_POST['customer_id']);

if (isset($_POST['customer_id']) && ($_POST['customer_id'] == ALL_TEXT))
{
	unset($_POST['customer_id']);
}

$settled = false;
if (RequestService::checkValueStatic('ShowSettled'))
	$settled = true;

$customer_id = null;
if (isset($_POST['customer_id']))
	$customer_id = $_POST['customer_id'];

//--------------------------------------------------------------------------------
function systype_name($dummy, $type)
{
	global $systypes_array;

	return $systypes_array[$type];
}

function trans_view($trans)
{
	return get_trans_view_str($trans["type"], $trans["trans_no"]);
}

function alloc_link($row)
{
	return pager_link(_(UI_TEXT_ALLOCATE),
		"/sales/allocations/customer_allocate.php?trans_no="
			.$row["trans_no"] . "&trans_type=" . $row["type"]. "&debtor_no=" . $row["debtor_no"], ICON_ALLOC);
}

function amount_total($row)
{
	return FormatService::priceFormat($row['type'] == ST_JOURNAL && $row["Total"] < 0 ? -$row["Total"] : $row["Total"]);
}

function amount_left($row)
{
	return FormatService::priceFormat(($row['type'] == ST_JOURNAL && $row["Total"] < 0 ? -$row["Total"] : $row["Total"])-$row["alloc"]);
}

function check_settled($row)
{
	return $row['settled'] == 1;
}


$sql = get_allocatable_from_cust_sql($customer_id, $settled);

$cols = array(
	_(UI_TEXT_TRANSACTION_TYPE) => array('fun'=>'systype_name'),
	_(UI_TEXT_NUMBER) => array('fun'=>'trans_view', 'align'=>'right'),
	_(UI_TEXT_REFERENCE), 
	_(UI_TEXT_DATE) => array('name'=>'tran_date', 'type'=>'date', 'ord'=>'asc'),
	_(UI_TEXT_CUSTOMER) => array('ord'=>''),
	_(UI_TEXT_CURRENCY) => array('align'=>'center'),
	_(UI_TEXT_TOTAL) => array('align'=>'right','fun'=>'amount_total'), 
	_(UI_TEXT_LEFT_TO_ALLOCATE) => array('align'=>'right','insert'=>true, 'fun'=>'amount_left'), 
	array('insert'=>true, 'fun'=>'alloc_link')
	);

if (isset($_POST['customer_id'])) {
	$cols[_(UI_TEXT_CUSTOMER)] = 'skip';
	$cols[_(UI_TEXT_CURRENCY)] = 'skip';
}

$table =& new_db_pager('alloc_tbl', $sql, $cols);
$table->set_marker('check_settled', _(UI_TEXT_MARKED_ITEMS_ARE_SETTLED), 'settledbg', 'settledfg');

$table->width = "75%";

display_db_pager($table);
end_form();

end_page();
