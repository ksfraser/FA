<?php

//require_once( 'class.generic_orders.php' ); 
require_once( 'class.generic_fa_interface.php' ); 


//class EXPORT_WOO_PROD
//class EXPORT_WOO_PROD extends generic_orders
class EXPORT_WOO_PROD extends generic_fa_interface
{
	var $include_header;
	var $maxrowsallowed;
	var $lastoid;
	var $mailto;
	var $db;
	var $woo_ck;
	var $woo_cs;
	var $woo_server;
	var $woo_rest_path;
	var $environment;
	var $maxpics;
	var $debug;
	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		global $db;
		$this->db = $db;
		//echo "EXPORT_WOO_PROD constructor";
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		$this->set_var( 'vendor', "EXPORT_WOO_PROD" );
		$this->set_var( 'include_header', TRUE );
		
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'include_header', 'label' => 'Include Headers' );
		$this->config_values[] = array( 'pref_name' => 'maxrowsallowed', 'label' => 'Maximum Rows Allowed in file' );
		$this->config_values[] = array( 'pref_name' => 'mailto', 'label' => 'Mail CSV to email address' );
		$this->config_values[] = array( 'pref_name' => 'image_serverurl', 'label' => 'Server URL for images (http[s]://servername/FA_base)' );
		$this->config_values[] = array( 'pref_name' => 'image_baseurl', 'label' => 'Base URL for images (/company/0/images)' );
		$this->config_values[] = array( 'pref_name' => 'use_img_baseurl', 'label' => 'Use Base URL or remote (true/false)' );
		$this->config_values[] = array( 'pref_name' => 'woo_server', 'label' => 'Base URL for WOO server (...wordpress)' );
		$this->config_values[] = array( 'pref_name' => 'woo_rest_path', 'label' => 'Path for REST API ("/wp-json/wc/v1/)' );
		$this->config_values[] = array( 'pref_name' => 'environment', 'label' => 'Environment (devel/accept/prod)' );
		$this->config_values[] = array( 'pref_name' => 'maxpics', 'label' => 'Maximum number of pics to upload' );
		$this->config_values[] = array( 'pref_name' => 'woo_ck', 'label' => 'WOO api Key (ck_...)' );
		$this->config_values[] = array( 'pref_name' => 'woo_cs', 'label' => 'WOO api Secret (cs_...)' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'create', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables', 'action' => 'init_tables_form', 'form' => 'init_tables_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables Completed', 'action' => 'init_tables_complete_form', 'form' => 'init_tables_complete_form', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'All Products Export', 'action' => 'productsexport', 'form' => 'form_products_export', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'All Products Exported', 'action' => 'pexed', 'form' => 'form_products_exported', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'QOH Populated', 'action' => 'qoh', 'form' => 'populate_qoh', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'WOO Populated', 'action' => 'woo', 'form' => 'populate_woo', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Missing Products from internal WOO table', 'action' => 'missingwoo', 'form' => 'missing_woo', 'hidden' => FALSE );
		
		$this->tabs[] = array( 'title' => 'Send Categories to WOO', 'action' => 'send_categories_form', 'form' => 'send_categories_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Categories Sent to WOO', 'action' => 'sent_categories_form', 'form' => 'sent_categories_form', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Products REST Export', 'action' => 'export_rest_products', 'form' => 'export_rest_products_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Products REST Exported', 'action' => 'exported_rest_products', 'form' => 'exported_rest_products_form', 'hidden' => TRUE );


		$this->tabs[] = array( 'title' => 'Purchase Order Export', 'action' => 'cexport', 'form' => 'form_export', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Purchase Order Exported', 'action' => 'c_export', 'form' => 'export_orders', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Export File', 'action' => 'exportfile', 'form' => 'export_file_form', 'hidden' => FALSE );
	
		//$this->tabs[] = array( 'title' => 'Product Select and REST Export', 'action' => 'export_rest_product', 'form' => 'export_rest_product_form', 'hidden' => FALSE );
		//$this->tabs[] = array( 'title' => 'Products REST Exported', 'action' => 'exported_rest_product', 'form' => 'exported_rest_product_form', 'hidden' => TRUE );
		
		$this->tabs[] = array( 'title' => 'Orders Export', 'action' => 'export_orders_form', 'form' => 'export_orders_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Orders Exported', 'action' => 'exported_orders_form', 'form' => 'exported_orders_form', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Orders Import', 'action' => 'import_orders_form', 'form' => 'import_orders_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Orders Imported', 'action' => 'imported_orders_form', 'form' => 'imported_orders_form', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Customer Export', 'action' => 'export_customer_form', 'form' => 'export_customer_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Customer Exported', 'action' => 'exported_customer_form', 'form' => 'exported_customer_form', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Customer Import', 'action' => 'import_customer_form', 'form' => 'import_customer_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Customer Imported', 'action' => 'imported_customer_form', 'form' => 'imported_customer_form', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Coupons create', 'action' => 'create_coupons_form', 'form' => 'create_coupons_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Coupons created', 'action' => 'created_coupons_form', 'form' => 'created_coupons_form', 'hidden' => TRUE );
/*
		$this->tabs[] = array( 'title' => 'Coupons REST Export', 'action' => 'export_rest_coupon', 'form' => 'export_rest_coupon_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Coupons REST Exported', 'action' => 'exported_rest_coupon', 'form' => 'exported_rest_coupon_form', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Customers REST Export', 'action' => 'export_rest_customer', 'form' => 'export_rest_customer_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Customers REST Exported', 'action' => 'exported_rest_customer', 'form' => 'exported_rest_customer_form', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Refunds REST Export', 'action' => 'export_rest_refunds', 'form' => 'export_rest_refunds_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Refunds REST Exported', 'action' => 'exported_rest_refunds', 'form' => 'exported_rest_refunds_form', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Taxes REST Export', 'action' => 'export_rest_taxes', 'form' => 'export_rest_taxes_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Taxes REST Exported', 'action' => 'exported_rest_taxes', 'form' => 'exported_rest_taxes_form', 'hidden' => TRUE );
 */
	}
	function init_tables_form()
	{
            	display_notification("init tables form");
		$this->call_table( 'init_tables_complete_form', "Init Tables" );
	}
	function init_tables_complete_form()
	{
		$this->create_table_woo_prod_variable_master();
		$this->create_table_woo_prod_variable_child();
		$this->create_table_woo_prod_variable_variables();
		$this->create_table_woo_prod_variables_values();
		$createdcount = 0;
		require_once( 'class.woo_orders.php' );
		$orders = new woo_orders($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $orders->create_table() )
			$createdcount++;

		require_once( 'class.woo.php' );
		$woo = new woo($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $woo->create_table() )
			$createdcount++;

		require_once( 'class.qoh.php' );
		$qoh = new qoh($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $qoh->create_table() )
			$createdcount++;


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

		require_once( 'class.woo_categories_xref.php' );
		$category_xref_address = new woo_categories_xref($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $category_xref_address->create_table() )
			$createdcount++;
		require_once( 'class.woo_coupons.php' );
		$coupons = new woo_coupons($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $coupons->create_table() )
			$createdcount++;
       			
     	display_notification("init tables complete form created " . $createdcount . " tables");
	}
	function create_table_woo_prod_variable_master()
	{
		$table_details = array();
		$fields_array = array();
		$fields_array[] = array('name' => 'id_woo_prod_variable_master', 'type' => 'int(11)', 'auto_increment' => 'TRUE' );
		$fields_array[] = array('name' => 'stock_id', 'type' => 'varchar(32)' );
		$fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		//$table_details['tablename'] = TB_PREF . "woo_categories_xref";
		$table_details['tablename'] = $this->company_prefix . "woo_prod_variable_master";
		//$table_details['primarykey'] = "fa_cat";
		$table_details['index'][0] = array( 'name' => "idx_stock_id", 'columns' => "stock_id" );

		$this->create_table( $table_details, $fields_array );
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
	function create_table_woo_prod_variable_variables()
	{
		$table_details = array();
		$fields_array = array();
		$fields_array[] = array('name' => 'id_woo_prod_variable_variables', 'type' => 'int(11)', 'auto_increment' => 'TRUE' );
		$fields_array[] = array('name' => 'variablename', 'type' => 'varchar(32)' );
		$fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		//$table_details['tablename'] = TB_PREF . "woo_categories_xref";
		$table_details['tablename'] = $this->company_prefix . "woo_prod_variable_variables";

		$this->create_table( $table_details, $fields_array );
	}
		function create_table_woo_prod_variables_values()
	{
		$table_details = array();
		$fields_array = array();
		$fields_array[] = array('name' => 'id_woo_prod_variables_values', 'type' => 'int(11)', 'auto_increment' => 'TRUE' );
		$fields_array[] = array('name' => 'variablename', 'type' => 'varchar(32)' );
		$fields_array[] = array('name' => 'value', 'type' => 'varchar(32)' );
		$fields_array[] = array('name' => 'slug', 'type' => 'varchar(32)' );
		$fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		//$table_details['tablename'] = TB_PREF . "woo_categories_xref";
		$table_details['tablename'] = $this->company_prefix . "woo_prod_variables_values";
		$table_details['index'][0] = array( 'name' => "idx_variables_values", 'columns' => "variablename, value" );
		$table_details['foreign'][0] = array( 'column' => "variablename", 'foreigntable' => "woo_prod_variable_variables", "foreigncolumn" => "variablename", "on_update" => "restrict", "on_delete" => "restrict" );

		$this->create_table( $table_details, $fields_array );
	}
	function create_coupons_form()
	{
		$coupons = new woo_coupons();
		$coupons->debug = $this->debug;
		$coupons->coupons_form();
	}
	function created_coupons_form()
	{
	}
	function form_products_export()
	{
		//$this->call_table( 'pexed', "Export" );
		$this->call_table( 'qoh', "QOH" );
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
	
            	display_notification("Missing from WOO");
		$missing_sql = "select sm.stock_id, sm.description, c.description, sm.inactive, sm.editable 
				from " . TB_PREF . "stock_master sm, " . TB_PREF . "stock_category c
				where sm.category_id = c.category_id and sm.stock_id not in (select stock_id from " . TB_PREF . "woo)";
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
		//$submit_on_change = false;
	
	
	        //if ($editkey)
	                set_editor('item', $name, $editkey);
	
		start_form();

		start_table();
		table_section_title(_("Possible Causes of problems leading to these items being missing from WOO"));
		/*
		 *		$sql2 = "replace into " . TB_PREF . "woo 
				( stock_id, 
				category_id, 
				category, 
				description, 
				long_description, 
				units, 
				price,
				instock )
			 select 
				sm.stock_id, 
				sm.category_id, 
				sc.description as category, 
				sm.description, 
				sm.long_description, 
				sm.units, 
				p.price,
				mv.instock "
				//. "woo.woo_cat as woo_category_id"
			 . "from 
				" . TB_PREF . "stock_master sm, 
				" . TB_PREF . "prices p, 
				" . TB_PREF . "stock_category sc,
				" . TB_PREF . "qoh mv,
				" . TB_PREF . "woo_categories_xref woo
			where 
				sm.stock_id = p.stock_id 
				and p.sales_type_id='1' 
				and mv.stock_id = sm.stock_id
				and sm.category_id = sc.category_id
				* */
		/*
		 * TROUBLESHOOTING:
		 	select sm.stock_id, sm.category_id, sc.description as category, sm.description, sm.long_description, sm.units, p.price, mv.instock 
              		from 0_stock_master sm, 0_prices p, 0_stock_category sc, 0_qoh mv, 0_woo_categories_xref woo
			where sm.stock_id = p.stock_id and p.sales_type_id='1' and mv.stock_id = sm.stock_id and sm.category_id = sc.category_id
			and sm.stock_id = 'hd-hat-hornpipe-51';
			select * from 0_qoh where stock_id = 'hd-hat-hornpipe-51';
 			select * from 0_woo where stock_id = 'hd-hat-hornpipe-51';
* 
		 * */
		label_row(_("No retail price set (Price type 1) in prices:"), NULL);
		label_row(_("No total (instock) in QOH (stock_id not in stock_master or issues in stock_moves"), NULL);
		label_row(_("An issue with the Category description/id"), NULL);
	//	label_row(_("No Transaction History (no inventory movement):"), NULL);
		label_row("&nbsp;", NULL);
		label_row("Press F4 to pop open a window to edit the item details", null);
		//label_row("&nbsp;", $stock_img_link);
//		end_table();

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
	        //if ($editkey)
	         //       $ret .= add_edit_combo('item');
			echo $ret;
	  	//echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
	        //        array('cells'=>true, 'show_inactive'=>$all), $editkey);
//		$this->sales_pricing();
		end_table();
		end_form();

	}
	function sales_pricing()
	{
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
	}
	function populate_qoh()
	{
            	display_notification("QOH");
/*
		$qoh = "create table if not exists 0_qoh SELECT 
				stock_id, SUM(qty) as instock
			FROM 
				0_stock_moves
			GROUP BY stock_id";
            		//AND tran_date <= '$date'";
		$res = db_query( $qoh, "Couldn't create table of stock on hand" );
		var_dump( $res );
*/
		$qoh2 = "insert ignore into " . TB_PREF . "qoh SELECT 
				stock_id, 0 as instock
			FROM 
				" . TB_PREF . "stock_master
			WHERE
				inactive='0'";
		$res = db_query( $qoh2, "Couldn't create table of stock on hand" );

		$qoh2 = "replace into " . TB_PREF . "qoh SELECT 
				stock_id, SUM(qty) as instock
			FROM 
				" . TB_PREF . "stock_moves
			GROUP BY stock_id";
		$res = db_query( $qoh2, "Couldn't create table of stock on hand" );
		//var_dump( $res );

		$res = db_query( "select count(*) from " . TB_PREF . "qoh", "Couldn't count QOH" );
		$count = db_fetch_row( $res );
            	display_notification("$count[0] rows of items exist.");
		$this->call_table( 'woo', "WOO" );
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
            	display_notification("WOO");
		$sql_create = "insert ignore into " . TB_PREF . "woo ( stock_id"
				. ", category_id"
				. ", description"
				. ", long_description"
				. ", units"
			//	. ", tax_status
				//. ", tax_class
				."	)
			 select 
				sm.stock_id 
				, sm.category_id"
				. ", sm.description"
				. ", sm.long_description"
				. ", sm.units"
			//	. ", sm.tax_type_id"
				//. ", sm.tax_class
			. " from " . TB_PREF . "stock_master sm"
			. " WHERE inactive=0"
			;
		$res = db_query( $sql_create, "Couldnt create items in  WOO" );

		$sql_update = "update " . TB_PREF . "woo woo, " . TB_PREF . "stock_master sm
			set
				woo.category_id = sm.category_id
				, woo.description = sm.description
				, woo.long_description = sm.long_description
				, woo.units = sm.units
			where woo.stock_id = sm.stock_id";
		$res = db_query( $sql_update, "Couldnt update stock_master details in  WOO" );

		$sql_update2 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "prices p
			set
				woo.price = p.price
			where woo.stock_id = p.stock_id
				and p.sales_type_id = '1'";
		$res = db_query( $sql_update2, "Couldnt update prices in  WOO" );

		$sql_update3 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "qoh q
			set
				woo.instock = q.instock
			where woo.stock_id = q.stock_id";
		$res = db_query( $sql_update3, "Couldnt update Quantity On Hand in  WOO" );
/*
		$sql_update4 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "specials s
			set
				woo.sale_price = s.sale_price,
				woo.date_on_sale_from = s.start,
				woo.date_on_sale_to = s.end
			where woo.stock_id = s.stock_id";
		$res = db_query( $sql_update4, "Couldnt update Sales and Specials in  WOO" );
 */

				//var $tax_status;	//string 	Tax status. Default is taxable. Options: taxable, shipping (Shipping only) and none.
				//var $tax_class;	//string 	Tax class.
				//item_tax_types is source for tax_type_id
				//item_tax_types.name would be tax_class
				//item_tax_types.exempt would set tax_status
		//$sql_update5 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "taxes t
		//	set
		//		woo.tax_status = t.tax_status,
		//		woo.tax_class = t.tax_class
		//	where woo.stock_id = t.stock_id";
		$sql_update5 = "update " . TB_PREF . "woo woo
			set
				woo.tax_status = 'taxable',
				woo.tax_class = 'GST'";
		$res = db_query( $sql_update5, "Couldnt update TAX data in  WOO" );
/*
		$sql_update6 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "shipdim s
			set
				woo.shipping_class = s.shipping_class,
				woo.length = s.length,
				woo.width = s.width,
				woo.height = s.height,
				woo.weight = s.weight
			where woo.stock_id = s.stock_id";
		$res = db_query( $sql_update6, "Couldnt update Shipping Dimensional data in  WOO" );

		$sql_update7 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "related s
			set
				woo.upsells_ids = s.upsells_ids,
				woo.crosssells_ids = s.crosssells_ids,
			where woo.stock_id = s.stock_id";
		$res = db_query( $sql_update7, "Couldnt update upsell and cross sell data in  WOO" );
 */

		$sql_update8 = "update " . TB_PREF . "woo woo,  " . TB_PREF . "stock_category s
			set
				woo.category = s.description
			where woo.category_id = s.category_id";
		$res = db_query( $sql_update8, "Couldnt update Category data in  WOO" );


				
				//. "woo.woo_cat as woo_category_id"
				//", " . TB_PREF . "woo_categories_xref woo"
				//. "and woo.fa_cat = sm.category_id"
		/*
		$sql2 = "replace into " . TB_PREF . "woo 
				( stock_id, 
				category_id, 
				category, 
				description, 
				long_description, 
				units, 
				price,
				instock"
				// . ", tax_status"
				// . ", tax_class"
				// . ", shipping_class"
				// . ", upsell_ids"
				// . ", crosssell_ids"
				."	)
			 select 
				sm.stock_id, 
				sm.category_id, 
				sc.description as category, 
				sm.description, 
				sm.long_description, 
				sm.units, 
				p.price,
				mv.instock"  
				// . ", tax_status"
				// . ", tax_class"
				// . ", shipping_class"
				// . ", upsell_ids"
				// . ", crosssell_ids"
				//. "woo.woo_cat as woo_category_id"
			 . "from 
				" . TB_PREF . "stock_master sm, 
				" . TB_PREF . "prices p, 
				" . TB_PREF . "stock_category sc,
				" . TB_PREF . "qoh mv,
				" . TB_PREF . "woo_categories_xref woo
			where 
				sm.stock_id = p.stock_id 
				and p.sales_type_id='1' 
				and mv.stock_id = sm.stock_id
				and sm.category_id = sc.category_id"
				//. "and woo.fa_cat = sm.category_id"
				;
            	//display_notification("WOO1");
		$res = db_query( $sql2 );
		 */
		$sql3 = "update " . TB_PREF . "woo woo, " . TB_PREF . "woo_categories_xref xref set woo.woo_category_id = xref.woo_cat where xref.fa_cat = woo.category_id";
		$res = db_query( $sql3, "Couldnt update categories WOO" );

		/*******************************************************************************
		 *
		 *	If the shipping dimensions module is installed, grab those too
		 *
		 * ****************************************************************************/

		//$res = db_query( $sql, "Couldnt populate WOO" );
            	//display_notification("WOO2");
		//var_dump( $res );
            	display_notification("WOO3");

		$res2 = db_query( "select count(*) from " . TB_PREF . "woo", "Couldn't count WOO" );
            	//display_notification("WOO4");
		$count = db_fetch_row( $res2 );
            	display_notification("$count[0] rows of items exist.");
		//$this->call_table( 'pexed', "Create Export File for WOO" );
	}
/*
	//export_rest_coupon	export_rest_customer_form	exported_rest_customer_form
	function export_rest_coupon_form()
	{
		$this->call_table( 'export_rest_coupon', "Send Coupons via REST to WOO" );
	}
	function export_rest_customer_form()
	{
		$this->call_table( 'export_rest_customer', "Send Customers via REST to WOO" );
	}
	function export_rest_refunds_form()
	{
		$this->call_table( 'export_rest_refunds', "Send Refunds via REST to WOO" );
	}
 */
	/***********************************************************************
	*
	*	Function export_rest_products_form()	
	*	
	*	To present the user with a button to launch the sending of 
	*	ALL Products to WOO.
	*
	************************************************************************/
	function export_rest_products_form()
	{
		$this->call_table( 'exported_rest_products', "Send Products via REST to WOO" );
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
	}
/************************
 * Moved into woo_products...
 *
	function product_images( $stock_id )
	{
		//IMAGES
		//If we use local URL we need to build it and send it
		//If we need to use WOOCOMMERCE image gallery, we need the filename
		//With the module to allow extra images, we need to check for that too
		////SHould also check for the existance of the filename in the local company
		//	Default location is (/company/0/images
		$image_array = array();
		$imagecount = 0;
		//if( isset( $this->image_serverurl ) AND isset( $this->image_baseurl ) AND $this->use_img_baseurl == "true" )
		if( isset( $this->image_serverurl ) AND isset( $this->image_baseurl ) )
		{
			//Assumption running on same machine for image check
			$filename = company_path().'/images/' . item_img_name($stock_id) . ".jpg";
			if( file_exists( $filename ) === TRUE )
			{
				$image_array[$imagecount]['src']  = $this->image_serverurl . '/' . $this->image_baseurl . '/' . $stock_id . '.jpg"';
				$image_array[$imagecount]['position'] = $imagecount;
				$imagecount++;
			}
		}
		else
		{
			$image_array[$imagecount]['src'] = $stock_id . '.jpg"';
			$image_array[$imagecount]['position'] = $imagecount;
			$imagecount++;
		}
		for ( $j = 1; $j <= $this->maxpics; $j++ )
		{
			$filename = item_img_name($stock_id) . $j . ".jpg";
			$fullfilename = company_path().'/images/' . $filename;
			if( file_exists( $fullfilename ) === TRUE )
			{
				$image_array[$imagecount]['src']  = $this->image_serverurl . '/' . $filename;
				$image_array[$imagecount]['position'] = $imagecount;
				$imagecount++;
			}
		}
		return $image_array;
	}
*/
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
	}
	function import_customer_form()
	{
		$this->call_table( 'imported_customer_form', "Get Customer from WOO" );
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
	}
	function export_customer_form()
	{
		$this->call_table( 'exported_customer_form', "Send Customer to WOO" );
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
	}
	function import_orders_form()
	{
		$this->call_table( 'imported_orders_form', "Get Orders from WOO" );
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
	}
	function export_orders_form()
	{
		$this->call_table( 'exported_orders_form', "Send Orders to WOO" );
	}
	function exported_rest_simple_products()
	{
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
	function form_pricebook()
	{
		$fname = 'PriceBook.csv';
		$filename = '../../tmp/' . $fname;

		$fp = $this->open_write_file( $filename );
			$hline  = '"stock_id",';
			$hline .= '"category",';
			$hline .= '"Title",';
			$hline .= '"price",';
			$hline .= '"barcode",';
			$this->write_line( $fp, $hline );
		fflush( $fp );

		$woo = "select * from " . TB_PREF . "woo order by category, description";
		$result = db_query( $woo, "Couldn't grab inventory to export" );

		$rowcount=0;
		while ($row = db_fetch($result)) 
		{
			$line  = '"' . $row['stock_id'] . '",';
			$line .= '"' . $row['category'] . '",';
			$line .= '"' . $row['description'] . '",';
			$line .= '"' . $row['price'] . '",';
			$line  .= '"*' . strtoupper( $row['stock_id'] ) . '*",';	//For 3of9 Barcode
			$this->write_line( $fp, $line );
			$rowcount++;
		}
		$this->file_finish( $fp );
            	display_notification("$rowcount rows of items created.");
		if( isset( $this->mailto ) )
		{
			$data = file_get_contents( $filename );
			$uu_data = "begin 644 " . $fname . "\n" . convert_uuencode($data) . "end\n";
			$subject = 'Pricebook file';
			$headers = 'From: sales@fraserhighlandshoppe.ca' . "\r\n" .
			    'Reply-To: sales@fraserhighlandshoppe.ca' . "\r\n";
			mail($this->mailto, $subject, $uu_data, $headers);
            		display_notification("email sent to $this->mailto.");
		}
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
	function export_file_form()
	{
		$this->call_table( 'pexed', "Create Export File for WOO" );
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
	}
}
?>
