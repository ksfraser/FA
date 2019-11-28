<?php

//require_once( 'class.generic_orders.php' ); 
//
require_once( '../ksf_modules_common/class.table_interface.php' );
require_once( '../ksf_modules_common/defines.inc.php' );


require_once( 'class.generic_fa_interface.php' ); 

class ksf_coupons_ui extends generic_fa_interface
{
	var $caller;	//!< which class called us.
	function __construct( $caller )
	{
		simple_page_mode(true);
		global $db;
		$this->set_var( 'caller', $caller );
		$this->set_var( 'db', $db );

		//$this->config_values[] = array( 'pref_name' => 'mailto', 'label' => 'Mail CSV to email address' );
		//$this->config_values[] = array( 'pref_name' => 'image_serverurl', 'label' => 'Server URL for images (http[s]://servername/FA_base)' );
		//$this->config_values[] = array( 'pref_name' => 'image_baseurl', 'label' => 'Base URL for images (/company/0/images)' );
		//$this->config_values[] = array( 'pref_name' => 'use_img_baseurl', 'label' => 'Use Base URL or remote (true/false)' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'create', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables', 'action' => 'init_tables_form', 'form' => 'init_tables_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables Completed', 'action' => 'init_tables_complete_form', 'form' => 'init_tables_complete_form', 'hidden' => TRUE );
	
		$this->tabs[] = array( 'title' => 'Coupons create', 'action' => 'create_coupons_form', 'form' => 'create_coupons_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Coupons created', 'action' => 'created_coupons_form', 'form' => 'created_coupons_form', 'hidden' => TRUE );
	}
	/****************************************************************************//**
	 *Inherited
	 *show_config_form()
	 *
	 * ******************************************************************************/
	function show_config_form()
	{
		parent::show_config_form();
	}
	/****************************************************************************//**
	 *in_table_display
	 *
	 * @param array display on the screen, within a table, 1 row as specified by the array
	 *
	 * ******************************************************************************/
	function in_table_display( $field_array )
	{
		//ASSUMPTION we've already checked the readwrite attribute
		//and this is a writeable fields
		if( strncmp( $field_array['type'], "varchar", 7 ) == 0 
			OR strncmp( $field_array['type'], "int", 3 ) == 0 
		  )
		{
			label_row( $name . "(VC)", $this->$name );
		}
		else
		if( strncmp( $field_array['type'], "timestamp", 7 ) == 0 
			OR strncmp( $field_array['type'], "datetime", 7 ) == 0 
		  )
		{
			label_row( $name . "(DT)", $this->$name );
		}
		else
		if( strncmp( $field_array['type'], "boolean", 7 ) == 0 

		  )
		{
			label_row( $name . "(bool)", $this->$name );
		}

	}
	/****************************************************************************//**
	 *create_coupons_form
	 *
	 * Display a form on screen for generating a coupon
	 *
	 * ******************************************************************************/
	function create_coupons_form()
	{
		start_form();

		start_table();
		table_section_title(_("Coupons entry form"));
	//	label_row(_("No Transaction History (no inventory movement):"), NULL);
		label_row("&nbsp;", NULL);
		table_section(1);
		foreach( $this->fields_array as $field )
		{
			$name = $field['name'];
			if( isset( $field['readwrite'] ) )
			{
				if( $field['readwrite'] == 'read' )
				{
					//READ ONLY
					label_row( $name . "(RO)", $this->$name );
				}
				else
				{
					$this->in_table_display( $field );
				}
			}
			else
			{
				$this->in_table_display( $field );
			}
		}
		end_table();
		end_form();
	}

	function init_tables_complete_form()
	{
		$createdcount = 0;
		//assumption create_table will return TRUE on success
		if( $this->caller->create_table() )
		{
			$createdcount++;
		}
     		display_notification("init tables complete form created " . $createdcount . " tables");
	}
	function created_coupons_form()
	{
     		display_notification("Coupon created?");
	}
//	function form_products_export()
//	{
//		//$this->call_table( 'pexed', "Export" );
//		$this->call_table( 'qoh', "QOH" );
//	}
	function call_table( $action, $msg )
	{
                start_form(true);
                 start_table(TABLESTYLE2, "width=40%");
                 table_section_title( $msg );
                 hidden('action', $action );
                 end_table(1);
                 submit_center( $action, $msg );
                 end_form();
	}
	function init_tables_form()
	{
            	display_notification("init tables form");
		$this->call_table( 'init_tables_complete_form', "Init Tables" );
	}
}
/**********************************************************************//**
 * class origin_ui
 *
 * Common processes for UI classes
 *
 * *************************************************************************/
class origin_ui
{
	/**********************************************************************************//**
	 *
	 * 
	 * @param object client object needing values set
	 * **************************************************************************************/
	function __construct( $client )
	{
		$this->client = $client;
	}
	/*********************************************************************************//**
	 *fields_array2var
	 *	Take the data out of POST variables and put them into
	 *	the variables defined as table columns (fields_array)
	 *
	 * 
	 *	@returns int count of fields set
	 *
	 * ***********************************************************************************/
	/*@int@*/function fields_array2var()
	{
		$count = 0;
		$this->client->reset_values();
		foreach( $this->client->fields_array as $row )
		{
			$var = $row['name'];
			if( isset( $_POST[$var] ) )
			{
				$this->client->$var = $_POST[$var];
				$count++;
			}
		}
		return $count;
	}
}
/**********************************************************************//**
 * class ksf_coupons_x_products_ui
 *
 * Screens for which products are ALLOWED for a given coupon
 *
 * *************************************************************************/
class ksf_coupons_x_products_ui extends origin_ui
{
	function __construct( $client )
	{
		parent::__construct( $client );
	}
	function display_codes_products_screen()
	{
	}
	function add_code_product_form()
	{
	}
}
/**********************************************************************//**
 * class ksf_coupons_x_products_id
 *
 * Which products are ALLOWED for a given coupon
 *
 * *************************************************************************/
class ksf_coupons_x_products_id extends table_interface
{
	var $code;		//!< string coupon code
	var $product_id;	//!< string product ID the coupon can be used on.
	var $date_created;	//	date-time 	The date the product was added to the coupon, in the site’s timezone.  	
	var $date_modified;	//	date-time 	The date the coupon-product was last modified, in the site’s timezone.  	
	var $date_removed;	//	date-time 	The date the product was removed from the coupon
	var $iam;
	function __construct( $caller )
	{
		$this->iam = get_class( $this );
		$this->table_details = array();
		$this->fields_array = array();
		$this->caller = $caller;
		if( isset( $this->caller->debug ) )
			$this->debug = $this->caller->debug;
		else
			$this->debug = 0;
		/*
		$this->build_write_properties_array();
		$this->build_properties_array();
		$this->fields_array2entry();
		$this->build_interestedin();
		 */
	}
	/**********************************************************************//**
	 * coupon_valid_product
	 *
	 * Which products are valid for a given coupon
	 * assumption is that if no rows (no products specified)
	 * then ALL products are valid
	 *
	 * @param code string coupon code
	 * @param stock_id string product stock ID
	 * @returns bool TRUE or False
	 * *************************************************************************/
	/*@bool@*/function coupon_valid_product( $code, $stock_id )
	{
		//Check the table to see if the stock_id and coupon code are entries, 
		//and that the removed date is not set.
		//Assumption is that the coupon itself is checking that it is within appropriate date ranges
		//if NO products are against the coupon code, assumption is coupon is valid for ALL products
		$sql = "SELECT count(*) as rows from " . $this->iam . " where code = " . $code . " and product_id = " . $stock_id;
		$ret = db_query( $sql, "Can't search for coupon on " . $code . ":" . $stock_id );
		$num = db_fetch( $ret );
		if( $num['rows'] < 1 )
		{
			return TRUE;
		}
		$sql = "SELECT * from " . $this->iam . " where code = " . $code . " and product_id = " . $stock_id;
		$ret = db_query( $sql, "Can't search for coupon on " . $code . ":" . $stock_id );
		$assoc = db_fetch_assoc( $ret );
		if( strlen( $assoc['date_removed'] > 8 ) )
		{
			//is the date past?
			if( date_expired( $assoc['date_removed'] ) )
			{
				return FALSE;
			}
			return TRUE;
		}
		return FALSE;
	}
	//inherited function create_table();
	function define_table()
	{
		$tableindex = $this->iam . "_id";
		$this->fields_array[] = array('name' => $tableindex,	 	'type' => 'int(11)', 		'comment' => 'Index.', 'readwrite' => 'read', 'auto_increment' => 'anything');
		$this->fields_array[] = array('name' => 'updated_ts', 		'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read');

		$this->fields_array[] = array('name' => 'code', 		'type' => 'varchar(64)', 	'comment' => ' 	Tax rate code.', 'readwrite' => 'read');
		$this->fields_array[] = array('name' => 'date_created', 	'type' => 'datetime', 		'null' => 'NOT NULL',  'readwrite' => 'read'); 	
		$this->fields_array[] = array('name' => 'date_modified', 	'type' => 'datetime', 		'null' => 'NOT NULL', 'readwrite' => 'read'); 	
		$this->fields_array[] = array('name' => 'date_removed', 	'type' => 'datetime', 		'null' => 'NOT NULL', 'readwrite' => 'read'); 	
		$this->fields_array[] = array('name' => 'product_id', 		'type' => 'varchar(64)', 	'comment' => 'stock_id', 'readwrite' => 'read');

		$this->table_details['tablename'] = $this->company_prefix . $this->iam;
		$this->table_details['primarykey'] = $tableindex;
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "code,product_id";
		$this->table_details['index'][0]['keyname'] = "id-code";
	}
}



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
	var $include_header;
	var $maxrowsallowed;
	var $lastoid;
	var $mailto;
	var $db;
	var $ck;
	var $cs;
	var $server;
	var $rest_path;
	var $environment;
	var $maxpics;
	var $debug;
	function __construct()
	{
	}
	function define_table()
	{
		$this->fields_array[] = array('name' => 'ksf_coupons_id', 	'type' => 'int(11)', 		'comment' => 'Index.', 'readwrite' => 'read', 'auto_increment' => 'anything');
		$this->fields_array[] = array('name' => 'updated_ts', 		'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read');

		$this->fields_array[] = array('name' => 'id', 			'type' => 'int(11)', 		'comment' => ' 	Item ID', 'readwrite' => 'read');
		$this->fields_array[] = array('name' => 'code', 		'type' => 'varchar(64)', 	'comment' => ' 	Tax rate code.', 'readwrite' => 'read');

		$this->fields_array[] = array('name' => 'mandatory', 		'type' => 'boolean', 	'comment' => 'Mandatory', 'readwrite' => 'readwrite'); 	
		$this->fields_array[] = array('name' => 'date_created', 	'type' => 'datetime', 		'null' => 'NOT NULL',  'readwrite' => 'read'); 	
		$this->fields_array[] = array('name' => 'date_modified', 	'type' => 'datetime', 		'null' => 'NOT NULL', 'readwrite' => 'read'); 	
		$this->fields_array[] = array('name' => 'description', 		'type' => 'varchar(64)', 	'comment' => 'Coupon Description.', 'readwrite' => 'readwrite'); 	
		$this->fields_array[] = array('name' => 'discount_type', 	'type' => 'varchar(64)', 	'comment' => 'Discount Type', 'readwrite' => 'readwrite'); 
							//Options: fixed_cart, percent, fixed_product and percent_product. Default: fixed_cart.
		$this->fields_array[] = array('name' => 'amount', 		'type' => 'varchar(64)', 	'comment' => 'Amount', 'readwrite' => 'readwrite'); 
		$this->fields_array[] = array('name' => 'expiry_date', 		'type' => 'datetime', 	'comment' => 'Expiry date', 'readwrite' => 'readwrite'); 
		$this->fields_array[] = array('name' => 'usage_count', 		'type' => 'int(11)', 		'comment' => 'usage count', 'readwrite' => 'read'); 
		$this->fields_array[] = array('name' => 'individual_use', 	'type' => 'boolean', 	'comment' => 'Individual Use.  Boolean', 'readwrite' => 'readwrite'); 
		//$this->fields_array[] = array('name' => 'product_ids', 		array 	List of product ID’s the coupon can be used on.
		//$this->fields_array[] = array('name' => 'exclude_product_ids', 		array 	List of product ID’s the coupon cannot be used on.
		$this->fields_array[] = array('name' => 'usage_limit', 		'type' => 'int(11)', 	'comment' => ' How many times can it be used', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'usage_limit_per_user', 'type' => 'int(11)', 	'comment' => 'How many times a customer can use it', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'limit_usage_to_x_items', 	'type' => 'int(11)', 		'comment' => 'max items in cart to use', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'free_shipping', 	 	'type' => 'boolean', 		'comment' => 'Boolean.  Free shipping?', 'readwrite' => 'readwrite');
		//$this->fields_array[] = array('name' => 'product_categories', 	 	array 	List of category ID’s the coupon applies to.
		//$this->fields_array[] = array('name' => 'excluded_product_categories', 	 	array 	List of category ID’s the coupon does not apply to.
		$this->fields_array[] = array('name' => 'exclude_sale_items', 	 	'type' => 'varchar(5)', 		'comment' => 'Boolean.  Exclude sale items?', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'minimum_amount', 	 	'type' => 'varchar(64)', 	'comment' => 'Minimum order amount before coupon applies', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'maximum_amount', 	 	'type' => 'varchar(64)', 	'comment' => 'Max order amount for coupon applies', 'readwrite' => 'readwrite'); 
		//$this->fields_array[] = array('name' => 'email_restrictions', 	 	array 	List of email addresses that can use this coupon.
		//$this->fields_array[] = array('name' => 'used_by', 	 		array 	List of user IDs who have used the coupon. 

		$this->table_details['tablename'] = $this->company_prefix . "ksf_coupons";
		$this->table_details['primarykey'] = "ksf_coupons_id";
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "id,code";
		$this->table_details['index'][0]['keyname'] = "id-code";
	}
}

?>
