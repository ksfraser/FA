<?php 
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once( dirname( __FILE__ ) .  '/../class.generic_fa_interface.php' );

global $db_connections;	//FA uses for DB stuff
global $_SESSION;
$_SESSION['wa_current_user'] = new stdClass();
$_SESSION['wa_current_user']->company = 1;
$_SESSION["wa_current_user"]->cur_con = 1;
$db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'] = '1_';
$db_connections[1]['tbpref'] = '1_';


//If asserts fail returning type NULL that is because the field
//is PROTECTED or PRIVATE and therefore can't be accessed!!
class generic_fa_interfaceTest extends TestCase
{
	protected $shared_var;
	protected $shared_val;
	protected $pref_tablename;
	function __construct()
	{
		parent::__construct();
		$this->shared_var =  'pub_unittestvar';
		$this->shared_val = '1';
		$this->pref_tablename = 'test';
		
	}

	public function testInstanceOf(): origin
	{
		$o = new generic_fa_interface( null, null, null, null, $this->pref_tablename );
		$this->assertInstanceOf( generic_fa_interface::class, $o );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorDebug( $o ): generic_fa_interface
	{
		$this->assertSame( 0 , $o->get( 'debug' ) );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorEventloop( $o ): generic_fa_interface
	{
		//eventloop is a var, not protected/private
		$this->assertInstanceOf( eventloop::class, $o->eventloop );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorPrefTablename( $o ): generic_fa_interface
	{
		//with pref_tablename set (5th constructor var) we can test.
		$this->assertSame( $this->pref_tablename , $o->get( 'pref_tablename' ) );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorTabs( $o ): generic_fa_interface
	{
		$this->assertIsArray( $o->get( 'tabs' ) );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorTabsIndex( $o ): generic_fa_interface
	{
		$this->assertArrayHasKeys( 'title', $o->get( 'tabs' ) );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorUIClass( $o ): generic_fa_interface
	{
		$this->assertIsNull( $o->get( 'ui_class' ) );
		return $o;
	}

	/**
	 * @depends testInstanceOf
	 */
	public function testNotify( $o ): generic_fa_interface
	{
		//UI calls
		//$this->assertIsNull( $o->get( 'ui_class' ) );
		$this->assertSame( 0, $o->notify( 'test', "ERROR" ) );
		$this->assertSame( 1, $o->notify( 'test', "WARN" ) );
		$this->assertSame( 2, $o->notify( 'test', "NOTIFY" ) );
		$this->assertSame( 3, $o->notify( 'test', "DEBUG" ) );
		$this->assertSame( 4, $o->notify( 'test', "DEFAULT" ) );
		return $o;
	}
	//public function testEventregister();	//empty function


	/**
	 * @depends testInstanceOf
	 */
	public function add_submodulesTest( $o )
	{
		//USES 		eventloop
		$this->assertSame( 0, $o->add_submodules() );
		$this->assertSame( dirname( __FILE__ ) . "/modules", $o->eventloop->get( 'moduledir' ) );
		$this->assertSame( 0, $o->add_submodules( "." ) );
		$this->assertIsArray( $o->get( 'tabs' ) );
		$this->assertSame( dirname( __FILE__ ), $o->eventloop->get( 'moduledir' ) );
		$this->assertIsArray( $o->get( 'tabs' ) );
		return $o;
		
	}
	/**
	 * @depends testInstanceOf
	 */
	public function module_installTest( $o )
	{
		$t = new generic_fa_interface( null, null, null, null, null );
		$this->expectException( $t->module_install() );	
		$this->assertIsBool( $o->module_install() );	//should return T/F since pref_table is set.
		return $o;
		
	}
	/**
	 * @depends testInstanceOf
	 */
	public function installTest( $o )
	{
		$t = new generic_fa_interface( null, null, null, null, null );
		$this->expectException( $t->module_install() );	
		$this->assertIsBool( $o->module_install() );	//should return T/F since pref_table is set.
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function installTestRedirect( $o )
	{
		$t = new generic_fa_interface( null, null, null, null, null );
		$this->expectException( $t->module_install() );	
		$o->set( 'redirect_to', "google.ca" );
		$this->assertIsBool( $o->module_install() );	//should return T/F since pref_table is set.
		//UI - header if ->redirect_to set
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function checkprefsTest( $o )
	{
		$this->expectException( $o->checkprefs() );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function call_tableTest( $o )
	{
		//UI
		$this->assertTrue( true );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function action_show_formTest()
	{
		$this->expectException( $o->action_show_form() );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function show_config_formTest( $o )
	{
		//UI
		//If ->found not set, there is an extra table added.
		$this->assertIsBool( $o->show_config_form() );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function form_exportTest( $o )
	{
		//UI
		//calls ->get_id_range()
		$this->assertIsBool( $o->form_export() );
		return $o;
	}
	/**
	 * @depends add_submodulesTest
	 */
	public function related_tabsTest( $o )
	{
		$this->assertIsArray( $o->tabs );	//If not an array (not set) can't have related tabs...
		$this->assertTrue( $o->related_tabs() > 0 );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function show_formTest( $o )
	{
		//IF ->view is set calls ->view->show_form
		unset( $o->view );
		//
		$o->set( 'action', 'test' );
		unset( $o->tabs );
		//IF at 473 objname shouldn't be set
		$o->tabs = array( 'action' => 'test',
				'form', 'form_unittest' );
		//form within generic_fa_interface.
		$this->assertTrue( $o->show_form() );
		//IF 463 - set objname
		unset( $o->tabs );
		$o->tabs = array( 'action' => 'test',
				'class' => 'generic_fa_interface',
				'form', 'form_unittest' );
		//IF at 473
		$this->assertTrue( $o->show_form() );
		//Line 474 -> 487
		unset( $o->tabs );
		$o->tabs = array( 'action' => 'test',
				'class' => 'test',
				'form', 'test' );
		//test->test shouldn't exist, so...
		$this->expectException( $o->show_form() );
		//else at 512, IF at 461
		$o->set( 'action', 'test_unavail' );
		$this->assertIsSame( KSF_OBJ_FCN_UNAVAILABLE, $o->show_form() );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function base_form_unttestTest( $o )
	{
		//This unittest form ONLY returns TRUE for other tests to use...
		$this->assertTrue( $o->form_unittest() );
		return $o;
	}
	/**
	 * @depends related_tabsTest
	 */
	public function base_pageTest( $o )
	{
		//UI
		//Calls ->related_tabs()
		$this->assertTrue( $o->base_page() );
		return $o;
	}
	/**
	 * @depends base_pageTest()
	 * @depends show_formTest()
	 */
	public function displayTest( $o, $p )
	{
		$this->assertTrue( $p->display() );
		return $o;
	}
	/**
	 * @depends loadprefsTest
	 * @depends installTest
	 * @depends displayTest
	 */
	public function runTest( $l, $i, $d )
	{
		//if found is set, we load prefs else install.
		$this->assertTrue( $l->run() );
		$this->assertTrue( $i->run() );	//sets action to DEFAULT
		$this->assertSame( "default", $i->get( 'action' ) );
		//As neither POST nor GET are set...else@574
		unset( $d->view );
		$this->assertTrue( $d->run() );
		$this->assertSame( "test", $d->get( 'action' ) );
		//An action MAYBE gets set...and calls display
		// Maybe - if we passed in a BUTTON action (post[row])

		$_POST['action'] = "test";
		$this->assertTrue( $l->run() );
		$this->assertSame( "test", $i->get( 'action' ) );

		unset( $_POST );
		$_GET['action'] = "test";
		$this->assertTrue( $l->run() );
		$this->assertSame( "test", $i->get( 'action' ) );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function modify_table_columnTest( $o )
	{
		$tables_array = array();
		$this->assertSame( 0, $o->modify_table_column( $tables_array ) );	//Maybe an exception from the db call
	}
	/**
	 * @depends testInstanceOf
	 */
	public function append_fileTest( $o )
	{	
		$this->expectException( $o->append_file() );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function overwrite_fileTest( $o )
	{
		$this->expectException( $o->overwrite_file() );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function open_write_fileTest( $o )
	{
		$this->expectException( $o->open_write_file() );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function write_lineTest( $o )
	{
		$this->expectException( $o->write_line() );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function close_fileTest( $o )
	{
		$this->expectException( $o->close_file() );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function file_finishTest( $o )
	{
		$this->expectException( $o->file_finish() );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function backtraceTest( $o )
	{
		$this->assertTrue( $o->backtrace() );
	}
	/**
	 * @depends testInstanceOf
	 */
		public function write_sku_labels_lineTest( $o )
	{
		$this->assertTrue( TRUE );
	}
	/**
	 * @depends testInstanceOf
	 */
		public function show_generic_formTest( $o )
	{
		$this->assertTrue( TRUE );
	}
}

class generic_fa_interface_viewTest extends TestCase
{
	protected $shared_var;
	protected $shared_val;
	protected $pref_tablename;
	function __construct( )
	{
		parent::__construct( );
		$this->shared_var =  'pub_unittestvar';
		$this->shared_val = '1';
		$this->pref_tablename = 'test';
		
	}

	public function testInstanceOf( ): origin
	{
		$o = new generic_fa_interface( null, null, null, null, $this->pref_tablename );
		$this->assertInstanceOf( generic_fa_interface::class, $o );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function page_processingTest( $o )
	{
		$this->assertTrue( TRUE );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function usage_formTest( $o )
	{
		$this->assertTrue( TRUE );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function page_modifiedTest( $o )
	{
		$this->assertTrue( TRUE );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function ajax_reloadTest( $o )
	{
		$this->assertTrue( TRUE );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function confirm_dialogTest( $o ) 
	{
		$this->assertTrue( TRUE );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function processing_startTest( $o )
	{
		$this->assertTrue( TRUE );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function processing_endTest( $o )
	{
		$this->assertTrue( TRUE );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function default_formTest( $o )
	{
		$this->assertTrue( TRUE );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function show_config_formTest( $o )
	{
		$this->assertTrue( TRUE );
	}
	/**
	 * @depends testInstanceOf
	 */
	public function tabledef2headersTest( $o )
	{
		$this->assertTrue( TRUE );
	}
}
