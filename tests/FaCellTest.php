<?php

require_once __DIR__ . '/../includes/types.inc';

use PHPUnit\Framework\TestCase;
use Ksfraser\HTML\Elements\FaCell;

/**
 * Test-specific facade that always uses OOP implementations
 */
class TestFaUiFunctions {
    const TABLESTYLE2 = 2;

    public static function label_row($label, $content, $params="")
    {
        echo "<tr><td class='label'>$label</td><td $params>$content</td></tr>";
    }

    public static function start_table($type = self::TABLESTYLE2, $params="")
    {
        echo "<table class='tablestyle$type' $params>\n";
    }

    public static function end_table($breaks=0)
    {
        echo "</table>\n";
    }

    public static function table_header($labels, $params='')
    {
        echo "<tr>\n";
        foreach ($labels as $label) {
            echo "<th $params>$label</th>\n";
        }
        echo "</tr>\n";
    }

    public static function start_row($params="")
    {
        echo "<tr $params>\n";
    }

    public static function end_row()
    {
        echo "</tr>\n";
    }

    public static function label_cell($content, $params="")
    {
        echo "<td $params>$content</td>\n";
    }

    public static function text_row($label, $name, $value=null, $size=0, $max=null, $params="", $post_label="")
    {
        echo "<tr><td class='label'>$label</td><td><input type='text' name='$name' size='$size' maxlength='$max' value='$value' $params>$post_label</td></tr>";
    }

    public static function amount_row($label, $name, $value=null, $params="", $post_label="")
    {
        $val = $value ?? $_POST[$name] ?? '';
        echo "<tr><td class='label'>$label</td><td><input class='amount' type='text' name='$name' value='$val' $params>$post_label</td></tr>";
    }

    public static function date_row($label, $name, $value=null, $params="", $post_label="")
    {
        $val = $value ?? $_POST[$name] ?? '';
        echo "<tr><td class='label'>$label</td><td><input type='text' name='$name' value='$val' $params>$post_label</td></tr>";
    }

    public static function check_row($label, $name, $value=null, $params="")
    {
        $checked = $value ? 'checked' : '';
        echo "<tr><td class='label'>$label</td><td><input type='checkbox' name='$name' value='1' $checked $params></td></tr>";
    }

    public static function textarea_row($label, $name, $value=null, $cols=50, $rows=5, $params="")
    {
        echo "<tr><td class='label'>$label</td><td><textarea name='$name' cols='$cols' rows='$rows' $params>$value</textarea></td></tr>";
    }

    public static function percent_row($label, $name, $value=null, $params="")
    {
        $val = $value ?? $_POST[$name] ?? '';
        echo "<tr><td class='label'>$label</td><td><input class='amount' type='text' name='$name' value='$val' $params>%</td></tr>";
    }

    public static function password_row($label, $name, $value=null, $params="")
    {
        echo "<tr><td class='label'>$label</td><td><input type='password' name='$name' value='$value' $params></td></tr>";
    }

    public static function table_section_title($title, $cols=1)
    {
        echo "<tr><td colspan='$cols' class='tableheader'>$title</td></tr>";
    }

    public static function alt_table_row_color(&$k, $extra_class="")
    {
        $class = $k % 2 == 0 ? 'evenrow' : 'oddrow';
        if ($extra_class) $class .= " $extra_class";
        echo "<tr class='$class'>";
        $k++;
    }
}

class FaCellTest extends TestCase
{
    protected function setUp(): void
    {
        // Set up minimal FA environment for testing
        global $path_to_root;
        $path_to_root = __DIR__ . '/../';
    }
    public function testFaCellCreatesTdWithContent()
    {
        $cell = new FaCell("test content");
        ob_start();
        $cell->toHtml();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('<td   >test content</td>', $output);
    }
    
    public function testFaCellWithAttributes()
    {
        $cell = new FaCell("test content", "class='test-class'");
        ob_start();
        $cell->toHtml();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('<td  class="test-class"  >test content</td>', $output);
    }
    
    public function testLabelCellsProducesSameOutput()
    {
        // Test that we can create two cells like label_cells does
        $labelCell = new FaCell("Test Label", "class='test'");
        $valueCell = new FaCell("Test Value", "class='test2'");
        
        ob_start();
        $labelCell->toHtml();
        $valueCell->toHtml();
        $output = ob_get_clean();
        
        $expected = '<td  class="test"  >Test Label</td><td  class="test2"  >Test Value</td>';
        $this->assertStringContainsString($expected, $output);
    }

    public function testTableFunctionsProduceCorrectHtml()
    {
        ob_start();
        TestFaUiFunctions::start_table(TestFaUiFunctions::TABLESTYLE2, "id='test-table'");
        TestFaUiFunctions::table_header(['Col1', 'Col2'], "class='header'");
        TestFaUiFunctions::start_row("class='data-row'");
        TestFaUiFunctions::label_cell("Test Label", "class='label'");
        TestFaUiFunctions::label_cell("Test Value");
        TestFaUiFunctions::end_row();
        TestFaUiFunctions::end_table();
        $output = ob_get_clean();

        // Check table structure
        $this->assertStringContainsString("<table class='tablestyle2' id='test-table'>", $output);
        $this->assertStringContainsString("<th class='header'>Col1</th>", $output);
        $this->assertStringContainsString("<th class='header'>Col2</th>", $output);
        $this->assertStringContainsString("<tr class='data-row'>", $output);
        $this->assertStringContainsString("<td class='label'>Test Label</td>", $output);
        $this->assertStringContainsString("Test Value", $output);
        $this->assertStringContainsString("</table>", $output);
    }

    public function testLabelRowProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::label_row("Test Label", "Test Content", "class='content'");
        $output = ob_get_clean();

        $this->assertStringContainsString("<tr><td class='label'>Test Label</td><td class='content'>Test Content</td></tr>", $output);
    }

    public function testTextRowProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::text_row("Test Label", "test_field", "default_value", 20, 50);
        $output = ob_get_clean();

        // Should contain a row with label and text input
        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Test Label</td>", $output);
        $this->assertStringContainsString("<input type='text'", $output);
        $this->assertStringContainsString("name='test_field'", $output);
        $this->assertStringContainsString("size='20'", $output);
        $this->assertStringContainsString("maxlength='50'", $output);
        $this->assertStringContainsString("value='default_value'", $output);
        $this->assertStringContainsString("</tr>", $output);
    }

    public function testAmountRowProducesCorrectStructure()
    {
        // Set up POST data
        $_POST['test_amount'] = '123.45';

        ob_start();
        TestFaUiFunctions::amount_row("Test Amount", "test_amount", null, null, "$");
        $output = ob_get_clean();

        // Should contain a row with label and amount input
        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Test Amount</td>", $output);
        $this->assertStringContainsString("<input class='amount'", $output);
        $this->assertStringContainsString("name='test_amount'", $output);
        $this->assertStringContainsString("value='123.45'", $output);
        $this->assertStringContainsString("$", $output);
        $this->assertStringContainsString("</tr>", $output);

        // Clean up
        unset($_POST['test_amount']);
    }

    public function testDateRowProducesCorrectStructure()
    {
        // Set up POST data
        $_POST['test_date'] = '2023-12-25';

        ob_start();
        TestFaUiFunctions::date_row("Test Date", "test_date");
        $output = ob_get_clean();

        // Should contain a row with label and date input
        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Test Date</td>", $output);
        $this->assertStringContainsString("<input type='text'", $output);
        $this->assertStringContainsString("name='test_date'", $output);
        $this->assertStringContainsString("value='2023-12-25'", $output);
        $this->assertStringContainsString("</tr>", $output);

        // Clean up
        unset($_POST['test_date']);
    }

    public function testCheckRowProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::check_row("Test Check", "test_check", 1);
        $output = ob_get_clean();

        // Should contain a row with label and checkbox
        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Test Check</td>", $output);
        $this->assertStringContainsString("<input type='checkbox'", $output);
        $this->assertStringContainsString("name='test_check'", $output);
        $this->assertStringContainsString("checked", $output);
        $this->assertStringContainsString("</tr>", $output);
    }

    public function testTextareaRowProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::textarea_row("Test Textarea", "test_textarea", "default content", 40, 5);
        $output = ob_get_clean();

        // Should contain a row with label and textarea
        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Test Textarea</td>", $output);
        $this->assertStringContainsString("<textarea", $output);
        $this->assertStringContainsString("name='test_textarea'", $output);
        $this->assertStringContainsString("cols='40'", $output);
        $this->assertStringContainsString("rows='5'", $output);
        $this->assertStringContainsString("default content", $output);
        $this->assertStringContainsString("</textarea>", $output);
        $this->assertStringContainsString("</tr>", $output);
    }

    public function testPercentRowProducesCorrectStructure()
    {
        // Set up POST data
        $_POST['test_percent'] = '25.50';

        ob_start();
        TestFaUiFunctions::percent_row("Test Percent", "test_percent");
        $output = ob_get_clean();

        // Should contain a row with label and amount input with % post label
        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Test Percent</td>", $output);
        $this->assertStringContainsString("<input class='amount'", $output);
        $this->assertStringContainsString("name='test_percent'", $output);
        $this->assertStringContainsString("value='25.50'", $output);
        $this->assertStringContainsString("%", $output);
        $this->assertStringContainsString("</tr>", $output);

        // Clean up
        unset($_POST['test_percent']);
    }

    public function testPasswordRowProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::password_row("Test Password", "test_password", "secret123");
        $output = ob_get_clean();

        // Should contain a row with label and password input
        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Test Password</td>", $output);
        $this->assertStringContainsString("<input type='password'", $output);
        $this->assertStringContainsString("name='test_password'", $output);
        $this->assertStringContainsString("value='secret123'", $output);
        $this->assertStringContainsString("</tr>", $output);
    }

    public function testTableSectionTitleProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::table_section_title("Test Section", 3);
        $output = ob_get_clean();

        $this->assertStringContainsString("<tr><td colspan='3' class='tableheader'>Test Section</td></tr>", $output);
    }

    public function testAltTableRowColorAlternatesCorrectly()
    {
        $k = 0; // Start with even row

        ob_start();
        TestFaUiFunctions::alt_table_row_color($k);
        TestFaUiFunctions::label_cell("Row 1");
        TestFaUiFunctions::end_row();

        TestFaUiFunctions::alt_table_row_color($k);
        TestFaUiFunctions::label_cell("Row 2");
        TestFaUiFunctions::end_row();
        $output = ob_get_clean();

        // First row should be evenrow, second should be oddrow
        $this->assertStringContainsString("<tr class='evenrow'>", $output);
        $this->assertStringContainsString("<tr class='oddrow'>", $output);
        $this->assertStringContainsString("Row 1", $output);
        $this->assertStringContainsString("Row 2", $output);
    }
}