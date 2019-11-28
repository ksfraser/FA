<?php

//require_once( 'class.generic_orders.php' ); 
require_once( 'class.generic_fa_interface.php' ); 


//class PURCHASING_ASSISTANT
//class PURCHASING_ASSISTANT extends generic_orders
class PURCHASING_ASSISTANT extends generic_fa_interface
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
		simple_page_mode(true);
		global $db;
		$this->db = $db;
		//echo "PURCHASING_ASSISTANT constructor";
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		$this->set_var( 'vendor', "PURCHASING_ASSISTANT" );
		$this->set_var( 'include_header', TRUE );
		
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'include_header', 'label' => 'Include Headers' );
		$this->config_values[] = array( 'pref_name' => 'maxrowsallowed', 'label' => 'Maximum Rows Allowed in file' );
		$this->config_values[] = array( 'pref_name' => 'mailto', 'label' => 'Mail CSV to email address' );
		$this->config_values[] = array( 'pref_name' => 'environment', 'label' => 'Environment (devel/accept/prod)' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'create', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables', 'action' => 'init_tables_form', 'form' => 'init_tables_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables Completed', 'action' => 'init_tables_complete_form', 'form' => 'init_tables_complete_form', 'hidden' => TRUE );
		
	}
	function init_tables_form()
	{
            	display_notification("init tables form");
		$this->call_table( 'init_tables_complete_form', "Init Tables" );
	}
	function eventloop( $event, $method )
	{
	}
	function eventregister( $event, $method )
	{
	}
	
	function init_tables_complete_form()
	{
		$createdcount = 0;
		require_once( 'class.woo_orders.php' );
		$orders = new woo_orders($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		if( $orders->create_table() )
			$createdcount++;

			
     	display_notification("init tables complete form created " . $createdcount . " tables");
	}
	/*
	function create_woo_product_variables_values_form()
	{
		require_once( 'class.woo_prod_variables_values.php' );
		$wpvm = new woo_prod_variables_values($this->woo_server, $this->woo_ck, $this->woo_cs, null, $this );
		$wpvm->debug = $this->debug;
		$wpvm->master_form();
	}
	function form_products_export()
	{
		//$this->call_table( 'pexed', "Export" );
		$this->call_table( 'qoh', "QOH" );
	}
	 */
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

}

?>
