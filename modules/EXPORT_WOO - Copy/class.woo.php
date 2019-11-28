<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( 'class.woo_interface.php' );

class woo extends woo_interface {
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

	function __construct($serverURL, $key, $secret, $options, $client)
	{
		parent::__construct($serverURL, $key, $secret, $options, $client);
		/*
		if( isset( $client->id ) )
		{
			$classtype=get_class( $client );
			echo "<br />" . __FILE__ . ":" . __LINE__ . " Class of type " . $classtype . "<br />";
			if( $classtype == 'woo_customer' )
				$this->customer_id = $client->id;
			else if( $classtype == 'woo_orders' )
				$this->order_id = $client->id;
		}
		 */
		return;
	}
	function define_table()
	{
		$sidl = 'varchar(' . STOCK_ID_LENGTH . ')';
		$this->fields_array[] = array('name' => 'stock_id', 'label' => 'SKU', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite' );
		$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->fields_array[] = array('name' => 'woo_last_update', 'type' => 'timestamp', 'null' => 'NOT NULL',);
		$this->fields_array[] = array('name' => 'woo_id', 'type' => 'varchar(32)' );
		$this->fields_array[] = array('name' => 'category_id', 'type' => 'int(11)' );
		$this->fields_array[] = array('name' => 'category', 'type' => 'varchar(64)' );
		$this->fields_array[] = array('name' => 'woo_category_id', 'type' => 'int(11)' );
		$this->fields_array[] = array('name' => 'description', 'type' => 'varchar(200)' );
		$this->fields_array[] = array('name' => 'long_description', 'type' => 'varchar(500)' );
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
	function insert_product()
	{
		$sql_create = "insert ignore into " . TB_PREF . "woo ( stock_id"
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
		$this->tell( WOO_PRODUCT_INSERT, __METHOD__ );
	}
	function update_product_details()
	{
		$sql_update = "update " . TB_PREF . "woo woo, " . TB_PREF . "stock_master sm
			set
				woo.category_id = sm.category_id
				, woo.description = sm.description
				, woo.long_description = sm.long_description
				, woo.units = sm.units
			where woo.stock_id = sm.stock_id";
		$res = db_query( $sql_update, "Couldnt update stock_master details in  WOO" );
		$this->tell( WOO_PRODUCT_UPDATE, __METHOD__ );
	}
	function update_prices()
	{
		$sql_update2 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "prices p
			set
				woo.price = p.price
			where woo.stock_id = p.stock_id
				and p.sales_type_id = '1'";
		$res = db_query( $sql_update2, "Couldnt update prices in  WOO" );
		$this->tell( WOO_PRODUCT_PRICE_UPDATE, __METHOD__ );
	}
	function update_qoh_count()
	{
		$sql_update3 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "qoh q
			set
				woo.instock = q.instock
			where woo.stock_id = q.stock_id";
		$res = db_query( $sql_update3, "Couldnt update Quantity On Hand in  WOO" );
		$this->tell( WOO_PRODUCT_QOH_UPDATE, __METHOD__ );
	}
	function update_specials()
	{
		$sql_update4 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "specials s
			set
				woo.sale_price = s.sale_price,
				woo.date_on_sale_from = s.start,
				woo.date_on_sale_to = s.end
			where woo.stock_id = s.stock_id";
		$res = db_query( $sql_update4, "Couldnt update Sales and Specials in  WOO" );
		$this->tell( WOO_PRODUCT_SPECIALS_UPDATE, __METHOD__ );
	}
	function update_tax_data()
	{
				//$sql_update5 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "taxes t
		//	set
		//		woo.tax_status = t.tax_status,
		//		woo.tax_class = t.tax_class
		//	where woo.stock_id = t.stock_id";
		$sql_update5 = "update " . TB_PREF . "woo woo
			set
				woo.tax_status = 'taxable',
				woo.tax_class = 'GST'";
		$res = db_query( $sql_update5, "Couldnt update TAX data in  WOO" );
		$this->tell( WOO_PRODUCT_TAXDATA_UPDATE, __METHOD__ );
	}
	function update_shipping_dimensions()
	{
		$sql_update6 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "shipdim s
			set
				woo.shipping_class = s.shipping_class,
				woo.length = s.length,
				woo.width = s.width,
				woo.height = s.height,
				woo.weight = s.weight
			where woo.stock_id = s.stock_id";
		$res = db_query( $sql_update6, "Couldnt update Shipping Dimensional data in  WOO" );
		$this->tell( WOO_PRODUCT_SHIPDIM_UPDATE, __METHOD__ );
	}
	function update_crosssells()
	{
		$sql_update7 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "related s
			set
				woo.upsells_ids = s.upsells_ids,
				woo.crosssells_ids = s.crosssells_ids,
			where woo.stock_id = s.stock_id";
		$res = db_query( $sql_update7, "Couldnt update upsell and cross sell data in  WOO" );
		$this->tell( WOO_PRODUCT_CROSSSELL_UPDATE, __METHOD__ );
	}
	function update_category_data()
	{
		$sql_update8 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "stock_category s
			set
				woo.category = s.description
			where woo.category_id = s.category_id";
		$res = db_query( $sql_update8, "Couldnt update Category data in  WOO" );
		$this->tell( WOO_PRODUCT_CATEGORY_UPDATE, __METHOD__ );
	}
}

?>
