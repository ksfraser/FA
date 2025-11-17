<?php

namespace Ksfraser\HTML;

if (!defined('FA_ROOT')) {
    define('FA_ROOT', realpath(__DIR__ . '/../../../..'));
}

// Load FA UI functions in global namespace
require_once(FA_ROOT . "/includes/ui/ui_input.inc");
require_once(FA_ROOT . "/includes/ui/ui_lists.inc");
require_once(FA_ROOT . "/includes/ui/ui_controls.inc");

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
            echo "<tr><td class='label'>$label</td><td $params>$content</td></tr>";
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
            echo "<tr>";
            foreach ($labels as $label) {
                echo "<th $params>$label</th>";
            }
            echo "</tr>\n";
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

    public static function label_cells($label, $value, $params="")
    {
        if (function_exists('\\label_cells')) {
            call_user_func('\\label_cells', $label, $value, $params);
        } else {
            echo "<td $params>$label</td><td $params>$value</td>\n";
        }
    }
}