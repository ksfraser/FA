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
        function form_SIC_xref_gl
        function form_SIC_xref_gl_completed
        function action_show_form()
        function install()
        function master_form()
 * 
 *
 * ***************************************************************/


class SIC_xref_gl extends generic_fa_interface {
	var $id_SIC_xref_gl;	//!< Index of table
	var $table_interface;
	var $model;
	var $view;

	function __construct( $prefs )
	{
		parent::__construct( null, null, null, null, $prefs );	//generic_interface has legacy mysql connection
									//not needed with the $prefs
		/*
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		 */
		$this->tabs[] = array( 'title' => 'SIC_xref_gl Updated', 'action' => 'form_SIC_xref_gl_completed', 'form' => 'form_SIC_xref_gl_completed', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Update SIC_xref_gl', 'action' => 'form_SIC_xref_gl', 'form' => 'form_SIC_xref_gl', 'hidden' => FALSE );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
		$this->table_interface = new table_interface();
		$this->define_table();
							
	}
	function define_table()
	{
		require_once( 'class.SIC_xref_gl_model.php' );
		$this->model = new SIC_xref_gl_model( SIC_xref_gl_PREFS );
	}
	function form_SIC_xref_gl()
	{
		$this->call_table( 'form_SIC_xref_gl_completed', "Associate a downloaded vendor SIC number to a destination GL" );
	}
	function form_SIC_xref_gl_completed()
	{	//Need to add code here to do whatever this submodule is for...
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
		parent::install();
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

	
}
