<?php

declare(strict_types=1);

namespace FA\Tests\Reports\Legacy\GL;

use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use FA\Modules\Reports\Legacy\GL\JournalEntriesReport;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test suite for Journal Entries Report (rep702)
 *
 * Tests the refactored service-based journal entries report
 * covering transaction listing, grouping, and totaling logic.
 */
class JournalEntriesReportTest extends TestCase
{
    private DBALInterface $db;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;
    private JournalEntriesReport $report;

    protected function setUp(): void
    {
        $this->db = $this->createMock(DBALInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->report = new JournalEntriesReport(
            $this->db,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testGenerateJournalEntriesWithDateRange(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';
        $systemType = null; // All types

        $mockTransactions = [
            [
                'type' => 0, // Journal Entry
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank Account',
                'memo_' => 'Deposit',
                'amount' => 1000.00,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'person_id' => 0
            ],
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '4000',
                'account_name' => 'Sales Revenue',
                'memo_' => 'Deposit',
                'amount' => -1000.00,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'person_id' => 0
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $result = $this->report->generate($startDate, $endDate, $systemType);

        $this->assertArrayHasKey('entries', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertCount(1, $result['entries']); // 1 journal entry (grouped)
    }

    public function testGenerateWithSystemTypeFilter(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';
        $systemType = 10; // ST_SALESINVOICE

        $mockTransactions = [
            [
                'type' => 10,
                'type_no' => 5,
                'tran_date' => '2024-01-20',
                'account' => '1200',
                'account_name' => 'Accounts Receivable',
                'memo_' => 'Invoice payment',
                'amount' => 500.00,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'person_id' => 1
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $result = $this->report->generate($startDate, $endDate, $systemType);

        $this->assertNotEmpty($result['entries']);
        $entry = $result['entries'][0];
        $this->assertEquals(10, $entry['type']);
    }

    public function testTransactionGrouping(): void
    {
        // Test that transactions are grouped by type and type_no
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $mockTransactions = [
            // Journal Entry #1 (2 lines)
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Deposit', 'amount' => 1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '4000', 'account_name' => 'Revenue', 'memo_' => 'Deposit', 'amount' => -1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            
            // Journal Entry #2 (2 lines)
            ['type' => 0, 'type_no' => 2, 'tran_date' => '2024-01-20', 'account' => '5000', 'account_name' => 'Expenses', 'memo_' => 'Office supplies', 'amount' => 200.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 2, 'tran_date' => '2024-01-20', 'account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Office supplies', 'amount' => -200.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $result = $this->report->generate($startDate, $endDate);

        $this->assertCount(2, $result['entries']); // 2 journal entries
        
        $entry1 = $result['entries'][0];
        $this->assertEquals(1, $entry1['type_no']);
        $this->assertCount(2, $entry1['lines']); // 2 line items
        
        $entry2 = $result['entries'][1];
        $this->assertEquals(2, $entry2['type_no']);
        $this->assertCount(2, $entry2['lines']); // 2 line items
    }

    public function testDebitCreditCalculation(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $mockTransactions = [
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Deposit', 'amount' => 1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '4000', 'account_name' => 'Revenue', 'memo_' => 'Deposit', 'amount' => -1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 2, 'tran_date' => '2024-01-20', 'account' => '5000', 'account_name' => 'Expenses', 'memo_' => 'Supplies', 'amount' => 500.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 2, 'tran_date' => '2024-01-20', 'account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Supplies', 'amount' => -500.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $result = $this->report->generate($startDate, $endDate);

        // Check entry 1 totals
        $entry1 = $result['entries'][0];
        $this->assertEquals(1000.00, $entry1['total_debit']);
        $this->assertEquals(1000.00, $entry1['total_credit']);

        // Check entry 2 totals
        $entry2 = $result['entries'][1];
        $this->assertEquals(500.00, $entry2['total_debit']);
        $this->assertEquals(500.00, $entry2['total_credit']);

        // Check grand totals
        $this->assertEquals(1500.00, $result['summary']['total_debit']);
        $this->assertEquals(1500.00, $result['summary']['total_credit']);
    }

    public function testDebitLineIdentification(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $mockTransactions = [
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Deposit', 'amount' => 1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '4000', 'account_name' => 'Revenue', 'memo_' => 'Deposit', 'amount' => -1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $result = $this->report->generate($startDate, $endDate);

        $entry = $result['entries'][0];
        
        // Positive amount = debit
        $this->assertEquals(1000.00, $entry['lines'][0]['debit']);
        $this->assertEquals(0.00, $entry['lines'][0]['credit']);
        
        // Negative amount = credit
        $this->assertEquals(0.00, $entry['lines'][1]['debit']);
        $this->assertEquals(1000.00, $entry['lines'][1]['credit']);
    }

    public function testEmptyResults(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $result = $this->report->generate($startDate, $endDate);

        $this->assertEmpty($result['entries']);
        $this->assertEquals(0.00, $result['summary']['total_debit']);
        $this->assertEquals(0.00, $result['summary']['total_credit']);
        $this->assertEquals(0, $result['summary']['entry_count']);
    }

    public function testMultipleSystemTypes(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $mockTransactions = [
            // Journal Entry
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Entry', 'amount' => 1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '4000', 'account_name' => 'Revenue', 'memo_' => 'Entry', 'amount' => -1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            
            // Sales Invoice
            ['type' => 10, 'type_no' => 5, 'tran_date' => '2024-01-20', 'account' => '1200', 'account_name' => 'AR', 'memo_' => 'Invoice', 'amount' => 500.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 1],
            ['type' => 10, 'type_no' => 5, 'tran_date' => '2024-01-20', 'account' => '4000', 'account_name' => 'Sales', 'memo_' => 'Invoice', 'amount' => -500.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 1],
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $result = $this->report->generate($startDate, $endDate);

        $this->assertCount(2, $result['entries']);
        $this->assertEquals(0, $result['entries'][0]['type']); // Journal Entry
        $this->assertEquals(10, $result['entries'][1]['type']); // Sales Invoice
    }

    public function testDimensionInformation(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $mockTransactions = [
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Project A', 'amount' => 1000.00, 'dimension_id' => 5, 'dimension2_id' => 3, 'person_id' => 0],
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '4000', 'account_name' => 'Revenue', 'memo_' => 'Project A', 'amount' => -1000.00, 'dimension_id' => 5, 'dimension2_id' => 3, 'person_id' => 0],
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $result = $this->report->generate($startDate, $endDate);

        $entry = $result['entries'][0];
        $this->assertEquals(5, $entry['lines'][0]['dimension_id']);
        $this->assertEquals(3, $entry['lines'][0]['dimension2_id']);
    }

    public function testPersonIdTracking(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $mockTransactions = [
            ['type' => 10, 'type_no' => 5, 'tran_date' => '2024-01-20', 'account' => '1200', 'account_name' => 'AR', 'memo_' => 'Customer invoice', 'amount' => 500.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 123],
            ['type' => 10, 'type_no' => 5, 'tran_date' => '2024-01-20', 'account' => '4000', 'account_name' => 'Sales', 'memo_' => 'Customer invoice', 'amount' => -500.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 123],
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $result = $this->report->generate($startDate, $endDate);

        $entry = $result['entries'][0];
        $this->assertEquals(123, $entry['lines'][0]['person_id']);
    }

    public function testTransactionDateIncluded(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $mockTransactions = [
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Test', 'amount' => 1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '4000', 'account_name' => 'Revenue', 'memo_' => 'Test', 'amount' => -1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $result = $this->report->generate($startDate, $endDate);

        $entry = $result['entries'][0];
        $this->assertEquals('2024-01-15', $entry['tran_date']);
    }

    public function testSummaryStatistics(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $mockTransactions = [
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Entry 1', 'amount' => 1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 1, 'tran_date' => '2024-01-15', 'account' => '4000', 'account_name' => 'Revenue', 'memo_' => 'Entry 1', 'amount' => -1000.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 2, 'tran_date' => '2024-01-20', 'account' => '5000', 'account_name' => 'Expenses', 'memo_' => 'Entry 2', 'amount' => 500.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 0, 'type_no' => 2, 'tran_date' => '2024-01-20', 'account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Entry 2', 'amount' => -500.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 0],
            ['type' => 10, 'type_no' => 5, 'tran_date' => '2024-01-25', 'account' => '1200', 'account_name' => 'AR', 'memo_' => 'Invoice', 'amount' => 750.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 1],
            ['type' => 10, 'type_no' => 5, 'tran_date' => '2024-01-25', 'account' => '4000', 'account_name' => 'Sales', 'memo_' => 'Invoice', 'amount' => -750.00, 'dimension_id' => 0, 'dimension2_id' => 0, 'person_id' => 1],
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $result = $this->report->generate($startDate, $endDate);

        $summary = $result['summary'];
        $this->assertEquals(3, $summary['entry_count']); // 3 journal entries
        $this->assertEquals(2250.00, $summary['total_debit']); // 1000 + 500 + 750
        $this->assertEquals(2250.00, $summary['total_credit']); // 1000 + 500 + 750
        $this->assertArrayHasKey('start_date', $summary);
        $this->assertArrayHasKey('end_date', $summary);
    }

    public function testExportToPDF(): void
    {
        $data = [
            'entries' => [
                [
                    'type' => 0,
                    'type_no' => 1,
                    'tran_date' => '2024-01-15',
                    'total_debit' => 1000.00,
                    'total_credit' => 1000.00,
                    'lines' => [
                        ['account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Deposit', 'debit' => 1000.00, 'credit' => 0.00]
                    ]
                ]
            ],
            'summary' => [
                'entry_count' => 1,
                'total_debit' => 1000.00,
                'total_credit' => 1000.00
            ]
        ];

        $result = $this->report->exportToPDF($data, 'Journal Entries Report');

        $this->assertIsString($result);
        $this->assertStringContainsString('PDF', $result);
    }

    public function testExportToExcel(): void
    {
        $data = [
            'entries' => [
                [
                    'type' => 0,
                    'type_no' => 1,
                    'tran_date' => '2024-01-15',
                    'total_debit' => 1000.00,
                    'total_credit' => 1000.00,
                    'lines' => [
                        ['account' => '1200', 'account_name' => 'Bank', 'memo_' => 'Deposit', 'debit' => 1000.00, 'credit' => 0.00]
                    ]
                ]
            ],
            'summary' => [
                'entry_count' => 1,
                'total_debit' => 1000.00,
                'total_credit' => 1000.00
            ]
        ];

        $result = $this->report->exportToExcel($data, 'Journal Entries Report');

        $this->assertIsString($result);
        $this->assertStringContainsString('Excel', $result);
    }

    public function testGeneratedAtTimestamp(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $result = $this->report->generate($startDate, $endDate);

        $this->assertArrayHasKey('generated_at', $result);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result['generated_at']);
    }
}
