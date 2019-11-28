#!/bin/sh

#1 is class
#2 is action
#3 is action Human Readable

echo "<?php" > class.$1_config.php

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
 * 
 *
 * ***************************************************************/


class $1_model extends generic_fa_interface {
	var \$id_$1;	//!< Index of table
	var \$table_interface;
	function __construct( \$prefs )
	{
		parent::__construct( null, null, null, null, \$prefs );	//generic_interface has legacy mysql connection
									//not needed with the \$prefs
		/*
		\$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		\$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		//We could be looking for plugins here, adding menu's to the items.
		\$this->add_submodules();
	}

	
}" >> class.$1_config.php

