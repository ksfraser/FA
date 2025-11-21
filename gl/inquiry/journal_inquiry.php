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

$page_security = 'SA_GLANALYTIC';
$path_to_root="../..";

include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/CompanyPrefsService.php");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(_($help_context = UI_TEXT_JOURNAL_INQUIRY_TITLE), false, false, "", $js);

//-----------------------------------------------------------------------------------
// Ajax updates
//
if (RequestService::getPostStatic('Search'))
{
	$Ajax->activate('journal_tbl');
}
//--------------------------------------------------------------------------------------
if (!isset($_POST['filterType']))
	$_POST['filterType'] = -1;

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();

ref_cells(_(UI_TEXT_REFERENCE_LABEL), 'Ref', '',null, _(UI_TEXT_ENTER_REFERENCE_FRAGMENT));

journal_types_list_cells(_(UI_TEXT_TYPE_LABEL), "filterType");
date_cells(_(UI_TEXT_FROM_LABEL), 'FromDate', '', null, -user_transaction_days());
date_cells(_(UI_TEXT_TO_LABEL), 'ToDate');

end_row();
start_row();
ref_cells(_(UI_TEXT_MEMO_LABEL), 'Memo', '',null, _(UI_TEXT_ENTER_MEMO_FRAGMENT));
users_list_cells(_(UI_TEXT_USER_LABEL), 'userid', null, false);
if (\FA\Services\CompanyPrefsService::getUseDimensions() && isset($_POST['dimension'])) // display dimension only, when started in dimension mode
	dimensions_list_cells(_('Dimension:'), 'dimension', null, true, null, true);
check_cells( _(UI_TEXT_SHOW_CLOSED_LABEL), 'AlsoClosed', null);
submit_cells('Search', _(UI_TEXT_SEARCH_BUTTON), '', '', 'default');
end_row();
end_table();

function journal_pos($row)
{
	return $row['gl_seq'] ? $row['gl_seq'] : '-';
}

function systype_name($dummy, $type)
{
	global $systypes_array;
	
	return $systypes_array[$type];
}

function person_link($row) 
{
    return payment_person_name($row["person_type_id"],$row["person_id"]);
}

function view_link($row) 
{
	return get_trans_view_str($row["trans_type"], $row["trans_no"]);
}

function gl_link($row) 
{
	return get_gl_view_str($row["trans_type"], $row["trans_no"]);
}

function edit_link($row)
{

	$ok = true;
	if ($row['trans_type'] == ST_SALESINVOICE)
	{
		$myrow = get_customer_trans($row["trans_no"], $row["trans_type"]);
		if ($myrow['alloc'] != $myrow['Total'] || get_voided_entry(ST_SALESINVOICE, $row["trans_no"]) !== false)
			$ok = false;
	}
	
	return $ok ? trans_editor_link( $row["trans_type"], $row["trans_no"]) : '--';
}

function invoice_supp_reference($row)
{
	return $row['supp_reference'];
}

$sql = get_sql_for_journal_inquiry(RequestService::getPostStatic('filterType', -1), RequestService::getPostStatic('FromDate'),
	RequestService::getPostStatic('ToDate'), RequestService::getPostStatic('Ref'), RequestService::getPostStatic('Memo'), RequestService::checkValueStatic('AlsoClosed'), RequestService::getPostStatic('userid'));

$cols = array(
	_(UI_TEXT_NUMBER_SIGN) => array('fun'=>'journal_pos', 'align'=>'center'), 
	_(UI_TEXT_DATE) =>array('name'=>'tran_date','type'=>'date','ord'=>'desc'),
	_(UI_TEXT_TYPE_LABEL) => array('fun'=>'systype_name'), 
	_(UI_TEXT_TRANS_NUMBER) => array('fun'=>'view_link'), 
	_(UI_TEXT_COUNTERPARTY) => array('fun' => 'person_link'),
	_(UI_TEXT_SUPPLIERS_REFERENCE) => 'skip',
	_(UI_TEXT_REFERENCE_LABEL), 
	_(UI_TEXT_AMOUNT) => array('type'=>'amount'),
	_(UI_TEXT_MEMO_LABEL),
	_(UI_TEXT_USER_LABEL),
	_(UI_TEXT_VIEW) => array('insert'=>true, 'fun'=>'gl_link'),
	array('insert'=>true, 'fun'=>'edit_link')
);

if (!RequestService::checkValueStatic('AlsoClosed')) {
	$cols[_(UI_TEXT_NUMBER_SIGN)] = 'skip';
}

if($_POST['filterType'] == ST_SUPPINVOICE) //add the payment column if shown supplier invoices only
{
	$cols[_(UI_TEXT_SUPPLIERS_REFERENCE)] = array('fun'=>'invoice_supp_reference', 'align'=>'center');
}

$table =& new_db_pager('journal_tbl', $sql, $cols);

$table->width = "80%";

display_db_pager($table);

end_form();
end_page();

