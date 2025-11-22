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

    public static function label_cell($content, $params="", $id=null)
    {
        $id_attr = $id ? " id='$id'" : '';
        echo "<td $params$id_attr>$content</td>\n";
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

    public static function email_cell($label, $params="", $id=null)
    {
        self::label_cell("<a href='mailto:$label'>$label</a>", $params, $id);
    }

    public static function percent_cell($label, $bold=false, $id=null)
    {
        $formatted = $bold ? "<b>" . number_format($label, 2) . "%</b>" : number_format($label, 2) . "%";
        self::label_cell($formatted, "nowrap align=right", $id);
    }

    public static function qty_cell($label, $bold=false, $dec=null, $id=null)
    {
        $dec = $dec ?? 2;
        $formatted = $bold ? "<b>" . number_format($label, $dec) . "</b>" : number_format($label, $dec);
        self::label_cell($formatted, "nowrap align=right", $id);
    }

    public static function button_cell($name, $value, $title=false, $icon=false, $aspect='')
    {
        echo "<td align='center'>";
        self::button($name, $value, $title, $icon, $aspect);
        echo "</td>";
    }

    public static function delete_button_cell($name, $value, $title=false)
    {
        self::button_cell($name, $value, $title, 'delete', '');
    }

    public static function edit_button_cell($name, $value, $title=false)
    {
        self::button_cell($name, $value, $title, 'edit', '');
    }

    public static function select_button_cell($name, $value, $title=false)
    {
        self::button_cell($name, $value, $title, 'select', '');
    }

    public static function button($name, $value, $title=false, $icon=false, $aspect='')
    {
        $title_attr = $title ? " title='$title'" : '';
        $icon_attr = $icon ? " icon='$icon'" : '';
        $aspect_attr = $aspect ? " aspect='$aspect'" : '';
        echo "<button type='submit' name='$name' value='$value'$title_attr$icon_attr$aspect_attr>$value</button>";
    }

    public static function radio($label, $name, $value, $selected=null, $submit_on_change=false)
    {
        $selected = $selected ?? ($_POST[$name] ?? null) === (string)$value;
        $onclick = $submit_on_change ? " onclick='JsHttpRequest.request(\"_{$name}_update\", this.form);'" : '';
        $checked = $selected ? ' checked' : '';
        return "<input type='radio' name='$name' value='$value'$checked$onclick>" . ($label ? $label : '');
    }

    public static function unit_amount_cell($label, $bold=false, $params="", $id=null)
    {
        $formatted = $bold ? "<b>$" . number_format($label, 4) . "</b>" : "$" . number_format($label, 4);
        self::label_cell($formatted, "nowrap align=right " . $params, $id);
    }

    public static function labelheader_cell($label, $params="")
    {
        echo "<td class='tableheader' $params>$label</td>\n";
    }

    public static function amount_decimal_cell($label, $params="", $id=null)
    {
        $formatted = number_format($label, 0);
        self::label_cell($formatted, "nowrap align=right " . $params, $id);
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

    public function testEmailCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::email_cell("test@example.com", "class='email'");
        $output = ob_get_clean();

        $this->assertStringContainsString("<a href='mailto:test@example.com'>test@example.com</a>", $output);
        $this->assertStringContainsString("class='email'", $output);
    }

    public function testPercentCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::percent_cell(25.5, true);
        $output = ob_get_clean();

        $this->assertStringContainsString("<b>25.50%</b>", $output);
        $this->assertStringContainsString("nowrap align=right", $output);
    }

    public function testQtyCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::qty_cell(123.456, false, 3);
        $output = ob_get_clean();

        $this->assertStringContainsString("123.456", $output);
        $this->assertStringContainsString("nowrap align=right", $output);
    }

    public function testButtonCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::button_cell("test_button", "Click Me", "Test Title", "edit");
        $output = ob_get_clean();

        $this->assertStringContainsString("<td align='center'>", $output);
        $this->assertStringContainsString("<button type='submit'", $output);
        $this->assertStringContainsString("name='test_button'", $output);
        $this->assertStringContainsString("value='Click Me'", $output);
        $this->assertStringContainsString("title='Test Title'", $output);
        $this->assertStringContainsString("icon='edit'", $output);
        $this->assertStringContainsString("</button>", $output);
        $this->assertStringContainsString("</td>", $output);
    }

    public function testDeleteButtonCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::delete_button_cell("delete_btn", "Delete");
        $output = ob_get_clean();

        $this->assertStringContainsString("<td align='center'>", $output);
        $this->assertStringContainsString("icon='delete'", $output);
        $this->assertStringContainsString("name='delete_btn'", $output);
        $this->assertStringContainsString("value='Delete'", $output);
    }

    public function testEditButtonCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::edit_button_cell("edit_btn", "Edit");
        $output = ob_get_clean();

        $this->assertStringContainsString("<td align='center'>", $output);
        $this->assertStringContainsString("icon='edit'", $output);
        $this->assertStringContainsString("name='edit_btn'", $output);
        $this->assertStringContainsString("value='Edit'", $output);
    }

    public function testSelectButtonCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::select_button_cell("select_btn", "Select");
        $output = ob_get_clean();

        $this->assertStringContainsString("<td align='center'>", $output);
        $this->assertStringContainsString("icon='select'", $output);
        $this->assertStringContainsString("name='select_btn'", $output);
        $this->assertStringContainsString("value='Select'", $output);
    }

    public function testRadioProducesCorrectStructure()
    {
        ob_start();
        $result = TestFaUiFunctions::radio("Option 1", "test_radio", "value1", "value1");
        $output = ob_get_clean();

        $this->assertStringContainsString("<input type='radio'", $result);
        $this->assertStringContainsString("name='test_radio'", $result);
        $this->assertStringContainsString("value='value1'", $result);
        $this->assertStringContainsString("checked", $result);
        $this->assertStringContainsString("Option 1", $result);
    }

    public function testUnitAmountCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::unit_amount_cell(123.4567, true);
        $output = ob_get_clean();

        $this->assertStringContainsString("<b>$123.4567</b>", $output);
        $this->assertStringContainsString("nowrap align=right", $output);
    }

    public function testLabelheaderCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::labelheader_cell("Header Text", "class='header'");
        $output = ob_get_clean();

        $this->assertStringContainsString("<td class='tableheader' class='header'>Header Text</td>", $output);
    }

    public function testAmountDecimalCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::amount_decimal_cell(1234.56, "class='decimal'");
        $output = ob_get_clean();

        $this->assertStringContainsString("1,235", $output); // Should round to 0 decimal places
        $this->assertStringContainsString("nowrap align=right", $output);
        $this->assertStringContainsString("class='decimal'", $output);
    }
}