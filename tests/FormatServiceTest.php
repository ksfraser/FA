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
        $original = FormatService::numberFormat2(1234567.89, 2);
        $replacement = FormatService::numberFormat2(1234567.89, 2);
        
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }
}
