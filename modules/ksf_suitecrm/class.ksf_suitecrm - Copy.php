<?php

require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 


class ksf_suitecrm extends generic_fa_interface
{
	var $include_header;
	var $maxrowsallowed;
	var $lastoid;
	var $mailto;
	var $mailfrom;
	var $db;
	var $environment;
	var $maxpics;	//!< int maximum number of pics for a product.  Integrate into module allowing more than 1!
	var $debug;
	var $force_update; //!< bool update ALL products/... rather than only find ones with a timestamp newer here than our record with Woo
	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		simple_page_mode(true);
		global $db;
		$this->db = $db;
		//echo "ksf_suitecrm constructor";
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		$this->set_var( 'vendor', "ksf_suitecrm" );
		$this->set_var( 'include_header', TRUE );
		//Pref can only be 14
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'include_header', 'label' => 'Include Headers' );
		$this->config_values[] = array( 'pref_name' => 'maxrowsallowed', 'label' => 'Maximum Rows Allowed in file' );
		$this->config_values[] = array( 'pref_name' => 'mailto', 'label' => 'Mail CSV to email address' );
		$this->config_values[] = array( 'pref_name' => 'mailfrom', 'label' => 'Mail from email address' );
		$this->config_values[] = array( 'pref_name' => 'environment', 'label' => 'Environment (devel/accept/prod)' );
		$this->config_values[] = array( 'pref_name' => 'maxpics', 'label' => 'Maximum number of pics to upload', 'integration_module' => '' );
		$this->config_values[] = array( 'pref_name' => 'force_update', 'label' => 'Force an Update even if timestamps don\'t require one (0/1)', 'integration_module' => '' );
		$this->config_values[] = array( 'pref_name' => 'send_images', 'label' => 'Should we send images?(0/1)', 'integration_module' => '' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'create', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables', 'action' => 'init_tables_form', 'form' => 'init_tables_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables Completed', 'action' => 'init_tables_complete_form', 'form' => 'init_tables_complete_form', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Products Export Prep', 'action' => 'productsexport', 'form' => 'form_products_export', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Products Export Prepped', 'action' => 'pexed', 'form' => 'form_products_exported', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Missing Products from internal CRM table', 'action' => 'missingwoo', 'form' => 'missing_woo', 'hidden' => FALSE );
		
		$this->tabs[] = array( 'title' => 'Send Categories to CRM', 'action' => 'send_categories_form', 'form' => 'send_categories_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Categories Sent to CRM', 'action' => 'sent_categories_form', 'form' => 'sent_categories_form', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Export File', 'action' => 'exportfile', 'form' => 'export_file_form', 'hidden' => FALSE );

		//$this->tabs[] = array( 'title' => 'Customer Export', 'action' => 'export_customer_form', 'form' => 'export_customer_form', 'hidden' => FALSE );
		//$this->tabs[] = array( 'title' => 'Customer Exported', 'action' => 'exported_customer_form', 'form' => 'exported_customer_form', 'hidden' => TRUE );
		//$this->tabs[] = array( 'title' => 'Customer Import', 'action' => 'import_customer_form', 'form' => 'import_customer_form', 'hidden' => FALSE );
		//$this->tabs[] = array( 'title' => 'Customer Imported', 'action' => 'imported_customer_form', 'form' => 'imported_customer_form', 'hidden' => TRUE );

		//$this->tabs[] = array( 'title' => 'Coupons create', 'action' => 'create_coupons_form', 'form' => 'create_coupons_form', 'hidden' => FALSE );
		//$this->tabs[] = array( 'title' => 'Coupons created', 'action' => 'created_coupons_form', 'form' => 'created_coupons_form', 'hidden' => TRUE );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
	}
	function init_tables_form()
	{
            	display_notification("init tables form");
		$this->call_table( 'init_tables_complete_form', "Init Tables" );
	}
	function init_tables_complete_form()
	{
		global $path_to_root;
		$createdcount = 0;
		require_once( 'class.woo_category.php' );
		$category_xref_address = new woo_category($this->woo_server, "", $this->woo_ck, $this->woo_cs, null, $this );
		if( $category_xref_address->create_table() )
			$createdcount++;
     		display_notification("init tables complete form created " . $createdcount . " tables");
	}
	function create_woo_product_variables_values_form()
	{
		require_once( 'class.woo_prod_variables_values.php' );
		$wpvm = new woo_prod_variables_values($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->master_form();
	}
	function create_table_woo_prod_variable_child()
	{
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
	}
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
	/************************************************************************
	 *
	 *	Populate CRM
 	 *	This Function populates the CRM table.
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
}

?>
