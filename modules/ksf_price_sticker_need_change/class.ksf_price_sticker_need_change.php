<?php


$path_to_root = "../..";

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

 require_once( '../ksf_modules_common/defines.inc.php' );
require_once( $path_to_ksfcommon . '/class.table_interface.php' ); 
require_once( $path_to_ksfcommon . '/class.generic_fa_interface.php' );


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
        function form_ksf_price_sticker_need_change
        function form_ksf_price_sticker_need_change_completed
        function action_show_form()
        function install()
        function master_form()
 * 
 *
 *
 * This class acts as a controller
 * ***************************************************************/


class ksf_price_sticker_need_change extends generic_fa_interface_controller {
	var $id_ksf_price_sticker_need_change;	//!< Index of table
	var $table_interface;
	function __construct( $prefs )
	{
		parent::__construct( null, null, null, null, $prefs );	//generic_interface has legacy mysql connection
									//not needed with the $prefs
		$this->set_var( 'found', $this->is_installed() );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		require_once( 'class.ksf_price_sticker_need_change_view.php' );
		$this->view = new ksf_price_sticker_need_change_view( $prefs, $this );
		$this->tabs = $this->view->tabs;	//Short term work around until VIEW code everywhere

		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
		require_once( 'class.ksf_price_sticker_need_change_model.php' );
		$this->model = new ksf_price_sticker_need_change_model( ksf_price_sticker_need_change_PREFS, $this );	//defines the tabl
		$this->model->set( found, $this->get( found ) );

	}
	function run()
	{
		if( isset( $_POST['ksf_price_sticker_need_change'] ) )
		{
			$this->model->insert_data( $_POST );
		}
		else if( $this->delete >= 0 )
		{
			$this->handle_delete();
		}
		else if( $this->edit >= 0 )
		{
			$this->handle_edit();
		}

		parent::run();
	}
	function action_show_form()
	{
		$this->install();
		parent::action_show_form();
	}
	function install()
	{
		$this->model->create_table();
		$this->model->install();
		//parent::install();	//_model calls parent::isntall as well so we shouldn't need to.
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
	function master_form()
	{
		global $Ajax;
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
	
		$this->display_table_with_edit( $sql, $this->entry_array, $this->table_interface->table_details['primarykey'] );
		div_end();
		div_start('generate');
		div_end();
	}

	
}
