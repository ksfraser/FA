<?php

use PHPUnit\Framework\TestCase;
use Ksfraser\HTML\Composites\HTML_TABLE;
use Ksfraser\HTML\Composites\HTML_ROW;
use Ksfraser\HTML\Elements\HtmlString;
use Ksfraser\HTML\Elements\HtmlTableRowCell;

class HTML_TABLETest extends TestCase
{
    public function testToHtmlGeneratesTableWithoutGlobalFunctions()
    {
        // Create a table
        $table = new HTML_TABLE(2, 100);
        
        // Add a row
        $cell = new HtmlTableRowCell(new HtmlString("Test content"));
        $row = new HTML_ROW($cell);
        $table->appendRow($row);
        
        // Capture output
        ob_start();
        $table->toHtml();
        $output = ob_get_clean();
        
        // Expected HTML without global functions
        $expected = "<table class='tablestyle2' width='100%'>\n" .
                    "<tr   ><td   >Test content</td></tr>" .
                    "</table>\n";
        
        $this->assertEquals($expected, $output);
    }
}