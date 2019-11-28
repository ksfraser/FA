<?php

require_once('class.ksf_import_ofx.php');
require_once('ksf_import.inc.php');

$_SESSION[] = '';

$test = new ksf_import_ofx( ksf_import_PREFS );
$test->set( 'filename', 'test.qfx' );
$test->run();
