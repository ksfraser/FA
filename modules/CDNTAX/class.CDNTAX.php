<?php

global $path_to_common;
//$path_to_common = "../ksf_modules_common/";
$path_to_common = "/mnt/2/development/var/www/html/fhs/frontaccounting/modules/ksf_modules_common/";

require_once( $path_to_common . '/class.CONTROLLER.php' );

/**************************************************************************
*
*	CONTROLLER
*
**************************************************************************/

class CDNTAX extends controller
{
		var $partnershipname;		//label' => 'Partnership Name' );
		var $partnershipacct;		//label' => 'Partnership Account Number' );
		var $operatingname;		//label' => 'Operating Name' );
		var $operationdesc;		//label' => 'Operation Description' );
		var $fiscalyearend;		//label' => 'Fiscal Year End YYYY-MM-DD' );
		var $fiscalyearstart;		//label' => 'Fiscal Year End YYYY-MM-DD' );
		var $p_095profdesignation;		//label' => 'Accountant has professional designation YN' );
		var $p_097acctconnected;		//label' => 'Is the Accountant connected to the Partnership YN' );
		var $p_101notesfinstatement;		//label' => 'Were notes to the financial statement completed YN' );
		var $p_104subsequentevents;		//label' => 'Are subsequent events mentioned in the notes? YN' );
		var $p_200fairvalue;
		//var $p_8299;		//label' => 'Total Revenue' );
		//var $p_9368;		//label' => 'Total Expense' );
		var $p_7000;		//label' => 'Revaluation Surplus' );
		var $p_7002;		//label' => 'Defined Benefit GainLoss' );
		var $p_7004;		//label' => 'Foreign Operation GainLoss' );
		var $p_7006;		//label' => 'Equity Instuments GainLoss' );
		var $p_7008;		//label' => 'Cash Flow Hedge GainLoss' );
		var $p_7010;		//label' => 'Income Tax non Comprehensive income' );
		var $p_7020;		//label' => 'Misc other comprehensive Income' );
		var $p_9970;		//label' => '*' );
		var $p_9998;		//label' => 'Total Comprehensive Income' );
		var $p_9999;		//label' => '*Net Income after Tax' );
		var $p_2599;		//label' => '*Total Assets' );
		var $p_3499;		//label' => '*Total Liabilities' );
		var $p_3580;		//label' => '*' );
		var $p_3600;		//label' => '*Retained Earnings' );
		var $p_3630;		//label' => '*' );
		var $p_3849;		//label' => '*Retained Earnings' );
		var $p_8000;		//Adjusted Gross Sales
		var $p_8290;		//Reserve deducted last year
		var $p_8230;		//Other Income
		var $p_2125A;
		var $p_2125Ai;
		var $p_2125B;
		var $p_2125Bii;
		var $p_2125Biii;
		var $p_2125Biv;
		var $p_2125C;
		var $p_2125_H;		//sum $_8290 and $_8230
		var $p_8299;		//Gross income = sum $_8000 and $2125_H
		var $p_8300;		//Opening Inventory
		var $p_8320;		//Purcahses during the year
		var $p_8340;		//Direct Wage cost
		var $p_8360;		//subcontracts
		var $p_8450;		//Other costs
		var $p_8500;		//closing inventory
		var $p_8518;		//COGS (8300+8320+8340+8360+8450-8500)
		var $p_8519;		//Gross Profit (8299-8518)
		var $p_8521;		//Advertising
		var $p_8523;		//Meals (allowable portion)
		var $p_8590;		//Bad Debt
		var $p_8690;		//Insurance
		var $p_8710;		//Interest
		var $p_8760;		//Business Fees, licenses, dues,
		var $p_8810;		//Office Expense
		var $p_8811;		//Office Supplies
		var $p_8860;		//Professional fees (legal, accounting,)
		var $p_8871;		//Management fees
		var $p_8910;		//Rent
		var $p_8960;		//Maintenance and repairs
		var $p_9060;		//Wages, Salary, Contributions
		var $p_9180;		//Property Tax
		var $p_9200;		//Travel
		var $p_9220;		//Telephone + utilities
		var $p_9224;		//Fuel except auto
		var $p_9275;		//Delivery, Freight
		var $p_9281;		//Motor Vehicle Expenses except CCA
		var $p_9935;		//Allowance on Capital
		var $p_9936;		//CCA
		var $p_9270;		//Other Expense
		var $p_9270Type;		//Specify what Other is
		var $p_9368;		//TOTAL Business Expense - sum 8521 through 9270
		var $p_9369;		//Net income (8519 - 9368)
		var $p_9974;		//GST rebate received
		var $p_9931;		//Total Business Liabilities
		var $p_9932;		//Total Drawings
		var $p_9933;		//Total Capital Contribution
		var $config_values_T5013FIN;
		var $T5013Sched1_values;
		var $T5013Sched2_values;
		var $T5013Sched5_values;
		var $T5013Sched6_values;
		var $T5013Sched8_values;
		var $T5013Sched9_values;
		var $T5013Sched10_values;
		var $T5013Sched12_values;
		var $T5013Sched50_values;
		var $T5013Sched52_values;
		var $T5013Sched100_values;
		var $T5013Sched125_values;
		var $T5013Sched141_values;
		var $GST34_101;
		var $GST34_102;
		var $GST34_103;
		var $GST34_104;
		var $GST34_105;
		var $GST34_106;
		var $GST34_107;
		var $GST34_108;
		var $GST34_109;
		var $GST34_110;
		var $GST34_111;
		var $GST34_112;
		var $GST34_113a;
		var $GST34_113b;
		var $GST34_113c;
		var $GST34_114;
		var $GST34_115;
		var $GST34_205;
		var $GST34_405;
		var $ccaclass1num;
		var $ccaclass1UCCstart;
		var $ccaclass1Addition;
		var $ccaclass1disposition;
		var $ccaclass1totalUCC;
		var $ccaclass1adjustment;
		var $ccaclass1baseamount;
		var $ccaclass1rate;
		var $ccaclass1CCA;
		var $ccaclass1UCCend;
		var $ccaclass2num;
		var $ccaclass2UCCstart;
		var $ccaclass2Addition;
		var $ccaclass2disposition;
		var $ccaclass2totalUCC;
		var $ccaclass2adjustment;
		var $ccaclass2baseamount;
		var $ccaclass2rate;
		var $ccaclass2CCA;
		var $ccaclass2UCCend;
		var $ccaclass3num;
		var $ccaclass3UCCstart;
		var $ccaclass3Addition;
		var $ccaclass3disposition;
		var $ccaclass3totalUCC;
		var $ccaclass3adjustment;
		var $ccaclass3baseamount;
		var $ccaclass3rate;
		var $ccaclass3CCA;
		var $ccaclass3UCCend;
		var $ccaclass4num;
		var $ccaclass4UCCstart;
		var $ccaclass4Addition;
		var $ccaclass4disposition;
		var $ccaclass4totalUCC;
		var $ccaclass4adjustment;
		var $ccaclass4baseamount;
		var $ccaclass4rate;
		var $ccaclass4CCA;
		var $ccaclass4UCCend;
		var $p_9925;
		var $p_9926;
		var $p_9927;
		var $p_9928;
		var $p_9923;
		var $p_9924;
		var $internetsitecount;
		var $internetsiteurl1;
		var $internetsiteurl2;
		var $internetsiteurl3;
		var $internetsiteurl4;
		var $internetsiteurl5;
		var $internetsitepercent;
		var $partner1name;
		var $partner1addr;
		var $partner1percent;
		var $partner1share;
		var $partner2name;
		var $partner2addr;
		var $partner2percent;
		var $partner2share;
		var $partner3name;
		var $partner3addr;
		var $partner3percent;
		var $partner3share;
		var $partner4name;
		var $partner4addr;
		var $partner4percent;
		var $partner4share;
	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		//var_dump( $_POST );


   		//$this->mode_callbacks["unknown"] = "config_form";
   		//$this->mode_callbacks["T5013FIN"] = "T5013FIN_form";

               // $this->config_values[] = array( 'pref_name' => 'mode', 'label' => 'Mode' );
               
		$this->config_values[] = array( 'pref_name' => 'partnershipname', 'label' => 'Partnership Name' );
		$this->config_values[] = array( 'pref_name' => 'partnershipacct', 'label' => 'Partnership Account Number' );
		$this->config_values[] = array( 'pref_name' => 'operatingname', 'label' => 'Operating Name' );
		$this->config_values[] = array( 'pref_name' => 'operationdesc', 'label' => 'Operation Description' );
		$this->config_values[] = array( 'pref_name' => 'fiscalyearstart', 'label' => 'Fiscal Year Begin YYYY-MM-DD' );
		$this->config_values[] = array( 'pref_name' => 'fiscalyearend', 'label' => 'Fiscal Year End YYYY-MM-DD' );
		$this->config_values[] = array( 'pref_name' => 'p_095profdesignation', 'label' => 'Accountant has professional designation YN' );
		$this->config_values[] = array( 'pref_name' => 'p_097acctconnected', 'label' => 'Is the Accountant connected to the Partnership YN' );
		$this->config_values[] = array( 'pref_name' => 'p_101notesfinstatement', 'label' => 'Were notes to the financial statement completed YN' );
		$this->config_values[] = array( 'pref_name' => 'internetsitecount', 'label' => 'How many websites foes your business earn income from?' );
		$this->config_values[] = array( 'pref_name' => 'internetsiteurl1', 'label' => 'website URL 1' );
		$this->config_values[] = array( 'pref_name' => 'internetsiteurl2', 'label' => 'website URL 2' );
		$this->config_values[] = array( 'pref_name' => 'internetsiteurl3', 'label' => 'website URL 3' );
		$this->config_values[] = array( 'pref_name' => 'internetsiteurl4', 'label' => 'website URL 4' );
		$this->config_values[] = array( 'pref_name' => 'internetsiteurl5', 'label' => 'website URL 5' );
		$this->config_values[] = array( 'pref_name' => 'internetsitepercent', 'label' => 'Percentage of gross income from websites' );
		$this->config_values[] = array( 'pref_name' => 'partner1name', 'label' => 'Name of Partner 1' );
		$this->config_values[] = array( 'pref_name' => 'partner1addr', 'label' => 'Address of Partner 1' );
		$this->config_values[] = array( 'pref_name' => 'partner1percent', 'label' => 'Partner 1 Percentage' );
		$this->config_values[] = array( 'pref_name' => 'partner1share', 'label' => 'Share of net income for Partner 1' );
		$this->config_values[] = array( 'pref_name' => 'partner2name', 'label' => 'Name of Partner 2' );
		$this->config_values[] = array( 'pref_name' => 'partner2addr', 'label' => 'Address of Partner 2' );
		$this->config_values[] = array( 'pref_name' => 'partner2percent', 'label' => 'Partner 2 Percentage' );
		$this->config_values[] = array( 'pref_name' => 'partner2share', 'label' => 'Share of net income for Partner 2' );
		$this->config_values[] = array( 'pref_name' => 'partner3name', 'label' => 'Name of Partner 3' );
		$this->config_values[] = array( 'pref_name' => 'partner3addr', 'label' => 'Address of Partner 3' );
		$this->config_values[] = array( 'pref_name' => 'partner3percent', 'label' => 'Partner 3 Percentage' );
		$this->config_values[] = array( 'pref_name' => 'partner3share', 'label' => 'Share of net income for Partner 3' );
		$this->config_values[] = array( 'pref_name' => 'partner4name', 'label' => 'Name of Partner 4' );
		$this->config_values[] = array( 'pref_name' => 'partner4addr', 'label' => 'Address of Partner 4' );
		$this->config_values[] = array( 'pref_name' => 'partner4percent', 'label' => 'Partner 4 Percentage' );
		$this->config_values[] = array( 'pref_name' => 'partner4share', 'label' => 'Share of net income for Partner 4' );


		$this->config_values[] = array( 'pref_name' => 'ccaclass1num', 'label' => '1 CCA Class Number' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass1UCCstart', 'label' => '2 Undepreciated UCC at year start' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass1Addition', 'label' => '3 Cost of additions during year' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass1disposition', 'label' => '4 Proceeds from disposition during year' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass1totalUCC', 'label' => '5 Total UCC after Add/Disposition (2+3-4)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass1adjustment', 'label' => '6 Adjusment for current year ((3-4)*.5)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass1baseamount', 'label' => '7 Base amount for CCA (5-6)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass1rate', 'label' => '8 Rate' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass1CCA', 'label' => '9 CCA for year (7*8)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass1UCCend', 'label' => '10 UCC remaining at end of year (5-9)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass2num', 'label' => '1 CCA Class Number' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass2UCCstart', 'label' => '2 Undepreciated UCC at year start' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass2Addition', 'label' => '3 Cost of additions during year' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass2disposition', 'label' => '4 Proceeds from disposition during year' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass2totalUCC', 'label' => '5 Total UCC after Add/Disposition (2+3-4)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass2adjustment', 'label' => '6 Adjusment for current year ((3-4)*.5)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass2baseamount', 'label' => '7 Base amount for CCA (5-6)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass2rate', 'label' => '8 Rate' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass2CCA', 'label' => '9 CCA for year (7*8)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass2UCCend', 'label' => '10 UCC remaining at end of year (5-9)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass3num', 'label' => '1 CCA Class Number' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass3UCCstart', 'label' => '2 Undepreciated UCC at year start' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass3Addition', 'label' => '3 Cost of additions during year' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass3disposition', 'label' => '4 Proceeds from disposition during year' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass3totalUCC', 'label' => '5 Total UCC after Add/Disposition (2+3-4)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass3adjustment', 'label' => '6 Adjusment for current year ((3-4)*.5)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass3baseamount', 'label' => '7 Base amount for CCA (5-6)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass3rate', 'label' => '8 Rate' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass3CCA', 'label' => '9 CCA for year (7*8)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass3UCCend', 'label' => '10 UCC remaining at end of year (5-9)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass4num', 'label' => '1 CCA Class Number' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass4UCCstart', 'label' => '2 Undepreciated UCC at year start' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass4Addition', 'label' => '3 Cost of additions during year' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass4disposition', 'label' => '4 Proceeds from disposition during year' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass4totalUCC', 'label' => '5 Total UCC after Add/Disposition (2+3-4)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass4adjustment', 'label' => '6 Adjusment for current year ((3-4)*.5)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass4baseamount', 'label' => '7 Base amount for CCA (5-6)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass4rate', 'label' => '8 Rate' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass4CCA', 'label' => '9 CCA for year (7*8)' );
		$this->config_values[] = array( 'pref_name' => 'ccaclass4UCCend', 'label' => '10 UCC remaining at end of year (5-9)' );
		$this->config_values[] = array( 'pref_name' => 'p_9925', 'label' => 'Total Equipment additions' );
		$this->config_values[] = array( 'pref_name' => 'p_9926', 'label' => 'Total Equipment dispositions' );
		$this->config_values[] = array( 'pref_name' => 'p_9927', 'label' => 'Total Building additions' );
		$this->config_values[] = array( 'pref_name' => 'p_9928', 'label' => 'Total Building dispositions' );
		$this->config_values[] = array( 'pref_name' => 'p_9923', 'label' => 'Total Land additions' );
		$this->config_values[] = array( 'pref_name' => 'p_9924', 'label' => 'Total Land dispositions' );



		$this->config_values[] = array( 'pref_name' => 'p_104subsequentevents', 'label' => 'Are subsequent events mentioned in the notes? YN' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershipname', 'label' => 'Partnership Name' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershipacct', 'label' => 'Partnership Account Number' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'operatingname', 'label' => 'Operating Name' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'operationdesc', 'label' => 'Operation Description' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'headofficelocation', 'label' => 'Has head office location changed? YN', 'type' => 'bool' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'headaddress1', 'label' => 'Head Office Address 1', 'type' => 'addr' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'headaddress2', 'label' => 'Head Office Address 2', 'type' => 'addr' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'headcity', 'label' => 'Head Office City', 'type' => 'city' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'headprov', 'label' => 'Head Office Province', 'type' => 'prov' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'headcountry', 'label' => 'Head Office Country', 'type' => 'addr' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'headpostal', 'label' => 'Head Office Postal Code', 'type' => 'postal' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershiplocation', 'label' => 'Has Partnership location changed? YN', 'type' => 'bool' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershipcareof', 'label' => 'Partnership Care Of', 'type' => 'text' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershipaddress1', 'label' => 'Partnership Address 1', 'type' => 'addr' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershipaddress2', 'label' => 'Partnership Address 2', 'type' => 'addr' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershipcity', 'label' => 'Partnership City', 'type' => 'city' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershipprov', 'label' => 'Partnership Province', 'type' => 'prov' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershipcountry', 'label' => 'Partnership Country', 'type' => 'addr' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershippostal', 'label' => 'Partnership Postal Code', 'type' => 'postal' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'amendedfiling', 'label' => 'Is this an amended return? YN', 'type' => 'bool' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'fiscalstart', 'label' => 'Fiscal period start? YYYYMMDD', 'type' => 'date' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'fiscalyearend', 'label' => 'Fiscal period end? YYYYMMDD', 'type' => 'date' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'firstfiling', 'label' => 'Is this the first year of filing? YN', 'type' => 'bool' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershipstart', 'label' => 'Partnership start? YYYYMMDD', 'type' => 'date' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'numberofslips', 'label' => 'Number of slips filed?', 'type' => 'int' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'finalfiling', 'label' => 'Is this the final filing? YN', 'type' => 'bool' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'election261', 'label' => 'Election 261 currency', 'type' => 'currency' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershipcdnresident', 'label' => 'Is the partnership a Canadian Residence? YN', 'type' => 'bool' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'residentcountry', 'label' => 'What country is the partnership a residence of?', 'type' => 'country' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershiptype1', 'label' => 'Type of Partnership? General', 'type' => 'flag' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershiptype2', 'label' => 'Type of Partnership? Limited', 'type' => 'flag' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershiptype3', 'label' => 'Type of Partnership? Limited Liability', 'type' => 'flag' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershiptype8', 'label' => 'Type of Partnership? Investment Club', 'type' => 'flag' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershiptype11', 'label' => 'Type of Partnership? Tax Shelter GP', 'type' => 'flag' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershiptype12', 'label' => 'Type of Partnership? TS LP', 'type' => 'flag' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershiptype13', 'label' => 'Type of Partnership? TS Co-ownership', 'type' => 'flag' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'partnershiptype19', 'label' => 'Type of Partnership? TS Other', 'type' => 'flag' );
		$this->config_values_T5013FIN[] = array( 'pref_name' => 'shelternumber', 'label' => 'Tax Shelter Identification Nuymber', 'type' => 'int' );

		$this->config_values_T619[] = array( 'pref_name' => 'sbmt_ref_id', 'label' => 'Required 8 alphanumeric ', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'rpt_tcd', 'label' => 'Required 1 alpha - Original = O - Amended = A', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'trnmtr_nbr', 'label' => 'required MM + 6 numeric, example: MM555555 ', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'trnmtr_tcd', 'label' => 'Transmitter type indicator 1 numeric - 1 if you are submitting your returns - 2 if you are submitting returns for others (service providers) - 3 if you are submitting your returns using a purchased software package - 4 if you are a software vendor ', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'summ_cnt', 'label' => 'Total number of summary records - Required 6 numeric', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'lang_cd', 'label' => 'Language of communication indicator - Required 1 alpha - E = English - F = French', 'type' => 'text' );
		//TRNMTR_NM
		$this->config_values_T619[] = array( 'pref_name' => 'l1_nm', 'label' => 'Transmitter name - line 1 -Required 30 alphanumeric', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'l2_nm', 'label' => 'Transmitter name - line 2 - 30 alphanumeric', 'type' => 'text' );
		//TRNMTR_ADDR
		$this->config_values_T619[] = array( 'pref_name' => 'addr_l1_txt', 'label' => 'Transmitter address - line 1 - 30 alphanumeric ', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'addr_l2_txt', 'label' => 'Transmitter address - line 2 - 30 alphanumeric ', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'cty_nm', 'label' => 'Transmitter city - Required 28 alphanumeric ', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'prov_cd', 'label' => 'Transmitter province or territory code - Required 2 alpha', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'cntry_cd', 'label' => 'Transmitter country code - CAN for Canada ISO3166', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'pstl_cd', 'label' => 'Transmitter postal code - Required 10 alphanumeric ', 'type' => 'text' );
		//CNTC
		$this->config_values_T619[] = array( 'pref_name' => 'cntc_nm', 'label' => 'Contact name - Required 22 alphanumeric ', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'cntc_area_cd', 'label' => 'Contact area code - Required 3 numeric ', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'cntc_phn_nbr', 'label' => 'Contact telephone number - Required 3 numeric, followed by (-) and 4 numeric', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'cntc_extn_nbr', 'label' => 'Contact extension number 5 numeric  ', 'type' => 'text' );
		$this->config_values_T619[] = array( 'pref_name' => 'cntc_email_area', 'label' => 'Contact e-mail address Required- 60 alphanumeric ', 'type' => 'text' );


		$this->loadprefs( $this->config_values_T5013FIN );

		$this->config_values[] = array( 'pref_name' => '_200fairvalue', 'label' => 'In any of the following assets, was an amount recognized in net income or other comprehensive income as a result of an impairment loss in the fiscal period, a reversal of an impairment loss recognized in a previous fiscal period, or a change in fair value during the fiscal period?' );

		//Schedule 125 Income Statement
		$this->config_values[] = array( 'pref_name' => 'p_8299', 'label' => 'Total Revenue' );
		$this->config_values[] = array( 'pref_name' => 'p_9368', 'label' => 'Total Expense' );
		$this->config_values[] = array( 'pref_name' => 'p_7000', 'label' => 'Revaluation Surplus' );
		$this->config_values[] = array( 'pref_name' => 'p_7002', 'label' => 'Defined Benefit GainLoss' );
		$this->config_values[] = array( 'pref_name' => 'p_7004', 'label' => 'Foreign Operation GainLoss' );
		$this->config_values[] = array( 'pref_name' => 'p_7006', 'label' => 'Equity Instuments GainLoss' );
		$this->config_values[] = array( 'pref_name' => 'p_7008', 'label' => 'Cash Flow Hedge GainLoss' );
		$this->config_values[] = array( 'pref_name' => 'p_7010', 'label' => 'Income Tax non Comprehensive income' );
		$this->config_values[] = array( 'pref_name' => 'p_7020', 'label' => 'Misc other comprehensive Income' );
		$this->config_values[] = array( 'pref_name' => 'p_9970', 'label' => '*' );
		$this->config_values[] = array( 'pref_name' => 'p_9998', 'label' => 'Total Comprehensive Income' );
		$this->config_values[] = array( 'pref_name' => 'p_9999', 'label' => '*Net Income after Tax' );

		//Balance Sheet Schedule 100
		$this->config_values[] = array( 'pref_name' => 'p_2599', 'label' => '*Total Assets' );
		$this->config_values[] = array( 'pref_name' => 'p_3499', 'label' => '*Total Liabilities' );
		$this->config_values[] = array( 'pref_name' => 'p_3580', 'label' => '*' );
		$this->config_values[] = array( 'pref_name' => 'p_3600', 'label' => '*Retained Earnings' );
		$this->config_values[] = array( 'pref_name' => 'p_3630', 'label' => '*' );
		$this->config_values[] = array( 'pref_name' => 'p_3849', 'label' => '*Retained Earnings' );

		$this->config_values_T5013FIN[] = array( 'pref_name' => 'p_3849', 'label' => '*Retained Earnings' );

                //The forms/actions for this module
                //Hidden tabs are just action handlers, without accompying GUI elements.
                //$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
                //$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'config_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'Instructions', 'action' => 'instructions', 'form' => 'instructions_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 FIN', 'action' => 'T5013FIN', 'form' => 'T5013FIN_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 1', 'action' => 'sched1', 'form' => 'T5013sched1_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 2', 'action' => 'sched2', 'form' => 'T5013sched2_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 5', 'action' => 'sched5', 'form' => 'T5013sched5_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 6', 'action' => 'sched6', 'form' => 'T5013sched6_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 8', 'action' => 'sched8', 'form' => 'T5013sched8_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 9', 'action' => 'sched9', 'form' => 'T5013sched9_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 10', 'action' => 'sched10', 'form' => 'T5013sched10_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 12', 'action' => 'sched12', 'form' => 'T5013sched12_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 50', 'action' => 'sched50', 'form' => 'T5013sched50_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 52', 'action' => 'sched52', 'form' => 'T5013sched52_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 100', 'action' => 'sched100', 'form' => 'T5013sched100_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 125', 'action' => 'sched125', 'form' => 'T5013sched125_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T5013 Schedule 141', 'action' => 'sched141', 'form' => 'T5013sched141_form', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'T2125', 'action' => 'T2125', 'form' => 'T2125_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'updateprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update5013', 'form' => 'updatevalues', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'T2125 Updated', 'action' => 'update2125', 'form' => 'updatevalues', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Grab GL Totals', 'action' => 'grabgltotals', 'form' => 'grabgltotals', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Calc GL Sums', 'action' => 'calcglsums', 'form' => 'calcglsums', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Calc GST', 'action' => 'calcgst', 'form' => 'calcGST', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'GST Return Form GST34', 'action' => 'GST34', 'form' => 'GST34_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'GST Updated', 'action' => 'updateGST', 'form' => 'updateGST', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Grab GL Sums', 'action' => 'grabglsums', 'form' => 'grabglsums', 'hidden' => TRUE );

		$this->GST34_values[] = array( 'pref_name' => 'GST34_101', 'label' => '101 Sales and Revenue' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_103', 'label' => '103 GST Collected' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_104', 'label' => '104 GST Recovered' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_105', 'label' => '105 Total GST In' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_106', 'label' => '106 GST Paid' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_107', 'label' => '107 GST Lost' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_108', 'label' => '108 Total GST ITC' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_109', 'label' => '109 Net GST' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_110', 'label' => '110 GST Installments Paid' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_111', 'label' => '111 GST Rebates' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_112', 'label' => '112 Total GST Credits' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_113a', 'label' => '113a Balance' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_205', 'label' => '205 GST on Real Property' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_405', 'label' => '405 GST self assessed' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_113b', 'label' => '113b Debit Balance' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_113c', 'label' => '113c Balance' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_114', 'label' => 'Refund Claimed' );
		$this->GST34_values[] = array( 'pref_name' => 'GST34_115', 'label' => 'Amount Owing' );

		//Part 1 Business Income
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_A', 'label' => '2125A Gross Sales including taxes' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_Ai', 'label' => '2125Ai Taxes and Adjustments' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_B', 'label' => '2125B Subtotal A - Ai' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_Bii', 'label' => '2125Bii Taxes and Adjustments' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_Biii', 'label' => '2125Biii GST Remitted' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_Biv', 'label' => '2125Biv Subtotal ii minus iii' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_C', 'label' => '2125C Subtotal B + Biv (becomes 8000)' );
		//D through F on professional income so not applicable
		//Part 3 Gross Income
		$this->config_values_2125[] = array( 'pref_name' => 'p_8000', 'label' => '8000 Adjusted Gross Sales' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8290', 'label' => '8290 Reserve deducted last year' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8230', 'label' => '8230 Other Income' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_H', 'label' => '2125H sum $p_8290 and $_8230' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8299', 'label' => '8299 Gross income = sum $p_8000 and $2125_H' );
		//Part 4 COGS and Gross Profit
		$this->config_values_2125[] = array( 'pref_name' => 'p_8300', 'label' => '8300 Opening Inventory' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8320', 'label' => '8320 Purcahses during the year' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8340', 'label' => '8340 Direct Wage cost' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8360', 'label' => '8360 subcontracts' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8450', 'label' => '8450 Other costs' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8500', 'label' => '8500 closing inventory' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8518', 'label' => '8518 COGS (8300+8320+8340+8360+8450-8500)' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8519', 'label' => '8519 Gross Profit (8299-8518)' );
		//Part 5 Net Income
		$this->config_values_2125[] = array( 'pref_name' => 'p_8521', 'label' => '8521 Advertising' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8523', 'label' => '8523 Meals (allowable portion)' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8590', 'label' => '8590 Bad Debt' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8690', 'label' => '8690 Insurance' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8710', 'label' => '8710 Interest' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8760', 'label' => '8760 Business Fees, licenses, dues,' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8810', 'label' => '8810 Office Expense' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8811', 'label' => '8811 Office Supplies' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8860', 'label' => '8860 Professional fees (legal, accounting,)' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8871', 'label' => '8871 Management fees' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8910', 'label' => '8910 Rent' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_8960', 'label' => '8960 Maintenance and repairs' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9060', 'label' => '9060 Wages, Salary, Contributions' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9180', 'label' => '9180 Property Tax' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9200', 'label' => '9200 Travel' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9220', 'label' => '9220 Telephone + utilities' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9224', 'label' => '9224 Fuel except auto' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9275', 'label' => '9275 Delivery, Freight' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9281', 'label' => '9281 Motor Vehicle Expenses except CCA' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9935', 'label' => '9935 Allowance on Capital' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9936', 'label' => '9936 CCA' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9270', 'label' => '9270 Other Expense' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9270Type', 'label' => '9270Type Specify what Other is' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9368', 'label' => '9368 TOTAL Business Expense - sum 8521 through 9270' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9369', 'label' => '9369 Net income (8519 - 9368)' );
		//Part 6 Net Income
		$this->config_values_2125[] = array( 'pref_name' => 'p_9974', 'label' => '9974 GST rebate received' );
		//Details of Equity
		$this->config_values_2125[] = array( 'pref_name' => 'p_9931', 'label' => '9931 Total Business Liabilities' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9932', 'label' => '9932 Total Drawings' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_9933', 'label' => '9933 Total Capital Contribution' );
		$this->loadprefs( $this->config_values_2125 );
		$this->loadprefs( $this->config_values );
		$this->loadprefs( $this->config_values_T5013FIN );
		$this->loadprefs( $this->GST34_values );

	}
	/*abs decimal*/ function getGLSums( $account_code2, $start_date, $end_date )
	{
		//0_chart_master has account_code which is used in FA, and account_code2
		//which has been set to the GIFI code.

		//SELECT account_code FROM `0_chart_master` WHERE account_code2 = '1060' //RES - 1200, 1062, 1068
		//$sql = "select account_code from 0_chart_master where account_code2 = '" . $account_code2 . "'";

		$sql = "select sum(amount) as amount from 0_gl_trans where account in (SELECT account_code FROM `0_chart_master` WHERE account_code2 = '" . $account_code2 . "') and tran_date <= '" . $end_date . "' and tran_date > '" . $start_date . "'";
		$resultarray = $this->mysql_query( $sql, "Couldn't get sum for GIFI " . $account_code2 );
		return abs($resultarray['amount']);
	}
	function listGL_GIFI( $account_code2 )
	{
		//List the GLs for each GIFI code
		$sql = "SELECT account_code,account_name FROM `0_chart_master` WHERE account_code2 = '" . $account_code2 . "'";
		$resultarray = $this->mysql_query( $sql, "Couldn't get GLs for GIFI " . $account_code2 );
		return $resultarray;
	}
	function listGLsForm( $formconfigvalues )
	{
		if( $formconfigvalues == NULL )
		{
			$formconfigvalues = "config_values_2125";
		}
		$prefs = array();
		foreach( $formconfigvalues as $arr )
		{
			$code = $arr['pref_name'];
			if( !strncmp( $code, 'p_', 2 ) AND (strlen( $code ) == 6) )
			{
				$code2 = substr( $code, 2, 4 );
			}
			else
				$code2 = $code;
			$GLs = $this->getGLSums( $code2, $this->fiscalyearstart, $this->fiscalyearend );
			$count = 0;
			foreach( $GLs as $key=>$val )
			{
				$this->set_var( $code . '_GL_' . $count, $val['account_code'] . " " . $val['account_name'] );
				$prefs[] = $code . '_GL_' . $count;
				$count++;
			}
		}
		$this->updateprefs( $prefs );
	}
	function grabglsums()
	{
		$this->calcglsums( $this->config_values_2125 );
	/*********************************************************************
	*	T5013FIN is an INFO set of fields.
	*	$this->calcglsums( $this->config_values_T5013FIN );
	**********************************************************************/
	/*********************************************************************
	* 	CAN'T USE CONFIG VALUES AS THAT WILL CAUSE ISSUES!
	*	There are non GIFI values in the config.
	*	$this->calcglsums( $this->config_values );
	**********************************************************************/
	}
	function calcglsums( $formconfigvalues = NULL )
	{
		//config_values_2125
		//$this->view->display_notification( "Calcglsums with val array " . $formconfigvalues );
		if( $formconfigvalues == NULL )
		{
			$formconfigvalues = "config_values_2125";
			//$this->view->display_notification( "Set to " . $formconfigvalues );
			//echo "calcglsums Set to " . $formconfigvalues . "<br />";
		}
		foreach( $formconfigvalues as $arr )
		{
			$code = $arr['pref_name'];
			if( !strncmp( $code, 'p_', 2 ) AND (strlen( $code ) == 6) )
			{
				$code2 = substr( $code, 2, 4 );
			}
			else
				$code2 = $code;
			//var_dump( $code2 );
			$sum = $this->getGLSums( $code2, $this->fiscalyearstart, $this->fiscalyearend );
			$this->set_var( $code, $sum );
			//$this->view->display_notification( "Setting " . $code . " to " . $sum );
			//echo "Setting " . $code . " to " . $sum . "<br />";
		}
		$this->view->display_notification( "Calling updateprefs with " . $formconfigvalues );
		$this->updateprefs( $formconfigvalues );
	}
	function post2var( $configvalname )
	{
		foreach( $this->$configvalname as $arr )
		{
			if( isset( $_POST[ $arr['pref_name'] ] ) )
			{
				$this->set_var( $arr['pref_name'], $_POST[ $arr['pref_name'] ] );
			}
		}
	}
	function updatevalues()
	{
		$formnumber = substr( $_POST['action'], 6, 4 );
		//var_dump( $formnumber );
		$arrname = "config_values_" . $formnumber;
		//var_dump( $arrname );
		$this->post2var( $arrname );
		$this->updateprefs( $this->$arrname );
	}
	function updateGST()
	{
		$this->updateprefs( $this->GST34_values );
	}
	function grabgltotals()
	{
    		start_form(true);
                start_table(TABLESTYLE2, "width=40%");
                $th = array("");
                table_header($th);
                $k = 0;
                alt_table_row_color($k);
                    hidden('action', 'grabglsums');
                    submit_center('grabglsums', 'Calculate Totals');
		end_table();
                end_form();
	}
	function instructions_form()
	{
    		start_form(true);
		echo "<p>The following is taken from RC4070</p>";
		echo "<p>A partnership by itself does not pay income tax on its operating results and does not file an annual income tax return. Instead, each partner includes a share of the partnership income (or loss) on a personal, corporation, or trust income tax return. You do this whether or not you received your share in money or as a credit to your partnership's capital account. </p><br /> <p>Each partner also has to file financial statements or one of the following forms <ol> <li>Form T2125, Statement of Business or Professional Activities </li><li> Form T2042, Statement of Farming Activities </li><li> Form T1163, Statement A AgriStability and AgriInvest </li><li> Programs Information and Statement of Farming Activities for Individuals </li><li> Form T1164, Statement B AgriStability and AgriInvest Programs Information and Statement of Farming Activities for Additional Farming Operations </li><li> Form T1273, Statement A Harmonized AgriStability and AgriInvest Programs Information and Statement of Farming Activities for Individuals </li><li> Form T1274, Statement B Harmonized AgriStability and AgriInvest Programs Information and Statement of Farming Activities for Additional Farming Operations </li><li> Form T2121, Statement of Fishing Activities. </li> </ol> A computer-generated version of any of these forms is acceptable.  </p><br />";
		echo "<p>
A partnership that carries on a business in Canada, or a 
Canadian partnership with Canadian or foreign operations 
or investments, has to file Form T5013, Statement of 
Partnership Income, for each of the fiscal periods of the 
partnership where: 
<ol>
<li>
at the end of the fiscal period, the partnership has an 
absolute value of revenues plus an absolute value of 
expenses of more than $2 million, or has more than 
$5 million in assets; or </li>
<li>
at any time during the fiscal period: <ul>
<li>the partnership is a tiered partnership (has another partnership as a partner or isitself a partner in another partnership); 
</li>
<li>the partnership has a corporation or a trust as a partner; 
</li>
<li>the partnership invested in flow-through shares of a principal-business corporation that incurred Canadian resource expenses and renounced those expenses to the partnership; or 
</li>
<li>the minister of National Revenue asked in writing for a 
completed Form T5013, Statement of Partnership Income.
</li>
</ul>
</li>
</ol>
";
		echo "<p>Important dates:
			<ol>
			<li>
				<b>15th</b> of each month send PAYROLL deductions
			</li>
			<li>
				Quarterly (<b>March 15, June 15...</b>) (Self Employed) installments of tax, CPP
			</li>
			<li>
				<b>Last Day Feb</b> Submit T4s to Gov and send to employees
			</li>
			<li>
				<b>March 31</b> Partnership Info return (if required)
			</li>
			<li>
				<b>April 30</b> File Tax return.  PAY ANY TAXES OWING
			</li>
			</ol>
			</p>";
                end_form();
	}
	function T2125_form()
	{
/*
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_A', 'label' => '2125A Gross Sales including taxes' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_Ai', 'label' => '2125Ai Taxes and Adjustments' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_B', 'label' => '2125B Subtotal A - Ai' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_Bii', 'label' => '2125Bii Taxes and Adjustments' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_Biii', 'label' => '2125Biii GST Remitted' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_Biv', 'label' => '2125Biv Subtotal ii minus iii' );
		$this->config_values_2125[] = array( 'pref_name' => 'p_2125_C', 'label' => '2125C Subtotal B + Biv (becomes 8000)' );
*/
		$this->set_var( 'p_2125_Ai', $this->GST34_105 );	
		$this->set_var( 'p_2125_A', $this->p_8299 + $this->p_2125_Ai );	
		$this->set_var( 'p_2125_B', $this->p_2125A - $this->p_2125Ai );
		//$this->set_var( 'p_2125_Bii', $this->p_2125A - $this->p_2125Ai );
		//$this->set_var( 'p_2125_Biii', $this->p_2125A - $this->p_2125Ai );
		$this->set_var( 'p_2125_Biv', $this->p_2125Bii - $this->p_2125Biii );
		$this->set_var( 'p_2125_C', $this->p_2125B + $this->p_2125Biv );

		$this->set_var( 'p_8518', $this->p_8300 + $this->p_8320 + $this->p_8340 + $this->p_8360 + $this->p_8450 - $this->p_8500 );
		$this->set_var( 'p_2125_H', $this->p_8290 + $this->p_8230 );
		$this->set_var( 'p_8299', $this->p_8000 + $this->p_2125_H );
		$this->set_var( 'p_9200', $this->p_8524 /*+ $this->p_5618*/ );

		$this->set_var( 'p_9368', $this->p_8521 + $this->p_8523 + $this->p_8590 + $this->p_8690 + $this->p_8710 + $this->p_8760 + $this->p_8810 + $this->p_8811 + $this->p_8860 + $this->p_8871 + $this->p_8910 + $this->p_8960 + $this->p_9060 + $this->p_9180 + $this->p_9200 + $this->p_9220 + $this->p_9224 + $this->p_9275 + $this->p_9281 + $this->p_9935 + $this->p_9936 + $this->p_9270);
		$this->set_var( 'p_8519', $this->p_8299 - $this->p_8518 );
		$this->set_var( 'p_9369', $this->p_8519 - $this->p_9368 );
		//$this->set_var( 'p_8300', $this->p_8519 - $this->p_9368 );
		//$this->set_var( 'p_8500', $this->p_8519 - $this->p_9368 );

    		start_form(true);
                start_table(TABLESTYLE2, "width=40%");
                $th = array("Variable", "Value");
                table_header($th);
                $k = 0;
                alt_table_row_color($k);
		$this->valuesarray2table( $this->config_values_2125 );
                    hidden('action', 'update2125');
                    submit_center('update2125', 'Update 2125');
		end_table();
                end_form();
	}
	function calcGST()
	{
		$this->calcGLSums( $this->GST34_values );
		$this->set_var( 'GST34_101', abs($this->p_8299) );
		$this->set_var( 'GST34_105', $this->GST34_103 + $this->GST34_104 );
		$this->set_var( 'GST34_108', $this->GST34_106 + $this->GST34_107 );
		$this->set_var( 'GST34_109', $this->GST34_105 - $this->GST34_108 );
		$this->set_var( 'GST34_112', $this->GST34_110 + $this->GST34_111 );
		$this->set_var( 'GST34_113a', $this->GST34_109 - $this->GST34_112 );
		$this->set_var( 'GST34_113b', $this->GST34_205 + $this->GST34_405 );
		$this->set_var( 'GST34_113c', $this->GST34_113a + $this->GST34_113b );
		$this->updateprefs( $this->GST34_values );
	}
	function GST34_form()
	{
/*
		foreach( $this->GST34_values as $row )
		{
			display_notification( "Code 2: " . $row['pref_name'] );
			$this->set_var( $row['pref_name'], $this->getGLSums( $row['pref_name'], $this->fiscalyearstart, $this->fiscalyearend ) );
		}	
*/
		if( $this->GST34_113c < 0 )
		{
			$this->set_var( 'GST34_114', 0 - $this->GST34_113c );
			$this->set_var( 'GST34_115', 0 );
		}
		else
		{
			$this->set_var( 'GST34_115', $this->GST34_113c );
			$this->set_var( 'GST34_114', 0 );
		}
    		start_form(true);
                start_table(TABLESTYLE2, "width=40%");
                $th = array("Variable", "Value");
                table_header($th);
                $k = 0;
                alt_table_row_color($k);
		$this->valuesarray2table( $this->GST34_values );
                    hidden('action', 'updateGST');
                    submit_center('updateGST', 'Update GST');
		end_table();
                end_form();
	}
	function T5013FIN_form()
	{
    		start_form(true);
                start_table(TABLESTYLE2, "width=40%");
                $th = array("Variable", "Value");
                table_header($th);
                $k = 0;
                alt_table_row_color($k);
                        /* To show a labeled cell...*/
                        //label_cell("Table Status");
                        //if ($this->found) $table_st = "Found";
                        //else $table_st = "<font color=red>Not Found</font>";
                        //label_cell($table_st);
                        //end_row();
/*
                echo combo_input("order_no2", $this->order_no, $sql, 'supp_name', 'order_no',
                        array(
                                //'format' => '_format_add_curr',
                                'order' => array('order_no'),
                                //'search_box' => $mode!=0,
                                'type' => 1,
                                //'search' => array("order_no","supp_name"),
                                //'spec_option' => $spec_option === true ? _("All Suppliers") : $spec_option,
                                'spec_id' => $all_items,
                                'select_submit'=> $submit_on_change,
                                'async' => false,
                                //'sel_hint' => $mode ? _('Press Space tab to filter by name fragment') :
                                //_('Select supplier'),
                                //'show_inactive'=>$all
                        )
                );
*/
		$this->valuesarray2table( $this->config_values_T5013FIN );
	/*
        *        //This currently only puts text boxes on the config screen!
	*               foreach( $this->config_values_T5013FIN as $row )
	*               {
	*                               text_row($row['label'], $row['pref_name'], $this->$row['pref_name'], 20, 40);
	*               }
        *        end_table(1);
	**/
                    hidden('action', 'update5013');
                    submit_center('update5013', 'Update Configuration');
                end_form();

	}
	function T5013sched1_form()
	{
	}
	function T5013sched2_form()
	{
	}
	function T5013sched5_form()
	{
	}
	function T5013sched6_form()
	{
	}
	function T5013sched8_form()
	{
	}
	function T5013sched9_form()
	{
	}
	function T5013sched10_form()
	{
	}
	function T5013sched12_form()
	{
	}
	function T5013sched50_form()
	{
	}
	function T5013sched52_form()
	{
	}
	function T5013scheda100_form()
	{
	}
	function T5013sched125_form()
	{
	}
	function T5013sched142_form()
	{
	}
	function is_installed()
	{
		$ret = parent::is_installed();
		//Now need to check for our own install steps ie database
		return $ret;
	}	
	function reset()
	{
        	$this->selected_id = -1;
        	$sav = get_post('show_inactive');
        	unset($_POST);
        	$_POST['show_inactive'] = $sav;
	}
}


?>
