<?php

//In main app, instantiates by
//	$coastc = new woo_rest( "mickey.ksfraser.com", "fhs", "fhs", "fhs", "woo_rest_prefs" );
//	$found = $coastc->is_installed();
//	$coastc->set_var( 'found', $found );
//	$coastc->set_var( 'help_context', "Export to Woo Commerce Interface" );
//	$coastc->set_var( 'redirect_to', "woo_rest.php" );
//	$coastc->run();


declare(strict_types=1);
global $path_to_root;
if( strlen( $path_to_root ) < 5 )
 	$path_to_root = dirname( __FILE__ ) . "/../../../";
use PHPUnit\Framework\TestCase;
require_once( dirname( __FILE__ ) .  '/../class.woo_rest.php' );

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
class woo_restTest extends TestCase
{
	protected $shared_var;
	protected $shared_val;
	function __construct()
	{
		parent::__construct();
		$this->shared_var =  'pub_unittestvar';
		$this->shared_val = '1';
		
	}
	public function testInstanceOf(): woo_rest
	{
		$o = new woo_rest( null, null, null, null, $this );
		$this->assertInstanceOf( woo_rest::class, $o );
		return $o;
	}
	public function testNullInstanceOf(): woo_rest
	{
		$o = new woo_rest( null, null, null, null, null );
		$this->assertInstanceOf( woo_rest::class, $o );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorValues( $o )
	{
		//$this->assertSame( $o->vendor, "woo_rest" );	//var not protected/private
		$this->assertTrue( is_object( $o->get( 'wc' ) ) );
		$this->assertSame( $o->get( 'client' ), $this );
		//Constructor also calls add_submodules
	}
	/**
	 * @depends testInstanceOf
	 * @depends testNullInstanceOf
	 */
	public function testnotify( $o, $n )
	{
		//->notify is inherited from generic_fa_interface.
		$this->assertTrue( $o->notify( "test", "test" ) );
		//unset( $o->client );
		$this->assertFalse( $n->notify( "test", "test" ) );

	}
	/**
	 * @depends testInstanceOf
	 */
	public function testsend( $o )
	{
		//->notify is inherited from generic_fa_interface.
		$this->expectException( $o->send( "test", "test", null ) );
		$this->expectException( $o->send( "test", "test", null ) );	//Bad endpoint so I expect the WC library to throw an exception
		$this->assertIsArray( $o->send( "product",array( "test" ), $this ) );	//Should run send_new
		$o->set( 'id', 1 );
		$this->assertIsArray( $o->send( "product",array( "test" ), $this ) );	//Should run send_update
	}
	/**
	 * @depends testInstanceOf
	 */
	/*** send_search is private function so can't be tested.
	public function testsend_search( $o )
	{
		//->notify is inherited from generic_fa_interface.
		$this->expectException( $o->send_search( "test", null ) );	//search_array can't be set so exception
		$this->expectException( $o->send_search( "test", $this ) );	//search_array isn't set so exception
		$this->search_array = array();
		$this->assertIsArray( $o->send_search( "test", $this ) );		//search_array is empty so returned array should also be.
		$this->search_array = array( 'client' );
		$this->assertIsArray( $o->send_search( "test", $this ) );		//search_array is not empty so there MIGHT be a return array

		$this->expectException( $o->send( "test", "test", null ) );	//Bad endpoint so I expect the WC library to throw an exception
		$this->assertIsArray( $o->send( "product",array( "test" ), $this ) );	//Should run send_new
		$o->set( 'id', 1 );
		$this->assertIsArray( $o->send( "product",array( "test" ), $this ) );	//Should run send_update
	}
	 */

	/**
	 * @depends testInstanceOf
	 */
	public function testsend_new( $o )
	{
		//->notify is inherited from generic_fa_interface.
		$this->expectException( $o->send_new( "test", null ) );	//search_array can't be set so exception
		$this->expectException( $o->send_new( "test", $this ) );	//search_array isn't set so exception
		$this->search_array = array();
		$this->assertIsArray( $o->send_new( "test", $this ) );		//search_array is empty so returned array should also be.
		$this->search_array = array( 'client' );
		$this->assertIsArray( $o->send_new( "test", $this ) );		//search_array is not empty so there MIGHT be a return array

		$this->expectException( $o->send( "test", "test", null ) );	//Bad endpoint so I expect the WC library to throw an exception
		$this->assertIsArray( $o->send( "product",array( "test" ), $this ) );	//Should run send_new
		$o->set( 'id', 1 );
		$this->assertIsArray( $o->send( "product",array( "test" ), $this ) );	//Should run send_update
	}
	
	/**
	 * @ depends testInstanceOf
	 */
	//public function testinit_tables_complete_form( $o )
	//{
		//uses woo_category
		//calls 		$this->fix_stock_id_size();
		//calls 		$this->create_table_woo_prod_variable_master();
		//calls 		$this->create_table_woo_prod_variable_sku_combos();
		//calls 		$this->create_table_woo_prod_variable_sku_full();
		//calls 		$this->create_table_woo_prod_variable_child();
		//calls 		$this->create_table_woo_prod_variable_variables();
		//calls 		$this->create_table_woo_prod_variables_values();
		//uses woo_orders($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		//calls 		$this->create_table_woo_prod_variable_master();
		//calls 		$this->create_table_woo_prod_variable_sku_combos();
		//calls 		$this->create_table_woo_prod_variable_sku_full();
		//calls 		$this->create_table_woo_prod_variable_child();
		//calls 		$this->create_table_woo_prod_variable_variables();
		//calls 		$this->create_table_woo_prod_variables_values();
		//uses woo_orders($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		//uses woo($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		//uses ksf_qoh( KSF_QOH_PREFS );
		//uses woo_prod_variation_attributes($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		//uses woo_categories_xref($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		//uses woo_coupons($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
	//}
	
	public function notify( $v1 = "", $v2 = "", $v3 = "" )
	{
		return "";
	}
}

?>
