<?php

$path_to_root="../..";

require_once( 'class.kfLog.php' );	//Extends origin

$configArray = array();

/******************************************************************************************//**
 * This class is the start of a controller, and loads config.*.php files to get sub-modules
 *
 * Routines here have been tested through my own framework, as well as add-ons to 
 * Wordpress, Zencart, Front Accounting.  Doesn't mean this is bug free though!!!
 *
 * Inherits:
        function __construct( $loglevel = PEAR_LOG_DEBUG )
        /*@NULL@* /function set_var( $var, $value )
        function get_var( $var )
        /*@array@* /function var2data()
        /*@array@* /function fields2data( $fieldlist )
        /*@NULL@* /function LogError( $message, $level = PEAR_LOG_ERR )	//Adds to an array only
        /*@NULL@* /function LogMsg( $message, $level = PEAR_LOG_INFO )	//adds to an array only
	function object_var_names()
        function set( $field, $value = null, $enforce_only_native_vars = true )
        /*@NULL@* /function set_var( $var, $value )
	function get( $field )
	function Log( $msg, $level )	//kfLog

 *
 * Provides:
        function __construct( $moduledir )
        function dumpObservers()
        function ObserverRegister( /*Class Instance* /$observer, $event )
         function ObserverDeRegister( $observer )
         function ObserverNotify( $trigger_class, $event, $msg )
         function notified( $object, $event, $message )
 * 
 *
 * ********************************************************************************************/
class eventloop extends kfLog implements splSubject
{
	var $config_values = array();   //What fields to be put on config screen
	var $tabs = array();
        var $help_context;
	var $tb_pref;
	private $observers = [];
	private $storage;	//From php.net example

	function __construct( $moduledir )
	{
		parent::__construct();
		$this->storage = new SplObjectStorage();	//php.net
		$this->initEventGroup( '*' );
		$this->initEventGroup( '**' );
 		/* 
		 * locate Module class files to open 
		 */
	        foreach (glob("{$moduledir}/config.*.php") as $filename)
	        {
			//echo "opening module config file " . $filename . "<br />\n";
	                include_once( $filename );
	        }
		/*
		 * Loop through the $configArray to set loading modules in right order
		 */
		//var_dump( $configArray );
		if( isset( $configArray ) AND  count( $configArray ) > 0 )
		{
			foreach( $configArray as $carray )
			{
				//var_dump( $carray );
				$modarray[$carray['loadpriority']][] = $carray;
			}
		}
		if( isset( $modarray ) AND count( $modarray ) > 0 )
		{
			//var_dump( $modarray );
			foreach( $modarray as $priarray )
			{
				foreach( $priarray as $marray )
				{
					$res = include_once( $moduledir . "/" . $marray['loadFile'] );
					if( TRUE == $res )
					{
						$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Module " . $marray['ModuleName'] . " being added" );
						//echo "Module " . $marray['ModuleName'] . " being added <br />";
						$marray['objectName'] = new $marray['className'];
						if( isset( $marray['objectName']->observers ) )
						{
							foreach( $marray['objectName']->observers as $obs )
							{
								$this->observers[] = $obs;
							}
						}
					}
					else
					{
						echo "Attempt to open " . $moduledir . "/" . $marray['loadFile'] . " FAILED!<br />";
					}
				}
			}
		}
		$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Completed Adding Modules" );
		$this->ObserverNotify( $this, 'NOTIFY_INIT_CONTROLLER_COMPLETE', "Completed Adding Modules" );
	}
	function dumpObservers()
	{
		if( isset( $this->observers ) )
		{
			foreach( $this->observers as $key=>$val )
			{
				echo "Observer Event: " . $key . " with value " . $val;
			}
/*
			foreach( $this->observers as $obs )
			{
				var_dump( $obs );
			}
*/
		}
	}
	function ObserverRegister( /*Class Instance*/$observer, $event )
        {
		//return FALSE;
		$this->initEventGroup( $event );
               	$this->observers[$event][] = $observer;	//Indirect modification has no effect ERROR
/*
		try {
			if( isset( $this->observers[$event] ) )
			{
               			$this->observers[$event][] = $observer;	//Indirect modification has no effect ERROR
			}
			else
			{
				$this->observers[$event] = array();
               			$this->observers[$event][] = $observer;	//Indirect modification has no effect ERROR
			}
			//$this->observers[$event] = array_merge( $this->observers[$event], $observer );
               		return SUCCESS;
		}
		catch( Exception $e )
		{
			$this->notify( __METHOD__ . ":" . __LINE__  . " Error: " . $e->getMessage(), "ERROR" );
			$this->notify( __METHOD__ . ":" . __LINE__ . print_r( $this->observers, true ), "WARN" );
        	}
		return FALSE;
*/
	}
        function ObserverDeRegister( $observer )
        {
              	$this->observers[] = array_diff( $this->observers, array( $observer) );
              	return SUCCESS;
        }
	private function initEventGroup($event = "*")
 	{
		if (!isset($this->observers[$event])) {
	        	$this->observers[$event] = [];
	     	}
 	}
        function ObserverNotify( $trigger_class, $event, $msg )
        {
	//	return;
		if( is_string( $msg ) )
			$this->Log( get_class( $trigger_class ) . " had event " . $event . " with message " . $msg, 1 );
		else
			$this->Log( get_class( $trigger_class ) . " had event " . $event, 1 );
               	if ( isset( $this->observers[$event] ) )
                      foreach ( $this->observers[$event] as $obs )
                      {
                              $obs->notified( $trigger_class, $event, $msg );
                      }
               	/* '**' being used as 'ALL' */
               	if ( isset( $this->observers['**'] ) )
                      	foreach ( $this->observers['**'] as $obs )
                      	{
                              	$obs->notified( $trigger_class, $event, $msg );
                      	}
               	return SUCCESS;
         }
         function notified( $object, $event, $message )
         {
               	//Called when another object we are observing sends us a notification
		//Needs to be extended by the inheriting class
               	return SUCCESS;
         }
	private function getEventObservers($event = "*")
    	{
        	$this->initEventGroup($event);
        	$group = $this->observers[$event];
        	$all = $this->observers["*"];
        	return array_merge($group, $all);
    	}
/****************************splSubject************************************************/
	public function attach(\SplObserver $observer, $event = "*")
    	{
        	$this->initEventGroup($event);
        	$this->observers[$event][] = $observer;
		$this->storage->attach($observer);	//php.net
    	}
    	public function detach(\SplObserver $observer, $event = "*")
    	{
		$this->storage->detach($observer);	//php.net
        	foreach ($this->getEventObservers($event) as $key => $s) {
            		if ($s === $observer) {
                		unset($this->observers[$event][$key]);
            		}
        	}
		/********php.net user comments************************/
        	$key = array_search($observer,$this->observers, true);
        	if(false !== $key){
            		unset($this->observers[$key]);
        	}
		/********!php.net user comments************************/
    	}
    	//public function notify(string $event = "*", $data = null)
    	public function notify( $event = "*", $data = null)
    	{
        	foreach ($this->getEventObservers($event) as $observer) {
            		$observer->update($this, $event, $data);
        	}
		//php.net
		foreach ($this->storage as $obj) {
            		$obj->update($this);
        	}
		//!php.net
    	}
/****************************!splSubject************************************************/
}


/****************************splObserver************************************************/
/****************************php.net****************************************************/
abstract class Observer implements SplObserver
{
    private $observable;

    function __construct(Observable $observable)
    {
        $this->observable = $observable;
        $observable->attach($this);
    }

    function update(SplSubject $subject)
    {
        if ($subject === $this->observable) {
            $this->doUpdate($subject);
        }
    }

    abstract function doUpdate(Observable $observable);
}

class ConcreteObserver extends Observer
{
    function doUpdate(Observable $observable)
    {
        //...
    }
}
/****************************!php.net****************************************************/
/****************************!splObserver************************************************/
?>
