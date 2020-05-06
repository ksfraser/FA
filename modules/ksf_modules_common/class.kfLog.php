<?php

//!< Dependant upon PEAR LOG

$path_to_root="../..";

require_once( 'class.origin.php' );
@include_once ( 'Log/file.php' );
require_once( 'class.write_file.php' );
require_once( 'defines.inc.php' );


/*
  define( 'PEAR_LOG_EMERG', 0 );
        define( 'PEAR_LOG_ALERT', 1 );
        define( 'PEAR_LOG_CRIT', 2 );
        define( 'PEAR_LOG_ERR', 3 );
        define( 'PEAR_LOG_WARNING', 4 );
        define( 'PEAR_LOG_NOTICE', 5 );
        define( 'PEAR_LOG_INFO', 6 );
        define( 'PEAR_LOG_DEBUG', 7 );
*/

class kfLog extends origin
{
	var $logobject;
	var $objWriteFile;	//MERGER - Unused?

	function __construct( $filename = null, $level = PEAR_LOG_DEBUG )
	{
		if( null == $filename )
			$filename = __FILE__;
		$filename = basename( realpath( $filename ) );
		parent::__construct();
		$conf = array();
		$this->logobject = new Log_file( $filename . "_debug_log.txt", "", $conf, $level );
		$this->objWriteFile = new write_file( ".", $filename . "_debug_log.txt" );
		return;	
	}
	function __destruct()
	{
		$this->objWriteFile->__destruct();
	}
	function Log( $msg, $level )//:bool
	{
		if( strcmp( $level, "WARN" ) == 0 )
		{
			unset( $level );
			//$level = PEAR_LOG_WARN;
		//	$this->logobject->log( $msg, PEAR_LOG_WARN );
		}
		//MERGER - newer version has logobject but not objWriteFile...Migration?
		$this->logobject->log( $msg, $level );
		$this->objWriteFile->write_line( $msg );
		return;	
	}
}
?>

