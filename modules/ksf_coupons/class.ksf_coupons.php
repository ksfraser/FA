<?php

//require_once( 'class.generic_orders.php' ); 
//
require_once( '../ksf_modules_common/class.table_interface.php' );
require_once( '../ksf_modules_common/defines.inc.php' );


require_once( 'class.generic_fa_interface.php' ); 


//class ksf_coupons
//class ksf_coupons extends generic_orders
class ksf_coupons extends generic_fa_interface
{
	var $id;	//	integer 	Unique identifier for the object.  	read-only
	var $code;	//	string 	Coupon code.
	var $date_created;	//	date-time 	The date the coupon was created, in the site’s timezone.  	read-only
	var $date_modified;	//	date-time 	The date the coupon was last modified, in the site’s timezone.  	read-only
	var $description;	//	string 	Coupon description.
	var $discount_type;	//	string 	Determines the type of discount that will be applied. Options: fixed_cart, percent, fixed_product and percent_product. Default: fixed_cart.
	var $amount;	//	string 	The amount of discount.
	var $expiry_date;	//	string 	UTC DateTime when the coupon expires.
	var $usage_count;	//	integer 	Number of times the coupon has been used already.	read-only
	var $individual_use;	//	boolean 	Whether coupon can only be used individually.
	var $product_ids;	//	array 	List of product ID’s the coupon can be used on.
	var $exclude_product_ids;	//	array 	List of product ID’s the coupon cannot be used on.
	var $usage_limit;	//	integer 	How many times the coupon can be used.
	var $usage_limit_per_user;	// 	integer 	How many times the coupon can be used per customer.
	var $limit_usage_to_x_items;	// 	integer 	Max number of items in the cart the coupon can be applied to.
	var $free_shipping;	// 	boolean 	Define if can be applied for free shipping.
	var $product_categories;	// 	array 	List of category ID’s the coupon applies to.
	var $excluded_product_categories;	// 	array 	List of category ID’s the coupon does not apply to.
	var $exclude_sale_items;	// 	boolean 	Define if should not apply when have sale items.
	var $minimum_amount;	// 	string 	Minimum order amount that needs to be in the cart before coupon applies.
	var $maximum_amount;	// 	string 	Maximum order amount allowed when using the coupon.
	var $email_restrictions;	// 	array 	List of email addresses that can use this coupon.
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

		$this->table_interface->fields_array[] = array('name' => 'mandatory', 		'type' => 'boolean', 	'comment' => 'Mandatory', 'readwrite' => 'readwrite'); 	
		$this->table_interface->fields_array[] = array('name' => 'date_created', 	'type' => 'datetime', 		'null' => 'NOT NULL',  'readwrite' => 'read'); 	
		$this->table_interface->fields_array[] = array('name' => 'date_modified', 	'type' => 'datetime', 		'null' => 'NOT NULL', 'readwrite' => 'read'); 	
		$this->table_interface->fields_array[] = array('name' => 'description', 		'type' => 'varchar(64)', 	'comment' => 'Coupon Description.', 'readwrite' => 'readwrite'); 	
		$this->table_interface->fields_array[] = array('name' => 'discount_type', 	'type' => 'varchar(64)', 	'comment' => 'Discount Type', 'readwrite' => 'readwrite'); 
							//Options: fixed_cart, percent, fixed_product and percent_product. Default: fixed_cart.
		$this->table_interface->fields_array[] = array('name' => 'amount', 		'type' => 'varchar(64)', 	'comment' => 'Amount', 'readwrite' => 'readwrite'); 
		$this->table_interface->fields_array[] = array('name' => 'expiry_date', 		'type' => 'datetime', 	'comment' => 'Expiry date', 'readwrite' => 'readwrite'); 
		$this->table_interface->fields_array[] = array('name' => 'usage_count', 		'type' => 'int(11)', 		'comment' => 'usage count', 'readwrite' => 'read'); 
		$this->table_interface->fields_array[] = array('name' => 'individual_use', 	'type' => 'boolean', 	'comment' => 'Individual Use.  Boolean', 'readwrite' => 'readwrite'); 
		//$this->table_interface->fields_array[] = array('name' => 'product_ids', 		array 	List of product ID’s the coupon can be used on.
		//$this->table_interface->fields_array[] = array('name' => 'exclude_product_ids', 		array 	List of product ID’s the coupon cannot be used on.
		$this->table_interface->fields_array[] = array('name' => 'usage_limit', 		'type' => 'int(11)', 	'comment' => ' How many times can it be used', 'readwrite' => 'readwrite');
		$this->table_interface->fields_array[] = array('name' => 'usage_limit_per_user', 'type' => 'int(11)', 	'comment' => 'How many times a customer can use it', 'readwrite' => 'readwrite');
		$this->table_interface->fields_array[] = array('name' => 'limit_usage_to_x_items', 	'type' => 'int(11)', 		'comment' => 'max items in cart to use', 'readwrite' => 'readwrite');
		$this->table_interface->fields_array[] = array('name' => 'free_shipping', 	 	'type' => 'boolean', 		'comment' => 'Boolean.  Free shipping?', 'readwrite' => 'readwrite');
		//$this->table_interface->fields_array[] = array('name' => 'product_categories', 	 	array 	List of category ID’s the coupon applies to.
		//$this->table_interface->fields_array[] = array('name' => 'excluded_product_categories', 	 	array 	List of category ID’s the coupon does not apply to.
		$this->table_interface->fields_array[] = array('name' => 'exclude_sale_items', 	 	'type' => 'varchar(5)', 		'comment' => 'Boolean.  Exclude sale items?', 'readwrite' => 'readwrite');
		$this->table_interface->fields_array[] = array('name' => 'minimum_amount', 	 	'type' => 'varchar(64)', 	'comment' => 'Minimum order amount before coupon applies', 'readwrite' => 'readwrite');
		$this->table_interface->fields_array[] = array('name' => 'maximum_amount', 	 	'type' => 'varchar(64)', 	'comment' => 'Max order amount for coupon applies', 'readwrite' => 'readwrite'); 
		//$this->table_interface->fields_array[] = array('name' => 'email_restrictions', 	 	array 	List of email addresses that can use this coupon.
		//$this->table_interface->fields_array[] = array('name' => 'used_by', 	 		array 	List of user IDs who have used the coupon. 

		$this->table_interface->table_details['tablename'] = $this->company_prefix . "ksf_coupons";
		$this->table_interface->table_details['primarykey'] = "ksf_coupons_id";
		$this->table_interface->table_details['index'][0]['type'] = 'unique';
		$this->table_interface->table_details['index'][0]['columns'] = "id,code";
		$this->table_interface->table_details['index'][0]['keyname'] = "id-code";
	}
}

?>
