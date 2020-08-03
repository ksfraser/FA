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

	function __construct( $moduledir = null, $caller = null )
	{
		parent::__construct();
		$this->caller = $caller;
		$this->storage = new SplObjectStorage();	//php.net
		$this->initEventGroup( '*' );
		$this->initEventGroup( '**' );
 		/* 
		 * locate Module class files to open 
		 */
		if( ! isset( $moduledir ) )
			$moduledir = dirname( __FILE__ ) . '/modules';
		$this->set_moduledir( $moduledir );
		$this->load_modules();
		$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Completed Adding Modules" );
		$this->ObserverNotify( $this, 'NOTIFY_INIT_CONTROLLER_COMPLETE', "Completed Adding Modules" );
	}
	function build_interestedin()
	{
		parent::build_interestedin();
		$this->interestedin['NOTIFY_DUMP_OBSERVERS']['function'] = "dumpObservers";
	}
	function set_moduledir( $dir )
	{
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
		$this->moduledir = $dir;
	}
	/*
	function notified( $obj, $event, $msg )
	{
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
		if( $event == "NOTIFY_DUMP_OBSERVERS" )
			$this->dumpObservers();
		else
			parent::notified();
               	return SUCCESS;
	}
 	*/
	function dumpObservers( $obj, $msg )
	{
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
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
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
		//return FALSE;
		$this->initEventGroup( $event );
		echo "Attaching Observer " . get_class( $observer ) . " to event " . $event . "\n\r";
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
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
              	$this->observers[] = array_diff( $this->observers, array( $observer) );
              	return SUCCESS;
        }
	private function initEventGroup($event = "*")
 	{
			echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
		if (!isset($this->observers[$event])) {
	        	$this->observers[$event] = [];
			echo "Created observer Event Group for " . $event . "\n\r";
	     	}
 	}
        /*****************************************************************************//**
         *ObserverNotify loops throug observers and tells interested ones about the event
         *
         * @param string event to match against
         * @data mixed ideally is the object that triggered the event
         *
         * *******************************************************************************/
        function ObserverNotify( $trigger_class, $event, $msg )
        {
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
		var_dump( $msg );
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
	private function getEventObservers($event = "*")
    	{
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
        	$this->initEventGroup($event);
        	$group = $this->observers[$event];
        	$all = $this->observers["*"];
        	return array_merge($group, $all);
    	}
	function load_modules()
	{
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
		global $configArray;
		//We should probably check for the existance of the moduledir.
		//See Mantis 214 for what triggered this...
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
				$modarray[$carray['loadpriority']][] = $carray;
				//Add to tabs...
                                $tabarray[$carray['taborder']][] = $carray;

			}
		}
		if( isset( $modarray ) AND count( $modarray ) > 0 )
		{
			foreach( $modarray as $priarray )
			{
				foreach( $priarray as $marray )
				{
					$res = @include_once( $this->moduledir . "/" . $marray['loadFile'] );
					if( TRUE == $res )
					{
						$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Module " . $marray['ModuleName'] . " being added" );

						//Passing this to the called class, they set us as the event dispatcher
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
						//echo "Attempt to open " . $this->moduledir . "/" . $marray['loadFile'] . " FAILED!<br />";
						$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Unable to add module" . $this->moduledir );
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
				}
			}
			$this->tabs = $tabs;
			$this->ObserverNotify( $this, 'NOTIFY_TABS_LOADED', $tabs );
		}
	}
/****************************splSubject************************************************/
	public function attach(\SplObserver $observer, $event = "*")
    	{
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
        	$this->initEventGroup($event);
        	$this->observers[$event][] = $observer;
		$this->storage->attach($observer);	//php.net
    	}
    	public function detach(\SplObserver $observer, $event = "*")
    	{
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
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
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
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
