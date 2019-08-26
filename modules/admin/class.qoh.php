<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( 'class.woo_interface.php' );

class qoh extends woo_interface {

	var $first_name; 	//	string 	First name.
	var $last_name; 	//	string 	Last name.
	var $company; 	//	string 	Company name.
	var $address_1; 	//	string 	Address line 1.
	var $address_2; 	//	string 	Address line 2.
	var $city; 	//	string 	City name.
	var $state; 	//	string 	ISO code or name of the state, province or district.
	var $postcode; 	//	string 	Postal code.
	var $country; 	//	string 	ISO code of the country.
	var $email; 	//	string 	Email address.
	var $phone; 	//	string 	Phone number.
	var $customer_id;
	var $order_id;
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
		//$this->fields_array[] = array('name' => 'billing_address_id', 'type' => 'int(11)', 'auto_increment' => 'yup');
		$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->fields_array[] = array('name' => 'stock_id', 'type' => 'varchar(32)' );
		$this->fields_array[] = array('name' => 'instock', 'type' => 'int(11)' );

		$this->table_details['tablename'] = $this->company_prefix . "qoh";
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
