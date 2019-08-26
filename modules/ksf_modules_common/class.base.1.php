<?php

error_reporting( E_ALL );
ini_set("display_errors", 1);
require_once( __DIR__ . '/../../includes/db/connect_db.inc' );
require_once( __DIR__ . '/../../includes/errors.inc' );
//20170106
require_once( __DIR__ . '/class.origin.php' );
//!20170106

//class base 
//20170106
class base extends origin
{
	var $username;
	var $password;
	var $errmsg;
	var $debug;
	var $json_decode_as_array = FALSE;

	const HASH_ALGORITHM = 'SHA256';

	function __construct( /*array*/ $args = array() )
	{
		$this->parse_args( $args );
	}
	function __destruct()
	{
	}
	
	/**********************************************************************
	 *
	 * Options is an array of options so needs to be handled recursively
	 *
	 * *******************************************************************/
	function parse_args( /*array*/$args )
	{
		foreach( $args as $key=>$value )
		{
			if( $key = "options" )
			{
				$this->parse_args( $value );
			}
			else
			{
				$this->$key = $value;
			}
		}
	}
	function set_var( $var, $val )
	{
		$this->$var = $val;
	}
	function get_var( $var )
	{
		return $this->$var;
	}
	function var2data()
	{
		foreach( $this->fields as $f )
		{
			$this->data[$f] = $this->get_var( $f );
		}
	}
        function fields2data( $fieldlist )
        {
                foreach( $fieldlist as $field )
                {
                        $data[$field] = $this->get_var( $field );
                }
                return $data;
	}
	function open_write_file( $filename )
	{
		return fopen( $filename, 'w' );
	}
	function write_line( $fp, $line )
	{
		fwrite( $fp, $line . "\n" );
	}
	function file_finish( $fp )
	{
		fflush( $fp );
		fclose( $fp );
	}
}

?>
