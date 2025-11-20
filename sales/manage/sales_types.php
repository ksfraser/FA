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
$page_security = 'SA_SALESTYPES';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

page(_($help_context = UI_TEXT_SALES_TYPES));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");

simple_page_mode(true);
//----------------------------------------------------------------------------------------------------

function can_process()
{
	if (strlen($_POST['sales_type']) == 0)
	{
		UiMessageService::displayError(_(UI_TEXT_THE_SALES_TYPE_DESCRIPTION_CANNOT_BE_EMPTY));
		set_focus('sales_type');
		return false;
	}

	if (!check_num('factor', 0))
	{
		UiMessageService::displayError(_(UI_TEXT_CALCULATION_FACTOR_MUST_BE_VALID_POSITIVE_NUMBER));
		set_focus('factor');
		return false;
	}
	return true;
}

//----------------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' && can_process())
{
	add_sales_type($_POST['sales_type'], RequestService::checkValueStatic('tax_included'),
	    RequestService::inputNumStatic('factor'));
	\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_NEW_SALES_TYPE_HAS_BEEN_ADDED));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------------

if ($Mode=='UPDATE_ITEM' && can_process())
{

	update_sales_type($selected_id, $_POST['sales_type'], RequestService::checkValueStatic('tax_included'),
	     RequestService::inputNumStatic('factor'));
	\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_SELECTED_SALES_TYPE_HAS_BEEN_UPDATED));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'debtor_trans'
	
	if (key_in_foreign_table($selected_id, 'debtor_trans', 'tpe'))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THIS_SALE_TYPE_BECAUSE_CUSTOMER_TRANSACTIONS_HAVE_BEEN_CREATED_USING_THIS_SALES_TYPE));

	}
	else
	{
		if (key_in_foreign_table($selected_id, 'debtors_master', 'sales_type'))
		{
			UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_THIS_SALE_TYPE_BECAUSE_CUSTOMERS_ARE_CURRENTLY_SET_UP_TO_USE_THIS_SALES_TYPE));
		}
		else
		{
			delete_sales_type($selected_id);
			display_notification(_(UI_TEXT_SELECTED_SALES_TYPE_HAS_BEEN_DELETED));
		}
	} //end if sales type used in debtor transactions or in customers set up
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = RequestService::getPostStatic('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}
//----------------------------------------------------------------------------------------------------

$result = get_all_sales_types(RequestService::checkValueStatic('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='30%'");

$th = array (_(UI_TEXT_TYPE_NAME), _(UI_TEXT_FACTOR), _(UI_TEXT_TAX_INCL), '','');
inactive_control_column($th);
table_header($th);
$k = 0;
$base_sales = get_base_sales_type();

while ($myrow = db_fetch($result))
{
	if ($myrow["id"] == $base_sales)
	    start_row("class='overduebg'");
	else
	    alt_table_row_color($k);
	label_cell($myrow["sales_type"]);
	$f = FormatService::numberFormat2($myrow["factor"],4);
	if($myrow["id"] == $base_sales) $f = "<I>"._(UI_TEXT_BASE)."</I>";
	label_cell($f);
	label_cell($myrow["tax_included"] ? _(UI_TEXT_YES) : _(UI_TEXT_NO), 'align=center');
	inactive_control_cell($myrow["id"], $myrow["inactive"], 'sales_types', 'id');
 	edit_button_cell("Edit".$myrow['id'], _(UI_TEXT_EDIT));
 	delete_button_cell("Delete".$myrow['id'], _(UI_TEXT_DELETE));
	end_row();
}
inactive_control_row($th);
end_table();

display_note(_(UI_TEXT_MARKED_SALES_TYPE_IS_THE_COMPANY_BASE_PRICELIST_FOR_PRICES_CALCULATIONS), 0, 0, "class='overduefg'");

//----------------------------------------------------------------------------------------------------

 if (!isset($_POST['tax_included']))
	$_POST['tax_included'] = 0;
 if (!isset($_POST['base']))
	$_POST['base'] = 0;

start_table(TABLESTYLE2);

if ($selected_id != -1)
{

 	if ($Mode == 'Edit') {
		$myrow = get_sales_type($selected_id);

		$_POST['sales_type']  = $myrow["sales_type"];
		$_POST['tax_included']  = $myrow["tax_included"];
		$_POST['factor']  = FormatService::numberFormat2($myrow["factor"],4);
	}
	hidden('selected_id', $selected_id);
} else {
		$_POST['factor']  = FormatService::numberFormat2(1,4);
}

text_row_ex(_(UI_TEXT_SALES_TYPE_NAME).':', 'sales_type', 20);
amount_row(_(UI_TEXT_CALCULATION_FACTOR).':', 'factor', null, null, null, 4);
check_row(_(UI_TEXT_TAX_INCLUDED).':', 'tax_included', $_POST['tax_included']);

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();

