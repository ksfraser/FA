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
$path_to_root = "..";
$page_security = 'SA_PURCHASEORDER';
include_once($path_to_root . "/purchasing/includes/po_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");
include_once($path_to_root . "/purchasing/includes/db/suppliers_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/CompanyPrefsService.php");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
use FA\Services\DateService;

set_page_security( @$_SESSION['PO']->trans_type,
	array(	ST_PURCHORDER => 'SA_PURCHASEORDER',
			ST_SUPPRECEIVE => 'SA_GRN',
			ST_SUPPINVOICE => 'SA_SUPPLIERINVOICE'),
	array(	'NewOrder' => 'SA_PURCHASEORDER',
			'ModifyOrderNumber' => 'SA_PURCHASEORDER',
			'AddedID' => 'SA_PURCHASEORDER',
			'NewGRN' => 'SA_GRN',
			'AddedGRN' => 'SA_GRN',
			'NewInvoice' => 'SA_SUPPLIERINVOICE',
			'AddedPI' => 'SA_SUPPLIERINVOICE')
);

$js = '';
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

if (isset($_GET['ModifyOrderNumber']) && is_numeric($_GET['ModifyOrderNumber'])) {

	$_SESSION['page_title'] = _($help_context = "Modify Purchase Order #") . $_GET['ModifyOrderNumber'];
	create_new_po(ST_PURCHORDER, $_GET['ModifyOrderNumber']);
	copy_from_cart();
} elseif (isset($_GET['NewOrder'])) {

	$_SESSION['page_title'] = _($help_context = "Purchase Order Entry");
	create_new_po(ST_PURCHORDER, 0);
	copy_from_cart();
} elseif (isset($_GET['NewGRN'])) {

	$_SESSION['page_title'] = _($help_context = "Direct GRN Entry");
	create_new_po(ST_SUPPRECEIVE, 0);
	copy_from_cart();
} elseif (isset($_GET['NewInvoice'])) {

	create_new_po(ST_SUPPINVOICE, 0);
	copy_from_cart();

	if (isset($_GET['FixedAsset'])) {
		$_SESSION['page_title'] = _($help_context = "Fixed Asset Purchase Invoice Entry");
		$_SESSION['PO']->fixed_asset = true;
	} else
		$_SESSION['page_title'] = _($help_context = "Direct Purchase Invoice Entry");
}

page($_SESSION['page_title'], false, false, "", $js);

if (isset($_GET['ModifyOrderNumber']))
	check_is_editable(ST_PURCHORDER, $_GET['ModifyOrderNumber']);

//---------------------------------------------------------------------------------------------------

check_db_has_suppliers(_(UI_TEXT_NO_SUPPLIERS_DEFINED_IN_SYSTEM));

//---------------------------------------------------------------------------------------------------------------

if (isset($_GET['AddedID'])) 
{
	$order_no = $_GET['AddedID'];
	$trans_type = ST_PURCHORDER;	

	if (!isset($_GET['Updated']))
		display_notification_centered(_(UI_TEXT_PURCHASE_ORDER_ENTERED));
	else
		display_notification_centered(_(UI_TEXT_PURCHASE_ORDER_UPDATED) . " #$order_no");
	display_note(get_trans_view_str($trans_type, $order_no, _(UI_TEXT_VIEW_THIS_ORDER)), 0, 1);

	display_note(print_document_link($order_no, _(UI_TEXT_PRINT_THIS_ORDER), true, $trans_type), 0, 1);

	display_note(print_document_link($order_no, _(UI_TEXT_EMAIL_THIS_ORDER), true, $trans_type, false, "printlink", "", 1));

	hyperlink_params($path_to_root . "/purchasing/po_receive_items.php", _(UI_TEXT_RECEIVE_ITEMS_ON_THIS_PURCHASE_ORDER), "PONumber=$order_no");

  // TODO, for fixed asset
	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_PURCHASE_ORDER), "NewOrder=yes");
	
	hyperlink_no_params($path_to_root."/purchasing/inquiry/po_search.php", _(UI_TEXT_SELECT_AN_OUTSTANDING_PURCHASE_ORDER));
	
	hyperlink_params("$path_to_root/admin/attachments.php", _(UI_TEXT_ADD_AN_ATTACHMENT), 
		"filterType=$trans_type&trans_no=$order_no");

	display_footer_exit();	

} elseif (isset($_GET['AddedGRN'])) {

	$trans_no = $_GET['AddedGRN'];
	$trans_type = ST_SUPPRECEIVE;

	display_notification_centered(_(UI_TEXT_DIRECT_GRN_ENTERED));

	display_note(get_trans_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THIS_GRN)), 0);

    $clearing_act = CompanyPrefsService::getCompanyPref('grn_clearing_act');
	if ($clearing_act)	
		display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_GL_JOURNAL_ENTRIES_FOR_THIS_DELIVERY)), 1);

	hyperlink_params("$path_to_root/purchasing/supplier_invoice.php",
		_(UI_TEXT_ENTRY_PURCHASE_INVOICE_FOR_THIS_RECEIVAL), "New=1");

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_GRN), "NewGRN=Yes");
	
	hyperlink_params("$path_to_root/admin/attachments.php", _(UI_TEXT_ADD_AN_ATTACHMENT), 
		"filterType=$trans_type&trans_no=$trans_no");

	display_footer_exit();	

} elseif (isset($_GET['AddedPI'])) {

	$trans_no = $_GET['AddedPI'];
	$trans_type = ST_SUPPINVOICE;

	display_notification_centered(_(UI_TEXT_DIRECT_PURCHASE_INVOICE_ENTERED));

	display_note(get_trans_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THIS_INVOICE)), 0);

	display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_GL_JOURNAL_ENTRIES_FOR_THIS_INVOICE)), 1);

	hyperlink_params("$path_to_root/purchasing/supplier_payment.php", _(UI_TEXT_ENTRY_SUPPLIER_PAYMENT_FOR_THIS_INVOICE),
		"trans_type=$trans_type&PInvoice=".$trans_no);

	hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_DIRECT_INVOICE), "NewInvoice=Yes");
	
	hyperlink_params("$path_to_root/admin/attachments.php", _(UI_TEXT_ADD_AN_ATTACHMENT), 
		"filterType=$trans_type&trans_no=$trans_no");

	display_footer_exit();	
}

if ($_SESSION['PO']->fixed_asset)
  check_db_has_purchasable_fixed_assets(_(UI_TEXT_NO_PURCHASABLE_FIXED_ASSETS_DEFINED_IN_SYSTEM));
else
  check_db_has_purchasable_items(_(UI_TEXT_NO_PURCHASABLE_INVENTORY_ITEMS_DEFINED_IN_SYSTEM));
//--------------------------------------------------------------------------------------------------

function line_start_focus() {
  global 	$Ajax;

  $Ajax->activate('items_table');
  set_focus('_stock_id_edit');
}
//--------------------------------------------------------------------------------------------------

function unset_form_variables() {
	unset($_POST['stock_id']);
    unset($_POST['qty']);
    unset($_POST['price']);
    unset($_POST['req_del_date']);
}

//---------------------------------------------------------------------------------------------------

function handle_delete_item($line_no)
{
	if($_SESSION['PO']->some_already_received($line_no) == 0)
	{
		$_SESSION['PO']->remove_from_order($line_no);
		unset_form_variables();
	} 
	else 
	{
		UiMessageService::displayError(_(UI_TEXT_ITEM_CANNOT_BE_DELETED_ALREADY_RECEIVED));
	}	
    line_start_focus();
}

//---------------------------------------------------------------------------------------------------

function handle_cancel_po()
{
	global $path_to_root;
	
	//need to check that not already dispatched or invoiced by the supplier
	if(($_SESSION['PO']->order_no != 0) && 
		$_SESSION['PO']->any_already_received() == 1)
	{
		UiMessageService::displayError(_(UI_TEXT_ORDER_CANNOT_BE_CANCELLED_ALREADY_RECEIVED) 
			. "<br>" . _(UI_TEXT_LINE_ITEM_QUANTITIES_MODIFICATION_RULES));
		return;
	}

	$fixed_asset = $_SESSION['PO']->fixed_asset;

	if($_SESSION['PO']->order_no != 0)
		delete_po($_SESSION['PO']->order_no);
	else {
		unset($_SESSION['PO']);

    	if ($fixed_asset)
			meta_forward($path_to_root.'/index.php','application=assets');
		else
			meta_forward($path_to_root.'/index.php','application=AP');
	}

	$_SESSION['PO']->clear_items();
	$_SESSION['PO'] = new purch_order;

	display_notification(_(UI_TEXT_PURCHASE_ORDER_CANCELLED));

	hyperlink_params($path_to_root . "/purchasing/po_entry_items.php", _(UI_TEXT_ENTER_NEW_PURCHASE_ORDER), "NewOrder=Yes");
	echo "<br>";

	end_page();
	exit;
}

//---------------------------------------------------------------------------------------------------

function check_data()
{
	if(!RequestService::getPostStatic('stock_id_text', true)) {
		UiMessageService::displayError( _(UI_TEXT_ITEM_DESCRIPTION_CANNOT_BE_EMPTY));
		set_focus('stock_id_edit');
		return false;
	}

	$dec = get_qty_dec($_POST['stock_id']);
	$min = 1 / pow(10, $dec);
    if (!check_num('qty',$min))
    {
    	$min = FormatService::numberFormat2($min, $dec);
	   	UiMessageService::displayError(_(UI_TEXT_QUANTITY_MUST_BE_NUMERIC_NOT_LESS_THAN).$min);
		set_focus('qty');
	   	return false;
    }

    if (!check_num('price', 0))
    {
	   	UiMessageService::displayError(_(UI_TEXT_PRICE_MUST_BE_NUMERIC_NOT_LESS_THAN_ZERO));
		set_focus('price');
	   	return false;	   
    }
    $dateService = new DateService();
    if ($_SESSION['PO']->trans_type == ST_PURCHORDER && !$dateService->isDate($_POST['req_del_date'])){
    		UiMessageService::displayError(_(UI_TEXT_DATE_ENTERED_INVALID_FORMAT));
		set_focus('req_del_date');
   		return false;    	 
    }
     
    return true;	
}

//---------------------------------------------------------------------------------------------------

function handle_update_item()
{
	$allow_update = check_data(); 

	if ($allow_update)
	{
		if ($_SESSION['PO']->line_items[$_POST['line_no']]->qty_inv > RequestService::inputNumStatic('qty') ||
			$_SESSION['PO']->line_items[$_POST['line_no']]->qty_received > RequestService::inputNumStatic('qty'))
		{
			UiMessageService::displayError(_(UI_TEXT_QUANTITY_ORDERED_LESS_THAN_INVOICED_RECEIVED_PROHIBITED) .
				"<br>" . _(UI_TEXT_QUANTITY_RECEIVED_INVOICED_MODIFICATION_RULES));
			set_focus('qty');
			return;
		}
	
		$_SESSION['PO']->update_order_item($_POST['line_no'], RequestService::inputNumStatic('qty'), RequestService::inputNumStatic('price'),
  			@$_POST['req_del_date'], $_POST['item_description'] );
		unset_form_variables();
	}	
    line_start_focus();
}

//---------------------------------------------------------------------------------------------------

function handle_add_new_item()
{
	$allow_update = check_data();
	
	if ($allow_update == true)
	{ 
		if (count($_SESSION['PO']->line_items) > 0)
		{
		    foreach ($_SESSION['PO']->line_items as $order_item) 
		    {
    			/* do a loop round the items on the order to see that the item
    			is not already on this order */
   			    if (($order_item->stock_id == $_POST['stock_id'])) 
   			    {
					display_warning(_(UI_TEXT_SELECTED_ITEM_ALREADY_ON_ORDER));
			    }
		    } /* end of the foreach loop to look for pre-existing items of the same code */
		}

		if ($allow_update == true)
		{
			$result = get_short_info($_POST['stock_id']);

			if (db_num_rows($result) == 0)
			{
				$allow_update = false;
			}

			if ($allow_update)
			{
				$_SESSION['PO']->add_to_order (count($_SESSION['PO']->line_items), $_POST['stock_id'], RequestService::inputNumStatic('qty'), 
					RequestService::getPostStatic('stock_id_text'), //$myrow["description"], 
					RequestService::inputNumStatic('price'), '', // $myrow["units"], (retrived in cart)
					$_SESSION['PO']->trans_type == ST_PURCHORDER ? $_POST['req_del_date'] : '', 0, 0);

				unset_form_variables();
				$_POST['stock_id']	= "";
	   		} 
	   		else 
	   		{
			     UiMessageService::displayError(_(UI_TEXT_SELECTED_ITEM_DOES_NOT_EXIST_OR_KIT));
		   	}

		} /* end of if not already on the order and allow input was true*/
    }
	line_start_focus();
}

//---------------------------------------------------------------------------------------------------

function can_commit()
{
	if (!RequestService::getPostStatic('supplier_id')) 
	{
		UiMessageService::displayError(_(UI_TEXT_NO_SUPPLIER_SELECTED));
		set_focus('supplier_id');
		return false;
	} 
	$dateService = new DateService();

	if (!$dateService->isDate($_POST['OrderDate'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_ORDER_DATE_INVALID));
		set_focus('OrderDate');
		return false;
	} 
	if (($_SESSION['PO']->trans_type == ST_SUPPRECEIVE || $_SESSION['PO']->trans_type == ST_SUPPINVOICE) 
		&& !DateService::isDateInFiscalYear($_POST['OrderDate'])) {
		UiMessageService::displayError(_(UI_TEXT_ENTERED_DATE_OUT_OF_FISCAL_YEAR));
		set_focus('OrderDate');
		return false;
	}

	if (($_SESSION['PO']->trans_type==ST_SUPPINVOICE) && !$dateService->isDate($_POST['due_date'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_ENTERED_DUE_DATE_INVALID));
		set_focus('due_date');
		return false;
	} 

	if (!$_SESSION['PO']->order_no) 
	{
    	if (!check_reference(RequestService::getPostStatic('ref'), $_SESSION['PO']->trans_type))
    	{
			set_focus('ref');
    		return false;
    	}
	}

	if ($_SESSION['PO']->trans_type == ST_SUPPINVOICE && trim(RequestService::getPostStatic('supp_ref')) == false)
	{
		UiMessageService::displayError(_(UI_TEXT_MUST_ENTER_SUPPLIER_INVOICE_REFERENCE));
		set_focus('supp_ref');
		return false;
	}
	if ($_SESSION['PO']->trans_type==ST_SUPPINVOICE 
		&& is_reference_already_there($_SESSION['PO']->supplier_id, RequestService::getPostStatic('supp_ref'), $_SESSION['PO']->order_no))
	{
		UiMessageService::displayError(_(UI_TEXT_INVOICE_NUMBER_ALREADY_ENTERED) . " (" . RequestService::getPostStatic('supp_ref') . ")");
		set_focus('supp_ref');
		return false;
	}
	if ($_SESSION['PO']->trans_type == ST_PURCHORDER && RequestService::getPostStatic('delivery_address') == '')
	{
		UiMessageService::displayError(_(UI_TEXT_NO_DELIVERY_ADDRESS_SPECIFIED));
		set_focus('delivery_address');
		return false;
	} 
	if (RequestService::getPostStatic('StkLocation') == '')
	{
		UiMessageService::displayError(_(UI_TEXT_NO_LOCATION_SPECIFIED_TO_MOVE_ITEMS));
		set_focus('StkLocation');
		return false;
	} 
	if (!db_has_currency_rates($_SESSION['PO']->curr_code, $_POST['OrderDate'], true))
		return false;
	if ($_SESSION['PO']->order_has_items() == false)
	{
     	display_error (_(UI_TEXT_ORDER_CANNOT_BE_PLACED_NO_LINES));
     	return false;
	}
	if (floatcmp(RequestService::inputNumStatic('prep_amount'), $_SESSION['PO']->get_trans_total()) > 0)
	{
		UiMessageService::displayError(_(UI_TEXT_REQUIRED_PREPAYMENT_GREATER_THAN_TOTAL));
		set_focus('prep_amount');
		return false;
	}

	return true;
}

function handle_commit_order()
{
	$cart = &$_SESSION['PO'];

	if (can_commit()) {

		copy_to_cart();
		DateService::newDocDateStatic($cart->orig_order_date);
		if ($cart->order_no == 0) { // new po/grn/invoice
			$trans_no = add_direct_supp_trans($cart);
			if ($trans_no) {
				unset($_SESSION['PO']);
				if ($cart->trans_type == ST_PURCHORDER)
	 				meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
				elseif ($cart->trans_type == ST_SUPPRECEIVE)
					meta_forward($_SERVER['PHP_SELF'], "AddedGRN=$trans_no");
				else
					meta_forward($_SERVER['PHP_SELF'], "AddedPI=$trans_no");
			}
		} else { // order modification
			$order_no = update_po($cart);
			unset($_SESSION['PO']);
        	meta_forward($_SERVER['PHP_SELF'], "AddedID=$order_no&Updated=1");	
		}
	}
}
//---------------------------------------------------------------------------------------------------
if (isset($_POST['update'])) {
	copy_to_cart();
	$Ajax->activate('items_table');
}

$id = find_submit('Delete');
if ($id != -1)
	handle_delete_item($id);

if (isset($_POST['Commit']))
{
	handle_commit_order();
}
if (isset($_POST['UpdateLine']))
	handle_update_item();

if (isset($_POST['EnterLine']))
	handle_add_new_item();

if (isset($_POST['CancelOrder'])) 
	handle_cancel_po();

if (isset($_POST['CancelUpdate']))
	unset_form_variables();

if (isset($_POST['CancelUpdate']) || isset($_POST['UpdateLine'])) {
	line_start_focus();
}

//---------------------------------------------------------------------------------------------------

start_form();

display_po_header($_SESSION['PO']);
echo "<br>";

display_po_items($_SESSION['PO']);

start_table(TABLESTYLE2);


if ($_SESSION['PO']->trans_type == ST_SUPPINVOICE) {
	cash_accounts_list_row(_(UI_TEXT_PAYMENT_LABEL), 'cash_account', null, false, _(UI_TEXT_DELAYED));
}

textarea_row(_(UI_TEXT_MEMO_LABEL), 'Comments', null, 70, 4);

end_table(1);

div_start('controls', 'items_table');
$process_txt = _(UI_TEXT_PLACE_ORDER);
$update_txt = _(UI_TEXT_UPDATE_ORDER);
$cancel_txt = _(UI_TEXT_CANCEL_ORDER);
if ($_SESSION['PO']->trans_type == ST_SUPPRECEIVE) {
	$process_txt = _(UI_TEXT_PROCESS_GRN);
	$update_txt = _(UI_TEXT_UPDATE_GRN);
	$cancel_txt = _(UI_TEXT_CANCEL_GRN);
}	
elseif ($_SESSION['PO']->trans_type == ST_SUPPINVOICE) {
	$process_txt = _(UI_TEXT_PROCESS_INVOICE);
	$update_txt = _(UI_TEXT_UPDATE_INVOICE);
	$cancel_txt = _(UI_TEXT_CANCEL_INVOICE);
}	
if ($_SESSION['PO']->order_has_items()) 
{
	if ($_SESSION['PO']->order_no)
		submit_center_first('Commit', $update_txt, '', 'default');
	else
		submit_center_first('Commit', $process_txt, '', 'default');
	submit_center_last('CancelOrder', $cancel_txt); 	
}
else
	submit_center('CancelOrder', $cancel_txt, true, false, 'cancel');
div_end();
//---------------------------------------------------------------------------------------------------

end_form();
end_page();
