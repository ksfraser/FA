<?php

/**************************************************************************
*
*	INIT
*
**************************************************************************/

$page_security = 'SA_CDNTAX';
$path_to_faroot= dirname ( realpath ( __FILE__ ) ) . "/../..";
$path_to_common = "../ksf_modules_common";
global $path_to_root;
$path_to_root= dirname ( realpath ( __FILE__ ) ) . "/../..";

//include_once($path_to_faroot . "/includes/session.inc");
include_once($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/CDNTAX');

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 0;  // Change to 1 for debug messages
$prefsDB = "CDNTAX_prefs";

//initialise no input errors assumed initially before we test
$input_error = 0;

/**************************************************************************
*
*	VIEW
*
**************************************************************************/
require_once( $path_to_common . '/class.VIEW.php' );
require_once( 'class.CDNTAX.php' );
require_once( 'class.CDNTAX_MODEL.php' );

include_once($path_to_faroot . "/includes/ui.inc");
include_once($path_to_faroot . "/includes/date_functions.inc");

/**************************************************************************
*
*	CONTROLLER
*
**************************************************************************/

$controller = new CDNTAX( "defiant.ksfraser.com", "fhs", "fhs", "fhs", $prefsDB );
$controller->model = new CDNTAX_MODEL( "defiant.ksfraser.com", "fhs", "fhs", "fhs", $prefsDB );
$controller->set_var( "found", $controller->is_installed() );
$controller->set_var( "help_context", "CDNTAX" );
$controller->set_var( "redirect_to", "class.CDNTAX.php" );
$controller->run();

?>
