<?php
/**********************************************
Name: COAST purchase order export
modified for COAST 1.5.1 and FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/

$page_security = 'SA_CALC_PRICING';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/CALC_PRICING');	//for language files

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/modules/CALC_PRICING/class.CALC_PRICING.php");

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 1;  // Change to 1 for debug messages
$prefsDB = "CALC_PRICING_prefs";

$calc = new CALC_PRICING( "defiant.ksfraser.com", "fhs", "fhs", "fhs", "CALC_PRICING_prefs" );
$found = $calc->is_installed();
$calc->set_var( 'found', $found );
$calc->set_var( 'help_context', "Calculate Pricing Interface" );
$calc->set_var( 'redirect_to', "CALC_PRICING.php" );

$calc->run();

?>
