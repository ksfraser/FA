<?php

//TODO: check in add_item for the barcode to be either an stock_id or a foreign_code

require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 
//require_once( 'class.generic_interface.php' ); 
define('ST_Inventory', 987 );
define('ST_INVENTORY', 98 );	//For REFERENCES

/* 
 *	This module is for doing a stock taking (aka inventory).
 *
 *	I intend that we can do partial and full inventories.
 *
 *	At this time it is only a COUNT that we are going for.
 * 	Any items with counts that don't match need to be flagged, and adjustments made into/out of 
 * 	a HOLDING location.  We will need to define which is the HOLDING location
 *	out of the companies' LOCATION table.
 *
 *	And then the Inventory Manager will need to appropriately adjust the inventory
 *	for the holding tank either through a transfer (i.e. do inventory at 2nd location)
 *	or through shrinkiage.
 *
 *	Need a reminder to set that config item.
 */

/**********************************************************************************
//from inventory/transfers.php
$path_to_root = "../..";
include_once($path_to_root . "/includes/ui/items_cart.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
//include_once($path_to_root . "/inventory/includes/item_adjustments_ui.inc");	//display_inventory_header()
//include_once($path_to_root . "/inventory/includes/stock_transfers_ui.inc");	//display_InventoryItems()
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

//From sales/Sales_inventory....
include_once($path_to_root . "/sales/includes/cart_class.inc");
//include_once($path_to_root . "/includes/session.inc");
//include_once($path_to_root . "/sales/includes/sales_ui.inc");
//include_once($path_to_root . "/sales/includes/ui/sales_inventory_ui.inc");
//include_once($path_to_root . "/sales/includes/sales_db.inc");
//include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
//include_once($path_to_root . "/reporting/includes/reporting.inc");

include_once($path_to_root . "/includes/db/inventory_db.inc");

$path_to_root = "../..";
$page_security = 'SA_Inventory';
set_page_security( @$_SESSION['InventoryItems']->trans_type,
        array(  ST_Inventory=>'SA_Inventory',
                        ST_Inventory => 'SA_Inventory',
                        ST_CUSTDELIVERY => 'SA_Inventory',
                        ST_Inventory => 'SA_Inventory'),
        array(  'NewOrder' => 'SA_Inventory',
                        'ModifyOrderNumber' => 'SA_Inventory',
                        'AddedID' => 'SA_Inventory',
                        'UpdatedID' => 'SA_Inventory',
                        'NewQuotation' => 'SA_Inventory',
                        'ModifyQuotationNumber' => 'SA_Inventory',
                        'NewQuoteToSalesOrder' => 'SA_Inventory',
                        'AddedQU' => 'SA_Inventory',
                        'UpdatedQU' => 'SA_Inventory',
                        'NewDelivery' => 'SA_Inventory',
                        'AddedDN' => 'SA_Inventory',
                        'NewInvoice' => 'SA_Inventory',
                        'AddedDI' => 'SA_Inventory'
                        )
);
*********************************************************************************/




/**************************************************************
 *
 *	This module allows an employee to do a partial stock taking
 *
 **************************************************************/

//class Inventory
class Inventory_model extends Inventory
{
	//var $ ;
	var $mailto;
	var $email_subject;	
	var $email_body;
	var $csv_filename;
	var $pretty_filename;
	var $location;		//Inventory location
	var $holdtank;		//Location that acts as holding tank for corrections
	var $to_location;	//Used for the xfer_all functions
	var $from_location;	//Used for the xfer_all functions
	var $url;
	//var $javascript; 	//in generic_interface 	//in generic_interface
	var $trans_type;
	var $trans_no;
	var $barcode;
	var $locationPrefix;
	var $locationPrefix2;
	var $line_items;	//Set as [$count][...].  	//quantity = amount being transferred (diff).  
								//counted is how many we counted.  
								//qoh is how many the system thinks we have at this location
								//stock_id is the stock_id in stock_master
	var $reference;		//set in create_cart
	var $Comments;
	var $document_date;
	var $ex_rate;
	var $deliver_to;
	var $delivery_address;
	//var $cart_id;
	//var $cart;	//includes/ui/items_cart.inc
	var $copyfromcount;
	var $copytocount;
	var $title;
	var $path_to_root;
	var $update_quantity;
	var $add_quantity;
	var $item_text;
	var $stock_id;
	var $change_line_number;
	var $table_interface;
	var $found;
	var $import_single_file;	//Should we allow multiple files to be loaded at once.
	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		global $path_to_root;
		$this->path_to_root = $path_to_root;
		$this->copyfromcount = 0;
		$this->copytocount = 0;
		//Using 998 and 999 to be the starting numbers for barcodes indicating location
		$this->set_var( 'locationPrefix', "999" );
		$this->set_var( 'locationPrefix2', "998" );
		$this->item_text = null;
		$this->import_single_file = true;
		require_once( '../ksf_modules_common/class.table_interface.php' );
		$this->table_interface = new table_interface();

		$this->trans_type = ST_LOCTRANSFER;
		if( isset( $_POST['location'] ) )
		{
			$this->setLocation( $_POST['location'] );
		}
		if( isset( $_GET['location'] ) )
		{
			$this->setLocation( $_GET['location'] );
		}
		global $Refs;
	        $this->reference = rand();
		$this->create_cart();
/***** CONTROLLER **************
		$this->handlePOST();
***** CONTROLLER **************/
	}
        function install()
        {
                $this->create_prefs_tablename();
                $this->loadprefs();
                $this->updateprefs();
        }
	function create_inventory_count_table()
	{
	}
	function handlePOST()
	{
		global $Ajax;
		if (isset($_POST['AddItem']))
		        $this->handle_new_item();
		if (isset($_POST['document_date']))
			$this->document_date = $_POST['document_date'];
		if (isset($_POST['location']))
		{
			$this->location = $_POST['location'];
			echo __FILE__ . ":" . __LINE__ . " Location set to " . $this->location . "<br />";
		}
/*
		if( isset( $_POST['overunder_form'] ) )
		{
			$_POST['action'] = 'overunder_form';
			$_GET['action'] = 'overunder_form';
			$this->page_modified();
		}
*/
		if (isset($_POST['UpdateItem']))
		{
		        $this->handle_update_item();
			$this->copy_to_session();
		}
		$id = find_submit('Delete');
		if ($id!=-1)
		{
		        $this->handle_delete_item($id);
			$this->copy_to_session();
		}
	$Ajax->activate('items_table');
	return;
/*
 *	Don't want to do this until they've been modded
 */
		
		if (isset($_POST['CancelItemChanges'])) {
		        $this->line_start_focus();
		}
		return;
	}
	function write()
	{
	}
	function process_inventory()
	{
/*
 *	Don't do anything until modded
 */
	return;
		
		$ret = $this->write(1);
		if ($ret == -1)
		{
		        display_error(_("The entered reference is already in use."));
		        $ref = get_next_reference($this->trans_type);
		        if ($ref != $this->reference)
		        {
		                display_error(_("The reference number field has been increased. Please save the document again."));
		                $_POST['ref'] = $this->reference = $ref;
		                $Ajax->activate('ref');
		        }
		        set_focus('ref');
		}
		else
		{
		        if (count($messages)) { // abort on failure or error messages are lost
		                $Ajax->activate('_page_body');
		                display_footer_exit();
		        }
		        $trans_no = key($_SESSION['InventoryItems']->trans_no);
		        $trans_type = $_SESSION['InventoryItems']->trans_type;
		        new_doc_date($_SESSION['InventoryItems']->document_date);
		        processing_end();
		        if ($modified) {
		                if ($trans_type == ST_Inventory)
		                        meta_forward($_SERVER['PHP_SELF'], "UpdatedQU=$trans_no");
		                else
		                        meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$trans_no");
		        } elseif ($trans_type == ST_Inventory) {
		                meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
		        } elseif ($trans_type == ST_Inventory) {
		                meta_forward($_SERVER['PHP_SELF'], "AddedQU=$trans_no");
		        } elseif ($trans_type == ST_Inventory) {
		                meta_forward($_SERVER['PHP_SELF'], "AddedDI=$trans_no&Type=$so_type");
		        } else {
		                meta_forward($_SERVER['PHP_SELF'], "AddedDN=$trans_no&Type=$so_type");
		        }
		}
	}
	function copy_from_session()
	{
		$this->copytocount++;
		//display_notification( "copyto count: " . $this->copytocount++ );

		if( isset( $_SESSION['reference'] ) )
		{
	        	$this->reference = $_SESSION['reference'];
			//display_notification( "Copy_from_session Ref: " . $this->reference );
		}
		if( isset( $_SESSION['Comments'] ) )
		{
	        	$this->Comments =  $_SESSION['Comments'];
			//display_notification( "Copy_from_session Comments: " . $this->Comments );
		}
		if( isset( $_SESSION['document_date'] ) )
		{
	        	$this->document_date = $_SESSION['document_date'];
			//display_notification( "Copy_from_session date: " . $this->document_date );
		}
		if( isset( $_SESSION['location'] ) )
		{
	        	$this->location = $_SESSION['location'];
			//display_notification( "Copy_from_session location: " . $this->location );
		}
		//Copy from SESSION to cart
		$json = $_SESSION['InventoryItems'];
		//First time through JSON is "[]"
		//display_notification( "Copy_from_session InventoryItems: " . $json );
		$data = json_decode( $json, true );
		foreach( $data as $line )
		{
			//For a stock taking, can't count what isn't there BUT we can set 0 (used during transfers)
			//Also no point having something without the stock_id
			if( $line['counted'] >= 0 AND isset( $line['stock_id'] ) )
			{
				$this->barcode = $this->get_master_sku( $line['stock_id'] );	
				//$this->barcode = $line['stock_id'];	//so find_cart_item can find it...
				$lineno = $this->find_cart_item();
				if( $lineno >= 0 )
				{
					//consolidate all of the same line items onto 1
					$count = $lineno;
					$this->line_items[$lineno]['counted'] += $line['counted'];
					$this->line_items[$lineno]['qoh'] += $line['qoh'];
					$this->line_items[$lineno]['quantity'] += $line['quantity'];
					//$this->line_items[$count]['item_description'] = $line['item_description'];
				}
				else
				{
					//Item not yet in the cart.
					$count = count($this->line_items);
					//display_notification( $line['stock_id'] );
					//display_notification( $line['counted'] );
					$this->line_items[$count]['stock_id'] = $line['stock_id'];
					$this->line_items[$count]['counted'] = $line['counted'];
					if( isset( $line['qoh'] ) )
						$this->line_items[$count]['qoh'] = $line['qoh'];
					if( isset( $line['quantity'] ) )
						$this->line_items[$count]['quantity'] = $line['quantity'];
					if( isset( $line['item_description'] ) )
						$this->line_items[$count]['item_description'] = $line['item_description'];
					else
						$this->line_items[$count]['item_description'] = "";
				}
			}
		}
		//display_notification( "exiting Copy_from_session" );
	}
	function copy_to_session()
	{
		$this->copyfromcount++;
		//display_notification( "copyfrom count: " . $this->copyfromcount++ );

		//Copy from cart to SESSION
		$count = 0;
		$l2 = array();
		foreach( $this->line_items as $line_item )
		{
			//can't have less than 0 of something
			if( $line_item['counted'] >= 0 )
			{
				//no point having a count on a NULL stock_id
				if( isset( $line_item['stock_id'] ) )
				{
					$stock_id = $this->get_master_sku( $line_item['stock_id'] );
					$l2[$count]['stock_id'] = $stock_id;
					$l2[$count]['counted'] = $line_item['counted'];
					if( isset( $line_item['qoh'] ) )
						$l2[$count]['qoh'] = $line_item['qoh'];
					if( isset( $line_item['quantity'] ) )
						$l2[$count]['quantity'] = $line_item['quantity'];
					if( isset( $line_item['item_description'] ) )
						$l2[$count]['item_description'] = $line_item['item_description'];
					$count++;
					//display_notification( "Copy2 count2: " . $count );
				}
			}
		}
		$json = json_encode( $l2 );
		//unset( $_SESSION['InventoryItems'] );
		$_SESSION['InventoryItems'] = $json;
		//display_notification( "Copy_to_session JSON: " . $json );

		if( isset( $this->reference ) )
	        	$_SESSION['reference'] = $this->reference;
		if( isset( $this->Comments ) )
	        	$_SESSION['Comments'] = $this->Comments;
	
		if( isset( $this->document_date ) )
	        	$_SESSION['document_date'] = $this->document_date;
		if( isset( $this->location ) )
	        	$_SESSION['location'] = $this->location;
		//display_notification( "Copy TO session location: " . $this->location );
	        //$_SESSION['cart_id'] = $this->cart_id;
	}
	function gen_reference()
	{
		$this->reference = rand();
	}

	/*@bool@*/ 
	function check_item_data()
	{
	        global $SysPrefs, $allow_negative_prices;
	
		$ret = TRUE;
	        if( !is_inventory_item(get_post('stock_id')) )
		{
	                display_error( __LINE__ . " " . _("The item is not in stock master"));
			$ret = FALSE;
		}
	        //if(!get_post('stock_id_text', true)) {
	        //        display_error( _("Item description cannot be empty."));
	        //        set_focus('stock_id_edit');
	        //}
	        if (!check_num('counted', 0) ) {
	                display_error( __LINE__ . " " . _("The item could not be updated because you are attempting to set the counted inventoryed to less than 0."));
	                $ret = FALSE;
	        } 
	        return $ret;
	}
	function handle_update_item()
	{
	        //if ($_POST['UpdateItem'] != '' && $this->check_item_data()) {
	        if( $_POST['UpdateItem'] != '' ) 
		{
/*
			foreach( $_POST as $key=>$val )
				//echo "UpdateItem POST values " . $key . "::" . $val . "<br />";
				 display_notification( __LINE__ . " UpdateItem POST values " . $key . "::" . $val  );
*/
			$this->line_items[$_POST['LineNo']]['counted'] = $_POST['counted'];
			display_notification( __LINE__ . ' updated counted for ' . $this->line_items[$_POST['LineNo']]['stock_id'] . ' to ' . $this->line_items[$_POST['LineNo']]['counted'] );
	        }
		//$this->copy_to_session();
	        $this->page_modified();
	  	$this->line_start_focus();
	}
	function handle_delete_item($line_no)
	{
	    	//$this->cart->remove_from_cart($line_no);
		//$lineno = $this->find_cart_item();
		display_notification( __LINE__ . ' handle_delete removing ' . $line_no . " item " . $this->line_items[$line_no]['stock_id'] );

		//$this->line_items[$line_no]['stock_id'] = "";
		$this->line_items[$line_no]['counted'] = -1;	//copy_to_session or _from_ should now discard this line
		//
		//unset( $this->line_items[$line_no]['stock_id'] );
		//unset( $this->line_items[$line_no]['counted'] );
		//$this->line_items[$line_no]['stock_id'] = null;
		//$this->line_items[$line_no]['counted'] = null;
		//var_dump( $this->line_items );
		//$this->line_items[$line_no] = null;
		$this->copy_to_session();
	        $this->page_modified();
	  	$this->line_start_focus();
	}
	function isStockid( $stock_id )
	{
		//echo __LINE__ . " DEBUG: isStockid<br />";
		include_once( $this->path_to_root . "/includes/db/inventory_db.inc" );
		return is_inventory_item( $stock_id );
	}
	/*@array@*/ function isItemCode( $item_code )
	{
		//echo __LINE__ . " DEBUG: isItemCode<br />";
	//Is the code in the Item_code table AND is NOT the native master code
        	$sql = "SELECT stock_id FROM "
        	        //. $this->tb_pref ."item_codes "
        	        . TB_PREF ."item_codes "
        	        . " WHERE item_code=".db_escape($item_code)."
        	                AND stock_id!=".db_escape($item_code);
        	$res = db_query($sql, "where used query failed");
		$fet = db_fetch_assoc( $res );
		if( count( $fet ) > 0  )
		{
			//echo __FILE__ . ":" . __LINE__ . " DEBUG: isItemCode count > 0<br />";
		//should we set the data into a set of variables?
			return TRUE;
		}
		return FALSE;
	}
	function inItemCode( $item_code )
	{
		echo __LINE__ . " DEBUG: inItemCode<br />";
	//Is the code in the Item_code table (can be the native master code
        	$sql = "SELECT count(stock_id) as count, * FROM "
        	        . $this->tb_pref ."item_codes "
        	        . " WHERE item_code=".db_escape($item_code);
        	          
        	$res = db_query($sql, "where used query failed");
		$fet = db_fetch( $res );
		if( count( $fet ) > 0  )
		{
			echo __LINE__ . " DEBUG: inItemCode count > 0<br />";
		//should we set the data into a set of variables?
			return TRUE;
		}
		return FALSE;
	}
	function get_item_description()
	{
		//Using stock_id get the description

 		$item_row = get_item($this->stock_id);	//includes/db/inventory_db.inc
		$ret = "Item not found in stock_master";

                if ($item_row == null)
		{
                        display_error("invalid item : $stock_id", "");
		}
		if( strlen($item_row["description"]) > 1)
			$ret = $item_row["description"];

/*
                $this->mb_flag = $item_row["mb_flag"];
                $this->units = $item_row["units"];
                $this->item_description = $item_row["description"];
                $this->standard_cost = $item_row["actual_cost"];
*/
		return $ret;
	}
	function get_master_sku( $barcode )
	{
			//NEED to check foreign_codes to see if it is the item, or a SKU?
			if( $this->isStockid( $barcode ) )
			{
				$stock_id = $barcode;
			}
			else
			{
				$ItemCodeArr = $this->isItemCode( $barcode );
				if( $ItemCodeArr != FALSE )
				{
					//convert to master sku (stock_id)
					if( $this->isStockid( $ItemCodeArr['stock_id'] ) )
						$stock_id = $ItemCodeArr['stock_id'];
					else
					{
						display_error( "Major FUBAR: " . $barcode . "::" . $ItemCodeArr['stock_id'] );
						$stock_id = "";
					}
				}
				else
				{
					$stock_id = "";
					//we don't have the item YET.
					display_error( "Barcode not in the system: " . $barcode );
				}
			}
			return $stock_id;
	}
	function add_item()
	{
		$line = $this->find_cart_item();
		if( $line >= 0 )
		{
			//echo __LINE__ . " DEBUG: Barcode " . $this->barcode . " has spot " . $line . " and counted " . $this->line_items[$line]['counted'] . "<br />";
			//display_notification( __LINE__ . " " . $this->barcode . ' found on line ' . $line );
			//display_notification( __LINE__ . " " . $this->line_items[$line]['stock_id'] . " Current quantity " .$this->line_items[$line]['counted'] . ' and adding ' . $this->add_quantity );
			$this->line_items[$line]['counted'] = $this->line_items[$line]['counted'] + $this->add_quantity;
			//display_notification( __LINE__ . " " . $this->line_items[$line]['stock_id'] . " New total: " .$this->line_items[$line]['counted'] );
			//$this->update_item( $line );
		}
		else
		{
			//display_notification( __LINE__ . ' new ' . $this->barcode );
			//display_notification( __LINE__ . " " .var_dump( $_POST ) );
			$newcount = count($this->line_items);
			$this->stock_id = $this->get_master_sku( $this->barcode );
			$this->line_items[$newcount]['stock_id'] = $this->stock_id;
			$this->line_items[$newcount]['counted'] = $this->add_quantity;
			if( isset( $this->item_text ) )
				$this->line_items[$newcount]['item_description'] = $this->item_text;
			else
				$this->line_items[$newcount]['item_description'] = $this->get_item_description();
			//display_notification( __LINE__ . ' text ' . $this->line_items[$newcount]['item_description'] );
			//$this->copy_to_session();
		}
		$this->copy_to_session();
	        //$this->page_modified();
		global $Ajax;
		$Ajax->activate('items_table');
	}
	function find_cart_item()
	{
		//echo __LINE__ . " DEBUG: find_cart_item: Barcode " . $this->barcode . "<br />";
		$count = 0;
		$itemcount = count( $this->line_items );
		//echo __LINE__ . " DEBUG: find_cart_item: itemcount in cart: " . $itemcount . "<br />";
		//display_notification( "511: " . var_dump( $_POST ) );
		for( $count; $count < $itemcount; $count++ )
		{
			if( isset( $_POST['stock_id'] ) )
			{
				if( $this->line_items[$count]['stock_id'] == $_POST['stock_id'] )
				{
					return $count;
				}
			}
			else
			if( isset( $this->barcode ) )
			{
				if( $this->line_items[$count]['stock_id'] == $this->barcode )
				{
					return $count;
				}
			}
			//display_notification( __LINE__ . " count: " . $count . "::stock_id: " . $this->line_items[$count]['stock_id'] . "::barcode: " . $this->barcode . ":<br />" );
			//echo __LINE__ .  " DEBUG: count: " . $count . "::stock_id: " . $this->line_items[$count]['stock_id'] . "::barcode: " . $this->barcode . ":<br />";
		}
		return -1;
	}
	function handle_new_item()
	{
		//Need to add item onto the cart
	
		$this->barcode = get_post('stock_id');
		//$this->item_text = get_post('_stock_id_text');
		$this->add_quantity = get_post('counted');
		$this->add_item();
	        unset($_POST['_stock_id_edit'], $_POST['stock_id']);
	        $this->page_modified();
	        $this->line_start_focus();
	}
	function  handle_cancel_inventory()
	{
/*
	        global $path_to_root, $Ajax;
	
	                if ($_SESSION['InventoryItems']->trans_no != 0)
	                        delete_sales_inventory(key($_SESSION['InventoryItems']->trans_no), $_SESSION['InventoryItems']->trans_type);
	                display_notification(_("This sales quotation has been cancelled as requested."), 1);
	                submenu_option(_("Enter a New Sales Quotation"), "/sales/sales_inventory_entry.php?NewQuotation=Yes");
	                if ($_SESSION['InventoryItems']->trans_no != 0) {
	                        $inventory_no = key($_SESSION['InventoryItems']->trans_no);
	                        if (sales_inventory_has_deliveries($inventory_no))
	                        {
	                                close_sales_inventory($inventory_no);
	                                display_notification(_("Undelivered part of inventory has been cancelled as requested."), 1);
	                                submenu_option(_("Select Another Sales Order for Edition"), "/sales/inquiry/sales_inventorys_view.php?type=".ST_Inventory);
	                        } else {
	                                delete_sales_inventory(key($_SESSION['InventoryItems']->trans_no), $_SESSION['InventoryItems']->trans_type);
	
	                                display_notification(_("This sales inventory has been cancelled as requested."), 1);
	                                submenu_option(_("Enter a New Sales Order"), "/sales/sales_inventory_entry.php?NewOrder=Yes");
	                        }
	                } else {
	                        processing_end();
	                        meta_forward($path_to_root.'/index.php','application=inventorys');
	                }
	        $Ajax->activate('_page_body');
	        processing_end();
	        display_footer_exit();
*/
	}
	function create_cart()
	{
		//Check to see if we have a cart in progress
		if( !isset( $_SESSION['InventoryItems'] ) )
		{
			$this->tran_date = new_doc_date();
			$this->copy_to_session();
		}
		else
		{
	        	$this->copy_from_session();	//Copy line items into cart from SESSION
		}
	}
	function create_location_scancode_table()
	{
		$tablename = $this->company_prefix . "location_scancode";
                $sql = "DROP TABLE IF EXISTS " . $tablename;
                        db_query($sql, "Error dropping table" . $tablename);
                $sql = "CREATE TABLE `" . $tablename . "` (
                         `location` char(32) NOT NULL default \"\",
                         `scancode` varchar(100) NOT NULL default \"\",
                          PRIMARY KEY  (`name`))
                          ENGINE=MyISAM";
                db_query($sql, "Error creating table" . $tablename);
	}
	/*@bool@*/ 
	function hasBarcode()
	{
		return FALSE;
	}
	function setlocation( $loc )
	{
		$this->location = $loc;
	        $_SESSION['location'] = $this->location;
		display_notification( "Copy TO session location: " . $this->location );
	}
	/*@bool@*/ 
	function haslocation()
	{
		if( isset( $this->location ) )
			return TRUE;
		else
		if( isset( $_POST['location'] ) )
		{
			$this->location = $_POST['location'];
			return TRUE;
		}
		return FALSE;
	}
	/*@bool@*/ 
	function islocationCode( $upc )
	{
		return TRUE;
		//from inventory/manage/locations.php
		include_once($this->path_to_root . "/includes/ui.inc");
		include_once($this->path_to_root . "/inventory/includes/inventory_db.inc");
		$_POST['loc_code'] = strtoupper( $upc );

		$result = get_item_locations(check_value('show_inactive'));
		while ($myrow = db_fetch($result))
		{
       		        $_POST['loc_code'] = $myrow["loc_code"];
	                $_POST['location_name']  = $myrow["location_name"];
	                $_POST['delivery_address'] = $myrow["delivery_address"];
	                $_POST['contact'] = $myrow["contact"];
	                $_POST['phone'] = $myrow["phone"];
	                $_POST['phone2'] = $myrow["phone2"];
	                $_POST['fax'] = $myrow["fax"];
	                $_POST['email'] = $myrow["email"];
		}
		hidden("loc_code");
		label_row(_("Location Code:"), $_POST['loc_code']);
		submit_add_or_update_center($selected_id == -1, '', 'both');

		//Check to see if the prefix of the scanned upc matches our 2 prefixes
		//defined above.
		//ALSO check that it doesn't match the LOCATIONS table.
		return FALSE;
	}
	function get_item_qoh_location( $stock_id )
	{
                // collect quantities by stock_id
		return get_qoh_on_date($stock_id, $this->location, $this->document_date);	//includes/db/inventory_db.inc
	}
	function get_items_qoh_location()
	{
		//Run through the entire list getting the QOH for each
                foreach ($this->line_items as $line_no => $line_item)
                {
			$this->line_items[$line_no]['qoh'] = $this->get_item_qoh_location($this->line_items[$line_no]['stock_id']);
                }
	}
	/*****************************************************************************************************************
	 *
	 * 	function xfer_all_to_location
	 *
	 * 	This function will transfer all inventory from a given location
	 * 	into another.  You would use this if you have a temporary
	 * 	inventory location (i.e. trade show/farmers market) where you bring
	 * 	inventory from multiple locations into 1 so you sell out of one place
	 * 	at that show/market rather than having to hunt for the inventory and 
	 * 	worse having one order from multiple locations.
	 *
	 *
	 *
	 *****************************************************************************************************************/
	//Assumption/requirement the passed in variable is a 1D array of SKUs.
	function csv2cart( $lines_arr )
	{
		//display_notification( __LINE__ );
		$this->create_cart();
		$this->add_quantity = 1;
		foreach( $lines_arr as $line )
		{
			$this->barcode = $line[0];
			$this->add_item();
		}
	       // $this->page_modified();
	        //$this->line_start_focus();
	}
	function Import_text_file()
	{
		display_notification( __LINE__ );
		$this->title = "Import Text File of counts";
  		$_SESSION['page_title'] = _($help_context = $this->title);
		//$_SESSION['page_title'] = _($help_context = "Inventory Stock Taking");
		require_once( '../ksf_modules_common/class.ksf_file.php' );
		$ksf_ui_class = new ksf_ui_class();
		$ksf_ui_class->set( "section_title", "Section Title", false );
		$ksf_ui_class->set( "instruction1", "This routine expects a file that is a series of UPCs, one per line, and nothing else on the line.", false );
		$ksf_ui_class->set( "hidden1", array( 'name' => 'action', 'value' => 'call_import' ), false );
		$ksf_ui_class->set( "hidden2", array( 'name' => 'file_type', 'value' => 'csv' ), false );
		$upload = new ksf_file_upload( "inventory_count_" . date('Ymd') . "csv", $ksf_ui_class, "import_files", $this->import_single_file );
		//$upload = new ksf_file_upload( "inventory_count_" . date('Ymd') . "csv", $ksf_ui_class, "import_files", false );
		//$upload->set( "ui_class", $ksf_ui_class, false );
		$upload->set( "upload_button_name", "upload", false );
		$upload->set( "upload_button_label", "Upload File", false );
		$upload->upload_form( true, '', 'csv import' );
	
		//hidden( 'action', 'call_import' );
	}
	function import_csv()
	{
		display_notification( __LINE__ );
		//We should have a file upload to move and process.
		require_once( '../ksf_modules_common/class.ksf_file.php' );
		$ksf_ui_class = new ksf_ui_class();
		$upload = new ksf_file_upload( "inventory_count_" . date('Ymd') . "csv", $ksf_ui_class, "import_files", $this->import_single_file );
		//$upload = new ksf_file_upload( "inventory_count_" . date('Ymd') . "csv", $ksf_ui_class, "import_files", false );
		$upload->process_files();
		$d_array = $upload->get( 'a_data' );
		foreach( $d_array as $data_arr )
		{
			$linecount = $data_arr['count'];
			$header = $data_arr['header'];
			$lines_arr = $data_arr['data'];
			$this->csv2cart( $lines_arr );
		}

	}
	function handle_new_inventory()
	{
	        if (isset($_SESSION['InventoryItems']))
	        {
	                $_SESSION['InventoryItems']->clear_items();
	                unset ($_SESSION['InventoryItems']);
	        }
		$this->create_cart();
		$_SESSION['InventoryItems'] = new items_cart(ST_Inventory);
	        $_POST['InventoryDate'] = new_doc_date();
	        if (!is_date_in_fiscalyear($_POST['InventoryDate']))
	                $_POST['InventoryDate'] = end_fiscalyear();
	        $_SESSION['InventoryItems']->tran_date = $_POST['InventoryDate'];
	}
	function init_tables_form()
	{
            	display_notification("init tables form");
		$this->call_table( 'init_tables_completed_form', "Init Tables" );
	}

	function create_table_inventory_last_performed()
	{
		$table_details = array();
		$fields_array = array();
		$fields_array[] = array('name' => 'stock_id', 'type' => 'varchar(32)' );
		$fields_array[] = array('name' => 'location', 'type' => 'varchar(32)' );
		$fields_array[] = array('name' => 'inventory_date', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'current_timestamp' );
		//$table_details['tablename'] = TB_PREF . "inventory_last_performed";
		$table_details['tablename'] = $this->company_prefix . "inventory_last_performed";
		$table_details['index'][0] = array( 'keyname' => "item-location", 'columns' => "stock_id, location", 'type' => 'unique' );

		$this->table_interface->table_details = $table_details;
		$this->table_interface->fields_array = $fields_array;
		$this->table_interface->create_table( $table_details, $fields_array );
	}
	function record_last_inv_date( $stock_id )
	{
		//Record the fact we did an inventory count for an item for a location
		//$stock_id
		//$this->location,
		//$this->document_date, 
		$sql = "replace into " . $this->company_prefix . "inventory_last_performed( stock_id, location, inventory_date ) values ( '" . $stock_id . "', '" . $this->location . "', now() )";
		//$sql = "replace into " . $this->company_prefix . "inventory_last_performed( stock_id, location, inventory_date ) values ( '" . $stock_id . "', '" . $this->location . "', '" . $this->document_date ."')";
        	$res = db_query($sql, "Record last inventory failed");
		
	}
	function process()
	{
		$trans_no = get_next_trans_no(ST_LOCTRANSFER);
		foreach( $this->line_items as $item )
		{
			if(  $item['quantity'] != 0 )
			{
				//If the count equals QOH no point doing a transfer
				add_stock_transfer_item($trans_no, $item['stock_id'],$this->holdtank, $this->location,
					$this->document_date, $this->trans_type, $this->reference, $item['quantity'] );
			}
			$this->record_last_inv_date( $item['stock_id'] );
		}
		//20180821 KSF reset the list so that we don't have all the items we just dealt with on screen again
		$this->line_items = array();
	        new_doc_date($this->document_date);
		$this->copy_to_session();
        	meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
	}
	function processing_start()
	{
	    $this->page_processing(false);
	    $this->processing_end();
	    $_SESSION['Processing'] = $_SERVER['PHP_SELF'];
	}
	
	function processing_end()
	{
	        $this->page_processing(true);
	    unset($_SESSION['Processing']);
	}
}
?>
