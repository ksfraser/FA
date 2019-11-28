<?php
/**********************************************
Name: 
for FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/
$path_to_root = '../../';	//Needed by Sessions etc.
$page_security = 'SA_ksf_shipping';
include( __DIR__ . "/../../includes/session.inc");
add_access_extensions();
set_ext_domain('modules/ksf_shipping');
include_once(__DIR__ . "/../../includes/ui.inc");
include_once(__DIR__ . "/../../includes/data_checks.inc");

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

//global $db; // Allow access to the FA database connection
//$debug_sql = 0;  // Change to 1 for debug messages

	include_once( "class.ksf_shipping.php");
	require_once( 'ksf_shipping.inc.php' );
	$my_mod = new ksf_shipping( ksf_shipping_PREFS );
	$my_mod->set_var( 'help_context', ksf_shipping_HELP );
	$my_mod->set_var( 'redirect_to', "ksf_shipping.php" );
	$my_mod->run();

