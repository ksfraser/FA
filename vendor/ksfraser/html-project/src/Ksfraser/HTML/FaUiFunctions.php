<?php

namespace Ksfraser\HTML;

// Conditionally load FA UI functions only when needed
$faLoaded = false;

function loadFaFunctions() {
    global $faLoaded;
    if (!$faLoaded) {
        if (!defined('FA_ROOT')) {
            define('FA_ROOT', realpath(__DIR__ . '/../../../..'));
        }
        require_once(FA_ROOT . "/includes/ui/ui_input.inc");
        require_once(FA_ROOT . "/includes/ui/ui_lists.inc");
        require_once(FA_ROOT . "/includes/ui/ui_controls.inc");
        $faLoaded = true;
    }
}

/**
 * Facade for Front Accounting UI functions
 * This allows us to decouple our HTML components from FA's UI functions
 */
class FaUiFunctions {
    const TABLESTYLE2 = 2; // Matching FA's constant

    public static function label_row($label, $content, $params="")
    {
        // Check for function in global namespace
        if (function_exists('\\label_row')) {
            call_user_func('\\label_row', $label, $content, $params);
        } else {
            $row = new \Ksfraser\HTML\Elements\HtmlTableRow(new \Ksfraser\HTML\Elements\HtmlString(''));
            
            $labelCell = new \Ksfraser\HTML\Elements\HtmlTableRowCell(new \Ksfraser\HTML\Elements\HtmlString($label));
            $labelCell->addAttribute(new \Ksfraser\HTML\HtmlAttribute('class', 'label'));
            $row->addNested($labelCell);
            
            $contentCell = new \Ksfraser\HTML\Elements\HtmlTableRowCell(new \Ksfraser\HTML\Elements\HtmlString($content));
            if ($params) {
                // Parse params
                $attrs = explode(' ', trim($params));
                foreach ($attrs as $attr) {
                    if (strpos($attr, '=') !== false) {
                        list($key, $value) = explode('=', $attr, 2);
                        $value = trim($value, "'\"");
                        $contentCell->addAttribute(new \Ksfraser\HTML\HtmlAttribute($key, $value));
                    }
                }
            }
            $row->addNested($contentCell);
            
            $row->toHtml();
        }
    }

    public static function start_table($type = self::TABLESTYLE2, $params="")
    {
        if (function_exists('\\start_table')) {
            call_user_func('\\start_table', $type, $params);
        } else {
            echo "<table class='tablestyle$type' $params>\n";
        }
    }

    public static function end_table($breaks=0)
    {
        if (function_exists('\\end_table')) {
            call_user_func('\\end_table', $breaks);
        } else {
            echo "</table>\n";
        }
    }

    public static function table_header($labels, $params='')
    {
        if (function_exists('\\table_header')) {
            call_user_func('\\table_header', $labels, $params);
        } else {
            $row = new \Ksfraser\HTML\Elements\HtmlTableRow(new \Ksfraser\HTML\Elements\HtmlString(''));
            foreach ($labels as $label) {
                $th = new \Ksfraser\HTML\Elements\HtmlTableHeaderCell(new \Ksfraser\HTML\Elements\HtmlString($label));
                if ($params) {
                    // Parse params and add attributes
                    $attrs = explode(' ', trim($params));
                    foreach ($attrs as $attr) {
                        if (strpos($attr, '=') !== false) {
                            list($key, $value) = explode('=', $attr, 2);
                            $value = trim($value, "'\"");
                            $th->addAttribute(new \Ksfraser\HTML\HtmlAttribute($key, $value));
                        }
                    }
                }
                $row->addNested($th);
            }
            $row->toHtml();
        }
    }

    public static function start_row($param="")
    {
        if (function_exists('\\start_row')) {
            call_user_func('\\start_row', $param);
        } else {
            echo "<tr $param>\n";
        }
    }

    public static function end_row()
    {
        if (function_exists('\\end_row')) {
            call_user_func('\\end_row');
        } else {
            echo "</tr>\n";
        }
    }

    public static function label_cells($label, $value, $params="", $params2="", $id=null)
    {
        if (function_exists('\\label_cells')) {
            call_user_func('\\label_cells', $label, $value, $params, $params2, $id);
        } else {
            // OOP implementation
            if ($label != null) {
                $labelCell = new \Ksfraser\HTML\Elements\FaCell($label, $params);
                $labelCell->toHtml();
            }
            
            // Handle Ajax updates if $id is set (simplified for now)
            $valueParams = $params2;
            if (isset($id)) {
                $valueParams .= " id='$id'";
            }
            
            $valueCell = new \Ksfraser\HTML\Elements\FaCell($value, $valueParams);
            $valueCell->toHtml();
        }
    }

    public static function label_cell($label, $params="", $id=null)
    {
        if (function_exists('\\label_cell')) {
            call_user_func('\\label_cell', $label, $params, $id);
        } else {
            // OOP implementation
            $cellParams = $params;
            $ajaxValue = is_string($label) ? $label : (is_object($label) && method_exists($label, 'getHtml') ? $label->getHtml() : (string)$label);
            if (isset($id)) {
                $cellParams .= " id='$id'";
                // Add Ajax update functionality
                if (isset($GLOBALS['Ajax'])) {
                    $GLOBALS['Ajax']->addUpdate($id, $id, $ajaxValue);
                }
            }
            $cell = new \Ksfraser\HTML\Elements\FaCell($label, $cellParams);
            $cell->toHtml();
        }
    }

    public static function number_list_cells($label, $name, $selected, $from, $to, $no_option=false)
    {
        if (function_exists('\\number_list_cells')) {
            call_user_func('\\number_list_cells', $label, $name, $selected, $from, $to, $no_option);
        } else {
            // OOP implementation
            if ($label != null) {
                self::label_cell($label);
            }
            // Get the number list HTML
            $numberListHtml = '';
            if (function_exists('\\number_list')) {
                $numberListHtml = call_user_func('\\number_list', $name, $selected, $from, $to, $no_option);
            } else {
                // Fallback: simple select using HTML element classes
                $select = new \Ksfraser\HTML\Elements\HtmlSelect($name);
                if ($no_option) {
                    $option = new \Ksfraser\HTML\Elements\HtmlOption('', '--');
                    $select->addOption($option);
                }
                for ($i = $from; $i <= $to; $i++) {
                    $option = new \Ksfraser\HTML\Elements\HtmlOption((string)$i, (string)$i);
                    if ($selected == $i) {
                        $option->setSelected(true);
                    }
                    $select->addOption($option);
                }
                // Get HTML string
                ob_start();
                $select->toHtml();
                $numberListHtml = ob_get_clean();
            }
            $cell = new \Ksfraser\HTML\Elements\FaCell($numberListHtml);
            $cell->toHtml();
        }
    }

    public static function check_cells($label, $name, $value=null, $submit_on_change=false, $title=false, $params='')
    {
        if (function_exists('\\check_cells')) {
            call_user_func('\\check_cells', $label, $name, $value, $submit_on_change, $title, $params);
        } else {
            // OOP implementation
            if ($label != null) {
                self::label_cell($label);
            }
            // Get the check HTML
            $checkHtml = '';
            if (function_exists('\\check')) {
                $checkHtml = call_user_func('\\check', null, $name, $value, $submit_on_change, $title);
            } else {
                // Fallback: simple checkbox using HTML element classes
                $checkbox = new \Ksfraser\HTML\Elements\HtmlInput('checkbox');
                $checkbox->setName($name);
                if ($value) {
                    $checkbox->addAttribute(new \Ksfraser\HTML\HtmlAttribute('checked', 'checked'));
                }
                // Get HTML string
                ob_start();
                $checkbox->toHtml();
                $checkHtml = ob_get_clean();
            }
            $cell = new \Ksfraser\HTML\Elements\FaCell($checkHtml, $params);
            $cell->toHtml();
        }
    }

    public static function submit_cells($name, $value, $extra="", $title=false, $async=false)
    {
        if (function_exists('\\submit_cells')) {
            call_user_func('\\submit_cells', $name, $value, $extra, $title, $async);
        } else {
            // OOP implementation
            // Get the submit HTML
            $submitHtml = '';
            if (function_exists('\\submit')) {
                $submitOb = \Ksfraser\HTML\Elements\HtmlOB::capture(function() use ($name, $value, $title, $async) {
                    call_user_func('\\submit', $name, $value, true, $title, $async);
                });
                $submitHtml = $submitOb->getHtml();
            } else {
                // Fallback: simple button
                $submitHtml = "<input type='submit' name='$name' value='$value'>";
            }
            $cell = new \Ksfraser\HTML\Elements\FaCell($submitHtml, $extra);
            $cell->toHtml();
        }
    }

    public static function text_cells($label, $name, $value=null, $size="", $max="", $title=false, $labparams="", $post_label="", $inparams="")
    {
        if (function_exists('\\text_cells')) {
            call_user_func('\\text_cells', $label, $name, $value, $size, $max, $title, $labparams, $post_label, $inparams);
        } else {
            // OOP implementation
            if (function_exists('\\default_focus')) {
                call_user_func('\\default_focus', $name);
            }
            if ($label != null) {
                self::label_cell($label, $labparams);
            }
            // Get the text input HTML
            $textHtml = '';
            if (function_exists('\\text_input')) {
                $textHtml = call_user_func('\\text_input', $name, $value, $size, $max, $title, $inparams);
            } else {
                // Fallback: simple input using HTML element classes
                $input = new \Ksfraser\HTML\Elements\HtmlInput('text');
                $input->setName($name);
                $input->setValue($value);
                $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('size', $size));
                $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('maxlength', $max));
                if ($title) {
                    $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('title', $title));
                }
                // Get HTML string
                ob_start();
                $input->toHtml();
                $textHtml = ob_get_clean();
            }
            if ($post_label != "") {
                $textHtml .= " " . $post_label;
            }
            $cell = new \Ksfraser\HTML\Elements\FaCell($textHtml);
            $cell->toHtml();
            
            // Add Ajax update functionality
            if (isset($GLOBALS['Ajax'])) {
                $GLOBALS['Ajax']->addUpdate($name, $name, $value);
            }
        }
    }

    public static function file_cells($label, $name, $id="")
    {
        if (function_exists('\\file_cells')) {
            call_user_func('\\file_cells', $label, $name, $id);
        } else {
            // OOP implementation using HTML element classes
            $fileInput = new \Ksfraser\HTML\Elements\HtmlInput('file');
            $fileInput->setName($name);
            if ($id != "") {
                $fileInput->addAttribute(new \Ksfraser\HTML\HtmlAttribute('id', $id));
            }
            // Get HTML string
            ob_start();
            $fileInput->toHtml();
            $fileHtml = ob_get_clean();
            self::label_cells($label, $fileHtml);
        }
    }

    public static function amount_cells_ex($label, $name, $size, $max=null, $init=null, $params=null, $post_label=null, $dec=null)
    {
        if (function_exists('\\amount_cells_ex')) {
            call_user_func('\\amount_cells_ex', $label, $name, $size, $max, $init, $params, $post_label, $dec);
        } else {
            // OOP implementation
            if (!isset($dec)) {
                if (function_exists('\\user_price_dec')) {
                    $dec = call_user_func('\\user_price_dec');
                } else {
                    $dec = 2; // fallback
                }
            }
            if (!isset($_POST[$name]) || $_POST[$name] == "") {
                if ($init !== null) {
                    $_POST[$name] = $init;
                } else {
                    $_POST[$name] = '';
                }
            }
            if ($label != null) {
                if ($params == null) {
                    $params = "class='label'";
                }
                self::label_cell($label, $params);
            }
            if (!isset($max)) {
                $max = $size;
            }
            $input = new \Ksfraser\HTML\Elements\HtmlInput('text');
            $input->setName($name);
            $input->setValue($_POST[$name]);
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('class', 'amount'));
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('size', $size));
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('maxlength', $max));
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('dec', $dec));
            // Get HTML string
            ob_start();
            $input->toHtml();
            $inputHtml = ob_get_clean();
            if ($post_label) {
                $span = new \Ksfraser\HTML\Elements\HtmlSpan(new \Ksfraser\HTML\Elements\HtmlString(" $post_label"));
                $span->addAttribute(new \Ksfraser\HTML\HtmlAttribute('id', "_{$name}_label"));
                ob_start();
                $span->toHtml();
                $spanHtml = ob_get_clean();
                $inputHtml .= $spanHtml;
            }
            $cellParams = ($label != null) ? "" : "align='right'";
            $cell = new \Ksfraser\HTML\Elements\FaCell($inputHtml, $cellParams);
            $cell->toHtml();
            
            // Add Ajax update functionality
            if (isset($GLOBALS['Ajax'])) {
                if ($post_label) {
                    $GLOBALS['Ajax']->addUpdate($name, '_'.$name.'_label', $post_label);
                }
                $GLOBALS['Ajax']->addUpdate($name, $name, $_POST[$name]);
                $GLOBALS['Ajax']->addAssign($name, $name, 'dec', $dec);
            }
        }
    }

    public static function amount_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        self::amount_cells_ex($label, $name, 15, 15, $init, $params, $post_label, $dec);
    }

    public static function unit_amount_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        if (!isset($dec)) {
            if (function_exists('\\user_price_dec')) {
                $dec = call_user_func('\\user_price_dec') + 2;
            } else {
                $dec = 4; // fallback
            }
        }
        self::amount_cells_ex($label, $name, 15, 15, $init, $params, $post_label, $dec + 2);
    }

    public static function text_row($label, $name, $value, $size, $max, $title=null, $params="", $post_label="")
    {
        if (function_exists('\\text_row')) {
            call_user_func('\\text_row', $label, $name, $value, $size, $max, $title, $params, $post_label);
        } else {
            // OOP implementation
            self::start_row();
            self::label_cell($label, "class='label'");
            self::text_cells(null, $name, $value, $size, $max, $title, $params, $post_label);
            self::end_row();
        }
    }

    public static function amount_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        if (function_exists('\\amount_row')) {
            call_user_func('\\amount_row', $label, $name, $init, $params, $post_label, $dec);
        } else {
            // OOP implementation
            self::start_row();
            self::amount_cells($label, $name, $init, $params, $post_label, $dec);
            self::end_row();
        }
    }

    public static function date_cells($label, $name, $title = null, $check=null, $inc_days=0, $inc_months=0, $inc_years=0, $params=null, $submit_on_change=false)
    {
        if (function_exists('\\date_cells')) {
            call_user_func('\\date_cells', $label, $name, $title, $check, $inc_days, $inc_months, $inc_years, $params, $submit_on_change);
        } else {
            // OOP implementation - simplified fallback
            if (!isset($_POST[$name]) || $_POST[$name] == "") {
                if ($inc_years == 1001) {
                    $_POST[$name] = null;
                } else {
                    // Simplified date calculation
                    $dd = date('Y-m-d');
                    if ($inc_days != 0) {
                        $dd = date('Y-m-d', strtotime("$dd + $inc_days days"));
                    }
                    if ($inc_months != 0) {
                        $dd = date('Y-m-d', strtotime("$dd + $inc_months months"));
                    }
                    if ($inc_years != 0) {
                        $dd = date('Y-m-d', strtotime("$dd + $inc_years years"));
                    }
                    $_POST[$name] = $dd;
                }
            }

            $post_label = ""; // Simplified, no date picker in fallback

            if ($label != null) {
                self::label_cell($label, $params);
            }

            $class = $submit_on_change ? 'date active' : 'date';
            $aspect = $check ? 'aspect="cdate"' : '';
            if ($check && ($_POST[$name] != date('Y-m-d'))) {
                $aspect .= ' style="color:#FF0000"';
            }

            if (function_exists('\\default_focus')) {
                call_user_func('\\default_focus', $name);
            }

            $size = 10; // Simplified
            $input = new \Ksfraser\HTML\Elements\HtmlInput('text');
            $input->setName($name);
            $input->setValue($_POST[$name]);
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('class', $class));
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('size', $size));
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('maxlength', '12'));
            if ($title) {
                $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('title', $title));
            }
            if ($aspect) {
                // aspect is not a standard attribute, but FA specific
                $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('aspect', trim($aspect, '"')));
            }
            // Get HTML string
            ob_start();
            $input->toHtml();
            $inputHtml = ob_get_clean();
            $inputHtml .= " $post_label";

            $cell = new \Ksfraser\HTML\Elements\FaCell($inputHtml);
            $cell->toHtml();
            
            // Add Ajax update functionality
            if (isset($GLOBALS['Ajax'])) {
                $GLOBALS['Ajax']->addUpdate($name, $name, $_POST[$name]);
            }
        }
    }

    public static function date_row($label, $name, $title=null, $check=null, $inc_days=0, $inc_months=0, $inc_years=0, $params=null, $submit_on_change=false)
    {
        if (function_exists('\\date_row')) {
            call_user_func('\\date_row', $label, $name, $title, $check, $inc_days, $inc_months, $inc_years, $params, $submit_on_change);
        } else {
            // OOP implementation
            self::start_row();
            self::label_cell($label, "class='label'");
            self::date_cells(null, $name, $title, $check, $inc_days, $inc_months, $inc_years, $params, $submit_on_change);
            self::end_row();
        }
    }

    public static function email_row($label, $name, $value, $size, $max, $title=null, $params="", $post_label="")
    {
        if (function_exists('\\email_row')) {
            call_user_func('\\email_row', $label, $name, $value, $size, $max, $title, $params, $post_label);
        } else {
            // OOP implementation
            $display_label = $label;
            if (!empty($_POST[$name])) {
                $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($label));
                $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', 'Mailto:' . $_POST[$name]));
                ob_start();
                $link->toHtml();
                $display_label = ob_get_clean();
            }
            self::text_row($display_label, $name, $value, $size, $max, $title, $params, $post_label);
        }
    }

    public static function link_row($label, $name, $value, $size, $max, $title=null, $params="", $post_label="")
    {
        if (function_exists('\\link_row')) {
            call_user_func('\\link_row', $label, $name, $value, $size, $max, $title, $params, $post_label);
        } else {
            // OOP implementation
            $display_label = $label;
            $val = $_POST[$name] ?? '';
            if ($val) {
                if (strpos($val,'http://')===false) {
                    $val = 'http://'.$val;
                }
                $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($label));
                $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', $val));
                $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('target', '_blank'));
                ob_start();
                $link->toHtml();
                $display_label = ob_get_clean();
            }
            self::text_row($display_label, $name, $value, $size, $max, $title, $params, $post_label);
        }
    }

    public static function password_row($label, $name, $value)
    {
        if (function_exists('\\password_row')) {
            call_user_func('\\password_row', $label, $name, $value);
        } else {
            // OOP implementation
            self::start_row();
            self::label_cell($label, "class='label'");
            $input = new \Ksfraser\HTML\Elements\HtmlInput('password');
            $input->setName($name);
            $input->setValue($value);
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('size', '32'));
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('maxlength', '32'));
            // Get HTML string
            ob_start();
            $input->toHtml();
            $inputHtml = ob_get_clean();
            $cell = new \Ksfraser\HTML\Elements\FaCell($inputHtml);
            $cell->toHtml();
            self::end_row();
        }
    }

    public static function file_row($label, $name, $id = "")
    {
        if (function_exists('\\file_row')) {
            call_user_func('\\file_row', $label, $name, $id);
        } else {
            // OOP implementation
            self::start_row();
            self::label_cell($label, "class='label'");
            self::file_cells(null, $name, $id);
            self::end_row();
        }
    }

    public static function ref_cells($label, $name, $title=null, $init=null, $params=null, $submit_on_change=false, $type=null, $context=null)
    {
        if (function_exists('\\ref_cells')) {
            call_user_func('\\ref_cells', $label, $name, $title, $init, $params, $submit_on_change, $type, $context);
        } else {
            // OOP implementation - simplified fallback
            if (!isset($_POST[$name]) || $_POST[$name] == "") {
                $_POST[$name] = $init ?? '';
            }

            if (isset($label)) {
                self::label_cell($label, $params);
            }

            $input = new \Ksfraser\HTML\Elements\HtmlInput('text');
            $input->setName($name);
            $input->setValue($_POST[$name]);
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('size', '16'));
            $input->addAttribute(new \Ksfraser\HTML\HtmlAttribute('maxlength', '35'));
            // Get HTML string
            ob_start();
            $input->toHtml();
            $inputHtml = ob_get_clean();
            $cell = new \Ksfraser\HTML\Elements\FaCell($inputHtml);
            $cell->toHtml();
            // TODO: Add Ajax updates and reference line handling
        }
    }

    public static function small_amount_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        if (function_exists('\\small_amount_cells')) {
            call_user_func('\\small_amount_cells', $label, $name, $init, $params, $post_label, $dec);
        } else {
            // OOP implementation
            self::amount_cells_ex($label, $name, 7, 12, $init, $params, $post_label, $dec);
        }
    }

    public static function small_amount_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        if (function_exists('\\small_amount_row')) {
            call_user_func('\\small_amount_row', $label, $name, $init, $params, $post_label, $dec);
        } else {
            // OOP implementation
            self::start_row();
            self::small_amount_cells($label, $name, $init, $params, $post_label, $dec);
            self::end_row();
        }
    }

    public static function ref_row($label, $name, $title=null, $init=null, $submit_on_change=false, $type=null, $context = null)
    {
        if (function_exists('\\ref_row')) {
            call_user_func('\\ref_row', $label, $name, $title, $init, $submit_on_change, $type, $context);
        } else {
            // OOP implementation
            self::start_row();
            self::label_cell($label, "class='label'");
            self::ref_cells(null, $name, $title, $init, null, $submit_on_change, $type, $context);
            self::end_row();
        }
    }

    public static function percent_row($label, $name, $init=null)
    {
        if (function_exists('\\percent_row')) {
            call_user_func('\\percent_row', $label, $name, $init);
        } else {
            // OOP implementation
            if (!isset($_POST[$name]) || $_POST[$name] == "") {
                $_POST[$name] = $init == null ? '' : $init;
            }
            // Use small_amount_row with % post_label, simplified dec
            self::small_amount_row($label, $name, $_POST[$name], null, "%", 2); // Simplified dec
        }
    }

    public static function qty_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        if (function_exists('\\qty_row')) {
            call_user_func('\\qty_row', $label, $name, $init, $params, $post_label, $dec);
        } else {
            // OOP implementation
            $dec = $dec ?? 2; // Simplified default
            self::start_row();
            self::amount_cells($label, $name, $init, $params, $post_label, $dec);
            self::end_row();
        }
    }

    public static function small_qty_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
    {
        if (function_exists('\\small_qty_row')) {
            call_user_func('\\small_qty_row', $label, $name, $init, $params, $post_label, $dec);
        } else {
            // OOP implementation
            $dec = $dec ?? 2; // Simplified default
            self::start_row();
            self::small_amount_cells($label, $name, $init, $params, $post_label, $dec);
            self::end_row();
        }
    }

    public static function combo_list_cells($options, $order_by_field, $label, $name, $selected_id = null, $none_option=false, $submit_on_change=false)
    {
        // Use OOP implementation for array data (DI) or when global function doesn't exist
        if (is_array($options)) {
            // OOP implementation with DI
            if ($label != null) {
                self::label_cell($label);
            }

            // Create select element using HtmlSelect
            $select = new \Ksfraser\HTML\Elements\HtmlSelect($name);
            
            if ($submit_on_change) {
                $select->setAttribute('onchange', 'this.form.submit()');
            }

            // Handle none option
            if ($none_option) {
                $noneText = ($none_option === true) ? 'select' : $none_option;
                $select->addOption(new \Ksfraser\HTML\Elements\HtmlOption('', '-- ' . $noneText . ' --'));
            }

            // Handle options data
            if (is_array($options)) {
                // DI: options passed as array
                foreach ($options as $value => $display) {
                    $isSelected = ($selected_id !== null && (string)$value === (string)$selected_id);
                    $select->addOption(new \Ksfraser\HTML\Elements\HtmlOption((string)$value, $display, $isSelected));
                }
            }

            // Output the select in a cell
            $cell = new \Ksfraser\HTML\Elements\FaCell($select);
            $cell->toHtml();
        } else {
            // For SQL strings, check for global function
            loadFaFunctions();
            if (function_exists('\\combo_list_cells')) {
                call_user_func('\\combo_list_cells', $options, $order_by_field, $label, $name, $selected_id, $none_option, $submit_on_change);
            } else {
                // OOP implementation for SQL
                if ($label != null) {
                    self::label_cell($label);
                }

                // Create select element using HtmlSelect
                $select = new \Ksfraser\HTML\Elements\HtmlSelect($name);
                
                if ($submit_on_change) {
                    $select->setAttribute('onchange', 'this.form.submit()');
                }

                // Handle none option
                if ($none_option) {
                    $noneText = ($none_option === true) ? 'select' : $none_option;
                    $select->addOption(new \Ksfraser\HTML\Elements\HtmlOption('', '-- ' . $noneText . ' --'));
                }

                // Execute SQL
                if (function_exists('\\db_query') && function_exists('\\db_fetch_row')) {
                    $result = call_user_func('\\db_query', $options);
                    if ($result) {
                        while ($row = call_user_func('\\db_fetch_row', $result)) {
                            $value = $row[0];
                            $display = isset($row[1]) ? $row[1] : $row[0];
                            $isSelected = ($selected_id !== null && $value == $selected_id);
                            $select->addOption(new \Ksfraser\HTML\Elements\HtmlOption($value, $display, $isSelected));
                        }
                    } else {
                        // Fallback if query fails
                        $select->addOption(new \Ksfraser\HTML\Elements\HtmlOption('', '-- SQL Error --'));
                    }
                } else {
                    // Fallback when database functions not available
                    $select->addOption(new \Ksfraser\HTML\Elements\HtmlOption('', '-- Database not available --'));
                }

                // Output the select in a cell
                $cell = new \Ksfraser\HTML\Elements\FaCell($select);
                $cell->toHtml();
            }
        }
    }

    public static function textarea_cells($label, $name, $value, $cols, $rows, $max=255, $title = null, $params="")
    {
        if (function_exists('\\textarea_cells')) {
            call_user_func('\\textarea_cells', $label, $name, $value, $cols, $rows, $max, $title, $params);
        } else {
            // OOP implementation
            if (function_exists('\\default_focus')) {
                call_user_func('\\default_focus', $name);
            }

            if ($label != null) {
                $labelCell = new \Ksfraser\HTML\Elements\FaCell($label, $params);
                $labelCell->toHtml();
            }

            $value = $value ?? ($_POST[$name] ?? "");
            $textareaHtml = "<textarea name='" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "' cols='$cols' rows='$rows'"
                . ($title ? " title='" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "'" : '')
                . ($max ? " maxlength='$max'" : '')
                . ">" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "</textarea>";

            $cell = new \Ksfraser\HTML\Elements\FaCell($textareaHtml);
            $cell->toHtml();
            
            // Add Ajax update functionality
            if (isset($GLOBALS['Ajax'])) {
                $GLOBALS['Ajax']->addUpdate($name, $name, $value);
            }
        }
    }

    public static function textarea_row($label, $name, $value, $cols, $rows, $max=255, $title=null, $params="")
    {
        if (function_exists('\\textarea_row')) {
            call_user_func('\\textarea_row', $label, $name, $value, $cols, $rows, $max, $title, $params);
        } else {
            // OOP implementation
            self::start_row();
            self::label_cell($label, "class='label'");
            self::textarea_cells(null, $name, $value, $cols, $rows, $max, $title, $params);
            self::end_row();
        }
    }

    public static function check_row($label, $name, $value=null, $submit_on_change=false, $title=false)
    {
        if (function_exists('\\check_row')) {
            call_user_func('\\check_row', $label, $name, $value, $submit_on_change, $title);
        } else {
            // OOP implementation
            self::start_row();
            self::label_cell($label, "class='label'");
            self::check_cells(null, $name, $value, $submit_on_change, $title);
            self::end_row();
        }
    }

    public static function submit_row($name, $value, $right=true, $extra="", $title=false, $async=false)
    {
        if (function_exists('\\submit_row')) {
            call_user_func('\\submit_row', $name, $value, $right, $extra, $title, $async);
        } else {
            // OOP implementation
            self::start_row();
            if ($right) {
                self::label_cell("&nbsp;");
            }
            self::submit_cells($name, $value, $extra, $title, $async);
            self::end_row();
        }
    }

    public static function start_outer_table($class=false, $extra="", $padding='2', $spacing='0', $br=false)
    {
        if (function_exists('\\start_outer_table')) {
            call_user_func('\\start_outer_table', $class, $extra, $padding, $spacing, $br);
        } else {
            // OOP implementation
            if ($br) {
                $brElement = new \Ksfraser\HTML\Elements\HtmlBr();
                $brElement->toHtml();
            }
            self::start_table($class, $extra);
            echo "<tr valign='top'><td>\n"; // outer table
        }
    }

    public static function table_section($number=1, $width=false)
    {
        if (function_exists('\\table_section')) {
            call_user_func('\\table_section', $number, $width);
        } else {
            // OOP implementation
            if ($number > 1) {
                echo "</table>\n";
                // output_hidden() - simplified, not implemented
                $widthAttr = ($width ? "width='" . htmlspecialchars($width, ENT_QUOTES, 'UTF-8') . "'" : "");
                echo "</td><td style='border-left:1px solid #cccccc;' $widthAttr>\n"; // outer table
            }
            echo "<table class='tablestyle_inner'>\n";
        }
    }

    public static function end_outer_table($breaks=0, $close_table=true)
    {
        if (function_exists('\\end_outer_table')) {
            call_user_func('\\end_outer_table', $breaks, $close_table);
        } else {
            // OOP implementation
            if ($close_table) {
                echo "</table>\n";
                // output_hidden() - simplified, not implemented
            }
            echo "</td></tr>\n";
            self::end_table($breaks);
        }
    }

    public static function alt_table_row_color(&$k, $extra_class=null)
    {
        if (function_exists('\\alt_table_row_color')) {
            call_user_func('\\alt_table_row_color', $k, $extra_class);
        } else {
            // OOP implementation
            $classes = $extra_class ? array(htmlspecialchars($extra_class, ENT_QUOTES, 'UTF-8')) : array();
            if ($k == 1) {
                array_push($classes, 'oddrow');
                $k = 0;
            } else {
                array_push($classes, 'evenrow');
                $k++;
            }
            echo "<tr class='" . implode(' ', $classes) . "'>\n";
        }
    }

    public static function table_section_title($msg, $colspan=2)
    {
        if (function_exists('\\table_section_title')) {
            call_user_func('\\table_section_title', $msg, $colspan);
        } else {
            // OOP implementation using HTML element classes
            $row = new \Ksfraser\HTML\Elements\HtmlTableRow(new \Ksfraser\HTML\Elements\HtmlString(''));
            
            $cell = new \Ksfraser\HTML\Elements\HtmlTableRowCell(new \Ksfraser\HTML\Elements\HtmlString($msg));
            $cell->addAttribute(new \Ksfraser\HTML\HtmlAttribute('colspan', $colspan));
            $cell->addAttribute(new \Ksfraser\HTML\HtmlAttribute('class', 'tableheader'));
            $row->addNested($cell);
            
            $row->toHtml();
        }
    }

    public static function email_cell($label, $params="", $id=null)
    {
        if (function_exists('\\email_cell')) {
            call_user_func('\\email_cell', $label, $params, $id);
        } else {
            // OOP implementation using HTML element classes
            $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($label));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', "mailto:$label"));
            self::label_cell($link, $params, $id);
        }
    }

    public static function percent_cell($label, $bold=false, $id=null)
    {
        if (function_exists('\\percent_cell')) {
            call_user_func('\\percent_cell', $label, $bold, $id);
        } else {
            // OOP implementation using HTML element classes
            $formatted = number_format($label, 2) . "%";
            $content = $bold ? new \Ksfraser\HTML\Elements\HtmlB(new \Ksfraser\HTML\Elements\HtmlString($formatted)) : new \Ksfraser\HTML\Elements\HtmlString($formatted);
            self::label_cell($content, "nowrap align=right", $id);
        }
    }

    public static function qty_cell($label, $bold=false, $dec=null, $id=null)
    {
        if (function_exists('\\qty_cell')) {
            call_user_func('\\qty_cell', $label, $bold, $dec, $id);
        } else {
            // OOP implementation using HTML element classes
            $dec = $dec ?? 2; // Default decimal places
            $formatted = number_format($label, $dec);
            $content = $bold ? new \Ksfraser\HTML\Elements\HtmlB(new \Ksfraser\HTML\Elements\HtmlString($formatted)) : new \Ksfraser\HTML\Elements\HtmlString($formatted);
            self::label_cell($content, "nowrap align=right", $id);
        }
    }

    public static function button_cell($name, $value, $title=false, $icon=false, $aspect='')
    {
        if (function_exists('\\button_cell')) {
            call_user_func('\\button_cell', $name, $value, $title, $icon, $aspect);
        } else {
            // OOP implementation using HTML element classes
            $button = new \Ksfraser\HTML\Elements\HtmlButton(new \Ksfraser\HTML\Elements\HtmlString($value));
            $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('type', 'submit'));
            $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('name', $name));
            $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('value', $value));
            if ($title) {
                $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('title', $title));
            }
            if ($icon) {
                $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('icon', $icon));
            }
            if ($aspect) {
                $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('aspect', $aspect));
            }
            $cell = new \Ksfraser\HTML\Elements\FaCell($button, "align='center'");
            $cell->toHtml();
        }
    }

    public static function delete_button_cell($name, $value, $title=false)
    {
        if (function_exists('\\delete_button_cell')) {
            call_user_func('\\delete_button_cell', $name, $value, $title);
        } else {
            // OOP implementation
            self::button_cell($name, $value, $title, 'delete', '');
        }
    }

    public static function edit_button_cell($name, $value, $title=false)
    {
        if (function_exists('\\edit_button_cell')) {
            call_user_func('\\edit_button_cell', $name, $value, $title);
        } else {
            // OOP implementation
            self::button_cell($name, $value, $title, 'edit', '');
        }
    }

    public static function select_button_cell($name, $value, $title=false)
    {
        if (function_exists('\\select_button_cell')) {
            call_user_func('\\select_button_cell', $name, $value, $title);
        } else {
            // OOP implementation
            self::button_cell($name, $value, $title, 'select', '');
        }
    }

    public static function button($name, $value, $title=false, $icon=false, $aspect='')
    {
        if (function_exists('\\button')) {
            call_user_func('\\button', $name, $value, $title, $icon, $aspect);
        } else {
            // OOP implementation using HTML element classes
            $button = new \Ksfraser\HTML\Elements\HtmlButton(new \Ksfraser\HTML\Elements\HtmlString($value));
            $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('type', 'submit'));
            $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('name', $name));
            $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('value', $value));
            if ($title) {
                $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('title', $title));
            }
            if ($icon) {
                $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('icon', $icon));
            }
            if ($aspect) {
                $button->addAttribute(new \Ksfraser\HTML\HtmlAttribute('aspect', $aspect));
            }
            $button->toHtml();
        }
    }

    public static function radio($label, $name, $value, $selected=null, $submit_on_change=false)
    {
        if (function_exists('\\radio')) {
            return call_user_func('\\radio', $label, $name, $value, $selected, $submit_on_change);
        } else {
            // OOP implementation using HTML element classes
            $radio = new \Ksfraser\HTML\Elements\HtmlInput('radio');
            $radio->setName($name);
            $radio->setValue($value);
            $selected_val = $selected ?? ($_POST[$name] ?? null);
            if ($selected_val === (string)$value) {
                $radio->addAttribute(new \Ksfraser\HTML\HtmlAttribute('checked', 'checked'));
            }
            if ($submit_on_change) {
                $radio->addAttribute(new \Ksfraser\HTML\HtmlAttribute('onclick', "JsHttpRequest.request(\"_{$name}_update\", this.form);"));
            }
            // Get the HTML string
            ob_start();
            $radio->toHtml();
            $radioHtml = ob_get_clean();
            return $radioHtml . ($label ? $label : '');
        }
    }

    public static function unit_amount_cell($label, $bold=false, $params="", $id=null)
    {
        if (function_exists('\\unit_amount_cell')) {
            call_user_func('\\unit_amount_cell', $label, $bold, $params, $id);
        } else {
            // OOP implementation using HTML element classes
            $formatted = "$" . number_format($label, 4);
            $content = $bold ? new \Ksfraser\HTML\Elements\HtmlB(new \Ksfraser\HTML\Elements\HtmlString($formatted)) : new \Ksfraser\HTML\Elements\HtmlString($formatted);
            self::label_cell($content, "nowrap align=right " . $params, $id);
        }
    }

    public static function labelheader_cell($label, $params="")
    {
        if (function_exists('\\labelheader_cell')) {
            call_user_func('\\labelheader_cell', $label, $params);
        } else {
            // OOP implementation using HTML element classes
            $cell = new \Ksfraser\HTML\Elements\FaCell($label, "class='tableheader' " . $params);
            $cell->toHtml();
        }
    }

    public static function amount_decimal_cell($label, $params="", $id=null)
    {
        if (function_exists('\\amount_decimal_cell')) {
            call_user_func('\\amount_decimal_cell', $label, $params, $id);
        } else {
            // OOP implementation using HTML element classes
            $formatted = number_format($label, 0);
            $content = new \Ksfraser\HTML\Elements\HtmlString($formatted);
            self::label_cell($content, "nowrap align=right " . $params, $id);
        }
    }

    public static function br($num=1)
    {
        if (function_exists('\\br')) {
            call_user_func('\\br', $num);
        } else {
            // OOP implementation using HTML element classes
            for ($i = 0; $i < $num; $i++) {
                $br = new \Ksfraser\HTML\Elements\HtmlBr();
                $br->toHtml();
            }
        }
    }

    public static function vertical_space($params='')
    {
        if (function_exists('\\vertical_space')) {
            call_user_func('\\vertical_space', $params);
        } else {
            // OOP implementation
            echo "</td></tr><tr><td valign=center $params>";
        }
    }

    public static function access_string($label, $clean=false)
    {
        if (function_exists('\\access_string')) {
            return call_user_func('\\access_string', $label, $clean);
        } else {
            // OOP implementation
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
    }

    public static function default_focus($name=null, $form_no=0)
    {
        if (function_exists('\\default_focus')) {
            return call_user_func('\\default_focus', $name, $form_no);
        } else {
            // OOP implementation
            static $next;
            if ($name==null)
                $name = uniqid('_el',true);
            if (!isset($_POST['_focus'])) {
                // Simplified for OOP
            }
            return $name;
        }
    }

    public static function hyperlink_no_params($target, $label, $center=true)
    {
        if (function_exists('\\hyperlink_no_params')) {
            call_user_func('\\hyperlink_no_params', $target, $label, $center);
        } else {
            // OOP implementation using HTML element classes
            $id = self::default_focus();
            $pars = self::access_string($label);
            if ($target == '')
                $target = $_SERVER['PHP_SELF'];

            // Create the link element
            $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($pars[0]));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', $target));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('id', $id));

            // Parse and add access key attributes
            if (!empty($pars[1])) {
                // Extract accesskey from the attribute string like " accesskey='L'"
                if (preg_match("/accesskey='([^']+)'/", $pars[1], $matches)) {
                    $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('accesskey', $matches[1]));
                }
            }

            if ($center) {
                // Add line break before center
                $br = new \Ksfraser\HTML\Elements\HtmlBr();
                $br->toHtml();

                // Create centered div
                $centerDiv = new \Ksfraser\HTML\Elements\HtmlDiv($link);
                $centerDiv->addAttribute(new \Ksfraser\HTML\HtmlAttribute('style', 'text-align: center'));
                $centerDiv->toHtml();
            } else {
                $link->toHtml();
            }
        }
    }

    public static function hyperlink_no_params_td($target, $label)
    {
        if (function_exists('\\hyperlink_no_params_td')) {
            call_user_func('\\hyperlink_no_params_td', $target, $label);
        } else {
            // OOP implementation using HTML element classes
            // Create the link element (without centering)
            $id = self::default_focus();
            $pars = self::access_string($label);
            if ($target == '')
                $target = $_SERVER['PHP_SELF'];

            $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($pars[0]));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', $target));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('id', $id));

            // Parse and add access key attributes
            if (!empty($pars[1])) {
                if (preg_match("/accesskey='([^']+)'/", $pars[1], $matches)) {
                    $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('accesskey', $matches[1]));
                }
            }

            // Wrap in table cell directly using HTML element composition
            $cell = new \Ksfraser\HTML\Elements\FaCell($link);
            $cell->toHtml();
        }
    }

    public static function viewer_link($label, $url='', $class='', $id='', $icon=null)
    {
        if (function_exists('\\viewer_link')) {
            return call_user_func('\\viewer_link', $label, $url, $class, $id, $icon);
        } else {
            // OOP implementation using HTML element classes
            if ($url != "") {
                $pars = self::access_string($label);
                $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($pars[0]));
                $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('target', '_blank'));
                $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', $url));
                $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('onclick', "javascript:openWindow(this.href,this.target); return false;"));

                if ($class != '') {
                    $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('class', $class));
                }
                if ($id != '') {
                    $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('id', $id));
                }

                // Parse and add access key attributes
                if (!empty($pars[1])) {
                    if (preg_match("/accesskey='([^']+)'/", $pars[1], $matches)) {
                        $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('accesskey', $matches[1]));
                    }
                }

                // Get HTML string
                ob_start();
                $link->toHtml();
                return ob_get_clean();
            } else {
                return $label;
            }
        }
    }

    public static function menu_link($url, $label, $id=null)
    {
        if (function_exists('\\menu_link')) {
            return call_user_func('\\menu_link', $url, $label, $id);
        } else {
            // OOP implementation using HTML element classes
            $id = self::default_focus($id);
            $pars = self::access_string($label);

            if ($url[0] != '/')
                $url = '/'.$url;
            // Simplified: ignore path_to_root
            // $url = $path_to_root.$url;

            $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($pars[0]));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', $url));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('class', 'menu_option'));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('id', $id));

            // Parse and add access key attributes
            if (!empty($pars[1])) {
                if (preg_match("/accesskey='([^']+)'/", $pars[1], $matches)) {
                    $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('accesskey', $matches[1]));
                }
            }

            // Get HTML string
            ob_start();
            $link->toHtml();
            return ob_get_clean();
        }
    }

    public static function display_caption($msg)
    {
        if (function_exists('\\display_caption')) {
            call_user_func('\\display_caption', $msg);
        } else {
            // OOP implementation using HTML element classes
            $caption = new \Ksfraser\HTML\Elements\HtmlTableCaption(new \Ksfraser\HTML\Elements\HtmlString($msg));
            $caption->toHtml();
            echo "\n";
        }
    }

    public static function display_heading($msg)
    {
        if (function_exists('\\display_heading')) {
            call_user_func('\\display_heading', $msg);
        } else {
            // OOP implementation using HTML element classes
            $span = new \Ksfraser\HTML\Elements\HtmlSpan(new \Ksfraser\HTML\Elements\HtmlString($msg));
            $span->addAttribute(new \Ksfraser\HTML\HtmlAttribute('class', 'headingtext'));

            $centerDiv = new \Ksfraser\HTML\Elements\HtmlDiv($span);
            $centerDiv->addAttribute(new \Ksfraser\HTML\HtmlAttribute('style', 'text-align: center'));
            $centerDiv->toHtml();
        }
    }

    public static function display_heading2($msg)
    {
        if (function_exists('\\display_heading2')) {
            call_user_func('\\display_heading2', $msg);
        } else {
            // OOP implementation using HTML element classes
            $span = new \Ksfraser\HTML\Elements\HtmlSpan(new \Ksfraser\HTML\Elements\HtmlString($msg));
            $span->addAttribute(new \Ksfraser\HTML\HtmlAttribute('class', 'headingtext2'));

            $centerDiv = new \Ksfraser\HTML\Elements\HtmlDiv($span);
            $centerDiv->addAttribute(new \Ksfraser\HTML\HtmlAttribute('style', 'text-align: center'));
            $centerDiv->toHtml();
        }
    }

    public static function check_value($name)
    {
        if (function_exists('\\check_value')) {
            return call_user_func('\\check_value', $name);
        } else {
            // OOP implementation
            if (is_array($name)) {
                $ret = array();
                foreach($name as $key)
                    $ret[$key] = self::check_value($key);
                return $ret;
            } else
                return (empty($_POST[$name]) ? 0 : 1);
        }
    }

    public static function div_start($id='', $trigger=null, $non_ajax=false)
    {
        if (function_exists('\\div_start')) {
            call_user_func('\\div_start', $id, $trigger, $non_ajax);
        } else {
            // OOP implementation
            if ($non_ajax) { // div for non-ajax elements
                echo "<div style='display:none' class='js_only' " . ($id != '' ? "id='" . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . "'" : '') . ">";
            } else { // ajax ready div
                echo "<div " . ($id != '' ? "id='" . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . "'" : '') . ">";
                // Simplified: no output buffering for Ajax
            }
        }
    }

    public static function div_end()
    {
        if (function_exists('\\div_end')) {
            call_user_func('\\div_end');
        } else {
            // OOP implementation
            // Simplified: no Ajax handling
            echo "</div>";
        }
    }

    public static function hyperlink_params($target, $label, $params, $center=true)
    {
        if (function_exists('\\hyperlink_params')) {
            call_user_func('\\hyperlink_params', $target, $label, $params, $center);
        } else {
            // OOP implementation using HTML element classes
            $id = self::default_focus();

            $pars = self::access_string($label);
            if ($target == '')
                $target = $_SERVER['PHP_SELF'];

            $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($pars[0]));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('id', $id));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', $target . '?' . $params));

            // Parse and add access key attributes
            if (!empty($pars[1])) {
                if (preg_match("/accesskey='([^']+)'/", $pars[1], $matches)) {
                    $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('accesskey', $matches[1]));
                }
            }

            if ($center) {
                // Add line break before center
                $br = new \Ksfraser\HTML\Elements\HtmlBr();
                $br->toHtml();

                // Create centered div
                $centerDiv = new \Ksfraser\HTML\Elements\HtmlDiv($link);
                $centerDiv->addAttribute(new \Ksfraser\HTML\HtmlAttribute('style', 'text-align: center'));
                $centerDiv->toHtml();
            } else {
                $link->toHtml();
            }
        }
    }

    public static function hyperlink_params_td($target, $label, $params)
    {
        if (function_exists('\\hyperlink_params_td')) {
            call_user_func('\\hyperlink_params_td', $target, $label, $params);
        } else {
            // OOP implementation using HTML element classes
            $id = self::default_focus();

            $pars = self::access_string($label);
            if ($target == '')
                $target = $_SERVER['PHP_SELF'];

            $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($pars[0]));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('id', $id));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', $target . '?' . $params));

            // Parse and add access key attributes
            if (!empty($pars[1])) {
                if (preg_match("/accesskey='([^']+)'/", $pars[1], $matches)) {
                    $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('accesskey', $matches[1]));
                }
            }

            // Wrap in table cell
            $cell = new \Ksfraser\HTML\Elements\FaCell($link);
            $cell->toHtml();
        }
    }

    public static function hyperlink_params_separate($target, $label, $params, $center=false)
    {
        if (function_exists('\\hyperlink_params_separate')) {
            call_user_func('\\hyperlink_params_separate', $target, $label, $params, $center);
        } else {
            // OOP implementation using HTML element classes
            $id = self::default_focus();

            $pars = self::access_string($label);

            $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($pars[0]));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('target', '_blank'));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('id', $id));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', $target . '?' . $params));

            // Parse and add access key attributes
            if (!empty($pars[1])) {
                if (preg_match("/accesskey='([^']+)'/", $pars[1], $matches)) {
                    $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('accesskey', $matches[1]));
                }
            }

            if ($center) {
                // Add line break before center
                $br = new \Ksfraser\HTML\Elements\HtmlBr();
                $br->toHtml();

                // Create centered div
                $centerDiv = new \Ksfraser\HTML\Elements\HtmlDiv($link);
                $centerDiv->addAttribute(new \Ksfraser\HTML\HtmlAttribute('style', 'text-align: center'));
                $centerDiv->toHtml();
            } else {
                $link->toHtml();
            }
        }
    }

    public static function hyperlink_params_separate_td($target, $label, $params)
    {
        if (function_exists('\\hyperlink_params_separate_td')) {
            call_user_func('\\hyperlink_params_separate_td', $target, $label, $params);
        } else {
            // OOP implementation using HTML element classes
            $id = self::default_focus();

            $pars = self::access_string($label);

            $link = new \Ksfraser\HTML\Elements\HtmlA(new \Ksfraser\HTML\Elements\HtmlString($pars[0]));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('target', '_blank'));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('id', $id));
            $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('href', $target . '?' . $params));

            // Parse and add access key attributes
            if (!empty($pars[1])) {
                if (preg_match("/accesskey='([^']+)'/", $pars[1], $matches)) {
                    $link->addAttribute(new \Ksfraser\HTML\HtmlAttribute('accesskey', $matches[1]));
                }
            }

            // Wrap in table cell
            $cell = new \Ksfraser\HTML\Elements\FaCell($link);
            $cell->toHtml();
        }
    }
}