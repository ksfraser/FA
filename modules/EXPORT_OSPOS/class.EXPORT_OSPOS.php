<?php

require_once( 'class.generic_interface.php' ); 

/**************************************************************
 *
 *	This module exports the inventory from Frontaccounting
 *	into OSPOS. 
 *
 **************************************************************/

//class EXPORT_OSPOS
class EXPORT_OSPOS extends generic_interface
{
	//var $ ;
	var $mailto;
	var $email_subject;	
	var $email_body;
	var $csv_filename;
	var $pretty_filename;
	var $location;		//Till location
	var $url;
	function __construct( $pref_tablename )
	{
		//echo "generate EAN constructor";
		parent::__construct( $pref_tablename );
		
		//$this->config_values[] = array( 'pref_name' => '', 'label' => '' );
		$this->config_values[] = array( 'pref_name' => 'mailto', 'label' => 'Email Address to mail the resulting CSV of barcode labels.' );
		$this->config_values[] = array( 'pref_name' => 'location', 'label' => 'Till Location Code. ALL exports for all locations in 1 run.' );
		$this->config_values[] = array( 'pref_name' => 'url', 'label' => 'Till OSPOS URL' );
		$this->config_values[] = array( 'pref_name' => 'supplier_id', 'label' => 'OSPOS Supplier ID (TILL specific - 6 laptop or 18 ws)' );
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'config_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'install', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'updateprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'EXPORT OSPOS', 'action' => 'export_form', 'form' => 'EXPORT_OSPOS_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'CSV Emailed', 'action' => 'email', 'form' => 'po2labels', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'CSV Emailed', 'action' => 'emailall', 'form' => 'po2labelsAll', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'EXPORT OSPOS Action', 'action' => 'export', 'form' => 'EXPORT_OSPOS_action', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'EXPORT CSV', 'action' => 'export_csv_form', 'form' => 'EXPORT_CSV_form', 'hidden' => FALSE );

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
	function export_csv()
	{
                $fname = 'items.csv';
                $filename = '/tmp/' . $fname;
                $fp = fopen( $filename, 'w' );
		$headers = array( 'UPC/EAN/ISBN', 'Item Name', 'Category', 'Supplier ID', 'Cost Price', 'Unit Price', 'Tax 1 Name', 'Tax 1 Percent', 'Tax 2 Name ', 'Tax 2 Percent', 'Reorder Level', 'Description', 'Allow Alt Description', 'Item has Serial Number', 'custom1', 'custom2', 'custom3', 'custom4', 'custom5', 'custom6', 'custom7', 'custom8', 'custom9', 'custom10', 'location_id', 'quantity' );
                foreach( $headers as $column )
                {
                        fwrite( $fp, $column . "," );
                }
                        fwrite( $fp, "\n" );
    		$sql = "SELECT m.stock_id as stock_id, SUM(m.qty) as quantity, 
				s.description as description, s.long_description as long_description, 
				i.item_code as EAN,
				p.price as retail_price
			FROM " . TB_PREF . "stock_moves m,  
				" . TB_PREF . "stock_master s, 
				" . TB_PREF . "item_codes i,
				" . TB_PREF . "prices p

        		WHERE s.stock_id = m.stock_id 
				AND s.stock_id = i.stock_id
				AND m.loc_code = " . db_escape($this->location) . " 
				AND i.is_foreign = 1
				and p.stock_id = s.stock_id
				and p.sales_type_id = 1
			GROUP BY m.stock_id";


    		$result = db_query($sql, "Stock query failed");
    		while ($myrow = db_fetch_row($result) )
		{
                                fwrite( $fp, '"' . $myrow['EAN'] . '",' );
                                fwrite( $fp, '"' . $myrow['description'] . '",' );
                                fwrite( $fp, '"Bagpipes",' );
                                fwrite( $fp, '"6",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"' . $myrow['retail_price'] . '",' );
                                fwrite( $fp, '"GST",' );
                                fwrite( $fp, '"5",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"0",' );
                                fwrite( $fp, '"' . $myrow['long_description'] . '",' );
                                fwrite( $fp, '"N",' );
                                fwrite( $fp, '"N",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"",' );
                                fwrite( $fp, '"1",' );
                                fwrite( $fp, '"' . $myrow['quantity'] . '",' );
                                fwrite( $fp, "\n" );
                                $rowcount++;
                }
                fflush( $fp );
                fclose( $fp );
                display_notification("$rowcount rows of items created, $ignoredrows rows of items ignored, $this->maxrowsallowed rows allowed.");
                if( isset( $this->mailto ) )
                {
                        $data = file_get_contents( $filename );
                        $uu_data = "begin 644 " . $fname . "\n" . convert_uuencode($data) . "end\n";
                        $subject =  'Items for OSPOS import - CSV file';
                        $headers = 'From: sales@fraserhighlandshoppe.ca' . "\r\n" .
                            'Reply-To: sales@fraserhighlandshoppe.ca' . "\r\n";

                        mail($this->mailto, $subject, $uu_data, $headers);
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
		$this->close_file( $fp );
		return TRUE;
	}
	function EXPORT_OSPOS_action()
	{
		$count = 0;
		$error = 0;
		$scount = 0;
		$serror = 0;
		display_notification("Exporting inventory to " . $this->url );
        	$date_ = Today();
    		$date = date2sql($date_);
/*
    		$sql = "SELECT m.stock_id as stock_id, SUM(m.qty) as quantity, 
				s.description as description, s.long_description as long_description, 
				i.item_code as EAN,
				c.description as category, c.dflt_tax_type as tax,
				t.name as tax_name, t.rate as tax_rate,
				p.price as retail_price
			FROM " . TB_PREF . "stock_moves m,  
				" . TB_PREF . "stock_master s, 
				" . TB_PREF . "item_codes i,
				" . TB_PREF . "stock_category c,
				" . TB_PREF . "tax_types t,
				" . TB_PREF . "prices p

        		WHERE s.stock_id = m.stock_id 
				AND s.stock_id = i.stock_id
				AND m.loc_code = " . db_escape($this->location) . " 
				AND m.tran_date <= '$date' 
				AND i.is_foreign = 1
				and s.category_id = c.category_id
				and c.dflt_tax_type = t.id
				and p.stock_id = s.stock_id
				and p.sales_type_id = 1
			GROUP BY m.stock_id";
*/
    		$sql = "SELECT m.stock_id as stock_id, SUM(m.qty) as quantity, 
				s.description as description, s.long_description as long_description, 
				i.item_code as EAN,
				p.price as retail_price
			FROM " . TB_PREF . "stock_moves m,  
				" . TB_PREF . "stock_master s, 
				" . TB_PREF . "item_codes i,
				" . TB_PREF . "prices p
        		WHERE s.stock_id = m.stock_id 
				AND s.stock_id = i.stock_id";
/*20150905 Setting Location to ALL means not being required to run this multiple times */
			if( 
				strncmp("ALL", 
					db_escape($this->location), 3) 
				<> 0 
			)
			{
				$sql .= " AND m.loc_code = " . db_escape($this->location);
			}
			$sql .= " AND i.is_foreign = 1
				and p.stock_id = s.stock_id
				and p.sales_type_id = 1
			GROUP BY m.stock_id";


    		$result = db_query($sql, "Stock query failed");

		$path2integration = "../../../integration/";
		require_once( $path2integration .  '/post_item2ospos.class.php' );
		//$item = new post_item2ospos( "http://defiant/fhs/POS/index.php/items");
		$item = new post_item2ospos( $this->url );	//DEFAULT URL is set by class
		$item->set_var( "username", "frontacc" );
		$item->set_var( "password", "frontacc" );
		$item->login();

    		while ($myrow = db_fetch_row($result) )
		{
			//var_dump( $myrow );
        		$_item['name'] = $myrow[2];			//Mandatory
        		$_item['description'] =  $myrow[3];
        		$_item['item_number'] =  $myrow[4];
        		//$_item['category'] =  $myrow[5];		//Mandatory
        		$_item['unit_price'] = $myrow[5];		//Mandatory
        		$_item['cost_price'] = $myrow[5] * .85;		//Mandatory
			if( isset( $this->supplier_id ) )
			{
        			$_item['supplier_id'] = $this->supplier_id;   //Mandatory
			}
			else
			{
				//18 on fhsws001, 6 on fhs-laptop1
        			$_item['supplier_id'] = "6";   //KACS		//Mandatory
			}
			
        		$_item['supplier'] = "KACS";			//Mandatory

        		$_item['is_deleted'] = "0";
        		$_item['is_serialized'] = "0";
/*20150905 Setting quantity to positive*/
			if( $myrow[1] < 1 )
			{
				$myrow[1] = 1;
			}
        		$_item['1_quantity'] =  $myrow[1];
        		$_item['location_id_quantity'] = $myrow[1];	//Mandatory
        		$_item['receiving_quantity'] = "1";		//This is how many are recieved as a unit :(
			$_item['reorder_level'] = "1";			//Mandatory
        		$_item['tax_names'][0] = "GST";
        		$_item['tax_percents'][0] = "5";
/*
        		$_item['tax_names'][0] =  $myrow[7];
        		$_item['tax_percents'][0] = $myrow[8];
        		$_item['location_id'] = "1";

*/

			$item->set_var( "data", $_item );
			if( $item->send_new() )
			{
				$count++;
				display_notification("Sent item: " . $_item['item_number']  . " : " . $_item['name'] . " with quantity  " . $_item['1_quantity'] );
			}
			else
			{
				$error++;	//Should we try send_edit instead?
				display_notification( var_dump( $item->server_response ) );
			}
			$item->send_edit();
			//Anything from JAM is not necessarily being entered as a foreign...
			$_item['item_number'] =  $myrow[0];
			if( $item->send_new() )
			{
				$scount++;
				display_notification("Sent item: " . $_item['item_number']  . " : " . $_item['name'] . " with quantity  " . $_item['1_quantity'] );
			}
			else
			{
				$serror++;
				display_notification( var_dump( $item->server_response ) );
			}
		}
		//display_notification("Export completed." );
		display_notification("Export completed. " . $count . " items succeeded, " . $error . " items failed. " . $scount . " stock_id succeeded, " . $serror . " stock_id failed" );

	}
	function EXPORT_OSPOS_form()
	{
                start_form(true);
                    hidden('action', 'export');
                    submit_center('export', 'Export inventory items to OSPOS');
                end_form();
	}
	function EXPORT_CSV_form()
	{
                start_form(true);
                    hidden('action', 'export_csv');
                    submit_center('export_csv', 'Export inventory items to OSPOS via CSV');
                end_form();
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
