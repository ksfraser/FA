<?php

declare(strict_types=1);

namespace FA\Tests\Reports\GL;

use FA\Modules\Reports\GL\TrialBalance;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TrialBalanceTest extends TestCase
{
    private DBALInterface $db;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;
    private TrialBalance $report;

    protected function setUp(): void
    {
        $this->db = $this->createMock(DBALInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->report = new TrialBalance(
            $this->db,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testGenerateTrialBalance(): void
    {
        // Mock account data with balances
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank Account',
                'account_type' => 1,
                'type_name' => 'Current Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 10000.00,
                'prev_credit' => 5000.00,
                'curr_debit' => 3000.00,
                'curr_credit' => 1000.00,
                'tot_debit' => 13000.00,
                'tot_credit' => 6000.00
            ],
            [
                'account_code' => '4010',
                'account_name' => 'Sales Revenue',
                'account_type' => 10,
                'type_name' => 'Sales',
                'class_id' => 3,
                'class_name' => 'Income',
                'prev_debit' => 0.00,
                'prev_credit' => 50000.00,
                'curr_debit' => 0.00,
                'curr_credit' => 10000.00,
                'tot_debit' => 0.00,
                'tot_credit' => 60000.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('accounts', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertCount(2, $result['accounts']);
    }

    public function testCalculatesBalances(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank Account',
                'account_type' => 1,
                'type_name' => 'Current Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 10000.00,
                'prev_credit' => 5000.00,
                'curr_debit' => 3000.00,
                'curr_credit' => 1000.00,
                'tot_debit' => 13000.00,
                'tot_credit' => 6000.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $account = $result['accounts'][0];
        
        // prev_balance = prev_debit - prev_credit = 10000 - 5000 = 5000
        $this->assertEquals(5000.00, $account['prev_balance']);
        
        // curr_balance = curr_debit - curr_credit = 3000 - 1000 = 2000
        $this->assertEquals(2000.00, $account['curr_balance']);
        
        // tot_balance = tot_debit - tot_credit = 13000 - 6000 = 7000
        $this->assertEquals(7000.00, $account['tot_balance']);
    }

    public function testSummaryTotals(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 10000.00,
                'prev_credit' => 5000.00,
                'curr_debit' => 3000.00,
                'curr_credit' => 1000.00,
                'tot_debit' => 13000.00,
                'tot_credit' => 6000.00
            ],
            [
                'account_code' => '4010',
                'account_name' => 'Sales',
                'account_type' => 10,
                'type_name' => 'Sales',
                'class_id' => 3,
                'class_name' => 'Income',
                'prev_debit' => 2000.00,
                'prev_credit' => 7000.00,
                'curr_debit' => 500.00,
                'curr_credit' => 2500.00,
                'tot_debit' => 2500.00,
                'tot_credit' => 9500.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $summary = $result['summary'];
        
        // Total previous: 10000 + 2000 = 12000 debit, 5000 + 7000 = 12000 credit
        $this->assertEquals(12000.00, $summary['prev_debit']);
        $this->assertEquals(12000.00, $summary['prev_credit']);
        
        // Total current: 3000 + 500 = 3500 debit, 1000 + 2500 = 3500 credit
        $this->assertEquals(3500.00, $summary['curr_debit']);
        $this->assertEquals(3500.00, $summary['curr_credit']);
        
        // Total: 13000 + 2500 = 15500 debit, 6000 + 9500 = 15500 credit
        $this->assertEquals(15500.00, $summary['tot_debit']);
        $this->assertEquals(15500.00, $summary['tot_credit']);
    }

    public function testBalanceCheck(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 10000.00,
                'prev_credit' => 10000.00,
                'curr_debit' => 5000.00,
                'curr_credit' => 5000.00,
                'tot_debit' => 15000.00,
                'tot_credit' => 15000.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $this->assertTrue($result['summary']['is_balanced']);
        $this->assertEquals(0.0, $result['summary']['prev_balance']);
        $this->assertEquals(0.0, $result['summary']['curr_balance']);
        $this->assertEquals(0.0, $result['summary']['tot_balance']);
    }

    public function testExcludeZeroBalances(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 10000.00,
                'prev_credit' => 5000.00,
                'curr_debit' => 3000.00,
                'curr_credit' => 1000.00,
                'tot_debit' => 13000.00,
                'tot_credit' => 6000.00
            ],
            [
                'account_code' => '1300',
                'account_name' => 'Empty Account',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 0.00,
                'prev_credit' => 0.00,
                'curr_debit' => 0.00,
                'curr_credit' => 0.00,
                'tot_debit' => 0.00,
                'tot_credit' => 0.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate('2024-01-01', '2024-01-31', false);

        // Should exclude zero balance account
        $this->assertCount(1, $result['accounts']);
        $this->assertEquals('1200', $result['accounts'][0]['account_code']);
    }

    public function testIncludeZeroBalances(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 10000.00,
                'prev_credit' => 5000.00,
                'curr_debit' => 3000.00,
                'curr_credit' => 1000.00,
                'tot_debit' => 13000.00,
                'tot_credit' => 6000.00
            ],
            [
                'account_code' => '1300',
                'account_name' => 'Empty Account',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 0.00,
                'prev_credit' => 0.00,
                'curr_debit' => 0.00,
                'curr_credit' => 0.00,
                'tot_debit' => 0.00,
                'tot_credit' => 0.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate('2024-01-01', '2024-01-31', true);

        // Should include zero balance account
        $this->assertCount(2, $result['accounts']);
    }

    public function testGroupsByAccountType(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_type' => 1,
                'type_name' => 'Current Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 10000.00,
                'prev_credit' => 5000.00,
                'curr_debit' => 3000.00,
                'curr_credit' => 1000.00,
                'tot_debit' => 13000.00,
                'tot_credit' => 6000.00
            ],
            [
                'account_code' => '1500',
                'account_name' => 'Equipment',
                'account_type' => 2,
                'type_name' => 'Fixed Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 50000.00,
                'prev_credit' => 0.00,
                'curr_debit' => 0.00,
                'curr_credit' => 0.00,
                'tot_debit' => 50000.00,
                'tot_credit' => 0.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $byType = $result['by_type'];
        
        $this->assertArrayHasKey(1, $byType); // Current Assets
        $this->assertArrayHasKey(2, $byType); // Fixed Assets
        $this->assertCount(1, $byType[1]);
        $this->assertCount(1, $byType[2]);
    }

    public function testGroupsByAccountClass(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 10000.00,
                'prev_credit' => 5000.00,
                'curr_debit' => 3000.00,
                'curr_credit' => 1000.00,
                'tot_debit' => 13000.00,
                'tot_credit' => 6000.00
            ],
            [
                'account_code' => '4010',
                'account_name' => 'Sales',
                'account_type' => 10,
                'type_name' => 'Sales',
                'class_id' => 3,
                'class_name' => 'Income',
                'prev_debit' => 0.00,
                'prev_credit' => 50000.00,
                'curr_debit' => 0.00,
                'curr_credit' => 10000.00,
                'tot_debit' => 0.00,
                'tot_credit' => 60000.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $byClass = $result['by_class'];
        
        $this->assertArrayHasKey(1, $byClass); // Assets
        $this->assertArrayHasKey(3, $byClass); // Income
        $this->assertCount(1, $byClass[1]);
        $this->assertCount(1, $byClass[3]);
    }

    public function testDimensionFiltering(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 10000.00,
                'prev_credit' => 5000.00,
                'curr_debit' => 3000.00,
                'curr_credit' => 1000.00,
                'tot_debit' => 13000.00,
                'tot_credit' => 6000.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate('2024-01-01', '2024-01-31', false, 5, 3);

        $this->assertArrayHasKey('filters', $result);
        $this->assertEquals(5, $result['filters']['dimension']);
        $this->assertEquals(3, $result['filters']['dimension2']);
    }

    public function testUnbalancedDetection(): void
    {
        $mockAccounts = [
            [
                'account_code' => '1200',
                'account_name' => 'Bank',
                'account_type' => 1,
                'type_name' => 'Assets',
                'class_id' => 1,
                'class_name' => 'Assets',
                'prev_debit' => 10000.00,
                'prev_credit' => 5000.00, // Unbalanced: 10000 - 5000 = 5000 balance
                'curr_debit' => 3000.00,
                'curr_credit' => 1000.00,
                'tot_debit' => 13000.00,
                'tot_credit' => 6000.00
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockAccounts);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $this->assertFalse($result['summary']['is_balanced']);
        $this->assertNotEquals(0.0, $result['summary']['tot_balance']);
    }

    public function testEmptyResultSet(): void
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $this->assertIsArray($result);
        $this->assertEmpty($result['accounts']);
        $this->assertEquals(0.0, $result['summary']['tot_debit']);
        $this->assertEquals(0.0, $result['summary']['tot_credit']);
        $this->assertTrue($result['summary']['is_balanced']);
    }

    public function testExportToPDF(): void
    {
        $data = [
            'accounts' => [],
            'summary' => [
                'tot_debit' => 100000.00,
                'tot_credit' => 100000.00,
                'is_balanced' => true
            ]
        ];

        $result = $this->report->exportToPDF($data, 'Trial Balance Report');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testExportToExcel(): void
    {
        $data = [
            'accounts' => [],
            'summary' => [
                'tot_debit' => 100000.00,
                'tot_credit' => 100000.00,
                'is_balanced' => true
            ]
        ];

        $result = $this->report->exportToExcel($data, 'Trial Balance Report');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }
}
