<?php

require_once( 'class.generic_interface.php' ); 
require_once( 'class.generic_orders.php' ); 

/**************************************************************
 *
 *	This module generates EANs for products that don't have
 *	foreign (item) codes that aren't equal to the stock_id
 *
 *	Uses the following (example) query:
		insert into 0_item_codes (stock_id, description, category_id, quantity, inactive, item_code, is_foreign)
		select stock_id, description, category_id, quantity, inactive, 2000000000000+floor(RAND()*9999999998), 1 
		from 0_item_codes where stock_id not in (SELECT stock_id FROM `0_item_codes` where item_code <> stock_id and is_foreign = 1)
 *
 **************************************************************/

//class generate_EAN
class generate_EAN extends generic_interface
{
	//var $ ;
	var $EANPrefix ;
	var $GenerateRequestCount ;
	var $GenerateRandom ;
	var $LowestEAN ;
	var $HighestEAN ;
	var $LastEAN;
	var $PurchaseOrderToPrint;
	var $mailto;
	var $email_subject;	
	var $email_body;
	var $csv_filename;
	var $pretty_filename;
	var $last_po;		//The highest PO number
	function __construct( $pref_tablename )
	{
		//echo "generate EAN constructor";
		parent::__construct( $pref_tablename );
		
		//$this->config_values[] = array( 'pref_name' => '', 'label' => '' );
		$this->config_values[] = array( 'pref_name' => 'EANPrefix', 'label' => 'EAN Prefix (020 or 040 or 200 for internal use)' );
		$this->config_values[] = array( 'pref_name' => 'GenerateRequestCount', 'label' => 'How many EANs to generate' );
		$this->config_values[] = array( 'pref_name' => 'GenerateRandom', 'label' => 'Generate Random? (1 yes, 0 no - will be sequential)' );
		$this->config_values[] = array( 'pref_name' => 'LowestEAN', 'label' => 'Lowest EAN number? (10 digits - include leading zeros)' );
		$this->config_values[] = array( 'pref_name' => 'HighestEAN', 'label' => 'Highest EAN number? (10 digits)' );
		$this->config_values[] = array( 'pref_name' => 'LastEAN', 'label' => 'The Highest EAN number previously generated' );
		$this->config_values[] = array( 'pref_name' => 'PurchaseOrderToPrint', 'label' => 'The PO number that needs barcode labels printed.' );
		$this->config_values[] = array( 'pref_name' => 'mailto', 'label' => 'Email Address to mail the resulting CSV of barcode labels.' );
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'config_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'install', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'updateprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Generate EANs', 'action' => 'generate', 'form' => 'generate_EANs_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'EANs Generated', 'action' => 'genean', 'form' => 'generate_EANs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Print EANs', 'action' => 'print', 'form' => 'print_EANs_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'CSV Emailed', 'action' => 'email', 'form' => 'po2labels', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'CSV Emailed', 'action' => 'emailall', 'form' => 'po2labelsAll', 'hidden' => TRUE );
		//$this->tabs[] = array( 'title' => 'CSV Emailed', 'action' => 'email', 'form' => 'email_csv', 'hidden' => TRUE );

		$this->getLastPO();

	}
        function install()
        {
                $this->create_prefs_tablename();
                $this->loadprefs();
                $this->updateprefs();
                if( isset( $this->redirect_to ) )
                {
                        header("Location: " . $this->redirect_to );
                }
        }
	function email()
	{
		$go = 0;
		//Requires mailto (address), subject, and body to be set.
                if( !isset( $this->mailto ) )
                {
			display_notification("Destination EMAIL address not set!");
			$go--;
		}
                if( !isset( $this->email_subject ) )
                {
			display_notification("EMAIL subject not set!");
			$go--;
		}
                if( !isset( $this->email_body ) )
                {
			display_notification("EMAIL body not set!");
			$go--;
		}
		if( $go < 0 )
		{
			return FALSE;
		}
                        $headers = 'From: sales@fraserhighlandshoppe.ca' . "\r\n" .
                            'Reply-To: sales@fraserhighlandshoppe.ca' . "\r\n";

                        return mail($this->mailto, $this->email_subject, $this->email_body, $headers);
	}
	function email_csv()
	{
		$go = 0;
                if( !isset( $this->pretty_filename ) )
                {
			display_notification("Pretty Filename not set!");
			$go--;
		}
                if( !isset( $this->csv_filename ) )
                {
			display_notification("Full Filename not set!");
			$go--;
		}
                if( !isset( $this->PurchaseOrderToPrint ) )
                {
			display_notification("PO to Print not set!");
			$go--;
		}
		if( $go < 0 )
		{
			return FALSE;
		}
                        $data = file_get_contents( $this->csv_filename );
                        $this->email_body = "begin 644 " . $this->pretty_filename . "\n" . convert_uuencode($data) . "end\n";
                        $this->email_subject =  'Purchase Order ' . $this->PurchaseOrderToPrint . ' labels to be printed';
		if( $this->email() )
		{
			display_notification("CSV emailed to " . $this->get_pref( 'mailto' ) );
			return TRUE;
		}
		else
		{
			display_notification("CSV email FAILED" );
			return FALSE;
		}
	}
	function purchaseOrder2CSV()
	{
		//set up file to be written to.
		$this->pretty_filename = "PO_" . $this->PurchaseOrderToPrint . ".csv";
		$this->csv_filename = "/tmp/" . $this->pretty_filename;
		$fp = $this->overwrite_file( $this->csv_filename );

		//Load details of PO
		$po = new generic_orders( "", "", "", "", $this->prefs_tablename );
		$po->set_var( 'order_no', $this->PurchaseOrderToPrint );
		$po->get_purchase_order();
 		foreach( $po->purchase_order->line_items as $po_line_details )
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
			//echo "<br />";
			//echo "In foreach" . __FILE__ . ":" . __LINE__;
			$stock_id = $po_line_details->stock_id;
			$description = $po_line_details->item_description;
			$quantity = $po_line_details->qty_received;
		//Join EANs for items in PO to the number of items
			$result = get_all_item_codes( $stock_id );
        		while ($myrow = db_fetch($result))
        		{
				$ean = $myrow["item_code"];
	
				//for items on PO, find EANs that start with (0)20, (0)40 or 200
				//Generate a line in the CSV with the EAN surrounded by *, the EAN, and the description
				//	(mail merge to be printed on labels) for each quantity of that EAN recieved on that PO
				if( 
					strncmp( $ean, "200", 3 ) == 0 
					OR strncmp( $ean, "020", 3 ) == 0 
					OR strncmp( $ean, "040", 3 ) == 0 
					OR strncmp( $ean, "20", 2 ) == 0 
					OR strncmp( $ean, "40", 2 ) == 0 
				  )
				{
					for( $eancount=0; $eancount < $quantity; $eancount++ )
					{
						fwrite( $fp, "*" . $ean . "*," . $ean . "," . $description . "\n" );
						fflush( $fp );
					}
				}
			}
		}
		$this->close_file( $fp );
		return TRUE;
	}
	function po2labels()
	{
		if( $this->purchaseOrder2CSV() )
			return $this->email_csv();
		return FALSE;
	}
	function getLastPO()
	{
		$sql = "Select max(order_no) as last_po from 0_purch_orders";
		$result = db_query($sql, "Couldn't generate EANs" );
		//var_dump( $result );
		$row = mysql_fetch_assoc($result);
		$this->last_po = $row['last_po'];
	}
	function po2labelsAll()
	{
		//Need to get the lowest and highest PO numbers
		//Set the PO number, call the functions
		for( $pos=1; $pos <= $this->last_po; $pos++ )
		{
			$this->PurchaseOrderToPrint = $pos;
			if( $this->purchaseOrder2CSV() )
				$this->email_csv();
		}
		return FALSE;
	}
	function print_EANs_form()
	{
		/*
		 * This function is to print into a CSV EANs from a purchase order
		 * It needs to grab the EANs for those items, and generate a row
		 * in the CSV for each item, for each quantity so that the CSV
		 * can be mail-merged and printed on labels.
		 */
                start_form(true);
                    hidden('action', 'email');
                    submit_center('email', 'Export to CSV the labels to be printed for PO ' . $this->PurchaseOrderToPrint );
                end_form();
                start_form(true);
                    hidden('action', 'emailall');
                    submit_center('emailall', 'Export to CSV the labels to be printed for ALL POs (Last is' . $this->last_po . ')');
                end_form();
	}
	function generate_EANs_form()
	{
                start_form(true);
                    hidden('action', 'genean');
                    submit_center('genean', 'Generate EANs for items that do not have them');
                end_form();
	}
	function generate_EANs()
	{
		/*
		 * var $EANPrefix ;
		 * var $GenerateRequestCount ;
		 * var $GenerateRandom ;
		 * var $LowestEAN ;
		 * var $HighestEAN ;
		 * var $LastEAN
		 */
		$prefix = $this->EANPrefix * 10000000000 + $this->LowestEAN;
		$range = $this->HighestEAN - $this->LowestEAN;
		if( $range < $this->GenerateRequestCount )
			$range = $this->GenerateRequestCount;

		if( $this->GenerateRandom )
		{
			//Generate random EANs within a range
			$sql = "insert into 0_item_codes (stock_id, description, category_id, quantity, inactive, item_code, is_foreign)
			select stock_id, description, category_id, quantity, inactive, " . $prefix . "+floor(RAND()*" . $range . "), 1 
			from 0_item_codes where stock_id not in (SELECT stock_id FROM `0_item_codes` where item_code <> stock_id and is_foreign = 1)";
		}
		else
		{
			//generate EANs in a series
			// *NOTE*
			// This current query generates random...
			$sql = "insert into 0_item_codes (stock_id, description, category_id, quantity, inactive, item_code, is_foreign)
			select stock_id, description, category_id, quantity, inactive, " . $prefix . "+floor(RAND()*" . $range . "), 1 
			from 0_item_codes where stock_id not in (SELECT stock_id FROM `0_item_codes` where item_code <> stock_id and is_foreign = 1)";
		}
		$result = db_query($sql, "Couldn't generate EANs" );
		//We should display the result - is it the number of EANs generated?
		display_notification("Note that the generation of EANs is currently ALWAYS random regardless of the config value.  It is a defect, and
					some day we will get to rewriting this function to allow sequential EANs.");
	}
	function some_quyery()
	{
		/******************************************************
		 *
		 *	This function isn't meant to be run.  It is
		 *	just example code.
		 ******************************************************/
		return;
		
                $sql = "SELECT MAX(`order_no`) as max FROM `" . $this->company_prefix . "purch_orders`";
                $result = db_query($sql, "Couldn't get PO ID range" );
                $this->order_no = max((int)$result['max'], $this->last_order_no+1);

                return mysql_fetch_assoc($result);
	}
	function config_form()
	{
                start_form(true);
                start_table(TABLESTYLE2, "width=40%");
                $th = array("Config Variable", "Value");
                table_header($th);
                $k = 0;
                alt_table_row_color($k);
                        /* To show a labeled cell...*/
                        //label_cell("Table Status");
                        //if ($this->found) $table_st = "Found";
                        //else $table_st = "<font color=red>Not Found</font>";
                        //label_cell($table_st);
                        //end_row();
/*
                echo combo_input("order_no2", $this->order_no, $sql, 'supp_name', 'order_no',
                        array(
                                //'format' => '_format_add_curr',
                                'order' => array('order_no'),
                                //'search_box' => $mode!=0,
                                'type' => 1,
                                //'search' => array("order_no","supp_name"),
                                //'spec_option' => $spec_option === true ? _("All Suppliers") : $spec_option,
                                'spec_id' => $all_items,
                                'select_submit'=> $submit_on_change,
                                'async' => false,
                                //'sel_hint' => $mode ? _('Press Space tab to filter by name fragment') :
                                //_('Select supplier'),
                                //'show_inactive'=>$all
                        )
                );
*/
		//This currently only puts text boxes on the config screen!
                foreach( $this->config_values as $row )
                {
                                text_row($row['label'], $row['pref_name'], $this->$row['pref_name'], 20, 40);
                }
                end_table(1);
                if (!$this->found) {
                    hidden('action', 'create');
                    submit_center('create', 'Create Table');
                } else {
                    hidden('action', 'update');
                    submit_center('update', 'Update Configuration');
                }
                end_form();
	}

}
?>
