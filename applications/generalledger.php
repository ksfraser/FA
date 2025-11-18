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
include_once("includes/ui_strings.php");

class general_ledger_app extends application
{
	function __construct()
	{
		parent::__construct("GL", _($this->help_context = "&Banking and General Ledger"));

		$this->add_module(_(UI_TEXT_TRANSACTIONS));
		$this->add_lapp_function(0, _(UI_TEXT_PAYMENTS),
			"gl/gl_bank.php?NewPayment=Yes", 'SA_PAYMENT', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_DEPOSITS),
			"gl/gl_bank.php?NewDeposit=Yes", 'SA_DEPOSIT', MENU_TRANSACTION);
		$this->add_lapp_function(0, _(UI_TEXT_BANK_ACCOUNT_TRANSFERS),
			"gl/bank_transfer.php?", 'SA_BANKTRANSFER', MENU_TRANSACTION);
		$this->add_rapp_function(0, _(UI_TEXT_JOURNAL_ENTRY),
			"gl/gl_journal.php?NewJournal=Yes", 'SA_JOURNALENTRY', MENU_TRANSACTION);
		$this->add_rapp_function(0, _(UI_TEXT_BUDGET_ENTRY),
			"gl/gl_budget.php?", 'SA_BUDGETENTRY', MENU_TRANSACTION);
		$this->add_rapp_function(0, _(UI_TEXT_RECONCILE_BANK_ACCOUNT),
			"gl/bank_account_reconcile.php?", 'SA_RECONCILE', MENU_TRANSACTION);
		$this->add_rapp_function(0, _(UI_TEXT_REVENUE_COSTS_ACCRUALS),
			"gl/accruals.php?", 'SA_ACCRUALS', MENU_TRANSACTION);

		$this->add_module(_(UI_TEXT_INQUIRIES_AND_REPORTS));
		$this->add_lapp_function(1, _(UI_TEXT_JOURNAL_INQUIRY),
			"gl/inquiry/journal_inquiry.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
		$this->add_lapp_function(1, _(UI_TEXT_GL_INQUIRY),
			"gl/inquiry/gl_account_inquiry.php?", 'SA_GLTRANSVIEW', MENU_INQUIRY);
		$this->add_lapp_function(1, _(UI_TEXT_BANK_ACCOUNT_INQUIRY),
			"gl/inquiry/bank_inquiry.php?", 'SA_BANKTRANSVIEW', MENU_INQUIRY);
		$this->add_lapp_function(1, _(UI_TEXT_TAX_INQUIRY),
			"gl/inquiry/tax_inquiry.php?", 'SA_TAXREP', MENU_INQUIRY);

		$this->add_rapp_function(1, _(UI_TEXT_TRIAL_BALANCE),
			"gl/inquiry/gl_trial_balance.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
		$this->add_rapp_function(1, _(UI_TEXT_BALANCE_SHEET_DRILLDOWN),
			"gl/inquiry/balance_sheet.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
		$this->add_rapp_function(1, _(UI_TEXT_PROFIT_AND_LOSS_DRILLDOWN),
			"gl/inquiry/profit_loss.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
		$this->add_rapp_function(1, _(UI_TEXT_BANKING_REPORTS),
			"reporting/reports_main.php?Class=5", 'SA_BANKREP', MENU_REPORT);
		$this->add_rapp_function(1, _(UI_TEXT_GENERAL_LEDGER_REPORTS),
			"reporting/reports_main.php?Class=6", 'SA_GLREP', MENU_REPORT);

		$this->add_module(_(UI_TEXT_MAINTENANCE));
		$this->add_lapp_function(2, _(UI_TEXT_BANK_ACCOUNTS),
			"gl/manage/bank_accounts.php?", 'SA_BANKACCOUNT', MENU_MAINTENANCE);
		$this->add_lapp_function(2, _(UI_TEXT_QUICK_ENTRIES),
			"gl/manage/gl_quick_entries.php?", 'SA_QUICKENTRY', MENU_MAINTENANCE);
		$this->add_lapp_function(2, _(UI_TEXT_ACCOUNT_TAGS),
			"admin/tags.php?type=account", 'SA_GLACCOUNTTAGS', MENU_MAINTENANCE);
		$this->add_lapp_function(2, "","");
		$this->add_lapp_function(2, _(UI_TEXT_CURRENCIES),
			"gl/manage/currencies.php?", 'SA_CURRENCY', MENU_MAINTENANCE);
		$this->add_lapp_function(2, _(UI_TEXT_EXCHANGE_RATES),
			"gl/manage/exchange_rates.php?", 'SA_EXCHANGERATE', MENU_MAINTENANCE);

		$this->add_rapp_function(2, _(UI_TEXT_GL_ACCOUNTS),
			"gl/manage/gl_accounts.php?", 'SA_GLACCOUNT', MENU_ENTRY);
		$this->add_rapp_function(2, _(UI_TEXT_GL_ACCOUNT_GROUPS),
			"gl/manage/gl_account_types.php?", 'SA_GLACCOUNTGROUP', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_GL_ACCOUNT_CLASSES),
			"gl/manage/gl_account_classes.php?", 'SA_GLACCOUNTCLASS', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_CLOSING_GL_TRANSACTIONS),
			"gl/manage/close_period.php?", 'SA_GLCLOSE', MENU_MAINTENANCE);
		$this->add_rapp_function(2, _(UI_TEXT_REVALUATION_OF_CURRENCY_ACCOUNTS),
			"gl/manage/revaluate_currencies.php?", 'SA_EXCHANGERATE', MENU_MAINTENANCE);

		$this->add_extensions();
	}
}


