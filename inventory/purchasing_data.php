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
$page_security = 'SA_PURCHASEPRICING';

if (@$_GET['page_level'] == 1)
	$path_to_root = "../..";
else	
	$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);
page(_($help_context = "Supplier Purchasing Data"), false, false, "", $js);

check_db_has_purchasable_items(_(UI_TEXT_THERE_ARE_NO_PURCHASABLE_INVENTORY_ITEMS_DEFINED_IN_THE_SYSTEM));
check_db_has_suppliers(_(UI_TEXT_THERE_ARE_NO_SUPPLIERS_DEFINED_IN_THE_SYSTEM));

//----------------------------------------------------------------------------------------
simple_page_mode(true);
if (isset($_GET['stock_id']))
{
	$_POST['stock_id'] = $_GET['stock_id'];
}

//--------------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

   	$input_error = 0;
   	if ($_POST['stock_id'] == "" || !isset($_POST['stock_id']))
   	{
      	$input_error = 1;
      	UiMessageService::displayError( _(UI_TEXT_THERE_IS_NO_ITEM_SELECTED));
		set_focus('stock_id');
   	}
   	elseif (!check_num('price', 0))
   	{
      	$input_error = 1;
      	UiMessageService::displayError( _(UI_TEXT_THE_PRICE_ENTERED_WAS_NOT_NUMERIC));
	set_focus('price');
   	}
   	elseif (!check_num('conversion_factor'))
   	{
      	$input_error = 1;
      	UiMessageService::displayError( _(UI_TEXT_THE_CONVERSION_FACTOR_ENTERED_WAS_NOT_NUMERIC_THE_CONVERSION_FACTOR_IS_THE_NUMBER_BY_WHICH_THE_PRICE_MUST_BE_DIVIDED_BY_TO_GET_THE_UNIT_PRICE_IN_OUR_UNIT_OF_MEASURE));
		set_focus('conversion_factor');
   	}
   	elseif ($Mode == 'ADD_ITEM' && get_item_purchasing_data($_POST['supplier_id'], $_POST['stock_id']))
   	{
      	$input_error = 1;
      	UiMessageService::displayError( _(UI_TEXT_THE_PURCHASING_DATA_FOR_THIS_SUPPLIER_HAS_ALREADY_BEEN_ADDED));
		set_focus('supplier_id');
	}
	if ($input_error == 0)
	{
     	if ($Mode == 'ADD_ITEM') 
       	{
			add_item_purchasing_data($_POST['supplier_id'], $_POST['stock_id'], RequestService::inputNumStatic('price',0),
				$_POST['suppliers_uom'], RequestService::inputNumStatic('conversion_factor'), $_POST['supplier_description']);
    		display_notification(_(UI_TEXT_THIS_SUPPLIER_PURCHASING_DATA_HAS_BEEN_ADDED));
       	} 
       	else
       	{
       		update_item_purchasing_data($selected_id, $_POST['stock_id'], RequestService::inputNumStatic('price',0),
       			$_POST['suppliers_uom'], RequestService::inputNumStatic('conversion_factor'), $_POST['supplier_description']);
    	  	display_notification(_(UI_TEXT_SUPPLIER_PURCHASING_DATA_HAS_BEEN_UPDATED));
       	}
		$Mode = 'RESET';
	}
}

//--------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	delete_item_purchasing_data($selected_id, $_POST['stock_id']);
	display_notification(_(UI_TEXT_THE_PURCHASING_DATA_ITEM_HAS_BEEN_SUCCESSFULLY_DELETED));
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
}

if (isset($_POST['_selected_id_update']) )
{
	$selected_id = $_POST['selected_id'];
	$Ajax->activate('_page_body');
}

if (list_updated('stock_id')) 
	$Ajax->activate('price_table');
//--------------------------------------------------------------------------------------------------

$action = $_SERVER['PHP_SELF'];
if ($page_nested)
	$action .= "?stock_id=".RequestService::getPostStatic('stock_id');
start_form(false, false, $action);

if (!isset($_POST['stock_id']))
	$_POST['stock_id'] = get_global_stock_item();

if (!$page_nested)
{
	echo "<center>" . _(UI_TEXT_ITEM_COLON). "&nbsp;";
	// All items can be purchased
	echo stock_items_list('stock_id', $_POST['stock_id'], false, true);
	echo "<hr></center>";
}
else
	br(2);

set_global_stock_item($_POST['stock_id']);

$mb_flag = get_mb_flag($_POST['stock_id']);

if ($mb_flag == -1)
{
	UiMessageService::displayError(_(UI_TEXT_ENTERED_ITEM_IS_NOT_DEFINED_PLEASE_RE_ENTER));
  	$Ajax->activate('price_table');
	set_focus('stock_id');
}
else
{
	$result = get_items_purchasing_data($_POST['stock_id']);
  	div_start('price_table');
    if (db_num_rows($result) == 0)
    {
    	display_note(_(UI_TEXT_THERE_IS_NO_PURCHASING_DATA_SET_UP_FOR_THE_PART_SELECTED));
    }
    else
    {
        start_table(TABLESTYLE, "width='65%'");

		$th = array(_(UI_TEXT_SUPPLIER), _(UI_TEXT_PRICE), _(UI_TEXT_CURRENCY),
			_(UI_TEXT_SUPPLIERS_UNIT), _(UI_TEXT_CONVERSION_FACTOR), _(UI_TEXT_SUPPLIERS_DESCRIPTION), "", "");

        table_header($th);

        $k = $j = 0; //row colour counter

        while ($myrow = db_fetch($result))
        {
			alt_table_row_color($k);

            label_cell($myrow["supp_name"]);
            amount_decimal_cell($myrow["price"]);
            label_cell($myrow["curr_code"]);
            label_cell($myrow["suppliers_uom"]);
            qty_cell($myrow['conversion_factor'], false, 'max');
            label_cell($myrow["supplier_description"]);
		 	edit_button_cell("Edit".$myrow['supplier_id'], _(UI_TEXT_EDIT));
		 	delete_button_cell("Delete".$myrow['supplier_id'], _(UI_TEXT_DELETE));
            end_row();

            $j++;
            If ($j == 12)
            {
            	$j = 1;
        		table_header($th);
            } //end of page full new headings
        } //end of while loop

        end_table();
    }
 div_end();
}

//-----------------------------------------------------------------------------------------------

$dec2 = 6;
if ($Mode =='Edit')
{
	$myrow = get_item_purchasing_data($selected_id, $_POST['stock_id']);

    $supp_name = $myrow["supp_name"];
    $_POST['price'] = price_decimal_format($myrow["price"], $dec2);
    $_POST['suppliers_uom'] = $myrow["suppliers_uom"];
    $_POST['supplier_description'] = $myrow["supplier_description"];
    $_POST['conversion_factor'] = maxprec_format($myrow["conversion_factor"]);
}

br();
hidden('selected_id', $selected_id);

start_table(TABLESTYLE2);

if ($Mode == 'Edit')
{
	hidden('supplier_id');
	label_row(_(UI_TEXT_SUPPLIER_COLON), $supp_name);
}
else
{
	supplier_list_row(_("Supplier:"), 'supplier_id', null, false, true);
	$_POST['price'] = $_POST['suppliers_uom'] = $_POST['conversion_factor'] = $_POST['supplier_description'] = "";
}
echo "<tr>";
unit_amount_cells(_("Price"), 'price', null, '', get_supplier_currency($selected_id));
echo "</tr>\n";

text_row(_("Suppliers Unit of Measure:"), 'suppliers_uom', null, 50, 51);

if (!isset($_POST['conversion_factor']) || $_POST['conversion_factor'] == "")
{
   	$_POST['conversion_factor'] = maxprec_format(1);
}
amount_row(_("Conversion Factor (to our UOM):"), 'conversion_factor', null, null, null, 'max');
text_row(_("Supplier's Code or Description:"), 'supplier_description', null, 50, 50);

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();
end_page();
