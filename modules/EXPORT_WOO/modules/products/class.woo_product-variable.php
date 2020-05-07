<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array appropriately
 * */

/*******************************************************************************************
* TODO
*
*	Refactor the woo=pz_model_woo code to use just the pz
*	Maybe move the build/reset pz_model_woo into a function
*
********************************************************************************************/

/*******************************************************************************************//**
 * This module is for handling frontaccounting to WooCommerce products.
 *
 * Any activities storing product data in FA is done through the woo class. (May be
 * some fcns here that need to be migrated)
 *
 * Any activites connecting to an outside web service will be (eventually) done through the
 * woo_rest class (May be some fcns here that need to be migrated).
 *
 *	Note there is logging going on in every routine.
 * ***********************************************************************************************/

require_once( 'class.woo_product-base.php' );

class woo_product_variable extends woo_product_base {
	var $variation_id;	//!<integer V		ID returned for the variation.
	var $image;	//array 	List of images. See Images properties
	
	/***************************************************************************************//**
	 *
	 *
	 * ****************************************************************************************/
	function __construct( $serverURL, /*unused*/ $woo_rest_path,
				$key, $secret, /*unused*/$enviro = "devel", $client = null )
	{
		parent::__construct( $serverURL, $woo_rest_path, $key, $secret, $enviro = "devel", $client = null );
	
	//	$this->provides[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
         //       $this->provides[] = array( 'title' => 'Init Tables', 'action' => 'init_tables_form', 'form' => 'init_tables_form', 'hidden' => FALSE );
		return;
	}
	//function notify (inherited from woo_interface)
	function reset_endpoint()	//Required by _interface
	{
		if( !isset( $this->id ) )
			throw new Exception( "Master Product ID required for resetting endpoint", KSF_FIELD_NOT_SET );
		if( !isset( $this->variation_id ) )
			throw new Exception( "Variation ID required for resetting endpoint", KSF_FIELD_NOT_SET );
		$this->endpoint = "products/" . $this->id . "/variations/" . $this->variation_id;
	}
	/*********************************************************************************************//**
	 * Woo_Interface now builds the properties_array and write_properties_array from the defined table!
	 * 
	 * ***********************************************************************************************/
	function build_properties_array()
	{
		/*All common properties*/
		parent::build_properties_array();

		$this->properties_array[] = "variation_id";
		$this->properties_array[] = "image";
	}
	/*********************************************************************************************//**
	 * Woo_Interface now builds the properties_array and write_properties_array from the defined table!
	 * 
	 * ***********************************************************************************************/
	function build_write_properties_array()
	{
		/*Took the list of properties, and removed the RO ones*/
		parent::build_write_properties_array();
		$this->write_properties_array[] = "image"; 
	}
	function build_interestedin()
	{
		parent::build_interestedin();
		$this->interestedin['WOO_SEND_PRODUCTS']['function'] = "send_products";
	}
	function send_products( $obj, $msg )
	{
		$this->send_variable_products();
		$this->update_variable_products();
	}
	function update_variation_id( $id )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$woo = $this->model_woo();
                $woo->stock_id = $this->stock_id;
                $woo->variation_id = $id;
		$woo->update_variation_id( $id );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
	}

	/*******************************************************************************************//**
	 * Updates the internal woo table with data from WooCommerce that we ARENT the source of record 
	 *
	 *	requires that ID is set
	 *
	 * @param none
	 * @returns bool did we update WOO
	 * ******************************************************************************************/
	/*@bool@*/function update_wootable_woodata()
	{
		parent::update_wootable_woodata();
		$this->update_variation_id();
		return TRUE;
	}
	// function update_woo_id( $id ) (inherited from -base)
	
	/****************************************************************************//**
	 * This function should send a new product to WooCommerce, "creating" it.
	 *
	 * Assumption is that this object has all of the data set appropriately
	 * for us to convert the variables into an array, and pass it along.
	 *
	 * ******************************************************************************/
	/*@bool@*/function create_product_wc()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
	
		set_time_limit( 60 );
	
		try {
			if( ! isset( $this->variation_id ) )
				$response = $this->send2woo( "new" );
			else
				$response = $this->send2woo( "update" );
			if( $response )
			{
				$this->products_sent++;
				//Need to update the woo_id in _woo
				$woo = $this->model_woo();
                		$woo->stock_id = $this->stock_id;
                		$woo->woo_id = $this->id;
				$woo->update_woo_id( $this->id );
				$this->update_variation_id( $this->id );
				//Need to send the images for this product
				$this->send_images( null, $this );
				$this->send_sku( null, $this );
			}
			$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
			return TRUE;
		}
		catch( Exception $e )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
			switch( $e->getCode() )
			{
				case 400:
					$this->notify( __METHOD__ . ":" . __LINE__ . " Data sent: " . print_r( $this->data_array, true), "ERROR" );
				break;
			}
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return FALSE;
	}
	/*************************************************************************************************//**
	 * Send product details to WooCommerce of an item they already have.
	*
	* This is the same code as create_product.  ->send uses insert or update depending on a match
	 *
	 * ****************************************************************************************************/
	/*@bool@*/function update_product()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		set_time_limit( 60 );
		if( $this->recursive_call > 1 )
		{
			throw new Exception( "LOOP!", KSF_FCN_PATH_OVERRIDE );
		}
	
		try {
			$endpoint = "products";
			$this->build_data_array();
			$response_arr = $this->woo_rest->send( $endpoint, $this->data_array, $this );
			if( is_array( $response_arr ) AND isset( $response_arr[0] ) )
			{
				$response = $response_arr[0];
				if( is_object( $response ) AND isset( $response->id ) )
				{
					$this->id = $response->id;
				}
			}
			else if( is_object( $response_arr ) AND isset( $response_arr->id ) )
			{
				$this->id = $response_arr->id;
			}
			if( isset( $this->id ) )
			{
				$this->send_images( null, $this );
				$this->send_sku( null, $this );
			}
			$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
			return TRUE;
		}
		catch( Exception $e )
		{
			$msg =  $e->getMessage();
			$code = $e->getCode();
			switch( $code )
			{
				case '404': if( false !== strstr( $msg, "woocommerce_rest_product_invalid_id" ) )
						{
							$this->notify( __METHOD__ . ":" . __LINE__ . " Error " . $code . "::" . $msg . " ::: Woo_ID: " . $this->woo_id, "ERROR" );
							//Woo doesn't know about this woo_id.  Rebuild case?  Should send it NEW!!
							$this->notify( __METHOD__ . ":" . __LINE__ . " Resubmit with nulled woo_id " . __METHOD__, "WARN" );
							$old_woo_id = $this->woo_id;
							$this->woo_id = null;
							$this->recursive_call++;
							try {
								$this->update_product();
							}
							catch( Exception $e )
							{
								$this->recursive_call--;
								throw $e;
							}
							$this->recursive_call--;
						}
						break;
				default:
					$this->notify( __METHOD__ . ":" . __LINE__ . " Error " . $code . "::" . $msg, "ERROR" );
					break;
			}
			if( strpos( $msg, "product_invalid_sku" ) !== FALSE )
			{
				//Invalid or Duplicate SKU
				$this->notify( __METHOD__ . ":" . __LINE__ . " stock_id/SKU/Woo ID :: " . $this->stock_id . "::" . $this->sku . "::" . $this->woo_id, "ERROR" );
			}
					
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return FALSE;
	}
	//function recode_sku( $callback = null ) (inherited from -base)
	//function seek( $search_array = "", $callback = null ) (inherited from -base)
	//function woo2wooproduct inherited
	//function is_inactive() (inherited from -base)
	
	
	/**************************************************************************************************************
	 *
	 * 	function send_simple_products
	 *
	 * 	Send a list of simple (no variation like size or color) products
	 * 	to WOO.  This is sending items we HAVE NOT yet sent (those need
	 * 	to be updated, not created)
	 *
	 * 	IN:
	 * 	OUT: number of products sent
	 * 	Sets products_sent by adding the count (vice setting) in case we are called multiple times.
	 * 	@return int count of products sent.  -1 if no new products to send
	 **************************************************************************************************************/
	/*@int@*/function send_simple_products()
	{		
		$this->notify( __METHOD__  . ":" . __LINE__ . " Entering " . __METHOD__, "WARN");
		//Check for how many we are expecting to send...
		$woo = $this->model_woo();
		$woo->filter_new_only = TRUE;
		$tosend = $woo->count_new_products();
		if( $tosend <= 0 )
			return -1;
		$this->notify( __METHOD__  . ":" . __LINE__ . " About to send " . $tosend . " rows (all, not simple) to Woo", "WARN" );
		$test_max_send = 0;
		if( isset( $this->client->environment ) AND ( $this->client->environment == "devel" ) )
		{
			if( isset( $this->client->test_max_send ) )
				$test_max_send = $this->client->test_max_send;
		}
		$res = $woo->new_simple_product_ids( $test_max_send );
		$sendcount = 0;
		foreach( $res as $stock_id )
		{
			$this->notify( __METHOD__  . ":" . __LINE__ . " ********************************************************************************************************", "NOTICE");
			$this->woo2wooproduct( $stock_id, __METHOD__ );	//Sets the object variables with data from query
			$this->type = "simple";
			try
			{
				if( $this->create_product_wc() )
				{
					$sendcount++;
					//create_product_wc already calls send_images (as does update...)
					//$this->send_images( null, $this );
				}
			}
			catch( Exception $e )
			{
				$this->notify(  __METHOD__  . ":" . __LINE__ . " " .  $e->getMessage(), "WARN" );
			}
		}
		$this->products_sent += $sendcount;
		$this->notify( __METHOD__  . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN");
		return $sendcount;
	}
	function model_woo()
	{
		if( ! isset( $this->pz_model_woo ) OR ( null == $this->pz_model_woo ) )
		{
			require_once( 'class.model_woo.php' );
			//standard constructor args...	$this->serverURL, $this->key, $this->secret, $this->options, $this
			$this->pz_model_woo = new model_woo( $this->serverURL, $this->key, $this->secret, $this->options, $this );
		}
		else
		{
			$this->pz_model_woo->reset_values();
		}
		return $this->pz_model_woo;
	}
	/*****************************************************************************//**
	 *
	 *	Adds to products_updated
	 *
	 * ******************************************************************************/
	function update_simple_products()
	{			
		$this->notify(  __METHOD__  . ":" . __LINE__ . " Entering " . __METHOD__, "WARN");
		$woo = $this->model_woo();

		$updatecount = 0;
		$res = $woo->select_simple_products_for_update();

		while( $prod_data = db_fetch_assoc( $res ) )
		{
			$this->notify(  __METHOD__  . ":" . __LINE__ . " WHILE LOOP " . __FUNCTION__, "WARN");
			$this->notify( __METHOD__  . ":" . __LINE__ . " ********************************************************************************************************", "NOTICE");
			if( $this->debug > 1 AND $updatecount > 0 )
			{
				$this->notify(  __METHOD__  . ":" . __LINE__ . " **Leaving " . __METHOD__ . " due to DEBUG limits **", "WARN");
				return $updatecount;	//Only action 1 item
			}
			$this->notify(  __METHOD__  . ":" . __LINE__ . " Calling woo2wooproduct", "WARN");
			$this->woo2wooproduct( $prod_data['stock_id'], __FUNCTION__);
			$this->notify( __METHOD__ . ":" . __LINE__ . " TRACE ***ID=woo_id::" . $this->id, "DEBUG" );
			$this->notify( __METHOD__ . ":" . __LINE__ . " TRACE ***SKU: " . $this->sku, "DEBUG" );
			$this->type = "simple";
			if( isset( $this->id ) AND ( $this->id > 0 ) )
			{
				$this->notify(  __METHOD__  . ":" . __LINE__ . " Calling update PRODUCT for " . $this->stock_id, "WARN");
				try {
					if( $this->update_product() )
						$updatecount++;
					else
						display_notification( __METHOD__  . ":" . __LINE__ . " Product not updated.  DEBUG level: " . $this->debug );
				}
				catch( Exception $e )
				{
					if( $e->getCode() == KSF_FCN_PATH_OVERRIDE )
					{
						//Do Nothing
					}
					else
					{
						throw $e;
					}
				}
			}
			else
			{
				if( isset( $this->id ) )
					$this->notify(  __METHOD__  . ":" . __LINE__ . " ID set but reading less than 1: " . $this->id . ";  UNSETTING", "WARN");
				unset( $this->id );
				$this->notify(  __METHOD__  . ":" . __LINE__ . " Calling create PRODUCT", "WARN");
				try
				{
					if( $this->create_product_wc() )
						$this->notify(  __METHOD__  . ":" . __LINE__ . " INSERT SUCCESS", "WARN");
					else
						$this->notify(  __METHOD__  . ":" . __LINE__ . " Insert FAILED", "WARN");
				}
				catch( Exception $e )
				{
					if( WC_CLIENT_NOT_SET == $e->getCode() )
					{
						//no wc_client
						throw $e;
					}
					$this->notify(  __METHOD__  . ":" . __LINE__ . " " .  $e->getMessage(), "WARN" );
					
				}
			}
			//}
			//catch( Exception $e )
			//{
			//	display_notification( __METHOD__  . ":" . __LINE__ . "caught error code " . $e->getCode() . " and msg " . $e->getMessage() . " from " . $e->getFile() . ":" . $e->getLine() );
			//}
			$this->notify(  __METHOD__  . ":" . __LINE__ . " End WHILE LOOP lap", "WARN");
		}
		$this->notify(  __METHOD__  . ":" . __LINE__ . " Leaving update_simple_products", "WARN");
		$this->products_updated += $updatecount;
		return $updatecount;
	}
	//function product_tags( $stock_id )
	//function product_category( $stock_id )
	//function product_attributes( $stock_id )
	//function  woo_prod_variation_attributes()
	//function product_default_attributes( $stock_id )
	//function product_variations( $stock_id )

	/*******************************************************************************//**
	 * Called by send_images to build the image array
	 *
	 * VARIATIONS can only have 1 image!!
	 *
	* @param none but uses internal vars
	* @return none but sets internal vars
	**********************************************************************************/
	function product_images()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( ! isset( $this->stock_id ) )
			throw new Exception( "stock_id requried.", KSF_VALUE_NOT_SET );
		require_once( 'class.woo_images.php' );
		if( isset( $this->client->remote_img_srv ) )
			$remote_img_srv = $this->client->remote_img_srv;
		else
			$remote_img_srv = FALSE;
		$w_imgs = new woo_images( $this->stock_id, $this->client, $this->debug, $remote_img_srv );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $w_imgs->run();
	}
	//function send_images( $stock_id = null, $caller )
	//function send2woo( /*@string@*/$new_or_update = "new" )
	/*******************************************************************************//**
	 * Send Prices as an update
	 *
	 * @param string stock_id optional if this->stock_id set
	 * @returns bool were we able to process a stock_id
	 * *******************************************************************************/
	/*@bool@*/function send_prices( $stock_id = null, $caller )
	{
	//ENDPOINT and variation ID
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( isset( $stock_id )  )
			$this->stock_id = $stock_id;
		if( !isset( $this->stock_id ) )
		{
			throw new Exception( "Stock ID not available so can't process", KSF_VALUE_NOT_SET );
		}
		$woo = $this->woo_select_product();
		//Need to reset values between each product.
		$this->reset_values();
		if( isset( $woo->woo_id ) AND $woo->woo_id > 0 )
			$this->id = $woo->woo_id;
		else
		{
			//can't send sku to a non existant product
			throw new Exception( "Non existant Woo ID so can't send updates", KSF_VALUE_NOT_SET );
		}

		//For EVERY update routine we want to check this!
		if( $this->is_inactive() )
			$this->status = "private";
		else
			$this->status = "publish";
		if( isset( $woo->price ) )
			$this->regular_price = $woo->price;
		//INTERFACE - sales pricing/dates to replace this!!!
		$this->sale_price = null;
		$this->date_on_sale_from = null;
		$this->date_on_sale_to = null;

		$response = $this->send2woo( "update" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $response;
	}
	/*******************************************************************************//**
	 * Send SKU as an update
	 *
	 * @param string stock_id optional if this->stock_id set
	 * @returns bool were we able to process a stock_id
	 * *******************************************************************************/
	/*@bool@*/function send_sku( $stock_id = null, $caller )
	{
	//ENDPOINT and Variation ID
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( isset( $stock_id )  )
			$this->stock_id = $stock_id;
		if( !isset( $this->stock_id ) )
		{
			throw new Exception( "Stock ID not available so can't process", KSF_VALUE_NOT_SET );
		}
		$woo = $this->woo_select_product();
		//Need to reset values between each product.
		$this->reset_values();
		if( isset( $woo->woo_id ) AND $woo->woo_id > 0 )
			$this->id = $woo->woo_id;
		else
		{
			//can't send sku to a non existant product
			throw new Exception( "Non existant Woo ID so can't send updates", KSF_VALUE_NOT_SET );
		}

		//For EVERY update routine we want to check this!
		if( $this->is_inactive() )
			$this->status = "private";
		else
			$this->status = "publish";
		if( isset( $woo->price ) )
			$this->regular_price = $woo->price;
		$this->sku = $woo->stock_id;

		$response = $this->send2woo( "update" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $response;
	}
	//function set_download_info()
	//function product_downloads( $stock_id )
	//function product_dimensions( $stock_id )
	//function set_shipping_info()
	//function set_stock_status()

}

?>
