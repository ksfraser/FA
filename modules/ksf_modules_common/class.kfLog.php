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
	var $objWriteFile;

	function __construct( $filename = __FILE__, $level = PEAR_LOG_DEBUG )
	{
		parent::__construct();
		$conf = array();
		$filename = basename( realpath( $filename ) );
		$this->logobject = new Log_file( $filename . "_debug_log_pear.txt", "", $conf, $level );
		$this->objWriteFile = new write_file( ".", $filename . "_debug_log." . date( 'YmdHis' ) . ".txt" );
		return;	
	}
	function __destruct()
	{
		$this->objWriteFile->__destruct();
	}
	function Log( $msg, $level )
	{
		if( strcmp( $level, "WARN" ) == 0 )
		{
			unset( $level );
			//$level = PEAR_LOG_WARN;
		//	$this->logobject->log( $msg, PEAR_LOG_WARN );
		}
		$this->objWriteFile->write_line( $msg );
		return;	
	}
	function build_interested()
	{
		$this->interestedin['NOTIFY_LOG_DEBUG']['function'] = "Log";
                $this->interestedin['NOTIFY_LOG_INFO']['function'] = "Log";
                $this->interestedin['NOTIFY_LOG_NOTICE']['function'] = "Log";
                $this->interestedin['NOTIFY_LOG_WARNING']['function'] = "Log";
                $this->interestedin['NOTIFY_LOG_ERR']['function'] = "Log";
                $this->interestedin['NOTIFY_LOG_CRIT']['function'] = "Log";
                $this->interestedin['NOTIFY_LOG_ALERT']['function'] = "Log";
                $this->interestedin['NOTIFY_LOG_EMERG']['function'] = "Log";
		
	}
}
?>

