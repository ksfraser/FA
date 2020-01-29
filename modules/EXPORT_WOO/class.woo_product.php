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

require_once( 'class.woo_rest.php' );
require_once( 'class.woo_interface.php' );
require_once( 'EXPORT_WOO.inc' );

class woo_product extends woo_interface {
	var $id;	//integer 	Unique identifier for the resource.  read-only
	var $name;	//string 	Product name.
	var $slug;	//string 	Product slug.
	var $permalink;	//string 	Product URL.  read-only
	var $created_at;	//date-time 	The date the product was created, in the site’s timezone.  read-only
	var $updated_at;	//date-time 	The date the product was last modified, in the site’s timezone.  read-only
	var $type;	//string 	Product type. Default is simple. Options (plugins may add new options): simple, grouped, external, variable.
	var $status;	//string 	Product status (post status). Default is publish. Options (plugins may add new options): draft, pending, private and publish.
	var $featured;	//boolean 	Featured product. Default is false.
	var $catalog_visibility;	//string 	Catalog visibility. Default is visible. Options: visible (Catalog and search), catalog (Only in catalog), search (Only in search) and hidden (Hidden from all).
	var $description;	//string 	Product description.
	var $short_description;	//string 	Product short description.
	var $sku;	//string 	Unique identifier.
	var $price;	//string 	Current product price. This is set from regular_price and sale_price.  read-only
	var $regular_price;	//string 	Product regular price.
	var $sale_price;	//string 	Product sale price.
	var $date_on_sale_from;	//string 	Start date of sale price. Date in the YYYY-MM-DD format.
	var $date_on_sale_to;	//string 	Sets the sale end date. Date in the YYYY-MM-DD format.
	var $price_html;	//string 	Price formatted in HTML, e.g. <del><span class=\"woocommerce-Price-amount amount\"><span class=\"woocommerce-Price-currencySymbol\">&#36;&nbsp;3.00</span></span></del> <ins><span class=\"woocommerce-Price-amount amount\"><span class=\"woocommerce-Price-currencySymbol\">&#36;&nbsp;2.00</span></span></ins> read-only
	var $on_sale;	//boolean 	Shows if the product is on sale.  read-only
	var $purchasable;	//boolean 	Shows if the product can be bought.  read-only
	var $total_sales;	//integer 	Amount of sales.  read-only
	var $virtual;	//boolean 	If the product is virtual. Virtual products are intangible and aren’t shipped. Default is false.
	var $downloadable;	//boolean 	If the product is downloadable. Downloadable products give access to a file upon purchase. Default is false.
	var $downloads;	//array 	List of downloadable files. See Downloads properties.
	var $download_limit;	//integer 	Amount of times the product can be downloaded, the -1 values means unlimited re-downloads. Default is -1.
	var $download_expiry;	//integer 	Number of days that the customer has up to be able to download the product, the -1 means that downloads never expires. Default is -1.
	var $download_type;	//string 	Download type, this controls the schema on the front-end. Default is standard. Options: 'standard' (Standard Product), application (Application/Software) and music (Music).
	var $external_url;	//string 	Product external URL. Only for external products.
	var $button_text;	//string 	Product external button text. Only for external products.
	var $tax_status;	//string 	Tax status. Default is taxable. Options: taxable, shipping (Shipping only) and none.
	var $tax_class;	//string 	Tax class.
	var $manage_stock;	//boolean 	Stock management at product level. Default is false.
	var $managing_stock;	//boolean 	Stock management at product level. Default is false.
	var $stock_quantity;	//integer 	Stock quantity. If is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.
	var $in_stock;	//boolean 	Controls whether or not the product is listed as “in stock” or “out of stock” on the frontend. Default is true.
	var $backorders;	//string 	If managing stock, this controls if backorders are allowed. If enabled, stock quantity can go below 0. Default is no. Options are: no (Do not allow), notify (Allow, but notify customer), and yes (Allow).
	var $backorders_allowed;	//boolean 	Shows if backorders are allowed.  read-only
	var $backordered;	//boolean 	Shows if a product is on backorder (if the product have the stock_quantity negative).  read-only
	var $sold_individually;	//boolean 	Allow one item to be bought in a single order. Default is false.
	var $weight;	//string 	Product weight in decimal format.
	var $dimensions;	//array 	Product dimensions. See Dimensions properties.
	var $shipping_required;	//boolean 	Shows if the product need to be shipped.  read-only
	var $shipping_taxable;	//boolean 	Shows whether or not the product shipping is taxable.  read-only
	var $shipping_class;	//string 	Shipping class slug. Shipping classes are used by certain shipping methods to group similar products.
	var $shipping_class_id;	//integer 	Shipping class ID.  read-only
	var $reviews_allowed;	//boolean 	Allow reviews. Default is true.
	var $average_rating;	//string 	Reviews average rating.  read-only
	var $rating_count;	//integer 	Amount of reviews that the product have.  read-only
	var $related_ids;	//array 	List of related products IDs (integer).  read-only
	var $upsell_ids;	//array 	List of up-sell products IDs (integer). Up-sells are products which you recommend instead of the currently viewed product, for example, products that are more profitable or better quality or more expensive.
	var $cross_sell_ids;	//array 	List of cross-sell products IDs. Cross-sells are products which you promote in the cart, based on the current product.
	var $parent_id;	//integer 	Product parent ID (post_parent).
	var $purchase_note;	//string 	Optional note to send the customer after purchase.
	var $categories;	//array 	List of categories. See Categories properties.		id => 9, ...
	var $tags;	//array 	List of tags. See Tags properties.
	var $images;	//array 	List of images. See Images properties
	var $attributes;	//array 	List of attributes. See Attributes properties.   USED for variations
	var $default_attributes;	//array 	Defaults variation attributes, used only for variations and pre-selected attributes on the frontend. See Default Attributes properties.
	var $variations;	//array 	List of variations. See Variations properties
	var $grouped_products;	//string 	List of grouped products ID, only for group type products.  read-only
	var $menu_order;		//integer 	Menu order, used to custom sort products.
		/*******/
	var $woo_products_list;	//!< Array of products returned by woo through get_products for match_product
	var $wc_client;	//!< WC_API_Client to be migrated into the woo_rest class
	var $caller; //!< Class which class instantiated this one
	var $products_sent;
	var $products_updated;
	var $get_product_by_sku_count; //every time we go in there we can get 10 items back from woo_commerce.  Can use to change which page...

	var $force_update; //!< grabbing from client (i.e. EXPORT_WOO)
	var $b_send_images;	//!< bool whether we should send images.  Func of name too
	var $pz_model_woo;	//!< place to store model_woo object
	var $woo_prod_variation_attributes;	//!< object

	/***************************************************************************************//**
	 *
	 *
	 * ****************************************************************************************/
	function __construct( $serverURL, /*unused*/ $woo_rest_path,
				$key, $secret, /*unused*/$enviro = "devel", $client = null )
	{
		$this->caller = $client;
		$options = array();
		set_time_limit( 30 );
		$this->need_rest_interface = TRUE;
		parent::__construct($serverURL, $key, $secret, $options, $client);

	//	$this->provides[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
         //       $this->provides[] = array( 'title' => 'Init Tables', 'action' => 'init_tables_form', 'form' => 'init_tables_form', 'hidden' => FALSE );

		$this->products_sent = $this->products_updated = 0;
		if( isset( $client ) )
			if( isset( $client->force_update ) )
				$this->force_update = $client->force_update;
			if( isset( $client->send_images ) )
				$this->b_send_images = $client->send_images;
			else
				$this->b_send_images = FALSE;	//testing is failing but that could be a local machine problem
		$this->to_match_array = array( "sku", "slug", "description", "short_description" );
		$this->match_worth['sku'] = 2;
		$this->match_worth['slug'] = 2;
		$this->match_worth['description'] = 1;
		$this->match_worth['short_description'] = 1;
		$this->match_need = 2;
		$this->search_array = array( "woo_id", "name", "slug", "sku", "description", "short_description" );
		return;
	}
	//function notify (inherited from woo_interface)
	function reset_endpoint()	//Required by _interface
	{
		$this->endpoint = "products";
	}
	/*********************************************************************************************//**
	 * Woo_Interface now builds the properties_array and write_properties_array from the defined table!
	 * 
	 * ***********************************************************************************************/
	function build_properties_array()
	{
		/*All properties*/
		$this->properties_array = array(
			'id',
			'name',
			'slug',
			'permalink',
			'created_at',
			'updated_at',
			'type',
			'status',
			'featured',
			'catalog_visibility',
			'description',
			'short_description',
			'sku',
			'price',
			'regular_price',
			'sale_price',
			'date_on_sale_from',
			'date_on_sale_to',
			'price_html',
			'on_sale',
			'purchasable',
			'total_sales',
			'virtual',
			'downloadable',
			'downloads',
			'download_limit',
			'download_expiry',
			'download_type',
			'external_url',
			'button_text',
			'tax_status',
			'tax_class',
			'manage_stock',
			'managing_stock',
			'stock_quantity',
			'in_stock',
			'backorders',
			'backorders_allowed',
			'backordered',
			'sold_individually',
			'weight',
			'dimensions',
			'shipping_required',
			'shipping_taxable',
			'shipping_class',
			'shipping_class_id',
			'reviews_allowed',
			'average_rating',
			'rating_count',
			'related_ids',
			'upsell_ids',
			'cross_sell_ids',
			'parent_id',
			'purchase_note',
			'categories',
			'tags',
			'images',
			'attributes',
			'default_attributes',
			'variations',
			'grouped_products',
			'menu_order',
		);
	}
	/*********************************************************************************************//**
	 * Woo_Interface now builds the properties_array and write_properties_array from the defined table!
	 * 
	 * ***********************************************************************************************/
	function build_write_properties_array()
	{
		/*Took the list of properties, and removed the RO ones*/
		$this->write_properties_array = array('name',
			'slug',
			'type',
			'status',
			'featured',
			'catalog_visibility',
			'description',
			'short_description',
			'sku',
			'regular_price',
			'sale_price',
			'date_on_sale_from',
			'date_on_sale_to',
			'virtual',
			'downloadable',
			'downloads',
			'download_limit',
			'download_expiry',
			'download_type',
			'external_url',
			'button_text',
			'tax_status',
			'tax_class',
			'manage_stock',
			'managing_stock',
			'stock_quantity',
			'in_stock',
			'backorders',
			'sold_individually',
			'weight',
			'dimensions',
			'shipping_class',
			'reviews_allowed',
			'upsell_ids',
			'cross_sell_ids',
			'parent_id',
			'purchase_note',
			'categories',
			'tags',
			'images',
			'attributes',
			'default_attributes',
			'variations',
			'menu_order',
			'name'
		);
	}
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
	/*@bool@*/function update_wootable_woodata()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		echo __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__;
		if( ! isset( $this->id ) )
		{
			throw new InvalidArgumentException( __METHOD__ . ":" . __LINE__ . "ID from woo not set" );
			$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
			echo __METHOD__ . ":" . __LINE__ . " Bad Arg Leaving " . __METHOD__;
			return FALSE;
		}
		if( ! isset( $this->sku ) )
		{
			throw new InvalidArgumentException( __METHOD__ . ":" . __LINE__ . " SKU from woo not set" );
			$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
			echo __METHOD__ . ":" . __LINE__ . " Bad Arg Leaving " . __METHOD__;
			return FALSE;
		}
		if( ! isset( $this->updated_at ) )
		{
			$this->updated_at = date("Y-m-d");
		}
		$woo = $this->model_woo();
		$woo->woo_last_update = $this->updated_at;
		$woo->updated_ts = $this->updated_at;
		$woo->woo_id = $this->id;
		$woo->date_on_sale_from = $this->date_on_sale_from;
		$woo->date_on_sale_to = $this->date_on_sale_to;
		$woo->tax_status = $this->tax_status;
		$woo->stock_id = $this->sku;
		try
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " Try " . __METHOD__, "WARN" );
			$woo->update_on_sale_data();
			$woo->update_woo_id();
			$woo->update_woo_last_update();
		}
		catch( InvalidArgumentException $e )
		{
			echo "Exception thrown in " . $e->getFile() . " at line " . $e->getLine() . " with message " . $e->getMessage();
			return FALSE;
		}
		catch( Exception $e )
		{
			throw $e;
			return FALSE;
		}
		//finally
		//{		
			display_notification( __LINE__ . " (Tried) Updated wootable sku " . $this->sku . " with dates and id_" . $this->id . "_" );
			$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
			echo __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__;
		//}
		return TRUE;
	}
	function update_woo_id( $id )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$woo = $this->model_woo();
                $woo->stock_id = $this->stock_id;
                $woo->woo_id = $this->id = $id;
		$woo->update_woo_id();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
	}
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
			if( ! isset( $this->id ) )
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
                		$woo->update_woo_id();
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
			throw new Exception( "LOOP!" );
		}
	
		try {
			$endpoint = "products";
		//	unset( $this->sku );	//Looks like every update with a SKU set comes back invald/duplicate
			$this->build_data_array();
			$response = $this->woo_rest->send( $endpoint, $this->data_array, $this );
			$this->id = $response->id;
			//$this->products_sent++;
				$this->send_images( null, $this );
				$this->send_sku( null, $this );
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
							$this->update_product();
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
	function recode_sku( $callback = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		//skus with '/' in them will fail GET if not POST
		$this->sku = str_replace( '/', '_', $this->sku );
		if( null != $callback )
			$this->$callback();
		$this->notify( __METHOD__  . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN");
		return;
	}
	function seek( $search_array = "", $callback = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " " . print_r( $search_array, true ), "DEBUG" );

		if( ! is_array( $search_array ) )
			throw new Exception( "Search Param is invalid", KSF_VALUE_NOT_SET );
		$endpoint = "products";
		$response = $this->woo_rest->get( $endpoint, $search_array, $this );
		$this->notify( __METHOD__ . ":" . __LINE__ . " " . print_r( $response, true ), "DEBUG" );
		if( isset( $callback ) )
			$callback( $response );

		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $response;
	}
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
	function is_inactive()
	{
		$this->notify( __METHOD__  . ":" . __LINE__ . " Entering " . __METHOD__, "WARN");
		require_once( '../ksf_modules_common/class.fa_stock_master.php' );
		if( !isset( $this->stock_master ) )
			$this->stock_master = new fa_stock_master( null );
		if( ! isset( $this->stock_id ) )
			throw new Exception( "Need stock_id to be set :(", KSF_VALUE_NOT_SET );
		$this->stock_master->set( 'stock_id', $this->stock_id );
		$this->stock_master->getById();	
		$this->notify( __METHOD__  . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN");
		return $this->stock_master->get( 'inactive' );
	}
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
				if( $this->update_product() )
					$updatecount++;
				else
					display_notification( __METHOD__  . ":" . __LINE__ . " Product not updated.  DEBUG level: " . $this->debug );
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
	function product_tags( $stock_id )
	{
		return null;
		/*
		require_once( 'class.woo_tags.php' );
		$w_imgs = new woo_tags( null, null, null, $stock_id, $this->client );
		return $w_imgs->run();
		 */
	}
	function product_category( $stock_id )
	{
		return null;
		/*
		require_once( 'class.woo_category.php' );
		$w_imgs = new woo_category( null, null, null, $stock_id, $this->client );
		return $w_imgs->run();
		*/
	}
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

	/*******************************************************************************//**
	 * Send Image Properties as an update
	 *
	 * @param string stock_id optional if this->stock_id set
	 * @returns bool were we able to process a stock_id
	 * *******************************************************************************/
	/*@bool@*/function send_images( $stock_id = null, $caller )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( ! $this->b_send_images )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
			return FALSE;
		}
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

		//images in position 0 are the featured image
		//1.. are in a gallery
		//$image2 = array( 'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg', 'position' => 2 );
		//$images = array( $image1, $image2 );
		$this->images = $this->product_images();
		$response = $this->send2woo( "update" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $response;
	}
	/**********************************************************************//**
	* Send product details to WooCommerce.  Insert or Update as appropriate
	*
	* @param string is this a new product AFAIK or an update
	* @return bool did we send?  
	**************************************************************************/
	/*@bool@*/function send2woo( /*@string@*/$new_or_update = "new" )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$this->reset_endpoint();
		try {
			$this->build_data_array();
			if( ! strcasecmp( $new_or_update , "new" ) )
			{
				$response = $this->woo_rest->send( $this->endpoint, $this->data_array, $this );
				$this->id = $response->id;
			}
			else
			if( ! strcasecmp( $new_or_update , "update" ) )
			{
				$response = $this->woo_rest->put( $this->endpoint, $this->data_array, $this );
			}
			else
			{
				throw new Exception( "Invalid variable passed in: " . $new_or_update, KSF_INVALID_DATA_VALUE );
			}
			$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
			return TRUE;
		}
		catch( Exception $e )
		{
			$msg = $e->getMessage();
			$code = $e->getCode();
			switch( $code )
			{
				case 400:
					$this->notify( __METHOD__ . ":" . __LINE__ . " Data sent: " . print_r( $this->data_array, true), "WARN" );
					if( strpos( $msg, "product_invalid_sku" ) !== FALSE )
					{
						//we need to Update our woo table as well as then resend an update to WC
						$response = $this->seek( array( 'search' => $this->sku ) );
						if( $this->fuzzy_match( $response ) )
						{
							if( $this->update_wootable_woodata() )
								$this->send2woo( "update" );
							else
								$this->notify( __METHOD__ . ":" . __LINE__ . " Recovery Attempt failed ", "ERROR" );
						}
						else
						{
						}
					}
				default:
					$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $code . ":" . $msg, "WARN" );
					break;
			}
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return FALSE;
	}
	/*******************************************************************************//**
	 * Send Prices as an update
	 *
	 * @param string stock_id optional if this->stock_id set
	 * @returns bool were we able to process a stock_id
	 * *******************************************************************************/
	/*@bool@*/function send_prices( $stock_id = null, $caller )
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
		//INTERFACE - sales pricing/dates to replace this!!!
		$this->sale_price = null;
		$this->date_on_sale_from = null;
		$this->date_on_sale_to = null;

		$response = $this->send2woo( "update" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return $response;
	}
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
