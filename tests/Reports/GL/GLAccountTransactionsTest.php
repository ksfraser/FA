<?php

declare(strict_types=1);

namespace FA\Tests\Reports\GL;

use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use FA\Modules\Reports\Base\ReportConfig;
use FA\Modules\Reports\GL\GLAccountTransactions;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for GL Account Transactions report
 */
class GLAccountTransactionsTest extends TestCase
{
    private DBALInterface $dbal;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;
    private GLAccountTransactions $service;

    protected function setUp(): void
    {
        $this->dbal = $this->createMock(DBALInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new GLAccountTransactions(
            $this->dbal,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testGenerateAccountTransactions(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            additionalParams: [
                'from_account' => '1000',
                'to_account' => '1999'
            ]
        );

        // Mock accounts
        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->with($this->stringContains('chart_master'))
            ->willReturn([
                ['account_code' => '1000', 'account_name' => 'Petty Cash'],
                ['account_code' => '1010', 'account_name' => 'Bank Account']
            ]);

        // Mock transactions for each account
        $this->dbal->expects($this->exactly(2))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                // Transactions for 1000
                [
                    [
                        'type' => 10,
                        'type_no' => 1,
                        'tran_date' => '2026-01-15',
                        'amount' => 100.00,
                        'dimension_id' => 0,
                        'dimension2_id' => 0,
                        'person_type_id' => 2,
                        'person_id' => 1,
                        'memo_' => 'Payment received'
                    ],
                    [
                        'type' => 12,
                        'type_no' => 2,
                        'tran_date' => '2026-01-20',
                        'amount' => -50.00,
                        'dimension_id' => 0,
                        'dimension2_id' => 0,
                        'person_type_id' => 3,
                        'person_id' => 2,
                        'memo_' => 'Expense paid'
                    ]
                ],
                // Transactions for 1010
                [
                    [
                        'type' => 1,
                        'type_no' => 3,
                        'tran_date' => '2026-01-10',
                        'amount' => 1000.00,
                        'dimension_id' => 0,
                        'dimension2_id' => 0,
                        'person_type_id' => 0,
                        'person_id' => 0,
                        'memo_' => 'Deposit'
                    ]
                ]
            );

        // Mock opening balances
        $this->dbal->expects($this->any())
            ->method('fetchOne')
            ->willReturn(['balance' => 0.0]);

        $result = $this->service->generate($config);

        $this->assertArrayHasKey('accounts', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertCount(2, $result['accounts']);
        $this->assertEquals(2, $result['summary']['account_count']);
        $this->assertEquals(3, $result['summary']['total_transactions']);
    }

    public function testAccountWithOpeningBalance(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            additionalParams: [
                'from_account' => '5000',
                'to_account' => '5000'
            ]
        );

        // Mock P&L account
        $this->dbal->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['account_code' => '5000', 'account_name' => 'Sales']],
                [
                    [
                        'type' => 10,
                        'type_no' => 1,
                        'tran_date' => '2026-01-15',
                        'amount' => -500.00, // Credit (sales)
                        'dimension_id' => 0,
                        'dimension2_id' => 0,
                        'person_type_id' => 2,
                        'person_id' => 1,
                        'memo_' => 'Invoice #001'
                    ]
                ]
            );

        // Mock opening balance (prior sales)
        $this->dbal->method('fetchOne')
            ->willReturn(['balance' => -1000.00]);

        $result = $this->service->generate($config);

        $this->assertCount(1, $result['accounts']);
        $account = $result['accounts'][0];
        
        $this->assertEquals('5000', $account['account_code']);
        $this->assertEquals(-1000.00, $account['opening_balance']);
        $this->assertCount(1, $account['transactions']);
        $this->assertEquals(-1500.00, $account['closing_balance']); // -1000 + -500
    }

    public function testRunningBalance(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            additionalParams: [
                'from_account' => '1000',
                'to_account' => '1000'
            ]
        );

        $this->dbal->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['account_code' => '1000', 'account_name' => 'Cash']],
                [
                    ['type' => 1, 'type_no' => 1, 'tran_date' => '2026-01-05', 'amount' => 100.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_type_id' => 0, 'person_id' => 0, 'memo_' => 'A'],
                    ['type' => 1, 'type_no' => 2, 'tran_date' => '2026-01-10', 'amount' => 50.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_type_id' => 0, 'person_id' => 0, 'memo_' => 'B'],
                    ['type' => 1, 'type_no' => 3, 'tran_date' => '2026-01-15', 'amount' => -30.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_type_id' => 0, 'person_id' => 0, 'memo_' => 'C'],
                    ['type' => 1, 'type_no' => 4, 'tran_date' => '2026-01-20', 'amount' => -20.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_type_id' => 0, 'person_id' => 0, 'memo_' => 'D']
                ]
            );

        $this->dbal->method('fetchOne')
            ->willReturn(['balance' => 0.0]);

        $result = $this->service->generate($config);

        $transactions = $result['accounts'][0]['transactions'];
        
        $this->assertEquals(100.00, $transactions[0]['balance']);  // 0 + 100
        $this->assertEquals(150.00, $transactions[1]['balance']);  // 100 + 50
        $this->assertEquals(120.00, $transactions[2]['balance']);  // 150 - 30
        $this->assertEquals(100.00, $transactions[3]['balance']);  // 120 - 20
        $this->assertEquals(100.00, $result['accounts'][0]['closing_balance']);
    }

    public function testWithDimensions(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            dimension1: 5,
            dimension2: 10,
            additionalParams: [
                'from_account' => '1000',
                'to_account' => '1000'
            ]
        );

        $this->dbal->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['account_code' => '1000', 'account_name' => 'Cash']],
                [
                    [
                        'type' => 1,
                        'type_no' => 1,
                        'tran_date' => '2026-01-15',
                        'amount' => 100.00,
                        'dimension_id' => 5,
                        'dimension2_id' => 10,
                        'person_type_id' => 0,
                        'person_id' => 0,
                        'memo_' => 'Filtered transaction'
                    ]
                ]
            );

        $this->dbal->method('fetchOne')
            ->willReturn(['balance' => 0.0]);

        $result = $this->service->generate($config);

        $this->assertCount(1, $result['accounts']);
        $this->assertCount(1, $result['accounts'][0]['transactions']);
    }

    public function testSkipAccountsWithNoActivity(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            additionalParams: [
                'from_account' => '1000',
                'to_account' => '2000'
            ]
        );

        $this->dbal->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [
                    ['account_code' => '1000', 'account_name' => 'Active'],
                    ['account_code' => '1500', 'account_name' => 'Inactive']
                ],
                [['type' => 1, 'type_no' => 1, 'tran_date' => '2026-01-15', 'amount' => 100.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_type_id' => 0, 'person_id' => 0, 'memo_' => 'Active']],
                [] // No transactions for 1500
            );

        $this->dbal->method('fetchOne')
            ->willReturn(['balance' => 0.0]);

        $result = $this->service->generate($config);

        // Only account with activity should be included
        $this->assertCount(1, $result['accounts']);
        $this->assertEquals('1000', $result['accounts'][0]['account_code']);
    }

    public function testBalanceSheetAccountNoOpeningBalance(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            additionalParams: [
                'from_account' => '1000',
                'to_account' => '1000'
            ]
        );

        $this->dbal->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['account_code' => '1000', 'account_name' => 'Asset Account']],
                [
                    [
                        'type' => 1,
                        'type_no' => 1,
                        'tran_date' => '2026-01-15',
                        'amount' => 100.00,
                        'dimension_id' => 0,
                        'dimension2_id' => 0,
                        'person_type_id' => 0,
                        'person_id' => 0,
                        'memo_' => 'Transaction'
                    ]
                ]
            );

        // No opening balance call expected for balance sheet accounts
        $this->dbal->expects($this->never())
            ->method('fetchOne');

        $result = $this->service->generate($config);

        $this->assertEquals(0.0, $result['accounts'][0]['opening_balance']);
    }

    public function testSummaryStatistics(): void
    {
        $config = new ReportConfig(
            fromDate: '2026-01-01',
            toDate: '2026-01-31',
            additionalParams: [
                'from_account' => '1000',
                'to_account' => '2000'
            ]
        );

        $this->dbal->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [
                    ['account_code' => '1000', 'account_name' => 'Account 1'],
                    ['account_code' => '2000', 'account_name' => 'Account 2']
                ],
                [
                    ['type' => 1, 'type_no' => 1, 'tran_date' => '2026-01-05', 'amount' => 100.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_type_id' => 0, 'person_id' => 0, 'memo_' => 'A'],
                    ['type' => 1, 'type_no' => 2, 'tran_date' => '2026-01-10', 'amount' => 50.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_type_id' => 0, 'person_id' => 0, 'memo_' => 'B']
                ],
                [
                    ['type' => 1, 'type_no' => 3, 'tran_date' => '2026-01-15', 'amount' => 200.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_type_id' => 0, 'person_id' => 0, 'memo_' => 'C'],
                    ['type' => 1, 'type_no' => 4, 'tran_date' => '2026-01-20', 'amount' => 150.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_type_id' => 0, 'person_id' => 0, 'memo_' => 'D'],
                    ['type' => 1, 'type_no' => 5, 'tran_date' => '2026-01-25', 'amount' => 100.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_type_id' => 0, 'person_id' => 0, 'memo_' => 'E']
                ]
            );

        $this->dbal->method('fetchOne')
            ->willReturn(['balance' => 0.0]);

        $result = $this->service->generate($config);

        $this->assertEquals(2, $result['summary']['account_count']);
        $this->assertEquals(5, $result['summary']['total_transactions']); // 2 + 3
    }
}
