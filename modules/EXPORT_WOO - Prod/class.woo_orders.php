<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( 'class.woo_interface.php' );

class woo_orders extends woo_interface {
		var $id;	//	'type' => 'int(11)' 	Unique identifier for the resource.  read-only
		var $parent_id;	//	'type' => 'int(11)' 	Parent order ID.
		var $status;	//	string 	Order status. Default is pending. Options (plugins may include new status): pending, processing, on-hold, completed, cancelled, refunded and failed.
		var $order_key;	//	string 	Order key.  read-only
		var $number;	//	string 	Order number.  read-only
		var $currency;	//	string 	Currency the order was created with, in ISO format, e.g USD. Default is the current store currency.
		var $version;	//	string 	Version of WooCommerce when the order was made.  read-only
		var $prices_include_tax;	//	boolean 	Shows if the prices included tax during checkout.  read-only
		var $date_created;	//	date-time 	The date the order was created, in the site’s timezone.  read-only
		var $date_modified;	//	date-time 	The date the order was last modified, in the site’s timezone.  read-only
		var $customer_id;	//	'type' => 'int(11)' 	User ID who owns the order. Use 0 for guests. Default is 0.
		var $discount_total;	//	string 	Total discount amount for the order.  read-only
		var $discount_tax;	//	string 	Total discount tax amount for the order.  read-only
		var $shipping_total;	//	string 	Total shipping amount for the order.  read-only
		var $shipping_tax;	//	string 	Total shipping tax amount for the order.  read-only
		var $cart_tax;	//	string 	Sum of line item taxes only.  read-only
		var $total;	//	string 	Grand total.  read-only
		var $total_tax;	//	string 	Sum of all taxes.  read-only
		var $billing_address;	//	array 	Billing address. See Customer Billing Address properties.
		var $shipping_address;	//	array 	Shipping address. See Customer Shipping Address properties.
		var $payment_method;	//	string 	Payment method ID.
		var $payment_method_title;	//	string 	Payment method title.
		var $set_paid;	//	boolean 	Define if the order is paid. It will set the status to processing and reduce stock items. Default is false.  write-only
		var $transaction_id;	//	string 	Unique transaction ID. In write-mode only is available if set_paid is true.
		var $customer_ip_address;	//	string 	Customer’s IP address.  read-only
		var $customer_user_agent;	//	string 	User agent of the customer.  read-only
		var $created_via;	//	string 	Shows where the order was created.  read-only
		var $customer_note;	//	string 	Note left by customer during checkout.
		var $date_completed;	//	date-time 	The date the order was completed, in the site’s timezone.  read-only
		var $date_paid;	//	date-time 	The date the order has been paid, in the site’s timezone.  read-only
		var $cart_hash;	//	string 	MD5 hash of cart items to ensure orders are not modified.  read-only
		var $line_items;	//	array 	Line items data. See Line Items properties.
		var $tax_lines;		//	array 	Tax lines data. See Tax Lines properties.  read-only
		var $shipping_lines;	//	array 	Shipping lines data. See Shipping Lines properties.
		var $fee_lines;		//	array 	Fee lines data. See Fee Lines Properties.
		var $coupon_lines;	//	array 	Coupons line data. See Coupon Lines properties.
		var $refunds;		//	array 	List of refunds. See Refunds Lines properties.  read-only
	
	function __construct($serverURL, $key, $secret, $options, $client)
	{
		parent::__construct($serverURL, $key, $secret, $options, $client);
		return;
	}
	function define_table()
	{
		$this->fields_array[] = array('name' => 'orders_id', 'type' => 'int(11)', 'auto_increment' => 'auto_increment');
		$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->fields_array[] = array('name' => 'id', 'type' => 'int(11)', 'comment' => 'WOOs id');
		$this->fields_array[] = array('name' => 'parent_id', 	'type' => 'int(11)', 'comment' => 'Parent order ID.' );
		$this->fields_array[] = array('name' => 'status', 'type' => 'varchar(64)', 'comment' => 'Order status. Default is pending. Options: pending, processing, on-hold, completed, cancelled, refunded and failed.' );
		$this->fields_array[] = array('name' => 'order_key', 		'type' => 'varchar(64)', 'comment' => 'Order key.  ' );
		$this->fields_array[] = array('name' => 'number', 		'type' => 'varchar(64)', 'comment' => 'Order number.  ' );
		$this->fields_array[] = array('name' => 'currency', 		'type' => 'varchar(64)', 'comment' =>'Currency the order was created with, in ISO format, e.g USD. Default is the current store currency.' );
		$this->fields_array[] = array('name' => 'version', 		'type' => 'varchar(64)', 'comment' => 'Version of WooCommerce when the order was made.  ' );
		$this->fields_array[] = array('name' => 'prices_include_tax', 	'type' => 'int(1)', 	'comment' => ' Shows if the prices included tax during checkout.  ' );
		$this->fields_array[] = array('name' => 'date_created', 	'type' => 'datetime', 	'comment' => 'The date the order was created, in the site’s timezone.  ' );
		$this->fields_array[] = array('name' => 'date_modified', 	'type' => 'datetime', 	'comment' => 'The date the order was last modified, in the site’s timezone.  ' );
		$this->fields_array[] = array('name' => 'customer_id', 		'type' => 'int(11)' , 	'comment' => 'User ID who owns the order. Use 0 for guests. Default is 0.' );
		$this->fields_array[] = array('name' => 'discount_total', 	'type' => 'varchar(64)', 'comment' => 'Total discount amount for the order.  ' );
		$this->fields_array[] = array('name' => 'discount_tax', 	'type' => 'varchar(64)', 'comment' => '	Total discount tax amount for the order.  ' );
		$this->fields_array[] = array('name' => 'shipping_total', 	'type' => 'varchar(64)', 'comment' => 'Total shipping amount for the order.  ' );
		$this->fields_array[] = array('name' => 'shipping_tax', 	'type' => 'varchar(64)', 'comment' => '	Total shipping tax amount for the order.  ' );
		$this->fields_array[] = array('name' => 'cart_tax', 		'type' => 'varchar(64)', 'comment' => 'Sum of line item taxes only.  ' );
		$this->fields_array[] = array('name' => 'total', 		'type' => 'varchar(64)', 'comment' => 'Grand total.  ' );
		$this->fields_array[] = array('name' => 'total_tax', 		'type' => 'varchar(64)', 'comment' => 'Sum of all taxes.  ' );
		$this->fields_array[] = array('name' => 'payment_method', 	'type' => 'varchar(64)', 'comment' => 'Payment method ID.' );
		$this->fields_array[] = array('name' => 'payment_method_title', 'type' => 'varchar(64)', 'comment' => '	Payment method title.' );
		$this->fields_array[] = array('name' => 'set_paid', 		'type' => 'int(1)', 	'comment' => ' Define if the order is paid. It will set the status to processing and reduce stock items. Default is false.  write-only' );
		$this->fields_array[] = array('name' => 'transaction_id', 	'type' => 'varchar(64)', 'comment' => 'Unique transaction ID. In write-mode only is available if set_paid is true.' );
		$this->fields_array[] = array('name' => 'customer_ip_address', 	'type' => 'varchar(64)', 'comment' => 'Customer’s IP address.  ' );
		$this->fields_array[] = array('name' => 'customer_user_agent', 	'type' => 'varchar(64)', 'comment' => 'User agent of the customer.  ' );
		$this->fields_array[] = array('name' => 'created_via', 		'type' => 'varchar(64)', 'comment' => 'Shows where the order was created.  ' );
		$this->fields_array[] = array('name' => 'customer_note', 	'type' => 'varchar(64)', 'comment' => 'Note left by customer during checkout.' );
		$this->fields_array[] = array('name' => 'date_completed', 	'type' => 'datetime', 	'comment' => '	The date the order was completed, in the site’s timezone.  ' );
		$this->fields_array[] = array('name' => 'date_paid', 		'type' => 'datetime', 	'comment' => ' The date the order has been paid, in the site’s timezone.  ' );
		$this->fields_array[] = array('name' => 'cart_hash', 		'type' => 'varchar(64)', 'comment' => 'MD5 hash of cart items to ensure orders are not modified.' );
		$this->fields_array[] = array('name' => 'billing_address', 		'type' => 'int(11)' , 	'comment' => 'FK	Billing address. See Customer Billing Address properties.', 	'foreign_obj' => 'woo_billing_address' );
		$this->fields_array[] = array('name' => 'shipping_address', 		'type' => 'int(11)', 	'comment' => 'FK	Shipping address. See Customer Shipping Address properties.', 	'foreign_obj' => 'woo_shipping_address' );
		$this->fields_array[] = array('name' => 'line_items', 		'type' => 'int(11)', 	'comment' => 'FK	Line items data. See Line Items properties.', 			'foreign_obj' => 'woo_line_items' );
		$this->fields_array[] = array('name' => 'tax_lines', 		'type' => 'int(11)', 	'comment' => 'FK	Tax lines data. See Tax Lines properties.  ', 			'foreign_obj' => 'woo_tax' );
		$this->fields_array[] = array('name' => 'shipping_lines', 	'type' => 'int(11)', 	'comment' => 'FK	Shipping lines data. See Shipping Lines properties.', 		'foreign_obj' => 'woo_shipping' );
		$this->fields_array[] = array('name' => 'fee_lines', 		'type' => 'int(11)', 	'comment' => 'FK	Fee lines data. See Fee Lines Properties.', 			'foreign_obj' => 'woo_fees' );
		$this->fields_array[] = array('name' => 'coupon_lines', 	'type' => 'int(11)', 	'comment' => 'FK	Coupons line data. See Coupon Lines properties.', 		'foreign_obj' => 'woo_coupons' );
		$this->fields_array[] = array('name' => 'refunds', 		'type' => 'int(11)', 	'comment' => 'FK	List of refunds. See Refunds Lines properties.  ', 		'foreign_obj' => 'woo_refunds' );
		$this->fields_array[] = array('name' => 'fa_order_num',		'type' => 'int(11)', 	'comment' => 'FA Order Number');
		$this->fields_array[] = array('name' => 'crm_persons_id',	'type' => 'int(11)', 	'comment' => 'FA Persons ID for customer');
		$this->fields_array[] = array('name' => 'branch_code',		'type' => 'int(11)', 	'comment' => 'FA cust_branch index for customer');
		$this->fields_array[] = array('name' => 'debtor_no',		'type' => 'int(11)', 	'comment' => 'FA cust_branch debtor_number for customer');
		$this->table_details['tablename'] = $this->company_prefix . "woo_orders";
		$this->table_details['primarykey'] = "orders_id";
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "id";
		$this->table_details['index'][0]['keyname'] = "id";
	}
	function get_order( $id )
	{
		try {
			$response = $this->wc_client->orders->get( $id );
			//print_r( $response );
			$this->extract_data_obj( $response->product );
			//var_dump( $this->id );
		} catch ( WC_API_Client_Exception $e ) {
			echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			//echo $e->getMessage() . PHP_EOL;
			$msg = $e->getMessage();
			//echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			$code = $e->getCode();
		        echo $code . PHP_EOL;
			if ( $e instanceof WC_API_Client_HTTP_Exception ) {
				switch( $code ) {
				default:
					echo "<br />" . __FILE__ . ":" . __LINE__ . ":Unhandled Error Code: " . $code . "<br />";
					break;
				}
				echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
				print_r( $e->get_request() );
				echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
				print_r( $e->get_response() );
			}
		}
		$this->ll_walk_update_fa();
	}
	function get_orders()
	{
		try {
			$response = $this->wc_client->orders->get();
			//print_r( $response );
			$extractcount = $this->extract_data_objects( $response->orders );	//This builds a linked list.  Will need to walk it.
			//var_dump( $this->id );
		} catch ( WC_API_Client_Exception $e ) {
			echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			//echo $e->getMessage() . PHP_EOL;
			$msg = $e->getMessage();
			//echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			$code = $e->getCode();
		        echo $code . PHP_EOL;
			if ( $e instanceof WC_API_Client_HTTP_Exception ) {
				switch( $code ) {
				default:
					echo "<br />" . __FILE__ . ":" . __LINE__ . ":Unhandled Error Code: " . $code . "<br />";
					break;
				}
			//	echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			//	print_r( $e->get_request() );
			//	echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			//	print_r( $e->get_response() );
			}
		}
		$this->ll_walk_insert_fa();
		return $extractcount;
	}
	function create_order()
	{
		$this->build_data_array();
		try {
			$this->wc_client->products->create_order( $this->data_array );
		} catch ( WC_API_Client_Exception $e ) {
			if ( $e instanceof WC_API_Client_HTTP_Exception ) {
				$code = $e->getCode();
				//echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
				//var_dump( $code );
				$msg = $e->getMessage();
				//echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
				//var_dump( $msg );
				switch( $code ){
				default:
						echo "<br />" . __FILE__ . ":" . __LINE__ . "Unhandled Code " . $code . " with message " . $msg . "<br />";
					break;
				}
			}
		}
	}
	function create_orders()
	{
		/*******************************************
		 * 
		 *	Take the list of orders out of FA
		 *	and send them to WOO
		 *
		 */
		$attr_sql = "SELECT * from " . $this->table_details['tablename'];
			//This will ensure we send only items that haven't already been inserted.
		$attr_sql .= " WHERE id = ''";
		//$attr_sql .= " ORDER BY sku_order";
		//$attr_sql .= " LIMIT 1";
		$res = db_query( $attr_sql, "Couldn't fetch orders to export" );
		while( $attr_data = db_fetch_assoc( $res ) )
		{
			$this->array2var( $attr_data );
			$this->create_order();
		}
	}
}

?>
