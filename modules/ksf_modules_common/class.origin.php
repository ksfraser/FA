<?php

//!< WARNING this class has some FrontAccounting specific code

require_once( 'defines.inc.php' );
//include_once( 'Log.php' );	//PEAR Logging - included in defines.inc

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
/***************************************************************//**
 * Base class for ksf common...  throws EXCEPTIONS for try/catch loops
 *
 * Provides:
   	function __construct( $loglevel = PEAR_LOG_DEBUG )
        /*@NULL@* /function set_var( $var, $value )
        function get_var( $var )
        /*@array@* /function var2data()
        /*@array@* /function fields2data( $fieldlist )
        /*@NULL@* /function LogError( $message, $level = PEAR_LOG_ERR )
	/*@NULL@* /function LogMsg( $message, $level = PEAR_LOG_INFO )
 *
 *
 * *********************************************************************************/
class origin
{
	/*refactor*/protected $config_values = array();   //!< What fields to be put on config screen.  Probably doesn't belong in this class :(
	/*refactor*/protected $tabs = array();
        /*refactor*/var $help_context;		//!< help context for screens in FA
	/*refactor*/var $tb_pref;			//!< FrontAccounting Table Prefix (i.e. 0_) 
	var $loglevel;			//!< PEAR_LOG level that must be specified to be added to log/errors
	var $errors;			//!< array of error messages
	var $log;			//!< array of log messages
	var $fields;			//!< array of fields in the class
	var $data;			//!< array of data from the fields
	private $testvar;
	var $object_fields;		//!< array of the variables in this object, under '___SOURCE_KEYS_'
	protected $application;		//!< string which application is the child object holding data for
	protected $module;		//!< string which module is the child object holding data for
	protected $container_arr;	//__get/__isset uses this
	protected $eventloop;		//!< object
	protected $client;		//!< object what object instantiated this object

	/************************************************************************//**
	 *constructor
	 *
	 * @startuml
	 * partition Origin {
	 * title Origin Constructor
	 * (*) --> "Set variables"
	 * --> "set variables from SESSION"
	 * --> "call object_var_names()"
	 * -->(*)
	 * }
	 * @enduml
	 *
	 * Should we be calling build_interestedin and register_with_eventloop?
	 * Should attach_eventloop be creating an eventloop if one doesn't exist?
	 *
	 *@param $loglevel int PEAR log levels
	 * @param client Object that uses us.
	 * @returns null
	 * ***************************************************************************/
	function __construct( $loglevel = PEAR_LOG_DEBUG, $client = null )
	{
		if( isset( $client ) )
			$this->client = $client;
		global $db_connections;
		if( isset( $_SESSION['wa_current_user'] ) )
		{
			$cu = $_SESSION['wa_current_user'];			//FrontAccounting specific
			$compnum = $cu->company;				//FrontAccounting specific
		}
		else
		{
			$compnum = 0;
			//$this->set( 'company_prefix', $compnum );	//db_base trying to set in test cases.
		}
		if( isset( $db_connections[$compnum]['tbpref'] ) )
			$this->tb_pref = $db_connections[$compnum]['tbpref'];	//FrontAccounting specific
		else
			$this->set( 'tb_pref', $compnum . "_", false );	//FrontAccounting specific
		$this->loglevel = $loglevel;
		$this->error = array();
		$this->log = array();
		//Set, with end of constructor values noted
		$this->object_var_names();
	}
	/***************************************************//**
	*
	* @since 20200708
	* @param none
	* @returns none
	*********************************************************/
	function __destruct()
	{
		//adding because child class called us and error'd out.
	}
	/*********************************************************//**
	 * Magic call method example from http://php.net/manual/en/language.types.object.php
	 *
	 * @param string function name
	 * @param array array of arguments to pass to function
	 * ************************************************************/
/*
	public function __call($method, $arguments) 
	{
		$arguments = array_merge(array("stdObject" => $this), $arguments); // Note: method argument 0 will always referred to the main class ($this).
	        if (isset($this->{$method}) && is_callable($this->{$method})) {
	            return call_user_func_array($this->{$method}, $arguments);
	        } else {
	            throw new Exception("Fatal error: Call to undefined method stdObject::{$method}()");
	        }
	    }
 */
	/**
	 * Magic getter to bypass referencing plugin.
	 *
	 * @param $prop
	 *
	 * @return mixed
	 */
	function __get( $prop ) {
		if( ! is_array( $this->container_arr ) )
			return NULL;
		if ( array_key_exists( $prop, $this->container_arr ) ) {
		    return $this->container_arr[ $prop ];
		}

		return $this->{$prop};
	}

	/**
	 * Magic isset to bypass referencing plugin.
	 *
	 * @param $prop
	 *
	 * @return mixed
	 */
	function __isset( $prop ) {
		return isset( $this->{$prop} ) || isset( $this->container_arr[ $prop ] );
	}

	/**
	 * Check if the PHP version is supported
	 *
	 * @return bool
	 */
	function is_supported_php() {
		if ( version_compare( PHP_VERSION, $this->min_php, '<' ) ) {
		    return false;
		}

		return true;
	}

	function object_var_names()
	{
		$clone = (array) $this;	    		
		$rtn = array ();
		//private prefixed by class name, protected by *
    		$rtn['___SOURCE_KEYS_'] = $clone;
    		while ( list ($key, $value) = each ($clone) ) {
        		$aux = explode ("\0", $key);
        		$newkey = $aux[count($aux)-1];
        		$rtn[$newkey] = $rtn['___SOURCE_KEYS_'][$key];
    		}
		$this->object_fields = $rtn;
	}
	//STUB until I can code module and data access...
	function user_access( $action )
	{
		switch( $action )
		{
			case KSF_DATA_ACCESS_READ:
			case KSF_DATA_ACCESS_WRITE:
			case KSF_DATA_ACCESS_READWRITE:
			case KSF_MODULE_ACCESS_READ:
			case KSF_MODULE_ACCESS_WRITE:
			case KSF_MODULE_ACCESS_READWRITE:
				break;
			case KSF_DATA_ACCESS_DENIED:
			case KSF_MODULE_ACCESS_DENIED:
			default:
				throw new Exception( "User doesn't have access to the field", KSF_DATA_ACCESS_DENIED );
		}
		return TRUE;
	}
	/*********************************************//**
	 * Set a variable.  Throws exceptions on sanity checks
	 *
	 * The throwing of exceptions is probably going to break a bunch of code!
	 * @param field string Variable to be set
	 * @param value ... value for variable to be set
	 * @param native... bool enforce only the variables of the class itself.  default TRUE, which will break code.
	 *
	 * **********************************************/
	function set( $field, $value = null, $enforce_only_native_vars = true )
	{
		if( !isset( $field )  )
			throw new Exception( "Fields not set", KSF_FIELD_NOT_SET );
		try{
			$this->user_access( KSF_DATA_ACCESS_WRITE );
		} 
		catch (Exception $e )
		{
			throw new Exception( $e->getMessage, $e->getCode );
		}

		if( $enforce_only_native_vars )
		{
			if( ! isset( $this->object_fields ) )
			{
				//debug_print_backtrace();
			}
			else if( ! in_array( $field, $this->object_fields ) AND ! array_key_exists( $field, $this->object_fields ) )
                               throw new Exception( "Variable to set ::" . $field . ":: is not a member of the class \n" . print_r( $this->object_fields, true ), KSF_FIELD_NOT_CLASS_VAR );

		}
		if( isset( $value ) )
			$this->$field = $value;
		else
			throw new Exception( "Value to be set not passed in", KSF_VALUE_NOT_SET );
	}
	/***************************************************//**
	 * Most of our existing code does not use TRY/CATCH so we will trap here
	 *
	 * Eat any exceptions thrown by ->set
	 *
	 * *****************************************************/
	/*@NULL@*/function set_var( $var, $value )
	{
		try {
			$this->set( $var, $value );
		} catch( Exception $e )
		{
		}
/*
		if(!empty($value) && is_string($value)) {
        		$this->$var = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
    		}
		else
		{
			$this->$var = $value ;
		}
 */
		return;
	}
	function get( $field )
	{
		if( isset( $this->$field ) )
			return $this->$field;
		else
			throw new Exception( __METHOD__ . "  Field not set.  Can't GET " . $field, KSF_FIELD_NOT_SET );
	}
	function get_var( $var )
	{
		return $this->get( $var );
	}
	/*@array@*/function var2data()
	{
		foreach( $this->fields as $f )
		{
			$this->data[$f] = $this->get_var( $f );
		}
	}
	/*@array@*/function fields2data( $fieldlist )
        {
                foreach( $fieldlist as $field )
                {
                        $this->data[$field] = $this->get_var( $field );
                }
                return $this->data;
	}
	
	/*@NULL@*/function LogError( $message, $level = PEAR_LOG_ERR )
	{
		if( $level <= $this->loglevel )
			$this->errors[] = $message;
		return;
	}
	/*@NULL@*/function LogMsg( $message, $level = PEAR_LOG_INFO )
	{
		if( $level <= $this->loglevel )
			$this->log[] = $message;
		return;
	}
	/******SPL EventLoop Funcs ********************************************/
	/****************//**
	*	Ensure we are attached to an eventloop object
	*
	********************/
	function attach_eventloop()
	{
		if( ! isset( $this->eventloop ) )
		{
			global $eventloop;
			if( isset( $eventloop ) )
			{
				$this->eventloop = $eventloop;
				return TRUE;
			}
			else
			{
				if( isset( $this->client ) )
				{
					if( isset( $this->client->eventloop ) )
					{
						$this->eventloop = $this->client->eventloop;
						return TRUE;
					}
					else
					{
						return FALSE;
					}
				}
				else
				{
					return FALSE;
				}
			}
		}
		else
		{
			return TRUE;
		}
	}
 	/************************************************************//**
         *
         *      tell.  Function to tell the using routine that we took
         *      an action.  That will let the client pass that data to
         *      any other plugin routines that are interested in that
         *      fact.
         *
         *      @param msg what event message to pass
         *      @param method Who triggered that event so that we don't pass back to them into an endless loop
         *
         * **************************************************************/
        function tell( $msg, $method )
        {
		if( ! isset( $msg ) )
			throw new Exception( "MSG to tell not set", KSF_VAR_NOT_SET );

                if( isset( $this->client ) )    //if not set nobody to tell
                        if( is_callable( $this->client->eventloop( $msg, $method ) ) )
                                $this->client->eventloop( $msg, $method );
                else
                {
                        $this->tell_eventloop( $this, $msg, $method );
                }
        }
        function tell_eventloop( $caller, $event, $msg )
        {
		if( $this->attach_eventloop() )
                        $this->eventloop->ObserverNotify( $caller, $event, $msg );

        }
        /***************************************************************//**
         *dummy
         *
         *      Dummy function so that build_interestedin has something to
         *      put in as an example.
         *
         *      @returns FALSE
         * ******************************************************************/
        function dummy( $obj, $msg )
        {
                $this->tell_eventloop( $this, NOTIFY_LOG_DEBUG, __METHOD__ . ":" . __LINE__ . " Entering " );
                $this->tell_eventloop( $this, NOTIFY_LOG_DEBUG, __METHOD__ . ":" . __LINE__ . " Exiting " );
                return FALSE;
        }
   	function register_with_eventloop()
        {
		if( $this->attach_eventloop() )
                {
                        foreach( $this->interestedin as $key => $val )
                        {
                                if( $key <> KSF_DUMMY_EVENT )
                                        $this->eventloop->ObserverRegister( $this, $key );
                        }
                }
        }
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
                //This NEEDS to be overridden
                $this->interestedin[KSF_DUMMY_EVENT]['function'] = "dummy";
	//	throw new Exception( "You MUST override this function, even if it is empty!", KSF_FCN_NOT_OVERRIDDEN );
        }
        /***************************************************************//**
         *notified
         *
         *      When we are notified that an event happened, check to see
         *      what we want to do about it
         *
         * @param $obj Object of who triggered the event
         * @param $event what event was triggered
         * @param $msg what message (data) was passed to us because of the event
         * ******************************************************************/
        function notified( $obj, $event, $msg )
        {
                if( isset( $this->interestedin[$event] ) )
                {
                        $tocall = $this->interested[$event]['function'];
                        $this->$tocall( $obj, $msg );
                }
	}
	/*************************************************//**
	 *
	 * @since 20200712
	 * @TODO - write Unit Test
	 * @param Exception object
	 * @returns null
	 * **************************************************/
	function error_handler( Exception $e )
	{
		$code = $e->getCode();
		$msg = $e->getMessage();
		switch( $code )
		{
			default:
				$this->tell_eventloop( $this, "NOTIFY_LOG_ERROR", $msg );
		}
	}

}

/***************DYNAMIC create setter and getter**********************
// Create dynamic method. Here i'm generating getter and setter dynimically
// Beware: Method name are case sensitive.
foreach ($obj as $func_name => $value) {
    if (!$value instanceOf Closure) {

        $obj->{"set" . ucfirst($func_name)} = function($stdObject, $value) use ($func_name) {  // Note: you can also use keyword 'use' to bind parent variables.
            $stdObject->{$func_name} = $value;
        };

        $obj->{"get" . ucfirst($func_name)} = function($stdObject) use ($func_name) {  // Note: you can also use keyword 'use' to bind parent variables.
            return $stdObject->{$func_name};
        };

    }
}


*************************************************************************/ 

/***********************TESTING******************************
class origin_child extends origin
{
	var $only_in_child;
}
$test = new origin_child();
var_dump( $test );
try {
	$test->set( 'only_in_child', true, true );
} catch( Exception $e )
{
	var_dump( $e );
}
try {
	$test->set( 'only_in_child', true );
} catch( Exception $e )
{
	var_dump( $e );
}
try {
	$test->set( 'only_in_child' );
} catch( Exception $e )
{
	var_dump( $e );
}
var_dump( $test );
/************!TESTING**********************/
?>
