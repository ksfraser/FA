<?php

declare(strict_types=1);

namespace FA\Tests\Reports\Base;

use FA\Modules\Reports\Base\ReportConfig;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ReportConfig value object
 */
class ReportConfigTest extends TestCase
{
    public function testBasicConfiguration(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31'
        );

        $this->assertEquals('2026-01-01', $config->getFromDate());
        $this->assertEquals('2026-01-31', $config->getToDate());
        $this->assertEquals(0, $config->getDimension1());
        $this->assertEquals(0, $config->getDimension2());
        $this->assertFalse($config->shouldExportToExcel());
        $this->assertFalse($config->isLandscapeOrientation());
    }

    public function testWithDimensions(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            dimension1: 5,
            dimension2: 10
        );

        $this->assertEquals(5, $config->getDimension1());
        $this->assertEquals(10, $config->getDimension2());
        $this->assertTrue($config->hasDimension1());
        $this->assertTrue($config->hasDimension2());
        $this->assertTrue($config->hasAnyDimension());
    }

    public function testNoDimensions(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31'
        );

        $this->assertFalse($config->hasDimension1());
        $this->assertFalse($config->hasDimension2());
        $this->assertFalse($config->hasAnyDimension());
    }

    public function testExportToExcel(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            exportToExcel: true
        );

        $this->assertTrue($config->shouldExportToExcel());
        $this->assertEquals('excel', $config->getFormat());
    }

    public function testExportToPdf(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            exportToExcel: false
        );

        $this->assertFalse($config->shouldExportToExcel());
        $this->assertEquals('pdf', $config->getFormat());
    }

    public function testOrientation(): void
    {
        $portrait = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            landscapeOrientation: false
        );

        $landscape = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            landscapeOrientation: true
        );

        $this->assertEquals('P', $portrait->getOrientation());
        $this->assertEquals('L', $landscape->getOrientation());
    }

    public function testPageSizeAndDecimals(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            decimals: 3,
            pageSize: 'Letter'
        );

        $this->assertEquals(3, $config->getDecimals());
        $this->assertEquals('Letter', $config->getPageSize());
    }

    public function testComments(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            comments: 'Monthly report for January'
        );

        $this->assertEquals('Monthly report for January', $config->getComments());
    }

    public function testCurrencyConversion(): void
    {
        $withConversion = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            currency: null,
            convertCurrency: true
        );

        $withoutConversion = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            currency: 'USD',
            convertCurrency: false
        );

        $this->assertNull($withConversion->getCurrency());
        $this->assertTrue($withConversion->shouldConvertCurrency());

        $this->assertEquals('USD', $withoutConversion->getCurrency());
        $this->assertFalse($withoutConversion->shouldConvertCurrency());
    }

    public function testSuppressZeros(): void
    {
        $suppress = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            suppressZeros: true
        );

        $include = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            suppressZeros: false
        );

        $this->assertTrue($suppress->shouldSuppressZeros());
        $this->assertFalse($include->shouldSuppressZeros());
    }

    public function testAdditionalParameters(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            additionalParams: [
                'customer_id' => 123,
                'show_balance' => true,
                'filter_type' => 'active'
            ]
        );

        $this->assertEquals(123, $config->getAdditionalParam('customer_id'));
        $this->assertTrue($config->getAdditionalParam('show_balance'));
        $this->assertEquals('active', $config->getAdditionalParam('filter_type'));
        $this->assertNull($config->getAdditionalParam('non_existent'));
        $this->assertEquals('default', $config->getAdditionalParam('non_existent', 'default'));
    }

    public function testGetAllAdditionalParams(): void
    {
        $params = [
            'customer_id' => 123,
            'show_balance' => true
        ];

        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            additionalParams: $params
        );

        $this->assertEquals($params, $config->getAllAdditionalParams());
    }

    public function testToArray(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            dimension1: 5,
            dimension2: 10,
            exportToExcel: true,
            landscapeOrientation: true,
            decimals: 3,
            pageSize: 'A4',
            comments: 'Test report',
            currency: 'USD',
            convertCurrency: false,
            suppressZeros: true,
            additionalParams: ['key' => 'value']
        );

        $array = $config->toArray();

        $this->assertEquals('2026-01-01', $array['from_date']);
        $this->assertEquals('2026-01-31', $array['to_date']);
        $this->assertEquals(5, $array['dimension1']);
        $this->assertEquals(10, $array['dimension2']);
        $this->assertTrue($array['export_to_excel']);
        $this->assertEquals('L', $array['orientation']);
        $this->assertEquals(3, $array['decimals']);
        $this->assertEquals('A4', $array['page_size']);
        $this->assertEquals('Test report', $array['comments']);
        $this->assertEquals('USD', $array['currency']);
        $this->assertFalse($array['convert_currency']);
        $this->assertTrue($array['suppress_zeros']);
        $this->assertEquals(['key' => 'value'], $array['additional_params']);
    }
}
