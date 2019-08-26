<?php

//!< WARNING this class has some FrontAccounting specific code

$path_to_root="../..";
require_once( 'defines.inc.php' );

/*
	# 0 PEAR_LOG_EMERG emerg() System is unusable
	# 1 PEAR_LOG_ALERT alert() Immediate action required
	# 2 PEAR_LOG_CRIT crit() Critical conditions
	# 3 PEAR_LOG_ERR err() Error conditions
	# 4 PEAR_LOG_WARNING warning() Warning conditions
	# 5 PEAR_LOG_NOTICE notice() Normal but significant
	# 6 PEAR_LOG_INFO info() Informational
	# 7 PEAR_LOG_DEBUG debug() Debug-level messages 
*/

class origin
{
	var $config_values = array();   //!< What fields to be put on config screen
	var $tabs = array();
        var $help_context;		//!< help context for screens in FA
	var $tb_pref;			//!< FrontAccounting Table Prefix (i.e. 0_) 
	var $loglevel;			//!< PEAR_LOG level that must be specified to be added to log/errors
	var $errors;			//!< array of error messages
	var $log;			//!< array of log messages
	var $fields;			//!< array of fields in the class
	var $data;			//!< array of data from the fields

	/************************************************************************//**
	 *constructor
	 *
	 *@param $loglevel int PEAR log levels
	 *
	 * ***************************************************************************/
	function __construct( $loglevel = PEAR_LOG_DEBUG )
	{
		global $db_connections;
		$cu = $_SESSION['wa_current_user'];				//FrontAccounting specific
		$compnum = $cu->company;					//FrontAccounting specific
		$this->tb_pref = $db_connections[$compnum]['tbpref'];		//FrontAccounting specific
		$this->loglevel = $loglevel;
		$this->error = array();
		$this->log = array();
	}
	function set_var( $var, $value )
	{
			$this->$var = $value ;
/*
		if(!empty($value) && is_string($value)) {
        		$this->$var = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
    		}
		else
		{
			$this->$var = $value ;
		}
*/
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
	
	function LogError( $message, $level = PEAR_LOG_ERR )
	{
		if( $level <= $this->loglevel )
			$this->errors[] = $message;
		return;
	}
	function LogMsg( $message, $level = PEAR_LOG_INFO )
	{
		if( $level <= $this->loglevel )
			$this->log[] = $message;
		return;
	}
}
?>
