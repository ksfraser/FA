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
$page_security = 'SA_STANDARDCOST';

if (@$_GET['page_level'] == 1)
	$path_to_root = "../..";
else	
	$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/includes/ui/items_cart.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);

if (isset($_GET['FixedAsset'])) {
	$_SESSION['page_title'] = _($help_context = "FA Revaluation");
	$_POST['fixed_asset'] = 1;
} else {
	$_SESSION['page_title'] = _($help_context = "Inventory Item Cost Update");
}
page($_SESSION['page_title'], false, false, "", $js);

//--------------------------------------------------------------------------------------

if (RequestService::getPostStatic('fixed_asset') == 1)
	check_db_has_disposable_fixed_assets(_("There are no fixed assets defined in the system."));
else
	check_db_has_costable_items(_(UI_TEXT_THERE_ARE_NO_COSTABLE_INVENTORY_ITEMS_DEFINED_IN_THE_SYSTEM_PURCHASED_OR_MANUFACTURED_ITEMS));

if (isset($_GET['stock_id']))
{
	$_POST['stock_id'] = $_GET['stock_id'];
}

//--------------------------------------------------------------------------------------
$should_update = false;
if (isset($_POST['UpdateData']))
{
	$old_cost = get_unit_cost($_POST['stock_id']);

   	$new_cost = RequestService::inputNumStatic('material_cost') + RequestService::inputNumStatic('labour_cost')
	     + RequestService::inputNumStatic('overhead_cost');

   	$should_update = true;

	if (!check_num('material_cost') || !check_num('labour_cost') ||
		!check_num('overhead_cost'))
	{
		UiMessageService::displayError( _(UI_TEXT_THE_ENTERED_COST_IS_NOT_NUMERIC));
		set_focus('material_cost');
   	 	$should_update = false;
	}
	elseif ($old_cost == $new_cost)
	{
   	 	UiMessageService::displayError( _(UI_TEXT_THE_NEW_COST_IS_THE_SAME_AS_THE_OLD_COST_COST_WAS_NOT_UPDATED));
   	 	$should_update = false;
	}

   	if ($should_update)
   	{
		$update_no = stock_cost_update($_POST['stock_id'],
		    RequestService::inputNumStatic('material_cost'), RequestService::inputNumStatic('labour_cost'),
		    RequestService::inputNumStatic('overhead_cost'),	$old_cost, 
        $_POST['refline'], $_POST['memo_']);

        display_notification(_(UI_TEXT_COST_HAS_BEEN_UPDATED));

        if ($update_no > 0)
        {
    		display_notification(get_gl_view_str(ST_COSTUPDATE, $update_no, _(UI_TEXT_VIEW_THE_GL_JOURNAL_ENTRIES_FOR_THIS_COST_UPDATE)));
        }

   	}
}

if (list_updated('stock_id') || $should_update) {
	unset($_POST['memo_']);
	$Ajax->activate('cost_table');
}
//-----------------------------------------------------------------------------------------

$action = $_SERVER['PHP_SELF'];
if ($page_nested)
	$action .= "?stock_id=".RequestService::getPostStatic('stock_id');
start_form(false, false, $action);

hidden('fixed_asset');

if (!isset($_POST['stock_id']))
	$_POST['stock_id'] = get_global_stock_item();

if (!$page_nested)
{
	echo "<center>" . _(UI_TEXT_ITEM_COLON). "&nbsp;";
	if (RequestService::getPostStatic('fixed_asset') == 1)
		echo stock_disposable_fa_list('stock_id', $_POST['stock_id'], false, true);
	else
		echo stock_items_list('stock_id', $_POST['stock_id'], false, true);

	echo "</center><hr>";
}
else
	br(2);

set_global_stock_item($_POST['stock_id']);

$myrow = get_item($_POST['stock_id']);

div_start('cost_table');

start_table(TABLESTYLE2);
$dec1 = $dec2 = $dec3 = 0;
if ($myrow) {
	$_POST['material_cost'] = price_decimal_format($myrow["material_cost"], $dec1);
	$_POST['labour_cost'] = price_decimal_format($myrow["labour_cost"], $dec2);
	$_POST['overhead_cost'] = price_decimal_format($myrow["overhead_cost"], $dec3);
}

amount_row(_(UI_TEXT_UNIT_COST), "material_cost", null, "class='tableheader2'", null, $dec1);

if ($myrow && $myrow["mb_flag"]=='M')
{
	amount_row(_(UI_TEXT_STANDARD_LABOUR_COST_PER_UNIT), "labour_cost", null, "class='tableheader2'", null, $dec2);
	amount_row(_(UI_TEXT_STANDARD_OVERHEAD_COST_PER_UNIT), "overhead_cost", null, "class='tableheader2'", null, $dec3);
}
else
{
	hidden("labour_cost", 0);
	hidden("overhead_cost", 0);
}
refline_list_row(_(UI_TEXT_REFERENCE_LINE_COLON), 'refline', ST_COSTUPDATE, null, false, RequestService::getPostStatic('fixed_asset'));
textarea_row(_(UI_TEXT_MEMO), 'memo_', null, 40, 4);

end_table(1);
div_end();
submit_center('UpdateData', _(UI_TEXT_UPDATE), true, false, 'default');

end_form();
end_page();
