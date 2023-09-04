<?php

require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 

/* 
 *	This class is a "CART" class for storing lines of something.
 *
 */

/**********************************************************************************
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

*********************************************************************************/


/**************************************************************
 *  INITIAL DRAFT!!!
 *	UNTESTED!!!
 **************************************************************/


/**************************************************************
 *
 *
 **************************************************************/

class ksf_Cart 
{
	function __construct( );
	{
		$this->create_cart();
	}
        function install()
        {
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


	function handle_update_item()
	{
		$status = FALSE;
		//Validity Check
		//Update the line
			//$this->line_items[$_POST['LineNo']]['counted'] = $_POST['counted'];
		//return status
		return $status;
	}
	function handle_delete_item($line_no)
	{
		$status = FALSE;
	//	display_notification( __LINE__ . ' handle_delete removing ' . $line_no . " item " . $this->line_items[$line_no]['stock_id'] );
		if( isset( $this->line_items[$line_no] ) )
		{
			$this->line_items[$line_no] = array();
			$this->copy_to_session();
			$status = TRUE;
		}
		return $status;
	}
	function handle_add_item( $obj )
	{
		$line = $this->find_cart_item( $obj );
		if( $line >= 0 )
		{
			$this->line_items[$line] = $this->line_items[$line]->update();
		}
		else
		{
			$newcount = count($this->line_items);
			$this->line_items[$newcount] = $obj;
		}
		$this->copy_to_session();
	}
	/*************************************//**
	*	Use object's built in compare function to determine match
	*
	*
	******************************************/
	function find_cart_item( $obj )
	{
		$count = 0;
		$itemcount = count( $this->line_items );
		for( $count; $count < $itemcount; $count++ )
		{
			if( ! is_callable( $this->line_items[$count]->compare( $obj ) )
				throw new Exception( "Required Function not defined", KSF_FIELD_NOT_SET );
			if( $this->line_items[$count]->compare( $obj ) )
			{
				return $count;
			}
		}
		return -1;
	}
	function handle_new_item( $obj )
	{
		//Need to add item onto the cart
		$this->add_item( $obj );
	}
	function create_cart()
	{
		//Check to see if we have a cart in progress
		if( !isset( $_SESSION['ksfCart'] ) )
		{
			$this->tran_date = new_doc_date();
			$this->copy_to_session();
		}
		else
		{
	        	$this->copy_from_session();	//Copy line items into cart from SESSION
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

}
?>
