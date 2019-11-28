<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array appropriately
 * */

/*******************************************************************************************//**
 *
 * TODO:
 *	Add variable product sending
 *	FIx Get By SKU so that we can match up product numbers for existing products
 *		so that we can send updates for attributes
 *	Migrate to own REST library once it is rewritten
 *	Convert to use a table definition.  This could define table _woo
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
	
	function __construct( $serverURL, $woo_rest_path,
				$key, $secret, $enviro = "devel", $client = null )
	{
		$this->serverURL = $serverURL;
		$this->enviro = $enviro;
		$this->subpath = "products";
		$this->conn_type = "POST" ;
		$this->woo_rest_path_base = $woo_rest_path;
		$this->woo_rest_path = $woo_rest_path;
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
	function update_wootable_woodata()
	{
		if( ! isset( $this->updated_at ) )
		{
			$this->updated_at = date("Y-m-d");
		}
		$updateprod_sql = "update " . TB_PREF . "woo set
					woo_last_update = '" . $this->updated_at . "',
					woo_id = '" . $this->id . "',
					date_on_sale_from = '" . $this->date_on_sale_from . "',
					date_on_sale_to = '" . $this->date_on_sale_to . "',
					tax_status = '" . $this->tax_status . "'";
			$updateprod_sql .= " where stock_id = '" . $this->sku . "'";
		$res = db_query( $updateprod_sql, "Couldn't update product after export" );
		display_notification( "Updated wootable sku " . $this->sku . " with dates and id_" . $this->id . "_" );
	}
	function delete_by_sku( $sku )
	{
		$sql = "delete FROM `" . TB_PREF . "woo` where stock_id = '" . $sku . "'";
		$res = db_query( $sql, "Couldn't delete sku" . $sku );
		$this->notify( "Deleted sku " . $sku, "NOTIFY" );
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
			default:
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " UNHANDLED CODE " . $this->code . " and msg " . $this->message);
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

	/*@bool@*/function create_product()
	{
		$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "NOTIFY" );
	
		//sets method, path, body and then calls do_request					(new woo_rest) except body
		//do_request calls make_api_call with path, endpoint, data				(new woo_rest) except data
		//make_api_call sets an array with method, url, data, user/pw, connection options	(new woo_rest) except data
		//and then calls http_request->dispatch
		//dispatch sets up the CURL options, curl_exec, grabs response				write2woo/this->curl_exec
		//takes apart response OR throws an exception
		//returns the response if no exception
/*	$client vice wc_client
		$options = array(
				'debug'           => true,
				'return_as_array' => false,
				'validate_url'    => false,
				'timeout'         => 30,
				'ssl_verify'      => false,
			);
 */
			//This should reset the time limit so every time we come in here
			//with a new product it should have 20 seconds to round-trip
			//there may be other time limits that come into play but at
			//least from the php interpreter this should reset for each
			//one
			set_time_limit( 60 );
			
			try {
			
				//$client = new WC_API_Client( 'https://fhsws001/devel/fhs/wordpress', 'ck_b23355fc0b9ee8b1ae073b64538ce4217f7530b3','cs_54b294848a424eff342ce5d7918dd17f122b0b56', $options );
				$this->build_data_array();
				//$data_r = $client->products->create( $this->data_array );
				$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ . " Prod " . $this->sku, "WARN");
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
			} catch ( WC_API_Client_Exception $e ) {

				//echo $e->getMessage() . PHP_EOL;
				//echo $e->getCode() . PHP_EOL;
			
				if ( $e instanceof WC_API_Client_HTTP_Exception ) {
					//print_r( $e->get_request() );
					//echo "<br />" . __LINE__ . "<Br />";
					//print_r( $e->get_response() );
					//
					$rep_object = $e->get_response();
					//echo "<br />" . __LINE__ . "<Br />";
					//print_r( $rep_object );	//Object has code, body, headers
					$err = json_decode( $rep_object->body, FALSE );	//err is an object
					//echo "<br />" . __LINE__ . "<Br />";
					//print_r( $err );
					//echo "<br />" . __LINE__ . "<Br />";
					if( isset(  $err->errors[0]->code ) )
					{
						//echo "<br />" . __LINE__ . "<Br />";
						$this->code = $err->errors[0]->code;
						return $this->rest_error_handler( __FUNCTION__ );
					}
					$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN");
					return FALSE;
				}
			}
			$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN");
			return FALSE;
		
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
		$this->notify( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__, "WARN");
		/*
		curl -X PUT https://example.com/wp-json/wc/v1/products/162  -u consumer_key:consumer_secret  -H "Content-Type: application/json"  -d '{ "regular_price": "24.54" }'
		 * 
		 * */
					try {
			
				//$client = new WC_API_Client( 'https://fhsws001/devel/fhs/wordpress', 'ck_b23355fc0b9ee8b1ae073b64538ce4217f7530b3','cs_54b294848a424eff342ce5d7918dd17f122b0b56', $options );
				$this->title = $this->name;
				$this->build_data_array();
				//$data_r = $client->products->create( $this->data_array );
				$data_r = $this->wc_client->products->update( $this->id, $this->data_array );
				//var_dump( $data_r );
				//$this->id = $data_r->id;
				$this->extract_data_obj( $data_r->product );
				$this->update_wootable_woodata();	
			} catch ( WC_API_Client_Exception $e ) {

				echo $e->getMessage() . PHP_EOL;
				//echo $e->getCode() . PHP_EOL;
			
				if ( $e instanceof WC_API_Client_HTTP_Exception ) {
/*
					//echo "<br />";
					//print_r( $e->get_request() );
					echo "<br />";
					echo "<br />";
					print_r( json_decode( $e->get_response()->body ) );
					echo "<br />";

					//echo "<br />";
					//print_r( $e->get_response() );
 */
					//
					/* once we know what to send for this->code from get_request..*/
					$rep_object = $e->get_response();
					$err = json_decode( $rep_object->body, FALSE );	//err is an object
					echo "<br />" . __LINE__ . "<Br />";
					print_r( $err );
					echo "<br />" . __LINE__ . "<Br />";
					print_r(  $err->errors[0]->code );
					if( isset(  $err->errors[0]->code ) )
					{
						$this->code = $err->errors[0]->code;
						$this->rest_error_handler( __FUNCTION__ );
					}
					 
				}
			}
			return;
	
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
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
		return;
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
	function woo2wooproduct( $stock_id )
	{
		$remove_desc_array = array(  "**Use Custom Form**,", "**Use Custom Order Form**,", );
		$removed_desc_array = array( "",                     "",                           );
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		$prod_sql = 	"select stock_id, woo_category_id, description, long_description, price, instock, 
				sale_price, date_on_sale_from, date_on_sale_to, external_url, tax_status, tax_class, 
				weight, length, width, height, shipping_class, upsell_ids, crosssell_ids, parent_id, 
				attributes, default_attributes, variations
				from " . TB_PREF . "woo";
		$prod_sql .= " where stock_id = '" . $stock_id . "'";
		$res = db_query( $prod_sql, __LINE__ . " Couldn't select product(s) for export" );
		//var_dump( $res );
		require_once( 'class.woo_product.php' );
		while( $prod_data = db_fetch_assoc( $res ) )
		{
			//Need to reset values between each product.
			$this->reset_values();
			if( $this->debug >= 1 )
			{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
			}
			$this->name = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $prod_data['description'] ) );
			$this->slug = $prod_data['stock_id'];
			$this->title = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $prod_data['description'] ) );
			$this->permalink = null;
			$this->type = "simple";	//OVERRIDE in calling routine as appropriate.
			$this->status = "publish";
			$this->featured = false;
			$this->catalog_visibility = "visible";
			$this->description = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $prod_data['long_description'] ) );
			$this->short_description = utf8_encode( str_replace( $remove_desc_array , $removed_desc_array , $prod_data['description'] ) );
			$this->sku = $prod_data['stock_id'];
			$this->regular_price = $prod_data['price'];
			$this->sale_price = null;
			$this->date_on_sale_from = null;
			$this->date_on_sale_to = null;
			$this->price_html = null;

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
			$this->in_stock = true;

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

			$this->reviews_allowed = "1";
			$this->parent_id = null;
			$this->purchase_note = null;

			$this->categories = array( "id" => $prod_data['woo_category_id'] );	//Should also have name and slug?
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
			$this->stock_quantity =$prod_data['instock'];
		}
		if( $this->debug >= 1 )
		{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}

		return;
	}
	/*
	 * This is the version that came out of woo_export
	function woo2wooproduct( $stock_id )
	{
		$prod_sql = 	"select stock_id, woo_category_id, description, long_description, price, instock, 
				sale_price, date_on_sale_from, date_on_sale_to, external_url, tax_status, tax_class, 
				weight, length, width, height, shipping_class, upsell_ids, crosssell_ids, parent_id, 
				attributes, default_attributes, variations
				from " . TB_PREF . "woo";
		$prod_sql .= " where stock_id = '" . $stock_id . "'";
		$res = db_query( $prod_sql, __LINE__ . " Couldn't select product(s) for export" );
		//var_dump( $res );
		require_once( 'class.woo_product.php' );
		while( $prod_data = db_fetch_assoc( $res ) )
		{
			//var_dump( $prod_data );
			$woo_product = new woo_product( $this->woo_server, $this->woo_rest_path, $this->woo_ck, $this->woo_cs );
			$woo_product->status = "publish";
			//$woo_product->featured = false;
			//$woo_product->virtual = false;
			//$woo_product->downloadable = false;
			$woo_product->manage_stock = true;
			$woo_product->catalog_visibility = "visible";
			$woo_product->backorders = "yes";
			$woo_product->sold_individually = false;
			$woo_product->reviews_allowed = "1";
			$woo_product->menu_order = "1";
			$var_array = array( 'sale_price', 
						'date_on_sale_from', 
						'date_on_sale_to', 
						'external_url', 
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
					$woo_product->$var = utf8_encode( $prod_data[$var] );
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
					$woo_product->dimensions[$var] = utf8_encode( $prod_data[$var] );
				}
			}
			$woo_product->categories = array();
			$woo_product->categories[] = array( "id" => $prod_data['woo_category_id'] );
			//$woo_product->tags = array("");
			//$woo_product->attributes = array("");
			//	id, name, position, visible(bool), variation(bool), options
			//$woo_product->default_attributes = "";
			$woo_product->sku = $prod_data['stock_id'];
			$woo_product->slug = $prod_data['stock_id'];
			$woo_product->name = utf8_encode( $prod_data['description'] );
			$woo_product->description = utf8_encode( $prod_data['long_description'] );
			$woo_product->short_description = utf8_encode( $prod_data['description'] );
			$woo_product->regular_price = $prod_data['price'];
			$woo_product->stock_quantity =$prod_data['instock'];
			$woo_product->in_stock = true;
			$woo_product->images = $this->product_images( $stock_id );
		}
		return $woo_product;
	}
	 */
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
	 **************************************************************************************************************/
	function send_simple_products()
	{		
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
$prod_sql = 	"select stock_id 
				from " . TB_PREF . "woo";
		$prod_sql .= " WHERE woo_id = ''";	//Otherwise need to do an UPDATE not CREATE
		//This will ensure we send only items that haven't already been inserted.
		$prod_sql .= " AND stock_id not in (SELECT sm.stock_id FROM " . TB_PREF . "stock_master sm 
			INNER JOIN (SELECT stock_id FROM " . TB_PREF . "woo_prod_variable_master GROUP BY stock_id) vm
			ON sm.stock_id LIKE  concat( vm.stock_id, '%') )";
		if( $this->debug == 1 )
		{
			$prod_sql .= " LIMIT 10";
			//$prod_sql .= "ORDER BY RAND() LIMIT 10";
		}
		else
		if( $this->debug >= 2)
		{
			$prod_sql .= "ORDER BY RAND() LIMIT 1";
		}
		//$prod_sql .= " ORDER BY RAND() LIMIT 5";
		
		$res = db_query( $prod_sql, __LINE__ . "Couldn't select product(s) for export" );
		$sendcount = 0;

		while( $prod_data = db_fetch_assoc( $res ) )
		{
			if( $this->debug >= 1 )
			{
				display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
			}
			$this->woo2wooproduct( $prod_data['stock_id']);	//Sets the object variables with data from query
			$this->type = "simple";
			if( $this->debug >= 1 )
			{
				display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
			}
			if( $this->create_product() )
				$sendcount++;
		}
		return $sendcount;
	}
	function update_simple_products()
	{		
		$prod_sql = 	"select stock_id 
				from " . TB_PREF . "woo";
		//$prod_sql .= " WHERE woo_id = ''";	//Otherwise need to do an UPDATE not CREATE
		$prod_sql .= " WHERE updated_ts > woo_last_update";	//need to do an UPDATE because we changed something that hasn't been sent

		//This will ensure we send only items that haven't already been inserted.
		$prod_sql .= " AND stock_id not in (SELECT sm.stock_id FROM " . TB_PREF . "stock_master sm 
			INNER JOIN (SELECT stock_id FROM " . TB_PREF . "woo_prod_variable_master GROUP BY stock_id) vm
			ON sm.stock_id LIKE  concat( vm.stock_id, '%') )";
		//$prod_sql .= " LIMIT 1";
		//$prod_sql .= " ORDER BY RAND() LIMIT 5";
		
		$res = db_query( $prod_sql, __LINE__ . "Couldn't select product(s) for export" );
		$sendcount = 0;

		while( $prod_data = db_fetch_assoc( $res ) )
		{
			$this->woo2wooproduct( $prod_data['stock_id']);
			$this->type = "simple";
			$this->update_product();
			$sendcount++;
		}
		return $sendcount;
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
		$w_attr = new woo_prod_variation_attributes( null, null, null, $stock_id, $this->client );
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
		$w_attr = new woo_prod_variation_attributes( null, null, null, $stock_id, $this->client );
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
