<?php

//require_once( 'class.generic_orders.php' ); 
require_once( 'class.generic_fa_interface.php' ); 


//class EXPORT_WOO_PROD
//class EXPORT_WOO_PROD extends generic_orders
//class simple_product extends generic_fa_interface
class simple_product 
{
	var $debug;
	function __construct( )
	{
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
	function call_table( $action, $msg )
	{
                start_form(true);
                 start_table(TABLESTYLE2, "width=40%");
                 table_section_title( $msg );
                 hidden('action', $action );
                 end_table(1);
                 submit_center( $action, $msg );
                 end_form();
	}

	function exported_rest_variable_products()
	{
		/*
		 *
		 * 	The following query grabs all stock_ids that have the stub from
		 * 	...master indicating they are all of the same product
		 * 	ASSUMPTION we have a smart stock_id policy
		 *
			SELECT sm.*, vm.stock_id as parent_id FROM 0_stock_master sm
			INNER JOIN (SELECT stock_id FROM 0_woo_prod_variable_master GROUP BY stock_id) vm
			ON sm.stock_id LIKE  concat( vm.stock_id, '%')
		 */
		$master_prod_sql = "SELECT stock_id FROM " . TB_PREF . "woo_prod_variable_master";
		$res = db_query( $master_prod_sql, __LINE__ . " Couldn't select master product(s) for export" );
		require_once( 'class.woo_product.php' );
		while( $prod_data = db_fetch_assoc( $res ) )
		{
			$woo_product = new woo_product( $this->woo_server, $this->woo_rest_path, $this->woo_ck, $this->woo_cs, $this->environment, $this );
			$woo_product->debug = $this->debug;
			$woo_product->status = "publish";
			$woo_product->tax_status = "taxable";
			$woo_product->tax_class = "GST";
			$woo_product->manage_stock = true;
			$woo_product->catalog_visibility = "visible";
			$woo_product->backorders = "yes";
			$woo_product->sold_individually = false;
			$woo_product->reviews_allowed = "1";
			//$woo_product->menu_order = "1";
			$var_array = array( 'sale_price', 
						'date_on_sale_from', 
						'date_on_sale_to', 
						'external_url', 
						'tax_status', 
						'tax_class', 
						'shipping_class'
				);
			foreach($var_array as $var )
			{
				if( isset( $prod_data[$var] ) && strlen( $prod_data[$var] ) > 1 )
				{
					$woo_product->$var = utf8_encode( $prod_data[$var] );
				}
			}
			$woo_product->weight = "1.0";
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
			$woo_product->categories = array( $prod_data['woo_category_id'] );
			//$woo_product->upsell_ids = "";
			//$woo_product->cross_sell_ids = "";
			//$woo_product->parent_id = "";
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

			$woo_product->images = $this->product_images;
			$woo_product->type = "variable";
			//prod_data is the master product.  Need to find each child as variations
			$child_sql = "select stock_id, woo_category_id, description, long_description, price, instock, 
				sale_price, date_on_sale_from, date_on_sale_to, 
				external_url, tax_status, tax_class, 
				weight, length, width, height, shipping_class, 
				upsell_ids, crosssell_ids, parent_id, 
				attributes, default_attributes, variations
				FROM " . TB_PREF . "woo
				where stock_id like '" . $prod_data['stock_id'] . "%'";
				$res2 = db_query( $child_sql, __LINE__ . " Couldn't select child product(s) for export" );
				while( $child_data = db_fetch_assoc( $res2 ) )
				{
				//$woo_product->variations = array("");
				//	Same properties as woo_product
				}
			
				$woo_product->create_product();		
		}
	}
	/***********************************************************************
	*
	*	Function exported_rest_products_form()
	*
	*	Function that sends the products to WOO
	*
		
	*
	************************************************************************/
	function exported_rest_products_form()
	{
		require_once( 'class.woo_product.php' );
		$woo_product = new woo_product( $this->woo_server, $this->woo_rest_path, $this->woo_ck, $this->woo_cs, null, $this );
		$woo_product->debug = $this->debug;
		$sentcount = $woo_product->send_simple_products();
		//If we haven't timed out...
		//$sentcount .= $woo_product->update_simple_products();

            	display_notification( $sentcount . " Products sent.");
		$this->call_table( 'export_rest_product', "Export Another" );
		$this->call_table( 'export_rest_products', "Export ALL Another" );
	}
	/***********************************************************************
	*
	*	Function export_rest_product_form
	*
	*	To show products and send 1 to WOO
	*
	*	Allow user to update the items based upon missing data...
	*
	************************************************************************/
	function export_rest_product_form()
	{
		$missing_sql = "select sm.stock_id, sm.description, c.description, sm.inactive, sm.editable 
				from " . TB_PREF . "stock_master sm, " . TB_PREF . "stock_category c
				where sm.category_id = c.category_id and sm.stock_id in (select stock_id from " . TB_PREF . "woo)";
		 global $all_items;
	  	//stock_items_list($name, $selected_id, $all_option, $submit_on_change,
	        //        array('cells'=>true, 'show_inactive'=>$all), $editkey);
		//function stock_items_list($name, $selected_id=null, $all_option=false,
	        				//$submit_on_change=false, $opts=array(), $editkey = false)
		$selected_id = "0";
		$name = "";
		$editkey = TRUE;
		$opts = array('cells'=>true, 'show_inactive'=>'1');
		$all_option = FALSE;
		$submit_on_change = TRUE;
	        //if ($editkey)
	                set_editor('item', $name, $editkey);
		start_form();

		start_table();
		table_section_title(_("Export a Product to WOO via REST"));
	//	label_row(_("No Transaction History (no inventory movement):"), NULL);
		label_row("&nbsp;", NULL);
		label_row("Press F4 to pop open a window to edit the item details.  The details WON'T make it to WOO until the 'All Products Export' routine is rerun", null);

		table_section(1);
	        $ret = combo_input($name, $selected_id, $missing_sql, 'stock_id', 'sm.description',
	        array_merge(
	          array(
	                'format' => '_format_stock_items',
	                'spec_option' => $all_option===true ?  _("All Items") : $all_option,
	                'spec_id' => $all_items,
			'search_box' => true,
	        	'search' => array("sm.stock_id", "c.description","sm.description"),
	                'search_submit' => get_company_pref('no_item_list')!=0,
	                'size'=>10,
	                'select_submit'=> $submit_on_change,
	                'category' => 2,
	                'order' => array('c.description','sm.stock_id')
	          ), $opts) );
			echo $ret;
	  	//echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
	        //        array('cells'=>true, 'show_inactive'=>$all), $editkey);
		end_table(); 
		submit_center( "exported_rest_products", "Export" );
		$this->call_table( 'exported_rest_products', "Send Product via REST to WOO" );
		end_form();
	}
	/************************************************************************
	 *
	 *	exported_rest_products_form
 	 *	This Function exports a product via REST to Woo
	 *
	 ************************************************************************/
	function exported_rest_products_form_poc()
	{
/*
		require_once( 'class.woo_product.php' );
		
		$woo_prod = new woo_product( $this->woo_server, $this->woo_rest_path, $this->woo_ck, $this->woo_cs );
		
			$woo_prod->name = "Test Product";
			$woo_prod->slug = "test-product";
			$woo_prod->type = "simple";
			$woo_prod->status = "publish";
			$woo_prod->featured = "0";
			$woo_prod->catalog_visibility = "visible";
			$woo_prod->description = "Test Product desc";
			$woo_prod->short_description = "test prod short desc";
			$woo_prod->sku = "p-tp2";
			$woo_prod->regular_price = "9.99";
			$woo_prod->virtual = "0";
			$woo_prod->downloadable = "0";
			//$woo_prod->downloads = "";
			//$woo_prod->download_limit = "";
			//$woo_prod->download_expiry = "";
			//$woo_prod->download_type = "";
			//$woo_prod->external_url = "";
			//$woo_prod->button_text = "";
			$woo_prod->tax_status = "taxable";
			$woo_prod->tax_class = "GST";
			$woo_prod->manage_stock = "1";
			$woo_prod->stock_quantity = "2";
			$woo_prod->in_stock = "1";
			$woo_prod->backorders = "yes";
			$woo_prod->sold_individually = "0";
			$woo_prod->weight = "1.0";
			//$woo_prod->dimensions = array();
			//$woo_prod->shipping_class = "";
			$woo_prod->reviews_allowed = "1";
			//$woo_prod->upsell_ids = "";
			//$woo_prod->cross_sell_ids = "";
			//$woo_prod->parent_id = "";
			$woo_prod->purchase_note = "";
			//$woo_prod->categories = array("test-products");
			//$woo_prod->tags = array("");
			//$woo_prod->images = array("");
			//$woo_prod->attributes = array("");
			//$woo_prod->default_attributes = "";
			//$woo_prod->variations = array("");
			$woo_prod->menu_order = "1";
		$woo_prod->create_product();
 */	
	}

}
?>
