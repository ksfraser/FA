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
 *		don't exist (imply also xref)
 *	Delete any categories that we don't have in FA (cleanup function - side effects?)
 *	Extend to allow us to change menu order?
 *	Extend to build parent-child relationships
 *
 * BUGS: 
 * 	(unconfirmed/can't reproduce) when there is 2 spaces in the category name/description 
 * 	it doesn't necessarily get added into _xref
 *
 * **********************************************************************************/


class model_woo_category extends MODEL
{
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
	var $collection;

	//var $woo_rest;
	var $header_array;
	var $dumped_woocategories_count;
	var $loadcount;
	function __construct( $client )
	{
		parent::__construct( $client );
		$this->dumped_woocategories_count = 0;
		$this->loadcount = 0;
		return;
	}
	/**************************************************
	*	Called by parent::__construct !!
	*************************************************/
	function reset_endpoint()
	{
		$this->endpoint = "products/categories";
	}
	function define_table()
	{
		$this->fields_array[] = array('name' => 'woo_category_id', 	'type' => 'int(11)', 		'comment' => 'Index.', 'readwrite' => 'read', 'auto_increment' => 'anything');
		$this->fields_array[] = array('name' => 'updated_ts', 		'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read');
		$this->fields_array[] = array('name' => 'updated_ts_woo',	'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read');

		$this->fields_array[] = array('name' => 'id', 			'type' => 'int(11)', 		'comment' => ' 	Item ID', 'readwrite' => 'read');
		$this->fields_array[] = array('name' => 'name', 		'type' => 'varchar(64)', 	'comment' => ' 	Category Name.', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'slug', 		'type' => 'varchar(64)', 	'comment' => ' 	Category Slug.', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'parent', 		'type' => 'int(11)', 		'comment' => ' 	Parent Category ID.', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'description', 		'type' => 'varchar(64)', 	'comment' => 'Category Description.', 'readwrite' => 'readwrite'); 	
		$this->fields_array[] = array('name' => 'display', 		'type' => 'varchar(64)', 	'comment' => 'Display Type.  default/caregories/subcategories/both.', 'readwrite' => 'readwrite'); 	
		$this->fields_array[] = array('name' => 'image', 		'type' => 'blob', 		'comment' => ' Category Image.', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'menu_order', 		'type' => 'int(11)', 		'comment' => ' Menu Order.', 'readwrite' => 'readwrite');
		$this->fields_array[] = array('name' => 'count', 		'type' => 'int(11)', 		'comment' => ' number of published categories', 'readwrite' => 'read');
		$this->fields_array[] = array('name' => 'fa_id',		'type' => 'int(11)', 		'comment' => ' 	FA Category ID', 'readwrite' => 'read');

		$this->table_details['tablename'] = $this->company_prefix . "woo_category";
		$this->table_details['primarykey'] = "woo_category_id";
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "name";
		$this->table_details['index'][0]['keyname'] = "u-name";
		$this->table_details['index'][1]['type'] = 'unique';
		$this->table_details['index'][1]['columns'] = "slug";
		$this->table_details['index'][1]['keyname'] = "u-slug";
	}
	//function fuzzy_match( $data ) 	//INTERFACE
	//function error_handler( /*@Exception@*/$e )	//INTERFACE
	//function get_wc_categories()		//INTERFACE
	//function get_categories()		//INTERFACE
	//function get_wc_category()		//INTERFACE
	//function get_category()		//INTERFACE
	//function seek_category()
	//function match_category_name( /*@array of stdClass@*/$product_categories )
	//function match_category_slug( /*@array of stdClass@*/$product_categories )
	//function seek_category_by_name()
	//function load_categories( /*array of objects*/$categories_array )
	//		CALLS insert_wc_category
	//
	/********************************************//**
	 * Insert a description (category) into WC
	 *
	 * Copied into the controller.
	 * *********************************************/
	/*@int@*/function insert_wc_category()
	{		
		try {
			$this->get_fa_id_by_category_name();
			$this->update_woo_categories_xref();
			$this->insert_table();
			$this->reset_values();
			$loadcount++;
		}
		catch( Exception $e )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . " Exception " . $e->getCode() . " with message " . $e->getMessage() );
		}
		
		if( $loadcount > 0 and $loadcount > $this->loadcount )
			$this->loadcount = $loadcount;
		else
			$this->loadcount++;
		//We should also tell any controller event loop listeners that we updated the list
		//table woo among others could then be updated...
		return $loadcount;
	}
	/************************************************************************//**
	 * Given a category description, find the FA Category ID
	 *
	 * @param noe but depends on description
	 * @returns bool success or not.   sets fa_id on success
	 * *************************************************************************/
	/*@bool@*/function get_fa_id_by_category_name()
	{
		if( ! isset( $this->description ) )
			throw new Exception( "Description not set", KSF_VALUE_NOT_SET );
		try
		{
			$sql = "select category_id as id from " . TB_PREF . "stock_category where description=" . db_escape($this->description);
			$res = db_query( $sql, __LINE__ . " Couldn't select from stock_category" );
			while( $cat_data = db_fetch_assoc( $res ) )
			{
				$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . " Setting fa_id to " . $cat_data['id'] . 
												" for description " .  $this->description . "<br /><br />" );
				$this->fa_id = $cat_data['id'];
			}
			return TRUE;
		}
		catch( Exception $e )
		{
			throw $e;
		}
		return FALSE;
	}
	/***********************************************//**
	 * Update our xref table with FA vs WC ID/Desc etc
	 *
	 * @param NONE but uses internal
	 * @return bool
	 * ************************************************/
	function update_woo_categories_xref()
	{
		if( ! isset( $this->fa_id ) )
			throw new Exception( "FA ID not set", KSF_FIELD_NOT_SET );
		if( ! isset( $this->id ) )
			throw new Exception( "WC ID not set", KSF_FIELD_NOT_SET );
		if( ! isset( $this->description ) )
			throw new Exception( "Description not set", KSF_FIELD_NOT_SET );
		require_once( 'class.categories_xref_model.php' );
		$xref = new categories_xref_model( null, null, null, null, $this );
		$xref->set_my_values( $this->fa_id, $this->id, $this->description );
		//We are setting blank descriptions.  WHY?
		if( $this->description == '' )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',   __METHOD__ . ":" . __LINE__ . " Blank Description for FA-ID " . $this->fa_id );
		}
		else
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . " Description for FA-ID " . $this->fa_id . " is " . $this->description );
		}
		try {
			$xref->insert_or_update( $this );
		}
		catch( Exception $e )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . ":" . __METHOD__ . " Exception " . $e->getCode() . "::" . $e->getMessage() );
			throw $e;
		}
		unset( $xref );
		return TRUE;
	}
	/****************************************************************************************************//**
	 *
	 * 
	 * Will throw an OutOfBoundsException if we need to reset our query to restart
	 *
	 * ****************************************************************************************************/
	/*@bool@*/function create_category()
	{
		$extractcount = 0;
		try {
			$this->build_data_array();
/*
			if( ! is_callable( $this->woo_rest->send( $this->endpoint, $this->data_array, $this ) ) )
			{
				echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " ERROR----<br /> ";
				var_dump( $this->woo_rest );
				echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " ----ERROR<br /> ";
			//	$this->log_exception( $this->woo_rest, $client );
			}
*/
			$response = $this->woo_rest->send( $this->endpoint, $this->data_array, $this );
			if( $this->debug > 1 )
			{
				echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " SEND RESPONSE----<br /> ";
				var_dump( $response );
				echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " ----SEND RESPONSE<br /> ";
			}
			$this->id = $response->id;
			$this->update_woo_categories_xref();
			$this->insert_table();
			//There should be a response, we need to update our xref table so we know what the cat WOO ID is
			//$this->extract_data_obj( $response->product );
			//$this->update_wootable_woodata();	
			return TRUE;	//Should we return false until the category is updated?

		} catch ( WC_API_Client_Exception $e ) {
				$this->log_exception( $e, $client );
			if ( $e instanceof WC_API_Client_HTTP_Exception ) 
			{
				//echo __METHOD__ . ":" . __LINE__ . " We hit an error.  Here is the data we sent! <br />";
				//var_dump( $this->data_array );
				//echo "<br />";
				$this->code = $e->getCode();
				$this->msg = $e->getMessage();
				//Moved into fcn rest_error_handler in other classes...
				switch( $this->code )
				{
					case "400":
					case "woocommerce_api_missing_callback_param":
					case "woocommerce_api_missing_product_category_data":
					case "woocommerce_api_cannot_create_product_category":
						echo "<br />" . __FILE__ . ":" . __LINE__ . " Code " . $this->code . " with message " . $this->msg . "<br />";
						$this->notify( __METHOD__ . ":" . __LINE__ . " Get Categories " . __METHOD__, "WARN" );
						$cat_array = $this->get_categories();
						if( isset( $cat_array ) )
						{
							$this->notify( __METHOD__ . ":" . __LINE__ . " Load Categories into our Woo tables " . __METHOD__, "WARN" );
							$this->load_categories( $cat_array );
							throw new OutOfBoundsException( "Reset and try again" );
						}
						////var_dump( $e->get_request() );
						//NEED to reset the process so that the ones we just LOADED aren't run hitting this error...
					break;
					case "404":
					case "woocommerce_api_no_route":
						echo "<br />" . __FILE__ . ":" . __LINE__ . "No Route (API) " . $this->code . " with message " . $this->msg . "<br />";
						//var_dump( $this->wc_client->products );
					break;
					case "woocommerce_rest_category_sku_already_exists":
						$this->get_category();
						break;
					case "rest_invalid_param":
						break;
					case "term_exists":
						echo "<br />" . __FILE__ . ":" . __LINE__ . "Code " . $this->code . " with message " . $this->msg . "<br />";
						//var_dump( $e->get_request() );
						echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
						//var_dump( $e->get_response() );
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
						//var_dump( $this->wc_client );
						exit();
					break;
				}
			}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return FALSE;
		} catch( Exception $e )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " EXCEPTION " . __METHOD__, "WARN" );
			echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " EXCEPTION----<br /> ";
				$this->log_exception( $e, $this->client );
			var_dump( $e );
			echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " ----EXCEPTION<br /> ";

		}
		
	}
	function retrieve_category()
	{
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ );
		/*
		curl https://example.com/wp-json/wc/v1/products/categories/162 -u consumer_key:consumer_secret
		 * 
		 * */
	}
	function update_category()
	{
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ );
		/*
		curl -X PUT https://example.com/wp-json/wc/v1/products/categories/162  -u consumer_key:consumer_secret  -H "Content-Type: application/json"  -d '{ "regular_price": "24.54" }'
		 * 
		 * */
	}
	function list_categories()
	{
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ );
		/*
		 * 	GET
		 *	curl https://example.com/wp-json/wc/v1/products/categories -u consumer_key:consumer_secret
		*/
	}
	/***********************************************************************************//**
	 * Send categories to WC that we don't have a woo_id for, so is "new"
	 *
	 * @params none
	 * @returns mysql_res
	 * *************************************************************************************/
	function select_new_categories()
	{
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ );
		$category_sql = "select category_id, description from " . TB_PREF . "stock_category where category_id not in (select fa_cat from " . TB_PREF . "woo_categories_xref ) order by category_id asc";
		$res = db_query( $category_sql, __LINE__ . " Couldn't select from stock_category" );
		return $res;
	}
	/****************************************************************************************************//**
	 * Send categories to WooCommerce
	 *
	 * @returns count of categories sent
	 * ****************************************************************************************************/
	/*@int@*/function send_categories_to_woo( )
	{
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ );
		//$category_sql = "select category_id, description from " . TB_PREF . "stock_category where category_id not in (select fa_cat from " . TB_PREF . "woo_categories_xref ) order by category_id asc";
		//$res = db_query( $category_sql, __LINE__ . " Couldn't select from stock_category" );
		$res = $this->select_new_categories();
		$catcount = 0;
		$sentcount = 0;
		while( $cat_data = db_fetch_assoc( $res ) )
		{
			$this->reset_values();
			$catcount++;
			if( $this->debug > 0 )
			{
				$this->notify( __METHOD__ . ":" . __LINE__ . " Var_dump category data from stock category", "WARN" );
				echo  __METHOD__ . ":" . __LINE__ . " Var_dump category data from stock category<br />";
				var_dump( $cat_data );
			}
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
				$this->notify( __METHOD__ . ":" . __LINE__ . " Sending  " . $this->name . "::ID " . $this->fa_id, "NOTIFY" );
				try
				{
					$ret = $this->create_category();
					if( $ret == TRUE )
						$sentcount++;
				}
				catch( OutOfBoundsException $e )
				{
					$this->log_exception( $e, $client );
					//Reset since we sent an item that exists.  Resulting in us loading from WooCommerce the list of categories
					$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving (recursively) " . __METHOD__, "WARN" );
					return $this->send_categories_to_woo() + $sendcount;
				}
				catch( Exception $e )
				{
					$this->notify( __METHOD__ . ":" . __LINE__ . ":" . __METHOD__ . " Exception " . $e->getCode() . "::" . $e->getMessage(), "ERROR" );
					throw $e;
				}
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

