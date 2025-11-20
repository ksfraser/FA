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

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/CompanyPrefsService.php");

include_once($path_to_root . "/gl/includes/gl_db.inc");

$js = "";
if (user_use_date_picker())
	$js = get_js_date_picker();

page(_($help_context = UI_TEXT_TRIAL_BALANCE_TITLE), false, false, "", $js);

$k = 0;
$pdeb = $pcre = $cdeb = $ccre = $tdeb = $tcre = $pbal = $cbal = $tbal = 0;

//----------------------------------------------------------------------------------------------------
// Ajax updates
//
if (RequestService::getPostStatic('Show'))
{
	$Ajax->activate('balance_tbl');
}


function gl_inquiry_controls()
{
	$dim = \FA\Services\CompanyPrefsService::getUseDimensions();
    start_form();

    start_table(TABLESTYLE_NOBORDER);

	$date = DateService::todayStatic();
	if (!isset($_POST['TransToDate']))
		$_POST['TransToDate'] = DateService::endMonthStatic($date);
	if (!isset($_POST['TransFromDate']))
		$_POST['TransFromDate'] = DateService::addDaysStatic(DateService::endMonthStatic($date), -user_transaction_days());
	start_row();	
    date_cells(_(UI_TEXT_FROM_LABEL), 'TransFromDate');
	date_cells(_(UI_TEXT_TO_LABEL), 'TransToDate');
	if ($dim >= 1)
		dimensions_list_cells(_(UI_TEXT_DIMENSION_LABEL)." 1:", 'Dimension', null, true, " ", false, 1);
	if ($dim > 1)
		dimensions_list_cells(_(UI_TEXT_DIMENSION_LABEL)." 2:", 'Dimension2', null, true, " ", false, 2);
	check_cells(_(UI_TEXT_NO_ZERO_VALUES), 'NoZero', null);
	check_cells(_(UI_TEXT_ONLY_BALANCES), 'Balance', null);
	check_cells(_(UI_TEXT_GROUP_TOTALS_ONLY), 'GroupTotalOnly', null);
	submit_cells(_(UI_TEXT_SHOW_BUTTON),_(UI_TEXT_SHOW_BUTTON),'','', 'default');
	end_row();
    end_table();
    end_form();
}

//----------------------------------------------------------------------------------------------------

function display_trial_balance($type, $typename)
{
	global $path_to_root, $SysPrefs,
		 $k, $pdeb, $pcre, $cdeb, $ccre, $tdeb, $tcre, $pbal, $cbal, $tbal;

	$printtitle = 0; //Flag for printing type name

	$k = 0;

	//Get Accounts directly under this group/type
	$accounts = get_gl_accounts(null, null, $type);

	$begin = get_fiscalyear_begin_for_date($_POST['TransFromDate']);
	if (DateService::date1GreaterDate2Static($begin, $_POST['TransFromDate']))
		$begin = $_POST['TransFromDate'];
	$begin = DateService::addDaysStatic($begin, -1);

	$Apdeb=$pdeb;
	$Apcre=$pcre;
	$Acdeb=$cdeb;
	$Accre=$ccre;
	$Atdeb=$tdeb;
	$Atcre=$tcre;
	$Apbal=$pbal;
	$Acbal=$cbal;
	$Atbal=$tbal;

	while ($account = db_fetch($accounts))
	{
		//Print Type Title if it has atleast one non-zero account
		if (!$printtitle)
		{
			if (!RequestService::checkValueStatic('GroupTotalOnly'))
			{
				start_row("class='inquirybg' style='font-weight:bold'");
				label_cell(_(UI_TEXT_GROUP_LABEL)." - ".$type ." - ".$typename, "colspan=8");
				end_row();
			}
			$printtitle = 1;
		}

		// FA doesn't really clear the closed year, therefore the brought forward balance includes all the transactions from the past, even though the balance is null.
		// If we want to remove the balanced part for the past years, this option removes the common part from from the prev and tot figures.
		if (@$SysPrefs->clear_trial_balance_opening)
		{
			$open = get_balance($account["account_code"], $_POST['Dimension'], $_POST['Dimension2'], $begin,  $begin, false, true);
			$offset = min($open['debit'], $open['credit']);
		} else
			$offset = 0;

		$prev = get_balance($account["account_code"], $_POST['Dimension'], $_POST['Dimension2'], $begin, $_POST['TransFromDate'], false, false);
		$curr = get_balance($account["account_code"], $_POST['Dimension'], $_POST['Dimension2'], $_POST['TransFromDate'], $_POST['TransToDate'], true, true);
		$tot = get_balance($account["account_code"], $_POST['Dimension'], $_POST['Dimension2'], $begin, $_POST['TransToDate'], false, true);
		if (RequestService::checkValueStatic("NoZero") && !$prev['balance'] && !$curr['balance'] && !$tot['balance'])
			continue;
		if (!RequestService::checkValueStatic('GroupTotalOnly'))
		{
			alt_table_row_color($k);

			$url = "<a href='$path_to_root/gl/inquiry/gl_account_inquiry.php?TransFromDate=" . $_POST["TransFromDate"] . "&TransToDate=" . $_POST["TransToDate"] . "&account=" . $account["account_code"] . "&Dimension=" . $_POST["Dimension"] . "&Dimension2=" . $_POST["Dimension2"] . "'>" . $account["account_code"] . "</a>";

			label_cell($url);
			label_cell($account["account_name"]);
		}
		if (RequestService::checkValueStatic('Balance'))
		{
			if (!RequestService::checkValueStatic('GroupTotalOnly'))
			{
				display_debit_or_credit_cells($prev['balance']);
				display_debit_or_credit_cells($curr['balance']);
				display_debit_or_credit_cells($tot['balance']);
			}
		}
		else
		{
			if (!RequestService::checkValueStatic('GroupTotalOnly'))
			{
				amount_cell($prev['debit']-$offset);
				amount_cell($prev['credit']-$offset);
				amount_cell($curr['debit']);
				amount_cell($curr['credit']);
				amount_cell($tot['debit']-$offset);
				amount_cell($tot['credit']-$offset);
			}
			$pdeb += $prev['debit'];
			$pcre += $prev['credit'];
			$cdeb += $curr['debit'];
			$ccre += $curr['credit'];
			$tdeb += $tot['debit'];
			$tcre += $tot['credit'];
		}
		$pbal += $prev['balance'];
		$cbal += $curr['balance'];
		$tbal += $tot['balance'];
		end_row();
	}

	//Get Account groups/types under this group/type
	$result = get_account_types(false, false, $type);
	while ($accounttype=db_fetch($result))
	{
		//Print Type Title if has sub types and not previously printed
		if (!$printtitle)
		{
			start_row("class='inquirybg' style='font-weight:bold'");
			label_cell(_(UI_TEXT_GROUP_LABEL)." - ".$type ." - ".$typename, "colspan=8");
			end_row();
			$printtitle = 1;

		}
		display_trial_balance($accounttype["id"], $accounttype["name"].' ('.$typename.')');
	}

	start_row("class='inquirybg' style='font-weight:bold'");
	if (!RequestService::checkValueStatic('GroupTotalOnly'))
		label_cell(_(UI_TEXT_TOTAL_LABEL) ." - ".$typename, "colspan=2");
	else
		label_cell(" - ".$typename, "colspan=2");


	if (!RequestService::checkValueStatic('Balance'))
	{
		amount_cell($pdeb-$Apdeb );
		amount_cell($pcre-$Apcre);
		amount_cell($cdeb-$Acdeb );
		amount_cell($ccre-$Accre );
		amount_cell($tdeb-$Atdeb );
		amount_cell($tcre-$Atcre);
	}
	else
	{
		display_debit_or_credit_cells($pbal-$Apbal);
		display_debit_or_credit_cells($cbal-$Acbal );
		display_debit_or_credit_cells($tbal-$Atbal);
	}
	end_row();
}

//----------------------------------------------------------------------------------------------------

gl_inquiry_controls();

if (isset($_POST['TransFromDate']))
{
	$row = DateService::getCurrentFiscalYearStatic();
	if (DateService::date1GreaterDate2Static($_POST['TransFromDate'], DateService::sql2dateStatic($row['end'])))
	{
		UiMessageService::displayError(_(UI_TEXT_FROM_DATE_ERROR));
		set_focus('TransFromDate');
		return;
	}
}
div_start('balance_tbl');
if (!isset($_POST['Dimension']))
	$_POST['Dimension'] = 0;
if (!isset($_POST['Dimension2']))
	$_POST['Dimension2'] = 0;
start_table(TABLESTYLE);
$tableheader =  "<tr>
	<td rowspan=2 class='tableheader'>" . _(UI_TEXT_ACCOUNT_LABEL) . "</td>
	<td rowspan=2 class='tableheader'>" . _(UI_TEXT_ACCOUNT_NAME_LABEL) . "</td>
	<td colspan=2 class='tableheader'>" . _(UI_TEXT_BROUGHT_FORWARD_LABEL) . "</td>
	<td colspan=2 class='tableheader'>" . _(UI_TEXT_THIS_PERIOD_LABEL) . "</td>
	<td colspan=2 class='tableheader'>" . _(UI_TEXT_BALANCE_LABEL) . "</td>
	</tr><tr>
	<td class='tableheader'>" . _(UI_TEXT_DEBIT_LABEL) . "</td>
	<td class='tableheader'>" . _(UI_TEXT_CREDIT_LABEL) . "</td>
	<td class='tableheader'>" . _(UI_TEXT_DEBIT_LABEL) . "</td>
	<td class='tableheader'>" . _(UI_TEXT_CREDIT_LABEL) . "</td>
	<td class='tableheader'>" . _(UI_TEXT_DEBIT_LABEL) . "</td>
	<td class='tableheader'>" . _(UI_TEXT_CREDIT_LABEL) . "</td>
	</tr>";

echo $tableheader;

//display_trial_balance();

$classresult = get_account_classes(false);
while ($class = db_fetch($classresult))
{
	start_row("class='inquirybg' style='font-weight:bold'");
	label_cell(_(UI_TEXT_CLASS_LABEL)." - ".$class['cid'] ." - ".$class['class_name'], "colspan=8");
	end_row();

	//Get Account groups/types under this group/type with no parents
	$typeresult = get_account_types(false, $class['cid'], -1);
	while ($accounttype=db_fetch($typeresult))
	{
		display_trial_balance($accounttype["id"], $accounttype["name"]);
	}
}

if (!RequestService::checkValueStatic('Balance'))
{
	start_row("class='inquirybg' style='font-weight:bold'");
	label_cell(_(UI_TEXT_TOTAL_LABEL) ." - ".$_POST['TransToDate'], "colspan=2");
	amount_cell($pdeb);
	amount_cell($pcre);
	amount_cell($cdeb);
	amount_cell($ccre);
	amount_cell($tdeb);
	amount_cell($tcre);
	end_row();
}
start_row("class='inquirybg' style='font-weight:bold'");
label_cell(_(UI_TEXT_ENDING_BALANCE_LABEL) ." - ".$_POST['TransToDate'], "colspan=2");
display_debit_or_credit_cells($pbal);
display_debit_or_credit_cells($cbal);
display_debit_or_credit_cells($tbal);
end_row();

end_table(1);
if (($pbal = round2($pbal, \FA\UserPrefsCache::getPriceDecimals())) != 0 && $_POST['Dimension'] == 0 && $_POST['Dimension2'] == 0)
	\FA\Services\UiMessageService::displayWarning(_(UI_TEXT_OPENING_BALANCE_WARNING));
div_end();

//----------------------------------------------------------------------------------------------------

end_page();

