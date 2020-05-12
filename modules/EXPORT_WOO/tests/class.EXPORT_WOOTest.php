<?php

//In main app, instantiates by
//	$coastc = new EXPORT_WOO( "mickey.ksfraser.com", "fhs", "fhs", "fhs", "EXPORT_WOO_prefs" );
//	$found = $coastc->is_installed();
//	$coastc->set_var( 'found', $found );
//	$coastc->set_var( 'help_context', "Export to Woo Commerce Interface" );
//	$coastc->set_var( 'redirect_to', "EXPORT_WOO.php" );
//	$coastc->run();

declare(strict_types=1);
use PHPUnit\Framework\TestCase;
require_once( dirname( __FILE__ ) .  '/../class.EXPORT_WOO.php' );
global $db_connections;	//FA uses for DB stuff
global $_SESSION;
$_SESSION['wa_current_user'] = new stdClass();
$_SESSION['wa_current_user']->company = 1;
$_SESSION["wa_current_user"]->cur_con = 1;
$db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'] = '1_';
$db_connections[1]['tbpref'] = '1_';

//If asserts fail returning type NULL that is because the field
//is PROTECTED or PRIVATE and therefore can't be accessed!!
class EXPORT_WOOTest extends TestCase
{
	protected $shared_var;
	protected $shared_val;
	function __construct()
	{
		parent::__construct();
		$this->shared_var =  'pub_unittestvar';
		$this->shared_val = '1';
		
	}
	public function testInstanceOf(): EXPORT_WOO
	{
		$o = new EXPORT_WOO( null, null, null, null, null );
		$this->assertInstanceOf( EXPORT_WOO::class, $o );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorValues( $o )
	{
		$this->assertSame( $o->vendor, "EXPORT_WOO" );	//var not protected/private
		$this->assertIsArray( $o->get( 'config_values' ) );
		$this->assertIsArray( $o->get( 'tabs' ) );
		//Constructor also calls add_submodules
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testnotify()
	{
		//->notify is inherited from generic_fa_interface.
		$this->assertTrue( true );

	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testcall_table()
	{
		//->call_table is inherited from generic_fa_interface.
		$this->assertTrue( true );

	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testfix_stock_id_size()
	{
		//wrapper function to call ksf_data_dictionary
		$this->assertTrue( true );
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
	
	
	/**
	 * @ depends testInstanceOf
	 */
	public function form_reset_store()
	{
		//calls		$this->notify();
		//calls		$this->call_table( 'reset_store_act', "Reset the Woocommerce Store" );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function reset_store_act()
	{
		//calls		$this->notify();
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function form_products_export()
	{
		//calls		$this->notify();
		//calls		$this->call_table( 'qoh', "QOH" );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function call_table( $action, $msg )
	{
		//calls		$this->notify();
		//UI - creates a table + section + form
		$this->assertTrue( true );
	}

	/*
	public function testLogMsg( $o )
	{
		$msg = 'boohoo';
		$ret = $o->LogMsg( $msg, PEAR_LOG_EMERG );
		$this->assertIsBool( $ret );
		$this->assertSame( true, $ret );
		$this->assertNotSame( $val, $o->get( $field ) ); //Shouldn't be set
		$this->expectException( Exception::class );
		$this->assertArrayHasKey( '___SOURCE_KEYS_', $o->object_fields );
		$this->assertNotEmpty( $o->object_fields );
		
		$this->assertIsArray( $o->get( 'interestedin' ) );
		$this->assertTrue( true );
	}
	 */

	/**
	 * @ depends testInstanceOf
	 */
	public function testmissing_woo()
	{
		//calls		$this->notify();
		//uses woo( null, null, null, null, $this );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testform_add_woo_id_to_sku()
	{
		//calls		$this->notify();
		//uses woo( null, null, null, null, $this );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testadd_woo_id_to_sku()
	{
		//calls		$this->notify();
		//uses woo( null, null, null, null, $this );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testsales_pricing()
	{
		//calls		$this->notify();
		//UI
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testpopulate_qoh()
	{
		//calls		$this->notify();
		//uses ksf_qoh( KSF_QOH_PREFS );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testpopulate_woo()
	{
		//calls		$this->notify();
		//uses model_woo( null, null, null, null, $this );
	//}
		//calls 	$this->call_table( 'exported_rest_products', "Send Products via REST to WOO" );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testsend_categories_form()
	{
		//calls		$this->call_table( "sent_categories_form", "Send Categories to WOO" );
		//calls		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testsent_categories_form()
	{
		//uses woo_category( $this->woo_server, $this->woo_rest_path, $this->woo_ck, $this->woo_cs, null, $this, "devel" );
		//calls		$this->notify
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testexported_rest_variable_products()
	{
		//Does SQL query
			//$master_prod_sql = "SELECT stock_id FROM " . TB_PREF . "woo_prod_variable_master";
		//uses woo_product( );
		// sets woo_product values
		//Fetches products and sets more values
		//Sub SQL
			//$child_sql = "select stock_id, woo_category_id, description, long_description, price, instock, sale_price, date_on_sale_from, date_on_sale_to, external_url, tax_status, tax_class, weight, length, width, height, shipping_class, upsell_ids, crosssell_ids, parent_id, attributes, default_attributes, variations FROM " . TB_PREF . "woo where stock_id like '" . $prod_data['stock_id'] . "%'";
		//$woo_product->create_product();		
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testimported_customer_form()
	{
		//uses woo_customer( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		//calls 	$this->call_table( 'import_order', "Import Another" );
		//calls 	$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testimport_customer_form()
	{
		//$this->call_table( 'imported_customer_form', "Get Customer from WOO" );
		//$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testexported_customer_form()
	{
		//uses woo_customer( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		//calls		$this->call_table( 'export_order', "Export Another" );
		//calls 	$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testexport_customer_form()
	{
		//calls		$this->notify();
		//calls		$this->call_table( );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testimported_orders_form()
	{
		//calls		$this->notify();
		//calls		$this->call_table( );
		//uses woo_orders( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testimport_orders_form()
	{
		//calls		$this->notify();
		//calls		$this->call_table( );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testexported_orders_form()
	{
		//calls		$this->notify();
		//calls		$this->call_table( );
		//uses woo_orders( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testexport_orders_form()
	{
		//calls		$this->notify();
		//calls		$this->call_table( );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testexported_rest_simple_products()
	{
		//calls		$this->notify();
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testexported_rest_products_form()
	{
		//calls		$this->notify();
		//calls		$this->call_table( );
		//uses woo_product( $this->woo_server, $this->woo_rest_path, $this->woo_ck, $this->woo_cs, $this->environment, $this );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testexport_rest_product_form()
	{
		//calls		$this->notify();
		//calls		$this->call_table( );

		//$missing_sql = "select sm.stock_id, sm.description, c.description, sm.inactive, sm.editable from " . TB_PREF . "stock_master sm, " . TB_PREF . "stock_category c where sm.category_id = c.category_id and sm.stock_id in (select stock_id from " . TB_PREF . "woo)";
		//UI
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testform_pricebook()
	{
		//calls		$this->notify();
		//uses woo( null, null, null, null, $this );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testexported_rest_products_form_poc()
	{
		//calls		$this->notify();
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testexport_file_form()
	{
		//calls		$this->notify();
		//calls		$this->call_table( );
		$this->assertTrue( true );
	}
	/**
	 * @ depends testInstanceOf
	 */
	public function testform_products_exported()
	{
		//calls		$this->populate_qoh();
		//calls		$this->populate_woo();

		//calls		$this->form_pricebook();
		//Writes to a file
		//Query 	$woo = "select * from " . TB_PREF . "woo";
		//Writes to a file
		//display_notification("$rowcount rows of items created.");
		//MAILs file
		//calls		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testexport_orders( $o )
	{
		//creates arrays
		$_POST['order_no'] = 1;
		$ret = $o->export_orders();
		if( $ret !== FALSE )
			$this->assertSame( $o->get( 'order_no' ), 1 );	
		//Opens a file
		//calls		$this->write_line( $fp, $hline );
		//calls		$this->get_purchase_order();
		//foreach PO write a line
		//Close FP
		//calls 	display_notification("$rowcount rows of items created, $ignoredrows rows of items ignored, $this->maxrowsallowed rows allowed.");
		$this->assertNotEmpty( $this->order_no );
		//Sends email
		//calls		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
}

?>
