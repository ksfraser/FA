<?php

require_once( 'class.generic_orders.php' ); 



//class CALC_PRICING
class CALC_PRICING extends generic_orders
{
	var $include_header;
	var $maxrowsallowed;
	var $lastoid;
	var $mailto;
	var $db;
	var $MAP_type;
	var $BASE_type;
	var $MSRP_type;
	var $COMPETITION_type;
	var $force_higher_base;
	var $force_higher_map;
	var $comp_pricing;
	var $comp_weight;
	var $base_weight;
	var $map_weight;
	var $msrp_weight;
	var $desired_weight;

	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		global $db;
		$this->db = $db;
		//echo "CALC_PRICING constructor";
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		$this->set_var( 'vendor', "CALC_PRICING" );
		//$this->set_var( 'include_header', TRUE );
		
		//$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		//$this->config_values[] = array( 'pref_name' => 'include_header', 'label' => 'Include Headers' );
		//$this->config_values[] = array( 'pref_name' => 'maxrowsallowed', 'label' => 'Maximum Rows Allowed in file' );
		//$this->config_values[] = array( 'pref_name' => 'mailto', 'label' => 'Mail CSV to email address' );
		$this->config_values[] = array( 'pref_name' => 'BASE_type', 'label' => 'Price Book name for Base Cost' );
		$this->config_values[] = array( 'pref_name' => 'MAP_type', 'label' => 'Price Book name for Minimum Advertised Price' );
		$this->config_values[] = array( 'pref_name' => 'MSRP_type', 'label' => 'Price Book name for Manufacturer Suggested Retail Price' );
		$this->config_values[] = array( 'pref_name' => 'COMPETITION_type', 'label' => 'Price Book name(s) for Competitors Prices. List each name wrapped in single quotes and separated by \',\'' );
		$this->config_values[] = array( 'pref_name' => 'force_higher_base', 'label' => 'Force recommended price to be higher than BASE price? T/F' );
		$this->config_values[] = array( 'pref_name' => 'force_higher_map', 'label' => 'Force recommended price to be higher than MAP price? T/F' );
		$this->config_values[] = array( 'pref_name' => 'comp_pricing', 'label' => 'When calculating the Competition factor, do we look at the Lowest price, Highest, Median, Middle or Mode price? High/Low/Ave/Mid/Mode' );
		$this->config_values[] = array( 'pref_name' => 'comp_weight', 'label' => 'Weighting of Competition Pricing in setting our price' );
		$this->config_values[] = array( 'pref_name' => 'base_weight', 'label' => 'Weighting of BASE Pricing in setting our price' );
		$this->config_values[] = array( 'pref_name' => 'map_weight', 'label' => 'Weighting of MAP Pricing in setting our price' );
		$this->config_values[] = array( 'pref_name' => 'msrp_weight', 'label' => 'Weighting of MSRP Pricing in setting our price' );
		$this->config_values[] = array( 'pref_name' => 'desired_weight', 'label' => 'Weighting of auto-calced (desired) Pricing in setting our price' );
		
		//	Must be higher than base_cost
		//	Must be higher than MAP
		//	When looking at the competition, do we want the lowest, average, or highest?
		//	Do we want to be at highest or lowest, or average of MSRP and desired_retail
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'create', 'form' => 'CreateTable', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Run Calculations', 'action' => 'run_calc_form', 'form' => 'run_calc_form', 'hidden' => FALSE );
		/*
		$this->tabs[] = array( 'title' => 'Export File', 'action' => 'exportfile', 'form' => 'export_file_form', 'hidden' => FALSE );
		 */
	}
	function run_calc_form()
	{
		$this->CreateTable();
		$this->insert_stocks();
		$inserted = db_num_affected_rows();
		echo "<br />Inserted: " . $inserted;
		$this->update_base_cost();
		$updatedbase = db_num_affected_rows();
		echo "<br />Updated Base: " . $updatedbase;
		$this->update_msrp();
		$updatedmsrp = db_num_affected_rows();
		echo "<br />Updated MSRP: " . $updatedmsrp;
		$this->update_MAP();
		$updatedmap = db_num_affected_rows();
		echo "<br />Updated MAP: " . $updatedmap ;
		$this->update_desired_retail();
		$updateddesired = db_num_affected_rows();
		echo "<br />Updated Desired retail: " . $updateddesired;
		//$updatedcompetition = $this->update_competition();
		//echo "<br />Updated Compeititon: " . $updatedcompetition;
		$this->fix_msrp();
		$updatedfixmsrp = db_num_affected_rows();
		echo "<br />Updated Fixed Base: " . $updatedfixmsrp;
		$this->calculate_proposed();
		echo "<br />Updated Propsed prices for : " . db_num_affected_rows() . " products";
		echo "<br /><br />Calculations Finished!";
	}
	function call_table( $action, $msg )
	{
                start_form(true);
                 start_table(TABLESTYLE2, "width=40%");
                 table_section_title( $msg );
                 hidden('action', $action );
                 end_table(1);
                 submit_center( $action, $msg );
                 end_form();
	}
	/*********************************************************************//**
	 *
	 *	Need to consider
	 *	force_higher_base
	 *	force_higher_map;
	 *	comp_weight;
	 *	base_weight;
	 *	map_weight;
	 *	msrp_weight;
	 *	desired_weight;
	 *
	***********************************************************************/
	function calculate_proposed()
	{
		$sql = "select * from " . TB_PREF . "prices_calculate";
		$res = db_query( $sql, "Couldn't select stocks for calculation" );
		$count = 0;
		while( $row = db_fetch_assoc( $res ) )
		{
			$compprice = $row['competition'];
			$baseprice = $row['base_cost'];
			$msrpprice = $row['msrp'];
			$desired = $row['desired_retail'];
			$mapprice = $row['MAP'];
			$compcomponent = $compprice * $this->comp_weight;
			$basecomponent = $baseprice * $this->base_weight;
			$mapcomponent = $mapprice * $this->map_weight;
			$msrpcomponent = $msrpprice * $this->msrp_weight;
			$desiredcomponent = $desired * $this->desired_weight;

			$totalweight = $this->comp_weight + $this->msrp_weight + $this->desired_weight;
			$sum = $compcomponent + $msrpcomponent + $desiredcomponent;
			if( strncasecmp( $this->force_higher_base, 'T', 1 ) )
			{
				$totalweight += $this->base_weight;
				$sum += $basecomponent;
			}
			if( strncasecmp( $this->force_higher_map, 'T', 1 ) )
			{
				$totalweight += $this->map_weight;
				$sum += $mapcomponent;
			}
			$proposed = $sum / $totalweight;
			if( ! strncasecmp( $this->force_higher_base, 'T', 1 ) )
			{
				if( $proposed < $baseprice )
					$proposed = $baseprice;
			}
			if( ! strncasecmp( $this->force_higher_map, 'T', 1 ) )
			{
				if( $proposed < $mapprice )
					$proposed = $mapprice;
			}
			if( $count == 0 )
			{
				echo "<br />Total weight: " . $totalweight;
				$count++;
			}
			echo "<br />Item: " . $row['stock_id'] . " Base: " . $baseprice;
			echo " MAP: " . $mapprice;
			echo " MSRP: " . $msrpprice;
			echo " Desired: " . $desired;
			echo " Competition: " . $compprice;
			echo " Proposed: " . $proposed;
			$updatesql = "update " . TB_PREF . "prices_calculate set proposed_retail = '" . $proposed . "' where stock_id = '" . $row['stock_id'] . "'";
			db_query( $updatesql, "Couldn't update price for " . $row['stock_id'] );
		}
	}
	/***********************************************************************
	*
	***********************************************************************/
	function insert_stocks()
	{
		$sql = "
		insert ignore into " . TB_PREF . "prices_calculate (stock_id) SELECT p1.stock_id
		 FROM `" . TB_PREF . "prices` p1";
		db_query( $sql, "Couldn't insert stocks into table" );
	}
	/***********************************************************************
	*
	***********************************************************************/
	function update_base_cost()
	{
		$sql = "
		update " . TB_PREF . "prices_calculate p1
		join " . TB_PREF . "prices p2
		on p1.stock_id=p2.stock_id
		set p1.base_cost = p2.price
		where p2.sales_type_id = (select id from " . TB_PREF . "sales_types where sales_type in ( '" . $this->BASE_type . "' ) )";
		db_query( $sql, "Couldn't update table base cost" );
	}
	/***********************************************************************
	*
	***********************************************************************/
	function update_desired_retail()
	{
		$sql = "
		update " . TB_PREF . "prices_calculate p1
		join " . TB_PREF . "prices p2
		on p1.stock_id=p2.stock_id
		set p1.desired_retail =
		       (p2.price + (p2.price * (SELECT value FROM `" . TB_PREF . "sys_prefs` where name in ('add_pct'))))
		        * (SELECT factor FROM `" . TB_PREF . "sales_types` where sales_type in ( 'Retail' ))
		where p2.sales_type_id = (select id from " . TB_PREF . "sales_types where sales_type in (  '" . $this->BASE_type . "' ) )";
		db_query( $sql, "Couldn't update table desired retail" );
	}
	/***********************************************************************
	*
	***********************************************************************/
	function update_msrp()
	{
		$sql = "
		update " . TB_PREF . "prices_calculate p1
		join " . TB_PREF . "prices p2
		on p1.stock_id=p2.stock_id
		set p1.msrp = p2.price
		where p2.sales_type_id = (select id from " . TB_PREF . "sales_types where sales_type in (  '" . $this->MSRP_type . "' ) )";
		db_query( $sql, "Couldn't update table MSRP" );
	}
	/***********************************************************************
	*
	***********************************************************************/
	function update_MAP()
	{
		$sql = "
		update " . TB_PREF . "prices_calculate p1
		join " . TB_PREF . "prices p2
		on p1.stock_id=p2.stock_id
		set p1.MAP = p2.price
		where p2.sales_type_id = (select id from " . TB_PREF . "sales_types where sales_type in (  '" . $this->MAP_type . "' ) )";
		db_query( $sql, "Couldn't update table MAP" );
	}
	/***********************************************************************
	*
	***********************************************************************/
	function update_competition()
	{
		$pricetype = "select id from " . TB_PREF . "sales_types where sales_type in ( " . $this->COMPETITION_type . " )";
		$res = db_query( $pricetype, "Couldn't query table competition" );
		$changed = 0;
		while( $row = db_fetch_assoc( $res ) )
		{
			$sql = "
			update " . TB_PREF . "prices_calculate p1
			join " . TB_PREF . "prices p2
			on p1.stock_id=p2.stock_id
			set p1.competition = p2.price
			where p2.sales_type_id = '" . $row['id'] . "'";
			db_query( $sql, "Couldn't update table competition" );
			$changed += db_num_affected_rows();
		}
		return $changed;
	}
	/***********************************************************************
	*
	***********************************************************************/
	function fix_msrp()
	{
		$sql = "
		update " . TB_PREF . "prices_calculate set msrp = desired_retail where msrp < base_cost";
		db_query( $sql, "Couldn't update table fix msrp" );
	}
	function calculate_proposed_retail()
	{
		//Rules:
		//	Must be higher than base_cost
		//	Must be higher than MAP
		//	When looking at the competition, do we want the lowest, average, or highest?
		//	Do we want to be at highest or lowest, or average of MSRP and desired_retail
	}
	/***********************************************************************
	*
	***********************************************************************/
	function insert_retail_into_prices()
	{
		$sql = "
		insert ignore into " . TB_PREF . "prices (stock_id, sales_type_id, curr_abrev, price) select stock_id, (select id from " . TB_PREF . "sales_types where sales_type in ( 'Retail' )), 'CAD', proposed_retail from " . TB_PREF . "prices_calculate";
		db_query( $sql, "Couldn't update PRICES table" );
	}
	
	function sales_pricing()
	{
		global $path_to_root;
/*
		start_table(TABLESTYLE_NOBORDER);
        	start_row();
    		stock_items_list_cells(_("Select an item:"), 'stock_id', null,
          		_('New item'), true, check_value('show_inactive'));
        	check_cells(_("Show inactive:"), 'show_inactive', null, true);
        	end_row();
        	end_table();

        	if (get_post('_show_inactive_update')) {
                	$Ajax->activate('stock_id');
                	set_focus('stock_id');
        	}
		else
		{
        		hidden('stock_id', get_post('stock_id'));
		}
*/

		div_start('details');
		
		$stock_id = get_post('stock_id');
		if (!$stock_id)
		        unset($_POST['_tabs_sel']); // force settings tab for new customer
		tabbed_content_start('tabs', array(
		                'sales_pricing' => array(_('S&ales Pricing'), $stock_id),
		                'standard_cost' => array(_('Standard &Costs'), $stock_id),
		                'movement' => array(_('&Transactions'), $stock_id),
		));
		
		switch (get_post('_tabs_sel')) {
		        default:
			case 'sales_pricing':
		        	$_GET['stock_id'] = $stock_id;
		                $_GET['popup'] = 1;
		                include_once($path_to_root."/inventory/prices.php");
		                break;
		        case 'standard_cost':
		                $_GET['stock_id'] = $stock_id;
		                $_GET['popup'] = 1;
		                include_once($path_to_root."/inventory/cost_update.php");
		                break;
		        case 'movement':
		                $_GET['stock_id'] = $stock_id;
		                $_GET['popup'] = 1;
		                include_once($path_to_root."/inventory/inquiry/stock_movements.php");
		                break;
	 	};
		br();
		tabbed_content_end();
		div_end();
		
		hidden('popup', @$_REQUEST['popup']);
	}
	function CreateTable()
	{
		$sql = "CREATE TABLE if not exists `" . TB_PREF . "prices_calculate` (
			  `stock_id` varchar(20) NOT NULL default '',
			  `base_cost` double NOT NULL default '0',
			  `msrp` double NOT NULL default '0',
			  `desired_retail` double NOT NULL default '0',
			  `MAP` double NOT NULL default '0',
			  `competition` double NOT NULL default '0',
			  `proposed_retail` double NOT NULL default '0',
			  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  PRIMARY KEY  (`stock_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		db_query( $sql, "Couldn't create table" );
            	display_notification("Created Pricing Calculate table");
	}


}
?>
