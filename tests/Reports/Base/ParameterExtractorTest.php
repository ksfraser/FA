<?php

declare(strict_types=1);

namespace FA\Tests\Reports\Base;

use FA\Modules\Reports\Base\ParameterExtractor;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ParameterExtractor
 */
class ParameterExtractorTest extends TestCase
{
    public function testGetString(): void
    {
        $extractor = ParameterExtractor::fromArray([
            'PARAM_0' => '2026-01-01',
            'PARAM_1' => 'Test'
        ]);

        $this->assertEquals('2026-01-01', $extractor->getString('PARAM_0'));
        $this->assertEquals('Test', $extractor->getString('PARAM_1'));
        $this->assertEquals('', $extractor->getString('PARAM_2'));
        $this->assertEquals('default', $extractor->getString('PARAM_3', 'default'));
    }

    public function testGetInt(): void
    {
        $extractor = ParameterExtractor::fromArray([
            'PARAM_0' => '123',
            'PARAM_1' => 456,
            'PARAM_2' => 'not_a_number'
        ]);

        $this->assertEquals(123, $extractor->getInt('PARAM_0'));
        $this->assertEquals(456, $extractor->getInt('PARAM_1'));
        $this->assertEquals(0, $extractor->getInt('PARAM_2'));
        $this->assertEquals(99, $extractor->getInt('PARAM_3', 99));
    }

    public function testGetBool(): void
    {
        $extractor = ParameterExtractor::fromArray([
            'PARAM_0' => 1,
            'PARAM_1' => '1',
            'PARAM_2' => true,
            'PARAM_3' => 'true',
            'PARAM_4' => 0,
            'PARAM_5' => '0',
            'PARAM_6' => false,
            'PARAM_7' => 'false'
        ]);

        $this->assertTrue($extractor->getBool('PARAM_0'));
        $this->assertTrue($extractor->getBool('PARAM_1'));
        $this->assertTrue($extractor->getBool('PARAM_2'));
        $this->assertTrue($extractor->getBool('PARAM_3'));
        $this->assertFalse($extractor->getBool('PARAM_4'));
        $this->assertFalse($extractor->getBool('PARAM_5'));
        $this->assertFalse($extractor->getBool('PARAM_6'));
        $this->assertFalse($extractor->getBool('PARAM_7'));
        $this->assertFalse($extractor->getBool('PARAM_8'));
        $this->assertTrue($extractor->getBool('PARAM_9', true));
    }

    public function testIsAll(): void
    {
        $extractor = ParameterExtractor::fromArray([
            'PARAM_0' => 'All',
            'PARAM_1' => 'Specific',
            'PARAM_2' => ''
        ]);

        $this->assertTrue($extractor->isAll('PARAM_0'));
        $this->assertFalse($extractor->isAll('PARAM_1'));
        $this->assertFalse($extractor->isAll('PARAM_2'));
        $this->assertFalse($extractor->isAll('PARAM_3'));
    }

    public function testGetOrNullIfAll(): void
    {
        $extractor = ParameterExtractor::fromArray([
            'PARAM_0' => 'All',
            'PARAM_1' => 'Value',
            'PARAM_2' => ''
        ]);

        $this->assertNull($extractor->getOrNullIfAll('PARAM_0'));
        $this->assertEquals('Value', $extractor->getOrNullIfAll('PARAM_1'));
        $this->assertNull($extractor->getOrNullIfAll('PARAM_2'));
        $this->assertNull($extractor->getOrNullIfAll('PARAM_3'));
    }

    public function testExtractGLReportConfigNoDimensions(): void
    {
        // Mock CompanyPrefsService
        if (!class_exists('\FA\Services\CompanyPrefsService')) {
            eval('namespace FA\Services { class CompanyPrefsService { public static function getUseDimensions() { return 0; } } }');
        }
        if (!class_exists('\FA\UserPrefsCache')) {
            eval('namespace FA { class UserPrefsCache { public static function getPriceDecimals() { return 2; } } }');
        }
        if (!function_exists('user_pagesize')) {
            eval('function user_pagesize() { return "A4"; }');
        }

        $params = [
            'PARAM_0' => '2026-01-01',
            'PARAM_1' => '2026-01-31',
            'PARAM_2' => 'Test comments',
            'PARAM_3' => 1, // landscape
            'PARAM_4' => 1  // excel
        ];

        $extractor = ParameterExtractor::fromArray($params, 0);
        $config = $extractor->extractGLReportConfig();

        $this->assertEquals('2026-01-01', $config->getFromDate());
        $this->assertEquals('2026-01-31', $config->getToDate());
        $this->assertEquals(0, $config->getDimension1());
        $this->assertEquals(0, $config->getDimension2());
        $this->assertEquals('Test comments', $config->getComments());
        $this->assertTrue($config->isLandscapeOrientation());
        $this->assertTrue($config->shouldExportToExcel());
    }

    public function testExtractGLReportConfigWithOneDimension(): void
    {
        $params = [
            'PARAM_0' => '2026-01-01',
            'PARAM_1' => '2026-01-31',
            'PARAM_2' => 5, // dimension1
            'PARAM_3' => 'Test comments',
            'PARAM_4' => 0, // portrait
            'PARAM_5' => 0  // pdf
        ];

        $extractor = ParameterExtractor::fromArray($params, 1);
        $config = $extractor->extractGLReportConfig();

        $this->assertEquals(5, $config->getDimension1());
        $this->assertEquals(0, $config->getDimension2());
        $this->assertEquals('Test comments', $config->getComments());
        $this->assertFalse($config->isLandscapeOrientation());
        $this->assertFalse($config->shouldExportToExcel());
    }

    public function testExtractGLReportConfigWithTwoDimensions(): void
    {
        $params = [
            'PARAM_0' => '2026-01-01',
            'PARAM_1' => '2026-01-31',
            'PARAM_2' => 5,  // dimension1
            'PARAM_3' => 10, // dimension2
            'PARAM_4' => 'Test comments',
            'PARAM_5' => 1, // landscape
            'PARAM_6' => 0  // pdf
        ];

        $extractor = ParameterExtractor::fromArray($params, 2);
        $config = $extractor->extractGLReportConfig();

        $this->assertEquals(5, $config->getDimension1());
        $this->assertEquals(10, $config->getDimension2());
        $this->assertEquals('Test comments', $config->getComments());
        $this->assertTrue($config->isLandscapeOrientation());
        $this->assertFalse($config->shouldExportToExcel());
    }

    public function testExtractCustomerSupplierConfig(): void
    {
        $params = [
            'PARAM_0' => '2026-01-01',
            'PARAM_1' => '2026-01-31',
            'PARAM_2' => '123', // customer_id
            'PARAM_3' => 1,     // show_balance
            'PARAM_4' => 'USD', // currency
            'PARAM_5' => 1,     // suppress_zeros
            'PARAM_6' => 'Monthly report',
            'PARAM_7' => 1,     // landscape
            'PARAM_8' => 0      // pdf
        ];

        $extractor = ParameterExtractor::fromArray($params, 0);
        $config = $extractor->extractCustomerSupplierConfig();

        $this->assertEquals('2026-01-01', $config->getFromDate());
        $this->assertEquals('2026-01-31', $config->getToDate());
        $this->assertEquals('123', $config->getAdditionalParam('entity_id'));
        $this->assertTrue($config->getAdditionalParam('show_balance'));
        $this->assertEquals('USD', $config->getCurrency());
        $this->assertFalse($config->shouldConvertCurrency());
        $this->assertTrue($config->shouldSuppressZeros());
        $this->assertEquals('Monthly report', $config->getComments());
        $this->assertTrue($config->isLandscapeOrientation());
        $this->assertFalse($config->shouldExportToExcel());
    }

    public function testExtractCustomerSupplierConfigWithAllCurrency(): void
    {
        $params = [
            'PARAM_0' => '2026-01-01',
            'PARAM_1' => '2026-01-31',
            'PARAM_2' => 'All',
            'PARAM_3' => 0,
            'PARAM_4' => 'All', // All currencies = convert to home
            'PARAM_5' => 0,
            'PARAM_6' => '',
            'PARAM_7' => 0,
            'PARAM_8' => 0
        ];

        $extractor = ParameterExtractor::fromArray($params, 0);
        $config = $extractor->extractCustomerSupplierConfig();

        $this->assertNull($config->getCurrency());
        $this->assertTrue($config->shouldConvertCurrency());
        $this->assertNull($config->getAdditionalParam('entity_id'));
    }

    public function testGetAllParams(): void
    {
        $params = [
            'PARAM_0' => 'value1',
            'PARAM_1' => 'value2',
            'PARAM_2' => 'value3'
        ];

        $extractor = ParameterExtractor::fromArray($params);
        $this->assertEquals($params, $extractor->getAllParams());
    }
}
