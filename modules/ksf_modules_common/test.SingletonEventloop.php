<?php

require_once( 'class.SingletonEventloop.php' );

try {
	//$t = new SingletonEventloop();		//Uncaught Error: Call to private SingletonEventloop::__construct()
	$t = SingletonEventloop::getInstance();
}
catch( Exception $e )
{
	//should be an error about instantiation!
	echo $e->getCode();
	echo $e->getMessage();
}

class ConcreteObserverB implements \SplObserver
{
    public function update(\SplSubject $subject)
    {
        //if ($subject->state == 0 || $subject->state >= 2) {
            echo "ConcreteObserverB: Reacted to the event.\n";
        //}
    }
	 function notified( $object, $event, $message )
         {
		echo get_class( $object ) . " Triggered Event " . $event . " with message " . $message;
                //Called when another object we are observing sends us a notification
                //Needs to be extended by the inheriting class
                return;
         }
}


if( null == $t )
{
	echo "Instance Null";
}
else
{
	$a = new ConcreteObserverB( $t );
	if( null == $a )
	{
		echo "Observer null";
	}
	else
	{
		$t->attach( $a );
		$t->ObserverRegister( $a, "*" );
		$t->ObserverNotify( $a, "*", "DATA" );
		$t->notify( "*", "DATA" );
	}
}


?>
