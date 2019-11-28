<?php



/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( __DIR__ . '/../ksf_modules_common/defines.inc.php' ); 
require_once( $path_to_ksfcommon . '/class.table_interface.php' ); 
require_once( $path_to_ksfcommon . '/class.generic_fa_interface.php' );
require_once( 'class.ksf_import_ofx.inc.php' );

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
		function get( $field )
        	function set( $field, $value = null, $enforce = false )
		function select_row()
		function install()
	        function insert_data( $data_arr )
 * Provides:
        function __construct( $prefs )
        function define_table()
	class_specific_funcs
        
 * 
 *
 * ***************************************************************/

require_once( 'class.bank_import_staging' );
class ksf_import_ofx_model extends bank_import_staging {

	function __construct( $prefs  = ksf_import_ofx_PREFS, $controller )
	{
		parent::__construct( null, null, null, null, $prefs );	//generic_interface has legacy mysql connection
									
		$this->controller = $controller;
	}
	/*
	function insert_data( $arr )
	{
		global $path_to_ksfcommon;

		require_once( $path_to_ksfcommon . /class.XXX.php );
		$XX = new XXX( $this );
		$XX->set( 'id', $arr['XXX'] );
		$XX->getById();
		$arr['XXX_name'] = $ba->get( 'XXX_name' );

		require_once( $path_to_ksfcommon . /class.YYY.php );
		$YY = new YYY( $this );
		$YY->set( 'terms_indicator', $arr['YYY'] );
		$YY->getById();
		$arr['YYY_name'] = $YY->get( 'YYY' );

		parent::insert_data( $arr );
	}
 	*/
}
