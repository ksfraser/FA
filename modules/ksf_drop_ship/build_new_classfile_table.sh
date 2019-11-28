#!/bin/sh

#1 is class
#2 is action
#3 is action Human Readable

echo "<?php" > class.$1.php

echo "

\$path_to_root = \"../..\";

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
 *                 function __construct( \$host, \$user, \$pass, \$database, \$pref_tablename )
                function eventloop( \$event, \$method )
                function eventregister( \$event, \$method )
                function add_submodules()
                function module_install()
                function install()
                function loadprefs()
                function updateprefs()
                function checkprefs()
                function call_table( \$action, \$msg )
                function action_show_form()
                function show_config_form()
                function form_export()
                function related_tabs()
                function show_form()
                function base_page()
                function display()
                function run()
                function modify_table_column( \$tables_array )
                / *@fp@* /function append_file( \$filename )
                /*@fp@* /function overwrite_file( \$filename )
                /*@fp@* /function open_write_file( \$filename )
                function write_line( \$fp, \$line )
                function close_file( \$fp )
                function file_finish( \$fp )
                function backtrace()
                function write_sku_labels_line( \$stock_id, \$category, \$description, \$price )
		function show_generic_form(\$form_array)
 * Provides:
        function __construct( \$prefs )
        function define_table()
        function form_$2
        function form_$2_completed
        function action_show_form()
        function install()
        function master_form()
 * 
 *
 * ***************************************************************/


class $1 extends generic_fa_interface {
	var \$id_$1;	//!< Index of table
	var \$table_interface;
	function __construct( \$prefs )
	{
		parent::__construct( null, null, null, null, \$prefs );	//generic_interface has legacy mysql connection
									//not needed with the \$prefs
		/*
		\$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		\$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		\$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		\$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		 */
		\$this->tabs[] = array( 'title' => '$1 Updated', 'action' => 'form_$1_completed', 'form' => 'form_$1_completed', 'hidden' => TRUE );
		\$this->tabs[] = array( 'title' => 'Update $1', 'action' => 'form_$1', 'form' => 'form_$1', 'hidden' => FALSE );
		//We could be looking for plugins here, adding menu's to the items.
		\$this->add_submodules();
		\$this->table_interface = new table_interface();
		\$this->define_table();
							
	}
	function define_table()
	{
		\$this->table_interface->table_details['tablename'] = TB_PREF . '$1';
		//woo_interface::define_table();
		//\$this->table_interface->fields_array[] = array('name' => 'stock_id', 'label' => 'SKU', 'type' => \$sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		//\$sidl = 'varchar(' . STOCK_ID_LENGTH . ')';
		//\$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';



		//\$this->table_interface->fields_array[] = array('name' => 'variablename', 'type' => \$sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		//\$this->table_interface->fields_array[] = array('name' => 'stock_id', 'label' => 'Stock ID', 'type' => \$sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', /*'foreign_obj' => 'woo_prod_variable_master', 'foreign_column' => 'stock_id'*/ 'comment' => 'Master Product stock_id');
		//\$this->table_interface->fields_array[] = array('name' => 'sku', 'label' => 'SKU', 'type' => \$sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'generated sku for this variable product' );
		\$this->table_interface->fields_array[] = array('name' => 'description', 'label' => 'Description', 'type' => \$descl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite' );
		\$this->table_interface->fields_array[] = array('name' => 'inserted_fa', 'label' => 'Inserted into FA', 'type' => 'bool', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		\$this->table_interface->fields_array[] = array('name' => 'woo_id', 'label' => 'WooCommerce ID', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );

		\$this->table_interface->table_details['primarykey'] = "stock_id";
		//\$this->table_interface->table_details['orderby'] = 'sku';
//		\$this->table_interface->table_details['index'][0]['type'] = 'unique';
//		\$this->table_interface->table_details['index'][0]['columns'] = \"stock_id, sku\";
//		\$this->table_interface->table_details['index'][0]['keyname'] = \"stock_id-sku\";
//		\$this->table_interface->table_details['index'][1]['type'] = 'unique';
//		\$this->table_interface->table_details['index'][1]['columns'] = \"sku\";
//		\$this->table_interface->table_details['index'][1]['keyname'] = \"sku\";
//
//		//\$this->table_interface->table_details['foreign'][0] = array( 'column' => \"variablename\", 'foreigntable' => \"woo_prod_variable_variables\", \"foreigncolumn\" => \"variablename\", \"on_update\" => \"restrict\", \"on_delete\" => \"restrict\" );	
//		//\$this->table_interface->table_details['foreign'][1] = array( 'column' => \"stock_id\", 'foreigntable' => \"woo_prod_variable_master\", \"foreigncolumn\" => \"stock_id\", \"on_update\" => \"restrict\", \"on_delete\" => \"restrict\" );
	}
	function form_$2
	{
		\$this->call_table( 'form_$2_completed', \"$3\" );
	}
	function form_$2_completed
	{	//Need to add code here to do whatever this submodule is for...
	}
	function action_show_form()
	{
		\$this->install();
		parent::action_show_form();
	}
	function install()
	{
		\$this->table_interface->create_table();
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
		global \$Ajax;
		\$this->notify( __METHOD__ . \"::\"  . __METHOD__ . \":\" . __LINE__, \"WARN\" );
		\$this->create_full();
		div_start('form');
		\$count = \$this->fields_array2var();
		
		\$sql = \"SELECT \";
		\$rowcount = 0;
		foreach( \$this->entry_array as \$row )
		{
			if( \$rowcount > 0 ) \$sql .= \", \";
			\$sql .= \$row['name'];
			\$rowcount++;
		}
		\$sql .= \" from \" . \$this->table_interface->table_details['tablename'];
		if( isset( \$this->table_interface->table_details['orderby'] ) )
			\$sql .= \" ORDER BY \" . \$this->table_interface->table_details['orderby'];
	
		\$this->notify( __METHOD__ . \":\" . __METHOD__ . \":\" . __LINE__ . \":\" . \$sql, \"WARN\" );
		\$this->notify( __METHOD__ . \":\" . __METHOD__ . \":\" . __LINE__ . \":\" . \" Display data\", \"WARN\" );
		\$this->display_table_with_edit( \$sql, \$this->entry_array, \$this->table_interface->table_details['primarykey'] );
		div_end();
		div_start('generate');
		div_end();
	}

	
}" >> class.$1.php

