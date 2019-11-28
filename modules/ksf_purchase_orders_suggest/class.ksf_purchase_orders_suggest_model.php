<?php



/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( __DIR__ . '/../ksf_modules_common/defines.inc.php' ); 
require_once( $path_to_ksfcommon . '/class.table_interface.php' ); 
require_once( $path_to_ksfcommon . '/class.generic_fa_interface.php' );
require_once( 'class.ksf_purchase_orders_suggest.inc.php' );

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


class ksf_purchase_orders_suggest_model extends generic_fa_interface {
	var $id_ksf_purchase_orders_suggest;	//!< Index of table
	//protected $class_specific_vars;
	protected $id;
	protected $created_ts;		//!< The timestamp of when the record was created
	protected $updated_ts;		//!< The timestamp of when the record was last updated
	protected $stock_id;
	protected $loc_code;
	protected $suggested_level;

	function __construct( $prefs  = ksf_purchase_orders_suggest_PREFS, $controller )
	{
		parent::__construct( null, null, null, null, $prefs );	//generic_interface has legacy mysql connection
									//not needed with the $prefs
		$this->controller = $controller;
		$this->table_interface = new table_interface();
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

		$this->table_interface->table_details['tablename'] = TB_PREF . 'ksf_purchase_orders_suggest';
		$this->table_interface->table_details['primarykey'] = 'id';
		//$this->table_interface->table_details['orderby'] = 'sku';

		$this->table_interface->fields_array[] = array('name' => 'id', 'label' => 'Table ID', 'type' => , 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		$this->table_interface->fields_array[] = array('name' => 'updated_ts', 'label' => 'Updated Timestamp', 'type' => 'timestamp', 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		$this->table_interface->fields_array[] = array('name' => 'created_ts', 'label' => 'Created Timestamp', 'type' => 'timestamp', 'null' => 'NOT NULL',  'readwrite' => 'readwrite');

		$this->fields_array[] = array('name' => 'loc_code', 'label' => 'Location Code', 'type' => $loccdl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'stock_id', 'label' => 'Stock ID', 'type' => $stockl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'suggested_level', 'label' => 'Suggested number to order', 'type' => $stockl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		//$this->table_interface->fields_array[] = array('name' => 'stock_id', 'label' => 'SKU', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite');

		$this->table_interface->table_details['index'][0]['type'] = 'unique';
		$this->table_interface->table_details['index'][0]['columns'] = "stock_id, loc_code";
		$this->table_interface->table_details['index'][0]['keyname'] = "stock_id-loc_code";
//
//		//$this->table_interface->table_details['foreign'][0] = array( 'column' => "variablename", 'foreigntable' => "woo_prod_variable_variables", "foreigncolumn" => "variablename", "on_update" => "restrict", "on_delete" => "restrict" );	
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
	function get_QOH_lt_reorder()
	{
		/**************************USING QOH********************************/
		//Depends on QOH being up to date
		require_once( '../ksf_qoh/class.ksf_qoh.php' );
		$qoh = new ksf_qoh;
		$qoh->update();
		//select sm.stock_id, abs(sm.instock), ls.reorder_level, ls.loc_code from 1_ksf_qoh sm, 1_loc_stock ls where ls.stock_id=sm.stock_id and abs(sm.instock) < ls.reorder_level order by stock_id;
		$this->table_interface->clear_sql_vars();
		$this->table_interface->select_array = array();
		$this->table_interface->select_array[] = "sm.stock_id as stock_id";
		$this->table_interface->select_array[] = "abs(sm.instock) as qoh";
		$this->table_interface->select_array[] = "ls.reorder_level as reorder";
		$this->table_interface->select_array[] = "ls.loc_code as loc_code";
		$this->table_interface->from_array = array();
		$this->table_interface->from_array[] = "1_ksf_qoh sm";
		$this->table_interface->from_array[] = "1_loc_stock ls";
		$this->table_interface->where_array = array();
		$this->table_interface->where_array['ls.stock_id'] = "sm.stock_id";
		$this->table_interface->where_array['abs(sm.instock)'] = array( "lt", "ls.reorder_level" );
		if( isset( $this->loc_code ) )
		{
			$this->table_interface->where_array['ls.loc_code'] = $this->loc_code;
			//if QOH ever gains the location code column...
			//$this->table_interface->where_array['sm.loc_code'] = $this->loc_code;
		}
		$this->table_interface->orderby_array = array( 'sm.stock_id' );
		$this->table_interface->buildSelectQuery();
		$res = $this->table_interface->query( "QOH lt reorder could not be retrieved", "select");
		$res_array = $this->table_interface->db_fetch( $res );
		foreach( $res_array as $row )
		{
			$ret_array[ $row['stock_id'] ]['stock_id'] = $row['stock_id'];
			$ret_array[ $row['stock_id'] ]['level'] = $row['reorder'] - $row['qoh'];
			$ret_array[ $row['stock_id'] ]['loc_code'] = $row['loc_code']; 
		}
		return $ret_array;
	}
	function get_unfilled_sales_orders( $ret_array )
	{
		require_once( '../ksf_modules_common/class.fa_sales_order_details.php' );
		$sod = new fa_sales_order_details();
		$res_array = $sod->get_unfilled_items();
		
		foreach( $res_array as $row )
		{
			if( ! isset( $ret_array[ $row['stk_code'] ] ) )
			{
				$ret_array[ $row['stk_code'] ]['stock_id'] = $row['stk_code'];
				$ret_array[ $row['stk_code'] ]['loc_code'] ="DEF";
			}
			$ret_array[ $row['stk_code'] ]['level'] += $row['qty_sent'] - $row['quantity'];
		}
		return $ret_array;
	}
	function get_suggested_order_levels()
	{
		$ret_array = $this->get_QOH_lt_reorder();
		$res_array = $this->get_unfilled_sales_orders( $ret_array );
		foreach( $res_array as $row )
		{
			$this->set( 'stock_id', $row['stock_id'] );
			$this->set( 'suggested_level', $row['level'] );
			$this->set( 'loc_code', $row['loc_code'] );
			$this->insert();
		}
	}
}
