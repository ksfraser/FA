<?php


$path_to_root = "../..";

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( '../ksf_modules_common/class.table_interface.php' ); 
require_once( '../ksf_modules_common/class.generic_fa_interface.php' );

/*************************************************************//**
 * 
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
 * Provides:
        function __construct( $prefs )
        function define_table()
        function form_Auto Generate Purchase Order to be Drop Shipped from Supplier
        function form_Auto Generate Purchase Order to be Drop Shipped from Supplier_completed
        function action_show_form()
        function install()
        function master_form()
 * 
 *
 * ***************************************************************/


class ksf_drop_ship_model extends generic_fa_interface {
	var $id_ksf_drop_ship;	//!< Index of table
	var $table_interface;
	function __construct( $prefs )
	{
		parent::__construct( null, null, null, null, $prefs );	//generic_interface has legacy mysql connection
									//not needed with the $prefs
		/*
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
		$this->table_interface = new table_interface();
		$this->define_table();
							
	}
	function define_table()
	{
		$this->table_interface->table_details['tablename'] = TB_PREF . 'ksf_drop_ship';
		//woo_interface::define_table();
		//$this->table_interface->fields_array[] = array('name' => 'stock_id', 'label' => 'SKU', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		//$sidl = 'varchar(' . STOCK_ID_LENGTH . ')';
		//$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';



		//$this->table_interface->fields_array[] = array('name' => 'variablename', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		//$this->table_interface->fields_array[] = array('name' => 'stock_id', 'label' => 'Stock ID', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', /*'foreign_obj' => 'woo_prod_variable_master', 'foreign_column' => 'stock_id'*/ 'comment' => 'Master Product stock_id');
		//$this->table_interface->fields_array[] = array('name' => 'sku', 'label' => 'SKU', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'generated sku for this variable product' );
		$this->table_interface->fields_array[] = array('name' => 'description', 'label' => 'Description', 'type' => $descl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite' );
		$this->table_interface->fields_array[] = array('name' => 'inserted_fa', 'label' => 'Inserted into FA', 'type' => 'bool', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->table_interface->fields_array[] = array('name' => 'woo_id', 'label' => 'WooCommerce ID', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );

		$this->table_interface->table_details['primarykey'] = stock_id;
		//$this->table_interface->table_details['orderby'] = 'sku';
//		$this->table_interface->table_details['index'][0]['type'] = 'unique';
//		$this->table_interface->table_details['index'][0]['columns'] = "stock_id, sku";
//		$this->table_interface->table_details['index'][0]['keyname'] = "stock_id-sku";
//		$this->table_interface->table_details['index'][1]['type'] = 'unique';
//		$this->table_interface->table_details['index'][1]['columns'] = "sku";
//		$this->table_interface->table_details['index'][1]['keyname'] = "sku";
//
//		//$this->table_interface->table_details['foreign'][0] = array( 'column' => "variablename", 'foreigntable' => "woo_prod_variable_variables", "foreigncolumn" => "variablename", "on_update" => "restrict", "on_delete" => "restrict" );	
//		//$this->table_interface->table_details['foreign'][1] = array( 'column' => "stock_id", 'foreigntable' => "woo_prod_variable_master", "foreigncolumn" => "stock_id", "on_update" => "restrict", "on_delete" => "restrict" );
	}
	function install()
	{
		$this->table_interface->create_table();
		parent::install();
	}

	
}
