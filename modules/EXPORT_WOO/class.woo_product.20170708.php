<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array appropriately
 * */

/*******************************************************************************************//**
 * This module is for handling frontaccounting to WooCommerce products.
 *
 * Any activities storing product data in FA is done through the woo class. (May be
 * some fcns here that need to be migrated)
 *
 * Any activites connecting to an outside web service will be done through the
 * woo_rest class (May be some fcns here that need to be migrated)
 *
 *
 * TODO:
 *	Add variable product sending
 *	FIx Get By SKU so that we can match up product numbers for existing products
 *		so that we can send updates for attributes
 *	Migrate to own REST library once it is rewritten
 *	Be able to request products and do something with them
 *		What if anything do we want to update in FA since it is the source of record.
 *
 * ***********************************************************************************************/

require_once( 'class.woo_rest.php' );
require_once( 'class.woo_interface.php' );

class woo_product extends woo_interface {
	var $id;	//integer 	Unique identifier for the resource.  read-only
	var $name;	//string 	Product name.
	var $title;
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
	var $price;	//string 	Current product price. This is setted from regular_price and sale_price.  read-only
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
	var $categories;	//array 	List of categories. See Categories properties.
	var $tags;	//array 	List of tags. See Tags properties.
	var $images;	//array 	List of images. See Images properties
	var $attributes;	//array 	List of attributes. See Attributes properties.
	var $default_attributes;	//array 	Defaults variation attributes, used only for variations and pre-selected attributes on the frontend. See Default Attributes properties.
	var $variations;	//array 	List of variations. See Variations properties
	var $grouped_products;	//string 	List of grouped products ID, only for group type products.  read-only
	var $menu_order;		//integer 	Menu order, used to custom sort products.

	var $woo_rest;
	var $header_array;
	var $woo_rest_path_base;
	var $woo_rest_path;
	var $subpath;
	var $woo_products_list;	//!< Array of products returned by woo through get_products for match_product
	var $wc_client;	//!< WC_API_Client to be migrated into the woo_rest class
	var $caller; //!< Class which class instantiated this one

	/***************************************************************************************//**
	 *
	 *
	 * ****************************************************************************************/
	function __construct( $serverURL, $woo_rest_path,
				$key, $secret, $enviro = "devel", $client = null )
	{
		$this->serverURL = $serverURL;
		$this->enviro = $enviro;
		$this->subpath = "products";
		$this->conn_type = "POST" ;
		$this->woo_rest_path_base = $woo_rest_path;
		$this->woo_rest_path = $woo_rest_path;
		$this->caller = $client;
		$options = array();
	
		set_time_limit( 300 );
		parent::__construct($serverURL, $key, $secret, $options, $client);


		return;
	}
	function new_woo_rest()
	{
		$this->build_data_array();
		$this->woo_rest = new woo_rest( $this->serverURL, $this->subpath, $this->data_array, $this->key, $this->secret, $this->conn_type, $this->woo_rest_path, null, $this->enviro, $this->debug );
	}
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
			'name', 'title'
		);
	}
	/*@int@*/function send_products()
	{
		$count = $this->send_simple_products();
		if( $this->debug < 2 )
			$count += $this->update_simple_products();
		//Not worrying about the variable products for the moment...
		return $count;
	}
	function update_wootable_woodata()
	{
		if( ! isset( $this->updated_at ) )
		{
			$this->updated_at = date("Y-m-d");
		}
		require_once( 'class.woo.php' );
		//standard constructor args...	$this->serverURL, $this->key, $this->secret, $this->options, $this
		$woo = new woo( $this->serverURL, $this->key, $this->secret, $this->options, $this );
		$woo->woo_last_update = $this->updated_at;
		$woo->woo_id = $this->id;
		$woo->date_on_sale_from = $this->date_on_sale_from;
		$woo->date_on_sale_to = $this->date_on_sale_to;
		$woo->tax_status = $this->tax_status;
		$woo->stock_id = $this->sku;
		$woo->update_on_sale_data();
		$woo->update_woo_id();
		$woo->update_woo_last_update();
		display_notification( "Updated wootable sku " . $this->sku . " with dates and id_" . $this->id . "_" );
	}
	function delete_by_sku( $sku )
	{
		require_once( 'class.woo.php' );
		$woo = new woo( $this->serverURL, $this->key, $this->secret, $this->options, $this );
		$woo->delete_by_sku( $sku );
	}
	/*********************************************************************************//**
	 *
	 * Handle the response codes from the server.
	 *
	 * @return bool 
	 *
	 * ********************************************************************************/
	/*@bool@*/function rest_error_handler( $caller )
	{
		$result = false;
		if( isset(  $this->code ) )
		{
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " CODE " . $this->code . " and msg " . $this->message, "NOTIFY");
			//echo "<br />" . __FILE__ . ":" . __LINE__ . " Server code " . $this->code . " with message " . $this->message . "<br />";
			//switch( $srvdata_object->code ){
			switch( $this->code ){
			//switch( $this->response->body->code ){
			case "woocommerce_rest_product_sku_already_exists":
				//WOO does NOT return the ID like for categories :(
				if ($this->get_product_by_sku( $this->sku ) )
				//$this->extract_data_obj( $srvdata_object );	//Is this needed vice get_by_sku...
					$this->update_wootable_woodata();	
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ , "NOTIFY");
				return FALSE;
			break;
			case "woocommerce_api_invalid_remote_product_image":
				//DOES WooCommerce send the ID back like it does categories?
				if( "create_product" == $caller )
				{
					$this->notify("<br />" . __METHOD__ . ":" . __LINE__ . "<Br />");

					if ($this->get_product_by_sku( $this->sku ) )
					{
						$this->update_wootable_woodata();	
						//$this->woo2wooproduct( $this->sku );
						$result = $this->update_product();
						$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ , "NOTIFY");
					}
					else
					{
						$this->id = -1;
						$this->match_product();
						$result = $this->update_wootable_woodata();
					}

				}
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN" );
				return $result;
			break;
			case "woocommerce_api_product_sku_already_exists":
				//DOES WooCommerce send the ID back like it does categories?
				if( "create_product" == $caller )
				{
					$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ , "NOTIFY");

					if ($this->get_product_by_sku( $this->sku ) )
					{
						$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ , "NOTIFY");
						$this->update_wootable_woodata();	
						//$this->woo2wooproduct( $this->sku );
						$result = $this->update_product();
						$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ , "NOTIFY");
					}
					else
					{
						$this->id = -1;
						$this->match_product();
						$result = $this->update_wootable_woodata();
					}

				}
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN" );
				return $result;
			break;
			case 400:	//"woocommerce_api_product_sku_already_exists"
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " UNHANDLED CODE " . $this->code . " and msg " . $this->message, "ERROR");
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN" );
				return FALSE;
			break;
			case "Invalid SKU":
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN" );
				return FALSE;
			break;
			case "woocommerce_api_no_route":
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__. "WARN no route for URL and Req Method in caller " . $caller . ".  Could be because of SSL Cert issues. wc_client: " . var_dump( $this->wc_client ) . "<br /><br />", "WARN" );
				return FALSE;
			case "woocommerce_api_authentication_error":
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " WARN Auth Error in caller " . $caller . ". Check SSL (https).  wc_client: " . var_dump( $this->wc_client ) . "<br /><br />", "WARN" );
				return FALSE;
			default:
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " UNHANDLED CODE " . $this->code . " and msg " . $this->message. "<br /><br />", "WARN");
				return FALSE;
			break;
			}
		}
	}	
	/*@null@*/function get_products()
	{
		$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN" );
		//Get the master list of categories as WOO knows it.
		$id = null;
		$args = array();	//array of fields.
		//Woo only sends 10 items back at a time :(/  per_page is supposed to change that...
		$args['posts_per_page'] = 500;
		$args['per_page'] = 500;
		$response = $this->wc_client->products->get( $id, $args );
		$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN" );
		if( $this->debug >= 2 )
		{
			print_r( $response );
		}
		$this->woo_products_list = $response->products;
		return;
		
	}
	/*@bool@*/function match_product()
	{
		$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN" );
		$count = count( $this->woo_products_list );
		if(  $count < 1 )
		{
			//This will return an array of ALL products.  
			$this->get_products();
		}
		else
		{
			$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN" );
		}
		//Need to cycle through them to find the one we are looking for :(
		//
		//	array of stdClass which we should be able to cast to woo_categories...
		foreach( $this->woo_products_list as $pobj )
		{
			if( $this->sku == $pobj->sku )
			{
				if( $this->sku != ' ' AND $this->sku != '')
				{
					$this->id = $pobj->id;
					return TRUE;
				}
				else
				{
					$this->notify( __METHOD__ . " Blank SKU: " . $this->sku . " Desc: " . $this->description, "ERROR" );
				}
			}
			else
			{
				//update SKUs with IDs?  Check if they are already set?
				$this->notify( __METHOD__ . ":" . __LINE__  , "NOTIFY" );
				$upd_obj = new woo_product( $this->serverURL, $this->woo_rest_path,
							$this->key, $this->secret, $this->enviro, $this->client);
				$upd_obj->sku = $pobj->sku;
				$upd_obj->id = $pobj->id;
				$upd_obj->update_wootable_woodata();
				if( $this->debug >= 2 )
				{
					var_dump( $pobj );
				}
			}
		}
		return FALSE;
	}

	/****************************************************************************//**
	 * This function should send a new product to WooCommerce, "creating" it.
	 *
	 * Assumption is that this object has all of the data set appropriately
	 * for us to conver the variables into an array, and pass it along.
	 *
	 * ******************************************************************************/
	/*@bool@*/function create_product()
	{
		if( $this->debug >= 1 )
			$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "NOTIFY" );
		//This should reset the time limit so every time we come in here
		//with a new product it should have 20 seconds to round-trip
		//there may be other time limits that come into play but at
		//least from the php interpreter this should reset for each
		//one
		set_time_limit( 60 );
	
		//try 
		//{
			if( isset( $this->wc_client ) )
			{
				try 
				{
					$this->wc_client->products->create( array( $this ) );
				} 
				catch ( WC_API_Client_Exception $e ) 
				{
					if( $this->debug > 1 )
					{
						echo $e->getMessage() . PHP_EOL. PHP_EOL;
						echo $e->getCode() . PHP_EOL. PHP_EOL;
					}
					if ( $e instanceof WC_API_Client_HTTP_Exception ) 
					{
						$rep_object = $e->get_response();
						$err = json_decode( $rep_object->body, FALSE );	//err is an object
						if( isset(  $err->errors[0]->code ) )
						{
							$this->code = $err->errors[0]->code;
							return $this->rest_error_handler( __FUNCTION__ );
						}
						if( $this->debug > 1 )
							$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN");
						return FALSE;
					}
					else 
						throw( $e );	//Pass the expection back along else it disappears
				}
			}
			else
			{
		//sets method, path, body and then calls do_request					(new woo_rest) except body
		//do_request calls make_api_call with path, endpoint, data				(new woo_rest) except data
		//make_api_call sets an array with method, url, data, user/pw, connection options	(new woo_rest) except data
		//and then calls http_request->dispatch
		//dispatch sets up the CURL options, curl_exec, grabs response				write2woo/this->curl_exec
		//takes apart response OR throws an exception
		//returns the response if no exception
				$this->build_data_array();
				//$data_r = $client->products->create( $this->data_array );
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Prod " . $this->stock_id, "WARN");
				//$data_r = $this->wc_client->products->create( (array) $this );
				$data_r = $this->wc_client->products->create( $this->data_array );
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . "VARDUMP");
				var_dump( $this->data_array );
				//$this->id = $data_r->id;
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN");
				$this->extract_data_obj( $data_r->product );
				$this->update_wootable_woodata();	
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN");
				return TRUE;
			}
		
			//send_simple fills in this objects variables from the database.
			$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "ERROR");
			//prep_json... converts object into data_array and then json_encodes it
			if( $this->prep_json_for_send( __method__ ) )
			{
				$this->new_woo_rest();
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN");
				if( $this->debug >= 2 )
				{
					var_dump( $this->json_data );
				}
				$this->woo_rest->write2woo_json( $this->json_data, "POST", $this );	//sets ->request, ->response, ->code, ->message
				if( $this->debug >= 1 )
				{
					echo "<br /><br />" . __METHOD__  . ":" . __LINE__ . " Request<br />";
					var_dump( $this->request );
					echo "<br /><br />" . __METHOD__  . ":" . __LINE__ . " Response<br />";
					var_dump( $this->response );
				}
				//$response_trimmed = substr( $response, strpos( $response, '{' ) );
				//$srvdata_object = json_decode( $response );
				//if( isset(  $srvdata_object->code ) )
				if( isset(  $this->code ) )
				{
					$this->rest_error_handler( __FUNCTION__ );
				}
				else 
				{
				}
				//We are no longer setting server_data_object so need to look into ->response
					echo "<br /><br />" . __METHOD__  . ":" . __LINE__ . " Response<br />";
					var_dump( $this->response );
				if( isset( $this->server_data_object->id ) )
				{
					//UPDATE 0_woo with the ID, created_at, updated_at
					$this->notify( __FILE__ . ":" . __LINE__ . " Product inserted " .  $srvdata_object->id, "NOTIFY" );
					$this->extract_data_obj( $this->server_data_object );
					$this->update_wootable_woodata();
					$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN");
				}
			}
		//}
		//catch ( Exception $e ) 
		//{
		//	throw( $e );
		//}
		return;
	}
	function retrieve_product()
	{
		$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN");
		/*
		curl https://example.com/wp-json/wc/v1/products/162 -u consumer_key:consumer_secret
		 * 
		 * */
	}
	/*@bool@*/function update_product()
	{
		$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Entering update_product", "WARN");
		/*
		curl -X PUT https://example.com/wp-json/wc/v1/products/162  -u consumer_key:consumer_secret  -H "Content-Type: application/json"  -d '{ "regular_price": "24.54" }'
		 * 
		 * */
		if( isset( $this->wc_client ) )
		{
			try 
			{
				if( isset( $this->id ) AND $this->id > 0 )
				{
					$this->notify( __METHOD__  . ":" . __LINE__ . " Calling ->update", "WARN" );
					$data_r = $this->wc_client->products->update( $this->id, array( $this ) );
					$this->extract_data_obj( $data_r->product );
					$this->update_wootable_woodata();	
				}
				else
				{
					throw new InvalidArgumentException( "Woo ID not set so can't update" );
				}
			} 
			catch ( WC_API_Client_Exception $e ) 
			{
				if( $this->debug > 1 )
				{
					echo $e->getMessage() . PHP_EOL. PHP_EOL;
					echo $e->getCode() . PHP_EOL. PHP_EOL;
				}
				if ( $e instanceof WC_API_Client_HTTP_Exception ) 
				{
					$rep_object = $e->get_response();
					//print_r( $rep_object );	//Object has code, body, headers
					$err = json_decode( $rep_object->body, FALSE );	//err is an object
					if( isset(  $err->errors[0]->code ) )
					{
						$this->code = $err->errors[0]->code;
						return $this->rest_error_handler( __FUNCTION__ );
					}
					return FALSE;
				}
				/*
				else 
					throw( $e );	//Pass the expection back along else it disappears
				 */
			}
			catch ( InvalidArgumentException $e )
			{
				$this->notify( __METHOD__  . ":" . __LINE__ . $e->getMessage(), "WARN" );
				$this->notify( __METHOD__  . ":" . __LINE__ . " Leaving update_product", "WARN" );
				return FALSE;

			}
			/*
			 * catch ( Exception $e )
			 * {
			 * 	$e->getMessage();
			 *	$e->getCode();
			 *	$e->getFile();
			 *	$e->getLine();
			 * }
			/*
			 * php5 Finally runs whether catches are run or not.  Usually used for cleanup and logging
			finally
			{
			}
			 */
		} //! Not wc_client
		else
		{
			if( $this->prep_json_for_send( __method__ ) )
			{
				$this->new_woo_rest();
				//NEED TO UPDATE SUBPATH
				$response = $this->woo_rest->write2woo_json( $this->json_data, "PUT", $this );
				if( isset(  $this->code ) )
				{
					return $this->rest_error_handler( __FUNCTION__ );
				}
				//NEED TO UPDATE WOOTABLE WITH TIMESTAMPS
				/*
				else if( isset( $this->server_data_object->id ) )
				{
					//UPDATE 0_woo with the ID, created_at, updated_at
					$this->notify( __FILE__ . ":" . __LINE__ . " Product inserted " .  $srvdata_object->id, "NOTIFY" );
					$this->extract_data_obj( $this->server_data_object );
					$this->update_wootable_woodata();
				}
				 */	
			}
		}
		return TRUE;
	}
	function recode_sku( $callback = null )
	{
		//skus with '/' in them will fail GET if not POST
		$this->sku = str_replace( '/', '_', $this->sku );
		if( null != $callback )
			$this->$callback();
		return;
	}
	function get_product_by_sku()
	{
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		//For now since the get by sku isn't working, return FALSE until we have time to troubleshoot.
		//return FALSE;
		//We MAY be able to use a "exclude" filter on list products to get the one we want...
		//
		//try {
			//products/sku/urlencode($sku)
			//if( !isset( $this->woo_rest ) )
			//	$this->new_woo_rest();
			//filter part of 2.3.9...
			/*
			$old_subpath = $this->woo_rest->subpath;
			//$this->woo_rest->data = array( "filter[sku]=" => $this->sku );
			$this->woo_rest->subpath = "/products";
			 */
			/*
			$old_woo_rest = $this->woo_rest->woo_rest_path;
			$this->woo_rest->woo_rest_path = "/wc-api/v2/";
			 */
			//$this->woo_rest->subpath = "products?filter[sku]=" . urlencode($this->sku);
			/*
			$this->woo_rest->buildURL(); 
			$this->woo_rest->request->params = null;
			$this->woo_rest->request->data = null;
			$this->woo_rest->request->body = null;
			$this->woo_rest->write2woo_json(  array( "filter[sku]=" => $this->sku ), "GET", $this );
			 */
			//$this->woo_rest->get( "/products/sku", array( "sku" => $this->sku ), $this );//no route
			//$this->woo_rest->get( "products", array( "category" => "9" ), $this );
			//$this->woo_rest->get( "products", array( "slug" => $this->sku ), $this );
			//$this->woo_rest->get( "products", array( "status" => "draft", "sku" => $this->sku, "search" => $this->sku ), $this );
			try {
			
				//$client = new WC_API_Client( 'https://fhsws001/devel/fhs/wordpress', 'ck_b23355fc0b9ee8b1ae073b64538ce4217f7530b3','cs_54b294848a424eff342ce5d7918dd17f122b0b56', $options );
				$data_r = $this->wc_client->products->get_by_sku( $this->sku );
			} catch ( WC_API_Client_Exception $e ) {

				echo $e->getMessage() . PHP_EOL;
				echo $e->getCode() . PHP_EOL;
			
				if ( $e instanceof WC_API_Client_HTTP_Exception ) {
			
					echo "<br />" . __METHOD__  . ":" . __LINE__ . " Request<br />";
					print_r( $e->get_request() );
					$this->request = $e->get_request();
					echo "<br />" . __METHOD__  . ":" . __LINE__ . " Response<br />";
					print_r( $e->get_response() );
					$this->response = $e->get_response();
				}
			}

			//$this->woo_rest->write2woo_json( $this->json_data, "GET", $this );
			if( $this->debug >= 1 )
			{
				display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
				echo "<br />" . __METHOD__  . ":" . __LINE__ . " Request<br />";
				var_dump( $this->request );
				echo "<br />" . __METHOD__  . ":" . __LINE__ . " Response<br />";
				var_dump( $this->response );
			}
			if( isset(  $this->code ) )
			{
				$this->rest_error_handler( __FUNCTION__ );
			}
			else 
			{
			}
			if( isset( $this->server_data_object->id ) )
			{
				if( $this->debug >= 1 )
				{
					display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
				}
				//UPDATE 0_woo with the ID, created_at, updated_at
				$this->notify( __FILE__ . ":" . __LINE__ . " Product inserted " .  $srvdata_object->id, "NOTIFY" );
				if( $this->debug >= 1 )
				{
					display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
				}
				$this->extract_data_obj( $this->server_data_object );
				if( $this->debug >= 1 )
				{
					display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
				}
				$this->update_wootable_woodata();
			}
			//$this->woo_rest->subpath = $old_subpath;
			//$this->woo_rest->woo_rest_path = $old_woo_rest;
			/*
			$response = $this->wc_client->products->get_by_sku( $this->sku );
			//print_r( $response );
			$this->extract_data_obj( $response->product );
			//var_dump( $this->id );
		} catch ( WC_API_Client_Exception $e ) {
			echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			echo $e->getMessage() . PHP_EOL;
			echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			$code = $e->getCode();
		        echo $code . PHP_EOL;
			if ( $e instanceof WC_API_Client_HTTP_Exception ) {
				switch( $code ) {
				case 404:
					echo "<br />" . __FILE__ . ":" . __LINE__ . "::get_product_by_sku:Invalid SKU: " . $this->sku . "<br />";
					$this->recode_sku( __FUNCTION__ );
					break;
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
			 */
		//Do we need to walk a linked list?
		//$this->ll_walk_update_fa();	//Does this exist?
	}
	function list_products()
	{
		/*
		 * 	GET
		 *	curl https://example.com/wp-json/wc/v1/products -u consumer_key:consumer_secret
		*/
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
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Entering woo2wooproduct with stock_id " . $stock_id . " from caller " . $caller);
		}
		if( isset( $stock_id )  )
			$this->stock_id = $stock_id;
		if( !isset( $this->stock_id ) )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Stock_id not set. Leaving woo2wooproduct");
			return FALSE;
		}
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " product selected for sku " . $this->stock_id);
		}
			
		$remove_desc_array = array(  "**Use Custom Form**,", "**Use Custom Order Form**,", );
		$removed_desc_array = array( "",                     "",                           );
if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " product selected for sku " . $this->stock_id);
		}
		
		require_once( 'class.woo.php' );
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " product selected for sku " . $this->stock_id);
		}
		$woo = new woo( $this->serverURL, $this->key, $this->secret, $this->options, $this );
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " product selected for sku " . $this->stock_id);
		}
		$woo->stock_id = $this->stock_id;
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " product selected for sku " . $this->stock_id);
		}
		$woo->select_product();
		//Need to reset values between each product.
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " product selected for sku " . $this->stock_id);
		}
		$this->reset_values();
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " product selected for sku " . $this->stock_id);
		}
		foreach( $woo->fields_array as $fieldrow )
		{
			//Set OUR value == to woo's
			if( isset( $woo->$fieldrow['name'] ) )
			{
				if( $this->debug > 1 )
					display_notification( __FILE__ . ":" .  __METHOD__ . ":" . __LINE__ . " Setting " . $fieldrow['name'] . " to " . $woo->$fieldrow['name'] );
				$this->$fieldrow['name'] = utf8_encode( $woo->$fieldrow['name'] );
			}
		}
		//$this->name = utf8_encode( str_replace( $remove_desc_array, $removed_desc_array, $this->name ) );
//		$this->name = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $prod_data['description'] ) );
		$this->slug = $this->stock_id;
		$this->title = utf8_encode( str_replace( $remove_desc_array, $removed_desc_array, $this->description ) );
		//$this->title = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $prod_data['description'] ) );
		$this->permalink = null;
		$this->type = "simple";	//OVERRIDE in calling routine as appropriate.
		$this->status = "publish";
		$this->featured = false;
		$this->catalog_visibility = "visible";
		$this->short_description = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $this->description ) );
		$this->description = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $this->long_description ) );
		//$this->description = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $prod_data['long_description'] ) );
		//$this->short_description = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $prod_data['description'] ) );
		$this->sale_price = null;
		$this->date_on_sale_from = null;
		$this->date_on_sale_to = null;
		$this->price_html = null;
		//$this->id = $prod_data['woo_id'];
		//$this->sku = $prod_data['stock_id'];
		//$this->regular_price = $prod_data['price'];

		//var_dump( $prod_data );
		$this->virtual = false;
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

		$this->manage_stock = true;
		$this->backorders = "notify";
		$this->sold_individually = false;	//true only allows 1 of this product per order
		/* made obselete above by foreach woo...
		$var_array = array( 'sale_price', 
					'date_on_sale_from', 
					'date_on_sale_to', 
					'external_url', 
					'button_text',
					'tax_status', 
					'tax_class', 
					'shipping_class',
					'upsell_ids',
					'cross_sell_ids',
					'weight'
			);
		foreach($var_array as $var )
		{
			if( isset( $prod_data[$var] ) && strlen( $prod_data[$var] ) > 1 )
			{
				$this->$var = utf8_encode( $prod_data[$var] );
			}
		}

		$dim_var_array = array( 'width', 
					'length', 
					'height'
			);
		foreach($dim_var_array as $var )
		{
			if( isset( $prod_data[$var] ) && strlen( $prod_data[$var] ) > 1 )
			{
				$this->dimensions[$var] = utf8_encode( $prod_data[$var] );
			}
		}
		 !obsolete */
		$this->in_stock = true;

		$this->reviews_allowed = "1";
		$this->parent_id = null;
		$this->purchase_note = null;

		$this->categories = array( "id" => $this->woo_category_id );	//Should also have name and slug?
		//$this->categories = array( "id" => $prod_data['woo_category_id'] );	//Should also have name and slug?
											//Can we add extra categories in FA through foreign_codes?
		//Here we could look at foreign codes for additional categories.

		//$this->tags = array( "id" => XXX, "name" => YYY, "slug" => ZZZ );
		$this->tags = $this->product_tags( $stock_id );
		$this->images = $this->product_images( $stock_id );
		$this->attributes = $this->product_attributes( $stock_id );
		$this->default_attributes = $this->product_default_attributes( $stock_id );
		$this->variations = $this->product_variations( $stock_id );
		$this->menu_order = "1";
		//$this->attributes = array("");
		//	id, name, position, visible(bool), variation(bool), options
		//$this->default_attributes = "";
		if( isset( $this->instock ) )
			$this->stock_quantity = $this->instock;
		else
			$this->stock_quantity = 0;
		if( isset( $this->woo_id ) )
			$this->id = $this->woo_id;
		if( isset( $this->stock_id ) )
			$this->sku = $this->stock_id;
		if( isset( $this->price ) )
			$this->regular_price = $this->price;
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Leaving woo2wooproduct");
		}

		return TRUE;
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
	 * 	@return int count of products sent
	 **************************************************************************************************************/
	/*@int@*/function send_simple_products()
	{		
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Entering send_simple_products");
		}
		//Check for how many we are expecting to send...
		require_once( 'class.woo.php' );
		$woo = new woo( $this->serverURL, $this->key, $this->secret, $this->options, $this);
		$woo->debug = $this->debug;
		$woo->filter_new_only = TRUE;
		if( $this->debug >= 1 )
		{
			$tosend = $woo->count_new_products();
			display_notification( __METHOD__  . ":" . __LINE__ . " About to send " . $tosend . " rows (all, not simple) to Woo");
		}
		$res = $woo->new_simple_product_ids();
		$sendcount = 0;
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		foreach( $res as $stock_id )
		{
			if( $this->debug > 1 AND $sendcount > 0 )
			{
				if( $this->debug >= 1 )
				{
					display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Leaving send_simple_products");
				}
				return $sendcount;	//Only action 1 item
			}
			if( $this->debug >= 1 )
			{
				display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Calling woo2wooproduct");
			}
			$this->woo2wooproduct( $stock_id);	//Sets the object variables with data from query
			$this->type = "simple";
			if( $this->create_product() )
				$sendcount++;
		}
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Leaving send_simple_products");
		}
		return $sendcount;
	}
	function update_simple_products()
	{			
		$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Entering update_simple_products", "WARN");

		require_once( 'class.woo.php' );
		$woo = new woo( $this->serverURL, $this->key, $this->secret, $this->options, $this);
		$woo->debug = $this->debug;
		$updatecount = 0;
		$res = $woo->select_simple_products_for_update();

		while( $prod_data = db_fetch_assoc( $res ) )
		{
			$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " WHILE LOOP update_simple_products", "WARN");
			if( $this->debug > 1 AND $updatecount > 0 )
			{
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Leaving update_simple_products", "WARN");
				return $updatecount;	//Only action 1 item
			}
			$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Calling woo2wooproduct", "WARN");
			$this->woo2wooproduct( $prod_data['stock_id'], __FUNCTION__);
			$this->type = "simple";
			if( isset( $this->id ) AND $this->id > 0 )
			{
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Calling update PRODUCT", "WARN");
			//try
			//{
				if( $this->update_product() )
					$updatecount++;
				else
					display_notification( __METHOD__  . ":" . __LINE__ . " Product not updated.  DEBUG level: " . $this->debug );
			}
			else
			{
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Calling create PRODUCT", "WARN");
				if( $this->create_product() )
					$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Insert Successful", "WARN");
				else
					$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Insert Failed", "WARN");
			}
			//}
			//catch( Exception $e )
			//{
			//	display_notification( __METHOD__  . ":" . __LINE__ . "caught error code " . $e->getCode() . " and msg " . $e->getMessage() . " from " . $e->getFile() . ":" . $e->getLine() );
			//}
			$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " End WHILE LOOP lap", "WARN");
		}
		$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Leaving update_simple_products", "WARN");
		return $updatecount;
	}
	/*@string@*/function image_exists( $stock_id )
	{
		$filename = company_path().'/images/' . item_img_name($stock_id) . ".jpg";
		if( file_exists( $filename ) === TRUE )
			return item_img_name($stock_id) . ".jpg"; 
		else
			return NULL;
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
	function product_attributes( $stock_id )
	{
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
	function product_default_attributes( $stock_id )
	{
		require_once( 'class.woo_prod_variation_attributes.php' );
		$w_attr = new woo_prod_variation_attributes( $this->serverURL, $this->key, $this->secret, $stock_id, $this->client );
		$arr = $w_attr->get_by_sku( $stock_id );
		$retarr = array();
		foreach( $arr as $sku => $val )
		{
			$retarr[] = $val;
		}
		return $retarr;
	}
	function product_variations( $stock_id )
	{
		return null;
		/*
		require_once( 'class.woo_variations.php' );
		$w_imgs = new woo_variations( null, null, null, $stock_id, $this->client );
		return $w_imgs->run();
		*/
	}
	function product_images( $stock_id )
	{
		require_once( 'class.woo_images.php' );
		//standard constructor args...	$this->serverURL, $this->key, $this->secret, $this->options, $this

		$w_imgs = new woo_images( $stock_id, $this->client );
		return $w_imgs->run();
	}

	function product_images_old( $stock_id )
	{
		//IMAGES
		//If we use local URL we need to build it and send it
		//If we need to use WOOCOMMERCE image gallery, we need the filename
		//With the module to allow extra images, we need to check for that too
		////SHould also check for the existance of the filename in the local company
		//	Default location is (/company/0/images
		$image_array = array();
		$imagecount = 0;
		//if( isset( $this->image_serverurl ) AND isset( $this->image_baseurl ) AND $this->use_img_baseurl == "true" )
		if( isset( $this->client->image_serverurl ) AND isset( $this->client->image_baseurl ) )
		{
			//Assumption running on same machine for image check
			$image_name = $this->image_exists( $stock_id );
			if( null != $image_name )
				/*
			$filename = company_path().'/images/' . item_img_name($stock_id) . ".jpg";
			if( file_exists( $filename ) === TRUE )
				 */
			{
				$image_array[$imagecount]['src']  = $this->client->image_serverurl . '/' . $this->client->image_baseurl . '/' . $image_name;
				$image_array[$imagecount]['position'] = $imagecount;
				$imagecount++;
			}
			if( isset( $this->client->maxpics ) )
			{
				for ( $j = 1; $j <= $this->client->maxpics; $j++ )
				{
					$image_name = $this->image_exists( $stock_id . $j );
					if( null != $image_name )
					/*
					$filename = item_img_name($stock_id) . $j . ".jpg";
					$fullfilename = company_path().'/images/' . $filename;
					if( file_exists( $fullfilename ) === TRUE )
					 */
					{
						$image_array[$imagecount]['src']  = $this->client->image_serverurl . '/'  . $this->client->image_baseurl . '/' . $image_name;
						$image_array[$imagecount]['position'] = $imagecount;
						$imagecount++;
					}
				}
			}
		}
		else
		{
			$image_array[$imagecount]['src'] = $stock_id . '.jpg"';
			$image_array[$imagecount]['position'] = $imagecount;
			$imagecount++;
		}
		return $image_array;
	}
}

?>
