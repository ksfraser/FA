<?php

use PHPUnit\Framework\TestCase;
use FA\Database;
use Doctrine\DBAL\DriverManager;

class DatabaseTest extends TestCase
{
    private Database $db;

    protected function setUp(): void
    {
        // Use in-memory SQLite for testing
        $params = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];
        $this->db = new Database($params);

        // Create a test table
        $this->db->execute('CREATE TABLE test_table (id INTEGER PRIMARY KEY, name TEXT)');
    }

    public function testExecuteInsert()
    {
        $affected = $this->db->execute('INSERT INTO test_table (name) VALUES (?)', ['Test Name']);
        $this->assertEquals(1, $affected);

        $id = $this->db->getLastInsertId();
        $this->assertIsString($id);
    }

    public function testQuerySelect()
    {
        $this->db->execute('INSERT INTO test_table (name) VALUES (?)', ['Test Name']);
        $results = $this->db->query('SELECT * FROM test_table WHERE name = ?', ['Test Name']);
        $this->assertCount(1, $results);
        $this->assertEquals('Test Name', $results[0]['name']);
    }

    public function testTransactionCommit()
    {
        $this->db->beginTransaction();
        $this->db->execute('INSERT INTO test_table (name) VALUES (?)', ['Committed']);
        $this->db->commit();

        $results = $this->db->query('SELECT COUNT(*) as count FROM test_table');
        $this->assertEquals(1, $results[0]['count']);
    }

    public function testTransactionRollback()
    {
        $this->db->beginTransaction();
        $this->db->execute('INSERT INTO test_table (name) VALUES (?)', ['Rolled Back']);
        $this->db->rollBack();

        $results = $this->db->query('SELECT COUNT(*) as count FROM test_table');
        $this->assertEquals(0, $results[0]['count']);
    }

    public function testQueryWithException()
    {
        $this->expectException(\RuntimeException::class);
        $this->db->query('SELECT * FROM non_existent_table');
    }

    public function testExecuteWithException()
    {
        $this->expectException(\RuntimeException::class);
        $this->db->execute('INSERT INTO non_existent_table VALUES (?)', ['test']);
    }
}