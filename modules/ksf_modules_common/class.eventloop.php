<?php

$path_to_root="../..";

require_once( 'class.kfLog.php' );	//Extends origin
//MERGER 0506
require_once( 'class.controller_origin.php' );
require_once( 'class.MODEL.php' );
require_once( 'class.VIEW.php' );

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
$configArray = array();	//Module configs use this

class eventloop extends kfLog implements splSubject
{
	var $config_values = array();   //What fields to be put on config screen
	var $tabs = array();
        var $help_context;
	var $tb_pref;
	private $observers = [];
	private $storage;	//From php.net example
	protected $caller;
	protected $moduledir;
	private $module_objects; //MERGER

	function __construct( $moduledir = null, $caller = null )
	{
	//MERGER
		if( isset( $caller ) && isset( $caller->debug ) )
			$debug = $caller->debug;
		else
			$debug = PEAR_LOG_CRIT;
		parent::__construct( __FILE__, $debug );
		if( isset( $caller ) )
		{
			$this->caller = $caller;
			$this->Log( "Caller set!" );
		}

		//parent::__construct();
		//$this->caller = $caller;
	//!MERGER
		$this->storage = new SplObjectStorage();	//php.net
		$this->initEventGroup( '*' );
		$this->initEventGroup( '**' );
 		/* 
		 * locate Module class files to open 
		 */
		if( ! isset( $moduledir ) )
			$moduledir = dirname( __FILE__ ) . '/modules';
		else
			$this->Log( "Moduledir is " . $moduledir, PEAR_LOG_DEBUG );
		$this->moduledir = $moduledir;
		$this->load_modules();
		$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Completed Adding Modules" );
		$this->ObserverNotify( $this, 'NOTIFY_INIT_CONTROLLER_COMPLETE', "Completed Adding Modules" );
	}
	function set_moduledir( $dir )
	{
		$this->moduledir = $dir;
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
	 /*****************************************************************************//**
         *ObserverRegister is the fcn that registers a class against an event
         *
         * @param class object to be registered
         * @param string event to register for
         * @param string value not used
         *
         * ******************************************************************************/
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
        /*****************************************************************************//**
         *ObserverNotify loops through observers and tells interested ones about the event
         *
         * @param string event to match against
         * @data mixed ideally is the object that triggered the event
         *
         * *******************************************************************************/
        function ObserverNotify( $trigger_class, $event, $msg )
        {
		if( is_string( $msg ) )
			$this->Log( get_class( $trigger_class ) . " had event " . $event . " with message " . $msg, PEAR_LOG_DEBUG );
		else
			$this->Log( get_class( $trigger_class ) . " had event " . $event, PEAR_LOG_DEBUG );
               	if ( isset( $this->observers[$event] ) )
                      foreach ( $this->observers[$event] as $obs )
                      {
				$tgt = get_class( $obs );
			      	$told[] = $tgt;
			      	$this->Log( "We told " . $tgt . " event_code  " . $event , PEAR_LOG_DEBUG );
                              $obs->notified( $trigger_class, $event, $msg );
                      }
               	/* '**' being used as 'ALL' */
               	if ( isset( $this->observers['**'] ) )
                      	foreach ( $this->observers['**'] as $obs )
                      	{
				$tgt = get_class( $obs );
				if( ! in_array( $tgt, $told  ) )
				{
					$obs->notified( $trigger_class, $event, $msg );
			      		$this->Log( "We told " . $tgt . " event_code **::" . $event, PEAR_LOG_DEBUG );
				}
				else
				{
					$this->Log( $tgt . " has already been told by event_code  " . $event . " rather than ALL", PEAR_LOG_DEBUG );
				}
                      	}
               	return SUCCESS;
         }
         function notified( $object, $event, $message )
         {
               	//Called when another object we are observing sends us a notification
		//Needs to be extended by the inheriting class
               	return SUCCESS;
         }
	//private function getEventObservers($event = "*")
	function getEventObservers($event = "*")
    	{
        	$this->initEventGroup($event);
        	$group = $this->observers[$event];
        	$all = $this->observers["*"];
        	return array_merge($group, $all);
    	}
	function load_modules()
	{
		global $configArray;
	        foreach (glob("{$this->moduledir}/config.*.php") as $filename)
	        {
			$this->log( " opening module config file " . $filename, PEAR_LOG_DEBUG );
	                include_once( $filename );
	        }
		/*
		 * Loop through the $configArray to set loading modules in right order
		 */
		$modarray = array();
		$tabarray = array();	//For menu options out of our modules (FA WOO interface driven)
		if( isset( $configArray ) AND  count( $configArray ) > 0 )
		{
			foreach( $configArray as $carray )
			{
				//MREGER.  Adding a default value incase loadpriority isn't specified
				if( isset( $carray['loadpriority'] ) )	
					$modarray[ $carray['loadpriority'] ][] = $carray;
				else
					$modarray[ 99 ][] = $carray;
				//Add to tabs...
				if( isset( $carray['taborder'] ) )
				{
					//Only if we have tabs to add!
					$tabarray[ $carray['taborder'] ][] = $carray;
				}
			}
		}
		if( isset( $modarray ) AND count( $modarray ) > 0 )
		{
			foreach( $modarray as $priarray )
			{
				foreach( $priarray as $marray )
				{
					//MERGER
					//If we want to have multiple load entries without loading the class multiple times, leave loadfile blank. 
					if( isset( $marray['loadFile'] ) )
					{
	
						$res = include_once( $this->moduledir . "/" . $marray['loadFile'] );
						if( TRUE == $res )
						{
							$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Module " . $marray['ModuleName'] . " being added" );
	
							//Passing this to the called class, they set us as the event dispatcher
							$g_obj = $marray['objectName'];
							global $$g_obj;
							$$g_obj = $this->module_objects[ $g_obj ] = new $g_obj( $this ); //Generates a lot of recursion!
							//$this->ObserverNotify( $this, 'NOTIFY_LOG_DEBUG', "Created Module Object " . $g_obj . " with object: " .  print_r( $this->module_objects[ $g_obj ], true ) );
							//$this->ObserverNotify( $this, 'NOTIFY_LOG_DEBUG', "Also Created global Object " . $g_obj . " with object: " .  print_r( $$g_obj , true ) );
							//$marray['objectName'] = new $marray['className']( $this );

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
							echo "Attempt to open " . $this->moduledir . "/" . $marray['loadFile'] . " FAILED!<br />";
							$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Unable to add module" . $this->moduledir );
						}
					}
				}
			}
		}
		$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Adding Module TABS" );
		$tabs = array();
		if( isset( $tabarray ) AND count( $tabarray ) > 0 )
		{
			//var_dump( $tabarray );
			foreach( $tabarray as $priarray )
			{
				foreach( $priarray as $tabinc )
				{
					 $tabs[] = array( 'title' => $tabinc['tabdata']['tabtitle'], 'action' => $tabinc['tabdata']['action'], 'form' => $tabinc['tabdata']['form'], 'hidden' => $tabinc['tabdata']['hidden'], 'class' => $tabinc['tabdata']['class'] );
					 $this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', print_r( $tabinc, true ) );
					//MERGER
					//Add the ability to add extra menu items
					 if( isset( $tabinc['tabdata']['additional_menus'] ) )
					 {
						 $this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Loading Additional Menus" );
						 foreach( $tabinc['tabdata']['additional_menus'] as $row )
						 {
						 	$this->ObserverNotify( $this, 'NOTIFY_LOG_DEBUG', "Load " . print_r( $row, true ) );
							$tabs[] = $row;
						 }
					 }
				}
			}
			$this->tabs = $tabs;
			$this->ObserverNotify( $this, 'NOTIFY_TABS_LOADED', $tabs );
		}
	}
/****************************splSubject************************************************/
	//public function attach(\SplObserver $observer, $event = "*")
	 function attach(\SplObserver $observer, $event = "*")
    	{
        	$this->initEventGroup($event);
        	$this->observers[$event][] = $observer;
		$this->storage->attach($observer);	//php.net
    	}
    	//public function detach(\SplObserver $observer, $event = "*")
    	 function detach(\SplObserver $observer, $event = "*")
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
    	//public function notify( $event = "*", $data = null)
    	 function notify( $event = "*", $data = null)
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
/*
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
*/
/****************************!php.net****************************************************/
/****************************!splObserver************************************************/
?>
