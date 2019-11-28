<?php

require_once( 'class.generic_orders.php' ); 


//class coast_orders
class coast_orders extends generic_orders
{
	var $include_header;
	var $maxrowsallowed;
	var $lastoid;
	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		//echo "coast orders constructor";
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		$this->set_var( 'vendor', "Coast" );
		$this->set_var( 'include_header', TRUE );
		
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'include_header', 'label' => 'Include Headers' );
		$this->config_values[] = array( 'pref_name' => 'maxrowsallowed', 'label' => 'Maximum Rows Allowed in file' );
		$this->config_values[] = array( 'pref_name' => 'mailto', 'label' => 'Mail CSV to email address' );
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Purchase Order Export', 'action' => 'cexport', 'form' => 'form_export', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'create', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Purchase Order Exported', 'action' => 'c_export', 'form' => 'export_orders', 'hidden' => TRUE );

	}
	function export_orders()
	{
		/*
		 *	Coast Music's B2B site can import an order in a CSV with  2 columns
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
		$filename = '/tmp/' . $fname;
		$fp = fopen( $filename, 'w' );
		if( $this->include_header )
		{
			foreach( $headers as $column )
			{
				fwrite( $fp, $column . "," );
			}
			fwrite( $fp, "\n" );
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

				fwrite( $fp, '"' . $po_line_details->stock_id . '",' );
				fwrite( $fp, '"' . $po_line_details->quantity . '",' );
				fwrite( $fp, '"' . $po_line_details->item_description . '",' );
				fwrite( $fp, '"' . $po_line_details->line_no . '",' );
				fwrite( $fp, "\n" );
				$rowcount++;
		}
		fflush( $fp );
		fclose( $fp );
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
