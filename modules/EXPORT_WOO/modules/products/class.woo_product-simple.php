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

class woo_product_simple extends woo_product_base {
	/***************************************************************************************//**
	 *
	 *
	 * ****************************************************************************************/
	function __construct( $serverURL, /*unused*/ $woo_rest_path,
				$key, $secret, /*unused*/$enviro = "devel", $client = null )
	{
		parent::__construct( $serverURL, $woo_rest_path, $key, $secret, $enviro, $client );
		return;
	}
	//function notify (inherited from woo_interface)
	/*********************************************************************************************//**
	 * Woo_Interface now builds the properties_array and write_properties_array from the defined table!
	 * 
	 * ***********************************************************************************************/
	//function build_properties_array() (inherited from -base)
	//function build_write_properties_array()
	//function reset_endpoint() inherited
	function build_interestedin()
	{
		parent::build_interestedin();
		$this->interestedin['WOO_SEND_PRODUCTS']['function'] = "send_products";
	}
	function send_products( $obj, $msg )
	{
		$this->send_simple_products();
		$this->update_simple_products();
	}
	
	/*******************************************************************************************//**
	 * Updates the internal woo table with data from WooCommerce that we ARENT the source of record 
	 *
	 *	requires that ID is set
	 *
	 * @param none
	 * @returns bool did we update WOO
	 * ******************************************************************************************/
	/*@bool@*/ //function update_wootable_woodata() (inherited from -base)

	// function update_woo_id( $id ) (inherited from -base)
	/****************************************************************************//**
	 * This function should send a new product to WooCommerce, "creating" it.
	 *
	 * Assumption is that this object has all of the data set appropriately
	 * for us to convert the variables into an array, and pass it along.
	 *
	 * ******************************************************************************/
	/*@bool@*/ //function create_product_wc() (inherited from -base)

	/*************************************************************************************************//**
	 * Send product details to WooCommerce of an item they already have.
	*
	* This is the same code as create_product.  ->send uses insert or update depending on a match
	 *
	 * ****************************************************************************************************/
	/*@bool@*/ //function update_product() (inherited from -base)

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
		{
			$this->eventloop->ObserverNotify( 'NOTIFY_LOG_DEBUG', __METHOD__  . ":" . __LINE__ . " Exiting " );
			return -1;
		}
		$this->eventloop->ObserverNotify( 'NOTIFY_LOG_DEBUG', __METHOD__  . ":" . __LINE__ . " About to send " . $tosend . " rows (all, not simple) to Woo" );
		$test_max_send = 0;
		if( isset( $this->client->environment ) AND ( $this->client->environment == "devel" ) )
		{
			if( isset( $this->client->test_max_send ) )
			{
				$test_max_send = $this->client->test_max_send;
				$this->eventloop->ObserverNotify( 'NOTIFY_LOG_DEBUG', __METHOD__  . ":" . __LINE__ . " TEST config limit to send " . $test_max_send );
			}
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
		$this->eventloop->ObserverNotify( 'WOO_PRODUCTS_SENT_COUNT', $sendcount );
		$this->eventloop->ObserverNotify( 'NOTIFY_LOG_DEBUG', __METHOD__  . ":" . __LINE__ . " Exiting " );
		return $sendcount;
	}
	//function model_woo() (inherited from -base)
	/*****************************************************************************//**
	 *
	 *	Adds to products_updated
	 *
	 * ******************************************************************************/
	function update_simple_products()
	{			
		$this->eventloop->ObserverNotify( 'NOTIFY_LOG_DEBUG', __METHOD__  . ":" . __LINE__ . " Entering " );
		$woo = $this->model_woo();	//Get the pointer to the WOO table model object
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
	// function product_tags( $stock_id ) (inherited from -base)
	// function product_category( $stock_id ) (inherited from -base)
	// function product_attributes( $stock_id )
 	//function  woo_prod_variation_attributes()
	//function product_default_attributes( $stock_id )
	//function product_variations( $stock_id )
	//function product_images() (inherited from -base)
	//function send_images( $stock_id = null, $caller ) (inherited from -base)
	//function send2woo( /*@string@*/$new_or_update = "new" ) (inherited from -base)
	//function send_prices( $stock_id = null, $caller ) (inherited from -base)
	//function woo_select_product()

	
	//function send_sku( $stock_id = null, $caller )
	//function set_download_info()
	//function product_downloads( $stock_id )
	//function product_dimensions( $stock_id )
	//function set_shipping_info()
	//function set_stock_status()

}

?>
