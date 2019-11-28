<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

/*******************************************************************************//**
 *
 *	Sends a category to WooCommerce and updates xref table
 *	If gets an error, then requests the data for the category (so can xref)
 *
 *TODO:
 *	Get the master list of categories and then insert into FA any that 
 *		don't exist (impy also xref)
 *	Delete any categories that we don't have in FA (cleanup function - side effects?)
 *
 * **********************************************************************************/

require_once( 'class.woo_rest.php' );
require_once( 'class.woo_interface.php' );

class woo_category extends woo_interface{
	var $id;		//	integer 	Unique identifier for the resource.
	var $name;		//	string 	Category name.  required
	var $slug;		//	string 	An alphanumeric identifier for the resource unique to its type.
	var $parent;		//	integer 	The id for the parent of the resource.
	var $description;		//	string 	HTML description of the resource.
	var $display;		//	string 	Category archive display type. Default is default. Options: default, categorys, subcategories and both
	var $image;		//	array 	Image data. See Category Image properties
	var $menu_order;		//	integer 	Menu order, used to custom sort the resource.
	var $count;		// 	integer 	Number of published categorys for the resource.   RO
	var $fa_id;

	var $woo_rest;
	var $header_array;
	function __construct( $serverURL, $woo_rest_path, $key, $secret,  $options, $client, $enviro = "devel" )
	{
		parent::__construct($serverURL, $key, $secret, $options, $client);
		$subpath = "products/categories";
		$data_array = array();
		$conn_type = "POST" ;
		$header_array = array();
		$header_array['Content-Type'] = "application/json";
		//$this->woo_rest = new woo_rest( $serverURL, $subpath, $data_array, $key, $secret, $conn_type, $woo_rest_path, $header_array, $enviro );
		return;
	}
		function define_table()
	{
		$this->fields_array[] = array('name' => 'woo_category_id', 	'type' => 'int(11)', 		'comment' => 'Index.', 'readwrite' => 'read', 'auto_increment' => 'anything');
		$this->fields_array[] = array('name' => 'updated_ts', 		'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read');

		$this->fields_array[] = array('name' => 'id', 			'type' => 'int(11)', 		'comment' => ' 	Item ID', 'readwrite' => 'read');
		$this->fields_array[] = array('name' => 'name', 		'type' => 'varchar(64)', 	'comment' => ' 	Category Name.', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'slug', 		'type' => 'varchar(64)', 	'comment' => ' 	Category Slug.', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'parent', 		'type' => 'int(11)', 		'comment' => ' 	Parent Category ID.', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'description', 		'type' => 'varchar(64)', 	'comment' => 'Category Description.', 'readwrite' => 'readwrite'); 	
		$this->fields_array[] = array('name' => 'display', 		'type' => 'varchar(64)', 	'comment' => 'Display Type.  default/caregories/subcategories/both.', 'readwrite' => 'readwrite'); 	
		//$this->fields_array[] = array('name' => 'image', 		'type' => 'int(11)', 		'comment' => ' 	Parent Category ID.', 'readwrite' => 'readwrite');.
		$this->fields_array[] = array('name' => 'menu_order', 		'type' => 'int(11)', 		'comment' => ' Menu Order.', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'count', 		'type' => 'int(11)', 		'comment' => ' number of published categories', 'readwrite' => 'read');
		$this->fields_array[] = array('name' => 'fa_id',		'type' => 'int(11)', 		'comment' => ' 	FA Category ID', 'readwrite' => 'read');

		$this->table_details['tablename'] = $this->company_prefix . "woo_category";
		$this->table_details['primarykey'] = "woo_category_id";
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "namee";
		$this->table_details['index'][0]['keyname'] = "u-name";
		$this->table_details['index'][1]['type'] = 'unique';
		$this->table_details['index'][1]['columns'] = "slug";
		$this->table_details['index'][1]['keyname'] = "u-slug";
	}
function build_properties_array()
	{
		/*All properties*/
		$this->properties_array = array(
			'id',
			'name',
			'slug',
			'description',
			'parent',
			'image',
			'menu_order',
			'count'
		);
	}
	function build_write_properties_array()
	{
		/*Took the list of properties, and removed the RO ones*/
		$this->write_properties_array = array(
			'name',
			'slug',
			'description',
			'parent',
			'image',
			'menu_order',
		);
	}
	/*@array of objects@*/function get_categories()
	{
		if( $this->debug >= 1 )
		{
			echo "<br />" . __METHOD__ . ":" . __LINE__ . "<br />";
		}
		//Get the master list of categories as WOO knows it.
		$id = null;
		$args = null;	//array of fields.
		$response = $this->wc_client->products->get_categories( $id, $args );
		if( $this->debug >= 2 )
		{
			echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			print_r( $response );
		}
		return $response->product_categories;
		
	}
	/*@bool@*/function match_category()
	{
		if( $this->debug >= 1 )
		{
			echo "<br />" . __METHOD__ . ":" . __LINE__ . "<br />";
		}
		//This will return an array of ALL categories.  
		$cats = $this->get_categories();
		//Need to cycle through them to find the one we are looking for :(
		//
		//	array of stdClass which we should be able to cast to woo_categories...
		foreach( $cats as $cobj )
		{
			if( $this->name == $cobj->name )
			{
				$this->id = $cobj->id;
				return TRUE;
			}
			else
			{
			}
		}
		return FALSE;
	}
	function get_category()
	{
		if( $this->debug >= 1 )
		{
			echo "<br />" . __METHOD__ . ":" . __LINE__ . "<br />";
		}
		try {
			if( $this->id > 0 )
				$id = $this->id;
			else
				$id = null;
			$args = null;	//array of fields.
				$response = $this->wc_client->products->get_categories( $id, $args );
				echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
				print_r( $response );
				//This will return an array of ALL categories.  
				//Need to cycle through them to find the one we are looking for :(
				//
				//	Object->product_categories array of stdClass which we should be able to cast to woo_categories...
				foreach( $response->product_categories as $cobj )
				{
					if( $this->name == $cobj->name )
					{
						$this->id = $cobj->id;
						return TRUE;
					}
					else
					{
					}

				}
				//
				//$this->extract_data_obj( $response->product );
			//var_dump( $this->id );
		} catch ( WC_API_Client_Exception $e ) {
			/*
			echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			 */
			//echo $e->getMessage() . PHP_EOL;
			$msg = $e->getMessage();
			echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			//$code = $e->getCode();
			var_dump( $e->get_request() );
			echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			var_dump( $e->get_response() );
		        //echo $code . PHP_EOL;
			if ( $e instanceof WC_API_Client_HTTP_Exception ) {
				switch( $code ) {
				default:
					/*
					echo "<br />" . __FILE__ . ":" . __LINE__ . ":Unhandled Error Code: " . $code . "<br />";
					var_dump( $this );
					 */
					break;
				}
				/*
				echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
				print_r( $e->get_request() );
				echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
				print_r( $e->get_response() );
				*/
			}
		}
		$this->ll_walk_update_fa();
	}
	function update_woo_categories_xref()
	{
		if( $this->debug >= 1 )
		{
			echo "<br />" . __METHOD__ . ":" . __LINE__ . "<br />";
		}
		require_once( 'class.woo_categories_xref.php' );
		$xref = new woo_categories_xref( null, null, null, null, $this );
		$xref->fa_cat = $this->fa_id;
		$xref->woo_cat = $this->id;
		$xref->description = $this->description;
		$xref->update();
/*Moved into ..xref..
		$updateprod_sql = "insert ignore into " . TB_PREF . "woo_categories_xref ( fa_cat, woo_cat, description ) values ('" . $this->fa_id . "', '" . $this->id . "', '" . $this->description . "')";
		//$updateprod_sql = "insert ignore into " . $this->company_prefix . "woo_categories_xref( 'fa_cat', 'woo_cat', 'description' ) values ('" . $this->fa_id . "', '" . $this->id . "', '" . $this->description . "')";
		$res = db_query( $updateprod_sql, "Couldn't update woo_categories_xref" );
		$this->notify( "Updated woo_categories_xref with values " . $this->fa_id . ", " . $this->id . ", " . $this->description   );
 */
	}
	function create_category()
	{
		if( $this->debug >= 1 )
		{
			echo "<br />" . __METHOD__ . ":" . __LINE__ . "<br />";
		}
		$extractcount = 0;
		if( $this->debug >= 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ );
		}

		/*
		curl -X POST http://fhsws001.ksfraser.com/wp-json/wc/v1/products/categories -u consumer_key:consumer_secret -H "Content-Type: application/json" -d '{
			"name": "Clothing",
			  "image": {
			    "src": "http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg"
			  }
		}'
		 */

		/*************************************************************************************************************************************************/
		try {
			//$response = $this->wc_client->products->get_categories(  $this->build_data_array() );
			$this->build_data_array();
			//	echo __FILE__ . ":" . __LINE__ . "<br />";
			//var_dump( $this->data_array );
			//	echo __FILE__ . ":" . __LINE__ . "<br />";
			$response = $this->wc_client->products->create_categories(  $this->data_array );
			if( $this->debug >= 1 )
			{
				echo __FILE__ . ":" . __LINE__ . "<br />";
				var_dump( $response );
			}
			$this->id = $response->product_category->id;
			$this->update_woo_categories_xref();
			//There should be a response, we need to update our xref table so we know what the cat WOO ID is
			//$this->extract_data_obj( $response->product );
			//$this->update_wootable_woodata();	
			return TRUE;	//Should we return false until the category is updated?

		} catch ( WC_API_Client_Exception $e ) {
			if( $this->debug >= 2 )
			{
				echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
				var_dump( $this->data_array );
				$code = $e->getCode();
				echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
				var_dump( $code );
				$msg = $e->getMessage();
				echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
				var_dump( $msg );
			}
			if ( $e instanceof WC_API_Client_HTTP_Exception ) 
			{
				$response_obj = $e->get_response();
				$err = json_decode( $response_obj->body, FALSE );
				if( isset( $err->errors[0]->code ) )
				{
					$this->code = $err->errors[0]->code;
					$this->msg = $err->errors[0]->message;
				}
				//Moved into fcn rest_error_handler in other classes...
				switch( $this->code )
				{
					case "400":
					case "woocommerce_api_missing_callback_param":
					case "woocommerce_api_missing_product_category_data":
						echo "<br />" . __FILE__ . ":" . __LINE__ . "Code " . $this->code . " with message " . $this->msg . "<br />";
						//var_dump( $this->wc_client->products );
						//echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
						var_dump( $e->get_request() );
					break;
					case "404":
					case "woocommerce_api_no_route":
						echo "<br />" . __FILE__ . ":" . __LINE__ . "No Route (API) " . $this->code . " with message " . $this->msg . "<br />";
						var_dump( $this->wc_client->products );
					break;
					case "woocommerce_rest_category_sku_already_exists":
						/*
						echo "<br />" . __FILE__ . ":" . __LINE__ . " SKU Exists<br />";
						//We should go get the ID and update...
						 */
						$this->get_category();
						break;
					case "rest_invalid_param":
						/*
						echo "<br />" . __FILE__ . ":" . __LINE__ . " Invalid Param<br />";
						var_dump( $this->json_data );
						 */
						break;
					case "term_exists":
					case "woocommerce_api_cannot_create_product_category":
						echo "<br />" . __FILE__ . ":" . __LINE__ . "Code " . $this->code . " with message " . $this->msg . "<br />";
						var_dump( $e->get_request() );
						echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
						var_dump( $e->get_response() );
						echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
						$this->match_category();
						//$this->id = $response->data;
						echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
						if( $this->id > 0 )
							$this->update_woo_categories_xref();
						echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
						break;
					default:
						echo "<br />" . __FILE__ . ":" . __LINE__ . "Unhandled Code " . $this->code . " with message " . $this->msg . "<br />";
						var_dump( $this->wc_client );
					break;
				}
			}
			if( $this->debug >= 2 )
			{
				display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__ );
			}
		return FALSE;
		}
		
			//$this->extract_data_obj( $srvdata_object );
		//$this->update_woo_categories_xref();
		/*******************************************************************************************************************************************************/
					//sets method, path, body and then calls do_request					(new woo_rest)
					//do_request calls make_api_call with path, endpoint, data				(new woo_rest) except data
					//make_api_call sets an array with method, url, data, user/pw, connection options	(new woo_rest) except data
					//and then calls http_request->dispatch
					//dispatch sets up the CURL options, curl_exec, grabs response				write2woo
					//takes apart response OR throws an exception
					//returns the response if no exception
		
		$this->build_data_array();
		$this->build_json_data();
		if( $this->json_data == FALSE )
		{
			//Something went wrong with the conversion to JSON
			/*
			echo "<br /><br />" . __FILE__ . ":" . __LINE__ . "<br />";
			echo json_last_error();
			echo "<br /><br />" . __FILE__ . ":" . __LINE__ . "<br />";
			var_dump( $this );
			 */
		}
		else
		{
			$this->woo_rest->set_content_type( "application/json" );
			$response = $this->woo_rest->write2woo_json( $this->json_data, "POST" );
			$response_trimmed = substr( $response, strpos( $response, '{' ) );	//BASE does this now?
			$srvdata_object = json_decode( $response_trimmed );
			if( isset(  $srvdata_object->code ) )
			{
				switch( $srvdata_object->code ){
				case "woocommerce_rest_category_sku_already_exists":
					/*
					echo "<br />" . __FILE__ . ":" . __LINE__ . " SKU Exists<br />";
					//We should go get the ID and update...
					 */
					$this->get_category();
					break;
				case "rest_invalid_param":
					/*
					echo "<br />" . __FILE__ . ":" . __LINE__ . " Invalid Param<br />";
					var_dump( $this->json_data );
					 */
					break;
				case "term_exists":
					/*
					echo "<br />" . __FILE__ . ":" . __LINE__ . " Code " . $srvdata_object->code . " for category " . $this->name . "::" . $srvdata_object->data . "<br />";
					 */
					//WOO sends back the id for the existing code as the DATA element
					$this->id = $srvdata_object->data;
					$this->update_woo_categories_xref();
					break;
				default:
					/*
					echo "<br />" . __FILE__ . ":" . __LINE__ . " Unhandled Code " . $srvdata_object->code . "<br />";
					 */
					break;
				}
			}
			if( isset( $srvdata_object->id ) )
			{
				//UPDATE 0_woo with the ID, date_created, date_modified
				$this->notify( "Sent category " . $this->description . " and received ID " . $srvdata_object->id, "NOTIFY" );
				//echo "<br />" . __FILE__ . ":" . __LINE__ . "category inserted " .  $srvdata_object->id . ":" . $srvdata_object->description . "<br />";
				$this->extract_data_obj( $srvdata_object );
				$this->update_woo_categories_xref();
				$extractcount++;
			}
		}
		return $extractcount;
		//$response = $this->wc_client->products->get_categories(  $this->build_data_array() );
	}
	function retrieve_category()
	{
		if( $this->debug >= 1 )
		{
			echo "<br />" . __METHOD__ . ":" . __LINE__ . "<br />";
		}
		/*
		curl https://example.com/wp-json/wc/v1/products/categories/162 -u consumer_key:consumer_secret
		 * 
		 * */
	}
	function update_category()
	{
		if( $this->debug >= 1 )
		{
			echo "<br />" . __METHOD__ . ":" . __LINE__ . "<br />";
		}
		/*
		curl -X PUT https://example.com/wp-json/wc/v1/products/categories/162  -u consumer_key:consumer_secret  -H "Content-Type: application/json"  -d '{ "regular_price": "24.54" }'
		 * 
		 * */
	}
	function list_categorys()
	{
		if( $this->debug >= 1 )
		{
			echo "<br />" . __METHOD__ . ":" . __LINE__ . "<br />";
		}
		/*
		 * 	GET
		 *	curl https://example.com/wp-json/wc/v1/products/categories -u consumer_key:consumer_secret
		*/
	}
	/*@int@*/function send_categories_to_woo( $company_prefix )
	{
		if( $this->debug >= 1 )
		{
			echo "<br />" . __METHOD__ . ":" . __LINE__ . "<br />";
		}
		$category_sql = "select category_id, description from " . $company_prefix . "stock_category where category_id not in (select fa_cat from " . $company_prefix . "woo_categories_xref) order by category_id asc";
		$res = db_query( $category_sql, __LINE__ . " Couldn't select from stock_category" );
		$catcount = 0;
		$sentcount = 0;
		while( $cat_data = db_fetch_assoc( $res ) )
		{
			$catcount++;
			//No point trying to send a blank item to Woo
			if( strlen( $cat_data['description'] ) > 1 )
			{
				$this->id = null;
				$this->name = $cat_data['description'];
				$this->slug= $cat_data['description'];
				$this->description= $cat_data['description'];
				//$this->image;
				$this->menu_order= $cat_data['category_id'];
				$this->fa_id= $cat_data['category_id'];
				if( $this->debug >= 1 )
					$this->notify( __METHOD__ . ":" . __LINE__ . " Sending  " . $this->name . "::ID " . $this->fa_id, "NOTIFY" );
				$ret = $this->create_category();
				if( $ret > 0 )
					$sentcount++;
			}
			else
				if( $this->debug >= 0 )
					$this->notify( __METHOD__ . ":" . __LINE__ . " CatID " . $cat_data['category_id'] . " Strlen of description < 1.  Sent: " . $sentcount . " Catcount: " . $catcount, "ERROR" );
				//display_notification( "Woo seems to have all of our categories" );
		}
		return $sentcount;
	}

}

?>
