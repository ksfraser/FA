<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( 'class.woo_interface.php' );
//require_once( 'class.EXPORT_WOO.inc.php' ); //Constants


class woo_target_ui extends woo_interface
{
	var $selected_id;
	var $woo_target_array;
	function __construct( $client )
	{
		parent::__construct(null, null, null, null, $client);
		return;
	}
	/*****************************************************************//**
	 * Display a form asking for the fields of a target
	 *
	 * ******************************************************************/
	function form_woo_target_entry()
	{
		return;
	}
	/*****************************************************************//**
	 * Display a dropdown selection box with list of targets
	 *
	 * ******************************************************************/
	function form_woo_target_select()
	{
		return;
	}
	/*****************************************************************//**
	 * Take field data from form and insert into table
	 *
	 * ******************************************************************/
	function POST2db()
	{
	}
}

/******************************************************************//**
 * We are now at the point of wanting 2 or more instances of WooCommerce
 *  - public site
 *  - laptop (till) for trade shows (Highland Games)
 *  so need to be able to send to multiple places
 *
 *  This class takes care of the data model
 * *******************************************************************/
class woo_target extends woo_interface {
	//Inherited
	//	table_interface
	//		function insert_table();
	//		function update_table();
	//		function select_table($fieldlist = "*", /*@array@*/$where = null, /*@array@*/$orderby = null, /*@int@*/$limit = null);
	//		function count_rows()
	//		function count_filtered($where);
	//		function alter_table()
	//		function create_table()
	//	woo_interface
	//		function backtrace
	//		function tell( $msg, $method )
	//		function build_interestedin()	- NEEDS TO BE OVERRIDDEN
	//		function notified
	//		function register
	//		function notify
	//		function fields_array2var - POST values to class Vars
	//		function fields_array2entry - create EDIT forms
	//		function display_table_with_edit( $sql, $headers, $index, $return_to = null )
	//		function form_post_handler()
	//		function display_edit_form
	//		function combo_list(), combo_list_cells(), combo_list_row() - probably shouldn't need to call these!
	//		function build_*_array - probably shouldn't need to call these
	//		function unset_values();
	//		function extract_data_objects( $obj_array )
	//		/*@int@*/function extract_data_array( $assoc_array )
	//		/*@int@*/function extract_data_obj( $srvobj )
	//		function build_json_data()
	//		/*@bool@*/function prep_json_for_send( $func = NULL )
	//		function ll_walk_insert_fa()
	//		function ll_walk_update_fa()
	var $lastoid;
	var $image_server_url; 	//!< Server URL for images (http[s]://servername/FA_base)
	var $image_baseURL; 	//!< Base URL for images (/company/0/images)
	var $use_img_baseurl; 	//!< Use Base URL or remote (true/false)
	var $woo_ck;		//!< API Key
	var $woo_cs;		//!< API Secret
	var $woo_server; 	//!< Base URL for WOO server (...wordpress)
	var $woo_rest_path;	//!< Path for REST API ("/wp-json/wc/v1/)
	var $remote_img_srv; 	//!< Is the images stored on a remote server? (Assume we copied from images dir)(0/1)
	var $environment;	//!< Environment (devel/accept/prod)
	var $maxpics;	//!< int maximum number of pics for a product.  Integrate into module allowing more than 1!
	var $debug;
	var $force_update; //!< bool update ALL products/... rather than only find ones with a timestamp newer here than our record with Woo
	var $updated_ts;
	var $id;	//!< The ID of the row we are searching for
	var $id_woo_target;
	var $target_name;

/*		var $woo_last_update;
		var $woo_id;
		var $category_id;
		var $category;
		var $woo_category_id;
 */

	function __construct($serverURL, $key, $secret, $options, $client)
	{
		parent::__construct($serverURL, $key, $secret, $options, $client);
		$this->filter_new_only = FALSE;
		if( isset( $client->force_update ) )
			$this->force_update = $client->force_update;

		//$this->define_table();
		return;
	}
	function define_table()
	{
		$sidl = 'varchar(' . STOCK_ID_LENGTH . ')';
		$vc_t = 'varchar(255)';
		$bool_t = 'int(1)';
		$int_t = 'int(11)';
		
	$this->fields_array[] = array('name' => 'id_' . $this->iam, 'type' => $int_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
	$this->fields_array[] = array('name' => 'target_name', 'type' => $vc_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Target Name' );
	$this->fields_array[] = array('name' => 'image_server_url', 'type' => $vc_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Server URL for images https_servername_FA_base' );
	$this->fields_array[] = array('name' => 'image_baseURL', 'type' => $vc_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Base URL for images company_0_images')
	$this->fields_array[] = array('name' => 'use_img_baseurl', 'type' => $bool_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Use Base URL or remote true_false');
	$this->fields_array[] = array('name' => 'woo_ck', 'type' => $vc_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'API Key' );
	$this->fields_array[] = array('name' => 'woo_cs', 'type' => $vc_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'API Secret' );
	$this->fields_array[] = array('name' => 'woo_server', 'type' => $vc_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Base URL for WOO server ..wordpress');
	$this->fields_array[] = array('name' => 'woo_rest_path', 'type' => $vc_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Path for REST API _wp-json_wc_v1_');
	$this->fields_array[] = array('name' => 'remote_img_srv', 'type' => $vc_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Is the images stored on a remote server Assume we copied from images dir 0_1' );
	$this->fields_array[] = array('name' => 'environment', 'type' => $vc_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Environment devel_acpt_prod');
	$this->fields_array[] = array('name' => 'maxpics', 'type' => $int_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'maximum number of pics for a product.  Integrate into module allowing more than 1' );
	$this->fields_array[] = array('name' => 'debug', 'type' => $int_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
	$this->fields_array[] = array('name' => 'force_update', 'type' => $bool_t, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'update ALL products rather than only find ones with a timestamp newer here than our record with Woo' );
	$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL',  'readwrite' => 'readwrite');

		

		//$this->table_details['tablename'] = TB_PREF . "woo_categories_xref";
		$this->table_details['tablename'] = $this->company_prefix . $this->iam;
		$this->table_details['primarykey'] = "id_" . $this->iam;

		/*
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "order_id,first_name,last_name,address_1,city,state";
		$this->table_details['index'][0]['keyname'] = "order-billing_address_customer";
		$this->table_details['index'][1]['type'] = 'unique';
		$this->table_details['index'][1]['columns'] = "customer_id,first_name,last_name,address_1,city,state";
		$this->table_details['index'][1]['keyname'] = "customer-billing_address_customer";
		 */
	}
	/**************************************************************//**
	 * Select the details of 1 target.  Requires that stock_id is set
	 *
	 * ****************************************************************/
	function select_target()
	{
		$prod_sql = 	"select * from " . TB_PREF . $this->iam;
		$prod_sql .= " where id_" . $this->iam. " = '" . $this->id . "'";
		$res = db_query( $prod_sql, __LINE__ . " Couldn't select target(s) for export" );
		$prod_data = db_fetch_assoc( $res );
		foreach( $this->fields_array as $fieldrow )
		{
			if( isset( $prod_data[ $fieldrow['name'] ] ) )
				$this->$fieldrow['name'] = $prod_data[ $fieldrow['name'] ];
		}
	}

}

?>
