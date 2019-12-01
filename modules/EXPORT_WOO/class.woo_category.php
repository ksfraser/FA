<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

/**********************************************
* REQUIREMENTS
*
*	Create a table to store data about WC categories
*		This was proven working in an upstream class
*	Send categories to WooCommerce
*	Search to see if a category exists (for updates/fault tolerance/repair)
*	Rebuild a store
*		Send list of categories anew
*			Reset internal data to allow/force update
*	Provide a list of capabilites so that the master module can load them
*		Menu choices and related screens
*
*********************************************/

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

//require_once( 'class.woo_rest.php' );	//Part of woo_interface
require_once( 'class.woo_interface.php' );
require_once( 'class.model_woo_category.php' );

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
	var $model;

	//var $woo_rest;
	var $header_array;
	var $dumped_woocategories_count;
	var $loadcount;
	function __construct( $serverURL, $woo_rest_path, $key, $secret,  $options, $client, $enviro = "devel" )
	{
		parent::__construct($serverURL, $key, $secret, $options, $client);
		$this->dumped_woocategories_count = 0;
		$this->loadcount = 0;
		$this->model = new model_woo_category( $serverURL, $woo_rest_path, $key, $secret, $options, $client, $enviro );
		return;
	}
	/**************************************************
	*	Called by parent::__construct !!
	*************************************************/
	function reset_endpoint()
	{
		$this->endpoint = "products/categories";
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
	/**********************************************************************************//**
	 *
	 *
	 * ***********************************************************************************/
	function error_handler( /*@Exception@*/$e )
	{
		if ( $e instanceof WC_API_Client_HTTP_Exception ) 
		{
			//$msg = $e->getMessage();
			////var_dump( $e->get_request() );
			////var_dump( $e->get_response() );
			switch( $e->getCode() ) {
			default:
				echo "<br />" . __FILE__ . ":" . __LINE__ . ":Unhandled Error Code: " . $e->getCode() . "<br />";
				break;
			}
		}
	}
	/**********************************************************************************//**
	 * Return the list of categories as an array of stdClass objects
	 *
	 * Only want to query WooCommerce once per run for the categories
	 *
	 * @param NONE
	 * @return array of stdClass Objects of category (woo_category) data.
	 * ***********************************************************************************/
	/*@array of stdClass@*/function get_categories()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->get_categories();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	/**************************************************************************************//**
	 * Get the details of 1 category from WooCommerce
	 *
	 *	requires ID to be set otherwise seeks first match by name
	 *
	 * @param none
	 * @returns bool did we find the category
	 * *************************************************************************************/
	/*@bool@*/function get_category()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->get_category();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
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
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->match_category_name( $product_categories );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
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
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->match_category_slug( $product_categories );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
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
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->seek_category_by_name();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
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
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->load_categories( $categories_array );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}		
	/************************************************************************//**
	 * Given a category description, find the FA Category ID
	 *
	 * @param noe but depends on description
	 * @returns bool success or not.   sets fa_id on success
	 * *************************************************************************/
	/*@bool@*/function get_fa_id_by_category_name()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->get_fa_id_by_category_name();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	function update_woo_categories_xref()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->update_woo_categories_xref();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	/****************************************************************************************************//**
	 *
	 * 
	 * Will throw an OutOfBoundsException if we need to reset our query to restart
	 *
	 * ****************************************************************************************************/
	/*@bool@*/function create_category()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		/*************************************************************************************************************************************************/
		try {
			$response = $this->model->create_category();
			$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
			return $response;
		} catch ( WC_API_Client_Exception $e ) {
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
		}
		
	}
	function retrieve_category()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->retrieve_category();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	function update_category()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->update_category();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	function list_categories()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->list_categories();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	/****************************************************************************************************//**
	 * Send categories to WooCommerce
	 *
	 * @returns count of categories sent
	 * ****************************************************************************************************/
	/*@int@*/function send_categories_to_woo( )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->send_categories_to_woo( );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}

}

?>
