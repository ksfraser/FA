<?php
// Bootstrap for PHPUnit tests

// Include necessary files or set up environment
// For example, include config or autoload

require_once __DIR__ . '/../vendor/autoload.php';

// Set up global variables
global $SysPrefs, $tmonths, $path_to_root;

$path_to_root = __DIR__ . '/..';

$SysPrefs = new stdClass();
$SysPrefs->date_system = 0; // Gregorian
$SysPrefs->dateseps = array('/', '-', '.');

$tmonths = array(
    1 => 'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
);

// Mock functions
function user_date_format() { return 2; } // Y/m/d
function user_date_sep() { return 1; } // -

require_once __DIR__ . '/../includes/date_functions.inc';
require_once __DIR__ . '/../includes/types.inc';