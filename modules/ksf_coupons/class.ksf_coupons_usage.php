<?php

//require_once( 'class.generic_orders.php' ); 
//
require_once( '../ksf_modules_common/class.table_interface.php' );
require_once( '../ksf_modules_common/defines.inc.php' );


require_once( 'class.generic_fa_interface.php' ); 

//Usage data for further tracking and analytics

//class ksf_coupons
//class ksf_coupons extends generic_orders
class ksf_coupons_usage extends generic_fa_interface
{
	var $id;	//	integer 	Unique identifier for the object.  	read-only
	var $code;	//	string 	Coupon code.
	var $date_created;	//	date-time 	The date the coupon was created, in the site’s timezone.  	read-only
	var $date_modified;	//	date-time 	The date the coupon was last modified, in the site’s timezone.  	read-only
	var $amount;	//	string 	The amount of discount.
	var $usage_count;	//	integer 	Number of times the coupon has been used already.	read-only
	var $used_by;	// 	array 	List of user IDs who have used the coupon.	
	//*****************************************************************************************************************
	var $lastoid;
	var $environment;
	var $debug;
	var $table_interface;
	function __construct( $pref_tablename )
	{
		parent::__construct( null, null, null, null, $pref_tablename );
		/*
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		 */
		$this->tabs[] = array( 'title' => 'Coupons Updated', 'action' => 'form_Coupons_completed', 'form' => 'form_Coupons_completed', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Update Coupons', 'action' => 'form_Coupons', 'form' => 'form_Coupons', 'hidden' => FALSE );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
		$this->table_interface = new table_interface();
		$this->define_table();

		return;
	}
	function action_show_form()
	{
		$this->install();
		parent::action_show_form();
	}
	function install()
	{
		$this->table_interface->create_table();
		parent::install();
	}
	function define_table()
	{
		$this->table_interface->fields_array[] = array('name' => 'ksf_coupons_id', 	'type' => 'int(11)', 		'comment' => 'Index.', 'readwrite' => 'read', 'auto_increment' => 'anything');
		$this->table_interface->fields_array[] = array('name' => 'updated_ts', 		'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read');

		$this->table_interface->fields_array[] = array('name' => 'id', 			'type' => 'int(11)', 		'comment' => ' 	Item ID', 'readwrite' => 'read');
		$this->table_interface->fields_array[] = array('name' => 'code', 		'type' => 'varchar(64)', 	'comment' => ' 	Tax rate code.', 'readwrite' => 'read');

		$this->table_interface->fields_array[] = array('name' => 'date_created', 	'type' => 'datetime', 		'null' => 'NOT NULL',  'readwrite' => 'read'); 	
		$this->table_interface->fields_array[] = array('name' => 'date_modified', 	'type' => 'datetime', 		'null' => 'NOT NULL', 'readwrite' => 'read'); 	
							//Options: fixed_cart, percent, fixed_product and percent_product. Default: fixed_cart.
		$this->table_interface->fields_array[] = array('name' => 'amount', 		'type' => 'varchar(64)', 	'comment' => 'Amount', 'readwrite' => 'readwrite'); 
		$this->table_interface->fields_array[] = array('name' => 'usage_count', 		'type' => 'int(11)', 		'comment' => 'usage count', 'readwrite' => 'read'); 
		$this->table_interface->fields_array[] = array('name' => 'used_by', 	 	'type' => 'int(11)', 		'comment' => 'used byt', 'readwrite' => 'read'); 		

		$this->table_interface->table_details['tablename'] = $this->company_prefix . "ksf_coupons";
		$this->table_interface->table_details['primarykey'] = "ksf_coupons_id";
		$this->table_interface->table_details['index'][0]['type'] = 'unique';
		$this->table_interface->table_details['index'][0]['columns'] = "id,code";
		$this->table_interface->table_details['index'][0]['keyname'] = "id-code";
	}
}

?>
