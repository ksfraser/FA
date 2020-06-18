<?php

require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 

/********************************************************************
* This class is acting like a controller
*
* This class is the MASTER controller for the module (called by EXPORT_WOO.php
*	which is called by hooks.php
*
*********************************************************************/

class EXPORT_WOO extends generic_fa_interface
{
	var $include_header;
	var $maxrowsallowed;
	var $lastoid;
	var $mailto;
	var $mailfrom;
	var $db;
	var $woo_ck;
	var $woo_cs;
	var $woo_server;
	var $woo_rest_path;
	var $environment;
	var $maxpics;	//!< int maximum number of pics for a product.  Integrate into module allowing more than 1!
	var $debug;
	var $test_max_send;
	var $force_update; //!< bool update ALL products/... rather than only find ones with a timestamp newer here than our record with Woo
	var $modules;		//!< array of modules for building menues, triggerring maintenance activities
	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		simple_page_mode(true);
		global $db;
		$this->db = $db;
		//echo "EXPORT_WOO constructor";
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		$this->set_var( 'vendor', "EXPORT_WOO" );
		$this->set_var( 'include_header', TRUE );
		//Pref can only be 14
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'include_header', 'label' => 'Include Headers' );
		$this->config_values[] = array( 'pref_name' => 'maxrowsallowed', 'label' => 'Maximum Rows Allowed in file' );
		$this->config_values[] = array( 'pref_name' => 'mailto', 'label' => 'Mail CSV to email address' );
		$this->config_values[] = array( 'pref_name' => 'mailfrom', 'label' => 'Mail from email address' );
		$this->config_values[] = array( 'pref_name' => 'image_serverurl', 'label' => 'Server URL for images (http[s]://servername/FA_base)' );
		$this->config_values[] = array( 'pref_name' => 'image_baseurl', 'label' => 'Base URL for images (/company/0/images/)' );
		$this->config_values[] = array( 'pref_name' => 'use_img_baseurl', 'label' => 'Use Base URL or remote (true/false)' );
		$this->config_values[] = array( 'pref_name' => 'woo_server', 'label' => 'Base URL (exact match wp_siteurl) for WOO server (...wordpress)' );	//Mantis 212
		$this->config_values[] = array( 'pref_name' => 'woo_rest_path', 'label' => 'Path for REST API ("/wp-json/wc/v1/)' );
		$this->config_values[] = array( 'pref_name' => 'environment', 'label' => 'Environment (devel/accept/prod)' );
		$this->config_values[] = array( 'pref_name' => 'maxpics', 'label' => 'Maximum number of pics to upload', 'integration_module' => '' );
		$this->config_values[] = array( 'pref_name' => 'force_update', 'label' => 'Force an Update even if timestamps don\'t require one (0/1)', 'integration_module' => '' );
		$this->config_values[] = array( 'pref_name' => 'remote_img_srv', 'label' => 'Is the images stored on a remote server? (Assume we copied from images dir)(0/1)', 'integration_module' => '' );
		$this->config_values[] = array( 'pref_name' => 'send_images', 'label' => 'Should we send images?(0/1)', 'integration_module' => '' );
		$this->config_values[] = array( 'pref_name' => 'woo_ck', 'label' => 'WOO api Key (ck_...)' );
		$this->config_values[] = array( 'pref_name' => 'woo_cs', 'label' => 'WOO api Secret (cs_...)' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		$this->config_values[] = array( 'pref_name' => 'test_max_send', 'label' => 'Max Send during test(1+)' );

		$this->config_values[] = array( 'pref_name' => 'woo_server2', 'label' => 'Base URL for 2nd WOO server (...wordpress)' );
		$this->config_values[] = array( 'pref_name' => 'woo_rest_path2', 'label' => 'Path for REST API 2nd  ("/wp-json/wc/v1/)' );
		$this->config_values[] = array( 'pref_name' => 'woo_ck2', 'label' => 'WOO api Key 2nd (ck_...)' );
		$this->config_values[] = array( 'pref_name' => 'woo_cs2', 'label' => 'WOO api Secret 2nd (cs_...)' );
		$this->config_values[] = array( 'pref_name' => 'environment2', 'label' => 'Environment 2nd (devel/accept/prod)' );

		$this->config_values[] = array( 'pref_name' => 'woo_server3', 'label' => 'Base URL for 3nd WOO server (...wordpress)' );
		$this->config_values[] = array( 'pref_name' => 'woo_rest_path3', 'label' => 'Path for REST API 3nd  ("/wp-json/wc/v1/)' );
		$this->config_values[] = array( 'pref_name' => 'woo_ck3', 'label' => 'WOO api Key 3nd (ck_...)' );
		$this->config_values[] = array( 'pref_name' => 'woo_cs3', 'label' => 'WOO api Secret 3nd (cs_...)' );
		$this->config_values[] = array( 'pref_name' => 'environment3', 'label' => 'Environment 3nd (devel/accept/prod)' );

		$this->config_values[] = array( 'pref_name' => 'woo_server4', 'label' => 'Base URL for 4nd WOO server (...wordpress)' );
		$this->config_values[] = array( 'pref_name' => 'woo_rest_path4', 'label' => 'Path for REST API 4nd  ("/wp-json/wc/v1/)' );
		$this->config_values[] = array( 'pref_name' => 'woo_ck4', 'label' => 'WOO api Key 4nd (ck_...)' );
		$this->config_values[] = array( 'pref_name' => 'woo_cs4', 'label' => 'WOO api Secret 4nd (cs_...)' );
		$this->config_values[] = array( 'pref_name' => 'environment4', 'label' => 'Environment 4nd (devel/accept/prod)' );
		
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'create', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Reinstall (reset) Woo', 'action' => 'reset_store', 'form' => 'form_reset_store', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables', 'action' => 'init_tables_form', 'form' => 'init_tables_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables Completed', 'action' => 'init_tables_complete_form', 'form' => 'init_tables_complete_form', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Products Export Prep', 'action' => 'productsexport', 'form' => 'form_products_export', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Products Export Prepped', 'action' => 'pexed', 'form' => 'form_products_exported', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'QOH Populated', 'action' => 'qoh', 'form' => 'populate_qoh', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'WOO Populated', 'action' => 'woo', 'form' => 'populate_woo', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Send Products', 'action' => 'send_products_rest_display', 'form' => 'send_products_rest_display', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Send Pictures', 'action' => 'send_pictures_rest_display', 'form' => 'send_pictures_rest_display', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Missing Products from internal WOO table', 'action' => 'missingwoo', 'form' => 'missing_woo', 'hidden' => FALSE );
		//$this->tabs[] = array( 'title' => 'Manually add Woo ID to a product in the internal WOO table', 'action' => 'form_add_woo_id_to_sku', 'form' => 'form_add_woo_id_to_sku', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Manually add Woo ID to a product in the internal WOO table', 'action' => 'add_woo_id_to_sku', 'form' => 'add_woo_id_to_sku', 'hidden' => TRUE  );
		
		$this->tabs[] = array( 'title' => 'Send Categories to WOO', 'action' => 'send_categories_form', 'form' => 'send_categories_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Categories Sent to WOO', 'action' => 'sent_categories_form', 'form' => 'sent_categories_form', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Products REST Export', 'action' => 'export_rest_products', 'form' => 'export_rest_products_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Products REST Exported', 'action' => 'exported_rest_products', 'form' => 'exported_rest_products_form', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Pictures Sent', 'action' => 'exported_rest_pictures_form', 'form' => 'exported_rest_pictures_form', 'hidden' => TRUE );


		//We could be looking for plugins here, adding menu's to the items.
		$moduledir = dirname( __FILE__ ) . '/modules';
		$this->add_submodules( $moduledir );	//This calls eventloop, loads modules.
		if( isset( $this->eventloop->tabs ) AND count( $this->eventloop->tabs ) > 0 )
		{
			//could probably do an array_merge instead...
			foreach( $this->eventloop->tabs as $tab )
			{
				$this->tabs[] = $tab;
			}
		}
	}
	function init_tables_form()
	{
            	display_notification("init tables form");
		$this->call_table( 'init_tables_complete_form', "Init Tables" );
	}
	/*******************************************************************************************//**
	 *fix_stock_id_size
	 *
	 *	Because of variation products (have attributes such as size and color) where 
	 *	everything else is the same, we want mostly human readable SKUs so that we
	 *	can easily type them in without having to hunt for them.  This requires longer
	 *	SKU lengths.  Default of 20 doesn't work for us anymore.
	 *
	 *	This function alters all of the tables (that we know of) to have the longer
	 *	length.  The Length is defined in an include file where we are putting
	 *	lengths so we can do this to other tables as needed too.
	 *
	 * **********************************************************************************************/
	function fix_stock_id_size()
	{
		require_once( '../ksf_modules_common/defines.inc.php' );
		if( include_once( '../ksf_data_dictionary/class.ksf_data_dictionary.php' ) )
		{
			$dd = new ksf_data_dictionary( $this->host, $this->user, $this->pass, $this->database, $this->prefs_tablename, $this );
			$dd->fix_stock_id_size();
		}
	}
	function init_tables_complete_form()
	{
		global $path_to_root;
		$createdcount = 0;
		require_once( 'class.woo_category.php' );
		$category_xref_address = new woo_category($this->woo_server, "", $this->woo_ck, $this->woo_cs, null, $this );
		if( $category_xref_address->create_table() )
			$createdcount++;

		$this->fix_stock_id_size();
		$this->create_table_woo_prod_variable_master();
		$this->create_table_woo_prod_variable_sku_combos();
		$this->create_table_woo_prod_variable_sku_full();
		$this->create_table_woo_prod_variable_child();
		$this->create_table_woo_prod_variable_variables();
		$this->create_table_woo_prod_variables_values();
		require_once( 'class.woo_orders.php' );
		$orders = new woo_orders($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $orders->create_table() )
			$createdcount++;

		$this->create_table_woo_prod_variable_master();
		$this->create_table_woo_prod_variable_sku_combos();
		$this->create_table_woo_prod_variable_sku_full();
		$this->create_table_woo_prod_variable_child();
		$this->create_table_woo_prod_variable_variables();
		$this->create_table_woo_prod_variables_values();
		require_once( 'class.woo_orders.php' );
		$orders = new woo_orders($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $orders->create_table() )
			$createdcount++;

		require_once( 'class.woo.php' );
		$woo = new woo($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $woo->create_table() )
			$createdcount++;

		//Check for external module and use it if possible
		if( @include_once( '../ksf_qoh/class.ksf_qoh.php' ) )
		{
			//Independant module.  This module is where all
			//future development for QOH will happen.
			include_once($path_to_root . "/modules/ksf_qoh/ksf_qoh.inc.php"); //KSF_QOH_PREFS
/*
		require_once( 'class.woo_line_items.php' );
		$line_items = new woo_line_items($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $line_items->create_table() )
			$createdcount++;
		require_once( 'class.woo_tax_lines.php' );
		$tax_lines = new woo_tax_lines($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $tax_lines->create_table() )
			$createdcount++;
		require_once( 'class.woo_shipping_lines.php' );
		$shipping_lines = new woo_shipping_lines($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $shipping_lines->create_table() )
			$createdcount++;
		require_once( 'class.woo_billing.php' );
		$billing_address = new woo_billing($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $billing_address->create_table() )
			$createdcount++;
		require_once( 'class.woo_fee_lines.php' );
		$fee_lines = new woo_fee_lines($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $fee_lines->create_table() )
			$createdcount++;
		require_once( 'class.woo_coupon_lines.php' );
		$coupon_lines = new woo_coupon_lines($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $coupon_lines->create_table() )
			$createdcount++;
       		require_once( 'class.woo_shipping.php' );
		$shipping_address = new woo_shipping($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $shipping_address->create_table() )
			$createdcount++;

		require_once( 'class.woo_customer.php' );
		$customer = new woo_customer($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $customer->create_table() )
			$createdcount++;
		require_once( 'class.woo_shipping_address.php' );
		$shipping_address = new woo_shipping_address($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if(  $shipping_address->create_table() )
			$createdcount++;
		require_once( 'class.woo_billing_address.php' );
		$billing_address_address = new woo_billing_address($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $billing_address_address->create_table() )
			$createdcount++;
*/

			$qoh = new ksf_qoh( KSF_QOH_PREFS );
			$qoh->install();
				$createdcount++;
		}
		else if( @include_once( 'class.qoh.php' ) )
		{
			$qoh = new qoh($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
			if( $qoh->create_table() )
				$createdcount++;
		}

/*
		require_once( 'class.woo_line_items.php' );
		$line_items = new woo_line_items($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $line_items->create_table() )
			$createdcount++;
		require_once( 'class.woo_tax_lines.php' );
		$tax_lines = new woo_tax_lines($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $tax_lines->create_table() )
			$createdcount++;
		require_once( 'class.woo_shipping_lines.php' );
		$shipping_lines = new woo_shipping_lines($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $shipping_lines->create_table() )
			$createdcount++;
		require_once( 'class.woo_billing.php' );
		$billing_address = new woo_billing($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $billing_address->create_table() )
			$createdcount++;
		require_once( 'class.woo_fee_lines.php' );
		$fee_lines = new woo_fee_lines($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $fee_lines->create_table() )
			$createdcount++;
		require_once( 'class.woo_coupon_lines.php' );
		$coupon_lines = new woo_coupon_lines($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $coupon_lines->create_table() )
			$createdcount++;
       		require_once( 'class.woo_shipping.php' );
		$shipping_address = new woo_shipping($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $shipping_address->create_table() )
			$createdcount++;

		require_once( 'class.woo_customer.php' );
		$customer = new woo_customer($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $customer->create_table() )
			$createdcount++;
		require_once( 'class.woo_shipping_address.php' );
		$shipping_address = new woo_shipping_address($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if(  $shipping_address->create_table() )
			$createdcount++;
		require_once( 'class.woo_billing_address.php' );
		$billing_address_address = new woo_billing_address($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $billing_address_address->create_table() )
			$createdcount++;
*/

		require_once( 'class.woo_prod_variation_attributes.php' );
		$category_xref_address = new woo_prod_variation_attributes($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $category_xref_address->create_table() )
			$createdcount++;
		require_once( 'class.woo_categories_xref.php' );
		$category_xref_address = new woo_categories_xref($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $category_xref_address->create_table() )
			$createdcount++;
		require_once( 'class.woo_coupons.php' );
		$coupons = new woo_coupons($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $coupons->create_table() )
			$createdcount++;
		if( isset( $this->eventloop ) )
			$this->eventloop->ObserverNotify( $this, 'NOTIFY_INIT_TABLES', $tabs );

       			
     	display_notification("init tables complete form created " . $createdcount . " tables");
	}
/*
	function create_table_woo_prod_variable_master()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_prod_variable_master.php' );
		$wpvm = new woo_prod_variable_master( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->create_table(); 
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_woo_product_variable_master_form()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_prod_variable_master.php' );
		$wpvm = new woo_prod_variable_master($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->master_form();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_table_woo_prod_variable_sku_full()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_prod_variable_sku_full.php' );
		$wpvm = new woo_prod_variable_sku_full( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->create_table(); 
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_woo_product_variable_sku_full_form()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_prod_variable_sku_full.php' );
		$wpvm = new woo_prod_variable_sku_full($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->master_form();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_table_woo_prod_variable_sku_combos()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_prod_variable_sku_combos.php' );
		$wpvm = new woo_prod_variable_sku_combos( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->create_table(); 
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_woo_product_variable_sku_combos_form()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_prod_variable_sku_combos.php' );
		$wpvm = new woo_prod_variable_sku_combos($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->master_form();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_table_woo_prod_variable_variables()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_prod_variable_variables.php' );
		$wpvm = new woo_prod_variable_variables( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->create_table(); 
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_woo_product_variable_variables_form()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_prod_variable_variables.php' );
		$wpvm = new woo_prod_variable_variables($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->master_form();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_table_woo_prod_variables_values()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_prod_variables_values.php' );
		$wpvm = new woo_prod_variables_values( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->create_table(); 
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_woo_product_variables_values_form()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_prod_variables_values.php' );
		$wpvm = new woo_prod_variables_values($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->master_form();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_table_woo_prod_variable_child()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		$table_details = array();
		$fields_array = array();
		$fields_array[] = array('name' => 'id_woo_prod_variable_child', 'type' => 'int(11)', 'auto_increment' => 'TRUE' );
		$fields_array[] = array('name' => 'master_stock_id', 'type' => 'varchar(32)' );
		$fields_array[] = array('name' => 'child_stock_id', 'type' => 'varchar(32)' );
		$fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		//$table_details['tablename'] = TB_PREF . "woo_categories_xref";
		$table_details['tablename'] = $this->company_prefix . "woo_prod_variable_child";
		//$table_details['primarykey'] = "fa_cat";
		$table_details['index'][0] = array( 'name' => "idx_master_child", 'columns' => "master_stock_id, child_stock_id" );

		$this->create_table( $table_details, $fields_array );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function create_coupons_form()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo_coupons.php' );
		$coupons = new woo_coupons( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$coupons->debug = $this->debug;
		$coupons->coupons_form();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function created_coupons_form()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
*/
	function form_reset_store()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		$this->call_table( 'reset_store_act', "Reset the Woocommerce Store" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function reset_store_act()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
	/*	foreach( $this->modules as $module )
		{
			$module->rebuild_woocommerce();
		}
	*/
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
			
	}
	function form_products_export()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		//$this->call_table( 'pexed', "Export" );
		$this->call_table( 'qoh', "QOH" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function call_table( $action, $msg )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
                start_form(true);
                 start_table(TABLESTYLE2, "width=40%");
                 table_section_title( $msg );
                 hidden('action', $action );
                 end_table(1);
                 submit_center( $action, $msg );
                 end_form();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************************
	*
	*	Function missing_woo
	*
	*	To show which products did not make it into the end table.
	*	Causes:
	*		No Transactions (no inventory)
	*		Missing Price 1 (Retail)
	*
	*	Allow user to update the items based upon missing data...
	*
	************************************************************************/
	function missing_woo()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo.php' );
		$woo = new woo( null, null, null, null, $this );
		$woo->missing_from_table();	
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************************
	* Add the Woo_id to a product manually
	*
	*	In our testing there is a couple of products that won't match
	*	up nicely in the search so can't have the invalid woo_id added.
	*	This will let us do it manually.
	*
	***********************************************************************/
	function form_add_woo_id_to_sku()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo.php' );
		$woo = new woo( null, null, null, null, $this );
		$woo->form_add_woo_id_to_sku();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************************
	* Add the Woo_id to a product manually
	*
	*	In our testing there is a couple of products that won't match
	*	up nicely in the search so can't have the invalid woo_id added.
	*	This will let us do it manually.
	*
	***********************************************************************/
	function add_woo_id_to_sku()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		require_once( 'class.woo.php' );
		$woo = new woo( null, null, null, null, $this );
		$woo->add_woo_id_to_sku();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function sales_pricing()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entered " . __METHOD__, "WARN" );
		global $path_to_root;
/*
		start_table(TABLESTYLE_NOBORDER);
        	start_row();
    		stock_items_list_cells(_("Select an item:"), 'stock_id', null,
          		_('New item'), true, check_value('show_inactive'));
        	check_cells(_("Show inactive:"), 'show_inactive', null, true);
        	end_row();
        	end_table();

        	if (get_post('_show_inactive_update')) {
                	$Ajax->activate('stock_id');
                	set_focus('stock_id');
        	}
		else
		{
        		hidden('stock_id', get_post('stock_id'));
		}
*/

		div_start('details');
		
		$stock_id = get_post('stock_id');
		if (!$stock_id)
		        unset($_POST['_tabs_sel']); // force settings tab for new customer
		tabbed_content_start('tabs', array(
		                'sales_pricing' => array(_('S&ales Pricing'), $stock_id),
		                'standard_cost' => array(_('Standard &Costs'), $stock_id),
		                'movement' => array(_('&Transactions'), $stock_id),
		));
		
		switch (get_post('_tabs_sel')) {
		        default:
			case 'sales_pricing':
		        	$_GET['stock_id'] = $stock_id;
		                $_GET['popup'] = 1;
		                include_once($path_to_root."/inventory/prices.php");
		                break;
		        case 'standard_cost':
		                $_GET['stock_id'] = $stock_id;
		                $_GET['popup'] = 1;
		                include_once($path_to_root."/inventory/cost_update.php");
		                break;
		        case 'movement':
		                $_GET['stock_id'] = $stock_id;
		                $_GET['popup'] = 1;
		                include_once($path_to_root."/inventory/inquiry/stock_movements.php");
		                break;
	 	};
		br();
		tabbed_content_end();
		div_end();
		
		hidden('popup', @$_REQUEST['popup']);
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function populate_qoh()
	{
		global $path_to_root;
		if( @include_once( '../ksf_qoh/class.ksf_qoh.php' ) )
		{
			//Independant module.  This module is where all
			//future development for QOH will happen.
			include_once($path_to_root . "/modules/ksf_qoh/ksf_qoh.inc.php"); //KSF_QOH_PREFS
			$qoh = new ksf_qoh( KSF_QOH_PREFS );
		}
		else if( @require_once( 'class.qoh.php' ) )
		{
			$qoh = new qoh( null, null, null, null, $this);
			
		}
		if( isset( $qoh ) )
		{
			$qoh->form_QOH_completed();
			$this->call_table( 'woo', "WOO" );
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/************************************************************************
	 *
	 *	Populate WOO
 	 *	This Function populates the WOO table.
	 *
	 *	TODO:
	 *		Alter table to have Virtual and Downloadable columns
	 *		Alter table to have Shipping data (weight, dimensions)
	 *		Create Query that updates Virtual (service) products
	 *		Create Query that updates Downloadable products
	 *		Create Query that updates Shipping data
	 *		Once the CRM is in place, alter table for crosssell and upsell
	 *		Create Query to update crosssell and upsell
	 *
	 ************************************************************************/
	function populate_woo()
	{
		require_once( 'class.model_woo.php' );
		$mwoo = new model_woo( null, null, null, null, $this );
		display_notification("WOO");
		$rowcount = $mwoo->populate_woo_table();
            	display_notification("$rowcount rows of items exist.");
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	//}
		$this->call_table( 'exported_rest_products', "Send Products via REST to WOO" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function send_products_rest_display()
	{		
		$this->call_table( 'exported_rest_products', "Send Products via REST to WOO" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function send_pictures_rest_display()
	{		
		$this->call_table( 'exported_rest_pictures_form', "Send Pictures via REST to WOO" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************************
	*
	*	Function send_categories_form()	
	*	
	*	To present the user with a button to launch the sending of categories
	*	to WOO.
	*
	************************************************************************/
	function send_categories_form()
	{
		$this->call_table( "sent_categories_form", "Send Categories to WOO" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************************
	*
	*	Function sent_categories_form()	
	*	
	*	To send categories that aren't already sent (as indicated by ...xref
	*	to WOO.
	*
	************************************************************************/
	function sent_categories_form()
	{
		require_once( 'class.woo_category.php' );
		$woo_category = new woo_category( $this->woo_server, $this->woo_rest_path, $this->woo_ck, $this->woo_cs, null, $this, "devel" );
		$woo_category->debug = $this->debug;
		$ret = $woo_category->send_categories_to_woo( $this->company_prefix );
		display_notification( "Sent " . $ret . " categories to WooCommerce" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
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
		
		while( $prod_data = db_fetch_assoc( $res ) )
		{
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
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************************
	*
	*	Function imported_customer_form()
	*
	*	Function that sends the customer to WOO
	*
		
	*
	************************************************************************/
	function imported_customer_form()
	{
		require_once( 'class.woo_customer.php' );
		
		$woo_customer = new woo_customer( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$woo_customer->debug = 1;
		$extractcount = $woo_customer->get_customer();	
            	display_notification( $extractcount . " Customer Imported.");
		$this->call_table( 'import_order', "Import Another" );
		$this->call_table( 'import_customer', "Import ALL Another" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function import_customer_form()
	{
		$this->call_table( 'imported_customer_form', "Get Customer from WOO" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************************
	*
	*	Function exported_customer_form()
	*
	*	Function that sends the customer to WOO
	*
		
	*
	************************************************************************/
	function exported_customer_form()
	{
		require_once( 'class.woo_customer.php' );
		
		$woo_customer = new woo_customer( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$woo_customer->debug = $this->debug;
	
            	display_notification("Customer Exported.");
		$this->call_table( 'export_order', "Export Another" );
		$this->call_table( 'export_customer', "Export ALL Another" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function export_customer_form()
	{
		$this->call_table( 'exported_customer_form', "Send Customer to WOO" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************************
	*
	*	Function imported_orders_form()
	*
	*	Function that sends the orders to WOO
	*
		
	*
	************************************************************************/
	function imported_orders_form()
	{
		require_once( 'class.woo_orders.php' );
		
		$woo_orders = new woo_orders( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$woo_orders->debug = 1;
		$extractcount = $woo_orders->get_orders();	
            	display_notification( $extractcount . " Orders Imported.");
		$this->call_table( 'import_order', "Import Another" );
		$this->call_table( 'import_orders', "Import ALL Another" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function import_orders_form()
	{
		$this->call_table( 'imported_orders_form', "Get Orders from WOO" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************************
	*
	*	Function exported_orders_form()
	*
	*	Function that sends the orders to WOO
	*
		
	*
	************************************************************************/
	function exported_orders_form()
	{
		require_once( 'class.woo_orders.php' );
		
		$woo_orders = new woo_orders( $this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$woo_orders->debug = $this->debug;
	
            	display_notification("Orders Exported.");
		$this->call_table( 'export_order', "Export Another" );
		$this->call_table( 'export_orders', "Export ALL Another" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function export_orders_form()
	{
		$this->call_table( 'exported_orders_form', "Send Orders to WOO" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function exported_rest_simple_products()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
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
		$woo_product = new woo_product( $this->woo_server, $this->woo_rest_path, $this->woo_ck, $this->woo_cs, $this->environment, $this );
		$woo_product->debug = $this->debug;
		$sentcount = $woo_product->send_products();
		//$sentcount = $woo_product->send_simple_products();
		//If we haven't timed out...
		$updatecount = $woo_product->update_simple_products();

            	display_notification( $sentcount . " Products sent and " . $updatecount . " updated.");
		$this->call_table( 'export_rest_product', "Export Another" );
		$this->call_table( 'export_rest_products', "Export ALL Another" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***********************************************************************
	*
	*	Function exported_rest_pictures_form()
	*
	*	Function that sends pictures to WOO
	*
		
	*
	************************************************************************/
	function exported_rest_pictures_form()
	{
		require_once( 'class.woo_product.php' );
		$woo_product = new woo_product( $this->woo_server, $this->woo_rest_path, $this->woo_ck, $this->woo_cs, $this->environment, $this );
		$woo_product->debug = $this->debug;

		require_once( 'class.model_woo.php' );
		$model_woo = new model_woo( $woo_product->serverURL, $woo_product->key, $woo_product->secret, $woo_product->options, $this );
		$model_woo->debug = $this->debug;	

		$sentcount = 0;
		$res = $model_woo->all_product_ids( $this->test_max_send );	//returns only products that have a woo_id (i.e. on WC)
		foreach( $res as $stock_id )
		{
			$woo_product->send_images( $stock_id, $this );
			$sentcount++;
		}
            	display_notification( $sentcount . " Products sent Pictures" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
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
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function form_pricebook()
	{
		require_once( 'class.woo.php' );
		$woo = new woo( null, null, null, null, $this );
		$woo->create_price_book( $this->mailto, $this->mailfrom );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
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
 */	
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function export_file_form()
	{
		$this->call_table( 'pexed', "Create Export File for WOO" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/************************************************************************
	 *
	 *	form Products Exported
 	 *	This Function creates the CSV file of exported products
	 *
	 ************************************************************************/
	function form_products_exported()
	{
		//$this->populate_qoh();
		//$this->populate_woo();

		$this->form_pricebook();
		$fname = $this->vendor . '_ALL.csv';
		$filename = '../../tmp/' . $fname;
/*
 *	WooCommerce Fields:
 *		Title
 *		Description
 *		Short Description
 *		SKU
 *		Price
 *		Sale Price
 *		Virtual
 *		Downloadable
 *		Tax Status (Taxable, Not Taxable, Shipping Only)
 *		Tax Class (Standard, Reduced, Zero)
 *		Manage Stock (Y/N)
 *		Stock Qty
 *		Allow Backorders
 *		Sold Individually
 *		Shipping Weight
 *		Shipping Dimensions (Length, Width, Height)
 *		Shipping Class
 *		Linked Products (Cross Sell, Upsell - SKUs comma separated)
 *		Attributes (Name, Value)
 *		Image URL
 *		Product Categories
 *		Product Tags
 */
		$fp = $this->open_write_file( $filename );
			$hline  = '"stock_id",';
			$hline  .= '"SKU",';
			$hline  .= '"Image_URL",';
			//$hline .= '"category_id",';
			$hline .= '"category",';
			$hline .= '"Title",';
			$hline .= '"description",';
			$hline .= '"long_description",';
			//$hline .= '"units",';
			$hline .= '"price",';
			//$hline .= '"saleprice",';
			$hline .= '"instock",';
			$hline .= '"managestock",';
			$hline .= '"virtual",';
			$hline .= '"downloadable",';
			$hline .= '"tax_status",';
			$hline .= '"tax_class",';
			$hline .= '"allow_backorders",';
			$hline .= '"shipping_weight",';
			$hline .= '"shipping_length",';
			$hline .= '"shipping_width",';
			$hline .= '"shipping_height",';
			$hline .= '"shipping_class",';
			$hline .= '"cross_sell_SKUs",';
			$hline .= '"upsell_SKUs",';
			$hline .= '"tags",';
			$this->write_line( $fp, $hline );
		fflush( $fp );

		$woo = "select * from " . TB_PREF . "woo";
		$result = db_query( $woo, "Couldn't grab inventory to export" );

		$rowcount=0;
		while ($row = db_fetch($result)) 
		{
			$line  = '"' . $row['stock_id'] . '",';
			$line  .= '"' . $row['stock_id'] . '",';
			//$line  .= '"http://fraserhighlandshoppe.ca/images/' . $row['stock_id'] . '.jpg",';
			if( isset( $this->image_serverurl ) AND isset( $this->image_baseurl ) )
			{
				$line  .= $this->image_serverurl . '/' . $this->image_baseurl . '/' . $row['stock_id'] . '.jpg",';
			}
			else
			{
				$line  .= '"https://defiant.ksfraser.com/fhs/frontaccounting/company/0/images/' . $row['stock_id'] . '.jpg",';
			}
			//$line .= '"' . $row['category_id'] . '",';
			$line .= '"' . $row['category'] . '",';
			$line .= '"' . $row['description'] . '",';
			$line .= '"' . $row['description'] . '",';
			$line .= '"' . $row['long_description'] . '",';
			//$line .= '"' . $row['units'] . '",';
			$line .= '"' . $row['price'] . '",';
			//$line .= '"' . $row['saleprice'] . '",';
			$line .= '"' . $row['instock'] . '",';
			$line .= '"1",';
			$line .= '"0",';
			$line .= '"0",';
			$line .= '"Taxable",';
			$line .= '"Standard",';
			$line .= '"1",';
			$line .= '"1",';	//Shipping weight
			$line .= '"1",';
			$line .= '"1",';
			$line .= '"1",';
			$line .= '"standard",';
			$line .= '"",';
			$line .= '"",';
			$line .= '"",';		//Tags
			$this->write_line( $fp, $line );
			$rowcount++;
		}
		$this->file_finish( $fp );
            	display_notification("$rowcount rows of items created.");
		if( isset( $this->mailto ) )
		{
			$data = file_get_contents( $filename );
			$uu_data = "begin 644 " . $fname . "\n" . convert_uuencode($data) . "end\n";
			$subject = $this->vendor . ' ALL Items CSV file';
			$headers = 'From: sales@fraserhighlandshoppe.ca' . "\r\n" .
			    'Reply-To: sales@fraserhighlandshoppe.ca' . "\r\n";
			mail($this->mailto, $subject, $uu_data, $headers);
            		display_notification("email sent to $this->mailto.");
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function export_orders()
	{
		/*
		 *	The first row is ignored
		 *	Needs MODEL NUMBER and QUANTITY
		 *	Any other columns are ignored (i.e. we can put a description as a third column)
		 *	Only 999 rows are accepted.
		 *	
		 *	If there are more than 999 rows, we will create only the first 999 lines
		 *	and set MAXOIDS to 999 so that this can be run a second time.
		 */
		$headers = array( 'Model', 'Quantity', 'Description' );

		/*
		 *	Table Purchase Orders
		 *		order_no
		 *		supplier_id
		 *
		 *	table Purchase Orders Details	
		 *		po_detail_item (index)
		 *		order_no
		 *		item_code (this is our code)
		 *		description
		 *		quantity_ordered
		 *
		 *
		 */
	
		$inserted = array();
		$ignored = array();
		$failed = array();
            	$ignoredrows = $rowcount = 0;
		if( !isset( $this->db_connection ) )
			$this->connect_db();	//connect to DB setting db_connection used below.
		if( !isset( $this->order_no ) )
		{
			if( isset( $_POST['order_no'] ) )
			{
				$this->set_var( 'order_no', $_POST['order_no'] );
			}
			else if( isset( $_GET['order_no'] ) )
			{
				$this->set_var( 'order_no', $_GET['order_no'] );
			}
			else
			{
				return FALSE;	//ERROR
			}
		}
		/*******************************************************************
		 *
		 *	Open the file and write the header row
		 *
		 *******************************************************************/
		$fname = $this->vendor . '_order_' . $this->order_no . '.csv';
		$filename = '../../tmp/' . $fname;
		$fp = fopen( $filename, 'w' );
		$hline = "";
		if( $this->include_header )
		{
			foreach( $headers as $column )
			{
				$hline .= $column . ",";
			}
			$this->write_line( $fp, $hline );
		}
		/******************************************************************
		 *
		 *	Should be able to use $this->get_purchase_order
		 *	so that this is generic - only need to change the output format...
		 *	
		 *	Could even add in mapping into the prefs so that we can
		 *	change the output on the fly.	
		 *
		 ******************************************************************/
		$this->get_purchase_order();
		foreach( $this->purchase_order->line_items as $po_line_details )
		{
			/*
			 *        var $line_no;
			 *        var $po_detail_rec;
			 *        var $grn_item_id;
			 *        var $stock_id;
			 *        var $item_description;
			 *        var $price;
			 *        var $units;
			 *        var $req_del_date;
			 *        var $tax_type;
			 *        var $tax_type_name;
			 *
			 *        var $quantity;          // current/entry quantity of PO line
			 *        var $qty_inv;   // quantity already invoiced against this line
			 *        var $receive_qty;       // current/entry GRN quantity
			 *        var $qty_received;      // quantity already received against this line
			 *
			 *        var $standard_cost;
			 *        var $descr_editable;
			 */

				$line = '"' . $po_line_details->stock_id . '","' . $po_line_details->quantity . '","' . $po_line_details->item_description . '","' . $po_line_details->line_no . '",';
				$this->write_line( $fp, $line );
				$rowcount++;
		}
		$this->file_finish( $fp );
            	display_notification("$rowcount rows of items created, $ignoredrows rows of items ignored, $this->maxrowsallowed rows allowed.");
		$this->set_pref( 'lastoid', $this->order_no );
		if( isset( $this->mailto ) )
		{
			$data = file_get_contents( $filename );
			$uu_data = "begin 644 " . $fname . "\n" . convert_uuencode($data) . "end\n";
			$subject = $this->vendor . ' Purchase Order CSV file';
			$headers = 'From: sales@fraserhighlandshoppe.ca' . "\r\n" .
			    'Reply-To: sales@fraserhighlandshoppe.ca' . "\r\n";

			mail($this->mailto, $subject, $uu_data, $headers);

		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
}

?>
