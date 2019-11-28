#!/bin/sh

#1 is class
#2 is action
#3 is action Human Readable

echo "<?php" > class.$1_model.php

echo "


/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( __DIR__ . '/../ksf_modules_common/defines.inc.php' ); 
require_once( \$path_to_ksfcommon . '/class.table_interface.php' ); 
require_once( \$path_to_ksfcommon . '/class.generic_fa_interface.php' );
require_once( 'class.$1.inc.php' );

/*************************************************************//**
 * A model (MVC) class for handling data, in and out of a database
 * 
 *	There are some (FA specific) display_* functions commented
 *	out in this class.  As this is not a VIEW class, it really
 * 	should not be doing any displays.  Need to determine if there
 *	is a way short of throwing Exceptions to pass that back
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
		function get( $field )
        	function set( $field, $value = null, $enforce = false )
		function select_row()
		function install()
	        function insert_data( $data_arr )
 * Provides:
        function __construct( \$prefs )
        function define_table()
	class_specific_funcs
        
 * 
 *
 * ***************************************************************/


class $1_model extends generic_fa_interface {
	var \$id_$1;	//!< Index of table
	//protected \$class_specific_vars;
	protected \$id;
	protected \$created_ts;		//!< The timestamp of when the record was created
	protected \$updated_ts;		//!< The timestamp of when the record was last updated

	function __construct( \$prefs  = $1_PREFS, \$controller )
	{
		parent::__construct( null, null, null, null, \$prefs );	//generic_interface has legacy mysql connection
									//not needed with the \$prefs
									
		\$this->controller = \$controller;
		\$this->table_interface = new table_interface();
		\$this->define_table();
							
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
		\$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		\$stockl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		\$refl = 'varchar(' . REFERENCE_LENGTH . ')';
		\$loccdl = 'varchar(' . LOC_CODE_LENGTH . ')';

		\$this->table_interface->table_details['tablename'] = TB_PREF . '$1';
		\$this->table_interface->table_details['primarykey'] = 'id';
		//\$this->table_interface->table_details['orderby'] = 'sku';

		\$this->table_interface->fields_array[] = array('name' => 'id', 'label' => 'Table ID', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		\$this->table_interface->fields_array[] = array('name' => 'updated_ts', 'label' => 'Updated Timestamp', 'type' => 'timestamp', 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		\$this->table_interface->fields_array[] = array('name' => 'created_ts', 'label' => 'Created Timestamp', 'type' => 'timestamp', 'null' => 'NOT NULL',  'readwrite' => 'readwrite');

		//\$this->table_interface->fields_array[] = array('name' => 'stock_id', 'label' => 'SKU', 'type' => \$sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite');

//		\$this->table_interface->table_details['index'][0]['type'] = 'unique';
//		\$this->table_interface->table_details['index'][0]['columns'] = \"stock_id, sku\";
//		\$this->table_interface->table_details['index'][0]['keyname'] = \"stock_id-sku\";
//
//		//\$this->table_interface->table_details['foreign'][0] = array( 'column' => \"variablename\", 'foreigntable' => \"woo_prod_variable_variables\", \"foreigncolumn\" => \"variablename\", \"on_update\" => \"restrict\", \"on_delete\" => \"restrict\" );	
	}
	/*******************************************************//**
	* Lookup some names from the keys passed in from a select form before inserting
	*
	***********************************************************/
	function insert_data( \$arr )
	{
		global \$path_to_ksfcommon;

//		require_once( \$path_to_ksfcommon . "/class.XXX.php" );
//		\$XX = new XXX( \$this );
//		\$XX->set( 'id', \$arr['XXX'] );
//		\$XX->getById();
//		\$arr['XXX_name'] = \$ba->get( 'XXX_name' );
//
//		require_once( \\$path_to_ksfcommon . "/class.YYY.php" );
//		\$YY = new YYY( \$this );
//		\$YY->set( 'terms_indicator', \$arr['YYY'] );
//		\$YY->getById();
//		\$arr['YYY_name'] = \$YY->get( 'YYY' );

		parent::insert_data( \$arr );
	}
}" >> class.$1_model.php

