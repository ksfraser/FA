<?php
/**********************************************
Name: VTIGER customer and order import
Based on zencart order import 
modified for VTIGER 1.5.1 and FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/

$page_security = 'SA_VTIGERIMPORT';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/vtiger_import');

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/db/branches_db.inc");
include_once($path_to_root . "/sales/includes/db/customers_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_order_db.inc");
include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/modules/vtiger_import/class.vtiger_customers.php");

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 0;  // Change to 1 for debug messages

$vtigerc = new vtiger_customers( "defiant.ksfraser.com", "fhs", "fhs", "fhs", "vtiger_import_prefs" );
$found = $vtigerc->is_installed();

if ($found) {
	$vtigerc->loadprefs();
}
else
{
	$vtigerc->create_prefs_tablename();
	$vtigerc->loadprefs();
	$vtigerc->checkprefs();
        //header("Location: vtiger.php");
}

// Show information to connect to VTIGER Database 
$action = $found ? 'cimport' : 'show';
if (isset($_GET['action']) && $found) $action = $_GET['action'];

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // Create Table
    if ($action == 'create') {
	$vtigerc->create_prefs_tablename();
	$vtigerc->loadprefs();
	$vtigerc->checkprefs();
        header("Location: vtiger.php");
    }
    if ($action == 'update') {
	$vtigerc->loadprefs();
	$vtigerc->checkprefs();
    } else {
    }
    
// Customer Import from VTIGER Database
   if ($action == 'c_import') {
	$vtigerc->minmax_cids();
	$vtigerc->import_customers();

    }
} else {	//POST versus GET
    if ($action == 'cimport') 
    { 
	// Preview Customer Import page
	$cid = $vtigerc->get_id_range();
    }
}

$vtigerc->base_page( $action );	//start the display of data that the forms below complete.

if ($action == 'show') {
	$vtigerc->action_show_form( $found );
}
if ($action == 'cimport') {
	$vtigerc->action_cimport_form();
}

?>
