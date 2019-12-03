<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( 'class.woo_interface.php' );
require_once( 'class.model_woo.php' );
//require_once( 'class.EXPORT_WOO.inc.php' ); //Constants

/******************************************************************//**
 * This class is a MVC Controller/View class - designed for handling the WOO table in frontaccounting.
 *
 * *******************************************************************/
class woo extends woo_interface {
		var $model;
		var $stock_id;
		var $updated_ts;
		var $woo_last_update;
		var $woo_id;
		var $category_id;
		var $category;
		var $woo_category_id;
		var $description;
		var $long_description;
		var $units;
		var $price;
		var $instock;
		var $saleprice;
		var $date_on_sale_from;
		var $date_on_sale_to;
		var $external_url;
		var $tax_status;
		var $tax_class;
		var $weight;
		var $length;
		var $width;
		var $height;
		var $shipping_class;
		var $upsell_ids;
		var $crosssell_ids;
		var $parent_id;
		var $attributes;
		var $default_attributes;
		var $variations;
		var $filter_new_only;
		var $force_update; //!< bool grabbed from client

	function __construct($serverURL, $key, $secret, $options, $client)
	{
		parent::__construct($serverURL, $key, $secret, $options, $client);
		$this->filter_new_only = FALSE;
		if( isset( $client->force_update ) )
			$this->force_update = $client->force_update;

		//$this->define_table();
		$this->model = new model_woo( $serverURL, $key, $secret, $options, $this );
		return;
	}
	function reset_endpoint()
	{
		$this->endpoint = "";
	}
	function define_table()
	{
		//$this->model->define_table();
	}
	/**************************************************************//**
	 * Select the details of 1 product.  Requires that stock_id is set
	 *
	 * ****************************************************************/
	/********
	*
		class.model_woo.php:    function select_product()
		class.woo.php:  function select_product()
		class.woo.php:          $this->model->select_product();
		class.woo_product.20170708.php:         $woo->select_product();
		class.woo_product.php:          $woo->select_product();
		class.woo_target_xref.php:      function select_product()
	*
	********/
	function select_product()
	{
		$this->model->select_product();
	}
	/*****************************************//**
	* Reset id and date so that we resend everything
	*
	********************************************/
	function clear_woocommerce_data()
	{
		$this->model->clear_woocommerce_data();
	}
	/********
	*
	*
	********/
	private function insert_product()
	{
		$res = $this->model->insert_product();
		$this->tell( WOO_PRODUCT_INSERT, __METHOD__ );
	}
	private function update_product_details()
	{
		$res = $this->model->update_product_details();
		$this->tell( WOO_PRODUCT_UPDATE, __METHOD__ );
	}
	private function update_prices()
	{
		$res = $this->model->update_prices();
		$this->tell( WOO_PRODUCT_PRICE_UPDATE, __METHOD__ );
	}
	private function zero_null_prices()
	{
		$res = $this->model->zero_null_prices();
		//$this->tell( WOO_PRODUCT_PRICE_NULL2ZERO, __METHOD__ );
	}
	/*********************************************************************//**
	 * This function updates the QOH count within the WOO table so the SQL statement here is OK.
	 *
	 * It is dependant on either an external QOH module or the included QOH
	 * class/table to hold the values of QOH by product.
	 *
	 * It is also dependant on all of the items ins stock_master have already
	 * been inserted into this table to be updated or the items will be missed.
	 *
	 * ***********************************************************************/
	private function update_qoh_count()
	{
		$res = $this->model->update_qoh_count();
		$this->tell( WOO_PRODUCT_QOH_UPDATE, __METHOD__ );
	}
	/********
	*
class.model_woo.php:    function update_on_sale_data()
class.woo.php:  function update_on_sale_data()
class.woo_product.20170708.php:         $woo->update_on_sale_data();
class.woo_product.php:                  $woo->update_on_sale_data();

	*
	********/
	function update_on_sale_data()
	{
		$res = $this->model->update_on_sale_data();
	}
	/********
	*
class.model_woo.php:    function update_woo_id()
class.woo.php:  function update_woo_id()
class.woo.php:          $res = $this->model->update_woo_id();
class.woo_product.20170708.php:         $woo->update_woo_id();
class.woo_product.php:                  $woo->update_woo_id();

	*
	********/
	function update_woo_id()
	{
		$res = $this->model->update_woo_id();
	}
	/********
	*
class.model_woo.php:    function update_woo_last_update()
class.woo.php:  function update_woo_last_update()
class.woo.php:          $res = $this->model->update_woo_last_update();
class.woo_product.20170708.php:         $woo->update_woo_last_update();
class.woo_product.php:                  $woo->update_woo_last_update();

	*
	********/
	function update_woo_last_update()
	{
		$res = $this->model->update_woo_last_update();
	}
	private function staledate_specials()
	{
		$res = $this->model->staledate_specials();
		//$this->tell( WOO_PRODUCT_STALEDATE_SPECIALS, __METHOD__ );
	}
	private function update_specials()
	{
		$res = $this->model->update_specials();
		$this->tell( WOO_PRODUCT_SPECIALS_UPDATE, __METHOD__ );
	}
	private function update_tax_data()
	{
		$res = $this->model->update_tax_data();
		$this->tell( WOO_PRODUCT_TAXDATA_UPDATE, __METHOD__ );
	}
	private function update_shipping_dimensions()
	{
		$res = $this->model->update_shipping_dimensions();
		$this->tell( WOO_PRODUCT_SHIPDIM_UPDATE, __METHOD__ );
	}
	/********
	*

	*
	********/
	function update_crosssells()
	{
		$res = $this->model->update_crosssells();
		$this->tell( WOO_PRODUCT_CROSSSELL_UPDATE, __METHOD__ );
	}
	/********
	*

	*
	********/
	function update_category_data()
	{
		$res = $this->model->update_category_data();
		$this->tell( WOO_PRODUCT_CATEGORY_UPDATE, __METHOD__ );
	}
	/********
	*

	*
	********/
	function update_category_xref()
	{
		$res = $this->model->update_category_xref();
		//$this->tell( WOO_PRODUCT_CATEGORY_XREF, __METHOD__ );
	}
	/*********************************************************************//**
	 *To show which products did not make it into the end table.
	 *
	*	Causes:
	*		No Transactions (no inventory)
	*		Missing Price 1 (Retail)
	*
	*	Allow user to update the items based upon missing data...
	* TROUBLESHOOTING:
		 	select sm.stock_id, sm.category_id, sc.description as category, sm.description, sm.long_description, sm.units, p.price, mv.instock 
              		from 0_stock_master sm, 0_prices p, 0_stock_category sc, 0_qoh mv, 0_woo_categories_xref woo
			where sm.stock_id = p.stock_id and p.sales_type_id='1' and mv.stock_id = sm.stock_id and sm.category_id = sc.category_id
			and sm.stock_id = 'hd-hat-hornpipe-51';
			select * from 0_qoh where stock_id = 'hd-hat-hornpipe-51';
 			select * from 0_woo where stock_id = 'hd-hat-hornpipe-51';
			*
			*
			* This function should be split into a data portion (model of MVC)
			* and a gui portion in a separate class (view of MVC).
	************************************************************************/
	/********
	*

	*
	********/
	function missing_from_table()
	{
            	display_notification("Missing from WOO");
		$missing_sql = $this->model->missing_from_table_query();
		 global $all_items;
		$selected_id = "0";
		$name = "";
		$editkey = TRUE;
		$opts = array('cells'=>true, 'show_inactive'=>'1');
		$all_option = FALSE;
		$submit_on_change = TRUE;
                set_editor('item', $name, $editkey);
		start_form();
		start_table();
		table_section_title(_("Possible Causes of problems leading to these items being missing from WOO"));
		label_row(_("Product is inactive (e.g. duplicate or depreciated)"), NULL);
		label_row(_("No retail price set (Price type 1) in prices:"), NULL);
		label_row(_("No total (instock) in QOH (stock_id not in stock_master or issues in stock_moves"), NULL);
		label_row(_("An issue with the Category description/id"), NULL);
	//	label_row(_("No Transaction History (no inventory movement):"), NULL);
		label_row("&nbsp;", NULL);
		label_row("Press F4 to pop open a window to edit the item details", null);
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
			echo "$ret";
		end_table();
		end_form();

	}
	/*******************************************************************************//**
	 * This function has been moved into ksf_generate_catalogue so we will call it from here
	 *
	 * This should be split into a VIEW class and a MODEL class.
	 * *********************************************************************************/
	/********
	*

	*
	********/
	function create_price_book()
	{
		if( @include_once( $path_to_root . "/modules/ksf_generate_catalogue/class.ksf_generate_catalogue.php" ) )
		{
			include_once($path_to_root . "/modules/ksf_generate_catalogue/ksf_generate_catalogue.inc.php"); //ksf_generate_catalogue_prefs
			$cat = new ksf_generate_catalogue( KSF_GENERATE_CATALOGUE_PREFS );
			$cat->create_price_book();
			$cat->email_price_book();
		}
		else 
		{
			display_warning( "This function depends on module ksf_generate_catalogue!" );
			return FALSE;
		}

	}
	/*****************************************************************//**
	 * Return a count of stock_ids belonging to products that are new
	 *
	 * @returns int count
	 * ******************************************************************/
	/********
	*

	*
	********/
	function count_new_products()
	{
		$count = $this->model->count_filtered( "woo_id = ''" );
		return $count;
	}
	/*****************************************************************//**
	 * Return an array of stock_ids belonging to simple products that are new
	 *
	 * @returns array stock_ids
	 * ******************************************************************/
	/********
	*

	*
	********/
	/*@array@*/function new_simple_product_ids()
	{
		$this->model->filter_new_only = TRUE;
		return $this->model->simple_product_ids();
	}
	/*****************************************************************//**
	 * Return an array of stock_ids belonging to simple products that are new
	 *
	 * @returns array stock_ids
	 * ******************************************************************/
	/********
	*

	*
	********/
	/*@array@*/function all_simple_product_ids()
	{
		$this->model->filter_new_only = FALSE;
		return $this->model->simple_product_ids();
	}
	/*****************************************************************//**
	 * Return an array of stock_ids belonging to simple products
	 *
	 * @returns array stock_ids
	 * ******************************************************************/
	/********
	*

	*
	********/
	/*@array@*/function simple_product_ids()
	{
		$res = $this->model->simple_product_ids();
		return $res;
	}
	/****************************************************************//**
	 * Runs an MySQL query returning the mysql_res of stock_ids
	 *
	 * @returns mysql_res
	 * ******************************************************************/
	/********
	*

	*
	********/
	/*@mysql_res@*/function select_simple_products_for_export()
	{
		return $this->select_simple_products();
	}
	/****************************************************************//**
	 * Runs an MySQL query returning the mysql_res of stock_ids
	 *
	 * Should be split into MODEL and VIEW classes
	 *   Could call VIEW function through db_query
	 * @returns mysql_res
	 * ******************************************************************/
	/********
	*

	*
	********/
	/*@mysql_res@*/function select_simple_products()
	{
		$res = $this->model->select_simple_products();
		return $res;
	}
	/****************************************************************//**
	 * Runs an MySQL query returning the mysql_res of stock_ids
	 *
	 * Should be split into MODEL and VIEW classes
	 *   Could call VIEW function through db_query
	 * @returns mysql_res
	 * ******************************************************************/
	/********
	*

	*
	********/
	/*@mysql_res@*/function select_simple_products_for_update()
	{
		$res = $this->model->select_simple_products_for_update();
		return $res;
	}
	/********
	*

	*
	********/
	function delete_by_sku( $sku )
	{
		$res = $this->model->delete_by_sku( $sku );
		$this->notify( "Deleted sku " . $sku, "NOTIFY" );
	}
}

?>
