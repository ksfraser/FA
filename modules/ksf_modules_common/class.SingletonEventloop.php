<?php

class SingletonEventloop implements splSubject
{
	private static $instance = null; 		// Hold the class instance.
	private $observers = [];
	private $storage;       //From php.net example
	private $last_triggering_class;

	// The constructor is private
	// to prevent initiation with outer code.
	private function __construct()
	{
	    	// The expensive process (e.g.,db connection) goes here.
		$this->storage = new SplObjectStorage();
	}
	public setLastTriggeringClass( $class )
	{
		$this->last_triggering_class = $class;
	}
	public getLastTriggeringClass( $class )
	{
		return $this->last_triggering_class;
	}
	// The object is created from within the class itself
	// only if the class has no instance.
	public static function getInstance()
	{
	    	if (self::$instance == null)
	    	{
	      		self::$instance = new SingletonEventloop();
	    	}
	    	return self::$instance;
	}
        private function initEventGroup($event = "*")
        {
                if (!isset($this->observers[$event])) {
                        $this->observers[$event] = [];
                }
        }
	function ObserverRegister( /*Class Instance*/$observer, $event = "*" )
        {
                $this->initEventGroup( $event );
                $this->observers[$event][] = $observer; //Indirect modification has no effect ERROR
        }
        function ObserverDeRegister( $observer )
        {
                $this->observers[] = array_diff( $this->observers, array( $observer) );
                return;
        }
        function ObserverNotify( $trigger_class, $event, $msg )
        {
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
                return;
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
                $this->storage->attach($observer);      //php.net
        }
        public function detach(\SplObserver $observer, $event = "*")
        {
                $this->storage->detach($observer);      //php.net
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

?>
	
