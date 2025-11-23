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
$page_security = 'SA_TAXREP';
$path_to_root="../..";
include_once($path_to_root . "/includes/session.inc");


include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");

$js = '';
set_focus('account');
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(_($help_context = UI_TEXT_TAX_INQUIRY_TITLE), false, false, '', $js);

//----------------------------------------------------------------------------------------------------
// Ajax updates
//
if (RequestService::getPostStatic('Show')) 
{
	$Ajax->activate('trans_tbl');
}

if (RequestService::getPostStatic('TransFromDate') == "" && RequestService::getPostStatic('TransToDate') == "")
{
	$date = DateService::todayStatic();
	$edate = DateService::addMonthsStatic($date, -\FA\Services\CompanyPrefsService::getCompanyPref('tax_last'));
	$edate = DateService::endMonthStatic($edate);
	$bdate = DateService::beginMonthStatic($edate);
	$bdate = DateService::addMonthsStatic($bdate, -\FA\Services\CompanyPrefsService::getCompanyPref('tax_prd') + 1);
	$_POST["TransFromDate"] = $bdate;
	$_POST["TransToDate"] = $edate;
}	

//----------------------------------------------------------------------------------------------------

function tax_inquiry_controls()
{
    start_form();

    start_table(TABLESTYLE_NOBORDER);
	start_row();

	date_cells(_(UI_TEXT_FROM_LOWER_LABEL), 'TransFromDate', '', null, -user_transaction_days());
	date_cells(_(UI_TEXT_TO_LOWER_LABEL), 'TransToDate');
	submit_cells('Show',_(UI_TEXT_SHOW_BUTTON),'','', 'default');

    end_row();

	end_table();

    end_form();
}

//----------------------------------------------------------------------------------------------------

function show_results()
{
    /*Now get the transactions  */
	div_start('trans_tbl');
	start_table(TABLESTYLE);

	$th = array(_(UI_TEXT_TYPE_HEADER), _(UI_TEXT_DESCRIPTION_HEADER), _(UI_TEXT_AMOUNT), _(UI_TEXT_OUTPUTS_LABEL)."/"._(UI_TEXT_INPUTS_LABEL));
	table_header($th);
	$k = 0;
	$total = 0;

	$taxes = get_tax_summary($_POST['TransFromDate'], $_POST['TransToDate']);

	while ($tx = db_fetch($taxes))
	{

		$payable = $tx['payable'];
		$collectible = -$tx['collectible'];
		$net = $collectible + $payable;
		$total += $net;
		alt_table_row_color($k);
		label_cell($tx['name'] . " " . $tx['rate'] . "%");
		label_cell(_(UI_TEXT_CHARGED_ON_SALES) . " (" . _(UI_TEXT_OUTPUT_TAX)."):");
		amount_cell($payable);
		amount_cell($tx['net_output']);
		end_row();
		alt_table_row_color($k);
		label_cell($tx['name'] . " " . $tx['rate'] . "%");
		label_cell(_(UI_TEXT_PAID_ON_PURCHASES) . " (" . _(UI_TEXT_INPUT_TAX)."):");
		amount_cell($collectible);
		amount_cell(-$tx['net_input']);
		end_row();
		alt_table_row_color($k);
		label_cell("<b>".$tx['name'] . " " . $tx['rate'] . "%</b>");
		label_cell("<b>"._(UI_TEXT_NET_PAYABLE_OR_COLLECTIBLE) . ":</b>");
		amount_cell($net, true);
		label_cell("");
		end_row();
	}	
	alt_table_row_color($k);
	label_cell("");
	label_cell("<b>"._(UI_TEXT_TOTAL_PAYABLE_OR_REFUND) . ":</b>");
	amount_cell($total, true);
	label_cell("");
	end_row();

	end_table(2);
	div_end();
}

//----------------------------------------------------------------------------------------------------

tax_inquiry_controls();

show_results();

//----------------------------------------------------------------------------------------------------

end_page();

