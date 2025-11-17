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
$page_security = 'SA_SALESTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/sales/includes/sales_db.inc");

use FA\ViewCreditNote;
use Ksfraser\HTML\HtmlFragment;
use Ksfraser\HTML\Elements\HtmlRaw;
use Ksfraser\HTML\Elements\HtmlOB;
use FA\TaxDetailsView;
use FA\VoidedView;
use FA\AllocationsView;

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
page(_($help_context = "View Credit Note"), true, false, "", $js);

if (isset($_GET["trans_no"]))
{
	$trans_id = $_GET["trans_no"];
}
elseif (isset($_POST["trans_no"]))
{
	$trans_id = $_POST["trans_no"];
}

$myrow = get_customer_trans($trans_id, ST_CUSTCREDIT);

$branch = get_branch($myrow["branch_code"]);

$view = new ViewCreditNote(new \FA\CreditNote($trans_id));
echo $view->render();

$additional = new HtmlFragment();

// Tax details
$taxResult = get_trans_tax_details(ST_CUSTCREDIT, $trans_id);
$taxItems = [];
while ($taxItem = db_fetch($taxResult)) {
    $taxItems[] = $taxItem;
}
$taxView = new TaxDetailsView();
$additional->addChild($taxView->render($taxItems, 6));

// Voided check
$voidEntry = get_voided_entry(ST_CUSTCREDIT, $trans_id);
$voidedView = new VoidedView();
$voidedElement = $voidedView->render($voidEntry, _("This credit note has been voided."));
if ($voidedElement) {
    $additional->addChild($voidedElement);
}

// Allocations
if (!$voidEntry) { // If not voided
    $allocResult = get_allocatable_to_cust_transactions($myrow['debtor_no'], $trans_id, ST_CUSTCREDIT);
    $allocRows = [];
    while ($allocRow = db_fetch($allocResult)) {
        $allocRows[] = $allocRow;
    }
    $allocView = new AllocationsView();
    $additional->addChild($allocView->render($allocRows, $myrow["ov_freight"] + $myrow["ov_gst"] + $myrow["ov_amount"] + $myrow["ov_freight_tax"], _("Allocations")));
}

echo $additional->getHtml();

/* end of check to see that there was an invoice record to print */

end_page(true, false, false, ST_CUSTCREDIT, $trans_id);

