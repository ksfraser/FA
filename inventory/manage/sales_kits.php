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
$page_security = 'SA_SALESKIT';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);

page(_($help_context = "Sales Kits & Alias Codes"), false, false, "", $js);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/data_checks.inc");

check_db_has_stock_items(_(UI_TEXT_THERE_ARE_NO_ITEMS_DEFINED_IN_THE_SYSTEM));

simple_page_mode(true);

//--------------------------------------------------------------------------------------------------
function display_kit_items($selected_kit)
{
	$result = get_item_kit($selected_kit);
	div_start('bom');
	start_table(TABLESTYLE, "width='60%'");
	$th = array(_(UI_TEXT_STOCK_ITEM_LABEL), _(UI_TEXT_DESCRIPTION_LABEL), _(UI_TEXT_QUANTITY_LABEL), _(UI_TEXT_UNITS_LABEL),
		'','');
	table_header($th);

	$k = 0;
	while ($myrow = db_fetch($result))
	{

		alt_table_row_color($k);

		label_cell($myrow["stock_id"]);
		label_cell($myrow["comp_name"]);
        qty_cell($myrow["quantity"], false, 
			$myrow["units"] == '' ? 0 : get_qty_dec($myrow["stock_id"]));
        label_cell($myrow["units"] == '' ? _(UI_TEXT_KIT_LABEL) : $myrow["units"]);
 		edit_button_cell("Edit".$myrow['id'], _(UI_TEXT_EDIT));
 		delete_button_cell("Delete".$myrow['id'], _(UI_TEXT_DELETE));
        end_row();

	} //END WHILE LIST LOOP
	end_table();
	div_end();
}

//--------------------------------------------------------------------------------------------------

function update_kit($selected_kit, $component_id)
{
	global $Mode, $Ajax;

	if (!check_num('quantity', 0))
	{
		UiMessageService::displayError(_(UI_TEXT_THE_QUANTITY_ENTERED_MUST_BE_NUMERIC_AND_GREATER_THAN_ZERO));
		set_focus('quantity');
		return 0;
	}
   	elseif (RequestService::getPostStatic('description') == '')
   	{
      	UiMessageService::displayError( _(UI_TEXT_ITEM_CODE_DESCRIPTION_CANNOT_BE_EMPTY));
		set_focus('description');
		return 0;
   	}
	elseif ($component_id == -1)	// adding new component to alias/kit with optional kit creation
	{
		if ($selected_kit == '') { // New kit/alias definition
			if (RequestService::getPostStatic('kit_code') == '') {
	    	  	UiMessageService::displayError( _(UI_TEXT_KIT_ALIAS_CODE_CANNOT_BE_EMPTY));
				set_focus('kit_code');
				return 0;
			}
			$kit = get_item_kit(RequestService::getPostStatic('kit_code'));
    		if (db_num_rows($kit)) {
			  	$input_error = 1;
    	  		UiMessageService::displayError( _(UI_TEXT_THIS_ITEM_CODE_IS_ALREADY_ASSIGNED_TO_STOCK_ITEM_OR_SALE_KIT));
				set_focus('kit_code');
				return 0;
			}
		}
   	}

	if (check_item_in_kit($component_id, $selected_kit, RequestService::getPostStatic('component'), true)) {
		UiMessageService::displayError(_(UI_TEXT_THE_SELECTED_COMPONENT_CONTAINS_DIRECTLY_OR_ON_ANY_LOWER_LEVEL_THE_KIT_UNDER_EDITION_RECURSIVE_KITS_ARE_NOT_ALLOWED));
		set_focus('component');
		return 0;
	}

		/*Now check to see that the component is not already in the kit */
	if (check_item_in_kit($component_id, $selected_kit, RequestService::getPostStatic('component'))) {
		UiMessageService::displayError(_(UI_TEXT_THE_SELECTED_COMPONENT_IS_ALREADY_IN_THIS_KIT_YOU_CAN_MODIFY_ITS_QUANTITY_BUT_IT_CANNOT_APPEAR_MORE_THAN_ONCE_IN_THE_SAME_KIT));
		set_focus('component');
		return 0;
	}
	if ($component_id == -1) { // new component in alias/kit 
		if ($selected_kit == '') {
			$selected_kit = RequestService::getPostStatic('kit_code');
			$msg = _(UI_TEXT_NEW_ALIAS_CODE_HAS_BEEN_CREATED);
		}
		 else
			$msg =_(UI_TEXT_NEW_COMPONENT_HAS_BEEN_ADDED_TO_SELECTED_KIT);

		add_item_code($selected_kit, RequestService::getPostStatic('component'), RequestService::getPostStatic('description'),
			 RequestService::getPostStatic('category'), RequestService::inputNumStatic('quantity'), 0);
		display_notification($msg);

	} else { // update component
		$props = get_kit_props($selected_kit);
		update_item_code($component_id, $selected_kit, RequestService::getPostStatic('component'),
			$props['description'], $props['category_id'], RequestService::inputNumStatic('quantity'), 0);
		display_notification(_(UI_TEXT_COMPONENT_OF_SELECTED_KIT_HAS_BEEN_UPDATED));
	}
	$Mode = 'RESET';
	$Ajax->activate('_page_body');

	return $selected_kit;
}

//--------------------------------------------------------------------------------------------------

if (RequestService::getPostStatic('update_name')) {
	update_kit_props(RequestService::getPostStatic('item_code'), RequestService::getPostStatic('description'), RequestService::getPostStatic('category'));
	display_notification(_(UI_TEXT_KIT_COMMON_PROPERTIES_HAS_BEEN_UPDATED));
	$Ajax->activate('_page_body');
}

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{
	if ($selected_kit = update_kit(RequestService::getPostStatic('item_code'), $selected_id))
		$_POST['item_code'] = $selected_kit;
}

if ($Mode == 'Delete')
{
	// Before removing last component from selected kit check 
	// if selected kit is not included in any other kit. 
	// 
	$other_kits = get_where_used($_POST['item_code']);
	$num_kits = db_num_rows($other_kits);

	$kit = get_item_kit($_POST['item_code']);
	if ((db_num_rows($kit) == 1) && $num_kits) {

		$msg = _(UI_TEXT_THIS_ITEM_CANNOT_BE_DELETED_BECAUSE_IT_IS_THE_LAST_ITEM_IN_THE_KIT_USED_BY_FOLLOWING_KITS)
			.':<br>';

		while($num_kits--) {
			$kit = db_fetch($other_kits);
			$msg .= "'".$kit[0]."'";
			if ($num_kits) $msg .= ',';
		}
		UiMessageService::displayError($msg);
	} else {
		delete_item_code($selected_id);
		display_notification(_(UI_TEXT_THE_COMPONENT_ITEM_HAS_BEEN_DELETED_FROM_THIS_BOM));
		$Mode = 'RESET';
	}
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	unset($_POST['quantity']);
	unset($_POST['component']);
}
//--------------------------------------------------------------------------------------------------

start_form();

echo "<center>" . _(UI_TEXT_SELECT_A_SALE_KIT_LABEL) . "&nbsp;";
echo sales_kits_list('item_code', null, _(UI_TEXT_NEW_KIT_LABEL), true);
echo "</center><br>";
$props = get_kit_props($_POST['item_code']);

if (list_updated('item_code')) {
	if (RequestService::getPostStatic('item_code') == '')
		$_POST['description'] = '';
	$Ajax->activate('_page_body');
}

$selected_kit = $_POST['item_code'];
//----------------------------------------------------------------------------------
if (RequestService::getPostStatic('item_code') == '') {
// New sales kit entry
	start_table(TABLESTYLE2);
	text_row(_(UI_TEXT_ALIAS_KIT_CODE_LABEL), 'kit_code', null, 20, 20);
} else
{
	 // Kit selected so display bom or edit component
	$_POST['description'] = $props['description'];
	$_POST['category'] = $props['category_id'];
	start_table(TABLESTYLE2);
	text_row(_(UI_TEXT_DESCRIPTION_LABEL_WITH_COLON), 'description', null, 50, 200);
	stock_categories_list_row(_(UI_TEXT_CATEGORY_LABEL), 'category', null);
	submit_row('update_name', _(UI_TEXT_UPDATE), false, 'align=center colspan=2', _(UI_TEXT_UPDATE_KIT_ALIAS_NAME), true);
	end_row();
	end_table(1);
	display_kit_items($selected_kit);
	echo '<br>';
	start_table(TABLESTYLE2);
}

	if ($Mode == 'Edit') {
		$myrow = get_item_code($selected_id);
		$_POST['component'] = $myrow["stock_id"];
		$_POST['quantity'] = FormatService::numberFormat2($myrow["quantity"], get_qty_dec($myrow["stock_id"]));
	}
	hidden("selected_id", $selected_id);
	
	sales_local_items_list_row(_(UI_TEXT_COMPONENT_LABEL),'component', null, false, true);

	if (RequestService::getPostStatic('item_code') == '') { // new kit/alias
		if ($Mode!='ADD_ITEM' && $Mode!='UPDATE_ITEM') {
			$_POST['description'] = is_array($props) ? $props['description'] : '';
			$_POST['category'] = is_array($props) ? $props['category_id'] : '';
		}
		text_row(_(UI_TEXT_DESCRIPTION_LABEL_WITH_COLON), 'description', null, 50, 200);
		stock_categories_list_row(_(UI_TEXT_CATEGORY_LABEL), 'category', null);
	}
	$res = get_item_edit_info(RequestService::getPostStatic('component'));
	$dec =  $res["decimals"] == '' ? 0 : $res["decimals"];
	$units = $res["units"] == '' ? _(UI_TEXT_KITS_LABEL) : $res["units"];
	if (list_updated('component')) 
	{
		$_POST['quantity'] = FormatService::numberFormat2(1, $dec);
		$Ajax->activate('quantity');
		$Ajax->activate('category');
	}
	
	qty_row(_(UI_TEXT_QUANTITY_LABEL_WITH_COLON), 'quantity', FormatService::numberFormat2(1, $dec), '', $units, $dec);

	end_table(1);
	submit_add_or_update_center($selected_id == -1, '', 'both');
	end_form();
//----------------------------------------------------------------------------------

end_page();

