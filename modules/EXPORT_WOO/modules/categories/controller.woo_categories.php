<?php

class controller_woo_categories extends controller_origin
{
	//var $model;	//Inherited
	//var $view;	//Inherited
	function __construct()
	{
		require_once( 'model.woo_categories.php' );
		require_once( 'view.woo_categories.php' );
		$this->model = new model_woo_categories( null, null, null, null, $this );
		$this->view = new view_woo_categories( null, null, null, null, $this );
	}
	function build_interestedin()
	{
			//calls $this->dummy( $calling_obj, $msg );
		$this->interestedin[KSF_DUMMY_EVENT]['function'] = "dummy";
		$this->interestedin['NOTIFY_INIT_TABLES']['function'] = "create_table";
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
			switch '$code':
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
	/****************************************************************************************************//**
	 * Send categories to WooCommerce
	 *
	 * @returns count of categories sent
	 * ****************************************************************************************************/
	/*@int@*/function send_categories_to_woo( )
	{
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG',  __METHOD__ . ":" . __LINE__ );
		$res = $this->model->select_new_categories();
		$catcount = 0;
		$sentcount = 0;
		while( $cat_data = db_fetch_assoc( $res ) )
		{
			$this->model->reset_values();
			$catcount++;
			$this->notify( __METHOD__ . ":" . __LINE__ . " Var_dump category data from stock category:" . print_r( $cat_data, true), "DEBUG" );
			//No point trying to send a blank item to Woo
			if( strlen( $cat_data['description'] ) > 1 )
			{
				$this->model->id = null;
				$this->model->name = $cat_data['description'];
				$this->model->slug= $cat_data['description'];
				$this->model->description= $cat_data['description'];
				//$this->image;
				$this->model->menu_order= $cat_data['category_id'];
				$this->model->fa_id= $cat_data['category_id'];
				$this->notify( __METHOD__ . ":" . __LINE__ . " Sending  " . $this->model->name . "::ID " . $this->model->fa_id, "NOTIFY" );
				try
				{
					//$ret = $this->create_category();
					$this->model->build_data_array();
					$response = $this->woo_rest->send( $this->endpoint, $this->model->data_array, $this );
					$this->notify( __METHOD__ . ":" . __LINE__ . " Response from WC:" . print_r( $response, true), "DEBUG" );
					$this->model->id = $response->id;
					$this->model->update_woo_categories_xref();
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
		return $sentcount;
	}
}

?>
