<?php

//20200707 Migrating from class.woo_category.php

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

//20200707 !migration

require_once( 'interface.woo_categories.php' );
global $fa_root;
require_once( dirname( __FILE__ ) . '/../../../ksf_modules_common/class.controller_origin.php' );

class controller_woo_categories extends controller_origin
{
	//var $model;	//Inherited
	//var $view;	//Inherited
	var $interface;
	function __construct()
	{
		require_once( 'model.woo_categories.php' );
		require_once( 'view.woo_categories.php' );
		$this->model = new model_woo_categories( null, null, null, null, $this );
		$this->view = new view_woo_categories( null, null, null, null, $this );
		$this->interface = new interface_woo_categories( null,null,null,null, $this );
		$this->reset_endpoint();
		/* for fuzzy_match
		$this->match_worth['sku'] = 2;
		$this->match_worth['slug'] = 2;
		$this->match_worth['description'] = 1;
		$this->match_worth['short_description'] = 1;
		$this->match_need = 2;
		$this->search_array = array( "woo_id", "name", "slug", "sku", "description", "short_description" );
		*/
	}
	function build_interestedin()
	{
			//calls $this->dummy( $calling_obj, $msg );
		$this->interestedin[KSF_DUMMY_EVENT]['function'] = "dummy";
		$this->interestedin['NOTIFY_INIT_TABLES']['function'] = "create_table";
		$this->interestedin['WOO_SEND_CATEGORIES']['function'] = "send_categories_to_woo";
		//p_attr_
	}
	/**************************************************
	*	Called by parent::__construct !!
	*************************************************/
	function reset_endpoint()
	{
		$this->endpoint = "products/categories";
	}

	function create_table()
	{
		$this->model->create_table();
	}
	function master_form()
	{
		$this->view->master_form();
	}
	/*@int@*/function insert_wc_category()
	{		
		try {
			//AS IS will error out because description not set.
			$this->model->get_fa_id_by_category_name();
			$this->model->update_woo_categories_xref();
			$this->model->insert_table();
			$this->model->reset_values();
			$loadcount++;
		}
		catch( Exception $e )
		{
			$code = $e->getCode();
			switch( $code )
			{
				case KSF_VALUE_NOT_SET:
				default:
					$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ . " Exception " . $code . " with message " . $e->getMessage() );
					break;
			}
		}
		
		//We should also tell any controller event loop listeners that we updated the list
		//table woo among others could then be updated...
		return $loadcount;
	}
	/**************************************************//**
	* woo_rest calls this fcn in ALL of its clients upon trying to send_new when WC responds item exists.
	*
	* Update our table with the data.
	*
	*@param id integer WC ID for this item
	*@return bool
	*****************************************************/
	function update_woo_id( $id )
	{
		//If we are calling this fcn from during a send_categories_to_woo
		//we will have not stored this data?
		$this->model->id = $id;
		try
		{
			$ret = $this->model->insert_table();
			//if ret is -1 than insert failed.
			if( $ret < 0 )
			{
				$ret = $this->model->update_table();
				if( $ret < 1 )
				{
					return FALSE;
				}
			}
		}
		catch( Exception $e )
		{
			throw $e;
		}
		return TRUE;
	}
	/**********************************************//**
	* Take WC response structure and set our values
	*
	* @param response Object (JSON)
	* @return bool
	**************************************************/
	function response2var( $response )
	{
		$fa_id = $this->model->fa_id;
		$this->reset_values();
		$this->model->id = $response->id;
		$this->model->name = $response['name'];
		$this->model->slug= $response['slug'];
		$this->model->description= $response['description'];
		//$this->model->image;
		$this->model->menu_order= $response['menu_order'];
		$this->model->fa_id = $fa_id;
		//$this->model->updated_ts;				//Should be set by MySQL
		//$this->model->woo_last_updated;
		return TRUE;
	}
	function cat2var( $cat_data )
	{
		$this->model->reset_values();
		$this->model->id = null;
		$this->model->name = $cat_data['description'];
		$this->model->slug= $cat_data['description'];
		$this->model->description= $cat_data['description'];
		//$this->image;
		$this->model->menu_order= $cat_data['category_id'];
		$this->model->fa_id= $cat_data['category_id'];
		return TRUE;
	}
	/****************************************************************************************************//**
	 * Send categories to WooCommerce
	 *
	 * @returns count of categories sent
	 * ****************************************************************************************************/
	/*@int@*/function send_categories_to_woo( )
	{
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ );
		try
		{
			$res = $this->model->select_new_categories();
			$catcount = 0;
			$sentcount = 0;
			while( $cat_data = db_fetch_assoc( $res ) )
			{
				$catcount++;
				$this->notify( __METHOD__ . ":" . __LINE__ . " Var_dump category data from stock category:" . print_r( $cat_data, true), "DEBUG" );
				//No point trying to send a blank item to Woo
				if( strlen( $cat_data['description'] ) > 1 )
				{
					$this->cat2var( $cat_data );
					$this->notify( __METHOD__ . ":" . __LINE__ . " Sending  " . $this->model->name . "::ID " . $this->model->fa_id, "NOTIFY" );
					try
					{
						//$ret = $this->create_category();
						$this->model->build_data_array();
						$response = $this->woo_rest->send( $this->endpoint, $this->model->data_array, $this );
						$this->notify( __METHOD__ . ":" . __LINE__ . " Response from WC:" . print_r( $response, true), "DEBUG" );
						$this->response2var( $response );
						$this->model->insert_table();
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
						$code = $e->getCode();
						$msg = $e->getMessage();
						switch( $this->code )
						{
							case "400":
							case "woocommerce_api_missing_callback_param":
							case "woocommerce_api_missing_product_category_data":
							case "woocommerce_api_cannot_create_product_category":
								if( strstr( $msg, "term_exists" ) )
								{
									$this->notify( __METHOD__ . ":" . __LINE__ . ":" . " TERM (Category) EXISTS for " . $this->name, "WARN" );
									//update xref so we don't try to resend.
									break;
								}
								$this->notify( __METHOD__ . ":" . __LINE__ . " Get Categories " . __METHOD__, "WARN" );
								$cat_array = $this->model->get_categories();
								if( isset( $cat_array ) )
								{
									$this->notify( __METHOD__ . ":" . __LINE__ . "Code " . $this->code . " with message " . $this->msg, "WARN" );
									$this->model->load_categories( $cat_array );
									return $this->send_categories_to_woo() + $sendcount;
								}
								//NEED to reset the process so that the ones we just LOADED aren't run hitting this error...
							break;
							case "404":
							case "woocommerce_api_no_route":
								$this->notify( __METHOD__ . ":" . __LINE__ . "No Route (API) " . $this->code . " with message " . $this->msg);
							break;
							case "woocommerce_rest_category_sku_already_exists":
								$this->model->get_category();
								break;
							case "rest_invalid_param":
								break;
							case "term_exists":
								$this->notify( __METHOD__ . ":" . __LINE__ . "Code " . $this->code . " with message " . $this->msg, "WARN" );
								$this->model->match_category();
								if( $this->id > 0 )
									$this->model->update_woo_categories_xref();
								break;
							default:
								$this->notify( __METHOD__ . ":" . __LINE__ . "Code " . $this->code . " with message " . $this->msg, "WARN" );
							break;
						}
					}
				}
				else
					if( $this->debug >= 0 )
						$this->notify( __METHOD__ . ":" . __LINE__ . " CatID " . $cat_data['category_id'] . " Strlen of description < 1.  Sent: " . $sentcount . " Catcount: " . $catcount, "ERROR" );
					//display_notification( "Woo seems to have all of our categories" );
			}
			//Now need to UPDATE categories that are there.
			//Do we assume that we've downloaded the data from WC already so we are only sending changes
			//that are in FA stock_category?
		}
		catch( Exception $e )
		{
			throw $e;
		}
		return $sentcount;
	}
	function export_categories_form()
	{
		$this->view->export_categories_form();
/*
	$woo_category = new woo_category( $this->woo_server, $this->woo_rest_path, $this->woo_ck, $this->woo_cs, null, $this, "devel" );
		$woo_category->debug = $this->debug;
		$ret = $woo_category->send_categories_to_woo( $this->company_prefix );
		display_notification( "Sent " . $ret . " categories to WooCommerce" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );

//woo_category->send_categories_to_woo...
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$response = $this->model->send_categories_to_woo( );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;

*/
	}
	function exported_categories_form()
	{
		$this->view->exported_categories_form();
	}
}

?>
