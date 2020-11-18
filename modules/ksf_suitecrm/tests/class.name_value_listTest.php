<?php



declare(strict_types=1);
global $path_to_root;
if( $path_to_root == null OR strlen( $path_to_root ) < 5 )
 	$path_to_root = dirname( __FILE__ ) . "/../../../";
use PHPUnit\Framework\TestCase;
require_once( dirname( __FILE__ ) .  '/../class.name_value_list.php' );

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
class name_value_listTest extends TestCase
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
	public function testInstanceOf(): name_value_list
	{
		$o = new name_value_list();
		$this->assertInstanceOf( name_value_list::class, $o );
		return $o;
	}
	/**
	 * @depends testInstanceOf
	 */
	public function testConstructorValues( $o )
	{
		//Constructor creates an array.
		$this->assertIsArray( $o->nvl );

		//$this->assertSame( $o->vendor, "name_value_list" );	//var not protected/private
		//$this->assertTrue( is_object( $o->get( 'wc' ) ) );
		//$this->assertSame( $o->get( 'client' ), $this );
		//Constructor also calls add_submodules
	}
	
	/**
	 * @depends testInstanceOf
	 */
	public function testadd_nvl( $o )
	{
		$o->add_nvl( $this->name, $this->value );
		$this->assertIsArray( $o->nvl );
		$this->assertSame( $o->nvl[0]['name'], $this->name );
		return $o;
	}
	/**
	 * @depends testadd_nvl
	 */
	public function testget_nvl( $o )
	{
		$this->assertIsArray( $o->get_nvl() );
	}
	/**
	 * @depends testadd_nvl
	 */
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
	/**
	 * @depends testget_value
	 */
	public function testsearch_nvl( $o )
	{
		$this->assertSame( $o->get_value( $o->search_nvl( $this->name ) ), $this->value );
		try
		{
			$o->search_nvl( "Nadda" );
			//$this->expectException( $o->search_nvl( "Nadda" ) );
		}
		catch( Exception $e )
		{
			$this->assertTrue( true );
			return $o;
		}
		throw Exception( "We shouldn't have made it here!" );
	}
	/**
	 * @depends testadd_nvl
	 */
	public function testget_named_value( $o )
	{
		$this->assertSame( $o->get_named_value( $this->name ), $this->value );
		try
		{
			$o->get_named_value( "nadda" );
			//$this->expectException( $o->get_named_value( "nadda" ) );
		}
		catch( Exception $e )
		{
			$this->assertTrue( true );
			return $o;
		}
		throw Exception( "We shouldn't have made it here!" );
	}



	
}

?>
