<?php



declare(strict_types=1);
global $path_to_root;
if( $path_to_root == null OR strlen( $path_to_root ) < 5 )
 	$path_to_root = dirname( __FILE__ ) . "/../../../";
use PHPUnit\Framework\TestCase;
require_once( dirname( __FILE__ ) .  '/../class.ksfSOAP.php' );


//If asserts fail returning type NULL that is because the field
//is PROTECTED or PRIVATE and therefore can't be accessed!!

global $sugar_config;
$sugar_config = array();
//$sugar_config['site_url'] = "https://mickey.ksfraser.com/ksfii/suitecrm/service/v4_1/soap.php/";
$sugar_config['appname'] = "FA_Integration";
$sugar_config['soapuser'] = "admin";
$sugar_config['user_hash'] = md5('m1l1ce');
global $userGUID;


class ksfSOAPTest extends TestCase
{
	protected $shared_var;
	protected $shared_val;
	protected $name;
	protected $value;
	function __construct()
	{
		parent::__construct();
		$this->shared_var =  'pub_unittestvar';
		$this->shared_val = '1';
		$this->name = "name";
		$this->value = "value";
	}
	public function testInstanceOf(): ksfSOAP
	{
		$o = new ksfSOAP();
		$this->assertInstanceOf( ksfSOAP::class, $o );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorValues( $o )
	{
		global $sugar_config;
		//$this->assertIsArray( $o->nvl );
		$this->assertSame( $o->get( "debug_level" ), 0 );	
		$this->assertSame( $o->get( "retryCount" ), 0 );	
		//ORIGIN throws an error on NULL.
		//$this->assertNull( $o->get( "result" ) );	
		//$this->assertSame( $o->get( "result" ), null );	
		//$this->assertSame( $o->get( "session_id" ), null );	
		//$this->assertSame( $o->get( "module_name" ), null );	
		try
		{
			$r = $o->get( "result" );
			//$this->assertSame( $o->get( "result" ), null );	
		}
		catch( Exception $e )
		{
			$this->assertSame( KSF_FIELD_NOT_SET, $e->getCode() );
		}
		try
		{
			$r = $o->get( "session_id" );
			//$this->assertSame( $o->get( "result" ), null );	
		}
		catch( Exception $e )
		{
			$this->assertSame( KSF_FIELD_NOT_SET, $e->getCode() );
		}
		try
		{
			$r = $o->get( "module_name" );
			//$this->assertSame( $o->get( "result" ), null );	
		}
		catch( Exception $e )
		{
			$this->assertSame( KSF_FIELD_NOT_SET, $e->getCode() );
		}
		return $o;
		//$this->assertTrue( is_object( $o->get( 'wc' ) ) );
	}

	/**
	 * @depends testConstructorValues
	 */
	public function testSetSoapClient( $o ) : ksfSOAP
	{
		global $sugar_config;
		$o->set( 'url', $sugar_config['site_url'] );
		$this->assertSame( $o->get( 'url' ), $sugar_config['site_url'] );
		$o->setSoapClient();
		$this->assertIsObject( $o->get( 'soapClient' ), SoapClient::class );
		//$this->assertSame( $o->get( 'soapClient' ), $sugar_config['site_url'] . '?wsdl' );
		return $o;

	}
	//public function testGet( $o )
	//Covered by ConstructorValues
	
	/**
	 * @depends testConstructorValues
	 */
	public function testSetAuthUser( $o ) 
	{
		global $sugar_config;
		$o->set( 'username', $sugar_config['soapuser'] );
		$this->assertSame( $o->get( 'username' ), $sugar_config['soapuser'] );
	}
	/**
	 * @depends testConstructorValues
	 */
	public function testSetAuthPass( $o )
	{
		global $sugar_config;
		$o->set( 'password', $sugar_config['user_hash'] );
		$this->assertSame( $o->get( 'password' ), $sugar_config['user_hash'] );
	}
	/**
	 * @depends testConstructorValues
	 */
	public function testBuild_auth_array( $o ) : array
	{
		$auth_array = $o->build_auth_array();
		$this->assertIsArray( $auth_array );
		$auth_array = $o->get( "soap_auth_array" );
		$this->assertIsArray( $auth_array );
		return $auth_array;
	}
	/**
	 * @depends testBuild_auth_array
	 */
	public function testBuild_auth_array_usernane( $o_arr ) : array
	{
		global $sugar_config;
		$this->assertSame( $o_arr['user_name'], $sugar_config['soapuser'] );
		return $o_arr;
	}
	/**
	 * @depends testBuild_auth_array
	 */
	public function testBuild_auth_array_password( $o_arr ) : array
	{
		global $sugar_config;
		$this->assertSame( $o_arr['password'], $sugar_config['user_hash'] );
		return $o_arr;
	}
	/**
	 * @ depends testConstructorValues
	 */
	/*
	public function testSoapReconnect( $o ) : array
	{
		//$this->assertIsArray( $auth_array['user_auth'] );
		return $o;
	}
	 */

	/**
	 * @depends testConstructorValues
	 */
	public function testSoapLogin( $o )
	{
		$o->set( 'appname', get_class( $this ) );
		$o->soapLogin();
		print $o->get( "session_id" );
		$this->assertNotNull( $o->get( "session_id" ) );
		$this->assertNotNull( $o->get( "soapLoginTime" ) );
		//time is resolved in seconds, so it is possible these will be the same. add 1
		$this->assertGreaterThan( $o->get( "soapLoginTime" ), time() + 1 );
		return $o;
	}
	/**
	 * @depends testSoapLogin
	 */
	public function testSoapLogout( $o )
	{
		$o->soapLogout();
		try
		{
			$r = $o->get( "session_id" );
		}
		catch( Exception $e )
		{
			$this->assertSame( KSF_FIELD_NOT_SET, $e->getCode() );
		}
		try
		{
			$r = $o->get( "soapLoginTime" );
		}
		catch( Exception $e )
		{
			$this->assertSame( KSF_FIELD_NOT_SET, $e->getCode() );
		}
	}

	/**
	 * @ depends testSoapLogin
	 * /
	public function testSetSoapParam( $o )
	{
	}
	 */
	 
	/**
	 * @depends testSoapLogin
	 */
	public function testSoapCall( $o )
	{
		try
		{
			//soapParams isn't set so should get an Exception
			$o->soapCall( "Test" );
		}
		catch( Exception $e )
		{
			$this->assertSame( KSF_VALUE_NOT_SET, $e->getCode() );
		}
		try
		{
			$nvl = new name_value_list();
			$nvl->add_nvl( "session_id", $o->get( 'session_id' ) );
			$nvl->add_nvl( "Module", "Accounts" );
			$o->set( "soapParams", $nvl->get_nvl() );
			$this->assertSame( $o->get( 'soapParams' ), $nvl->get_nvl() );
			$this->assertIsObject( $o->soapCall( "get_entry_list" ) );
		}
		catch( Exception $e )
		{
			print $e->getMessage();
			$this->assertSame( KSF_VALUE_NOT_SET, $e->getCode() );
		}
		return $o;
	}
	/**
	 * @depends testSoapLogin
	 */
	public function testGetFunctions( $o )
	{
		$this->assertIsArray( $o->getFunctions() );
	}

}

?>
