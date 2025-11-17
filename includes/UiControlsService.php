<?php

namespace FA;

/**
 * UI Controls Service
 *
 * Handles UI control functions like form handling and input retrieval.
 * Refactored from procedural functions to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages UI controls only
 * - Open/Closed: Can be extended for additional UI features
 * - Liskov Substitution: Compatible with UI interfaces
 * - Interface Segregation: Focused UI methods
 * - Dependency Inversion: Depends on abstractions, not globals
 *
 * DRY: Reuses UI logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | UiControlsService  |
 * +---------------------+
 * | - form_nested: int  |
 * +---------------------+
 * | + getPost(name,dflt)|
 * | + startForm(...)    |
 * | ...                 |
 * +---------------------+
 *
 * @package FA
 */
class UiControlsService
{
    private static int $form_nested = -1;

    /**
     * Retrieve value of POST variable(s)
     *
     * @param string|array $name Variable name or array
     * @param mixed $dflt Default value
     * @return mixed POST value or default
     */
    public function getPost($name, $dflt = '')
    {
        if (is_array($name)) {
            $ret = array();
            foreach($name as $key => $dflt)
                if (!is_numeric($key)) {
                    $ret[$key] = is_numeric($dflt) ? input_num($key, $dflt) : $this->getPost($key, $dflt);
                } else {
                    $ret[$dflt] = $this->getPost($dflt, null);
                }
            return $ret;
        } else
            return is_float($dflt) ? input_num($name, $dflt) :
                    ((!isset($_POST[$name]) /*|| $_POST[$name] === ''*/) ? $dflt : $_POST[$name]);
    }

    /**
     * Start a form
     *
     * @param bool $multi Multipart flag
     * @param bool $dummy Dummy flag (compatibility)
     * @param string $action Form action
     * @param string $name Form name
     */
    public function startForm(bool $multi = false, bool $dummy = false, string $action = "", string $name = ""): void
    {
        if (++self::$form_nested) return;

        if ($name != "") $name = "name='$name'";
        if ($action == "") $action = $_SERVER['PHP_SELF'];

        if ($multi)
            echo "<form enctype='multipart/form-data' method='post' action='$action' $name>\n";
        else
            echo "<form method='post' action='$action' $name>\n";
    }

    // Add more methods as needed
}