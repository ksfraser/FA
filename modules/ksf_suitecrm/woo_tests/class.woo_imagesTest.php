<?php

//In main app, instantiates by
//	$coastc = new woo_image( "mickey.ksfraser.com", "fhs", "fhs", "fhs", "woo_image_prefs" );
//	$found = $coastc->is_installed();
//	$coastc->set_var( 'found', $found );
//	$coastc->set_var( 'help_context', "Export to Woo Commerce Interface" );
//	$coastc->set_var( 'redirect_to', "woo_image.php" );
//	$coastc->run();

declare(strict_types=1);
use PHPUnit\Framework\TestCase;
require_once( dirname( __FILE__ ) .  '/../class.woo_images.php' );

if( ! function_exists( 'item_img_name' ) )
{
	function item_img_name( $sku ) { return $sku; }
}
if( ! function_exists( 'company_path' ) )
{
	function company_path() { return dirname( __FILE__ ) . "/../../company/1/"; }
}

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
class woo_imageTest extends TestCase
{
	protected $good_stock_id;
	protected $bad_stock_id;
	function __construct()
	{
		parent::__construct();
		$this->good_stock_id = "";
		$this->bad_stock_id = "";
	}
	public function testInstanceOf(): woo_image
	{
		$stock_id = "boo";
		$pic_num = 0;
		$server_url = "localhost";
		$base_url = ""; 
		$client = $this;
		$debug = 0;
		$remote_img_srv = 0;
		$o = new woo_image(  $stock_id, $pic_num, $server_url, $base_url, $client, $debug, $remote_img_srv );
		$this->assertInstanceOf( woo_image::class, $o );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorValues( $o )
	{
		//$this->assertSame( $o->vendor, "woo_image" );	//var not protected/private
		//$this->assertIsObject( $o->get( 'wc' ) );
		$this->assertSame( $o->client, $this );
		//Constructor also calls add_submodules
	}
	/*
	public function testCompany_path()
	{
		if( is_callable( company_path()  ) )
			$path = company_path();
		else
			$path = dirname( __FILE__ ) . "/../../company/1/";
		$this->assertIsString( $path );
		$len = strlen( $path );
		$this->assertGreaterThan( 5, $len );
	}
	 */
	/**
	 * @depends testInstanceOf
	 */
	public function testImage_exists( $o )
	{
		//Not testing var_dump on debug > 2
		//constructor sets remote_img_srv = 0;
		$this->assertTrue( is_string( $o->image_exists( $this->good_stock_id ) ) );
		//$this->assertIsString( $o->image_exists( $this->good_stock_id ) );
		$this->assertNull( $o->image_exists( $this->bad_stock_id ) );

		$o->remote_img_srv = 1;
		$this->assertTrue( is_string( $o->image_exists( $this->good_stock_id ) ) );
		//$this->assertIsString( $o->image_exists( $this->good_stock_id ) );
		$this->assertNull( $o->image_exists( $this->bad_stock_id ) );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testRun( $o )
	{
		$this->assertTrue( $o->run( $this->good_stock_id ) );
		unset( $o->image_serverurl );
		$this->assertFalse( $o->run( $this->bad_stock_id ) );
	}
	public function notify( $v1 = "", $v2 = "", $v3 = "" )
	{
		return "";
	}
}


//		$this->expectException( $o->send( "test", "test", null );	
//		$this->assertIsArray( $o->send( "product",array( "test" ), $this );	
//		$this->assertIsString( $path );
//		$this->assertGreaterThan( 5, $len );

class woo_imagesTest extends TestCase
{
	protected $good_stock_id;
	protected $bad_stock_id;
	protected $image_serverurl;
	protected $image_baseurl;
	protected $maxpics;
	function __construct()
	{
		parent::__construct();
		$this->good_stock_id = "";
		$this->bad_stock_id = "";
		$this->image_serverurl = "";
		$this->image_baseurl = "";
		$this->maxpics = 2;
	}
	public function testInstanceOf(): woo_images
	{
		$stock_id = "boo"; 
		$client = $this;
		$debug = 0;
		$remote_img_srv = 0;
		$o = new woo_images(  $stock_id, $client, $debug, $remote_img_srv );
		$this->assertInstanceOf( woo_images::class, $o );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testRun( $o )
	{
		$this->assertTrue(  is_array( $o->run() ) );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testProduct_images( $o )
	{
		$res = $o->run();
		$count = count( $res );
		$this->assertTrue( is_array( $res ) );
		$this->assertLessThan( $this->maxpics + 2, $count ); //0 based array so max=2 means 3 pics means less than 4
		$this->assertGreaterThan( 0, $count );	//assumption GOOD sku/pic

		unset( $this->maxpics );
		$res = $o->run();
		$count = count( $res );
		$this->assertTrue( is_array( $res ) );
		$this->assertLessThan( 2, $count ); //0 based array so max=2 means 3 pics means less than 4
		$this->assertGreaterThan( 0, $count );	//assumption GOOD sku/pic
	}
	public function notify( $v1 = "", $v2 = "", $v3 = "" )
	{
		return "";
	}
}


?>
