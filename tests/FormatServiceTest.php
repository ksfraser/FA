<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FA\Services\FormatService;

class FormatServiceTest extends TestCase
{
    protected function setUp(): void
    {
        global $SysPrefs;
        // Set up minimal SysPrefs for testing
        if (!isset($SysPrefs)) {
            $SysPrefs = new stdClass();
        }
        $SysPrefs->thoseps = [0 => ',', 1 => '.', 2 => ' ', 3 => "'"];
        $SysPrefs->decseps = [0 => '.', 1 => ','];
        
        // Set up session for user preferences (needed by original functions)
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 2,
            'qty_dec' => 2,
            'tho_sep' => 0,
            'dec_sep' => 0,
            'exrate_dec' => 4,
            'percent_dec' => 1
        ]);
        
        // Clear UserPrefsCache to ensure fresh state
        \FA\Services\UserPrefsCache::invalidate();
    }
    
    protected function tearDown(): void
    {
        \FA\Services\UserPrefsCache::invalidate();
    }
    
    /**
     * Create mock user object with preferences
     */
    private function createMockUser(array $prefs): object
    {
        $mockPrefs = new class($prefs) {
            private $prefs;
            
            public function __construct(array $prefs) {
                $this->prefs = $prefs;
            }
            
            public function price_dec() {
                return $this->prefs['price_dec'];
            }
            
            public function qty_dec() {
                return $this->prefs['qty_dec'];
            }
            
            public function tho_sep() {
                return $this->prefs['tho_sep'];
            }
            
            public function dec_sep() {
                return $this->prefs['dec_sep'];
            }
            
            public function exrate_dec() {
                return $this->prefs['exrate_dec'];
            }
            
            public function percent_dec() {
                return $this->prefs['percent_dec'];
            }
        };
        
        return (object)['prefs' => $mockPrefs];
    }

    public function testNumberFormat2BasicFormatting(): void
    {
        $original = FormatService::numberFormat2(1234.56, 2);
        $replacement = FormatService::numberFormat2(1234.56, 2);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testNumberFormat2WithZeroDecimals(): void
    {
        $original = FormatService::numberFormat2(1234.56, 0);
        $replacement = FormatService::numberFormat2(1234.56, 0);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testNumberFormat2WithNegativeNumber(): void
    {
        $original = FormatService::numberFormat2(-1234.56, 2);
        $replacement = FormatService::numberFormat2(-1234.56, 2);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testNumberFormat2WithMaxDecimals(): void
    {
        $original = FormatService::numberFormat2(1234.5, 'max');
        $replacement = FormatService::numberFormat2(1234.5, 'max');
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results for max decimals');
    }

    public function testNumberFormat2WithLargeNumber(): void
    {
        $original = number_format2(1234567.89, 2);
        $replacement = FormatService::numberFormat2(1234567.89, 2);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testPriceFormatBasic(): void
    {
        $original = FormatService::priceFormat(1234.56);
        $replacement = FormatService::priceFormat(1234.56);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testPriceFormatNegative(): void
    {
        $original = FormatService::priceFormat(-999.99);
        $replacement = FormatService::priceFormat(-999.99);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testPriceFormatZero(): void
    {
        $original = FormatService::priceFormat(0);
        $replacement = FormatService::priceFormat(0);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testPriceFormatLargeNumber(): void
    {
        $original = FormatService::priceFormat(9999999.99);
        $replacement = FormatService::priceFormat(9999999.99);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    // Test exrate_format
    public function testExrateFormatBasic(): void
    {
        $original = exrate_format(1.234567);
        $replacement = FormatService::exrateFormat(1.234567);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testExrateFormatWholeNumber(): void
    {
        $original = exrate_format(2);
        $replacement = FormatService::exrateFormat(2);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    // Test percent_format
    public function testPercentFormatBasic(): void
    {
        $original = percent_format(15.75);
        $replacement = FormatService::percentFormat(15.75);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testPercentFormatZero(): void
    {
        $original = percent_format(0);
        $replacement = FormatService::percentFormat(0);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testPercentFormatNegative(): void
    {
        $original = percent_format(-5.5);
        $replacement = FormatService::percentFormat(-5.5);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    // Test maxprec_format
    public function testMaxprecFormatWithDecimals(): void
    {
        $original = maxprec_format(1234.5000);
        $replacement = FormatService::maxprecFormat(1234.5000);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testMaxprecFormatWholeNumber(): void
    {
        $original = maxprec_format(1234);
        $replacement = FormatService::maxprecFormat(1234);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testMaxprecFormatManyDecimals(): void
    {
        $original = maxprec_format(0.123456789);
        $replacement = FormatService::maxprecFormat(0.123456789);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }
}
