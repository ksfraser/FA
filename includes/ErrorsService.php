<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
namespace FA;

/**
 * Errors Service
 *
 * Handles error handling, backtrace, and message formatting.
 * Refactored to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages errors and messages
 * - Open/Closed: Extensible for new error types
 * - Liskov Substitution: Compatible with error handling interfaces
 * - Interface Segregation: Focused methods for different error operations
 * - Dependency Inversion: Depends on abstractions for output and DB
 *
 * DRY: Consolidates error logic into class methods
 * TDD: Designed for testable error handling
 *
 * UML Class Diagram:
 * +---------------------+
 * |   ErrorsService    |
 * +---------------------+
 * | - messages: array  |
 * | - beforeBox: string|
 * +---------------------+
 * | + triggerError()   |
 * | + getBacktrace()   |
 * | + fmtErrors()      |
 * | + errorBox()       |
 * | + endFlush()       |
 * | + displayDbError() |
 * | + checkDbError()   |
 * +---------------------+
 *
 * @package FA
 */
class ErrorsService {

    private array $messages = [];
    private string $beforeBox = '';

    public function triggerError(string $msg, int $errorLevel = E_USER_NOTICE): void {
        if ($errorLevel == E_USER_ERROR) {
            $this->errorHandler(E_USER_ERROR, $msg, __FILE__, __LINE__);
        } else {
            trigger_error($msg, $errorLevel);
        }
    }

    public function getBacktrace(bool $html = false, int $skip = 0): string {
        $str = '';
        if ($html) $str .= '<table border=0>';
        $trace = debug_backtrace();

        foreach($trace as $trn => $tr) {
            if ($trn <= $skip) continue;
            if ($html) $str .= '<tr><td>';
            if (isset($tr['file']) && isset($tr['line']))
                $str .= $tr['file'].':'.$tr['line'].': ';
            if ($html) $str .= '</td><td>';
            if (isset($tr['type'])) {
                if($tr['type'] == '::') {
                    $str .= $tr['class'].'::';
                } else if($tr['type'] == '->') {
                    $str .= '('.$tr['class'].' Object)'.'->';
                }
            }
            $str .= $tr['function'].'(';
            
            if(isset($tr['args']) && is_array($tr['args'])) {
                $args = array();
                foreach($tr['args'] as $n=>$a) {
                    if (is_object($tr['args'][$n]))
                        $args[$n] = "(".get_class($tr['args'][$n])." Object)";
                    elseif (is_array($tr['args'][$n]))
                        $args[$n] = "(Array[".count($tr['args'][$n])."])";
                    else
                        $args[$n] = "'".$tr['args'][$n]."'";
                }
                $str .= implode(',',$args);
            }
            $str .= ')</td>';
        }

        if ($html) $str .= '</tr></table>';
        return $str;
    }

    public function fmtErrors(bool $center = false): string {
        global $path_to_root, $SysPrefs;

        $msg_class = array(
            E_USER_ERROR => 'err_msg',
            E_USER_WARNING =>'warn_msg', 
            E_USER_NOTICE => 'note_msg');

        $type = E_USER_NOTICE;
        $content = '';

        if (count($this->messages)) {
            foreach($this->messages as $cnt=>$msg) {
                if ($SysPrefs->go_debug && $msg[0]>E_USER_NOTICE)
                    $msg[0] = E_ERROR;

                if ($msg[0]>$type) continue;

                if ($msg[0]<$type) { 
                    if ($msg[0] == E_USER_WARNING) {
                        $type = E_USER_WARNING;
                        $content = '';
                    } else  {
                        $type = E_USER_ERROR;
                        if($type == E_USER_WARNING)
                            $content = '';
                    }
                }

                $str = $msg[1];
                if (!in_array($msg[0], array(E_USER_NOTICE, E_USER_ERROR, E_USER_WARNING)) && $msg[2] != null)
                    $str .= ' '._('in file').': '.$msg[2].' '._('at line ').$msg[3];

                if ($SysPrefs->go_debug>1 && $type!=E_USER_NOTICE && $type!=E_USER_WARNING)
                    $str .= '<br>'.$msg[4];
                $content .= ($cnt ? '<hr>' : '').$str;
            }
            $class = $msg_class[$type];
            $content = "<div class='$class'>$content</div>";
        } elseif ($path_to_root=='.')
            return '';
        return $content;
    }

    public function errorBox(): void {
        echo "<div id='msgbox'>";

        $this->beforeBox = ob_get_clean();
        ob_start('\output_html');
        echo "</div>";
    }

    public function endFlush(): void {
        global $Ajax;

        if (isset($Ajax))
            $Ajax->run();

        while(ob_get_level() > 1)
            ob_end_flush();
        @ob_end_flush();

        \cancel_transaction();
    }

    public function displayDbError(?string $msg, ?string $sqlStatement = null, bool $exit = true): void {
        global $db, $SysPrefs, $db_connections;

        $warning = $msg === null;
        $db_error = db_error_no();
        
        if($warning)
            $str = "<b>" . _("Debug mode database warning:") . "</b><br>";
        else
            $str = "<b>" . _("DATABASE ERROR :") . "</b> $msg<br>";
        
        if ($db_error != 0) {
            $str .= "error code $db_error: " . db_error_msg() . "<br>";
        }
        
        if ($sqlStatement !== null) {
            $str .= "<b>SQL:</b> $sqlStatement<br>";
        }
        
        if ($SysPrefs->go_debug > 1) {
            $str .= get_backtrace(true, 1);
        }
        
        if ($warning) {
            trigger_error($str, E_USER_WARNING);
        } else {
            trigger_error($str, E_USER_ERROR);
        }
        
        if ($exit) {
            end_page();
            exit;
        }
    }

    public function checkDbError(string $msg, string $sqlStatement, bool $exitIfError = true, bool $rollbackIfError = true): void {
        $db_error = db_error_no();
        
        if ($db_error != 0) {
            if ($rollbackIfError) {
                \cancel_transaction();
            }
            $this->displayDbError($msg, $sqlStatement, $exitIfError);
        }
    }

    private function errorHandler(int $errno, string $errstr, string $file, int $line): bool {
        global $SysPrefs, $cur_error_level;

        $excluded_warnings = array(
            'html_entity_decode',
            'should be compatible with that',
            'mysql extension is deprecated'
        );
        foreach($excluded_warnings as $ref) {
            if (strpos($errstr, $ref) !== false) {
                return true;
            }
        }

        $bt = isset($SysPrefs) && $SysPrefs->go_debug > 1 ? $this->getBacktrace(true, 1) : '';

        if ($cur_error_level == error_reporting()) {
            if ($errno & $cur_error_level) {
                if (!in_array(array($errno, $errstr, $file, $line, $bt), $this->messages))
                    $this->messages[] = array($errno, $errstr, $file, $line, $bt);
            } elseif ($errno & ~E_NOTICE && $errstr != '') {
                $user = @$_SESSION["wa_current_user"]->loginname;
                $context = isset($SysPrefs) && !$SysPrefs->db_ok ? '[before upgrade]' : '';
                error_log(user_company() . ":$user:". basename($file) .":$line:$context $errstr");
            }
        }
        return true;
    }
}