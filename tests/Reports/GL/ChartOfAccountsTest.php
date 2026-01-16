<?php

declare(strict_types=1);

namespace FA\Tests\Reports\GL;

use FA\Modules\Reports\GL\ChartOfAccounts;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ChartOfAccountsTest extends TestCase
{
    private DBALInterface $db;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;
    private ChartOfAccounts $report;

    protected function setUp(): void
    {
        $this->db = $this->createMock(DBALInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->report = new ChartOfAccounts(
            $this->db,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testGenerateChartOfAccounts(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank Account',
                'account_code2' => 'BANK001',
                'account_type' => 1,
                'type_name' => 'Current Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'balance' => 15000.00
            ],
            [
                'account_code' => '4010',
                'account_name' => 'Sales Revenue',
                'account_code2' => 'SALES001',
                'account_type' => 10,
                'type_name' => 'Sales',
                'class_id' => 3,
                'class_name' => 'Income',
                'balance' => -50000.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate(true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('accounts', $result);
        $this->assertArrayHasKey('by_class', $result);
        $this->assertCount(2, $result['accounts']);
    }

    public function testWithoutBalances(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank Account',
                'account_code2' => 'BANK001',
                'account_type' => 1,
                'type_name' => 'Current Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'balance' => null
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate(false);

        $this->assertIsArray($result);
        $this->assertNull($result['accounts'][0]['balance']);
    }

    public function testGroupsByClass(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_code2' => '',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'balance' => 15000.00
            ],
            [
                'account_code' => '4010',
                'account_name' => 'Sales',
                'account_code2' => '',
                'account_type' => 10,
                'type_name' => 'Sales',
                'class_id' => 3,
                'class_name' => 'Income',
                'balance' => -50000.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate(true);

        $this->assertArrayHasKey(1, $result['by_class']);
        $this->assertArrayHasKey(3, $result['by_class']);
        $this->assertCount(1, $result['by_class'][1]);
        $this->assertCount(1, $result['by_class'][3]);
    }

    public function testGroupsByType(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_code2' => '',
                'account_type' => 1,
                'type_name' => 'Current Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'balance' => 15000.00
            ],
            [
                'account_code' => '1500',
                'account_name' => 'Equipment',
                'account_code2' => '',
                'account_type' => 2,
                'type_name' => 'Fixed Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'balance' => 50000.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate(true);

        $this->assertArrayHasKey(1, $result['by_type']);
        $this->assertArrayHasKey(2, $result['by_type']);
    }

    public function testAccountCount(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_code2' => '',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'balance' => 15000.00
            ],
            [
                'account_code' => '4010',
                'account_name' => 'Sales',
                'account_code2' => '',
                'account_type' => 10,
                'type_name' => 'Sales',
                'class_id' => 3,
                'class_name' => 'Income',
                'balance' => -50000.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate(true);

        $this->assertEquals(2, $result['summary']['account_count']);
    }

    public function testEmptyChart(): void
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $result = $this->report->generate(true);

        $this->assertEmpty($result['accounts']);
        $this->assertEquals(0, $result['summary']['account_count']);
    }

    public function testExportToPDF(): void
    {
        $data = [
            'accounts' => [],
            'summary' => ['account_count' => 50]
        ];

        $result = $this->report->exportToPDF($data, 'Chart of Accounts');

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    public function testExportToExcel(): void
    {
        $data = [
            'accounts' => [],
            'summary' => ['account_count' => 50]
        ];

        $result = $this->report->exportToExcel($data, 'Chart of Accounts');

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }
}
