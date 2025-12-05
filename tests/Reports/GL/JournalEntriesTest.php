<?php

declare(strict_types=1);

namespace FA\Tests\Reports\GL;

use FA\Modules\Reports\GL\JournalEntries;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class JournalEntriesTest extends TestCase
{
    private DBALInterface $db;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;
    private JournalEntries $report;

    protected function setUp(): void
    {
        $this->db = $this->createMock(DBALInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->report = new JournalEntries(
            $this->db,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testGenerateJournalEntriesReport(): void
    {
        $mockData = [
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank Account',
                'amount' => 1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Payment received'
            ],
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '4010',
                'account_name' => 'Sales Revenue',
                'amount' => -1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Payment received'
            ],
            [
                'type' => 10,
                'type_no' => 2,
                'tran_date' => '2024-01-16',
                'account' => '5010',
                'account_name' => 'Cost of Goods Sold',
                'amount' => 500.00,
                'person_id' => null,
                'dimension_id' => 1,
                'dimension2_id' => 0,
                'memo_' => 'Inventory purchase'
            ],
            [
                'type' => 10,
                'type_no' => 2,
                'tran_date' => '2024-01-16',
                'account' => '2100',
                'account_name' => 'Accounts Payable',
                'amount' => -500.00,
                'person_id' => null,
                'dimension_id' => 1,
                'dimension2_id' => 0,
                'memo_' => 'Inventory purchase'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('entries', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertCount(2, $result['entries']); // 2 transactions
    }

    public function testGroupEntriesByTransaction(): void
    {
        $mockData = [
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank Account',
                'amount' => 1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Payment received'
            ],
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '4010',
                'account_name' => 'Sales Revenue',
                'amount' => -1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Payment received'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $entry = $result['entries'][0];
        $this->assertEquals(0, $entry['type']);
        $this->assertEquals(1, $entry['type_no']);
        $this->assertCount(2, $entry['lines']); // 2 line items
    }

    public function testCalculateDebitCreditTotals(): void
    {
        $mockData = [
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank Account',
                'amount' => 1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Payment'
            ],
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '4010',
                'account_name' => 'Sales Revenue',
                'amount' => -1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Payment'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $entry = $result['entries'][0];
        $this->assertEquals(1000.00, $entry['total_debit']);
        $this->assertEquals(1000.00, $entry['total_credit']);
    }

    public function testFilterBySystemType(): void
    {
        $mockData = [
            [
                'type' => 10, // Sales invoice
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank Account',
                'amount' => 1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Invoice payment'
            ],
            [
                'type' => 10,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '4010',
                'account_name' => 'Sales',
                'amount' => -1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Invoice payment'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-01-31', 10);

        $this->assertCount(1, $result['entries']);
        $this->assertEquals(10, $result['entries'][0]['type']);
    }

    public function testSummaryTotals(): void
    {
        $mockData = [
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank',
                'amount' => 1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ],
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '4010',
                'account_name' => 'Sales',
                'amount' => -1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ],
            [
                'type' => 10,
                'type_no' => 2,
                'tran_date' => '2024-01-16',
                'account' => '5010',
                'account_name' => 'COGS',
                'amount' => 500.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ],
            [
                'type' => 10,
                'type_no' => 2,
                'tran_date' => '2024-01-16',
                'account' => '2100',
                'account_name' => 'AP',
                'amount' => -500.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $this->assertEquals(1500.00, $result['summary']['total_debit']);
        $this->assertEquals(1500.00, $result['summary']['total_credit']);
        $this->assertEquals(2, $result['summary']['transaction_count']);
    }

    public function testEmptyResultSet(): void
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $this->assertIsArray($result);
        $this->assertEmpty($result['entries']);
        $this->assertEquals(0.0, $result['summary']['total_debit']);
        $this->assertEquals(0.0, $result['summary']['total_credit']);
    }

    public function testIncludesDimensionInformation(): void
    {
        $mockData = [
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank',
                'amount' => 1000.00,
                'person_id' => null,
                'dimension_id' => 5,
                'dimension2_id' => 3,
                'memo_' => 'Test'
            ],
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '4010',
                'account_name' => 'Sales',
                'amount' => -1000.00,
                'person_id' => null,
                'dimension_id' => 5,
                'dimension2_id' => 3,
                'memo_' => 'Test'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $line = $result['entries'][0]['lines'][0];
        $this->assertEquals(5, $line['dimension_id']);
        $this->assertEquals(3, $line['dimension2_id']);
    }

    public function testHandlesNullDimensions(): void
    {
        $mockData = [
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank',
                'amount' => 1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ],
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '4010',
                'account_name' => 'Sales',
                'amount' => -1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $line = $result['entries'][0]['lines'][0];
        $this->assertEquals(0, $line['dimension_id']);
        $this->assertEquals(0, $line['dimension2_id']);
    }

    public function testIncludesTransactionReference(): void
    {
        $mockData = [
            [
                'type' => 10,
                'type_no' => 123,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank',
                'amount' => 1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ],
            [
                'type' => 10,
                'type_no' => 123,
                'tran_date' => '2024-01-15',
                'account' => '4010',
                'account_name' => 'Sales',
                'amount' => -1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $entry = $result['entries'][0];
        $this->assertEquals(10, $entry['type']);
        $this->assertEquals(123, $entry['type_no']);
    }

    public function testBalancedEntries(): void
    {
        $mockData = [
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank',
                'amount' => 1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ],
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '4010',
                'account_name' => 'Sales',
                'amount' => -1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $entry = $result['entries'][0];
        $this->assertEquals($entry['total_debit'], $entry['total_credit']);
        $this->assertTrue($entry['is_balanced']);
    }

    public function testTransactionDate(): void
    {
        $mockData = [
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '1200',
                'account_name' => 'Bank',
                'amount' => 1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ],
            [
                'type' => 0,
                'type_no' => 1,
                'tran_date' => '2024-01-15',
                'account' => '4010',
                'account_name' => 'Sales',
                'amount' => -1000.00,
                'person_id' => null,
                'dimension_id' => 0,
                'dimension2_id' => 0,
                'memo_' => 'Test'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-01-31');

        $entry = $result['entries'][0];
        $this->assertEquals('2024-01-15', $entry['tran_date']);
    }

    public function testExportToPDF(): void
    {
        $data = [
            'entries' => [],
            'summary' => [
                'total_debit' => 1000.00,
                'total_credit' => 1000.00,
                'transaction_count' => 1
            ]
        ];

        $result = $this->report->exportToPDF($data, 'Journal Entries Report');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testExportToExcel(): void
    {
        $data = [
            'entries' => [],
            'summary' => [
                'total_debit' => 1000.00,
                'total_credit' => 1000.00,
                'transaction_count' => 1
            ]
        ];

        $result = $this->report->exportToExcel($data, 'Journal Entries Report');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }
}
