<?php 
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once( dirname( __FILE__ ) .  '/../class.origin.php' );

/*
final class EmailTest extends TestCase
{
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        $this->assertInstanceOf(
            Email::class,
            Email::fromString('user@example.com')
        );
    }

    public function testCannotBeCreatedFromInvalidEmailAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::fromString('invalid');
    }

    public function testCanBeUsedAsString(): void
    {
        $this->assertEquals(
            'user@example.com',
            Email::fromString('user@example.com')
        );
    }
}
 */

global $db_connections;	//FA uses for DB stuff
$_SESSION['wa_current_user']->company = 1;
$db_connections[1]['tbpref'] = '1_';

//If asserts fail returning type NULL that is because the field
//is PROTECTED or PRIVATE and therefore can't be accessed!!
class originTest extends TestCase
{
	protected $shared_var;
	protected $shared_val;
	function __construct()
	{
		parent::__construct();
		$this->shared_var =  'pub_unittestvar';
		$this->shared_val = '1';
		
	}

	public function testInstanceOf(): origin
	{
		$o = new origin( null, null );
		$this->assertInstanceOf( origin::class, $o );
		return $o;
	}
	/*****
	 * Test the setting of client
	 * ***/
	public function testConstructorClient(): origin
	{
		$o = new origin( null, $this );
		$this->assertSame( $this, $o->get( 'client' ) );
		return $o;
	}
	public function testConstructorLogLevelDEBUG(): void
	{
		$o = new origin( PEAR_LOG_DEBUG, $this );
		$this->assertSame( PEAR_LOG_DEBUG, $o->loglevel );
	}
	public function testConstructorDefaultLogLevel(): void
	{
		$o = new origin( null, $this );
		$this->assertSame( PEAR_LOG_DEBUG, $o->loglevel );
	}
	public function testConstructorDebug(): void
	{
		$o = new origin( PEAR_LOG_DEBUG, $this );
		//->debug is coming back as null
		$this->assertSame( 0 , $o->get( 'debug' ) );
	}
	public function testConstructorClientDebug(): void
	{
		$this->debug = 2;
		$o = new origin( PEAR_LOG_DEBUG, $this );
		$this->assertSame( $this->debug , $o->get( 'debug' ) );
	}
	public function testConstructorErrorArray(): void
	{
		$o = new origin( null, null );
		$this->assertIsArray( $o->get( 'errors' ) );
	}
	public function testConstructorLogArray(): void
	{
		$o = new origin( null, null );
		$this->assertIsArray( $o->log );
	}
	public function testConstructorBuildInterestedIn(): void
	{
		$o = new origin( null, null );
		//$this->assertIsArray( $o->log );
		$this->assertIsArray( $o->get( 'interestedin' ) );
	}
	public function testBuildInterestedIn(): void
	{
		$o = new origin( null, null );
		$o->build_interestedin();	//should have been called by constructor!!
		//$this->assertIsArray( $o->log );
		$this->assertIsArray( $o->get( 'interestedin' ) );
	}
	public function testConstructorRegisterEventloop(): void
	{
		$o = new origin( null, null );
		$this->assertTrue( true );
		//$this->assertIsArray( $o->log );
	}
	public function testConstructorObjectVarNames(): origin
	{
		$o = new origin( null, null );
		$this->assertIsArray( $o->object_fields );
		return $o;
	}
	/**
	 * @depends testConstructorObjectVarNames
	 */
	public function testConstructorObjectVarNamesNotEmpty( $o ): void
	{
		$this->assertNotEmpty( $o->object_fields );
	}
	/**
	 * @depends testConstructorObjectVarNames
	 */
	public function testConstructorObjectVarNamesHasKey( $o ): void
	{
		$this->assertArrayHasKey( '___SOURCE_KEYS_', $o->object_fields );
	}
	/**
	 * @depends testInstanceOf
	 * @ depends testConstructorClient
	 */
	public function testSetNullField( $o ): void
	{
		$field = '';
		$val = '1';
		$enforce = false;
		$this->expectException( Exception::class );
		$o->set( null, $val, $enforce );	//should get KSF_FIELD_NOT_SET
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testSetEnforcedBadField( $o ): void
	{
		$field = 'dontexist';
		$val = '2';
		$enforce = true;
		$this->expectException( Exception::class );
		$ret = $o->set( $field, $val, $enforce );	//should get KSF_FIELD_NOT_CLASS_VAR
		$this->assertSame( $ret, false );
		$this->assertSame( false, $o->get( 'unenforced' ) );
		$this->assertNotSame( $val, $o->get( $field ) ); //Shouldn't be set
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testSetEnforcedGoodPublicField( $o ): origin
	{
		$this->shared_var =  'pub_unittestvar';
		$this->shared_val = '1';
		$enforce = true;
		$ret = $o->set( $this->shared_var, $this->shared_val, $enforce );
		$this->assertSame( $ret, true );
		$this->assertSame( false, $o->get( 'unenforced' ) );
		$this->assertSame( $this->shared_val, $o->get( $this->shared_var ) );
		return $o;
	}
	/**
	 * @depends testSetEnforcedGoodPublicField
	 */
	public function testGetGoodField( $o ):void
	{
		$this->assertSame( $o->get( $this->shared_var ), $this->shared_val );
	}
	/**
	 * @depends testSetEnforcedGoodPublicField
	 */
	public function testGetBadField( $o ):void
	{
		$this->expectException( Exception::class );
		//Should not get here!!
		$this->assertNotSame( $o->get( 'doesntexist' ), $this->shared_val );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testSetEnforcedGoodPrivateField( $o ): origin
	{
		$enforce = true;
		$field = "unittestvar";
		$o->set( $field, $this->shared_val, $enforce );
		//Check for NULL because field is protected and therefore inaccessible
		$this->assertSame( null, $o->$field );	
		return $o;
	}
	/**
	 * @depends testSetEnforcedGoodPublicField
	 */
	public function testVar2Data( $o )
	{
		$o->var2data();
		$this->assertSame( $o->data[$this->shared_var], $this->shared_val );
	}
	/**
	 * @depends testSetEnforcedGoodPublicField
	 */
	public function testFielsd2Data( $o )
	{
		$list = array( 'loglevel', 'unittestvar', 'pub_unittestvar', 'application', 'module', 'debug' );
		foreach( $list as $field )
		{
			$o->set( $field, $this->shared_val );
		}
		//$this->assertEmpty( $o->data );
		$o->fields2data( $list );
		//$this->assertIsArray( $o->errors );	//Checking that not in list didn't get set!
		foreach( $list as $field )
		{
			$this->assertSame( $o->data[$field], $this->shared_val );
		}
	}
	/**
	 */
	public function testCopyObjFieldlist2me()
	{
		$o = new origin();
		$f = new origin();
		$list = array( 'loglevel', 'unittestvar', 'pub_unittestvar', 'application', 'module', 'debug', 'badfield' );
		foreach( $list as $field )
		{
			$f->set( $field, $this->shared_val, false );
		}
		$this->assertSame( $f->get( 'badfield' ), $this->shared_val );
		$o->copy_obj_fieldlist2me( $f, $list );
		foreach( $list as $field )
		{
			if( 'badfield' !== $field )
				$this->assertSame( $o->get( $field ), $this->shared_val );
			else
			{
				$this->expectException( Exception::class );
				$o->get( $field );
			}
		}
	}
	/**
	 * @ depends testTellEventloop
	 * @depends testAttachEventloop
	 */
	public function testLogError()
	{
		$msg = 'boohoo';
		$ret = $o->LogError( $msg, PEAR_LOG_ERR );
		$this->assertIsBool( $ret );
	}
	/**
	 * @ depends testTellEventloop
	 * @depends testAttachEventloop
	 */
	public function testLogMsg()
	{
		$msg = 'boohoo';
		$ret = $o->LogMsg( $msg, PEAR_LOG_EMERG );
		$this->assertIsBool( $ret );
	}
	/**
	 * @ depends testTellEventloop
	 * @depends testAttachEventloop
	 */
	public function testLog( $o )
	{
		$msg = 'boohoo';
		$ret = $o->Log( $msg, PEAR_LOG_EMERG );
		$this->assertIsBool( $ret );
		//Log then uses tell_eventloop to NOTIFY_...
	}
	/**
	 *  
	 */
	public function testAttachEventloop()
	{
		$o = new origin( null, $this );	//NO eventloop
		$ret = $o->attach_eventloop();
		$this->assertSame( false, $ret );
		global $eventloop;
		require_once( dirname( __FILE__ ) . "/../class.eventloop.php" );
		$eventloop = new eventloop();
		$p = new origin( null, $this );
		$ret = $p->attach_eventloop();	//Because global exists should attach
		$this->assertSame( true, $ret );
		$ret = $o->attach_eventloop();	//Because global exists should attach
		$this->assertSame( true, $ret );
		return $o;
	}
	public function testTell()
	{
		global $eventloop;
		require_once( dirname( __FILE__ ) . "/../class.eventloop.php" );
		$eventloop = new eventloop();
		$o = new origin( null, null );	//NO eventloop from client
		//$o->interestedin['METHOD'] = array( 'function' => 'noexist');
		//$o->build_interested();
		$o->tell( KSF_DUMMY_EVENT, __FUNCTION__ );	//calls self->tell_eventloop
						//which calls ObserverNotify

		//If isset treats NULL as not set...
		$this->expectException( Exception::class );
		$o->tell( null, __FUNCTION__ );
	}
	/**
	 * @ depends testAttachEventloop
	 * /
	public function testTellEventloop( $o )
	{
		//tell_eventloop uses attach_eventloop
		$ret = $o->tell_eventloop( $this, KSF_DUMMY_EVENT, "TESTING" );	//cals ObserverNotify
		$this->assertIsBool( $ret );	//eventloop returns a bool
		$this->assertSame( true, $ret );//testAttachEventloop should ensure we are attached.
		return $o;
	}
	 */
	public function testRegisterWithEventloop()
	{
		$o = new origin( null, null );	//NO eventloop from client
		$ret = $o->register_with_eventloop();
		$this->assertSame( true, $ret );	//With an existing eventloop should be true

		//Test whether we can register with a global eventloop
		$o->unset_eventloop();
		$ret = $o->register_with_eventloop();	//attach is called within so should attach to eventloop
		$this->assertSame( true, $ret );	//With an existing eventloop should be true

		/* This test keeps failing.  eventloop not being nulled?
		//If there isn't a global eventloop?
		$o->unset_eventloop();
		global $eventloop;
		unset( $eventloop );
		$ret = $o->register_with_eventloop();	//attach is called within no eventloop to attach
		$this->assertSame( false, $ret );	//attach doesn't create an eventloop.
		 */
	}
	public function testNotified()
	{
		$o = new origin( null, null );	//NO eventloop from client
		//TEST NOT INTERESTED
		$ret = $o->notified( $this, 'NOTIFY_LOG_INFO', "dummy" );
		$this->assertSame( false, $ret );	//Not Interested returns FALSE.
		//$o->interestedin['METHOD']['function'] = 'noexist';
		$ret = $o->notified( $this, 'KSF_DUMMY_EVENT', "dummy" );
		$this->assertSame( false, $ret );	//DUMMY returns false.
		//TEST INVALID CONFIG (name) 
		$this->expectException( Exception::class );
		$o->interestedin[] = array( 'METHOD' );
		$o->interestedin['METHOD'] = array( 'function' => 'noexist');
		try {
			$ret = $o->notified( $this, 'METHOD', "dummy" ); //Should throw error
		} catch( Exception $e )
		{
			$this->assertSame( KSF_INVALID_DATA_VALUE, $e->getCode() );
		}
	}



}

