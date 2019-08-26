<?php


$path_to_root = "../..";

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( '../ksf_modules_common/class.generic_fa_interface.php' );
require_once( '../ksf_modules_common/class.table_interface.php' ); 

/*************************************************************//**
 * Search for products that don't have an image
 *
 * Motivated by the fact that online shopping carts are annoying
 * if there isn't at least 1 product image attached.  This is to 
 * help ensure we have images for ALL products prior to sending
 * to WooCommerce
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
        function action_show_form()
        function install()
        function updateCount()
        function count_images()
        function form_ksf_missing_image()
        function form_ksf_missing_image_completed()
        function master_form()
        function missing_image()
 * 
 *
 * ***************************************************************/
class ksf_missing_image extends generic_fa_interface {
	var $id_ksf_missing_image;	//!< Index of table
	var $stock_id;
	var $image_count;
	var $table_interface;		//!<Object class.table_interface for doing db work to better separate out the MVC functions.	
	function __construct( $prefs )
	{
		parent::__construct( null, null, null, null, $prefs );	//generic_interface has legacy mysql connection
									//not needed with the $prefs
		/*
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		 */
		//$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		//Don't need a staged approach as we are having a separate tab for the normal multi-steps...
		//$this->tabs[] = array( 'title' => 'Missing_Image Updated', 'action' => 'form_ksf_missing_image_completed', 'form' => 'form_ksf_missing_image_completed', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Update Missing_Image', 'action' => 'form_ksf_missing_image', 'form' => 'form_ksf_missing_image', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Count Images', 'action' => 'count_images', 'form' => 'count_images', 'hidden' => FALSE );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
		$this->table_interface = new table_interface();
		$this->define_table();

	}
	function define_table()
	{
		$this->table_interface->table_details['tablename'] = TB_PREF . 'ksf_missing_image';
		//woo_interface::define_table();
		$sidl = 'varchar(' . STOCK_ID_LENGTH . ')';
		$this->table_interface->fields_array[] = array('name' => 'stock_id', 'label' => 'Stock ID', 'type' => $sidl , 'null' => 'NOT NULL',  'readwrite' => 'readwrite', /*'foreign_obj' => 'woo_prod_variable_master', 'foreign_column' => 'stock_id'*/ 'comment' => 'Master Product stock_id');
		$this->table_interface->fields_array[] = array('name' => 'image_count', 'label' => 'Images counted for this Stock_ID', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );

		$this->table_interface->table_details['orderby'] = 'stock_id';
		$this->table_interface->table_details['primarykey'] = "stock_id";
	}
	function action_show_form()
	{
		$this->install();
		parent::action_show_form();
	}
	function install()
	{
		$this->table_interface->create_table();
		parent::install();
	}
	function updateCount()
	{
		$sql = "insert ignore into " . $this->table_interface->table_details['tablename'] . " (stock_id, image_count) values( '" . $this->stock_id . "', '" . $this->image_count . "')";
		$res = db_query( $sql, "Couldn't populate table image count" );

		$sql = "replace into " . $this->table_interface->table_details['tablename'] . "  (stock_id, image_count) values( '" . $this->stock_id . "', '" . $this->image_count . "')";
		$res = db_query( $sql, "Couldn't populate table image count" );
	}
	function count_images()
	{
		require_once( '../ksf_modules_common/class.fa_image.php' );
		require_once( '../ksf_modules_common/class.fa_stock_master.php' );
		$fa_stock_master = new fa_stock_master( null );
		//grab the list of stock_ids
		$fa_stock_master->getStock_IDs();
		//search through fa_image for each
		//var_dump( $fa_stock_master->stock_id_array );
		foreach( $fa_stock_master->stock_id_array as $stock_id )
		{
			$fa_image = new fa_image( $stock_id );
			$fa_image->get_next_filename();
			$this->image_count = $fa_image->images_count;
			$this->stock_id = $stock_id;
			//update our table with the stock_id and the count
			$this->updateCount();
			//TESTING
			//display_notification( "stock_id " . $this->stock_id . " has " . $this->image_count . " pics" );
			unset( $fa_image );
			$this->stock_id = "";
			$this->image_count = 0;
			//TESTING
			//continue;
		}
		display_notification( __METHOD__ . "End" );
	}
	function form_ksf_missing_image()
	{
		//$this->count_images();
		$this->missing_image();
		//$this->call_table( 'form_ksf_missing_image_completed', "ksf_missing_image" );

	}
	/*********************************************************************************//**
	 *master_form
	 *	Display the summary of items with edit/delete
	 *		
	 *	assumes entry_array has been built (constructor)
	 *	assumes table_details has been built (constructor)
	 *	assumes selected_id has been set (constructor?)
	 *	assumes iam has been set (constructor)
	 *
	 * ***********************************************************************************/
/*
	function master_form()
	{
		echo __METHOD__ . "::" . __LINE__ . "<br />";
		global $Ajax;
		$this->notify( __METHOD__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );
		$this->create_full();
		div_start('form');
		$count = $this->fields_array2var();
		
		$sql = "SELECT ";
		$rowcount = 0;
		foreach( $this->entry_array as $row )
		{
			if( $rowcount > 0 ) $sql .= ", ";
			$sql .= $row['name'];
			$rowcount++;
		}
		$sql .= " from " . $this->table_interface->table_details['tablename'];
		if( isset( $this->table_interface->table_details['orderby'] ) )
			$sql .= " ORDER BY " . $this->table_interface->table_details['orderby'];
	
		$this->notify( __METHOD__ . ":" . __METHOD__ . ":" . __LINE__ . ":" . $sql, "WARN" );
		$this->notify( __METHOD__ . ":" . __METHOD__ . ":" . __LINE__ . ":" . " Display data", "WARN" );
		$this->display_table_with_edit( $sql, $this->entry_array, $this->table_interface->table_details['primarykey'] );
		div_end();
		div_start('generate');
		div_end();
	}
 */
	/*********************************************************************//**
	 *To show which products have an image count of 0           
	 *
	*	Allow user to update the items based upon missing data...
	************************************************************************/
	function missing_image()
	{
		$subquery = "select stock_id from " . $this->table_interface->table_details['tablename'] . " where image_count='0'";
		$title = "Stock Items missing Product Images";
		$instrows = array();
		$instrows[] = "These products do not have images associated to them in FrontAccounting";
		require_once( '../ksf_modules_common/class.fa_stock_master.php' );
		$fa_stock_master = new fa_stock_master( null );
		$fa_stock_master->display_edit_list_form( $subquery, true, $title, $instrows );
	}

	
}
