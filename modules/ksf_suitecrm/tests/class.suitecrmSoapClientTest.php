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
$sugar_config['site_url'] = "https://mickey.ksfraser.com/ksfii/suitecrm/service/v4_1/soap.php";
$sugar_config['appname'] = "FA_Integration";
$sugar_config['soapuser'] = "admin";
$sugar_config['user_hash'] = md5('m1l1ce');
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
		$this->assertIsObject(  $o->get( "soapClient" ) );	
		$this->assertInstanceOf( ksfSOAP::class, $o->get( "soapClient" ) );	

		$this->assertIsString( $o->soapClient->get( "url" ) );	
		$this->assertIsString( $o->soapClient->get( "appname" ) );	
		$this->assertIsString( $o->soapClient->get( "username" ) );	
		$this->assertIsString( $o->soapClient->get( "password" ) );	
		//$this->assertSame( $o->get( "retryCount" ), 0 );	
		//$this->assertSame( $o->get( "result" ), null );	
		//$this->assertSame( $o->get( "session_id" ), null );	
		//$this->assertSame( $o->get( "module_name" ), null );	
		//$this->assertSame( $o->get( "url" ), $sugar_config['site_url'] . "/rest.php" );
		$this->assertSame( $o->soapClient->get( "url" ), $sugar_config['site_url'] . "/soap.php?wsdl" );
		$this->assertSame( $o->soapClient->get( "username" ), $sugar_config['soapuser'] );
		$this->assertSame( $o->soapClient->get( "password" ), $sugar_config['user_hash'] );

		$this->assertInstanceOf( name_value_list::class, $o->get( "nvl" ) );	

		return $o;
	}
//soap_url implicitly tested in constructor
//appname implicitly tested in constructor
//soapuser implicitly tested in constructor
//user_hash implicitly tested in constructor
	 
	/**
	 * @depends testConstructorValues
	 */
	public function testSoapCall( $o )
	{
		//, array( $o->get( 'session_id' ), "Accounts", "", "", array( 'id', 'name' ), '', '', '')
		
		$nvl = new name_value_list();
		$nvl->add_nvl( "session_id", $o->soapClient->get( 'session_id' ) );
		$nvl->add_nvl( "Module", "Accounts" );
		$nvl->add_nvl( "Filter", "" );
		$nvl->add_nvl( "Order_by", "" );
		$nvl->add_nvl( "Start", "" );
		$nvl->add_nvl( "Return", "" );
		$nvl->add_nvl( "Link", "" );
		$nvl->add_nvl( "Results", "" );
		$nvl->add_nvl( "Deleted", "1" );
		$o->soapClient->set( "soapParams", $nvl->get_nvl() );
		 
		//$o->soapParams = $nvl->get_nvl();
		//var_dump( $nvl->get_nvl() );
		$this->assertIsObject( $o->soapCall( "get_entry_list" ) );
		return $o;
	}
	/**
	 * @depends testConstructorValues
	 */
	public function testGet_one( $o )
	{
		$strings = "strings";
		try
		{
			$o->get_one( $strings );
			$this->assertTrue( false ); //We shouldn't get here!
		}
		catch( Exception $e )
		{
			$this->assertSame( "Not an array.  Did you mean ->get", $e->getMessage() );
		}
		$arr = array();
		try
		{
			$o->get_one( $arr );
			$this->assertTrue( false ); //We shouldn't get here!
		}
		catch( Exception $e )
		{
			print_r( $e->getMessage(), true ); 
			$this->assertSame( "0 element not set", $e->getMessage() );
		}
		$r = "Test";
		$arr[] = $r;
		try
		{
			$res = $o->get_one( $arr );
			$this->assertSame( $r, $res );
		}
		catch( Exception $e )
		{
		}
	}
	/**
	 * @depends testConstructorValues
	 */
	public function testsetSoapParams( $o )
	{
		$mod = "Accounts";
		$nvl = new name_value_list();
		$nvl->add_nvl( "Filter", "" );
		$nvl->add_nvl( "Order_by", "" );
		$nvl->add_nvl( "Start", "" );
		$nvl->add_nvl( "Return", "" );
		$nvl->add_nvl( "Link", "" );
		$o->setSoapParams( $mod, $nvl->get_nvl() );
		$res = $o->get( "soapParams" );
		$this->assertIsArray( $res );
		return $res;
	}
	/**
	 * @depends testsetSoapParams
	 * @depends testConstructorValues
	 */
	public function testsetSoapParams_output( $res_array, $o )
	{
		$expect = array( $o->soapClient->get( "session_id" ), "Accounts", "", "", "", "", "" );
		$this->assertSame( $res_array, $expect );
	}
	
		
	/**
	 * @ depends testConstructorValues
	 * /
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
	 */

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
		throw new Exception( "We shouldn't have made it here!" );
		//$this->expectException( $o->get_value( 1 ) );
	}


	/**
	 * @ depends testConstructorValues
	 *
	public function testGetFunctions( $o )
	{
		$this->assertIsArray( $o->getFunctions() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	 */

	/**
	 * @depends testConstructorValues
	 */
	public function testInit( $o )
	{
		$o->set( "module_name", "Accounts" );
		$o->set( "module_id", "" );
		$o->set( "module_ids", array( "" ) );
		$o->set( "record_id", "" );
		$o->set( "related_ids", array( "" ) );
		$o->set( "related_fields", array( "" ) );
		$o->set( "related_module_query", "" );
		$o->set( "module_names", array( "Accounts" ) );
		$o->set( "query", "" );
		$o->set( "track_view", "" );
		$o->set( "max_results", "10" );
		$o->set( "delete", "0" );
		$o->set( "order_by", "" );
		$o->set( "offset", "" );
		$o->set( "record_ids", array() );
		$o->set( "select_fields", array() );
		$o->set( "link_field_name", "" );
		$o->set( "link_field_names", array() );
		$o->set( "link_name_to_fields_array", array() );
		$this->assertTrue( true ); //Getting rid of RISKY TEST warning
		return $o;
	}
	/**
	 * @depends testInit
	 */
	public function testGet_entry( $o )
	{
		$this->assertIsObject( $o->get_entry() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 */
	public function testGet_entries( $o )
	{
		$this->assertIsObject( $o->get_entries() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 */
	public function testGet_entry_list( $o )
	{
		$this->assertIsObject( $o->get_entry_list() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 */
	public function testSet_relationship( $o )
	{
		$this->assertIsObject( $o->set_relationship() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 */
	public function testSet_relationships( $o )
	{
		$this->assertIsObject( $o->set_relationships() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 */
	public function testGet_relationships( $o )
	{
		$this->assertIsObject( $o->get_relationships() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 */
	public function testGet_upcoming_activities( $o )
	{
		$this->assertIsObject( $o->get_upcoming_activities() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 */
	public function testGet_modified_relationships( $o )
	{
		$this->assertIsObject( $o->get_modified_relationships() );
		//$this->assertIsArray( $o->getFunctions() );
	}


	/**
	 * @ depends testConstructorValues
	 * /
	public function testSoapLogout( $o )
	{
		$o->soapLogout();
		//$this->assertIsArray( $auth_array['user_auth'] );
		$this->assertIsNull( $o->get( "session_id" ) );
		$this->assertIsNull( $o->get( "soapLoginTime" ) );
		//time is resolved in seconds, so it is possible these will be the same. add 1
		return $o;
	}
	 */


	

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
