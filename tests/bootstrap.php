<?php
// Bootstrap for PHPUnit tests

// Include necessary files or set up environment
// For example, include config or autoload

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/MockFactory.php';

// Initialize mock factory
\FA\Tests\MockFactory::init();

// Set up global variables
global $SysPrefs, $tmonths, $path_to_root, $systypes_array;

$path_to_root = __DIR__ . '/..';

$SysPrefs = new stdClass();
$SysPrefs->date_system = 0; // Gregorian
$SysPrefs->dateseps = array('/', '-', '.');
$SysPrefs->go_debug = 0;

$tmonths = array(
    1 => 'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
);

$systypes_array = array(
    0 => 'Journal Entry',
    10 => 'Customer Payment',
    11 => 'Customer Credit',
    12 => 'Customer Invoice',
    13 => 'Customer Delivery'
);

// Mock functions
function user_date_format() { return 2; } // Y/m/d
function user_date_sep() { return 1; } // -

require_once __DIR__ . '/../includes/date_functions.inc';
require_once __DIR__ . '/../includes/types.inc';