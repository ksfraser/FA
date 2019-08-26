<?php
/**********************************************
Name: 
for FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/

//$page_security = 'SA_ksf_missing_image';
$page_security = 'SA_ITEM';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/ksf_missing_image');

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 0;  // Change to 1 for debug messages

//page mode and page are needed to setup the theme, display_* Exception handler etc.
//simple_page_mode(true);
//page("test");

	include_once( $path_to_root . "/modules/ksf_missing_image/class.ksf_missing_image.php");
	require_once( 'ksf_missing_image.inc.php' );
	$my_mod = new ksf_missing_image( ksf_missing_image_PREFS );
	$found = $my_mod->is_installed();
	$my_mod->set_var( 'found', $found );
	$my_mod->set_var( 'help_context', ksf_missing_image_HELP );
	$my_mod->set_var( 'redirect_to', "ksf_missing_image.php" );
	$my_mod->run();


