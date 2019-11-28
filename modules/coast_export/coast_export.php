<?php
/**********************************************
Name: COAST purchase order export
modified for COAST 1.5.1 and FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/

$page_security = 'SA_COASTEXPORT';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/coast_export');

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/modules/coast_export/class.coast_orders.php");

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 0;  // Change to 1 for debug messages
$prefsDB = "coast_export_prefs";


$coastc = new coast_orders( "defiant.ksfraser.com", "fhs", "fhs", "fhs", "coast_export_prefs" );
$found = $coastc->is_installed();
$coastc->set_var( 'found', $found );
$coastc->set_var( 'help_context', "Coast Music Interface" );
$coastc->set_var( 'redirect_to', "coast_export.php" );

$coastc->run();


?>
