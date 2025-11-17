<?php

use PHPUnit\Framework\TestCase;

class crm_contacts_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/db/crm_contacts_db.inc';
        $this->assertTrue(true);
    }
}

