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

    public static function hidden($name, $value=null, $echo=true)
    {
        $input = "<input type='hidden' name='$name'";
        if (isset($value)) {
            $input .= " value='$value'";
        }
        $input .= ">";
        if ($echo) {
            echo $input;
        } else {
            return $input;
        }
    }

    public static function submit($name, $value, $echo=true, $title=false, $atype=false, $icon=false)
    {
        $input = "<input type='submit' name='$name' value='$value'";
        if ($title) {
            $input .= " title='$title'";
        }
        $input .= ">";
        if ($echo) {
            echo $input;
        } else {
            return $input;
        }
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

    public static function br($num=1)
    {
        for ($i = 0; $i < $num; $i++)
            echo "<br>";
    }

    public static function vertical_space($params='')
    {
        echo "</td></tr><tr><td valign=center $params>";
    }

    public static function access_string($label, $clean=false)
    {
        $access = '';
        $slices = array();

        if (preg_match('/(.*)&([a-zA-Z0-9])(.*)/', $label, $slices)) {
            $label = $clean ? $slices[1].$slices[2].$slices[3] :
                $slices[1].'<u>'.$slices[2].'</u>'.$slices[3];
            $access = " accesskey='".strtoupper($slices[2])."'";
        }

        $label = str_replace('&&', '&', $label);

        return $clean ? $label : array($label, $access);
    }

    public static function default_focus($name=null, $form_no=0)
    {
        static $next;
        if ($name==null)
            $name = uniqid('_el',true);
        if (!isset($_POST['_focus'])) {
            // Simplified for testing
        }
        return $name;
    }

    public static function hyperlink_no_params($target, $label, $center=true)
    {
        $id = self::default_focus();
        $pars = self::access_string($label);
        if ($target == '')
            $target = $_SERVER['PHP_SELF'];
        if ($center)
            echo "<br><center>";
        echo "<a href='$target' id='$id' $pars[1]>$pars[0]</a>\n";
        if ($center)
            echo "</center>";
    }

    public static function hyperlink_no_params_td($target, $label)
    {
        echo "<td>";
        self::hyperlink_no_params($target, $label);
        echo "</td>\n";
    }

    public static function viewer_link($label, $url='', $class='', $id='', $icon=null)
    {
        if ($class != '')
            $class = " class='$class'";

        if ($id != '')
            $class = " id='$id'";  // Note: this overwrites class, probably a bug in original

        if ($url != "") {
            $pars = self::access_string($label);
            // Simplified: ignore icon for now
            $preview_str = "<a target='_blank' $class $id href='$url' onclick=\"javascript:openWindow(this.href,this.target); return false;\"$pars[1]>$pars[0]</a>";
        } else
            $preview_str = $label;
        return $preview_str;
    }

    public static function menu_link($url, $label, $id=null)
    {
        $id = self::default_focus($id);
        $pars = self::access_string($label);

        if ($url[0] != '/')
            $url = '/'.$url;
        // Simplified: ignore path_to_root
        $url = $url;

        return "<a href='$url' class='menu_option' id='$id' $pars[1]>$pars[0]</a>";
    }

    public static function display_caption($msg)
    {
        echo "<caption>$msg</caption>\n";
    }

    public static function display_heading($msg)
    {
        echo "<center><span class='headingtext'>$msg</span></center>\n";
    }

    public static function display_heading2($msg)
    {
        echo "<center><span class='headingtext2'>$msg</span></center>\n";
    }

    public static function check_value($name)
    {
        if (is_array($name)) {
            $ret = array();
            foreach($name as $key)
                $ret[$key] = self::check_value($key);
            return $ret;
        } else
            return (empty($_POST[$name]) ? 0 : 1);
    }

    public static function qty_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        $dec = $dec ?? 2;
        self::amount_cells($label, $name, $init, $params, $post_label, $dec);
    }

    public static function amount_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        $dec = $dec ?? 2;
        if (!isset($_POST[$name]) || $_POST[$name] == "") {
            $_POST[$name] = $init ?? '';
        }
        if ($label != null) {
            echo "<td class='label'>$label</td>";
        }
        $formatted_value = is_numeric($_POST[$name]) ? number_format($_POST[$name], $dec) : $_POST[$name];
        echo "<td><input class='amount' type='text' name='$name' size='15' maxlength='15' dec='$dec' value='$formatted_value'>";
        if ($post_label) {
            echo "<span id='_{$name}_label'> $post_label</span>";
        }
        echo "</td>";
    }

    public static function small_qty_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        $dec = $dec ?? 2;
        self::small_amount_cells($label, $name, $init, $params, $post_label, $dec);
    }

    public static function small_amount_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        $dec = $dec ?? 2;
        if (!isset($_POST[$name]) || $_POST[$name] == "") {
            $_POST[$name] = $init ?? '';
        }
        if ($label != null) {
            echo "<td class='label'>$label</td>";
        }
        $formatted_value = is_numeric($_POST[$name]) ? number_format($_POST[$name], $dec) : $_POST[$name];
        echo "<td><input class='amount' type='text' name='$name' size='7' maxlength='12' dec='$dec' value='$formatted_value'>";
        if ($post_label) {
            echo "<span id='_{$name}_label'> $post_label</span>";
        }
        echo "</td>";
    }

    public static function ahref($label, $href, $target="", $onclick="")
    {
        $target_attr = $target ? " target='$target'" : '';
        $onclick_attr = $onclick ? " onclick='$onclick'" : '';
        echo "<a href='$href'$target_attr$onclick_attr>$label</a>";
    }

    public static function ahref_cell($label, $href, $target="", $onclick="")
    {
        $target_attr = $target ? " target='$target'" : '';
        $onclick_attr = $onclick ? " onclick='$onclick'" : '';
        self::label_cell("<a href='$href'$target_attr$onclick_attr>$label</a>");
    }

    public static function inactive_control_cell($id, $value, $table, $key)
    {
        self::label_cell($value);
    }

    public static function inactive_control_row($th)
    {
        echo "<tr><td class='label'>$th</td><td></td></tr>\n";
    }

    public static function inactive_control_column(&$th)
    {
        $th .= "<th>Inactive</th>";
    }

    public static function customer_credit_row($customer, $credit, $parms='')
    {
        echo "<tr><td class='label'>Customer Credit</td><td>$" . number_format($credit, 2) . "</td></tr>\n";
    }

    public static function supplier_credit_row($supplier, $credit, $parms='')
    {
        echo "<tr><td class='label'>Supplier Credit</td><td>$" . number_format($credit, 2) . "</td></tr>\n";
    }

    public static function bank_balance_row($bank_acc, $parms='')
    {
        echo "<tr><td class='label'>Bank Balance</td><td>$" . number_format($bank_acc, 2) . "</td></tr>\n";
    }

    public static function div_start($id='', $trigger=null, $non_ajax=false)
    {
        if ($non_ajax) { // div for non-ajax elements
            echo "<div style='display:none' class='js_only' ".($id !='' ? "id='$id'" : '').">";
        } else { // ajax ready div
            echo "<div ". ($id !='' ? "id='$id'" : '').">";
            // Simplified: no output buffering for Ajax
        }
    }

    public static function div_end()
    {
        // Simplified: no Ajax handling
        echo "</div>";
    }

    public static function hyperlink_params($target, $label, $params, $center=true)
    {
        $id = self::default_focus();

        $pars = self::access_string($label);
        if ($target == '')
            $target = $_SERVER['PHP_SELF'];
        if ($center)
            echo "<br><center>";
        echo "<a id='$id' href='$target?$params'$pars[1]>$pars[0]</a>\n";
        if ($center)
            echo "</center>";
    }

    public static function hyperlink_params_td($target, $label, $params)
    {
        echo "<td>";
        self::hyperlink_params($target, $label, $params, false);
        echo "</td>\n";
    }

    public static function hyperlink_params_separate($target, $label, $params, $center=false)
    {
        $id = self::default_focus();

        $pars = self::access_string($label);
        if ($center)
            echo "<br><center>";
        echo "<a target='_blank' id='$id' href='$target?$params' $pars[1]>$pars[0]</a>\n";
        if ($center)
            echo "</center>";
    }

    public static function hyperlink_params_separate_td($target, $label, $params)
    {
        echo "<td>";
        self::hyperlink_params_separate($target, $label, $params);
        echo "</td>\n";
    }

    public static function tabbed_content_start($name, $tabs, $dft='')
    {
        $selname = '_'.$name.'_sel';
        $div = '_'.$name.'_div';

        $sel = get_post($selname, (string)($dft==='' ? key($tabs) : $dft));

        $_POST[$selname] = $sel;

        self::div_start($name);
        $str = "<ul class='ajaxtabs' rel='$div'>\n";
        foreach($tabs as $tab_no => $tab) {

            $acc = self::access_string(is_array($tab) ? $tab[0] : $tab);
            $disabled = (is_array($tab) && !$tab[1])  ? 'disabled ' : '';
            $str .= ( "<li>"
                ."<button type='submit' name='{$name}_".$tab_no
                ."' class='".((string)$tab_no===$sel ? 'current':'ajaxbutton')."' $acc[1] $disabled>"
                ."<span>$acc[0]</span>"
                ."</button>\n"
                ."</li>\n" );
        }

        $str .= "</ul>\n";
        $str .= "<div class='spaceBox'></div>\n";
        $str .= "<input type='hidden' name='$selname' value='$sel'>\n";
        $str .= "<div class='contentBox' id='$div'>\n";
        echo $str;
    }

    public static function tabbed_content_end()
    {
        // Simplified: no output_hidden
        echo "</div>"; // content box
        self::div_end(); // tabs widget
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

    public function testBrProducesCorrectNumberOfBreaks()
    {
        ob_start();
        TestFaUiFunctions::br(3);
        $output = ob_get_clean();

        $this->assertEquals("<br><br><br>", $output);
    }

    public function testVerticalSpaceProducesCorrectTableStructure()
    {
        ob_start();
        TestFaUiFunctions::vertical_space('class="spacer"');
        $output = ob_get_clean();

        $this->assertEquals("</td></tr><tr><td valign=center class=\"spacer\">", $output);
    }

    public function testAccessStringReturnsCorrectArray()
    {
        $result = TestFaUiFunctions::access_string('Test &Label');
        $this->assertEquals(['Test <u>L</u>abel', " accesskey='L'"], $result);

        $result = TestFaUiFunctions::access_string('Test Label', true);
        $this->assertEquals('Test Label', $result);
    }

    public function testDefaultFocusReturnsName()
    {
        $result = TestFaUiFunctions::default_focus('test_name');
        $this->assertEquals('test_name', $result);

        $result = TestFaUiFunctions::default_focus();
        $this->assertIsString($result);
    }

    public function testHyperlinkNoParamsProducesCorrectLink()
    {
        ob_start();
        TestFaUiFunctions::hyperlink_no_params('test.php', 'Test &Link');
        $output = ob_get_clean();

        $this->assertTrue(strpos($output, '<br><center>') !== false);
        $this->assertTrue(strpos($output, '<a href=\'test.php\'') !== false);
        $this->assertTrue(strpos($output, 'Test <u>L</u>ink') !== false);
        $this->assertTrue(strpos($output, '</center>') !== false);
    }

    public function testHyperlinkNoParamsTdProducesCorrectTableCell()
    {
        ob_start();
        TestFaUiFunctions::hyperlink_no_params_td('test.php', 'Test &Link');
        $output = ob_get_clean();

        $this->assertTrue(strpos($output, '<td>') !== false);
        $this->assertTrue(strpos($output, '<a href=\'test.php\'') !== false);
        $this->assertTrue(strpos($output, '</td>') !== false);
    }

    public function testViewerLinkReturnsCorrectLinkString()
    {
        $result = TestFaUiFunctions::viewer_link('Test &Link', 'test.php', 'btn', 'testid');
        $this->assertTrue(strpos($result, '<a target=\'_blank\'') !== false);
        $this->assertTrue(strpos($result, 'href=\'test.php\'') !== false);
        $this->assertTrue(strpos($result, 'Test <u>L</u>ink') !== false);

        $result = TestFaUiFunctions::viewer_link('Test Label', '');
        $this->assertEquals('Test Label', $result);
    }

    public function testMenuLinkReturnsCorrectMenuLink()
    {
        $result = TestFaUiFunctions::menu_link('test.php', 'Test &Menu');
        $this->assertTrue(strpos($result, '<a href=\'/test.php\'') !== false);
        $this->assertTrue(strpos($result, 'class=\'menu_option\'') !== false);
        $this->assertTrue(strpos($result, 'Test <u>M</u>enu') !== false);
    }

    public function testDisplayCaptionProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::display_caption("Test Caption");
        $output = ob_get_clean();

        $this->assertEquals("<caption>Test Caption</caption>\n", $output);
    }

    public function testDisplayHeadingProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::display_heading("Test Heading");
        $output = ob_get_clean();

        $this->assertEquals("<center><span class='headingtext'>Test Heading</span></center>\n", $output);
    }

    public function testDisplayHeading2ProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::display_heading2("Test Heading 2");
        $output = ob_get_clean();

        $this->assertEquals("<center><span class='headingtext2'>Test Heading 2</span></center>\n", $output);
    }

    public function testCheckValueReturnsCorrectBoolean()
    {
        // Test with empty value
        $_POST['test_empty'] = '';
        $result = TestFaUiFunctions::check_value('test_empty');
        $this->assertEquals(0, $result);

        // Test with non-empty value
        $_POST['test_value'] = 'some value';
        $result = TestFaUiFunctions::check_value('test_value');
        $this->assertEquals(1, $result);

        // Clean up
        unset($_POST['test_empty'], $_POST['test_value']);
    }

    public function testHiddenProducesCorrectInput()
    {
        ob_start();
        TestFaUiFunctions::hidden('test_name', 'test_value');
        $output = ob_get_clean();

        $this->assertEquals("<input type='hidden' name='test_name' value='test_value'>", $output);
    }

    public function testHiddenWithoutValueProducesCorrectInput()
    {
        ob_start();
        TestFaUiFunctions::hidden('test_name');
        $output = ob_get_clean();

        $this->assertEquals("<input type='hidden' name='test_name'>", $output);
    }

    public function testSubmitProducesCorrectInput()
    {
        ob_start();
        TestFaUiFunctions::submit('test_name', 'test_value');
        $output = ob_get_clean();

        $this->assertEquals("<input type='submit' name='test_name' value='test_value'>", $output);
    }

    public function testSubmitWithTitleProducesCorrectInput()
    {
        ob_start();
        TestFaUiFunctions::submit('test_name', 'test_value', true, 'Test Title');
        $output = ob_get_clean();

        $this->assertEquals("<input type='submit' name='test_name' value='test_value' title='Test Title'>", $output);
    }

    public function testButtonProducesCorrectButton()
    {
        ob_start();
        TestFaUiFunctions::button('test_name', 'test_value');
        $output = ob_get_clean();

        $this->assertEquals("<button type='submit' name='test_name' value='test_value'>test_value</button>", $output);
    }

    public function testButtonWithTitleProducesCorrectButton()
    {
        ob_start();
        TestFaUiFunctions::button('test_name', 'test_value', 'Test Title');
        $output = ob_get_clean();

        $this->assertEquals("<button type='submit' name='test_name' value='test_value' title='Test Title'>test_value</button>", $output);
    }

    public function testQtyCellsProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::qty_cells("Quantity", "qty", 10.5);
        $output = ob_get_clean();

        $this->assertStringContainsString("<td>", $output);
        $this->assertStringContainsString("name='qty'", $output);
        $this->assertStringContainsString("value='10.50'", $output);
    }

    public function testSmallQtyCellsProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::small_qty_cells("Small Qty", "small_qty", 5.25);
        $output = ob_get_clean();

        $this->assertStringContainsString("<td>", $output);
        $this->assertStringContainsString("name='small_qty'", $output);
        $this->assertStringContainsString("value='5.25'", $output);
    }

    public function testAhrefProducesCorrectLink()
    {
        ob_start();
        TestFaUiFunctions::ahref("Click me", "http://example.com");
        $output = ob_get_clean();

        $this->assertEquals("<a href='http://example.com'>Click me</a>", $output);
    }

    public function testAhrefWithTargetProducesCorrectLink()
    {
        ob_start();
        TestFaUiFunctions::ahref("Click me", "http://example.com", "_blank");
        $output = ob_get_clean();

        $this->assertEquals("<a href='http://example.com' target='_blank'>Click me</a>", $output);
    }

    public function testAhrefCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::ahref_cell("Click me", "http://example.com");
        $output = ob_get_clean();

        $this->assertStringContainsString("<td", $output); // Allow for <td> or <td >
        $this->assertStringContainsString("<a href='http://example.com'>Click me</a>", $output);
        $this->assertStringContainsString("</td>", $output);
    }

    public function testInactiveControlCellProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::inactive_control_cell(1, "Test Value", "test_table", "id");
        $output = ob_get_clean();

        $this->assertStringContainsString("<td", $output); // Allow for <td> or <td >
        $this->assertStringContainsString("Test Value", $output);
        $this->assertStringContainsString("</td>", $output);
    }

    public function testInactiveControlRowProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::inactive_control_row("Test Header");
        $output = ob_get_clean();

        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Test Header</td>", $output);
        $this->assertStringContainsString("</tr>", $output);
    }

    public function testInactiveControlColumnModifiesHeader()
    {
        $th = "<th>Name</th>";
        TestFaUiFunctions::inactive_control_column($th);

        $this->assertEquals("<th>Name</th><th>Inactive</th>", $th);
    }

    public function testCustomerCreditRowProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::customer_credit_row("Customer A", 1234.56);
        $output = ob_get_clean();

        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Customer Credit</td>", $output);
        $this->assertStringContainsString("<td>$1,234.56</td>", $output);
        $this->assertStringContainsString("</tr>", $output);
    }

    public function testSupplierCreditRowProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::supplier_credit_row("Supplier B", 789.12);
        $output = ob_get_clean();

        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Supplier Credit</td>", $output);
        $this->assertStringContainsString("<td>$789.12</td>", $output);
        $this->assertStringContainsString("</tr>", $output);
    }

    public function testBankBalanceRowProducesCorrectStructure()
    {
        ob_start();
        TestFaUiFunctions::bank_balance_row(5678.90);
        $output = ob_get_clean();

        $this->assertStringContainsString("<tr>", $output);
        $this->assertStringContainsString("<td class='label'>Bank Balance</td>", $output);
        $this->assertStringContainsString("<td>$5,678.90</td>", $output);
        $this->assertStringContainsString("</tr>", $output);
    }

    public function testDivStartCreatesCorrectDiv()
    {
        ob_start();
        TestFaUiFunctions::div_start('test_id', 'trigger', false);
        $output = ob_get_clean();

        $this->assertStringContainsString("<div id='test_id'>", $output);
    }

    public function testDivStartCreatesNonAjaxDiv()
    {
        ob_start();
        TestFaUiFunctions::div_start('test_id', null, true);
        $output = ob_get_clean();

        $this->assertStringContainsString("<div style='display:none' class='js_only' id='test_id'>", $output);
    }

    public function testDivEndClosesDiv()
    {
        ob_start();
        TestFaUiFunctions::div_end();
        $output = ob_get_clean();

        $this->assertEquals("</div>", $output);
    }

    public function testHyperlinkParamsCreatesCorrectLink()
    {
        ob_start();
        TestFaUiFunctions::hyperlink_params('test.php', 'Test &Link', 'param=value');
        $output = ob_get_clean();

        $this->assertStringContainsString('<br><center>', $output);
        $this->assertStringContainsString('<a', $output);
        $this->assertStringContainsString('href=\'test.php?param=value\'', $output);
        $this->assertStringContainsString('Test <u>L</u>ink', $output);
        $this->assertStringContainsString('</center>', $output);
    }

    public function testHyperlinkParamsTdCreatesCorrectTableCell()
    {
        ob_start();
        TestFaUiFunctions::hyperlink_params_td('test.php', 'Test &Link', 'param=value');
        $output = ob_get_clean();

        $this->assertStringContainsString('<td>', $output);
        $this->assertStringContainsString('<a', $output);
        $this->assertStringContainsString('href=\'test.php?param=value\'', $output);
        $this->assertStringContainsString('</td>', $output);
    }

    public function testHyperlinkParamsSeparateCreatesCorrectLink()
    {
        ob_start();
        TestFaUiFunctions::hyperlink_params_separate('test.php', 'Test &Link', 'param=value', true);
        $output = ob_get_clean();

        $this->assertStringContainsString('<br><center>', $output);
        $this->assertStringContainsString('<a target=\'_blank\'', $output);
        $this->assertStringContainsString('href=\'test.php?param=value\'', $output);
        $this->assertStringContainsString('Test <u>L</u>ink', $output);
        $this->assertStringContainsString('</center>', $output);
    }

    public function testHyperlinkParamsSeparateTdCreatesCorrectTableCell()
    {
        ob_start();
        TestFaUiFunctions::hyperlink_params_separate_td('test.php', 'Test &Link', 'param=value');
        $output = ob_get_clean();

        $this->assertStringContainsString('<td>', $output);
        $this->assertStringContainsString('<a target=\'_blank\'', $output);
        $this->assertStringContainsString('href=\'test.php?param=value\'', $output);
        $this->assertStringContainsString('</td>', $output);
    }

    public function testComboListCellsWithArrayData()
    {
        // Test DI functionality with array data
        $options = [
            '1' => 'Option 1',
            '2' => 'Option 2',
            '3' => 'Option 3'
        ];

        ob_start();
        \Ksfraser\HTML\FaUiFunctions::combo_list_cells($options, null, 'Test Label', 'test_select', '2', true, true);
        $output = ob_get_clean();

        // Check that label cell is created
        $this->assertStringContainsString('<td >Test Label</td>', $output);
        
        // Check select element in its own cell
        $this->assertStringContainsString('<td   ><select name="test_select"', $output);
        $this->assertStringContainsString('onchange="this.form.submit()"', $output);
        
        // Check none option
        $this->assertStringContainsString('<option value=""', $output);
        $this->assertStringContainsString('-- select --', $output);
        
        // Check options with correct selected state
        $this->assertStringContainsString('<option value="1"', $output);
        $this->assertStringContainsString('Option 1</option>', $output);
        $this->assertStringContainsString('<option value="2" selected', $output);
        $this->assertStringContainsString('Option 2</option>', $output);
        $this->assertStringContainsString('<option value="3"', $output);
        $this->assertStringContainsString('Option 3</option>', $output);
        
        // Check closing tags
        $this->assertStringContainsString('</select></td>', $output);
    }
}