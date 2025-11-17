<?php

use PHPUnit\Framework\TestCase;
use FA\Database;
use FA\DatabaseService;

class DatabaseServiceTest extends TestCase
{
    private DatabaseService $dbService;

    protected function setUp(): void
    {
        if (!defined('TB_PREF')) {
            define('TB_PREF', '0_');
        }
        $params = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];
        $db = new Database($params);
        $connections = [
            0 => ['tbpref' => 'test_']
        ];
        $this->dbService = new DatabaseService($db, $connections, 0);

        // Create test table with prefix
        $this->dbService->execute('CREATE TABLE test_table (id INTEGER PRIMARY KEY, name TEXT)');
    }

    public function testQuery()
    {
        $this->dbService->execute('INSERT INTO test_table (name) VALUES (?)', ['Test']);
        $result = $this->dbService->query('SELECT * FROM test_table');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Test', $result[0]['name']);
    }

    public function testExecute()
    {
        $affected = $this->dbService->execute('INSERT INTO test_table (name) VALUES (?)', ['Test']);
        $this->assertEquals(1, $affected);
    }

    public function testFetchRow()
    {
        $this->dbService->execute('INSERT INTO test_table (name) VALUES (?)', ['Test']);
        $result = $this->dbService->query('SELECT * FROM test_table');
        $row = $this->dbService->fetchRow($result);
        $this->assertIsArray($row);
        $this->assertEquals('Test', $row['name']);
    }

    public function testNumRows()
    {
        $this->dbService->execute('INSERT INTO test_table (name) VALUES (?)', ['Test1']);
        $this->dbService->execute('INSERT INTO test_table (name) VALUES (?)', ['Test2']);
        $result = $this->dbService->query('SELECT * FROM test_table');
        $this->assertEquals(2, $this->dbService->numRows($result));
    }

    public function testInsertId()
    {
        $this->dbService->execute('INSERT INTO test_table (name) VALUES (?)', ['Test']);
        $id = $this->dbService->insertId();
        $this->assertIsString($id);
    }

    public function testPrefixReplacement()
    {
        // Assuming TB_PREF is defined
        if (!defined('TB_PREF')) {
            define('TB_PREF', '0_');
        }
        $this->dbService = new DatabaseService(new Database(['driver' => 'pdo_sqlite', 'memory' => true]), [['tbpref' => 'test_']], 0);
        $this->dbService->execute('CREATE TABLE test_table (id INTEGER PRIMARY KEY)');
        $result = $this->dbService->query('SELECT * FROM ' . TB_PREF . 'table');
        // Should replace 0_ with test_
        $this->assertIsArray($result);
    }
}