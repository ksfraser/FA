<?php

require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 



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
	var $delivery_no;		//!< @var int order number to export labels for
	var $smtp_server;
	var $smtp_port;
	var $smtp_user;
	var $smtp_pass;
	var $b_email;
	function __construct( $pref_tablename )
	{
		simple_page_mode(true);
		global $db;
		$this->db = $db;
		//echo "ksf_generate_catalogue constructor";
		parent::__construct( null, null, null, null, $pref_tablename );
		
		$this->tmp_dir = "../../tmp";
		$this->filename = "pricebook.csv";
		//$this->set_var( 'vendor', "ksf_generate_catalogue" );
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
		$this->config_values[] = array( 'pref_name' => 'smtp_server', 'label' => 'Mail Server' );
		$this->config_values[] = array( 'pref_name' => 'smtp_port', 'label' => 'Mail Server Port (25/993)' );
		$this->config_values[] = array( 'pref_name' => 'smtp_user', 'label' => 'Mail Server User' );
		$this->config_values[] = array( 'pref_name' => 'smtp_passs', 'label' => 'Mail Server Password' );
		$this->config_values[] = array( 'pref_name' => 'b_email', 'label' => 'Send file by email' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		$this->dolabels = 0;
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'create', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Export File', 'action' => 'exportfile', 'form' => 'write_file_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Generate Catalogue', 'action' => 'gencat', 'form' => 'form_pricebook', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Lables for a Purchase Order', 'action' => 'polabelsfile', 'form' => 'polabelsfile_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Labels Generated', 'action' => 'label_export_by_PO_Delivery', 'form' => 'label_export_by_PO_Delivery', 'hidden' => TRUE );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
	/*	
	 */
	}
	//CALLED by child classes
	function prep_write_file()
	{	
		require_once( '../ksf_modules_common/class.write_file.php' ); 
		$this->write_file = new write_file( $this->tmp_dir, $this->filename );

	}
	//CALLED by form_pricebook
	/*@int@*/function create_price_book()
	{
		if( include_once( 'class.pricebook_file.php' ) )
		{
			$pb = new pricebook_file( $this->pref_tablename );
			foreach( $this->config_values as $arr )
			{
				$value = $arr["pref_name"];
				$pb->$value = $this->$value;
			}
			$rowcount = $pb->create_file();
			//return $rowcount;
		}
		if( include_once( 'class.square_catalog.php' ) )
		{
			$sc = new square_catalog( $this->pref_tablename );
			foreach( $this->config_values as $arr )
			{
				$value = $arr["pref_name"];
				$sc->$value = $this->$value;
			}
			$rowcount = $sc->create_file();
			//return $rowcount;
		}
		if( include_once( 'class.WooPOS_Count.php' ) )
		{
			$woopos = new WooPOS_Count_file( $this->pref_tablename );
			foreach( $this->config_values as $arr )
			{
				$value = $arr["pref_name"];
				$woopos->$value = $this->$value;
			}
			$woopos->create_file();
			return $rowcount;
		}
	}
	//Called by form_pricebook
	/*@int@*/function create_sku_labels()
	{
		if( include_once( 'class.labels_file.php' ) )
		{
			$lf = new labels_file( $this->pref_tablename );
			foreach( $this->config_values as $arr )
			{
				$value = $arr["pref_name"];
				$lf->$value = $this->$value;
			}
			$rowcount = $lf->create_file();
			$lf->email_file();
/** 20230605 This is running twice, no need.
			if( include_once( 'class.square_catalog.php' ) )
			{
				$sc = new square_catalog( $this->pref_tablename );
				foreach( $this->config_values as $arr )
				{
					$value = $arr["pref_name"];
					$sc->$value = $this->$value;
				}
				$rowcount2 = $sc->create_file();
			}
**/
			return $rowcount;
		}
		else
			return -1;
	}
 
	
	function email_file( $email_subject = 'Pricebook file' )
	{
		if( isset( $this->mailto ) )
		{
			require_once( '../ksf_modules_common/class.email_file.php' ); 
			if( $this->b_email )
			{
				try {
					$mail_file = new email_file( $this->mailfrom, $this->mailto, $this->tmp_dir, $this->filename, $this->smtp_user, $this->smtp_pass, $this->smtp_server, $this->smtp_port );
					//$mail_file = new email_file( $this->mailfrom, $this->mailto, $this->tmp_dir, $this->filename, "kevin@ksfraser.com", "letmein", "musicone.ksfraser.com", "25" );	//Error about HELO name
					//$mail_file = new email_file( $this->mailfrom, $this->mailto, $this->tmp_dir, $this->filename, "sales@fraserhighlandshoppe.ca", "HiGhLaNd12@", "p3plcpnl0185.prod.phx3.secureserver.net", "993" );
					$mail_file->email_file( $email_subject );
					display_notification("email sent to $this->mailto.");
				}
				catch( Exception $e )
				{
				}
				return TRUE;
			}
		}
		return FALSE;
	}
	function form_pricebook()
	{
		$this->create_price_book();
		$this->email_file();
		if( $this->dolabels )
		{
			
			$this->create_sku_labels();
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
	function polabelsfile_form()
	{
	 	$selected_id = 1;
                $none_option = "";
                $submit_on_change = FALSE;
                $all = FALSE;
                $all_items = TRUE;
                $mode = 1;
                $spec_option = "";
                start_form(true);
                start_table(TABLESTYLE2, "width=40%");
                table_section_title("Labels for Purchase Order");
/*
                $company_record = get_company_prefs();
                $this->get_id_range();
                $sql = "SELECT supp_name, delivery_no FROM " . $this->company_prefix . "purch_orders o, " . $this->company_prefix . "suppliers s where s.supplier_id = o.supplier_id";
                //echo combo_input("SupplierPO", $selected_id, $sql, 'supplier_id', 'supp_name',
*/

                 text_row("Export Purchase Order Delivery ID (First):", 'delivery_no', $this->lastoid+1, 10, 10);
		/**
		 *      Mantis 2483 */
                 text_row("Export Purchase Order Delivery ID (Last):", 'last_delivery_no', $this->lastoid+1, 10, 10);

                 end_table(1);

                 hidden('action', 'label_export_by_PO_Delivery');
                 submit_center('label_export_by_PO_Delivery', "Export PO Delivery");

                 end_form();

	//	$this->call_table( 'polabels', "Create Labels" );
	}
	function label_export()
	{
			$this->filename = "delivery_" . $this->delivery_no . "_labels.csv";
			$this->create_sku_labels();
			$this->email_file();
	}
	/******************************************************************************//**
	* Given a PO number create the labels for the items in that PO
	*
	*
	* @returns bool
	*********************************************************************************/
	/*@bool@*/function label_export_by_PO_Delivery()
	{
		if( !isset( $this->delivery_no ) )
		{
			if( isset( $_POST['delivery_no'] ) )
				$this->delivery_no = $_POST['delivery_no'];
		}
		
		if( include_once( 'class.labels_file.php' ) )
		{
			$lf = new labels_file( $this->pref_tablename );
			$lf->set( "delivery_no", $this->delivery_no );
			/**
			 *      Mantis 2483 */
			if( isset(  $_POST['last_delivery_no'] ) )
			{
				$lf->set( "last_delivery_no", $_POST['last_delivery_no'] );
			}
			else
			{
				$lf->set( "last_delivery_no", $this->delivery_no );
			}
			/** !	Mantis 2483 */
			foreach( $this->config_values as $arr )
			{
				if( isset( $arr['title'] ) )
				{
					foreach( $arr['title'] as $value )
					{
						$lf->$value = $this->$value;
					}
				}
				else
				{
					//20230903 This was printing the PREFS always!
					if( $this->debug > 1 )
					{
						echo "<br />";
						var_dump( $arr );
						echo "<br />";
					}
				}
			}
			$count = $lf->create_sku_labels_from_PO( $this->delivery_no );
			if( 0 < $count )
			{
				$this->set_pref( 'lastoid', $this->delivery_no );
			}
		}
		return TRUE;
	}
	

}

?>

