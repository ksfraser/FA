<?php

/****************************************************//**
* Read in a Dream csv and pass to bank_staging
*
***********************************************************/

$path_to_root = "../../..";

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( '../../ksf_modules_common/class.generic_fa_interface.php' );
/*************************************************************//**
 * Search for products that don't have an image
 *
 * Motivated by the fact that online shopping carts are annoying
 * if there isn't at least 1 product image attached.  This is to 
 * help ensure we have images for ALL products prior to sending
 * to WooCommerce
 *
 * Inherits:
 *                 function __construct( $host, $user, $pass, $database, $pref_tablename )
                function eventloop( $event, $method )
                function eventregister( $event, $method )
                function add_submodules()
                function module_install()
                function install()
                function loadprefs()
                function updateprefs()
                function checkprefs()
                function call_table( $action, $msg )
                function action_show_form()
                function show_config_form()
                function form_export()
                function related_tabs()
                function show_form()
                function base_page()
                function display()
                function run()
                function modify_table_column( $tables_array )
                / *@fp@* /function append_file( $filename )
                /*@fp@* /function overwrite_file( $filename )
                /*@fp@* /function open_write_file( $filename )
                function write_line( $fp, $line )
                function close_file( $fp )
                function file_finish( $fp )
                function backtrace()
                function write_sku_labels_line( $stock_id, $category, $description, $price )
		function show_generic_form($form_array)
* Provides:
*
* ***********************************************/

class dream_payments extends generic_fa_interface {
	var $id_dream_payments;	//!< Index of table

        protected $transaction_id;              
        protected $transaction_date; //!<Date   
        protected $transaction_time;            
        //protected $order_number;
        protected $merchant_user;               
        protected $gps_location;                
        protected $ip_address;                  
        protected $transaction_type;            
        protected $payment_method;              
        protected $entry_type;                  
        protected $card_type;                   
        protected $card_number;                 
        protected $subtotal;    //!<float       
        protected $tax;         //!<float       
        protected $tip;         //!<float       
        protected $total;       //!<float       
        protected $tcc;                         
        protected $tcd;                         
        protected $receipt_sent;                
        protected $receipt_email;               
        protected $receipt_mobile_number;       
	protected $order_status;               
        protected $c_file;	
	function __construct( $prefs )
	{
		parent::__construct( null, null, null, null, $prefs );	//generic_interface has legacy mysql connection
									//not needed with the $prefs
		/*
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		 */
		//$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		//Don't need a staged approach as we are having a separate tab for the normal multi-steps...
		//$this->tabs[] = array( 'title' => 'Missing_Image Updated', 'action' => 'form_ksf_missing_image_completed', 'form' => 'form_ksf_missing_image_completed', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Update Missing_Image', 'action' => 'form_ksf_missing_image', 'form' => 'form_ksf_missing_image', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Count Images', 'action' => 'count_images', 'form' => 'count_images', 'hidden' => FALSE );
		
	}
	/****************************************************************************//**
	* We won't have a table for this class
	*
	*******************************************************************************/
	function define_table()
	{
		return;
	}
	function form_dream_payments
	{
		$fup = new ksf_file_upload( "tmp.txt" );
		$fup->upload_form();
		foreach( $fp->files_array as $filename )
		{
			$csv = new ksf_file_csv( $filename, 100000000, "," );
			while( $line = $csv->readcsv_line() !== false )
			{
		        	$this->transaction_id = $line[];              
        			$this->transaction_date = $line[]; //!<Date   
        			$this->transaction_time = $line[];            
        //			$this->order_number = $line[];
        			$this->merchant_user = $line[];               
        			$this->gps_location = $line[];                
        			$this->ip_address = $line[];                  
        			$this->transaction_type = $line[];            
        			$this->payment_method = $line[];              
        			$this->entry_type = $line[];                  
        			$this->card_type = $line[];                   
        			$this->card_number = $line[];                 
        			$this->subtotal = $line[];    //!<float       
        			$this->tax = $line[];         //!<float       
        			$this->tip = $line[];         //!<float       
        			$this->total = $line[];       //!<float       
        			$this->tcc = $line[];                         
        			$this->tcd = $line[];                         
        			$this->receipt_sent = $line[];                
        			$this->receipt_email = $line[];               
        			$this->receipt_mobile_number = $line[];       
				$this->order_status = $line[];               
	}
		}
		//$this->call_table( 'form_dream_payments_completed', "Dream_Payments" );
	}
	function form_dream_payments_completed
	{	//Need to add code here to do whatever this submodule is for...
	}
	/*********************************************************************************//**
	 *master_form
	 *	Display the summary of items with edit/delete
	 *		
	 *	assumes entry_array has been built (constructor)
	 *	assumes table_details has been built (constructor)
	 *	assumes selected_id has been set (constructor?)
	 *	assumes iam has been set (constructor)
	 *
	 * ***********************************************************************************/
	function master_form()
	{
		global $Ajax;
		$this->notify( __METHOD__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );
		$this->create_full();
		div_start('form');
		$count = $this->fields_array2var();
		
		$sql = "SELECT ";
		$rowcount = 0;
		foreach( $this->entry_array as $row )
		{
			if( $rowcount > 0 ) $sql .= ", ";
			$sql .= $row['name'];
			$rowcount++;
		}
		$sql .= " from " . $this->table_details['tablename'];
		if( isset( $this->table_details['orderby'] ) )
			$sql .= " ORDER BY " . $this->table_details['orderby'];
	
		$this->notify( __METHOD__ . ":" . __METHOD__ . ":" . __LINE__ . ":" . $sql, "WARN" );
		$this->notify( __METHOD__ . ":" . __METHOD__ . ":" . __LINE__ . ":" . " Display data", "WARN" );
		$this->display_table_with_edit( $sql, $this->entry_array, $this->table_details['primarykey'] );
		div_end();
		div_start('generate');
		div_end();
	}

	
}
