<?php

require_once __DIR__ . '/../includes/types.inc';

use PHPUnit\Framework\TestCase;
use Ksfraser\HTML\Elements\FaTable;

class FaTableTest extends TestCase
{
    public function testFaTableGeneratesSameAsStartTable()
    {
        // Test TABLESTYLE2 (default)
        $table = new FaTable();
        ob_start();
        $table->toHtml();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('<center><table  class="tablestyle2" cellpadding="2" cellspacing="0"  ></table></center>', $output);
    }
    
    public function testFaTableWithStyle()
    {
        $table = new FaTable(TABLESTYLE);
        ob_start();
        $table->toHtml();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('<center><table  class="tablestyle" cellpadding="2" cellspacing="0"  ></table></center>', $output);
    }
    
    public function testFaTableWithExtra()
    {
        $table = new FaTable(TABLESTYLE2, "width='50%'");
        ob_start();
        $table->toHtml();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('<center><table  class="tablestyle2" cellpadding="2" cellspacing="0" width="50%"  ></table></center>', $output);
    }
}