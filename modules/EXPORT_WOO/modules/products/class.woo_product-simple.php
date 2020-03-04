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

class woo_product-simple extends woo_product-base {
	/***************************************************************************************//**
	 *
	 *
	 * ****************************************************************************************/
	function __construct( $serverURL, /*unused*/ $woo_rest_path,
				$key, $secret, /*unused*/$enviro = "devel", $client = null )
	{
		parent::__construct( $serverURL, $woo_rest_path, $key, $secret, $enviro, $client );
		//parent::__construct($serverURL, $key, $secret, $options, $client);

		//$this->provides[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
         	//$this->provides[] = array( 'title' => 'Init Tables', 'action' => 'init_tables_form', 'form' => 'init_tables_form', 'hidden' => FALSE );
		return;
	}
	//function notify (inherited from woo_interface)
	/*********************************************************************************************//**
	 * Woo_Interface now builds the properties_array and write_properties_array from the defined table!
	 * 
	 * ***********************************************************************************************/
	//function build_properties_array() (inherited from -base)
	//function build_write_properties_array()
	/*@int@*/function send_products()
	{
		$this->products_sent = $this->products_updated = 0;
		try
		{
			$count = $this->send_simple_products();	//sets products_sent
			if( $this->debug < 2 )
				$count += $this->update_simple_products(); //sets products_updated
			//Not worrying about the variable products for the moment...
			//$count = $this->send_variable_products();	//sets products_sent
			//$count += $this->update_variable_products(); //sets products_updated
			return $count;
		}
		catch( Exception $e )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
			throw $e;
		}
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
	/*******************************************************************************//**
	 * Grab data out of the WOO table using the woo class and populate our fields.
	 *
	 * Sets type to "simple".  This needs to be overridden in variable products
	 *
	 * @param string stock_id optional if this->stock_id set
	 * @returns bool were we able to process a stock_id
	 * *******************************************************************************/
	/*@bool@*/function woo2wooproduct( $stock_id = null, $caller )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( isset( $stock_id )  )
			$this->stock_id = $stock_id;
		if( !isset( $this->stock_id ) )
		{
			throw new Exception( "Stock ID not available so can't process", KSF_VALUE_NOT_SET );
		}
			
		$remove_desc_array = array(  "**Use Custom Form**,", "**Use Custom Order Form**,", );
		$removed_desc_array = array( "",                     "",                           );
		$woo = $this->model_woo();
		$woo->stock_id = $this->stock_id;
		$woo->select_product();
		//Need to reset values between each product.
		$this->reset_values();
		if( isset( $woo->woo_id ) AND $woo->woo_id > 0 )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " Setting ID=woo_id::" . $woo->woo_id , "WARN" );
			$this->id = $woo->woo_id;
		}
		else
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " woo_id Not Set" , "WARN" );
			$this->id = null;
		}
		foreach( $woo->fields_array as $fieldrow )
		{
			//Set OUR value == to woo's
			if( isset( $woo->$fieldrow['name'] ) )
			{
				$this->notify( __METHOD__ . ":" . __LINE__ . " Setting " . $fieldrow['name'] . " to " . $woo->$fieldrow['name'], "DEBUG" );
				$this->$fieldrow['name'] = utf8_encode( $woo->$fieldrow['name'] );
			}
		}
		$this->slug = $this->name = $this->short_description = utf8_encode( str_replace( $remove_desc_array, $removed_desc_array, $this->description ) );

		$this->featured = false;
		/**************************
		* These are variation properties too
		*************************/
		$this->description = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $this->long_description ) );
		if( ! isset( $this->description ) )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " woo2wooproduct Description not set", "WARN" );
		}
		$this->permalink = null;
		if( isset( $this->price ) )
			$this->regular_price = $this->price;
		$this->sale_price = null;
		$this->date_on_sale_from = null;
		$this->date_on_sale_to = null;
		if( $this->is_inactive() )
		{
			$this->status = "private";
		}
		else
		{
			$this->status = "publish";
			$this->catalog_visibility = "visible";
		}
		if( $this->tax_class == "GST" )
			$this->tax_class = "Standard";	//Woo provides Standard, Reduced Rate, Zero
		else
			$this->tax_class = "Reduced Rate";	//Woo provides Standard, Reduced Rate, Zero
		$this->price_html = null;
		$this->virtual = false;
		$this->set_download_info();

		$this->manage_stock = true;
		//$this->managing_stock = true;
		$this->backorders = "notify";
		$this->sold_individually = false;	//true only allows 1 of this product per order
		$this->in_stock = true;	//Need to extend so that categories that are Special Order do not show IN STOCK in WOO.
		if( isset( $this->instock ) )
			$this->stock_quantity = $this->instock;
		else
			$this->stock_quantity = 0;
		$this->set_stock_status();
		$this->set_shipping_info();
		/*************************
		* !variation
		*************************/

		$this->reviews_allowed = TRUE;
		$this->parent_id = null;
		$this->purchase_note = null;

		if( isset( $this->woo_category_id ) )
			$this->categories = array(  array( "id" => $this->woo_category_id ) );	//Woo API is expecting id=>NUM.  
											//Can we add extra categories in FA through foreign_codes?
											//We can also add a XREF table
		else
			$this->categories = null;
			

		//$this->tags = array( "id" => XXX, "name" => YYY, "slug" => ZZZ );
		$this->tags = $this->product_tags( $stock_id );
//->send_images( $stock_id, $this );
		$this->attributes = $this->product_attributes( $stock_id );
		$this->default_attributes = $this->product_default_attributes( $stock_id );
		$this->variations = $this->product_variations( $stock_id );
		$this->menu_order = "1";
		$this->notify( __METHOD__  . ":" . __LINE__ . " SETTING Sku to Stock_ID: " . $this->stock_id, "WARN");
		$this->sku = $this->stock_id;
		$this->notify( __METHOD__  . ":" . __LINE__ . " Sku: " . $this->sku, "WARN");

		$this->notify( __METHOD__  . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN");
		return TRUE;
	}
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
		$woo->debug = $this->debug;
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
	//function model_woo() (inherited from -base)
	/*****************************************************************************//**
	 *
	 *	Adds to products_updated
	 *
	 * ******************************************************************************/
	function update_simple_products()
	{			
		$this->notify(  __METHOD__  . ":" . __LINE__ . " Entering " . __METHOD__, "WARN");
		$woo = $this->model_woo();

		$woo->debug = $this->debug;
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
	function product_attributes( $stock_id )
	{
		$this->notify( __METHOD__  . ":" . __LINE__ . " Entering " . __METHOD__, "WARN");
		$this->notify( __METHOD__  . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN");
		return null;
		/*
		 *	For a Variable Product, the ATTRIBUTES is just a list
		 *	of the possible combinations. 
		 *		id(global)/name, 
		 *		position, 
		 *		visible t/f, 
		 *		variation(t/f can be used for variations), 
		 *		options (values)
		 *	The DEFAULT ATTRIBUTES is the default.
		 *		id/name
		 *		option
		 *
		 *	In a Variation, the ATTRIBUTES is an array of the appropriate
		 *	IDs or NAMEs plus the OPTION.
		 * */
		require_once( 'class.woo_prod_variation_attributes.php' );
		
		$w_attr = new woo_prod_variation_attributes( $this->serverURL, $this->key, $this->secret, $stock_id, $this->client );
		$arr = $w_attr->get_by_sku( $stock_id );
		$retarr = array();
		$name = "";
		foreach( $arr as $sku => $val )
		{
			if( $name != $val['name'] )
			{
				if( isset( $values ) )	//don't set blank first array
				{
					$retarr[] = array( 'visible' => 'true',
			       			'variation' => 'true',
						'name' => $name,
						'options' => $values );
					unset( $values );
				}
				$name = $val['name'];
				$values = array();
				$values[] = $val['option'];
			}
		}
		$retarr[] = array( 'visible' => 'true',
		 		'variation' => 'true',
				'name' => $name,
				'options' => $values );
		return $retarr;
	}
	function  woo_prod_variation_attributes()
	{
		$this->notify( __METHOD__  . ":" . __LINE__ . " Entering " . __METHOD__, "WARN");
		if( ! isset( $this->woo_prod_variation_attributes ) )
		{
			$this->notify( __METHOD__  . ":" . __LINE__ . " Setting new class ohject.  Should only happen once per instance ", "NOTICE");
			require_once( 'class.woo_prod_variation_attributes.php' );
			$this->woo_prod_variation_attributes = new woo_prod_variation_attributes( $this->serverURL, $this->key, $this->secret, $this->stock_id, $this->client );
		}
		$this->notify( __METHOD__  . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN");
		return $this->woo_prod_variation_attributes;
		
	}
	function product_default_attributes( $stock_id )
	{
		$this->notify( __METHOD__  . ":" . __LINE__ . " Entering " . __METHOD__, "WARN");
		$w_attr = $this->woo_prod_variation_attributes();
		$arr = $w_attr->get_by_sku( $stock_id );
		$retarr = array();
		foreach( $arr as $sku => $val )
		{
			$this->notify( __METHOD__  . ":" . __LINE__ . " Add attribute to list: " . $val, "NOTICE");
			$retarr[] = $val;
		}
		$this->notify( __METHOD__  . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN");
		return $retarr;
	}
	function product_variations( $stock_id )
	{
		$this->notify( __METHOD__  . ":" . __LINE__ . " Entering " . __METHOD__, "WARN");
		$this->notify( __METHOD__  . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN");
		return null;
		/*
		require_once( 'class.woo_variations.php' );
		$w_imgs = new woo_variations( null, null, null, $stock_id, $this->client );
		return $w_imgs->run();
		*/
	}
	/*******************************************************************************//**
	* Called by send_images to build the image array
	* @param none but uses internal vars
	* @return none but sets internal vars
	**********************************************************************************/
	//function product_images() (inherited from -base)

	/*******************************************************************************//**
	 * Send Image Properties as an update
	 *
	 * @param string stock_id optional if this->stock_id set
	 * @returns bool were we able to process a stock_id
	 * *******************************************************************************/
	/*@bool@*/ //function send_images( $stock_id = null, $caller ) (inherited from -base)

	/**********************************************************************//**
	* Send product details to WooCommerce.  Insert or Update as appropriate
	*
	* @param string is this a new product AFAIK or an update
	* @return bool did we send?  
	**************************************************************************/
	/*@bool@*/ //function send2woo( /*@string@*/$new_or_update = "new" ) (inherited from -base)
	
	/*******************************************************************************//**
	 * Send Prices as an update
	 *
	 * @param string stock_id optional if this->stock_id set
	 * @returns bool were we able to process a stock_id
	 * *******************************************************************************/
	/*@bool@*/ //function send_prices( $stock_id = null, $caller ) (inherited from -base)

	/*******************************************************************//**
	* Select a row matching the set stock_id
	*
	*	Called by send_prices
	*	Called by send_sku
	*	Called by send_images
	*
	* @param none but uses stock_id
	* @return object model_woo
	***********************************************************************/
	function woo_select_product()
	{
		if( !isset( $this->stock_id ) )
			throw new Exception( "Stock_id not set.  Needed!", KSF_VALUE_NOT_SET );
		$woo = $this->model_woo();
		$woo->stock_id = $this->stock_id;
		$woo->select_product();
		return $woo;
	}

	/*******************************************************************************//**
	 * Send SKU as an update
	 *
	 * @param string stock_id optional if this->stock_id set
	 * @returns bool were we able to process a stock_id
	 * *******************************************************************************/
	/*@bool@*/function send_sku( $stock_id = null, $caller )
	{
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
	function set_download_info()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		/*****************************************************************//**
		 *Downloads
		 *
		 * 	id, name, file(url) are properties within an array
		 *
		 * ********************************************************************/
		/*
		 *	$this->download = array( "id" => XXX, "name" => YYY, "file" => ZZZ );
		 * */
		$this->downloadable = false;
		$this->downloads = null;
		$this->download_limit = null;
		$this->download_expiry = null;
		$this->download_type = null;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return;
	}
	function product_downloads( $stock_id )
	{
		return null;
		/*
		require_once( 'class.woo_downloads.php' );
		$w_imgs = new woo_downloads( null, null, null, $stock_id, $this->client );
		return $w_imgs->run();
		*/
	}
	function product_dimensions( $stock_id )
	{
		return null;
		/*
		require_once( 'class.woo_dimensions.php' );
		$w_imgs = new woo_dimensions( null, null, null, $stock_id, $this->client );
		return $w_imgs->run();
		*/
	}
	function set_shipping_info()
	{
		/*Shipping*/
/*
			'weight',
			'dimensions',
			'shipping_required',
			'shipping_taxable',
			'shipping_class',
			'shipping_class_id',
*/
		//TODO:
		//	Extend to check to see if we have the dimension module installed
		//	Use a different class to set these including units and translations
		//$this->shipping_class = 'parcel';
		//$this->weight = '';	//Should be set by foreach woo above
		$dim_var_array = array( 'width', 'length', 'height');
		foreach($dim_var_array as $var )
		{
			if( isset( $this->$var ) && strlen( $this->$var ) > 1 )
			{
				$this->dimensions[$var] = utf8_encode( $this->$var );
			}
			$this->dimensions['unit'] = "cm";	
		}
	}
	function set_stock_status()
	{
		if( $this->stock_quantity < 1 )
		{
			$this->in_stock = false;
			$this->stock_status = "outofstock";
			//EXTEND so that if the on_order count in FA is >0 then set backordered (bool) status
		}
		else
		{
			$this->stock_status = "instock";
		}
	}

}

?>
