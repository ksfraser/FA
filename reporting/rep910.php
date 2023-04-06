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


$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ?
	'SA_SALESTRANSVIEW' : 'SA_SALESBULKREP';
// ----------------------------------------------------------------
// $ Revision:	1.0 $
// Creator:	Kevin Fraser      
// date_:	2023-04-04
// Title:	Print Mailing Information
//	This started as report 110
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/reporting/class.ksfFrontReport.php");

//----------------------------------------------------------------------------------------------------

print_mailing();

/**
 * Paramaters 0 and 1 give us a FROM and TO range.  This will allow us
 * to add this as  a report in other menus permitted batch printing of
 * shipping labels.  This will be useful on importing orders from WC etc.
 *
 */

//----------------------------------------------------------------------------------------------------

function print_mailing()
{
	global $path_to_root;

	include_once($path_to_root . "/reporting/includes/pdf_report.inc");
//When called post adding a different document than delivery (i.e. Direct Sales Invoice)
//the from and to (Param 0 and 1) could/will be wrong.  We need to check the doc type and
//backtrack the DELIVERY numbers

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	//$email = $_POST['PARAM_2'];
	$comments = $_POST['PARAM_4'];
	$orientation = $_POST['PARAM_5'];

	if (!$from || !$to) return;

	$orientation = ($orientation ? 'L' : 'P');

	$fno = explode("-", $from);
	$tno = explode("-", $to);
	$from = min($fno[0], $tno[0]);
	$to = max($fno[0], $tno[0]);

	$cols = array(4, 60, 225, 300, 325, 385, 450, 515);
	$aligns = array('left',	'left',	'right', 'left', 'right', 'right', 'right');

	$params = array('comments' => $comments);

	//$cur = get_company_Pref('curr_default');
						//title, filename, size, font, orientation, margins
			$rep = new ksfFrontReport(_('Mailing'), "MailingBulk", user_pagesize(), 10, 'L' );
//Taking out check of orientation, and setting to Landscape
	recalculate_cols($cols);
	for ($i = $from; $i <= $to; $i++)
	{
			if (!exists_customer_trans(ST_CUSTDELIVERY, $i))
				continue;
			$myrow = get_customer_trans($i, ST_CUSTDELIVERY);
			$branch = get_branch($myrow["branch_code"]);
			$sales_order = get_sales_order_header($myrow["order_"], ST_SALESORDER); // ?
			$rep->SetHeaderType('Header4');
				//NewPage calls the function specified by SetHeaderType
			//$rep->currency = $cur;
			$rep->Font();
			$rep->Info($params, $cols, null, $aligns);

			$contacts = get_branch_contacts($branch['branch_code'], 'delivery', $branch['debtor_no'], true);
			$rep->SetCommonData($myrow, $branch, $sales_order, '', ST_CUSTDELIVERY, $contacts);
			$rep->NewPage();

			$memo = get_comments_string(ST_CUSTDELIVERY, $i);
			if ($memo != "")
			{
				$rep->NewLine();
				$rep->TextColLines(1, 5, $memo, -2);
			}


    		$rep->row = $rep->bottomMargin + (15 * $rep->lineHeight);
			$doctype=ST_CUSTDELIVERY;
	}
	$rep->End();
}

?>
