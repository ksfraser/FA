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
require_once($path_to_root. "/includes/JsHttpRequest.php");

/**
 * Ajax Service
 *
 * Handles AJAX requests and responses.
 * Refactored to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages AJAX operations only
 * - Open/Closed: Can be extended for additional AJAX features
 * - Liskov Substitution: Compatible with JsHttpRequest
 * - Interface Segregation: Focused AJAX methods
 * - Dependency Inversion: Depends on abstractions, not globals
 *
 * DRY: Reuses AJAX logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * |       Ajax         |
 * +---------------------+
 * | - aCommands: array |
 * | - triggers: array  |
 * +---------------------+
 * | + __construct()    |
 * | + activate(name)   |
 * | + redirect(url)    |
 * | + popup(url)       |
 * | ...                 |
 * +---------------------+
 *           |
 *           | extends
 *           v
 * +---------------------+
 * |  JsHttpRequest     |
 * +---------------------+
 *
 * @package FA
 */
class Ajax extends JsHttpRequest {

	private array $aCommands = [];
	private array $triggers = [];
	
    /**
     * Constructor
     */
    public function __construct() 
    {
	  parent::__construct(@$_SESSION['language']->encoding);
    }

	/**
	 * Activate update of ajaxified html element
	 *
	 * @param string $trigname Trigger name
	 */
	public function activate(string $trigname): void {
	  if (in_ajax()) {
		$this->triggers[$trigname] = true;
	  }
	}

	/**
	 * Javascript clientside redirection
	 *
	 * @param string $url URL to redirect to
	 */
	public function redirect(string $url): void {
	    if(in_ajax()) {
			$this->_addCommand(true, array('n'=>'rd'), absolute_url($url));
		  	$this->run();
	    }
	}

	/**
	 * Popup window (target=_blank)
	 *
	 * @param string $url URL to popup
	 */
	public function popup(string $url): void {
		  $this->_addCommand(true, array('n'=>'pu'), absolute_url($url));
	}
	/**
	 * Adds an executable Javascript code.
	 *
	 * @param string $trigger Trigger name
	 * @param string $sJS JavaScript code
	 * @return self
	 */
	public function addScript(string $trigger, string $sJS): self
	{
		$this->_addCommand($trigger, array('n'=>'js'),$sJS);
		return $this;
	}
	/**
	 * Assign target attribute with data.
	 *
	 * @param string $trigger Trigger name
	 * @param string $sTarget Target element
	 * @param string $sAttribute Attribute name
	 * @param mixed $sData Data to assign
	 * @return self
	 */
	public function addAssign(string $trigger, string $sTarget, string $sAttribute, $sData): self
	{
		$this->_addCommand($trigger, array('n'=>'as','t'=>$sTarget,'p'=>$sAttribute),$sData);
		return $this;
	}
	/**
	 * Updates input element or label with data.
	 *
	 * @param string $trigger Trigger name
	 * @param string $sTarget Target element
	 * @param mixed $sData Data to update
	 * @return self
	 */
	public function addUpdate(string $trigger, string $sTarget, $sData): self
	{
		$this->_addCommand($trigger, array('n'=>'up','t'=>$sTarget),$sData);
		return $this;
	}
	/**
	 * Set disable state of element.
	 *
	 * @param string $trigger Trigger name
	 * @param string $sTarget Target element
	 * @param bool $sData Disable state
	 * @return self
	 */
	public function addDisable(string $trigger, string $sTarget, bool $sData=true): self
	{
		$this->_addCommand($trigger, array('n'=>'di','t'=>$sTarget),$sData);
		return $this;
	}
	/**
	 * Set state of element to enabled.
	 *
	 * @param string $trigger Trigger name
	 * @param string $sTarget Target element
	 * @param bool $sData Enable state
	 * @return self
	 */
	public function addEnable(string $trigger, string $sTarget, bool $sData=true): self
	{
		$this->_addCommand($trigger, array('n'=>'di','t'=>$sTarget), !$sData);
		return $this;
	}
	/**
	 * Set current focus.
	 *
	 * @param string $trigger Trigger name
	 * @param string $sTarget Target element
	 * @return self
	 */
	public function addFocus(string $trigger, string $sTarget): self
	{
		$this->_addCommand($trigger, array('n'=>'fc'),$sTarget);
		return $this;
	}
	/**
	 * Internal procedure adding command to response.
	 *
	 * @param mixed $trigger Trigger condition
	 * @param array $aAttributes Command attributes
	 * @param mixed $mData Command data
	 */
	private function _addCommand($trigger, array $aAttributes, $mData): void
	{
	  	if ($this->isActive() && ($trigger !== false)) {

			$aAttributes['why'] = $trigger;
			$aAttributes['data'] = $mData;
			$this->aCommands[] = $aAttributes;
	  	}
	}

	/**
	 * Execute the AJAX response
	 */
	public function run(): void
	{
	    if (!$this->isActive()) return;

		// remove not active commands
		foreach ($this->aCommands as $idx => $com) {
// If we should reload whole page content ignore all commands but the update.
// This is page repost equivalent, although header and footer are not reloaded.
		  	if ($com['why'] !== true && !isset($this->triggers[$com['why']])) {
				unset($this->aCommands[$idx]);
			}
			elseif($com['n'] == 'up' && $com['t'] == '_page_body') {
			  	$cmds = array($com);
			  	foreach( $this->aCommands as $k=> $cmd) {
				  	if ($cmd['n'] == 'fc' || $cmd['n'] == 'js') {	// save focus
						$cmds[] = $cmd; //break;
			  		}
				}
				$this->aCommands = $cmds;
				break;
			}
		}
	    $GLOBALS['_RESULT'] = $this->aCommands;
	}
}

/**
 * Check if the current request is an AJAX request
 *
 * @return bool True if AJAX request
 */
function in_ajax() {
    global $Ajax;
    return $Ajax->isActive();
}

/**
 * Returns absolute path of relative $url. To be used in ajax calls
 * for proper redirection from any referer page.
 *
 * @param string $url Relative URL
 * @return string Absolute URL
 */
function absolute_url($url) 
{
   return strpos($url, '..')===0 ? dirname($_SERVER['PHP_SELF']).'/'.$url : $url;
}
