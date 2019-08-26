<?php

require_once( '../lib/woocommerce-api.php' );

$options = array(
	'debug'           => true,
	'return_as_array' => false,
	'validate_url'    => false,
	'timeout'         => 30,
	'ssl_verify'      => false,
);

try {

	$client = new WC_API_Client( 'https://fhsws001/devel/fhs/wordpress', 'ck_a960f347ee0e58ce3ade0df32be5ca7f54a4ffaf', 'cs_ce8df992fa05238888f7fe73fbef612e36a2d564', $options );
	//$client = new WC_API_Client( 'http://your-store-url.com', 'ck_enter_your_consumer_key', 'cs_enter_your_consumer_secret', $options );

	// coupons
	//print_r( $client->coupons->get() );
	//print_r( $client->coupons->get( $coupon_id ) );
	//print_r( $client->coupons->get_by_code( 'coupon-code' ) );
	//print_r( $client->coupons->create( array( 'code' => 'test-coupon', 'type' => 'fixed_cart', 'amount' => 10 ) ) );
	//print_r( $client->coupons->update( $coupon_id, array( 'description' => 'new description' ) ) );
	//print_r( $client->coupons->delete( $coupon_id ) );
	//print_r( $client->coupons->get_count() );

	// custom
	//$client->custom->setup( 'webhooks', 'webhook' );
	//print_r( $client->custom->get( $params ) );

	// customers
	//print_r( $client->customers->get() );
	//print_r( $client->customers->get( $customer_id ) );
	//print_r( $client->customers->get_by_email( 'help@woothemes.com' ) );
	//print_r( $client->customers->create( array( 'email' => 'woothemes@mailinator.com' ) ) );
	//print_r( $client->customers->update( $customer_id, array( 'first_name' => 'John', 'last_name' => 'Galt' ) ) );
	//print_r( $client->customers->delete( $customer_id ) );
	//print_r( $client->customers->get_count( array( 'filter[limit]' => '-1' ) ) );
	//print_r( $client->customers->get_orders( $customer_id ) );
	//print_r( $client->customers->get_downloads( $customer_id ) );
	//$customer = $client->customers->get( $customer_id );
	//$customer->customer->last_name = 'New Last Name';
	//print_r( $client->customers->update( $customer_id, (array) $customer ) );

	// index
	//print_r( $client->index->get() );

	// orders
	print_r( $client->orders->get() );
	//print_r( $client->orders->get( $order_id ) );
	//print_r( $client->orders->update_status( $order_id, 'pending' ) );

	// order notes
	//print_r( $client->order_notes->get( $order_id ) );
	//print_r( $client->order_notes->create( $order_id, array( 'note' => 'Some order note' ) ) );
	//print_r( $client->order_notes->update( $order_id, $note_id, array( 'note' => 'An updated order note' ) ) );
	//print_r( $client->order_notes->delete( $order_id, $note_id ) );

	// order refunds
	//print_r( $client->order_refunds->get( $order_id ) );
	//print_r( $client->order_refunds->get( $order_id, $refund_id ) );
	//print_r( $client->order_refunds->create( $order_id, array( 'amount' => 1.00, 'reason' => 'cancellation' ) ) );
	//print_r( $client->order_refunds->update( $order_id, $refund_id, array( 'reason' => 'who knows' ) ) );
	//print_r( $client->order_refunds->delete( $order_id, $refund_id ) );

	// products
	// This filter is an EXACT MATCH
	//print_r( $client->products->get( null, array(  'filter[sku]' => 'U-B-ThistleS'  ) ) );
	//print_r( $client->products->get( null, array( 'page' => '1', 'filter[sku]' => 'DISC1'  ) ) );
	//print_r( $client->products->get( null, array( 'page' => '1', 'filter[category]' => 'Charges'  ) ) );
	//print_r( $client->products->get( $product_id ) );
	//print_r( $client->products->get( $variation_id ) );
	//print_r( $client->products->get_by_sku( 'a-product-sku' ) );
	//**************GET_BY_SKU DOESN'T WORK
	//print_r( $client->products->get_by_sku( 'M-BP-PC1' ) );
	//print_r( $client->products->create( array( 'title' => 'Test Product', 'type' => 'simple', 'regular_price' => '9.99', 'description' => 'test' ) ) );
	
		$image1 = array( 'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg', 'position' => 2 );
		$image2 = array( 'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg', 'position' => 1 );
		$image3 = array( 'src' => 'https://fhsws001.ksfraser.com/devel/fhs/frontaccounting/company/1/images/u-spats.jpg', 'position' => 0 );
		$images = array( $image1, $image2, /*$image3*/ );
		$data = array( 
			//'images' => $images,
			'title' => 'Spats',
	            	//'type' => simple
			//'status' => publish
		    /**GENERAL**/
	            'regular_price' => '60.02',
		    'sale_price' => '59.98',
		    'tax_status' => 'taxable',
		    'tax_class' => 'Standard',
		    /**Downloadable* on GENERAL**/
		    //files - name and URL
		    //limit - max number of times, blank for unlimited
		    //expiry (days)
		    //download type - Standard App/Software Music
		    //	            'downloads' => Array(),
	            //'download_limit' => 0,
	            //'download_expiry' => 0,
	            //'download_type' => '',

		    /**Gift Card**/
		    /**Virtual**/
		    	//Intangible so disables shipping
	            /**INVENTORY**/
		    	//sku
		    	/**The following are co-dependant!*/
	            'managing_stock' => TRUE,	
		    'stock_quantity' => 2,
		    	//allow backorders
		    'in_stock' => TRUE,	
		    'sold_individually' => TRUE,
		    /**SHIPPING**/
	            'weight' => '1.2',
	            'dimensions' => array (
	                    'length' => '4',
	                    'width' => '3',
	                    'height' => '2',
	                    'unit' => 'cm' ),
		    'shipping_class' => 'parcel',
		    /*Linked*/
		    /*
		    	'upsell_ids' => Array(),
			'cross_sell_ids' => Array(),
			'grouping' => array(),

	 		*/
		    /*Advanced Properties*/
	            'purchase_note' => 'good choice!',
		    'menu_order' => 2,
		    'reviews_allowed' => TRUE,
    	 		/**Further**/
		    'description' => '<p>White Spats - These Spats come with a Velcro strip for fastening. This gets away from having to do up each button individually. These spats are made of a bleached white material and easy to clean. Buttons are white. These spats are available in full sizes from 7 to 13. __edit__</p>',
	
		    'short_description' => '<p>Spats 1</p>',
		    /**CATEGORY**/
		    /**TAGS**/
		    /**Product Image**/
		    'images' => $images,
		    /**Image Gallery**/
		);


	//var_dump( $data );
	//print_r( $client->products->update( 6035,  $data  ) );
	/*
	print_r( $client->products->update( 724, array( 'title' => 'Yo  another test product', 'regular_price' => '11' ) ) );
	print_r( $client->products->get( 724 ) );
	print_r( $client->products->get( 335 ) );
	 */
	//print_r( $client->products->delete( $product_id, true ) );
	//print_r( $client->products->get_count() );
	//print_r( $client->products->get_count( array( 'type' => 'simple' ) ) );
	//print_r( $client->products->get_categories() );
	//print_r( $client->products->get_categories( "11" ) );

	// reports
	//print_r( $client->reports->get() );
	//print_r( $client->reports->get_sales( array( 'filter[date_min]' => '2014-07-01' ) ) );
	//print_r( $client->reports->get_top_sellers( array( 'filter[date_min]' => '2014-07-01' ) ) );

	// webhooks
	//print_r( $client->webhooks->get() );
	//print_r( $client->webhooks->create( array( 'topic' => 'coupon.created', 'delivery_url' => 'http://requestb.in/' ) ) );
	//print_r( $client->webhooks->update( $webhook_id, array( 'secret' => 'some_secret' ) ) );
	//print_r( $client->webhooks->delete( $webhook_id ) );
	//print_r( $client->webhooks->get_count() );
	//print_r( $client->webhooks->get_deliveries( $webhook_id ) );
	//print_r( $client->webhooks->get_delivery( $webhook_id, $delivery_id );

	// trigger an error
	//print_r( $client->orders->get( 0 ) );

} catch ( WC_API_Client_Exception $e ) {

	//echo $e->getMessage() . PHP_EOL;
	//echo $e->getCode() . PHP_EOL;

	if ( $e instanceof WC_API_Client_HTTP_Exception ) {

		//print_r( $e->get_request() );
		//print_r( $e->get_response() );
	}
}
