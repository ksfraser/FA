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

/**
 * Controller for viewing sales dispatch notes
 *
 * Refactored using MVC pattern with dependency injection.
 * Separates business logic (Dispatch model) from view logic (ViewDispatch).
 * Uses OOP HTML rendering for maintainable, testable code.
 *
 * SOLID Principles:
 * - Single Responsibility: Handles dispatch viewing only
 * - Open/Closed: Can be extended for additional dispatch types
 * - Liskov Substitution: Compatible with FA controller interface
 * - Interface Segregation: Minimal, focused controller
 * - Dependency Inversion: Injects Dispatch into ViewDispatch
 *
 * DRY: Reuses model and view classes, avoids code duplication
 * TDD: Refactored with unit tests to prevent regressions
 *
 * UML Class Diagram:
 * +---------------------+
 * | view_dispatch.php  |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + (main logic)     |
 * +---------------------+
 *           |
 *           | instantiates
 *           v
 * +---------------------+
 * |   ViewDispatch     |
 * +---------------------+
 *           |
 *           | uses
 *           v
 * +---------------------+
 * |     Dispatch       |
 * +---------------------+
 *
 * @package FA
 */

$page_security = 'SA_SALESTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

use FA\ViewDispatch;
use Ksfraser\HTML\HtmlFragment;
use Ksfraser\HTML\Elements\HtmlRaw;
use Ksfraser\HTML\Elements\HtmlOB;

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 600);
page(_($help_context = "View Sales Dispatch"), true, false, "", $js);


if (isset($_GET["trans_no"]))
{
	$trans_id = $_GET["trans_no"];
}
elseif (isset($_POST["trans_no"]))
{
	$trans_id = $_POST["trans_no"];
}

// 3 different queries to get the information - what a JOKE !!!!

$myrow = get_customer_trans($trans_id, ST_CUSTDELIVERY);

$branch = get_branch($myrow["branch_code"]);

$sales_order = get_sales_order_header($myrow["order_"], ST_SALESORDER);

$view = new ViewDispatch(new \FA\Dispatch($trans_id));
echo $view->render();

end_page(true, false, false, ST_CUSTDELIVERY, $trans_id);

