<?php



/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( __DIR__ . '/../ksf_modules_common/defines.inc.php' ); 
require_once( $path_to_ksfcommon . '/class.table_interface.php' ); 

/*************************************************************//**
 * A model (MVC) class for handling data, in and out of a database
 * 
 *	There are some (FA specific) display_* functions commented
 *	out in this class.  As this is not a VIEW class, it really
 * 	should not be doing any displays.  Need to determine if there
 *	is a way short of throwing Exceptions to pass that back
 *
 * Inherits:
 *                 function __construct( $host, $user, $pass, $database, $pref_tablename )
                function eventloop( $event, $method )
                function eventregister( $event, $method )
                function add_submodules()
                function module_install()
                function install()
                function loadprefs()
                function updateprefs()
                function checkprefs()
                function call_table( $action, $msg )
                function action_show_form()
                function show_config_form()
                function form_export()
                function related_tabs()
                function show_form()
                function base_page()
                function display()
                function run()
                function modify_table_column( $tables_array )
                / *@fp@* /function append_file( $filename )
                /*@fp@* /function overwrite_file( $filename )
                /*@fp@* /function open_write_file( $filename )
                function write_line( $fp, $line )
                function close_file( $fp )
                function file_finish( $fp )
                function backtrace()
                function write_sku_labels_line( $stock_id, $category, $description, $price )
		function show_generic_form($form_array)
		function get(  )
        	function set( ,  = null,  = false )
		function select_row()
		function install()
	        function insert_data(  )
 * Provides:
        function __construct( $prefs )
        function define_table()
	class_specific_funcs
        
 * 
 *
 * ***************************************************************/


class ksf_sold_timesliced extends table_interface {
	var $id_ksf_purchase_orders_suggest;	//!< Index of table
	//protected $class_specific_vars;
	protected $id;
	protected $created_ts;		//!< The timestamp of when the record was created
	protected $updated_ts;		//!< The timestamp of when the record was last updated
	protected $stock_id;
	protected $loc_code;
	protected $largest_trans;
	protected $lifetime_sales;
	protected $slice1_sales;
	protected $slice1_startdate;
	protected $slice2_sales;
	protected $slice2_startdate;
	protected $slice3_sales;
	protected $slice3_startdate;
	protected $slice4_sales;
	protected $slice4_startdate;
	protected $slice4_enddate;	//Assumption slice 3 start date is slice 2 end date

	function __construct()
	{
		parent::__construct();
		$this->define_table();
	}
	/*************************************************************************//**
	* Define the structure of the database table
	*
	*	Setup an array that describes the SQL options for the table
	*
	*	fields_array fields:
	*		name
	*		label
	*		type (varchar, bool, int, )
	*		null (can be NULL or not in table)
	*		readwrite (read only, or writeable)
	*		comment
	*		default
	*		Foreign_Object (foreign table)
	*		foreign_column (column in foreign table.  typically a Foreign Key)
	*
	*	table_details fields:
	*		primarykey
	*		index
	*			numeric index (doesn't end up in the table)
	*			type (type of index)
	*			columns (comma separated list of columns in this index)
	*			keyname (my convention is to hyphenate the columns)
	*		orderby (alter SELECT queries to order by, by default)
	*
	*	foreign fields (for setting up foreign key constraints)
	*		column
	*		foreigntable
	*		foreigncolumn
	*		on_update (restrict)
	*		on_delete (restrict)
	*
	*	When defining some of the table fields, make sure you have checked defines.inc.php
	*	for length definition CONSTANTS so that if we upgrade a definition, things can be
	*	cascaded.
	*
	******************************************************************************/
	function define_table()
	{
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
	
		$stockl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$refl = 'varchar(' . REFERENCE_LENGTH . ')';
		$loccdl = 'varchar(' . LOC_CODE_LENGTH . ')';

		$this->table_details['tablename'] = TB_PREF . 'ksf_sold_timesliced';
		$this->table_details['primarykey'] = 'id';
		//$this->table_details['orderby'] = 'sku';

		$this->fields_array[] = array('name' => 'id', 'label' => 'Table ID', 'type' => , 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'updated_ts', 'label' => 'Updated Timestamp', 'type' => 'timestamp', 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'created_ts', 'label' => 'Created Timestamp', 'type' => 'timestamp', 'null' => 'NOT NULL',  'readwrite' => 'readwrite');

		$this->fields_array[] = array('name' => 'loc_code', 'label' => 'Location Code', 'type' => $loccdl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'stock_id', 'label' => 'Stock ID', 'type' => $stockl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'largest_trans', 'label' => 'Largest single transaction', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'lifetime_sales', 'label' => 'Lifetime Sales amount', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'slice1_sales', 'label' => 'Time Slice 1 Sales amount', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'slice2_sales', 'label' => 'Time Slice  2 Sales amount', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'slice3_sales', 'label' => 'Time Slice  3 Sales amount', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'slice4_sales', 'label' => 'Time Slice  4 Sales amount', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'slice1_startdate', 'label' => 'Time Slice  1 Start Date', 'type' => 'date', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'slice2_startdate', 'label' => 'Time Slice  2 Start Date', 'type' => 'date', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'slice3_startdate', 'label' => 'Time Slice  3 Start Date', 'type' => 'date', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'slice4_startdate', 'label' => 'Time Slice  4 Start Date', 'type' => 'date', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'slice4_enddate', 'label' => 'Time Slice  4 End Date', 'type' => 'date', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		//$this->fields_array[] = array('name' => 'stock_id', 'label' => 'SKU', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite');

		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "stock_id, loc_code, slice1_startdate";
		$this->table_details['index'][0]['keyname'] = "stock_id-loc_code-slice1_startdate";
//
//		//$this->table_details['foreign'][0] = array( 'column' => "variablename", 'foreigntable' => "woo_prod_variable_variables", "foreigncolumn" => "variablename", "on_update" => "restrict", "on_delete" => "restrict" );	
	}
	/*******************************************************//**
	* Lookup some names from the keys passed in from a select form before inserting
	*
	***********************************************************/
	function insert_data( $arr )
	{
		global $path_to_ksfcommon;

//		require_once( $path_to_ksfcommon . /class.XXX.php );
//		$XX = new XXX( $this );
//		$XX->set( 'id', $arr['XXX'] );
//		$XX->getById();
//		$arr['XXX_name'] = $ba->get( 'XXX_name' );
//
//		require_once( \ . /class.YYY.php );
//		$YY = new YYY( $this );
//		$YY->set( 'terms_indicator', $arr['YYY'] );
//		$YY->getById();
//		$arr['YYY_name'] = $YY->get( 'YYY' );

		parent::insert_data( $arr );
	}
	/***************************//**
	 * Set the reorder level for a stock_id
	 *
	 * Throws exceptions
	 * @params int reorder level
	 * @param internal location code, stock id
	 * @return bool success or failure
	 * *****************************/
	private /*bool*/function set_reorder( $level )
	{
		$reorder = new fa_loc_stock();
		if( !isset( $this->loc_code ) )
			throw new Exception( "Required Field not set", KSF_FIELD_NOT_SET );
		if( !isset( $this->stock_id ) )
			throw new Exception( "Required Field not set", KSF_FIELD_NOT_SET );
		$reorder->set( 'loc_code', $this->loc_code );
		$reorder->set( 'stock_id', $this->stock_id );
		$reorder->set( 'reorder_level', $level );
		if (! $reorder->set_Location_reorder_level() )
		{
			//Insert failed.  Likely because entry already there.
			$res = $reorder->update_Location_reorder_level();
			return $res;
		}
		else return true;
	}
	private function calc_values()
	{
		if( !isset( $this->stock_id ) )
			throw new Exception( "Required Field not set", KSF_FIELD_NOT_SET );
		$sales = new fa_stock_moves();
		$sales->set( 'stock_id', $this->stock_id );
		if( isset( $this->loc_code ) )
		{
			$byLoc = true;
			$sales->set( 'loc_code', $this->loc_code );
		}
		else
		{
			$byLoc = false;
			$this->set( 'loc_code', "DEF" );
		}
		$res = $sales->get_Lifetime_Sale( $byLoc );
		$this->set( 'lifetime_sales', $res[0]['sum'] );
		$this->set( 'largest_trans', $res[0]['max'] );
		$res = $sales->get_Daterange_Sale( $this->slice1_startdate, $this->slice2_startdate, $byLoc );
		$this->set( 'slice1_sales', $res[0]['sum'] );
		$res = $sales->get_Daterange_Sale( $this->slice2_startdate, $this->slice3_startdate, $byLoc );
		$this->set( 'slice2_sales', $res[0]['sum'] );
		$res = $sales->get_Daterange_Sale( $this->slice3_startdate, $this->slice4_startdate, $byLoc );
		$this->set( 'slice3_sales', $res[0]['sum'] );
		$res = $sales->get_Daterange_Sale( $this->slice4_startdate, $this->slice4_enddate, $byLoc );
		$this->set( 'slice4_sales', $res[0]['sum'] );
		$this->insert();
	}
	private function set_reorder_slice( $slice = 0 )
	{
		//Set a Location Code reorder level based upon a time slice
		switch( $slice )
		{
			case 1: $level = $this->slice1_sales;
				break;
			case 2: $level = $this->slice2_sales;
				break;
			case 3: $level = $this->slice3_sales;
				break;
			case 4: $level = $this->slice4_sales;
				break;
			case 0:
			default:  $level = $this->largest_trans;
			break;
		}
		$this->set_reorder( $level );
	}
	public function calculate_reorder_levels( $slice = 0 )
	{
		require_once( '../ksf_modules_common/class.fa_stock_moves.php' );
		$sm = new fa_stock_moves();
		$res_array = $sm->get_stock_id_ever_sold();
		foreach( $res_array as $row )
		{
			$this->set( 'stock_id', $row['stock_id'] );
			$this->calc_values();
			$this->set_reorder_slice( $slice );
		}
	}
	//replace into 1_loc_stock( stock_id, reorder_level, loc_code ) select stock_id, abs(min(qty)) as max, loc_code from 1_stock_moves where type=13 and tran_date > '2017-05-01' group by stock_id, loc_code having abs(sum(qty)) > max;
 	//replace into 1_loc_stock( stock_id, reorder_level, loc_code ) select stock_id, abs(min(qty)) as max, 'DEF' from 1_stock_moves where type=13 and tran_date > '2017-05-01' group by stock_id having abs(sum(qty)) > max;
 	//insert ignore into 1_loc_stock( stock_id, reorder_level, loc_code ) select stock_id, abs(min(qty)) as max, 'DEF' from 1_stock_moves where type=13 group by stock_id;
}
