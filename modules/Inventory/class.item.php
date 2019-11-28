<?php

/** 
 *	This module is for doing a stock taking (aka inventory).
 */

/**************************************************************
 *
 *	This module allows an employee to do a partial stock taking
 *
 **************************************************************/

/***********************************************************//**
 *
 * TODO:
 * 	Test that cart2session puts the cart_items into it.
 * *************************************************************/
class inventory_cart
{
	var $reference;
	var $comments;
	var $document_date;
	var $from_location;
	var $to_location;
	var $cart_items;
	function __construct( $date = null )
	{
		$this->cart_items = array();
		if( null == $date )
		{
			$this->document_date = date( 'Y-m-d' );
		}
		else
			$this->document_date = $date;

	}
	function new_item( $stock_id )
	{
		$this->cart_items[] = new item($stock_id, $this->from_location);
	}
    	public function serialize()
    	{
        	return serialize(get_object_vars($this));
    	}
    	public function unserialize($data)
    	{
    		$values = unserialize($data);
    	    	foreach ($values as $key=>$value) {
    	        	$this->$key = $value;
    	    	}
    	}
	
	function cart2session()
	{
		//Could I have instead used Serialize/Unserialize?

		$_SESSION['reference'] = $this->reference;
	        $_SESSION['Comments'] = $this->comments;
	        $_SESSION['document_date'] = $this->document_date;
	        $_SESSION['location'] = $this->from_location;
	        $_SESSION['to_location'] = $this->to_location;
		$_SESSION['from_location'] = $this->from_location;
		//Don't know if this will work!
		$_SESSION['InventoryItems'] = json_encode( $this->cart_items );
	}
	function session2cart()
	{
		//Could I have instead used Serialize/Unserialize?
		if( isset( $_SESSION['reference'] ) )
		{
	        	$this->reference = $_SESSION['reference'];
		}
		if( isset( $_SESSION['Comments'] ) )
		{
	        	$this->Comments =  $_SESSION['Comments'];
		}
		if( isset( $_SESSION['document_date'] ) )
		{
	        	$this->document_date = $_SESSION['document_date'];
		}
		if( isset( $_SESSION['location'] ) )
		{
	        	$this->location = $_SESSION['location'];
		}
		if( isset( $_SESSION['from_location'] ) )
		{
	        	$this->from_location = $_SESSION['from_location'];
		}
		if( isset( $_SESSION['to_location'] ) )
		{
	        	$this->to_location = $_SESSION['to_location'];
		}
	}
	/*@bool@*/function can_process()
	{
		global $Refs, $SysPrefs;
		$input_error = 0;
	
	       	if (!$Refs->is_valid($this->reference) )
	        {
			$this->reference = rand();
			return $this->can_process();
	        }
	        elseif (!is_new_reference($this->reference, ST_LOCTRANSFER))
	        {
			$this->reference = rand();
			return $this->can_process();
	        }
	        elseif (!is_date($this->document_date))
		{
			throw new Exception( "The entered transfer date is invalid.", INVALIDDATE );
	        }
	        elseif (!is_date_in_fiscalyear($this->document_date))
	        {
	                throw new Exception( "The entered date is not in fiscal year.", INVALIDYEAR);
	        }
	        elseif ($this->holdtank == $this->from_location)
	        {
	                throw new Exception( "The locations to transfer from must not be the holding tank.", INVALIDFROMLOC);
	        }
	        elseif ($this->holdtank == $this->to_location)
	        {
	                throw new Exception( "The locations to transfer TO must not be the holding tank.", INVALIDTOLOC);
	        }
	        elseif ($this->from_location == $this->to_location)
	        {
	                throw new Exception( "The locations to transfer from and to must be different.", INVALIDTOLOC );
	        }
		return TRUE;
	}
	/*@bool@*/function handle_update_item()
	{
	        if( $_POST['UpdateItem'] != '' ) 
		{
			$this->cart_items[$_POST['LineNo']]->update_counted( $_POST['counted'] );
			return TRUE;
	        }
	}
	function handle_delete_item($line_no)
	{
		$this->line_items[$line_no] = null;
		$this->copy_to_session();
	}
	/***************************************************************//**
	 * Increment the count or add new item
	 *
	 * @param string stock_id
	 * @param int count
	 * @returns Bool success/fail
	 * ***************************************************************/
	/*@bool@*/function increment_item_count( $stock_id, $count )
	{
		if( ! $this->isStockid( $stock_id ) )
		{
			//foreign code not stock_id
			$stock_id = $this->get_stock_id( $stock_id );
			if( null == $stock_id )
				return FALSE;
		}
		$line = $this->find_cart_item();
		if( $line < 0 ) //found
		{
			$this->new_item( $stock_id );
			//$line = $this->find_cart_item();
			$line = count( $this->cart_items );
			$this->cart_items[$line]->get_item_description();
		}
		$this->cart_items[$line]->increase_count( $count );
		return TRUE;
	}
	function get_stock_id( $item_code )
	{
		$sql = "SELECT stock_id FROM "
        	        . TB_PREF ."item_codes "
        	        . " WHERE item_code=".db_escape($item_code);
        	$res = db_query($sql, "where used query failed");
		$fet = db_fetch_assoc( $res );
		if( count( $fet ) > 0  )
		{
 			return $fet[0]['stock_id'];
		}
		return null;
	}
	/********************************************************************************//**
	 * Is the item in the ?stock_master? databas
	 *
	 * @param string the stock_id to check
	 * @return bool Is the item in the ?stock_master? database
	 * *********************************************************************************/
	/*@bool@*/function isStockid( $stock_id )
	{
		include_once( $this->path_to_root . "/includes/db/inventory_db.inc" );
		return is_inventory_item( $stock_id );
	}
	/********************************************************************************//**
	 * check for a foreign/part code.  Sets stock_id if found.
	 *
	 * @param string stock_id/item_code
	 * @returns bool whether item is in item_codes AND stock_id<>item_code
	 * *********************************************************************************/
	/*@bool@*/ function isItemCode( $item_code )
	{
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
			return TRUE;
		}
		return FALSE;
	}
}
/*************************************************************************//**
 *
 *	Class Inventory is the routines for doing a stock taking.
 *
 *	Class Inventory is the routines for doing a stock taking.
 *	It also allows you to do inventory transfers of ALL inventory
 *	without having to count those items at both locations.  
 *	ASSUMPTION is that you will do an appropriate inventory count later.
 *
 * ***************************************************************************/
class item  
{
	var $location;		//!< Inventory location
	var $barcode;
	
	var $title;
	var $stock_id;
	var $description;
	var $debug;
	var $qoh;		//!< How many FA thinks we have at this location
	var $counted;		//!< How many we have counted at this location
	/********************************************************************************//**
	 * Constructor
	 *
	 * @param string stock_id
	 * @param string location identifier
	 * @param string date
	 * @return null
	 * *********************************************************************************/
	/*@void@*/function __construct( $stock_id, $location = -1  )
	{
		if( ! $this->isStockid( $stock_id ) )
		{
			$stock_id = $this->get_stock_id( $stock_id );
			if( null == $stock_id )
				return FALSE;
		}
		$this->stock_id = $stock_id;
		if( $location == -1 )
		{
			//need to look up the default location
		}
		else 
		{
			//Need to validate that the location value is valid
			$this->location = $location;
		}
		return;
	}
	function update_counted( $count )
	{
		$this->counted = $count;
	}
	function increase_count( $count )
	{
		$this->counted += $count;
	}
	function is_me( $stock_id )
	{
		if( $stock_id == $this->stock_id )
			return TRUE;
		else if( $this->get_stock_id( $stock_id ) == $this->stock_id )
			return TRUE;	//a Foreign code for me!
		else
			return FALSE;
	}
	function get_stock_id( $item_code )
	{
		$sql = "SELECT stock_id FROM "
        	        . TB_PREF ."item_codes "
        	        . " WHERE item_code=".db_escape($item_code);
        	$res = db_query($sql, "where used query failed");
		$fet = db_fetch_assoc( $res );
		if( count( $fet ) > 0  )
		{
 			return $fet[0]['stock_id'];
		}
		return null;
	}
	/********************************************************************************//**
	 * Is the item in the ?stock_master? databas
	 *
	 * @param string the stock_id to check
	 * @return bool Is the item in the ?stock_master? database
	 * *********************************************************************************/
	/*@bool@*/function isStockid( $stock_id )
	{
		include_once( $this->path_to_root . "/includes/db/inventory_db.inc" );
		return is_inventory_item( $stock_id );
	}
	/********************************************************************************//**
	 * check for a foreign/part code.  Sets stock_id if found.
	 *
	 * @param string stock_id/item_code
	 * @returns bool whether item is in item_codes AND stock_id<>item_code
	 * *********************************************************************************/
	/*@bool@*/ function isItemCode( $item_code )
	{
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
			if( $this->debug > 1 )
			{
				echo __FILE__ . ":" . __LINE__ . " DEBUG: isItemCode count > 0<br />";
				$this->stock_id = $ret['stock_id'];
			}
			//should we set the data into a set of variables?
			return TRUE;
		}
		return FALSE;
	}
	/********************************************************************************//**
	 * retrieve item's description.  Depends on stock_id being set
	 *
	 * @returns bool whether we retrieved or not.
	 * *********************************************************************************/
	/*@bool@*/function get_item_description()
	{
		if( !isset( $this->stock_id ) )
			return FALSE;
		//Using stock_id get the description

 		$item_row = get_item($this->stock_id);	//includes/db/inventory_db.inc
                if ($item_row == null)
		{
			display_error("invalid item : $stock_id", "");
		}
		else if( strlen($item_row["description"]) > 1)
		{
			$this->description = $item_row["description"];
			return TRUE;
		}
		return FALSE;
	}
	/********************************************************************************//**
	 * retrieve item's QOH on a date.  Depends on stock_id, location, document_date being set
	 *
	 * @returns bool whether we retrieved or not.
	 * *********************************************************************************/
	/*@bool@*/function get_item_qoh_location( $stock_id )
	{
		if( !isset( $this->stock_id ) )
			return FALSE;
		if( !isset( $this->location ) )
			return FALSE;
		if( !isset( $this->document_date ) )
			return FALSE;
                // collect quantities by stock_id
		$this->qoh = get_qoh_on_date( $this->stock_id, $this->location, $this->document_date);	//includes/db/inventory_db.inc
		return TRUE;
	}
}
?>
