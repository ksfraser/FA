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
$page_security = 'SA_BUDGETENTRY';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui_strings.php");
add_js_file('budget.js');

page(_($help_context = UI_TEXT_BUDGET_ENTRY));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/CompanyPrefsService.php");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");


check_db_has_gl_account_groups(_(UI_TEXT_THERE_ARE_NO_ACCOUNT_GROUPS_DEFINED_PLEASE_DEFINE_AT_LEAST_ONE_ACCOUNT_GROUP_BEFORE_ENTERING_ACCOUNTS));

//-------------------------------------------------------------------------------------

if (isset($_POST['add']) || isset($_POST['delete']))
{
	begin_transaction();

	for ($i = 0, $da = $_POST['begin']; DateService::date1GreaterDate2Static($_POST['end'], $da); $i++)
	{
		if (isset($_POST['add']))
			add_update_gl_budget_trans($da, $_POST['account'], $_POST['dim1'], $_POST['dim2'], RequestService::inputNumStatic('amount'.$i));
		else
			delete_gl_budget_trans($da, $_POST['account'], $_POST['dim1'], $_POST['dim2']);
		$da = DateService::addMonthsStatic($da, 1);
	}
	commit_transaction();

	if (isset($_POST['add']))
		display_notification_centered(_(UI_TEXT_THE_BUDGET_HAS_BEEN_SAVED));
	else
		display_notification_centered(_(UI_TEXT_THE_BUDGET_HAS_BEEN_DELETED));

	$Ajax->activate('budget_tbl');
}
if (isset($_POST['submit']) || isset($_POST['update']))
	$Ajax->activate('budget_tbl');

//-------------------------------------------------------------------------------------

start_form();

if (db_has_gl_accounts())
{
	$dim = \FA\Services\CompanyPrefsService::getUseDimensions();
	start_table(TABLESTYLE2);
	fiscalyears_list_row(_(UI_TEXT_FISCAL_YEAR_LABEL), 'fyear', null);
	gl_all_accounts_list_row(_(UI_TEXT_ACCOUNT_CODE_LABEL), 'account', null);
	if (!isset($_POST['dim1']))
		$_POST['dim1'] = 0;
	if (!isset($_POST['dim2']))
		$_POST['dim2'] = 0;
    if ($dim == 2)
    {
		dimensions_list_row(_(UI_TEXT_DIMENSION_1), 'dim1', $_POST['dim1'], true, null, false, 1);
		dimensions_list_row(_(UI_TEXT_DIMENSION_2), 'dim2', $_POST['dim2'], true, null, false, 2);
	}
	elseif ($dim == 1)
	{
		dimensions_list_row(_(UI_TEXT_DIMENSION), 'dim1', $_POST['dim1'], true, null, false, 1);
		hidden('dim2', 0);
	}
	else
	{
		hidden('dim1', 0);
		hidden('dim2', 0);
	}
	submit_row('submit', _(UI_TEXT_GET), true, '', '', true);
	end_table(1);
	div_start('budget_tbl');
	start_table(TABLESTYLE2);
	$showdims = (($dim == 1 && $_POST['dim1'] == 0) ||
		($dim == 2 && $_POST['dim1'] == 0 && $_POST['dim2'] == 0));
	if ($showdims)
		$th = array(_(UI_TEXT_PERIOD), _(UI_TEXT_AMOUNT), _(UI_TEXT_DIM_INCL), _(UI_TEXT_LAST_YEAR));
	else
		$th = array(_(UI_TEXT_PERIOD), _(UI_TEXT_AMOUNT), _(UI_TEXT_LAST_YEAR));
	table_header($th);
	$year = $_POST['fyear'];
	if (RequestService::getPostStatic('update') == '') {
		$fyear = get_fiscalyear($year);
		$_POST['begin'] = DateService::sql2dateStatic($fyear['begin']);
		$_POST['end'] = DateService::sql2dateStatic($fyear['end']);
	}
	hidden('begin');
	hidden('end');
	$total = $btotal = $ltotal = 0;
	for ($i = 0, $date_ = $_POST['begin']; DateService::date1GreaterDate2Static($_POST['end'], $date_); $i++)
	{
		start_row();
		if (RequestService::getPostStatic('update') == '')
			$_POST['amount'.$i] = FormatService::numberFormat2(get_only_budget_trans_from_to(
				$date_, $date_, $_POST['account'], $_POST['dim1'], $_POST['dim2']), 0);

		label_cell($date_);
		amount_cells(null, 'amount'.$i, null, 15, null, 0);
		if ($showdims)
		{
			$d = get_budget_trans_from_to($date_, $date_, $_POST['account'], $_POST['dim1'], $_POST['dim2']);
			label_cell(FormatService::numberFormat2($d, 0), "nowrap align=right");
			$btotal += $d;
		}
		$lamount = get_gl_trans_from_to(DateService::addYearsStatic($date_, -1), DateService::addYearsStatic(DateService::endMonthStatic($date_), -1), $_POST['account'], $_POST['dim1'], $_POST['dim2']);
		$total += RequestService::inputNumStatic('amount'.$i);
		$ltotal += $lamount;
		label_cell(FormatService::numberFormat2($lamount, 0), "nowrap align=right");
		$date_ = DateService::addMonthsStatic($date_, 1);
		end_row();
	}
	start_row();
	label_cell("<b>"._(UI_TEXT_TOTAL)."</b>");
	label_cell(FormatService::numberFormat2($total, 0), 'align=right style="font-weight:bold"', 'Total');
	if ($showdims)
		label_cell("<b>".FormatService::numberFormat2($btotal, 0)."</b>", "nowrap align=right");
	label_cell("<b>".FormatService::numberFormat2($ltotal, 0)."</b>", "nowrap align=right");
	end_row();
	end_table(1);
	div_end();
	submit_center_first('update', _(UI_TEXT_UPDATE), '', null);
	submit('add', _(UI_TEXT_SAVE), true, '', 'default');
	submit_center_last('delete', _(UI_TEXT_DELETE), '', true);
}
end_form();

end_page();

