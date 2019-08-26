<?php
/**********************************************************************
*	Based upon modules/CDNTAX/profit_loss.php
*	But modified for CDN tax use
*	Also using acct2 fields rather than 1
*		Assumption is that user has set up acct2 fields to be GIFA codes
*
*	Converting to OOP as I go along
*
**********************************************************************/

//$page_security = 'SA_CDNTAX';
$page_security = 'SA_GLANALYTIC';

//$path_to_faroot="../..";
$path_to_faroot= dirname ( realpath ( __FILE__ ) ) . "/../..";

include_once($path_to_faroot . "/includes/session.inc");

include_once($path_to_faroot . "/includes/date_functions.inc");
include_once($path_to_faroot . "/includes/ui.inc");
include_once($path_to_faroot . "/includes/data_checks.inc");

include_once($path_to_faroot . "/gl/includes/gl_db.inc");

$js = "";
if ($use_date_picker)
	$js = get_js_date_picker();

page(_($help_context = "CDN TAX Profit & Loss Drilldown"), false, false, "", $js);

//----------------------------------------------------------------------------------------------------
// Ajax updates

if (get_post('Show')) 
{
	$Ajax->activate('pl_tbl');
}

class cdntax_profitloss
{
	var $TransFromDate;
	var $TransToDate;
	var $Compare;
	var $AccGrp;
	var $code_per_balance;
	var $code_acc_balance;
	var $per_balance_total;
	var $acc_balance_total;
	var $totals_arr;
	var $parent;
	var $classper;
	var $classacc;
	var $salesper;
	var $salesacc;	
	var $levelptr;
	var $drilldown;
	var $gifi_balance_array;
	function __construct()
	{
		$this->code_per_balance = 0;
		$this->code_acc_balance = 0;
		$this->per_balance_total = 0;
		$this->acc_balance_total = 0;
		$this->totals_arr = array();
		$this->levelptr = 0;
		$this->drilldown = 0; // Root level
		$this->gifi_balance_array = array();

		$date = today();

		if (isset($_GET["TransFromDate"]))
		{
			$this->TransFromDate = $_GET["TransFromDate"];	
		}
		else
		{
			$this->TransFromDate = begin_fiscalyear();	
			//$this->TransFromDate = begin_month($date);
		}
		$_POST["TransFromDate"] = $this->TransFromDate;	

		if (isset($_GET["TransToDate"]))
		{
			$this->TransToDate = $_GET["TransToDate"];
		}
		else
		{
			$this->TransToDate = end_month($date);
		}
		$_POST["TransToDate"] = $this->TransToDate;

		if (isset($_GET["Compare"]))
		{
			$this->Compare = $_GET["Compare"];
		}
		else
		{
			$this->Compare = 1;
		}
		$_POST["Compare"] = $this->Compare;

		if (isset($_GET["AccGrp"]))
		{
			$this->AccGrp = $_GET["AccGrp"];
			$this->drilldown = 1; // Deeper Level
		}
		else
		{
			$this->AccGrp = NULL;
		}
		$_POST["AccGrp"] = $this->AccGrp;

	}
	function run()
	{
		start_form();
		$this->inquiry_controls();
		$this->display_profit_and_loss();
		end_form();
		end_page();
	}
	function get_gl_accounts($from=null, $to=null, $type=null)
	{
		return get_gl_accounts($from, $to, $type);
	}
	function get_gl_account($code)
	{
		//Using provided code
		return get_gl_account($code);
	}
	function get_gl_account2($code)
	{
		//Using GIFA account codes in account_code2
	        $sql = "SELECT * FROM ".TB_PREF."chart_master WHERE account_code2=".db_escape($code);
	        $result = db_query($sql, "could not get gl account2");
	        return db_fetch($result);
	}
	function get_account_types( $bool1, $bool2, $type )
	{
		return get_account_types( $bool1, $bool2, $type);
	}
	function gl_account_inquiry_url( $account_name, $account_code, $account_code2 = NULL )
	{
		global $path_to_faroot;
		if( !isset( $account_code2 ) )
			$account_code2 = $account_code;

		$url = "<a href='$path_to_faroot/gl/inquiry/gl_account_inquiry.php?TransFromDate=" 
			. $this->TransFromDate . "&TransToDate=" . $this->TransToDate 
			. "&account=" . $account_code . "'>" . $account_code2 
			." ". $account_name ."</a>";				
		return $url;
	}
	function profit_loss_url( $typename, $type )
	{
		global $path_to_faroot;
		$url = "<a href='$path_to_faroot/modules/CDNTAX/profit_loss.php?TransFromDate=" 
			. $this->TransFromDate . "&TransToDate=" . $this->TransToDate 
			. "&AccGrp=" . $type ."'>" . $type . " " . $typename ."</a>";
		return $url;
	}
	function get_GIFI_data( $account )
	{
			if( !isset( $this->gifi_balance_array[$account['account_code2']]['name'] ) )
				$this->gifi_balance_array[$account['account_code2']]['name'] = $account['account_name'];
			if( !isset( $this->gifi_balance_array[$account['account_code2']]['GL'] ) )
				$this->gifi_balance_array[$account['account_code2']]['GL'] = $account['account_code'];
			if( !isset( $this->gifi_balance_array[$account['account_code2']]['GIFI'] ) )
				$this->gifi_balance_array[$account['account_code2']]['GIFI'] = $account['account_code2'];
			$per_balance = get_gl_trans_from_to($this->TransFromDate, $this->TransToDate, $account["account_code"], 0, 0 );
			if( !isset( $this->gifi_balance_array[$account['account_code2']]['balance'] ) )
			{
				$this->gifi_balance_array[$account['account_code2']]['balance'] = $per_balance;
			}
			else
			{
				$this->gifi_balance_array[$account['account_code2']]['balance'] += $per_balance;
			}
		return $per_balance;
	}

//----------------------------------------------------------------------------------------------------

	function display_type ($type, $typename, $from, $to, $begin, $end, $compare, $convert,
		&$dec, &$pdec, &$rep, $path_to_faroot)
	{
		//Tax purposes should not have dimensions
		$dimension=0;
		$dimension2=0;
		global $k;
			
		//Get Accounts directly under this group/type
		$result = $this->get_gl_accounts(null, null, $type);	
			
		while ($account=db_fetch($result))
		{
			$per_balance = $this->get_GIFI_data( $account );

			if ($this->drilldown && $this->levelptr == 0)
			{
				$url = $this->gl_account_inquiry_url( $account['account_name'], $account['account_code'], $account['account_code2'] );
					
				start_row("class='stockmankobg'");
				label_cell($url);
				amount_cell($per_balance * $convert);
				end_row();
			}
				
			$this->code_per_balance += $per_balance;
		}
	
		$this->levelptr = 1;
		
		//Get Account groups/types under this group/type
		$result = $this->get_account_types(false, false, $type);
		while ($accounttype=db_fetch($result))
		{	
			$totals_arr = $this->display_type($accounttype["id"], $accounttype["name"], $this->TransFromDate, $this->TransToDate, $begin, $end, 
				NULL, $convert, $dec, $pdec, $rep, $path_to_faroot);
			$this->per_balance_total += $totals_arr[0];
			$this->acc_balance_total += $totals_arr[1];
		}
	
		if ($this->drilldown && $type == $this->AccGrp)
		{		
			start_row("class='inquirybg' style='font-weight:bold'");
			label_cell(_('Total') . " " . $typename);
			amount_cell(($this->code_per_balance + $this->per_balance_total) * $convert);
			end_row();
		}
		//START Patch#1 : Display  only direct child types
		$acctype1 = get_account_type($type);
		$parent1 = $acctype1["parent"];
		if ($this->drilldown && $parent1 == $this->AccGrp)
		{	
			$url = $this->profit_loss_url( $typename, $type );
			alt_table_row_color($k);
			label_cell($url);
			amount_cell(($this->code_per_balance + $this->per_balance_total) * $convert);
			end_row();
		}
		
		$totals_arr[0] = $this->code_per_balance + $this->per_balance_total;
		$totals_arr[1] = $this->code_acc_balance + $this->acc_balance_total;
		return $totals_arr;
	}	
		
	function Achieve($d1, $d2)
	{
		if ($d1 == 0 && $d2 == 0)
			return 0;
		elseif ($d2 == 0)
			return 999;
		$ret = ($d1 / $d2 * 100.0);
		if ($ret > 999)
			$ret = 999;
		return $ret;
	}
	
	function inquiry_controls()
	{  
		//$dim = get_company_pref('use_dimension');

	    	start_table(TABLESTYLE_NOBORDER);
	    	date_cells(_("From:"), 'TransFromDate');
		date_cells(_("To:"), 'TransToDate');
		submit_cells('Show',_("Show"),'','', 'default');
	    	end_table();
	
		hidden('AccGrp');
	}
	function print_class_header( $classname, $tableheader )
	{
		//Print Class Name	
		table_section_title( $classname, 4 );	
		echo $tableheader;
	}
	
	//----------------------------------------------------------------------------------------------------
	
	function display_profit_and_loss()
	{
		global $path_to_faroot, $sel;
		$dimension=0;
		$dimension2=0;
	
		$from = $this->TransFromDate;
		$to = $this->TransToDate;
		
		
		$dec = 0;
		$pdec = user_percent_dec();
		
		div_start('pl_tbl');
	
		start_table(TABLESTYLE, "width=50%");
		$tableheader =  "<tr> <td class='tableheader'>" . _("Group/Account Name") . "</td> <td class='tableheader'>" . _("Period") . "</td> </tr>";	
		
		if (!$this->drilldown) //Root Level
		{
			$this->parent = -1;
			$this->classper = 0.0;
			$this->classacc = 0.0;
			$this->salesper = 0.0;
			$this->salesacc = 0.0;	
		
			//Get classes for PL
			$classresult = get_account_classes(false, 0);
			while ($class = db_fetch($classresult))
			{
				$class_per_total = 0;
				$class_acc_total = 0;
				$convert = get_class_type_convert($class["ctype"]); 		
				
				//Print Class Name	
				$this->print_class_header( $class["class_name"], $tableheader );
				
				//Get Account groups/types under this group/type
				$typeresult = get_account_types(false, $class['cid'], -1);
				while ($accounttype=db_fetch($typeresult))
				{
					$TypeTotal = $this->display_type($accounttype["id"], $accounttype["name"], $this->TransFromDate, $this->TransToDate, begin_fiscalyear(), end_fiscalyear(), NULL, $convert, 
						$dec, $pdec, $rep,  $path_to_faroot);
					$class_per_total += $TypeTotal[0];
					$class_acc_total += $TypeTotal[1];	
	
					$url = "<a href='$path_to_faroot/modules/CDNTAX/profit_loss.php?TransFromDate=" 
						. $from . "&TransToDate=" 
						. "&AccGrp=" . $accounttype['id'] ."'>" . $accounttype['id'] . " " . $accounttype['name'] ."</a>";
						
					alt_table_row_color($k);
					label_cell($url);
					amount_cell($TypeTotal[0] * $convert);
					end_row();
				}
				
				//Print Class Summary
				
				start_row("class='inquirybg' style='font-weight:bold'");
				label_cell(_('Total') . " " . $class["class_name"]);
				amount_cell($class_per_total * $convert);
				end_row();			
				
				$this->salesper += $class_per_total;
				$this->salesacc += $class_acc_total;
			}
			
			start_row("class='inquirybg' style='font-weight:bold'");
			label_cell(_('Calculated Return'));
			amount_cell($this->salesper *-1);
			end_row();		
	
		}
		else 
		{
			//Drilled down past root level
			//Level Pointer : Global variable defined in order to control display of root 
			$this->levelptr = 0;
			
			$accounttype = get_account_type($this->AccGrp);
			$classid = $accounttype["class_id"];
			$class = get_account_class($classid);
			$convert = get_class_type_convert($class["ctype"]); 
			
			//Print Class Name	
			table_section_title($this->AccGrp . " " . get_account_type_name($this->AccGrp),4);	
			echo $tableheader;
			
			$classtotal = $this->display_type($accounttype["id"], $accounttype["name"], $this->TransFromDate, $this->TransToDate, begin_fiscalyear(), end_fiscalyear(), NULL, $convert, 
				$dec, $pdec, $rep,  $path_to_faroot);
			
		}
			
	
		end_table(1); // outer table
		div_end();
	}
}

//----------------------------------------------------------------------------------------------------

$g = new cdntax_profitloss();
$g->run();
?>
