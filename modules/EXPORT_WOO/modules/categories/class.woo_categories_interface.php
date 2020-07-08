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

/*
id	integer	Unique identifier for the resource.READ-ONLY
name	string	Category name.MANDATORY
slug	string	An alphanumeric identifier for the resource unique to its type.
parent	integer	The ID for the parent of the resource.
description	string	HTML description of the resource.
display	string	Category archive display type. Options: default, products, subcategories and both. Default is default.
image	object	Image data. See Product category - Image properties
menu_order	integer	Menu order, used to custom sort the resource.
count	integer	Number of published products for the resource.
 * */

require_once( '../../class.woo_interface.php'  );

/****************************************************************
 *
 * Depends on client->model
 * *************************************************************/
class woo_categories_interface extends woo_interface
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
	var $model;
	function __construct( $client )
	{
		parent::__construct( $client );
		$this->dumped_woocategories_count = 0;
		$this->loadcount = 0;
		if( isset( $client->model ) )
			$this->model = $client->model;
		return;
	}
	/**************************************************
	*	Called by parent::__construct !!
	*************************************************/
	function reset_endpoint()
	{
		$this->endpoint = "products/categories";
	}
	//function define_table() //MODEL
	/******************************************************
	 * Did woo_interface end up with a generic version 
	 * expecting config data in arrays?
	 * ****************************************************/
	function fuzzy_match( $data )
	{
		if( !isset( $data[0] ) )
			throw new Exception( "fuzzy_match expects a data array.  Not passed in", KSF_VALUE_NOT_SET );
		$match=0;
		if( ! strcasecmp( $data[0]->name, $this->name ) )
		{
			$match++; 
			$match++; 
		}
		if( ! strcasecmp( $data[0]->slug, $this->slug ) )
		{
			$match++; 
		}
		if( ! strcasecmp( $data[0]->description, $this->description ) )
		{
			$match++; 
		}
		if( $match > 1 )
		{
			$this->id = $data[0]->id;
			return TRUE;
		}
		return FALSE;
	}
	/**********************************************************************************//**
	 *
	 *
	 * ***********************************************************************************/
	function error_handler( /*@Exception@*/$e )
	{
				$this->log_exception( $e, $client );
		if ( $e instanceof WC_API_Client_HTTP_Exception ) 
		{
			switch( $e->getCode() ) {
			default:
				echo "<br />" . __FILE__ . ":" . __LINE__ . ":Unhandled Error Code: " . $e->getCode() . "<br />";
				break;
			}
		}
	}
	/**********************************************************************************//**
	 * Return the list of categories from WooCommerce as an array of stdClass objects
	 *
	 * Only want to query WooCommerce once per run for the categories
	 *
	 * @param NONE
	 * @return array of stdClass Objects of category (woo_category) data.
	 * ***********************************************************************************/
	/*@array of stdClass@*/function get_wc_categories()
	{		
		if( $this->loadcount > 0 )
		{
			return null;
		}
		try {
			$response = $this->retreive_woo();
			$this->loadcount++;
			return $response->product_categories;
		}
		catch( Exception $e )
		{
			$this->error_handler( $e );
		}
	}
	//Fix name...refactor!
	function get_categories()
	{
		return $this->get_wc_categories();
	}
	/**************************************************************************************//**
	 * Get the details of 1 category from WooCommerce
	 *
	 *	requires ID to be set otherwise seeks first match by name
	 *
	 * @param none
	 * @returns StdObject Class             
	 * *************************************************************************************/
	/*@stdobj@*/function get_wc_category()
	{
		try {
			if( $this->id < 1 )
				throw new Exception( "ID can't be less than 1", KSF_INVALID_VALUE );
			try {
				$response = $this->retreive_one( $this->endpoint, null, $this );
				return $response;
			}
			catch( Exception $e )
			{
				$this->error_handler( $e );
			}

		} catch ( Exception $e ) {
			$this->error_handler( $e );
		}
	}
	//Fix name...refactor!
	/*@bool@*/function get_category()
	{
		return $this->get_wc_category();
	}
	/*************
	* Looking for a specific category
	************/
	/*@bool@*/function seek_category()
	{
		$response = $this->get_wc_category();
			//	Object->product_categories array of stdClass which we should be able to cast to woo_categories...
			if( $this->match_category_name( $response->product_categories ) )
			{
				return TRUE;
			}
		return FALSE;
	}
	/************************************************************************************************//**
	 * Match our name against the names in WooCommerce
	 *
	 * 	Sets our details to match WooCommerce
	 *
	 * @param array the response from wc_client query
	 * @returns bool did we find a match
	 * ***********************************************************************************************/
	/*@bool@*/function match_category_name( /*@array of stdClass@*/$product_categories )
	{
		foreach( $product_categories as $cobj )
		{
			if( $this->name == $cobj->name )
			{
				$this->id = $cobj->id;
				$this->slug = $cobj->slug;
				return TRUE;
			}
			else
			{
				//didn't match.  NEXT!
			}
		}
		return FALSE;
	}
	/************************************************************************************************//**
	 * Match our slug against the slugs in WooCommerce
	 *
	 * 	Sets our details to match WooCommerce
	 *
	 * @param array the response from wc_client query
	 * @returns bool did we find a match
	 * ***********************************************************************************************/
	/*@bool@*/function match_category_slug( /*@array of stdClass@*/$product_categories )
	{
		foreach( $product_categories as $cobj )
		{
			if( $this->slug == $cobj->slug )
			{
				$this->id = $cobj->id;
				$this->slug = $cobj->slug;
				return TRUE;
			}
			else
			{
			}
		}
		return FALSE;
	}
	/************************************************************************************//**
	 * When we don't have the woo category_id, see if one exists with our name
	 *
	 *	Requires that NAME is set
	 *	HOWEVER nothing is done with the name :(
	 *
	 * @returns bool whether one is found
	 * **************************************************************************************/
	/*@bool@*/function seek_category_by_name()
	{
		//In this case we DON'T know the woo category_id
		$this->id = null;
		if( ! isset( $this->name ) )
			throw new InvalidArgumentException( "Category Name not set" );
		if( $this->get_category() )
		{
			//We have found the category and ID
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	/************************************************************************************//**
	 * Take the returned array of categories data from WooCommerce and insert into here/xref
	 *
	 * This function has a check to ensure we only run once.  Else a logic error elsewhere
	 * has this function called repeatedly and we eventually time out.	
	 *
	 * @returns int count of categories we loaded
	 * **************************************************************************************/
	/*@int@*/function load_categories( /*array of objects*/$categories_array )
	{		
		if( ! isset( $this->model ) )
		{
			throw new Exception( __METHOD__ . ":: MODEL is required for this function to work!", KSF_FIELD_NOT_SET );
		}
		if( $this->loadcount > 0 )
		{
			return 0;
		}
		$loadcount = 0;
		foreach( $categories_array as $cat )
		{ 
			//5 seconds to process 1 category item should be more than sufficient!
			set_time_limit( 5 );
			try
			{
				//$cat is an object
				if( $loadcount == 0 )
				{
					//dump once for each time we come into load_categories
					//$this->notify( __METHOD__ . ":" . __LINE__ . " Var Dump object ", "WARN" );
					////var_dump( $cat );
					$this->dumped_woocategories_count++;
				}
				foreach( $this->properties_array as $fieldname )
				{
					if( isset( $cat->$fieldname ) )
						$this->model->$fieldname = $cat->$fieldname;
				}
				$loadcount = $loadcount+$this->model->insert_wc_category();
			}
			catch( Exception $e )
			{
				$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . " Exception " . $e->getCode() . " with message " . $e->getMessage() );
			}
		}
		if( $loadcount > 0 and $loadcount > $this->loadcount )
			$this->loadcount = $loadcount;
		else
			$this->loadcount++;
		return $loadcount;
	}
	//function get_fa_id_by_category_name()		//MODEL
	//function update_woo_categories_xref()		//MODEL
	/****************************************************************************************************//**
	 *
	 * 
	 * Will throw an OutOfBoundsException if we need to reset our query to restart
	 *
	 * ****************************************************************************************************/
	/*@bool@*/function create_category()
	{
		if( ! isset( $this->model ) )
		{
			throw new Exception( __METHOD__ . ":: MODEL is required for this function to work!", KSF_FIELD_NOT_SET );
		}
		$extractcount = 0;
		try {
			$this->build_data_array();
			$response = $this->woo_rest->send( $this->endpoint, $this->data_array, $this );
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . ":Response from WC::" . print_r( $response, true )  );
			$this->model->id = $this->id = $response->id;
			$this->model->obj_insert_or_update( $this );
			$this->model->update_woo_categories_xref();
			//$this->model->insert_table();
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
						$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . " Get Categories "  );
						$cat_array = $this->get_categories();
						if( isset( $cat_array ) )
						{
							$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . " Load Categories into our Woo tables "  );
							$this->load_categories( $cat_array );
							throw new OutOfBoundsException( "Reset and try again" );
						}
					break;
					case "404":
					case "woocommerce_api_no_route":
							$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . "No Route (API) " . $this->code . " with message " . $this->msg );
					break;
					case "woocommerce_rest_category_sku_already_exists":
						$this->get_category();
						break;
					case "rest_invalid_param":
						break;
					case "term_exists":
						$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . " Term Exists:: " . $this->code . " with message " . $this->msg );
						$this->match_category();
						if( $this->id > 0 )
							$this->update_woo_categories_xref();
						break;
					default:
						$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . " Unhandled Error:: " . $this->code . " with message " . $this->msg );
						throw $e;
					break;
				}
			}
		return FALSE;
		} catch( Exception $e )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . " Unhandled Error:: " . $e->code . " with message " . $e->msg );
			throw $e;
		}
		
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
	function list_categories()
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
	/****************************************************************************************************//**
	 * Send categories to WooCommerce
	 *
	 * @returns count of categories sent
	 * ****************************************************************************************************/
	/*@int@*/function send_categories_to_woo( )
	{
		$category_sql = "select category_id, description from " . TB_PREF . "stock_category where category_id not in (select fa_cat from " . TB_PREF . "woo_categories_xref ) order by category_id asc";
		$res = db_query( $category_sql, __LINE__ . " Couldn't select from stock_category" );
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

