<?php
/**********************************************
Name: EXPORT OSPOS
modified for COAST 1.5.1 and FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/

$page_security = 'SA_EXPORTOSPOS';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/EXPORT_OSPOS');

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/modules/EXPORT_OSPOS/class.EXPORT_OSPOS.php");

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 0;  // Change to 1 for debug messages
$prefsDB = "EXPORT_OSPOS_prefs";


$EXPORTc = new EXPORT_OSPOS( $prefsDB );
$found = $EXPORTc->is_installed();
$EXPORTc->set_var( 'found', $found );
$EXPORTc->set_var( 'help_context', "EXPORT OSPOS Interface" );
$EXPORTc->set_var( 'redirect_to', "EXPORT_OSPOS.php" );

$EXPORTc->run();


?>
