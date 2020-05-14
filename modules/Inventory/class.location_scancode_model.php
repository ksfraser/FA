<?php

/**************************************************************
 *
 *	This table stores the last counted date for inventory taking.
 *
 **************************************************************/
require_once( '../ksf_modules_common/class.table_interface.php' );

class location_scancode_model extends table_interface
{
	var $stock_id;
	var $location;
	var $inventory_date;
	function __construct( $caller )
	{
		parent::__construct( $caller );
	}
        function install()
        {
        }
	function define_table()
        {
                //$this->fields_array[] = array('name' => 'billing_address_id', 'type' => 'int(11)', 'auto_increment' => 'yup');
                $this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
                $this->fields_array[] = array('name' => 'scancode', 'type' => 'varchar(100)' );
		$this->fields_array[] = array('name' => 'location', 'type' => 'varchar(32)' );

                $this->table_details['tablename'] = $this->company_prefix . "location_scancode";
                $this->table_details['primarykey'] = "scancode";
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
