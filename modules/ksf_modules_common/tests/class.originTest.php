<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once( dirname( __FILE__ ) .  '/defines.php' );
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

class originTest extends TestCase
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
                $o = new origin( null, null, null, null, $this->pref_tablename );
                $this->assertInstanceOf( origin::class, $o );
                //$this->assertSame( $this->pref_tablename , $o->get( 'pref_tablename' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorDebug( $o ): origin
        {
                $this->assertSame( 0 , $o->get( 'debug' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorEventloop( $o ): origin
        {
                //eventloop is a var, not protected/private
                $this->assertInstanceOf( eventloop::class, $o->eventloop );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorPrefTablename( $o ): origin
        {
                //with pref_tablename set (5th constructor var) we can test.
                $this->assertSame( $this->pref_tablename , $o->get( 'pref_tablename' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorTabs( $o ): origin
        {
                $this->assertIsArray( $o->get( 'tabs' ) );
                return $o;
        }

}
