<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

/**********************************************************************************//**
 * class purchase_data
 *
 * 	This is a wrapper for inventory/includes/db/items_purchase_db.inc
 *
 * 	table is TB_PREF purch_data
 *
 * ************************************************************************/

$path_to_root = "../..";

require_once( 'class.woo_interface.php' );
require_once( $path_to_root . '/inventory/includes/db/items_purchase_db.inc' );
require_once( $path_to_root . '/inventory/includes/inventory_db.inc' );

class purchase_data extends woo_interface {
	var $id;	//	integer 	Unique identifier for the resource.  read-only
	var $date_created;	//	date-time 	The date the customer was created, in the site’s timezone.  read-only
	var $date_modified;	//A	date-time 	The date the customer was last modified, in the site’s timezone.  read-only
	var $stock_id;
	var $supplier_id;
	var $location_code;
	var $item_description;
	var $quantity;
	var $minimum_stock_count;
	var $to_be_ordered;
	var $location_name;
	var $location_email;
	var $supplier_turnaround;		//!< int average days turnaround from order to receive for a supplier
	var $supplier_turnaround_sku;		//!< int average days turnaround from order to receive for a supplier for a SKU
	
	function __construct( )
	{
		parent::__construct();
		$this->ObserverRegister( $this, "NOTIFY_ITEM_QUANTITY_UPDATED", 1 );	//For EVENTLOOP.
		//$this->ObserverRegister( $this, "NOTIFY_SEARCH_REMOTE_UPC", 1 );	//For EVENTLOOP.
	}

	function define_table()
	{
		$this->fields_array[] = array('name' => 'customers_id', 'type' => 'int(11)', 'auto_increment' => 'yes');
		$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->fields_array[] = array('name' => 'id', 'type' => 'int(11)', 'comment' => 'WOOs id');
		$this->fields_array[] = array('name' => 'created_at',	 	'type' => 'datetime', 	'comment' => 'The date the order was created, in the site’s timezone.  ' );
//		$this->fields_array[] = array('name' => 'billing_address', 		'type' => 'int(11)' , 	'comment' => 'FK	Billing address. See Customer Billing Address properties.', 'foreign_obj' => 'woo_billing_address' );

		$this->table_details['tablename'] = $this->company_prefix . "woo_customers";
		$this->table_details['primarykey'] = "customers_id";
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "first_name,last_name,email";
		$this->table_details['index'][0]['keyname'] = "person-email";
	}
	function get_items_purchasing_data()
	{
		if( isset( $this->stock_id ) )
			$res = get_items_purchasing_data( $this->stock_id ); //returns supplier details from db_query
		else
			return FALSE;
	}
	function get_item_purchasing_data()
	{
		if( isset( $this->stock_id ) && isset( $this->supplier_id) )
			$res = get_item_purchasing_data( $this->supplier_id, $this->stock_id ); //returns supplier details from db_fetch
		else
			return FALSE;
	}
	function get_loc_details()
	{
		if( isset( $this->stock_id ) )
		{
			$result = get_loc_details( $this->stock_id );
			return $result;
		}
		else
			return NULL;
	}
	function display_reorder_level()
	{
		$result = $this->get_loc_details();
		if( null != $result )
		{
			start_form();
			if (!isset($this->stock_id))
				$this->stock_id = get_global_stock_item();
			echo "<center>" . _("Item:"). "&nbsp;";
			echo stock_costable_items_list('stock_id', $this->stock_id, false, true);
			echo "<hr></center>";
			div_start('show_heading');
			stock_item_heading($this->stock_id);
			br();
			div_end();
			
			set_global_stock_item($this->stock_id);
			
			div_start('reorders');
			start_table(TABLESTYLE, "width=30%");
			
			$th = array(_("Location"), _("Quantity On Hand"), _("Re-Order Level"));
			table_header($th);
			
			$j = 1;
			$k=0; //row colour counter
		}
		else
			return FALSE;
		while ($myrow = db_fetch($result))
		{

			alt_table_row_color($k);
		
			if (isset($_POST['UpdateData']) && check_num($myrow["loc_code"]))
			{
		
				$myrow["reorder_level"] = input_num($myrow["loc_code"]);
				set_reorder_level($this->stock_id, $myrow["loc_code"], input_num($myrow["loc_code"]));
				display_notification(_("Reorder levels has been updated."));
			}
		
			$qoh = get_qoh_on_date($this->stock_id, $myrow["loc_code"]);
		
			label_cell($myrow["location_name"]);
		
			$_POST[$myrow["loc_code"]] = qty_format($myrow["reorder_level"], $this->stock_id, $dec);
		
			qty_cell($qoh, false, $dec);
			qty_cells(null, $myrow["loc_code"], null, null, null, $dec);
			end_row();
			$j++;
			If ($j == 12)
			{
				$j = 1;
				table_header($th);
			}
		}
		
		end_table(1);
		div_end();
		submit_center('UpdateData', _("Update"), true, false, 'default');
		end_form();
		return TRUE;
	}
	function calculate_reorder_level()
	{
		//  inventory\includes\inventory_db.inc
		//  var2 looks to be a CART instance with stock_id, item_description, quantity
		//  st_ variables are arrays filled with the items needing reorder.
		//  data has loc_Stock.*, locations.name, locations.email
		//  st_num = QOH - demand - demand_asy - this->quantity.
		//  Looks like we should look for the number already on outstanding POs for quantity.
		$this->count_quantity_on_outstanding_POs();
		$st_ids = array();		//$this->stock_id
		$st_names = array();		//$this->description
		$st_num = array();		//how many below reorder_level for a location 
		$st_reorder_level = array();	//$data['reorder_level']
		$data = calculate_reorder_level($this->location_code, $this, $st_ids, $st_names, $st_num, $st_reorder_level);
		$this->minimum_stock_count = $st_reorder_level;
		$this->to_be_ordered = $st_num;
		$this->location_name = $data['location_name'];
		$this->location_email = $data['email'];
	}
	/*************************************************************************//**
	 *set quantity to the number already ordered still outstanding
	 *
	 *
	 * ***************************************************************************/
	function count_quantity_on_outstanding_POs()
	{
		$this->quantity = 0;
	}
	/*************************************************************************//**
	 * What is the typical turnaround time from a vendor from order placed 
	 * to shipment received
	 *
	 *
	 * ***************************************************************************/
	function calculate_supplier_normal_turnaround()
	{
	}
	/*************************************************************************//**
	 * What is the typical turnaround time from a vendor from order placed 
	 * to shipment received by SKU
	 *
	 *
	 * ***************************************************************************/
	function calculate_supplier_normal_turnaround_stockid()
	{
	}
	function get_sums( $start, $end, $field, $table )
	{
		$sql = "SELECT sum(" . $field . ") as total
			FROM " . TB_PREF .  $table . "
			WHERE date >= '" . $start . "'START and date < '" . $end . "'";
		$res = db_query( $sql, "Couldn't calculcate sum in " . $table );
		$row = db_fetch_assoc( $res );
		return $row;
	}
	/**************************************************************************//**
	 * How many of a sku are purchased in a year
	 *
	 * 	Simple calculation would be within a financial year
	 * 	More complex would be the moving average.
	 * 	i.e. what is the most number sold within a 365 day period
	 * 		where the period can slide
	 *
	 * 	@params start year 4 digit year
	 * 	@params end year 4 digit year
	 *	@returns float
	 * ******************************************************************************/
	/*@float@*/function calculate_average_sku_per_year($start, $end)
	{
		if( $start > $end )
		{
			$temp = $start;
			$start = $end;
			$end = $temp;
		}
		$count = 1;
		$average = array();
		$totals = array();
		$average[$start-1] = 0;
		for( $start, $start <= $end, $start++ )
		{
			$startdate = $start . "-01-01";
			$enddate = $start . "-12-31";
			$row = $this->get_sums($startdate, $enddate, $field, $table);
			$totals[$start] = $row['total'];
			$average[$start] = ( ($average[$start-1] * $count) + $row[$total] ) / $count++;
		}
	}
	/**************************************************************************//**
	 * How many of a sku are purchased in a quarter
	 *
	 * 	Simple calculation would be within a financial quarter
	 * 	More complex would be the moving average.
	 * 	i.e. what is the most number sold within a 90 day period
	 * 		where the period can slide
	 *
	 *
	 *	@returns float
	 * ******************************************************************************/
	/*@float@*/function calculate_average_sku_per_quarter()
	{
	}
	/**************************************************************************//**
	 * How many of a sku are purchased in a month
	 *
	 * 	Simple calculation would be within a financial month
	 * 	More complex would be the moving average.
	 * 	i.e. what is the most number sold within a 30 day period
	 * 		where the period can slide
	 *
	 *
	 *	@returns float
	 * ******************************************************************************/
	/*@float@*/function calculate_average_sku_per_month()
	{
	}
	/**************************************************************************//**
	 * How many of a sku are purchased in a week
	 *
	 * 	Simple calculation would be within a financial week
	 * 	More complex would be the moving average.
	 * 	i.e. what is the most number sold within a 7 day period
	 * 		where the period can slide
	 *
	 *
	 *	@returns float
	 * ******************************************************************************/
	/*@float@*/function calculate_average_sku_per_week()
	{
	}
	/**************************************************************************//**
	 * How many of a sku are purchased in a day
	 *
	 * 	Simple calculation would be within a financial day
	 * 		Calculate X sold in our Y days of business (consider sku added date?)
	 * 		Calculate X sold on Z days of business (average/day on days of sale)
	 *
	 *
	 *	@returns float
	 * ******************************************************************************/
	/*@float@*/function calculate_average_sku_per_day()
	{
	}
	/**************************************************************************//**
	 * How many of a sku are purchased in a year
	 *
	 * 	Simple calculation would be within a financial year
	 * 	More complex would be the moving period.
	 *
	 *	@returns float
	 * ******************************************************************************/
	/*@float@*/function calculate_maximum_sku_per_year()
	{
	}
	/**************************************************************************//**
	 * How many of a sku are purchased in a quarter
	 *
	 * 	Simple calculation would be within a financial quarter
	 * 	More complex would be the moving period.
	 *
	 *
	 *	@returns float
	 * ******************************************************************************/
	/*@float@*/function calculate_maximum_sku_per_quarter()
	{
	}
	/**************************************************************************//**
	 * How many of a sku are purchased in a month
	 *
	 * 	Simple calculation would be within a financial month
	 * 	More complex would be the moving period.
	 *
	 *
	 *	@returns float
	 * ******************************************************************************/
	/*@float@*/function calculate_maximum_sku_per_month()
	{
	}
	/**************************************************************************//**
	 * How many of a sku are purchased in a week
	 *
	 * 	Simple calculation would be within a financial week
	 * 	More complex would be the moving period.
	 *
	 *
	 *	@returns float
	 * ******************************************************************************/
	/*@float@*/function calculate_maximum_sku_per_week()
	{
	}
	/**************************************************************************//**
	 * How many of a sku are purchased in a day
	 *
	 * 	Simple calculation would be within a financial day
	 *
	 *
	 *	@returns float
	 * ******************************************************************************/
	/*@float@*/function calculate_maximum_sku_per_day()
	{
	}
}

?>
