<?php
/**********************************************
Name: COAST purchase order EAN
modified for COAST 1.5.1 and FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/

$page_security = 'SA_GENERATEEAN';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/generate_EAN');

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/modules/generate_EAN/class.generate_EAN.php");

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 0;  // Change to 1 for debug messages
$prefsDB = "generate_EAN_prefs";


$generatec = new generate_EAN( $prefsDB );
$found = $generatec->is_installed();
$generatec->set_var( 'found', $found );
$generatec->set_var( 'help_context', "Generate EAN Interface" );
$generatec->set_var( 'redirect_to', "generate_EAN.php" );

$generatec->run();


?>
