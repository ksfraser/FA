<?php

//!< Dependant upon PEAR LOG

$path_to_root="../..";

require_once( 'class.origin.php' );
@include_once ( 'Log/file.php' );
require_once( 'class.write_file.php' );

class kfLog extends origin
{
	var $logobject;
	var $objWriteFile;

	function __construct( $filename = __FILE__, $level = PEAR_LOG_DEBUG )
	{
		parent::__construct();
		$conf = array();
		$filename = basename( realpath( $filename ) );
		$this->logobject = new Log_file( $filename . "_debug_log.txt", "", $conf, $level );
		$this->objWriteFile = new write_file( ".", $filename . "_debug_log.txt" );
		return;	
	}
	function __destruct()
	{
		$this->objWriteFile->__destruct();
	}
	function Log( $msg, $level )
	{
		$this->logobject->log( $msg, $level );
	//	$this->objWriteFile->write_line( $msg );
		return;	
	}
}
?>
