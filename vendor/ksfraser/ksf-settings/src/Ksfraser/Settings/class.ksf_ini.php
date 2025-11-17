<?php

/*******************************************//**
* This class is to read a config file in ini format and "Set" the config values
*
*	Format:  php INI format
*		VAR = VAL
*		comments start with ;
***********************************************/
require_once( '../class.origin.php');
class ksf_ini extends origin
{
/*
	function __construct()
	{
		parent::__construct();
	}
*/
 	/***************************************************************//**
         *build_interestedin
         *
         *      DEMO function that needs to be overridden
         *      This function builds the table of events that we
         *      want to react to and what handlers we are passing the
         *      data to so we can react.
         * ******************************************************************/
        function build_interestedin()
        {
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";

                //This NEEDS to be overridden
                $this->interestedin['READ_INI']['function'] = "read_ini";
        //      throw new Exception( "You MUST override this function, even if it is empty!", KSF_FCN_NOT_OVERRIDDEN );
        }

	function read_ini( /*unused*/$caller, $filename )
	{
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
		$data = parse_ini_file( $filename );
		var_dump( $data );
		foreach( $data as $key=>$val )
		{
			var_dump( $key );
			var_dump( $val );
			$this->set( $key, $val, false );
			$this->tell_eventloop( $this, 'SETTINGS_SAVE', array( $key => $val ) );
			//$this->tell_eventloop( $this, 'SETTINGS_SAVE', $data );
		}
		//var_dump( $this );
	}
	
}
