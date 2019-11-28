<?php

//require_once( 'class.generic_orders.php' ); 
require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 

//global $prefsDB;
//$prefsDB = "ksf_generate_catalogue_prefs";	//used in module install (hooks.php), file ksf_generate_catalogue.php


//class ksf_generate_catalogue
//class ksf_generate_catalogue extends generic_orders
//
/************************************************************************//**
 *
 * uses inherited call_table
 * uses class write_file
 * uses class email_file
 *
 * *************************************************************************/
class ksf_generate_catalogue extends generic_fa_interface
{
	var $include_header;
	var $maxrowsallowed;
	var $lastoid;
	var $mailto;
	var $mailfrom;
	var $db;
	var $environment;
	var $maxpics;
	var $debug;
	var $fields_array;
	var $write_file;	//!< class write_file for writing files
	var $tmp_dir;		//!< @var string temp directory to store pricebook
	var $filename;		//!< @var string pricebook filename.
	var $dolabels;
	function __construct( $pref_tablename )
	{
		simple_page_mode(true);
		global $db;
		$this->db = $db;
		//echo "ksf_generate_catalogue constructor";
		parent::__construct( null, null, null, null, $pref_tablename );
		
		$this->tmp_dir = "../../tmp";
		$this->filename = "pricebook.csv";
		$this->set_var( 'vendor', "ksf_generate_catalogue" );
		$this->set_var( 'include_header', TRUE );
		/*
		$this->fields_array = array();
		$this->fields_array[] = array( 'field' => 'category_id', 'table' => '', 'header' => '', 'join' => '0', );
		$this->fields_array[] = array( 'field' => 'sku', 'table' => 'stock_master', 'header' => 'SKU Barcode', 'join' => '0',);
		$this->fields_array[] = array( 'field' => 'sku', 'table' => 'stock_master', 'header' => 'SKU Text', 'join' => '0',);
		$this->fields_array[] = array( 'field' => 'price', 'table' => '', 'header' => 'Price', 'join' => '0',);
		$this->fields_array[] = array( 'field' => 'inactive', 'table' => 'stock_master', 'header' => '', 'join' => '0', 'where' => '=0');
		 */
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		$this->config_values[] = array( 'pref_name' => 'include_header', 'label' => 'Include Headers' );
		$this->config_values[] = array( 'pref_name' => 'maxrowsallowed', 'label' => 'Maximum Rows Allowed in file' );
		$this->config_values[] = array( 'pref_name' => 'mailto', 'label' => 'Mail CSV to email address' );
		$this->config_values[] = array( 'pref_name' => 'mailfrom', 'label' => 'Mail from email address' );
		$this->config_values[] = array( 'pref_name' => 'environment', 'label' => 'Environment (devel/accept/prod)' );
		$this->config_values[] = array( 'pref_name' => 'dolabels', 'label' => 'Print Labels (0/1)' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		$this->dolabels = 0;
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'create', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Export File', 'action' => 'exportfile', 'form' => 'write_file_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Generate Catalogue', 'action' => 'gencat', 'form' => 'form_pricebook', 'hidden' => TRUE );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
	/*	
	 */
	}
	
	function create_price_book()
	{
		require_once( '../ksf_modules_common/class.write_file.php' ); 
		$this->write_file = new write_file( $this->tmp_dir, $this->filename );

		$hline  = '"stock_id", "category", "Title", "price", "barcode"';
		$this->write_file->write_line( $hline );
		
		$woo = "select s.stock_id as stock_id, c.description as category, s.description as description, p.price as price from " . TB_PREF . "stock_master s, " . TB_PREF . "stock_category c, " . TB_PREF . "prices p where s.category_id=c.category_id and s.inactive=0 and p.stock_id=s.stock_id and p.sales_type_id=1 order by c.description, s.description";
	 	$result = db_query( $woo, "Couldn't grab inventory to export" );

		$rowcount=0;
		while ($row = db_fetch($result)) 
		{
			$line  = '"' . $row['stock_id'] . '",';
			$line .= '"' . $row['category'] . '",';
			$line .= '"' . $row['description'] . '",';
			$line .= '"' . $row['price'] . '",';
			$line  .= '"*' . strtoupper( $row['stock_id'] ) . '*",';	//For 3of9 Barcode
			$this->write_file->write_line( $line );
			$rowcount++;
		}
		$this->write_file->close();
	}
	/*@int@*/function create_sku_labels()
	{
		require_once( '../ksf_modules_common/class.write_file.php' ); 
		require_once( '../ksf_qoh/class.ksf_qoh.php' ); 
		$this->write_file = new write_file( $this->tmp_dir, $this->filename );

		$hline  = '"stock_id", "Title", "barcode"';
		$this->write_file->write_line( $hline );
		
		$woo = "select s.stock_id as stock_id, s.description as description, q.instock as instock , c.description as category from " . TB_PREF . "stock_master s, " . TB_PREF . "ksf_qoh q, " . TB_PREF . "stock_category c where s.inactive=0 and s.stock_id=q.stock_id and s.category_id = c.category_id order by c.description, s.description";
	 	$result = db_query( $woo, "Couldn't grab inventory to export labels" );

		$rowcount=0;
		while ($row = db_fetch($result)) 
		{
			$num = $row['instock'];
			//If we have 6 items instock, we need 6 labels to print so we can put on product
			for( $num; $num > 0; $num-- )
			{
				$line  = '"' . $row['stock_id'] . '",';
				$line .= '"' . $row['description'] . '",';
				$line  .= '"*' . strtoupper( $row['stock_id'] ) . '*",';	//For 3of9 Barcode
				$line .= '"' . $row['category'] . '",';
				$this->write_file->write_line( $line );
				$rowcount++;
			}
		}
		$this->write_file->close();
		return $rowcount;
	}
	 
	
	function email_price_book()
	{
		if( isset( $this->mailto ) )
		{
			require_once( '../ksf_modules_common/class.email_file.php' ); 
			$mail_file = new email_file( $this->mailfrom, $this->mailto, $this->tmp_dir, $this->filename );
			$mail_file->email_file( 'Pricebook file' );
			display_notification("email sent to $this->mailto.");
		}
	}
	function form_pricebook()
	{
		$this->create_price_book();
		$this->email_price_book();
		if( $this->dolabels )
		{
			$this->filename = "labels.csv";
			$this->create_sku_labels();
			$this->email_price_book();
		}
		$this->call_table( '', "OK" );
	}
	function write_file_form()
	{
		if( $this->dolabels)
			$this->call_table( 'gencat', "Create Catalogue File and Labels" );
		else
			$this->call_table( 'gencat', "Create Catalogue File" );
	}
	

}

?>
