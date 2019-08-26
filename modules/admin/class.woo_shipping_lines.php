<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( 'class.woo_interface.php' );

class woo_shipping_lines extends woo_interface {

	var $order_id;
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
	function __construct($serverURL, $key, $secret, $options, $client)
	{
		parent::__construct($serverURL, $key, $secret, $options, $client);
		if( isset( $client->id ) )
			$this->order_id = $client->id;
		
		return;
	}
	function define_table()
	{
		$this->fields_array[] = array('name' => 'shipping_lines_id', 'type' => 'int(11)', 'auto_increment' => 'yes');
		$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->fields_array[] = array('name' => 'order_id', 	'type' => 'int(11)', 	'comment' => 'Order ID this is linked to. Genned by module', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'id',	 	'type' => 'int(11)', 		'comment' => 'Index.' );
		$this->fields_array[] = array('name' => 'method_id', 	'type' => 'varchar(64)', 	'comment' => 'Shipping Method ID' );
		$this->fields_array[] = array('name' => 'method_title',	'type' => 'varchar(64)', 	'comment' => 'Shipping Method' );
		$this->fields_array[] = array('name' => 'total', 	'type' => 'varchar(64)', 	'comment' => 'Total' );
		$this->fields_array[] = array('name' => 'total_tax',	 	'type' => 'varchar(64)', 	'comment' => ' 	Shipping tax total. ', 'readwrite' => 'write');
		$this->fields_array[] = array('name' => 'taxes',	 	'type' => 'varchar(64)', 	'comment' => 'taxes. ', 'readwrite' => 'write');
		$this->table_details['tablename'] = $this->company_prefix . "woo_shipping_lines";
		$this->table_details['primarykey'] = "shipping_lines_id";

		$this->table_details['primarykey'] = "shipping_lines_id";
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "id";
		$this->table_details['index'][0]['keyname'] = "id";
	}
}

?>
