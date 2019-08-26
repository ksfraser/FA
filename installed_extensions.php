<?php

/* List of installed additional extensions. If extensions are added to the list manually
	make sure they have unique and so far never used extension_ids as a keys,
	and $next_extension_id is also updated. More about format of this file yo will find in 
	FA extension system documentation.
*/

$next_extension_id = 44; // unique id for next installed extension

$installed_extensions = array (
  0 => 
  array (
    'name' => 'English Canadian COA - General',
    'package' => 'chart_en_CA-general',
    'version' => '2.3.0-5',
    'type' => 'chart',
    'active' => false,
    'path' => 'sql',
    'sql' => 'en_CA-general.sql',
  ),
  1 => 
  array (
    'name' => '8 digit GAAP compatible American chart of accounts',
    'package' => 'chart_en_US-GAAP',
    'version' => '2.3.0-3',
    'type' => 'chart',
    'active' => false,
    'path' => 'sql',
    'sql' => 'en_US-GAAP.sql',
  ),
  2 => 
  array (
    'name' => 'Inventory Items CSV Import',
    'package' => 'import_items',
    'version' => '2.3.0-2',
    'type' => 'extension',
    'active' => true,
    'path' => 'modules/import_items',
  ),
  3 => 
  array (
    'name' => 'Requisitions',
    'package' => 'requisitions',
    'version' => '2.3.13-3',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/requisitions',
  ),
  4 => 
  array (
    'name' => 'zen_import',
    'package' => 'zen_import',
    'version' => '2.3.15-1',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/zen_import',
  ),
  5 => 
  array (
    'name' => 'Report Generator',
    'package' => 'repgen',
    'version' => '2.3.9-4',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/repgen',
  ),
  6 => 
  array (
    'name' => 'Tax inquiry and detailed report on cash basis',
    'package' => 'rep_tax_cash_basis',
    'version' => '2.3.7-4',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/rep_tax_cash_basis',
  ),
  7 => 
  array (
    'name' => 'Bank Statement w/ Reconcile',
    'package' => 'rep_statement_reconcile',
    'version' => '2.3.3-3',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/rep_statement_reconcile',
  ),
  8 => 
  array (
    'name' => 'Sales Summary Report',
    'package' => 'rep_sales_summary',
    'version' => '2.3.3-3',
    'type' => 'extension',
    'active' => true,
    'path' => 'modules/rep_sales_summary',
  ),
  9 => 
  array (
    'name' => 'Inventory History',
    'package' => 'rep_inventory_history',
    'version' => '2.3.2-1',
    'type' => 'extension',
    'active' => true,
    'path' => 'modules/rep_inventory_history',
  ),
  10 => 
  array (
    'name' => 'Dated Stock Sheet',
    'package' => 'rep_dated_stock',
    'version' => '2.3.3-3',
    'type' => 'extension',
    'active' => true,
    'path' => 'modules/rep_dated_stock',
  ),
  11 => 
  array (
    'name' => 'Check Printing based on Tu Nguyen, Canada',
    'package' => 'rep_cheque_print',
    'version' => '2.3.0-1',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/rep_cheque_print',
  ),
  12 => 
  array (
    'name' => 'Cash Flow Statement Report',
    'package' => 'rep_cash_flow_statement',
    'version' => '2.3.0-1',
    'type' => 'extension',
    'active' => true,
    'path' => 'modules/rep_cash_flow_statement',
  ),
  13 => 
  array (
    'name' => 'Annual expense breakdown report',
    'package' => 'rep_annual_expense_breakdown',
    'version' => '2.3.0-1',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/rep_annual_expense_breakdown',
  ),
  14 => 
  array (
    'name' => 'Annual balance breakdown report',
    'package' => 'rep_annual_balance_breakdown',
    'version' => '2.3.0-1',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/rep_annual_balance_breakdown',
  ),
  15 => 
  array (
    'name' => 'Import Transactions',
    'package' => 'import_transactions',
    'version' => '2.3.22-5',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/import_transactions',
  ),
  16 => 
  array (
    'name' => 'Company Dashboard',
    'package' => 'dashboard',
    'version' => '2.3.15-5',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/dashboard',
  ),
  17 => 
  array (
    'name' => 'Asset register',
    'package' => 'asset_register',
    'version' => '2.3.3-10',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/asset_register',
  ),
  18 => 
  array (
    'package' => 'import_transactions-master',
    'name' => 'import_transactions-master',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/import_transactions-master',
    'active' => false,
  ),
  19 => 
  array (
    'package' => 'bank_import-master',
    'name' => 'bank_import-master',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/bank_import-master',
    'active' => false,
  ),
  20 => 
  array (
    'name' => 'Import Multiple Journal Entries',
    'package' => 'import_multijournalentries',
    'version' => '2.3.0-7',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/import_multijournalentries',
  ),
  21 => 
  array (
    'name' => 'Import Paypal transactions',
    'package' => 'import_paypal',
    'version' => '2.3.10-3',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/import_paypal',
  ),
  22 => 
  array (
    'package' => 'api',
    'name' => 'api',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/api',
    'active' => true,
  ),
  23 => 
  array (
    'name' => 'osCommerce Order and Customer Import Module',
    'package' => 'osc_orders',
    'version' => '2.3.0-3',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/osc_orders',
  ),
  24 => 
  array (
    'package' => 'fa_soap_web_services',
    'name' => 'fa_soap_web_services',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/fa_soap_web_services',
    'active' => false,
  ),
  25 => 
  array (
    'package' => 'FrontAccountingSimpleAPI-master',
    'name' => 'FrontAccountingSimpleAPI-master',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/FrontAccountingSimpleAPI-master',
    'active' => false,
  ),
  26 => 
  array (
    'package' => 'mobile',
    'name' => 'mobile',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/mobile',
    'active' => false,
  ),
  27 => 
  array (
    'name' => 'Auth_LDAP',
    'package' => 'auth_ldap',
    'version' => '2.3.5-2',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/auth_ldap',
  ),
  28 => 
  array (
    'package' => 'CALC_PRICING',
    'name' => 'CALC_PRICING',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/CALC_PRICING',
    'active' => false,
  ),
  29 => 
  array (
    'package' => 'EXPORT_OSPOS',
    'name' => 'EXPORT_OSPOS',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/EXPORT_OSPOS',
    'active' => false,
  ),
  30 => 
  array (
    'package' => 'EXPORT_WOO',
    'name' => 'EXPORT_WOO',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/EXPORT_WOO',
    'active' => false,
  ),
  31 => 
  array (
    'package' => 'Inventory',
    'name' => 'Inventory',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/Inventory',
    'active' => false,
  ),
  32 => 
  array (
    'package' => 'WOO',
    'name' => 'WOO',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/WOO',
    'active' => false,
  ),
  33 => 
  array (
    'package' => 'coast_export',
    'name' => 'coast_export',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/coast_export',
    'active' => false,
  ),
  34 => 
  array (
    'package' => 'generate_EAN',
    'name' => 'generate_EAN',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/generate_EAN',
    'active' => false,
  ),
  35 => 
  array (
    'package' => 'vtiger_import',
    'name' => 'vtiger_import',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/vtiger_import',
    'active' => false,
  ),
  36 => 
  array (
    'package' => 'ksf_data_dictionary',
    'name' => 'ksf_data_dictionary',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/ksf_data_dictionary',
    'active' => true,
  ),
   37 => 
  array (
    'package' => 'ksf_generate_catalogue',
    'name' => 'ksf_generate_catalogue',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/ksf_generate_catalogue',
    'active' => true,
  ),
   38 => 
  array (
    'package' => 'ksf_qoh',
    'name' => 'ksf_qoh',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/ksf_qoh',
    'active' => true,
  ),
  39 => 
  array (
    'package' => 'ksf_expense_claims',
    'name' => 'ksf_expense_claims',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/ksf_expense_claims',
    'active' => true,
  ),
  40 => 
  array (
    'package' => 'EXPORT_WOO_PROd',
    'name' => 'EXPORT_WOO_PROD',
    'version' => '-',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/EXPORT_WOO_PROD',
    'active' => true,
  ),
  41 => 
  array (
    'package' => 'ksf_stockid_search_replace',
    'name' => 'ksf_stockid_search_replace',
    'version' => '1',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/ksf_stockid_search_replace',
    'active' => true,
  ),
  42 => 
  array (
    'package' => 'ksf_missing_image',
    'name' => 'ksf_missing_image',
    'version' => '1',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/ksf_missing_image',
    'active' => true,
  ),
 43 => 
  array (
    'package' => 'ksf_payment_destinations',
    'name' => 'ksf_payment_destinations',
    'version' => '1',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/ksf_payment_destinations',
    'active' => true,
    //'active' => false,
  ),
)
?>
