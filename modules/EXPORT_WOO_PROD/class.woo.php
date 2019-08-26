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
		$this->fields_array[] = array('name' => 'stock_id', 'type' => 'varchar(32)' );
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
}

?>
