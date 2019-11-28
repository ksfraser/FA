<?php
/**********************************************
Name: 
for FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/

$page_security = 'SA_ksf_suitecrm';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/ksf_suitecrm');

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 0;  // Change to 1 for debug messages
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

	include_once( $path_to_root . "/modules/ksf_suitecrm/class.ksf_suitecrm.php");
	require_once( 'ksf_suitecrm.inc.php' );
	$my_mod = new ksf_suitecrm( ksf_suitecrm_PREFS );
	$found = $my_mod->is_installed();
	$my_mod->set_var( 'found', $found );
	$my_mod->set_var( 'help_context', ksf_suitecrm_HELP );
	$my_mod->set_var( 'redirect_to', "ksf_suitecrm.php" );
	$my_mod->run();

//}

