<?php

namespace FA\Tests;

use PHPUnit\Framework\TestCase;
use FA\Tests\Mocks\MockDatabaseConnection;

/**
 * Database Connection Test
 *
 * Tests the database abstraction layer with mocks.
 *
 * @package FA\Tests
 */
class DatabaseConnectionTest extends TestCase
{
    private MockDatabaseConnection $db;

    protected function setUp(): void
    {
        $this->db = new MockDatabaseConnection();
    }

    /** @test */
    public function testQueryExecutesAndLogsSQL(): void
    {
        $this->db->setQueryResult('SELECT', [
            ['id' => 1, 'name' => 'Test']
        ]);

        $result = $this->db->query('SELECT * FROM test');
        
        $this->assertNotFalse($result);
        $this->assertCount(1, $this->db->getQueryLog());
        $this->assertEquals('SELECT * FROM test', $this->db->getQueryLog()[0]);
    }

    /** @test */
    public function testFetchReturnsRowData(): void
    {
        $this->db->setQueryResult('SELECT', [
            ['id' => 1, 'name' => 'Test1'],
            ['id' => 2, 'name' => 'Test2']
        ]);

        $result = $this->db->query('SELECT * FROM test');
        
        $row1 = $this->db->fetch($result);
        $this->assertEquals(1, $row1['id']);
        $this->assertEquals('Test1', $row1['name']);

        $row2 = $this->db->fetch($result);
        $this->assertEquals(2, $row2['id']);
        
        $row3 = $this->db->fetch($result);
        $this->assertNull($row3);
    }

    /** @test */
    public function testFetchAllReturnsAllRows(): void
    {
        $this->db->setQueryResult('SELECT', [
            ['id' => 1, 'name' => 'Test1'],
            ['id' => 2, 'name' => 'Test2'],
            ['id' => 3, 'name' => 'Test3']
        ]);

        $result = $this->db->query('SELECT * FROM test');
        $rows = $this->db->fetchAll($result);

        $this->assertCount(3, $rows);
        $this->assertEquals(1, $rows[0]['id']);
        $this->assertEquals(3, $rows[2]['id']);
    }

    /** @test */
    public function testEscapeHandlesStrings(): void
    {
        $escaped = $this->db->escape("O'Reilly");
        $this->assertEquals("'O\\'Reilly'", $escaped);
    }

    /** @test */
    public function testEscapeHandlesNumbers(): void
    {
        $this->assertEquals('123', $this->db->escape(123));
        $this->assertEquals('45.67', $this->db->escape(45.67));
    }

    /** @test */
    public function testEscapeHandlesNull(): void
    {
        $this->assertEquals('NULL', $this->db->escape(null));
    }

    /** @test */
    public function testTransactionManagement(): void
    {
        $this->assertFalse($this->db->isInTransaction());
        
        $this->assertTrue($this->db->begin());
        $this->assertTrue($this->db->isInTransaction());
        
        $this->assertTrue($this->db->commit());
        $this->assertFalse($this->db->isInTransaction());
    }

    /** @test */
    public function testTransactionRollback(): void
    {
        $this->db->begin();
        $this->assertTrue($this->db->isInTransaction());
        
        $this->assertTrue($this->db->rollback());
        $this->assertFalse($this->db->isInTransaction());
    }

    /** @test */
    public function testInsertIdTracking(): void
    {
        $this->db->setInsertId(42);
        $this->assertEquals(42, $this->db->insertId());
    }

    /** @test */
    public function testAffectedRowsTracking(): void
    {
        $this->db->setAffectedRows(5);
        $this->assertEquals(5, $this->db->affectedRows());
    }

    /** @test */
    public function testErrorHandling(): void
    {
        $this->db->setError('Test error', 1064);
        
        $result = $this->db->query('INVALID SQL');
        
        $this->assertFalse($result);
        $this->assertEquals('Test error', $this->db->error());
        $this->assertEquals(1064, $this->db->errno());
    }

    /** @test */
    public function testNumRowsReturnsCorrectCount(): void
    {
        $this->db->setQueryResult('SELECT', [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3]
        ]);

        $result = $this->db->query('SELECT * FROM test');
        $this->assertEquals(3, $this->db->numRows($result));
    }

    /** @test */
    public function testQueryLogCanBeCleared(): void
    {
        $this->db->query('SELECT 1');
        $this->db->query('SELECT 2');
        $this->assertCount(2, $this->db->getQueryLog());
        
        $this->db->clearQueryLog();
        $this->assertCount(0, $this->db->getQueryLog());
    }

    /** @test */
    public function testMultipleQueryResults(): void
    {
        $this->db->setQueryResult('users', [['id' => 1, 'name' => 'User']]);
        $this->db->setQueryResult('products', [['id' => 2, 'name' => 'Product']]);

        $userResult = $this->db->query('SELECT * FROM users');
        $userRow = $this->db->fetch($userResult);
        $this->assertEquals('User', $userRow['name']);

        $productResult = $this->db->query('SELECT * FROM products');
        $productRow = $this->db->fetch($productResult);
        $this->assertEquals('Product', $productRow['name']);
    }
}
