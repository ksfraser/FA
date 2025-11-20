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
$page_security = 'SA_INVENTORYADJUSTMENT';
$path_to_root = "..";
include_once($path_to_root . "/includes/ui/items_cart.inc");

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui_strings.php");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/fixed_assets/includes/fixed_assets_db.inc");
include_once($path_to_root . "/inventory/includes/item_adjustments_ui.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

// Modern OOP Services
require_once($path_to_root . "/includes/DateService.php");
require_once($path_to_root . "/includes/InventoryService.php");
use FA\Services\DateService;
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
if (isset($_GET['NewAdjustment'])) {
	if (isset($_GET['FixedAsset'])) {
		$page_security = 'SA_ASSETDISPOSAL';
		$_SESSION['page_title'] = _($help_context = "Fixed Assets Disposal");
	} else {
		$_SESSION['page_title'] = _($help_context = "Item Adjustments Note");
	}
}
page($_SESSION['page_title'], false, false, "", $js);

//-----------------------------------------------------------------------------------------------

if (isset($_GET['AddedID'])) 
{
	$trans_no = $_GET['AddedID'];
	$trans_type = ST_INVADJUST;

  $result = get_stock_adjustment_items($trans_no);
  $row = db_fetch($result);

	if (InventoryService::isFixedAsset($row['mb_flag'])) {
		display_notification_centered(_(UI_TEXT_FIXED_ASSETS_DISPOSAL_HAS_BEEN_PROCESSED));
		display_note(get_trans_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THIS_DISPOSAL)));    display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THE_GL_POSTINGS_FOR_THIS_DISPOSAL)), 1, 0);
	  hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_DISPOSAL), "NewAdjustment=1&FixedAsset=1");
  }
  else {
    display_notification_centered(_(UI_TEXT_ITEMS_ADJUSTMENT_HAS_BEEN_PROCESSED));
    display_note(get_trans_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THIS_ADJUSTMENT)));

    display_note(get_gl_view_str($trans_type, $trans_no, _(UI_TEXT_VIEW_THE_GL_POSTINGS_FOR_THIS_ADJUSTMENT)), 1, 0);

	  hyperlink_params($_SERVER['PHP_SELF'], _(UI_TEXT_ENTER_ANOTHER_ADJUSTMENT), "NewAdjustment=1");
  }

	hyperlink_params("$path_to_root/admin/attachments.php", _(UI_TEXT_ADD_AN_ATTACHMENT), "filterType=$trans_type&trans_no=$trans_no");

	display_footer_exit();
}
//--------------------------------------------------------------------------------------------------

function line_start_focus() {
  global 	$Ajax;

  $Ajax->activate('items_table');
  set_focus('_stock_id_edit');
}
//-----------------------------------------------------------------------------------------------

function handle_new_order()
{
	if (isset($_SESSION['adj_items']))
	{
		$_SESSION['adj_items']->clear_items();
		unset ($_SESSION['adj_items']);
	}

    $_SESSION['adj_items'] = new items_cart(ST_INVADJUST);
    $_SESSION['adj_items']->fixed_asset = isset($_GET['FixedAsset']);
	$_POST['AdjDate'] = DateService::newDocDateStatic();
	if (!DateService::isDateInFiscalYear($_POST['AdjDate']))
		$_POST['AdjDate'] = DateService::endFiscalYear();
	$_SESSION['adj_items']->tran_date = $_POST['AdjDate'];	
}

//-----------------------------------------------------------------------------------------------

function can_process()
{
	global $SysPrefs;

	$adj = &$_SESSION['adj_items'];

	if (count($adj->line_items) == 0)	{
		UiMessageService::displayError(_(UI_TEXT_YOU_MUST_ENTER_AT_LEAST_ONE_NON_EMPTY_ITEM_LINE));
		set_focus('stock_id');
		return false;
	}

	if (!check_reference($_POST['ref'], ST_INVADJUST))
	{
		set_focus('ref');
		return false;
	}
	$dateService = new DateService();

	if (!$dateService->isDate($_POST['AdjDate'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_FOR_THE_ADJUSTMENT_IS_INVALID));
		set_focus('AdjDate');
		return false;
	} 
	elseif (!DateService::isDateInFiscalYearStatic($_POST['AdjDate'])) 
	{
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_DATE_IS_OUT_OF_FISCAL_YEAR_OR_IS_CLOSED_FOR_FURTHER_DATA_ENTRY));
		set_focus('AdjDate');
		return false;
	}
	elseif (!$SysPrefs->allow_negative_stock())
	{
		$low_stock = $adj->check_qoh($_POST['StockLocation'], $_POST['AdjDate']);

		if ($low_stock)
		{
    		UiMessageService::displayError(_(UI_TEXT_THE_ADJUSTMENT_CANNOT_BE_PROCESSED_BECAUSE_IT_WOULD_CAUSE_NEGATIVE_INVENTORY_BALANCE_FOR_MARKED_ITEMS_AS_OF_DOCUMENT_DATE_OR_LATER));
			unset($_POST['Process']);
			return false;
		}
	}
	return true;
}

//-------------------------------------------------------------------------------

if (isset($_POST['Process']) && can_process()){

  $fixed_asset = $_SESSION['adj_items']->fixed_asset; 

	$trans_no = add_stock_adjustment($_SESSION['adj_items']->line_items,
		$_POST['StockLocation'], $_POST['AdjDate'],	$_POST['ref'], $_POST['memo_']);
	DateService::newDocDateStatic($_POST['AdjDate']);
	$_SESSION['adj_items']->clear_items();
	unset($_SESSION['adj_items']);

  if ($fixed_asset)
   	meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no&FixedAsset=1");
  else
   	meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");

} /*end of process credit note */

//-----------------------------------------------------------------------------------------------

function check_item_data()
{
	if (RequestService::inputNumStatic('qty') == 0)
	{
		UiMessageService::displayError(_(UI_TEXT_THE_QUANTITY_ENTERED_IS_INVALID));
		set_focus('qty');
		return false;
	}

	if (!check_num('std_cost', 0))
	{
		UiMessageService::displayError(_(UI_TEXT_THE_ENTERED_STANDARD_COST_IS_NEGATIVE_OR_INVALID));
		set_focus('std_cost');
		return false;
	}

   	return true;
}

//-----------------------------------------------------------------------------------------------

function handle_update_item()
{
	$id = $_POST['LineNo'];
   	$_SESSION['adj_items']->update_cart_item($id, RequestService::inputNumStatic('qty'), 
		RequestService::inputNumStatic('std_cost'));
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_delete_item($id)
{
	$_SESSION['adj_items']->remove_from_cart($id);
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_new_item()
{
	add_to_order($_SESSION['adj_items'], $_POST['stock_id'], 
	RequestService::inputNumStatic('qty'), RequestService::inputNumStatic('std_cost'));
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------
$id = find_submit('Delete');
if ($id != -1)
	handle_delete_item($id);

if (isset($_POST['AddItem']) && check_item_data()) {
	handle_new_item();
	unset($_POST['selected_id']);
}
if (isset($_POST['UpdateItem']) && check_item_data()) {
	handle_update_item();
	unset($_POST['selected_id']);
}
if (isset($_POST['CancelItemChanges'])) {
	unset($_POST['selected_id']);
	line_start_focus();
}
//-----------------------------------------------------------------------------------------------

if (isset($_GET['NewAdjustment']) || !isset($_SESSION['adj_items']))
{

	if (isset($_GET['FixedAsset']))
		check_db_has_disposable_fixed_assets(_(UI_TEXT_THERE_ARE_NO_FIXED_ASSETS_DEFINED_IN_THE_SYSTEM));
	else
		check_db_has_costable_items(_(UI_TEXT_THERE_ARE_NO_INVENTORY_ITEMS_DEFINED_IN_THE_SYSTEM_WHICH_CAN_BE_ADJUSTED_PURCHASED_OR_MANUFACTURED));

	handle_new_order();
}

//-----------------------------------------------------------------------------------------------
start_form();

if ($_SESSION['adj_items']->fixed_asset) {
	$items_title = _(UI_TEXT_DISPOSAL_ITEMS);
	$button_title = _(UI_TEXT_PROCESS_DISPOSAL);
} else {
	$items_title = _(UI_TEXT_ADJUSTMENT_ITEMS);
	$button_title = _(UI_TEXT_PROCESS_ADJUSTMENT);
}

display_order_header($_SESSION['adj_items']);

start_outer_table(TABLESTYLE, "width='70%'", 10);

display_adjustment_items($items_title, $_SESSION['adj_items']);
adjustment_options_controls();

end_outer_table(1, false);

submit_center_first('Update', _("Update"), '', null);
submit_center_last('Process', $button_title, '', 'default');

end_form();
end_page();

