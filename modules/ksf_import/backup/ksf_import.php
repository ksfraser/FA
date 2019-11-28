<?php
/**********************************************
Name: COAST purchase order export
modified for COAST 1.5.1 and FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/

$page_security = 'SA_KSF_IMPORT';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/ksf_import');

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 0;  // Change to 1 for debug messages
//global $prefsDB;	//defined in class.ksf_import.php
/*
if( isset( $_POST['edit_form'] ) )
{
	//AJAX call
	$cl = $_POST['my_class'];	//set in woo_interface
	require_once( 'class.' . $cl . '.php' );
	$mycl = new $cl( null, null, null, null, null );
	$mycl->form_post_handler();
	$_GET['action'] = $_POST['action'] = $_POST['return_to'];
	unset( $_POST );
	header("Status: 301 Moved Permanently");
	header("Location: " . $_SERVER['REQUEST_URI'] . ($_GET ? "?" . http_build_query( $_GET ) : "" ) );
	

}
else
{
	$eventloop = new eventloop( "." );
 */

//display_notification( __LINE__ );
//page mode and page are needed to setup the theme, display_* Exception handler etc.
//simple_page_mode(true);
//page("test");

	include_once($path_to_root . "/modules/ksf_import/class.ksf_import.php");
	require_once( 'ksf_import.inc.php' );
	$my_mod = new ksf_import( KSF_IMPORT_PREFS );
	$found = $my_mod->is_installed();
	$my_mod->set_var( 'found', $found );
	$my_mod->set_var( 'help_context', KSF_IMPORT_HELP );
	$my_mod->set_var( 'redirect_to', "ksf_import.php" );
	$my_mod->run();

//}

?>
