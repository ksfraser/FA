<?php



declare(strict_types=1);
global $path_to_root;
if( $path_to_root == null OR strlen( $path_to_root ) < 5 )
 	$path_to_root = dirname( __FILE__ ) . "/../../../";
use PHPUnit\Framework\TestCase;
require_once( 'defines.php' );
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
//$sugar_config['soapuser'] = "kevin";
//$sugar_config['user_hash'] = md5('Letmein1');
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
	/**
	 * Initializes a bunch of values.  Doesn't really test anything.
	 * Refactoring of these values being set elsewhere.
	 * @depends testConstructorValues
	 */
	public function testInit( $o )
	{
		//$o->set( "module_name", "Accounts" );
		//$o->set( "module_id", "" );
		$o->set( "module_name", "Contacts" );
		$o->set( "module_id", "17ba9fdb-2f18-b080-cd7b-5f7390c41f0d" );	//Test Print
		$o->set( "module_ids", array( "" ) );
		$o->set( "record_id", "" );
		$o->set( "related_ids", array( "" ) );
		$o->set( "related_fields", array( "" ) );
		$o->set( "related_module_query", "" );
		$o->set( "module_names", array( "Accounts" ) );
		$o->set( "query", "" );
		$o->set( "track_view", "" );
		$o->set( "max_results", "10" );
		$o->set( "limit", "10" );
		$o->set( "delete", "0" );
		$o->set( "deleted", "0" );
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

//soap_url implicitly tested in constructor
//appname implicitly tested in constructor
//soapuser implicitly tested in constructor
//user_hash implicitly tested in constructor
//
	/**
	 * @depends testConstructorValues
	 */
	public function testSoapCall_NoParams( $o )
	{
		try {
			////var_dump( $o );
			$ret = $o->soapCall( "get_entry_list" );
			$this->assertIsObject( $ret );
			//Getting a NULL back.  Is that expected from soapCall?
		}
		catch( Exception $e )
		{
			//At a guess because we don't have soapParams set, the underlying
			//PHP soap client returning null?
			$this->assertSame( KSF_RESULT_NOT_SET, $e->getCode() );
		}
		////var_dump( $ret );
		return $o;
	}
	 
	/**
	 * @depends testConstructorValues
	 */
	public function testSoapCall_ParamsSet( $o )
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

		$o->set( "soapParams", $nvl->get_nvl() );
		try {
			$ret = $o->soapCall( "get_entry_list" );
			$this->assertIsObject( $ret );
		}
		catch( Exception $e )
		{
			//Getting 8 vs 573000
			$this->assertSame( KSF_FIELD_NOT_SET, $e->getCode() );
		}
		return $o;
	}
	/**
	 * @depends testConstructorValues
	 */
	public function testSoapCall_ParamsPassed( $o )
	{
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
		try {
			$ret = $o->soapCall( "get_entry_list", $nvl->get_nvl() );
			$this->assertIsObject( $ret );
		}
		catch( Exception $e )
		{
			//Getting 8 vs 573000
			$this->assertSame( KSF_FIELD_NOT_SET, $e->getCode() );
		}
		return $o;
	}
	/**
	 * Passing in a string
	 * @depends testConstructorValues
	 */
	public function testGet_one_NotArray( $o )
	{
		$strings = "strings";
		$o->set( "test_arr", $strings );
		try
		{
			$o->get_one( "test_arr" );
			$this->assertTrue( false ); //We shouldn't get here!
		}
		catch( Exception $e )
		{
			//$this->assertSame( "Not an array.  Did you mean ->get()", $e->getMessage() );
			$this->assertSame( KSF_INVALID_DATA_TYPE, $e->getCode() );
		}
	}
	/**
	 * Passing in an array that doesn't have zero set
	 * @depends testConstructorValues
	 */
	public function testGet_one_ZeroNotSet( $o )
	{
		$arr = array();
		$o->set( "test_arr", $arr );
		try
		{
			$ret = $o->get_one( "test_arr" );
			$this->assertTrue( false ); //We shouldn't get here!
		}
		catch( Exception $e )
		{
			$this->assertSame( KSF_VAR_NOT_SET, $e->getCode() );
			//getting 8 vice 573002
		}
	}
	/**
	 * @depends testConstructorValues
	 */
	public function testGet_one( $o )
	{
		$strings = "strings";
		
		$r = "Test";
		$arr = array();
		$arr[] = $r;
		$o->set( "test_arr", $arr );
		try
		{
			$res = $o->get_one( "test_arr" );
			$this->assertSame( $r, $res );
		}
		catch( Exception $e )
		{
			$this->assertTrue( false ); //We shouldn't get here!
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
		if( $o->setSoapParams( $mod, $nvl->get_nvl() ) )
		{
			//print "//var_dump in setSoapParams shows soapParams to be an array\n";
			////var_dump( $o->soapParams );
			//print "\n";
			////var_dump( $o );
			$res = $o->get( "soapParams" );
			$this->assertIsArray( $res );
			return $res;
		}
		else
		{
			$this->assertTrue( False );
			return null;
		}
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
	 * @ depends testConstructorValues
	 *
	public function testGetFunctions( $o )
	{
		$this->assertIsArray( $o->getFunctions() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	 */

	/**
	 * @depends testInit
	 */
	public function testGet_entry( $o )
	{
		$o->set( "module_name", "Contacts" );
		$o->set( "record_id", "17ba9fdb-2f18-b080-cd7b-5f7390c41f0d" );	//print test 
		$ret = $o->get_entry();
		////var_dump( $ret );
		$this->assertIsObject( $ret );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 */
	public function testGet_entries( $o )
	{
		//Query doesn't get used by get_entry
		//$o->set( "query", "name like '%print%'" );
		$o->set( "module_name", "Contacts" );
						//deleted, 	test test, print test
		$o->set( "record_ids", array( "3D55e3344b-c045-7ca3-5078-603e93941b99", "3Defb4a753-8c48-d356-7f99-603e931503e1" , "17ba9fdb-2f18-b080-cd7b-5f7390c41f0d" ) );
		$ret = $o->get_entries();
		////var_dump( $ret );
		$this->assertIsObject( $ret );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 */
	public function testGet_entry_list( $o )
	{
		$o->set( "query", "name like '%print%'" );
		$res = $o->get_entry_list();
		////var_dump( $res );
		$this->assertIsObject( $res );
	}
	/**
	 * @depends testInit
	 * /
	public function testSet_relationship( $o )
	{
		$this->assertIsObject( $o->set_relationship() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 * /
	public function testSet_relationships( $o )
	{
		//Invalid DATA sent
		$this->assertIsObject( $o->set_relationships() );
		//$this->assertIsArray( $o->getFunctions() );
	}
	/**
	 * @depends testInit
	 */
	public function testGet_relationships( $o )
	{
		//Record print test
		$o->set( "module_name", "Contacts" );
		$o->set( "module_id", "17ba9fdb-2f18-b080-cd7b-5f7390c41f0d" );
		$ret = $o->get_relationships();
		//Getting ACCESS DENIED
		////var_dump( $res );
		$this->assertIsObject( $ret );
	}
	/**
	 *
	 * @depends testInit
	 * /
	 public function testSet_entry( $o )
	 {
		 	//The following is tested working with code in client.protect.php
			//$ret = $this->soapClient->set_entry( 
			//	$this->get( 'session_id' ), 
			//	$this->get( 'module_name' ),
			//	$this->get( 'nvl' )						
		 	//);
		 $ret = $o->set_entry();
		 $this->assertTrue( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 * /
	 public function testSet_entries( $o )
	 {
		 	//The following is tested working with code in client.protect.php
			//$ret = $this->soapClient->set_entry( 
			//	$this->get( 'session_id' ), 
			//	$this->get( 'module_name' ),
			//	$this->get( 'nvl' )						
		 	//);
		 $ret = $o->set_entries();
		 $this->assertTrue( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 */
	 public function testGet_server_info( $o )
	 {
		 //Depends on a session_id.
		 $ret = $o->get_server_info();
		 ////var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 */
	 public function testGet_user_id( $o )
	 {
		 //Depends on a session_id.
		 $ret = $o->get_user_id();
		 ////var_dump( $ret );
		 //Returned '1' 
		 //Using admin for testing, so ID is 1
		 $this->assertSame( '1', $ret );
	 }
	/**
	 *
	 * @depends testInit
	 */
	 public function testGet_module_fields( $o )
	 {
		 //Depends on a session_id, module .
		 $ret = $o->get_module_fields();
		 ////var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
		/**
	 *
	 * @depends testInit
	 */
	 public function testSeamless_login( $o )
	 {
		 //Depends on a session_id.
		 $ret = $o->seamless_login();
		 //Returns 1 (TRUE?)
		 ////var_dump( $ret );
		 $this->assertSame( 1, $ret );
	 }
	/**
	 *
	 * @depends testInit
	 * /
	 public function testSet_note_attachment( $o )
	 {
		 //Depends on a session_id, note attachment .
		 $ret = $o->set_note_attachment();
		 //var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 */
	 public function testGet_note_attachment( $o )
	 {
		 //Depends on a session_id, record_id
		 //$o->set( 'record_id', "8eb30b55-bd28-fb88-1107-5d9261ebe489" );	//Attachment?
		 $o->set( 'record_id', "3D6657ee4d-2c80-5339-5fb9-5b32acb9870d" );
		 $o->set( 'record_id', "5b4a3e5d-d6a4-e66d-1ffa-603ebc8716e2" ); 
		 $ret = $o->get_note_attachment();
		 ////var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 * /
	 public function testSet_document_revision( $o )
	 {
		 //Depends on a session_id, note attachment .
		 $ret = $o->set_document_revision();
		 //var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 * /
	 public function testSearch_by_module( $o )
	 {
		 $ret = $o->search_by_module();
		 ////var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 * /
	 public function testGet_available_modules( $o )
	 {
		 $ret = $o->get_available_modules();
		 ////var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
	/**
	 * Not defined in v4.1
	 * @depends testInit
	 * /
	 public function testGet_user_team_id( $o )
	 {
		 $ret = $o->get_user_team_id();
		 //var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 */
	 public function testSet_campaign_merge( $o )
	 {
		 $ret = $o->set_campaign_merge();
		 //var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 */
	 public function testGet_entries_count( $o )
	 {
		 //uses module_name, query, deleted
		 $o->set( 'module_name', 'Accounts' );
		 $o->set( 'query', '' );
		 $o->set( 'deleted', '1' );
		 $ret = $o->get_entries_count();
		 //var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 */
	 public function testGet_module_fields_md5( $o )
	 {
		 $ret = $o->get_module_fields_md5();
		 //var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }
	/**
	 *
	 * @depends testInit
	 */
	 public function testGet_last_viewed( $o )
	 {
		 $ret = $o->get_last_viewed();
		 //var_dump( $ret );
		 $this->assertIsObject( $ret );
	 }

	/**
	 * @depends testInit
	 */
	public function testGet_upcoming_activities( $o )
	{
		//Array on successful download
		//stdClass on error
		$this->assertIsArray( $o->get_upcoming_activities() );
	}
	/**
	 * @depends testInit
	 */
	public function testGet_modified_relationships( $o )
	{
		$this->assertIsObject( $o->get_modified_relationships() );
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
