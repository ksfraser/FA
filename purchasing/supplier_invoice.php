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
$page_security = 'SA_SUPPLIERINVOICE';
$path_to_root = "..";

include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/includes/ui_strings.php");

include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
use FA\Services\DateService;
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
//----------------------------------------------------------------------------------------

if (isset($_GET['New']))
{
	if (isset( $_SESSION['supp_trans']))
	{
		unset ($_SESSION['supp_trans']->grn_items);
		unset ($_SESSION['supp_trans']->gl_codes);
		unset ($_SESSION['supp_trans']);
	}
	$help_context = "Enter Supplier Invoice";
	$_SESSION['page_title'] = _(UI_TEXT_ENTER_SUPPLIER_INVOICE);

	$_SESSION['supp_trans'] = new supp_trans(ST_SUPPINVOICE);
} else if(isset($_GET['ModifyInvoice'])) {
	$help_context = 'Modifying Purchase Invoice';
	$_SESSION['page_title'] = sprintf( _(UI_TEXT_MODIFYING_PURCHASE_INVOICE), $_GET['ModifyInvoice']);
	$_SESSION['supp_trans'] = new supp_trans(ST_SUPPINVOICE, $_GET['ModifyInvoice']);
}

page($_SESSION['page_title'], false, false, "", $js);

if (isset($_GET['ModifyInvoice']))
	check_is_editable(ST_SUPPINVOICE, $_GET['ModifyInvoice']);

check_db_has_suppliers(_(UI_TEXT_NO_SUPPLIERS_DEFINED));

//---------------------------------------------------------------------------------------------------------------

if (isset($_GET['AddedID'])) 
{
	$invoice_no = $_GET['AddedID'];
	$trans_type = ST_SUPPINVOICE;


    echo "<center>";
    display_notification_centered(_(UI_TEXT_SUPPLIER_INVOICE_PROCESSED));
    display_note(get_trans_view_str($trans_type, $invoice_no, _(UI_TEXT_VIEW_THIS_INVOICE)));

	display_note(get_gl_view_str($trans_type, $invoice_no, _(UI_TEXT_VIEW_GL_JOURNAL_ENTRIES_FOR_THIS_INVOICE)), 1);

	hyperlink_params("$path_to_root/purchasing/supplier_payment.php", _(UI_TEXT_ENTRY_SUPPLIER_PAYMENT_FOR_THIS_INVOICE),
		"PInvoice=".$invoice_no."&trans_type=".$trans_type);

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_INVOICE), "New=1");

	hyperlink_params("$path_to_root/admin/attachments.php", _(UI_TEXT_ADD_AN_ATTACHMENT), "filterType=$trans_type&trans_no=$invoice_no");
	
	display_footer_exit();
}

//--------------------------------------------------------------------------------------------------

if (isset($_GET['New']))
{
	if (isset( $_SESSION['supp_trans']))
	{
		unset ($_SESSION['supp_trans']->grn_items);
		unset ($_SESSION['supp_trans']->gl_codes);
		unset ($_SESSION['supp_trans']);
	}

	$_SESSION['supp_trans'] = new supp_trans(ST_SUPPINVOICE);
} else if(isset($_GET['ModifyInvoice'])) {
	$_SESSION['supp_trans'] = new supp_trans(ST_SUPPINVOICE, $_GET['ModifyInvoice']);
}

//--------------------------------------------------------------------------------------------------
function clear_fields()
{
	global $Ajax;
	
	unset($_POST['gl_code']);
	unset($_POST['dimension_id']);
	unset($_POST['dimension2_id']);
	unset($_POST['amount']);
	unset($_POST['memo_']);
	unset($_POST['AddGLCodeToTrans']);
	$Ajax->activate('gl_items');
	set_focus('gl_code');
}

function reset_tax_input()
{
	global $Ajax;

	unset($_POST['mantax']);
	$Ajax->activate('inv_tot');
}

//------------------------------------------------------------------------------------------------
//	GL postings are often entered in the same form to two accounts
//  so fileds are cleared only on user demand.
//
if (isset($_POST['ClearFields']))
{
	clear_fields();
}

if (isset($_POST['AddGLCodeToTrans'])){

	$Ajax->activate('gl_items');
	$input_error = false;

	$result = get_gl_account_info($_POST['gl_code']);
	if (db_num_rows($result) == 0)
	{
		UiMessageService::displayError(_(UI_TEXT_INVALID_ACCOUNT_CODE_FOR_TRANSACTION));
		set_focus('gl_code');
		$input_error = true;
	}
	else
	{
		$myrow = db_fetch_row($result);
		$gl_act_name = $myrow[1];
		if (!check_num('amount'))
		{
			UiMessageService::displayError(_(UI_TEXT_NON_NUMERIC_AMOUNT_FOR_TRANSACTION));
			set_focus('amount');
			$input_error = true;
		}
	}

	if (!is_tax_gl_unique(RequestService::getPostStatic('gl_code'))) {
   		UiMessageService::displayError(_(UI_TEXT_CANNOT_POST_TO_GL_ACCOUNT_USED_BY_MULTIPLE_TAX_TYPES));
		set_focus('gl_code');
   		$input_error = true;
	}

	if ($input_error == false)
	{
		$_SESSION['supp_trans']->add_gl_codes_to_trans($_POST['gl_code'], $gl_act_name,
			$_POST['dimension_id'], $_POST['dimension2_id'], 
			RequestService::inputNumStatic('amount'), $_POST['memo_']);
		reset_tax_input();
		set_focus('gl_code');
	}
}

//------------------------------------------------------------------------------------------------

function check_data()
{
	global $Refs;

	if (!RequestService::getPostStatic('supplier_id')) 
	{
		UiMessageService::displayError(_(UI_TEXT_NO_SUPPLIER_SELECTED));
		set_focus('supplier_id');
		return false;
	} 

	if (!$_SESSION['supp_trans']->is_valid_trans_to_post())
	{
		UiMessageService::displayError(_(UI_TEXT_INVOICE_NO_ITEMS_OR_VALUES));
		return false;
	}

	if (!check_reference($_SESSION['supp_trans']->reference, ST_SUPPINVOICE, $_SESSION['supp_trans']->trans_no))
	{
		set_focus('reference');
		return false;
	}
	$dateService = new DateService();

	if (!$dateService->isDate( $_SESSION['supp_trans']->tran_date))
	{
		UiMessageService::displayError(_(UI_TEXT_INVALID_INVOICE_DATE_FORMAT));
		set_focus('trans_date');
		return false;
	} 
	elseif (!DateService::isDateInFiscalYear($_SESSION['supp_trans']->tran_date)) 
	{
		UiMessageService::displayError(_(UI_TEXT_DATE_OUT_OF_FISCAL_YEAR_OR_CLOSED));
		set_focus('trans_date');
		return false;
	}
	if (!$dateService->isDate( $_SESSION['supp_trans']->due_date))
	{
		UiMessageService::displayError(_(UI_TEXT_INVALID_DUE_DATE_FORMAT_FOR_INVOICE));
		set_focus('due_date');
		return false;
	}

	if (trim(RequestService::getPostStatic('supp_reference')) == false)
	{
		UiMessageService::displayError(_(UI_TEXT_MUST_ENTER_SUPPLIER_INVOICE_REFERENCE));
		set_focus('supp_reference');
		return false;
	}

	if (is_reference_already_there($_SESSION['supp_trans']->supplier_id, $_POST['supp_reference'], $_SESSION['supp_trans']->trans_no))
	{ 	/*Transaction reference already entered */
		UiMessageService::displayError(_(UI_TEXT_INVOICE_NUMBER_ALREADY_ENTERED) . " (" . $_POST['supp_reference'] . ")");
		set_focus('supp_reference');
		return false;
	}

	return true;
}

//--------------------------------------------------------------------------------------------------

function handle_commit_invoice()
{
	copy_to_trans($_SESSION['supp_trans']);

	if (!check_data())
		return;
	$inv = $_SESSION['supp_trans'];
	$invoice_no = add_supp_invoice($inv);

    $_SESSION['supp_trans']->clear_items();
    unset($_SESSION['supp_trans']);

	meta_forward($_SERVER['PHP_SELF'], "AddedID=$invoice_no");
}

//--------------------------------------------------------------------------------------------------

if (isset($_POST['PostInvoice']))
{
	handle_commit_invoice();
}

function check_item_data($n)
{
	global $SysPrefs;

	if (!check_num('this_quantity_inv'.$n, 0) || RequestService::inputNumStatic('this_quantity_inv'.$n)==0)
	{
		UiMessageService::displayError( _(UI_TEXT_QUANTITY_TO_INVOICE_MUST_BE_NUMERIC_AND_GREATER_THAN_ZERO));
		set_focus('this_quantity_inv'.$n);
		return false;
	}

	if (!check_num('ChgPrice'.$n))
	{
		UiMessageService::displayError( _(UI_TEXT_PRICE_NOT_NUMERIC));
		set_focus('ChgPrice'.$n);
		return false;
	}

	$margin = $SysPrefs->over_charge_allowance();
	if ($SysPrefs->check_price_charged_vs_order_price == True)
	{
		if ($_POST['order_price'.$n]!=RequestService::inputNumStatic('ChgPrice'.$n)) {
		     if ($_POST['order_price'.$n]==0 ||
				RequestService::inputNumStatic('ChgPrice'.$n)/$_POST['order_price'.$n] >
			    (1 + ($margin/ 100)))
		    {
			UiMessageService::displayError(_(UI_TEXT_PRICE_BEING_INVOICED_MORE_THAN_PURCHASE_ORDER_PRICE) .
			_(UI_TEXT_OVER_CHARGE_PERCENTAGE_ALLOWANCE_IS) . $margin . "%");
			set_focus('ChgPrice'.$n);
			return false;
		    }
		}
	}

	if ($SysPrefs->check_qty_charged_vs_del_qty == true && ($_POST['qty_recd'.$n] != $_POST['prev_quantity_inv'.$n])
		&& !empty($_POST['prev_quantity_inv'.$n]))
	{
		if (RequestService::inputNumStatic('this_quantity_inv'.$n) / ($_POST['qty_recd'.$n] - $_POST['prev_quantity_inv'.$n]) >
			(1+ ($margin / 100)))
		{
			UiMessageService::displayError( _(UI_TEXT_QUANTITY_BEING_INVOICED_MORE_THAN_OUTSTANDING_QUANTITY)
			. _(UI_TEXT_OVER_CHARGE_PERCENTAGE_ALLOWANCE_IS) . $margin . "%");
			set_focus('this_quantity_inv'.$n);
			return false;
		}
	}

	return true;
}

function commit_item_data($n)
{
	if (check_item_data($n))
	{
		$_SESSION['supp_trans']->add_grn_to_trans($n, $_POST['po_detail_item'.$n],
			$_POST['item_code'.$n], $_POST['item_description'.$n], $_POST['qty_recd'.$n],
			$_POST['prev_quantity_inv'.$n], RequestService::inputNumStatic('this_quantity_inv'.$n),
			$_POST['order_price'.$n], RequestService::inputNumStatic('ChgPrice'.$n));
		reset_tax_input();
	}
}

//-----------------------------------------------------------------------------------------

$id = find_submit('grn_item_id');
if ($id != -1)
{
	commit_item_data($id);
}

if (isset($_POST['InvGRNAll']))
{
   	foreach($_POST as $postkey=>$postval )
    {
		if (strpos($postkey, "qty_recd") === 0)
		{
			$id = substr($postkey, strlen("qty_recd"));
			$id = (int)$id;
			commit_item_data($id);
		}
    }
}	

//--------------------------------------------------------------------------------------------------
$id3 = find_submit('Delete');
if ($id3 != -1)
{
	$_SESSION['supp_trans']->remove_grn_from_trans($id3);
	$Ajax->activate('grn_items');
	reset_tax_input();
}

$id4 = find_submit('Delete2');
if ($id4 != -1)
{
	$_SESSION['supp_trans']->remove_gl_codes_from_trans($id4);
	clear_fields();
	reset_tax_input();
	$Ajax->activate('gl_items');
}

$id5 = find_submit('Edit');
if ($id5 != -1)
{
    $_POST['gl_code'] = $_SESSION['supp_trans']->gl_codes[$id5]->gl_code;
    $_POST['dimension_id'] = $_SESSION['supp_trans']->gl_codes[$id5]->gl_dim;
    $_POST['dimension2_id'] = $_SESSION['supp_trans']->gl_codes[$id5]->gl_dim2;
    $_POST['amount'] = $_SESSION['supp_trans']->gl_codes[$id5]->amount;
    $_POST['memo_'] = $_SESSION['supp_trans']->gl_codes[$id5]->memo_;

       $_SESSION['supp_trans']->remove_gl_codes_from_trans($id5);
       reset_tax_input();
       $Ajax->activate('gl_items');
}

$id2 = -1;
if ($_SESSION["wa_current_user"]->can_access('SA_GRNDELETE'))
{
	$id2 = find_submit('void_item_id');
	if ($id2 != -1) 
	{
		remove_not_invoice_item($id2);
		display_notification(sprintf(_('All yet non-invoiced items on delivery line # %d has been removed.'), $id2));

	}
}

if (isset($_POST['go']))
{
	$Ajax->activate('gl_items');
	display_quick_entries($_SESSION['supp_trans'], $_POST['qid'], RequestService::inputNumStatic('totamount'), QE_SUPPINV);
	$_POST['totamount'] = FormatService::priceFormat(0); $Ajax->activate('totamount');
	reset_tax_input();
}

start_form();

invoice_header($_SESSION['supp_trans']);

if ($_POST['supplier_id']=='') 
		UiMessageService::displayError(_(UI_TEXT_NO_SUPPLIER_SELECTED));
else {
	display_grn_items($_SESSION['supp_trans'], 1);

	display_gl_items($_SESSION['supp_trans'], 1);

	div_start('inv_tot');
	invoice_totals($_SESSION['supp_trans']);
	div_end();

}

//-----------------------------------------------------------------------------------------

if ($id != -1 || $id2 != -1)
{
	$Ajax->activate('grn_items');
	$Ajax->activate('inv_tot');
}

if (RequestService::getPostStatic('AddGLCodeToTrans') || RequestService::getPostStatic('update'))
	$Ajax->activate('inv_tot');

br();
submit_center('PostInvoice', _(UI_TEXT_ENTER_INVOICE), true, '', 'default');
br();

end_form();

//--------------------------------------------------------------------------------------------------

end_page();
