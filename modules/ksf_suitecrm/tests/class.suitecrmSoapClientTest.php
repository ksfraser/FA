<?php



declare(strict_types=1);
global $path_to_root;
if( $path_to_root == null OR strlen( $path_to_root ) < 5 )
 	$path_to_root = dirname( __FILE__ ) . "/../../../";
use PHPUnit\Framework\TestCase;
require_once( dirname( __FILE__ ) .  '/../class.suitecrmSoapClient.php' );

/*
global $db_connections;	//FA uses for DB stuff
global $_SESSION;
$_SESSION['wa_current_user'] = new stdClass();
$_SESSION['wa_current_user']->company = 1;
$_SESSION["wa_current_user"]->cur_con = 1;
$db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'] = '1_';
$db_connections[1]['tbpref'] = '1_';
 */

//If asserts fail returning type NULL that is because the field
//is PROTECTED or PRIVATE and therefore can't be accessed!!

global $sugar_config;
$sugar_config = array();
//$sugar_config['site_url'] = "http://URL";
//$sugar_config['soapuser'] = "soapuser";
//$sugar_config['user_hash'] = "user_hash";
//$sugar_config['site_url'] = "https://mickey.ksfraser.com/devel/fhs/suitecrm/service/v4_1/rest.php";
$sugar_config['site_url'] = "https://mickey.ksfraser.com/ksfii/suitecrm/service/v4_1/";
$sugar_config['appname'] = "FA_Integration";
//$sugar_config['site_url'] = "https://mickey.ksfraser.com/devel/fhs/suitecrm/service/v4_1/";
$sugar_config['soapuser'] = "admin";
$sugar_config['user_hash'] = md5('m1l1ce');
//$sugar_config['soapuser'] = "kevin";
//$sugar_config['user_hash'] = md5("Letmein1");
global $userGUID;


class suitecrmSoapClientTest extends TestCase
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
	public function testInstanceOf(): suitecrmSoapClient
	{
		$o = new suitecrmSoapClient();
		$this->assertInstanceOf( suitecrmSoapClient::class, $o );
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
		$this->assertSame( $o->get( "result" ), null );	
		$this->assertSame( $o->get( "session_id" ), null );	
		$this->assertSame( $o->get( "module_name" ), null );	
		//$this->assertSame( $o->get( "url" ), $sugar_config['site_url'] . "/rest.php" );
		$this->assertSame( $o->get( "url" ), $sugar_config['site_url'] . "/soap.php" );
		$this->assertSame( $o->get( "username" ), $sugar_config['soapuser'] );
		$this->assertSame( $o->get( "soapCredential" ), $sugar_config['user_hash'] );

		return $o;

		//$this->assertSame( $o->vendor, "name_value_list" );	//var not protected/private
		//$this->assertTrue( is_object( $o->get( 'wc' ) ) );
		//$this->assertSame( $o->get( 'client' ), $this );
		//Constructor also calls add_submodules
	}

	//public function testGet( $o )
	//Covered by ConstructorValues
	
	/**
	 * @depends testConstructorValues
	 */
	public function testBuild_auth_array( $o ) : array
	{
		$o->build_auth_array();
		$auth_array = $o->get( "soap_auth_array" );
		$this->assertIsArray( $auth_array );
		//$this->assertIsArray( $auth_array['user_auth'] );
		//return $o;
		return $auth_array;
	}
	/**
	 * @depends testBuild_auth_array
	 */
	public function testBuild_auth_array_usernane( $o ) : array
	{
		global $sugar_config;
		$this->assertSame( $o['user_name'], $sugar_config['soapuser'] );
		return $o;
	}
	/**
	 * @depends testBuild_auth_array
	 */
	public function testBuild_auth_array_password( $o ) : array
	{
		global $sugar_config;
		$this->assertSame( $o['password'], $sugar_config['user_hash'] );
		return $o;
	}
	/**
	 * @depends testConstructorValues
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
		$o->soapLogin();
		//$this->assertIsArray( $auth_array['user_auth'] );
		$this->assertNotNull( $o->get( "session_id" ) );
		$this->assertNotNull( $o->get( "soapLoginTime" ) );
		//time is resolved in seconds, so it is possible these will be the same. add 1
		$this->assertGreaterThan( $o->get( "soapLoginTime" ), time() + 1 );
		return $o;
	}

	/**
	 * @depends testConstructorValues
	 */
	
	public function testSoapParams( $o )
	{
		try
		{
			//Default class has exception
			$o->setSoapParams( "","" );
		}
		catch( Exception $e )
		{
			$this->assertTrue( true );
			return $o;
		}
		throw Exception( "We shouldn't have made it here!" );
		//$this->expectException( $o->get_value( 1 ) );
	}
	 
	/**
	 * @depends testConstructorValues
	 */
	public function testSoapCall( $o )
	{
		//, array( $o->get( 'session_id' ), "Accounts", "", "", array( 'id', 'name' ), '', '', '')
		$nvl = new name_value_list();
		$nvl->add_nvl( "session_id", $o->get( 'session_id' ) );
		$nvl->add_nvl( "Module", "Accounts" );
		$nvl->add_nvl( "Filter", "" );
		$nvl->add_nvl( "Order_by", "" );
		$nvl->add_nvl( "Start", "" );
		$nvl->add_nvl( "Return", "" );
		$nvl->add_nvl( "Link", "" );
		$nvl->add_nvl( "Results", "" );
		$nvl->add_nvl( "Deleted", "1" );
		//$o->soapParams = $nvl->get_nvl();
		//var_dump( $nvl->get_nvl() );
		$this->assertIsObject( $o->soapCall( "get_entry_list" ) );
		return $o;
	}


	

		/**
	 * @ depends testadd_nvl
		 */
	/*
	public function testget_value( $o )
	{
		$this->assertSame( $o->get_value( 0 ), $this->value );
		try
		{
			$o->get_value( 1 );
		}
		catch( Exception $e )
		{
			return $o;
		}
		throw Exception( "We shouldn't have made it here!" );
		//$this->expectException( $o->get_value( 1 ) );
	}
	 */
}

?>
