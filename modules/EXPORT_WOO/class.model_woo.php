<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( 'class.woo_interface.php' );
//require_once( 'class.EXPORT_WOO.inc.php' ); //Constants

/******************************************************************//**
 * This class is a MVC Model class - designed for handling the WOO table in frontaccounting.
 *
 * Provides:
  	function __construct($serverURL, $key, $secret, $options, $client)
        function define_table()
        function reset_endpoint()
        / *@int@* / function populate_woo_table()
        function select_product()
        function insert_product()
        function update_product_details()
        function update_prices()
        function zero_null_prices()
        function update_qoh_count()
        function update_on_sale_data()
        function update_woo_id()
        function update_woo_last_update()
        function clear_woocommerce_data()
        function staledate_specials()
        function update_specials()
        function update_tax_data()
        function update_shipping_dimensions()
        function update_crosssells()
        function update_category_data()
        function update_category_xref()
        function missing_from_table_query()
        function create_price_book()
        function count_new_products()
        / *@array@* /function new_simple_product_ids( $max = 0 )
        / *@array@* /function all_simple_product_ids()
        / *@array@* /function simple_product_ids( $max = 0 )
        / *@mysql_res@* /function select_simple_products_for_export()
        / *@mysql_res@* /function select_simple_products( $max = 0 )
        / *@mysql_res@* /function select_simple_products_for_update()
        function delete_by_sku( $sku )
 * Inherits: (woo_interface)
         function fuzzy_match( $data )
        function rebuild_woocommerce()
        function backtrace()
        function tell( $msg, $method )
        function tell_eventloop( $caller, $event, $msg )
        function dummy( $obj, $msg )
        function register_with_eventloop()
        function build_interestedin()
        function notified( $obj, $event, $msg )
        function register( $msg, $method )
        function notify( $msg, $level = "ERROR" )
        /  *  @int@ * /function fields_array2var()
        function master_form()
        /  *  @array@ * /function fields_array2entry()
        function display_table_with_edit( $sql, $headers, $index, $return_to = null )
        function form_post_handler()
        function display_edit_form( $form_def, $selected_id = -1, $return_to )
        function combo_list( $sql, $order_by_field, $name, $selected_id=null, $none_option=false, $submit_on_change=false)
        function combo_list_cells( $sql, $order_by_field, $label, $name, $selected_id = null, $none_option=false, $submit_on_change=false )
        function combo_list_row( $sql, $order_by_field, $label, $name, $selected_id = null, $none_option=false, $submit_on_change=false )
        function define_table()
        function build_write_properties_array()
	function build_properties_array()
        function build_foreign_objects_array()
        function array2var( $data_array )
        function build_data_array()
        function reset_values()
        function extract_data_objects( $srvobj_array )
        /  *  @int@ * /function extract_data_array( $assoc_array )
        /  *  @int@ * /function extract_data_obj( $srvobj )
        function build_json_data()
        /  *  @bool@ * /function prep_json_for_send( $func = NULL )
        function ll_walk_insert_fa()
        function ll_walk_update_fa()
        function reset_endpoint()
        function error_handler( /  *  @Exception@ * / $e )
        function retrieve_woo( $search_array = null )
        function log_exception( $e, $client )
 * INHERITS (table_interface)
        function get( $field )
        / * @bool@ *  /function set( $field, $value = null )
        / * @bool@ *  /function validate( $data_value, $data_type )
        / * none *  /function select_row( $set_caller = false )
        / * @mysql_result@ *  /function select_table($fieldlist = " * ", / * @array@ *  /$where = null, / * @array@ *  /$orderby = null, / * @int@ *  /$limit = null)
        function delete_table()
        function update_table()
        / * @bool@ *  /function check_table_for_id()
        / * @int@ *  /function insert_table()
        function create_table()
        function alter_table()
        / * @int@ *  /function count_rows()
        / * @int@ *  /function count_filtered($where = null)
        / * string *  /function getPrimaryKey()
        / * none *  /function getByPrimaryKey()
        function assoc2var( $assoc )            Take an associated array and take returned values and place into the calling MODEL class
        function get( $field )
        function query( $msg )
        function buildLimit()
        function buildSelect( $b_validate_in_table = false)
        function buildFrom()
        function buildWhere( $b_validate_in_table = false)
        function buildOrderBy( $b_validate_in_table = false)
        function buildGroupBy( $b_validate_in_table = false)
        function buildHaving( )
        function buildJoin()
        function buildSelectQuery( $b_validate_in_table = false )
        function clear_sql_vars()
        function assoc2var( $assoc )
        function var2caller()

 *
 * *******************************************************************/
class model_woo extends woo_interface {
		var $stock_id;
		var $updated_ts;
		var $woo_last_update;
		var $woo_id;
		var $category_id;
		var $category;
		var $woo_category_id;
		var $description;
		var $long_description;
		var $units;
		var $price;
		var $instock;
		var $saleprice;
		var $date_on_sale_from;
		var $date_on_sale_to;
		var $external_url;
		var $tax_status;
		var $tax_class;
		var $weight;
		var $length;
		var $width;
		var $height;
		var $shipping_class;
		var $upsell_ids;
		var $crosssell_ids;
		var $parent_id;
		var $attributes;
		var $default_attributes;
		var $variations;
		var $filter_new_only;
		var $force_update; //!< bool grabbed from client

	function __construct($serverURL, $key, $secret, $options, $client)
	{
		parent::__construct($serverURL, $key, $secret, $options, $client);
		$this->filter_new_only = FALSE;
		if( isset( $client->force_update ) )
			$this->force_update = $client->force_update;

		//$this->define_table();
		return;
	}
	function build_interestedin()
        {
                //This NEEDS to be overridden
                $this->interestedin[WOO_DUMMY_EVENT]['function'] = "dummy";
                $this->interestedin[FA_NEW_STOCK_ID]['function'] = "insert_product";
                $this->interestedin[FA_PRODUCT_UPDATED]['function'] = "update_product_details";
                $this->interestedin[FA_PRODUCT_LINKED]['function'] = "update_crosssells";
                $this->interestedin[FA_PRICE_UPDATED]['function'] = "update_prices";
                $this->interestedin[KSF_WOO_RESET_ENDPOINT]['function'] = "reset_endpoint";
                $this->interestedin[KSF_WOO_INSTALL]['function'] = "define_table";
                $this->interestedin[KSF_WOO_INSTALL]['function'] = "populate_woo_table";
                $this->interestedin[KSF_SALE_ADDED]['function'] = "update_on_sale_data";
                $this->interestedin[KSF_SALE_REMOVED]['function'] = "update_on_sale_data";
                $this->interestedin[KSF_SALE_EXPIRED]['function'] = "update_on_sale_data";
                $this->interestedin[KSF_WOO_GET_PRODUCT]['function'] = "select_product";	//stock_id must be set
                $this->interestedin[KSF_WOO_GET_PRODUCTS_ALL]['function'] = "";
/*
        function zero_null_prices()
        function update_qoh_count()
        function update_woo_id()
        function update_woo_last_update()
        function clear_woocommerce_data()
        function staledate_specials()
        function update_specials()
        function update_tax_data()
        function update_shipping_dimensions()
        function update_crosssells()
        function update_category_data()
        function update_category_xref()
        function missing_from_table_query()
        function create_price_book()
        function count_new_products()
        / *@array@* /function new_simple_product_ids( $max = 0 )
        / *@array@* /function all_simple_product_ids()
        / *@array@* /function simple_product_ids( $max = 0 )
        / *@mysql_res@* /function select_simple_products_for_export()
        / * @mysql_res@* /function select_simple_products( $max = 0 )
        / * @mysql_res@* /function select_simple_products_for_update()
        function delete_by_sku( $sku )
*/
        }
	function define_table()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sidl = 'varchar(' . STOCK_ID_LENGTH . ')';
		$this->fields_array[] = array('name' => 'stock_id', 'label' => 'SKU', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite' );
		$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->fields_array[] = array('name' => 'woo_last_update', 'type' => 'timestamp', 'null' => 'NOT NULL',);
		$this->fields_array[] = array('name' => 'woo_id', 'type' => 'varchar(32)' );
		$this->fields_array[] = array('name' => 'category_id', 'type' => 'int(11)' );
		$this->fields_array[] = array('name' => 'category', 'type' => 'varchar(64)' );
		$this->fields_array[] = array('name' => 'woo_category_id', 'type' => 'int(11)' );
		$this->fields_array[] = array('name' => 'description', 'type' => 'varchar(200)' );
		$this->fields_array[] = array('name' => 'long_description', 'type' => 'text' );
		$this->fields_array[] = array('name' => 'units', 'type' => 'varchar(20)' );
		$this->fields_array[] = array('name' => 'price', 'type' => 'double' );
		$this->fields_array[] = array('name' => 'instock', 'type' => 'int(11)' );
		$this->fields_array[] = array('name' => 'saleprice', 'type' => 'float' );
		$this->fields_array[] = array('name' => 'date_on_sale_from', 'type' => 'date', 'null' => 'NOT NULL');
		$this->fields_array[] = array('name' => 'date_on_sale_to', 'type' => 'date', 'null' => 'NOT NULL');
		$this->fields_array[] = array('name' => 'external_url', 'type' => 'varchar(128)' );
		$this->fields_array[] = array('name' => 'tax_status', 'type' => 'varchar(32)' );
		$this->fields_array[] = array('name' => 'tax_class', 'type' => 'varchar(32)' );
		$this->fields_array[] = array('name' => 'weight', 'type' => 'float' );
		$this->fields_array[] = array('name' => 'length', 'type' => 'float' );
		$this->fields_array[] = array('name' => 'width', 'type' => 'float' );
		$this->fields_array[] = array('name' => 'height', 'type' => 'float' );
		$this->fields_array[] = array('name' => 'shipping_class', 'type' => 'varchar(32)' );
		$this->fields_array[] = array('name' => 'upsell_ids', 'type' => 'varchar(128)' );
		$this->fields_array[] = array('name' => 'crosssell_ids', 'type' => 'varchar(128)' );
		$this->fields_array[] = array('name' => 'parent_id', 'type' => 'varchar(32)' );
		$this->fields_array[] = array('name' => 'attributes', 'type' => 'varchar(255)' );
		$this->fields_array[] = array('name' => 'default_attributes', 'type' => 'varchar(255)' );
		$this->fields_array[] = array('name' => 'variations', 'type' => 'varchar(255)' );

		//$this->table_details['tablename'] = TB_PREF . "woo_categories_xref";
		$this->table_details['tablename'] = $this->company_prefix . "woo";
		$this->table_details['primarykey'] = "stock_id";

		/*
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "order_id,first_name,last_name,address_1,city,state";
		$this->table_details['index'][0]['keyname'] = "order-billing_address_customer";
		$this->table_details['index'][1]['type'] = 'unique';
		$this->table_details['index'][1]['columns'] = "customer_id,first_name,last_name,address_1,city,state";
		$this->table_details['index'][1]['keyname'] = "customer-billing_address_customer";
		 */
	}
	function reset_endpoint()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$this->endpoint = "products";
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/*******************************************//**********
	* Initial population of data.  Probably should only run once
	*
	* @params none
	* @returns int count of rows
	****************************************************/
	/*@int@*/ function populate_woo_table()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
               $this->insert_product();
                $this->update_product_details();
                $this->update_prices();
                $this->zero_null_prices();
                $this->update_qoh_count();
                $this->staledate_specials();
                //$this->update_specials();
                $this->update_tax_data();
                //$this->update_shipping_dimensions();
                //$this->update_crosssells();
                $this->update_category_data();
                $this->update_category_xref();
                $rowcount = $this->count_rows();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $rowcount;
	}
	/**************************************************************//**
	 * Select the details of 1 product.  Requires that stock_id is set
	 *
	 * ****************************************************************/
	function select_product()
	{
		if( ! isset( $this->stock_id ) )
			throw new Exception( "Stock_id must be set!", KSF_VALUE_NOT_SET );
/*****THIS DOESN"T WORK AS EXPECTED
		$this->clear_sql_vars();
                $this->select_array = array( 'stock_id', 'woo_category_id', 'description', 'long_description', 'price', 'instock', 'sale_price', 'date_on_sale_from', 'date_on_sale_to', 'external_url', 'tax_status', 'tax_class', 'weight', 'length', 'width', 'height', 'shipping_class', 'upsell_ids', 'crosssell_ids', 'parent_id', 'attributes', 'default_attributes', 'variations', 'woo_id');
		$this->from_array = array( $this->table_details['tablename'] );
                $this->where_array = array( 'stock_id' => $this->stock_id );
                $this->groupby_array = array();
                $this->buildSelectQuery();
                $res = $this->query( "Couldn't select woo category", "select");
                $prod_data = db_fetch_assoc( $res );
****/

/***BELOW TESTED WORKING*/
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$prod_sql = 	"select stock_id, woo_category_id, description, long_description, price, instock, 
				sale_price, date_on_sale_from, date_on_sale_to, external_url, tax_status, tax_class, 
				weight, length, width, height, shipping_class, upsell_ids, crosssell_ids, parent_id, 
				attributes, default_attributes, variations, woo_id
				from " . TB_PREF . "woo";
		$prod_sql .= " where stock_id = '" . $this->stock_id . "'";
		$res = db_query( $prod_sql, __LINE__ . " Couldn't select product(s) for export" );
		$prod_data = db_fetch_assoc( $res );
/****/
		foreach( $this->fields_array as $fieldrow )
		{
			if( isset( $prod_data[ $fieldrow['name'] ] ) )
				$this->$fieldrow['name'] = $prod_data[ $fieldrow['name'] ];
		}
//		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function insert_product()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql_create = "insert ignore into " . $this->table_details['tablename'] . " ( stock_id"
				. ", category_id"
				. ", description"
				. ", long_description"
				. ", units"
				."	)
			 select 
				sm.stock_id 
				, sm.category_id"
				. ", sm.description"
				. ", sm.long_description"
				. ", sm.units"
			. " from " . TB_PREF . "stock_master sm"
			. " WHERE inactive=0"
			;
		$res = db_query( $sql_create, "Couldnt create items in  WOO" );
//		$this->tell( WOO_PRODUCT_INSERT, __METHOD__ );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function update_product_details()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql_update = "update " . $this->table_details['tablename'] . " woo, " . TB_PREF . "stock_master sm
			set
				woo.category_id = sm.category_id
				, woo.description = sm.description
				, woo.long_description = sm.long_description
				, woo.units = sm.units
			where woo.stock_id = sm.stock_id";
		$this->notify( __METHOD__ . ":" . __LINE__ . " SQL to be run: " . $sql_update, "WARN" );
		$res = db_query( $sql_update, "Couldnt update stock_master details in  WOO" );
//		$this->tell( WOO_PRODUCT_UPDATE, __METHOD__ );
//		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function update_prices()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql_update2 = "update " . $this->table_details['tablename'] . " woo,  " . TB_PREF . "prices p
			set
				woo.price = p.price
			where woo.stock_id = p.stock_id
				and p.sales_type_id = '1'";
		$res = db_query( $sql_update2, "Couldnt update prices in  WOO" );
//		$this->tell( WOO_PRODUCT_PRICE_UPDATE, __METHOD__ );
//		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function zero_null_prices()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql_update2a = "update " . $this->table_details['tablename'] . " woo
			set
				woo.price = '0'
			where woo.price is null";
		$res = db_query( $sql_update2a, "Couldnt update NULL prices in  WOO" );
		//$this->tell( WOO_PRODUCT_PRICE_NULL2ZERO, __METHOD__ );
//		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/*********************************************************************//**
	 * This function updates the QOH count within the WOO table so the SQL statement here is OK.
	 *
	 * It is dependant on either an external QOH module or the included QOH
	 * class/table to hold the values of QOH by product.
	 *
	 * It is also dependant on all of the items ins stock_master have already
	 * been inserted into this table to be updated or the items will be missed.
	 *
	 * ***********************************************************************/
	function update_qoh_count()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		global $path_to_root;
		if( @include_once( '../ksf_qoh/class.ksf_qoh.php' ) )
		{
			//Independant module.  This module is where all
			//future development for QOH will happen.
			include_once($path_to_root . "/modules/ksf_qoh/ksf_qoh.inc.php"); //KSF_QOH_PREFS
			$qoh = new ksf_qoh( KSF_QOH_PREFS );
			$qoh->define_table();
			$qoh_table = $qoh->table_interface->table_details['tablename'];
		}
		else if( @include_once( 'class.qoh.php' ) )
		{
			//included class
			$qoh = new qoh( null, null, null, null, $this );
			$qoh->define_table();
			$qoh_table = $qoh->table_details['tablename'];
		}
		else
		{
			$qoh_table = TB_PREF . "qoh";
		}
		//Grab the count out of the QOH table.  

		$sql_update3 = "update " . $this->table_details['tablename'] . " woo,  " .  $qoh_table . " q
			set
				woo.instock = q.instock
			where woo.stock_id = q.stock_id";
		$res = db_query( $sql_update3, "Couldnt update Quantity On Hand in  WOO" );
	//	$this->tell( WOO_PRODUCT_QOH_UPDATE, __METHOD__ );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function update_on_sale_data()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( !isset( $this->stock_id ) )
			throw new InvalidArgumentException( "stock_id" );
		$updateprod_sql = "update " . $this->table_details['tablename'] . " set
					date_on_sale_from = '" . $this->date_on_sale_from . "',
					date_on_sale_to = '" . $this->date_on_sale_to . "',
					tax_status = '" . $this->tax_status . "'";
		$updateprod_sql .= " where stock_id = '" . $this->stock_id . "'";
		$res = db_query( $updateprod_sql, "Couldn't update product after export" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************//**
	*
	* @param UNUSED compatibility with woo_interface
	**************************************************************/
	function update_woo_id( /*unused*/ $id )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( !isset( $this->stock_id ) )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "ERROR" );
			throw new InvalidArgumentException( "stock_id" );
		}
		$updateprod_sql = "update " . $this->table_details['tablename'] . " set
					woo_id = '" . $this->woo_id . "'";		
		$updateprod_sql .= ", woo_last_update=now()";
		$updateprod_sql .= " where stock_id = '" . $this->stock_id . "'";
		$this->notify( __METHOD__ . ":" . __LINE__ . " Updating stock_id:  " . $this->stock_id  . " with " . $this->woo_id, "WARN" );
		$res = db_query( $updateprod_sql, "Couldn't update woo_id after export" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Query: " . $updateprod_sql, "DEBUG" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function update_woo_last_update()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( !isset( $this->stock_id ) )
			throw new InvalidArgumentException( "stock_id" );
		$updateprod_sql = "update " . $this->table_details['tablename'] . " set
					woo_last_update = '" . $this->woo_last_update . "'";
		$updateprod_sql .= " where stock_id = '" . $this->stock_id . "'";
		$res = db_query( $updateprod_sql, "Couldn't update woo_id after export" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/**********************************************//***
	* If you have to rebuild your woocommerce store you need to resend everything
	*
	* Clear the data that tells this module to send an
	* update rather than a send
	*
	* @param none
	* @return none
	***********************************************/
	function clear_woocommerce_data()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql_update = "update " . $this->table_details['tablename'] . " woo
			set
				woo.woo_last_update = '0000-01-01', woo.woo_id = null, woo.woo_category_id = null";
		$res = db_query( $sql_update, "Couldnt reset Woocommerce data to null" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function staledate_specials()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql_update4 = "update " . $this->table_details['tablename'] . " woo
			set
				woo.date_on_sale_to = '2015-01-01'";
		$res = db_query( $sql_update4, "Couldnt invalidate Sales and Specials in  WOO" );
		$sql_update4a = "update " . $this->table_details['tablename'] . " woo
			set
				woo.sale_price = woo.price
			";
		$res = db_query( $sql_update4a, "Couldnt set sales prices to prices in WOO" );
		//$this->tell( WOO_PRODUCT_STALEDATE_SPECIALS, __METHOD__ );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function update_specials()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql_update4 = "update " . $this->table_details['tablename'] . " woo,  " . TB_PREF . "specials s
			set
				woo.sale_price = s.sale_price,
				woo.date_on_sale_from = s.start,
				woo.date_on_sale_to = s.end
			where woo.stock_id = s.stock_id";
		$res = db_query( $sql_update4, "Couldnt update Sales and Specials in  WOO" );
		$this->tell( WOO_PRODUCT_SPECIALS_UPDATE, __METHOD__ );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function update_tax_data()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
				//$sql_update5 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "taxes t
		//	set
		//		woo.tax_status = t.tax_status,
		//		woo.tax_class = t.tax_class
		//	where woo.stock_id = t.stock_id";
		$sql_update5 = "update " . $this->table_details['tablename'] . " woo
			set
				woo.tax_status = 'taxable',
				woo.tax_class = 'GST'";
		$res = db_query( $sql_update5, "Couldnt update TAX data in  WOO" );
		$this->tell( WOO_PRODUCT_TAXDATA_UPDATE, __METHOD__ );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function update_shipping_dimensions()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		//need to do a check that the ShipDim module is installed
		$sql_update6 = "update " . $this->table_details['tablename'] . " woo,  " . TB_PREF . "shipdim s
			set
				woo.shipping_class = s.shipping_class,
				woo.length = s.length,
				woo.width = s.width,
				woo.height = s.height,
				woo.weight = s.weight
			where woo.stock_id = s.stock_id";
		$res = db_query( $sql_update6, "Couldnt update Shipping Dimensional data in  WOO" );
		$this->tell( WOO_PRODUCT_SHIPDIM_UPDATE, __METHOD__ );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function update_crosssells()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql_update7 = "update " . $this->table_details['tablename'] . " woo,  " . TB_PREF . "related s
			set
				woo.upsells_ids = s.upsells_ids,
				woo.crosssells_ids = s.crosssells_ids,
			where woo.stock_id = s.stock_id";
		$res = db_query( $sql_update7, "Couldnt update upsell and cross sell data in  WOO" );
		$this->tell( WOO_PRODUCT_CROSSSELL_UPDATE, __METHOD__ );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function update_category_data()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql_update8 = "update " . $this->table_details['tablename'] . " woo,  " . TB_PREF . "stock_category s
			set
				woo.category = s.description
			where woo.category_id = s.category_id";
		$res = db_query( $sql_update8, "Couldnt update Category data in  WOO" );
		$this->tell( WOO_PRODUCT_CATEGORY_UPDATE, __METHOD__ );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function update_category_xref()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql3 = "update " . $this->table_details['tablename'] . " woo, " . TB_PREF . "woo_categories_xref xref set woo.woo_category_id = xref.woo_cat where xref.fa_cat = woo.category_id";
		$res = db_query( $sql3, "Couldnt update categories WOO" );
		//$this->tell( WOO_PRODUCT_CATEGORY_XREF, __METHOD__ );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/*********************************************************************//**
	 *To show which products did not make it into the end table.
	 *
	*	Causes:
	*		No Transactions (no inventory)
	*		Missing Price 1 (Retail)
	*
	*	Allow user to update the items based upon missing data...
	* TROUBLESHOOTING:
		 	select sm.stock_id, sm.category_id, sc.description as category, sm.description, sm.long_description, sm.units, p.price, mv.instock 
              		from 0_stock_master sm, 0_prices p, 0_stock_category sc, 0_qoh mv, 0_woo_categories_xref woo
			where sm.stock_id = p.stock_id and p.sales_type_id='1' and mv.stock_id = sm.stock_id and sm.category_id = sc.category_id
			and sm.stock_id = 'hd-hat-hornpipe-51';
			select * from 0_qoh where stock_id = 'hd-hat-hornpipe-51';
 			select * from 0_woo where stock_id = 'hd-hat-hornpipe-51';
			*
			*
			* This function should be split into a data portion (model of MVC)
			* and a gui portion in a separate class (view of MVC).
	************************************************************************/
	function missing_from_table_query()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$missing_sql = "select sm.stock_id, sm.description, c.description, sm.inactive, sm.editable 
				from " . TB_PREF . "stock_master sm, " . TB_PREF . "stock_category c
				where sm.category_id = c.category_id and sm.stock_id not in (select stock_id from " . TB_PREF . "woo)";
		 global $all_items;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $missing_sql;

	}
	/*******************************************************************************//**
	 * This function has been moved into ksf_generate_catalogue so we will call it from here
	 *
	 * This should be split into a VIEW class and a MODEL class.
	 * *********************************************************************************/
	function create_price_book()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( @include_once( $path_to_root . "/modules/ksf_generate_catalogue/class.ksf_generate_catalogue.php" ) )
		{
			include_once($path_to_root . "/modules/ksf_generate_catalogue/ksf_generate_catalogue.inc.php"); //ksf_generate_catalogue_prefs
			$cat = new ksf_generate_catalogue( KSF_GENERATE_CATALOGUE_PREFS );
			$cat->create_price_book();
			$cat->email_price_book();
		}
		else 
		{
			display_warning( "This function depends on module ksf_generate_catalogue!" );
			$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
			return FALSE;
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );

	}
	/*****************************************************************//**
	 * Return a count of stock_ids belonging to products that are new
	 *
	 * @returns int count
	 * ******************************************************************/
	function count_new_products()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$count = $this->count_filtered( "woo_id = ''" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $count;
	}
	/*****************************************************************//**
	 * Return an array of stock_ids belonging to simple products that are new
	 *
	 * @returns array stock_ids
	 * ******************************************************************/
	/*@array@*/function new_simple_product_ids( $max = 0 )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$this->filter_new_only = TRUE;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $this->simple_product_ids( $max );
	}
	/*****************************************************************//**
	 * Return an array of stock_ids belonging to simple products that are new
	 *
	 * @returns array stock_ids
	 * ******************************************************************/
	/*@array@*/function all_simple_product_ids()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$this->filter_new_only = FALSE;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $this->simple_product_ids();
	}
	/*****************************************************************//**
	 * Return an array of stock_ids belonging to simple products
	 *
	 * @returns array stock_ids
	 * ******************************************************************/
	/*@array@*/function simple_product_ids( $max = 0 )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$res = $this->select_simple_products( $max );
		$resarray = array();

		while( $prod_data = db_fetch_assoc( $res ) )
		{
			$resarray[] = $prod_data['stock_id'];
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $resarray;
	}
	/****************************************************************//**
	 * Runs an MySQL query returning the mysql_res of stock_ids
	 *
	 * @returns mysql_res
	 * ******************************************************************/
	/*@mysql_res@*/function select_simple_products_for_export()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->select_simple_products();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $response;
	}
	/****************************************************************//**
	 * Runs an MySQL query returning the mysql_res of stock_ids
	 *
	 * Should be split into MODEL and VIEW classes
	 *   Could call VIEW function through db_query
	* @params int max number of rows to return for testing to limit test run time.  Default no limit
	 * @returns mysql_res
	 * ******************************************************************/
	/*@mysql_res@*/function select_simple_products( $max = 0 )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( ! defined( $this->table_details['tablename'] ) )
			$this->define_table();
		$prod_sql = 	"select stock_id from " . $this->table_details['tablename'];
		if( $this->filter_new_only )
		{
			$prod_sql .= " WHERE woo_id = ''";	//Otherwise need to do an UPDATE not CREATE
			$prod_sql .= " or woo_id = '-1'";	//Otherwise need to do an UPDATE not CREATE
		}
		//This will ensure we send only items that haven't already been inserted.
		$prod_sql .= " AND stock_id not in (SELECT sm.stock_id FROM " . TB_PREF . "stock_master sm 
			INNER JOIN (SELECT stock_id FROM " . TB_PREF . "woo_prod_variable_master GROUP BY stock_id) vm
			ON sm.stock_id LIKE  concat( vm.stock_id, '%') )";
		if( $max > 0 )
		{
			$prod_sql .= " LIMIT " . $max;
		}
		else if( $this->debug == 1 )
		{
			$prod_sql .= " LIMIT 10";
			//$prod_sql .= "ORDER BY RAND() LIMIT 10";
		}
		else if( $this->debug >= 2)
		{
			$prod_sql .= "ORDER BY RAND() LIMIT 1";
		}
		//$prod_sql .= " ORDER BY RAND() LIMIT 5";
		
		$res = db_query( $prod_sql, __LINE__ . "Couldn't select product(s) for export" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $res;
	}
	/****************************************************************//**
	 * Runs an MySQL query returning the mysql_res of stock_ids
	 *
	 * Should be split into MODEL and VIEW classes
	 *   Could call VIEW function through db_query
	 * @returns mysql_res
	 * ******************************************************************/
	/*@mysql_res@*/function select_simple_products_for_update()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$prod_sql = 	"select stock_id 
				from " . $this->table_details['tablename'];
		$prod_sql .= " WHERE ";
		//if( $this->force_update != TRUE )
			$prod_sql .= " updated_ts > woo_last_update AND";	//need to do an UPDATE because we changed something that hasn't been sent
		$prod_sql .= " stock_id not in (SELECT sm.stock_id FROM " . TB_PREF . "stock_master sm 
			INNER JOIN (SELECT stock_id FROM " . TB_PREF . "woo_prod_variable_master GROUP BY stock_id) vm
			ON sm.stock_id LIKE  concat( vm.stock_id, '%') ) limit 10";
		$res = db_query( $prod_sql, __LINE__ . "Couldn't select product(s) for export" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $res;
	}
	function delete_by_sku( $sku )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$sql = "delete FROM `" . $this->table_details['tablename'] . "` where stock_id = '" . $sku . "'";
		$res = db_query( $sql, "Couldn't delete sku" . $sku );
		$this->notify( "Deleted sku " . $sku, "NOTIFY" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
  	/***************************************************//**
        * modified from inventory/manage/items.php
	*
	* 	As this is a "view" function shouldn't be
	*	in here in the first place.  Making private
	*	so it can't be called.
        *********************************************************/
        private function display_list_table()
        {
		$this->select_all_sql();

/*
	function display_edit_form( $form_def, $selected_id = -1, $return_to )
        function combo_list( $sql, $order_by_field, $name, $selected_id=null, $none_option=false, $submit_on_change=false)
		 return combo_input($name, $selected_id, $sql, $order_by_field,  'name',
                array(
                        'order' => $order_by_field,
                        'spec_option' => $none_option,
                        'spec_id' => ALL_NUMERIC,
                        'select_submit'=> $submit_on_change,
                        'async' => false,
                ) );

        function combo_list_cells( $sql, $order_by_field, $label, $name, $selected_id = null, $none_option=false, $submit_on_change=false )
        function combo_list_row( $sql, $order_by_field, $label, $name, $selected_id = null, $none_option=false, $submit_on_change=false )
*/

		$label = _("Select an item:");
		$name = 'stock_id';
		$selected_id = $this->stock_id;
		$all_option=false;	//_('New item')
        	$submit_on_change=true; 
		$all=check_value('show_inactive');
		$editkey = false;


		$valuefield = 'stock_id';
		$namefield = 'description';	//order by

                start_table(TABLESTYLE_NOBORDER);
                start_row();
	        combo_input($name, $selected_id, $this->sql, $valuefield, $namefield,
		        array_merge(
		          array(
		                'format' => '_format_stock_items',
		                'spec_option' => $all_option===true ?  _("All Items") : $all_option,
		                'spec_id' => $all_items,
		                'search_box' => true,
		                'search' => array("stock_id", "description","category"),
		                'search_submit' => get_company_pref('no_item_list')!=0,
		                'size'=>10,
		                'select_submit'=> $submit_on_change,
		                'category' => 2,
		                'order' => array('description','stock_id')
		          ), $opts) );
                $new_item = get_post('stock_id')=='';
                check_cells(_("Show inactive:"), 'show_inactive', null, true);
                end_row();
                end_table();
                if (get_post('_show_inactive_update')) {
                        $Ajax->activate('stock_id');
                        set_focus('stock_id');
                }
        }
	function select_all()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$m_result = $this->select_table();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function select_all_sql()
	{
		$this->clear_sql_vars();
                $this->select_array = array( 
			'stock_id', 'description', 'updated_ts', 'woo_last_update','woo_id', 'category_id', 'category', 'woo_category_id', 'long_description', 'units', 'price', 'instock', 'saleprice', 'date_on_sale_from', 'date_on_sale_to', 'external_url', 'tax_status', 'tax_class', 'weight', 'length', 'width', 'height', 'shipping_class', 'upsell_ids', 'crosssell_ids', 'parent_id', 'attributes', 'default_attributes', 'variations' );

 		$this->from_array = array( $this->table_details['tablename'] );
/*
                $this->where_array = array();
                $this->groupby_array = array();
                $this->orderby_array = array();
                $this->having_array = array();
*/
                $this->buildSelectQuery();	//sets $this->sql
	}
	function select_woo_id_stock_id( $stock_id = null )
	{
		$this->clear_sql_vars();
                $this->select_array = array( 'woo_id', 'stock_id', 'category', 'description' );
 		$this->from_array = array( $this->table_details['tablename'] );
		if( null !== $stock_id )
		{
			$this->where_array = array( 'stock_id' => $stock_id );
		}
                $this->buildSelectQuery();	//sets $this->sql
	}

}

?>
