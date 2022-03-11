<?php

//require_once( 'class.generic_orders.php' ); 
require_once( 'class.ksf_generate_catalogue.php' ); 


/*******************************************************//**
 * Generate an import CSV for SquareUp
 *
 * This class is only for data extraction and file creation.
 * There shouldn't be any UI.
 *
 * ********************************************************/
class labels_file extends ksf_generate_catalogue
{
	protected $hline = '"stock_id", "Title", "barcode", "category", "price"';
	protected $query;
	 	
	function __construct( $pref_tablename )
	{
		parent::__construct( null, null, null, null, $pref_tablename );
		set_time_limit(300);
		
		$this->filename = "labels.csv";
		$this->set_var( 'include_header', TRUE );
		$this->query = "select s.stock_id as stock_id, s.description as description, q.instock as instock, c.description as category, 0 as price from " . TB_PREF . "stock_master s, " . TB_PREF . "ksf_qoh q, " . TB_PREF . "stock_category c where s.inactive=0 and s.stock_id=q.stock_id and s.category_id = c.category_id order by c.description, s.description";
	}
	//INHERIT function prep_write_file()
	//CALLED by ksf_generate_catalogue::create_sku_labels()
	/*@int@*/function create_file()
	{

		$this->prep_write_file();
		$this->write_file->write_line( $this->hline );

		//require_once( '../ksf_qoh/class.ksf_qoh.php' ); 
		
		$result = db_query( $this->query, "Couldn't grab inventory to export labels" );

		$rowcount=0;
		while ($row = db_fetch($result)) 
		{
			$num = $row['instock'];
			//If we have 6 items instock, we need 6 labels to print so we can put on product
			for( $num; $num > 0; $num-- )
			{
				$this->write_sku_labels_line( $row['stock_id'], $row['category'], $row['description'], $row['price'] );
				$rowcount++;
			}
		}
		$this->write_file->close();
		if( $rowcount > 0 )
			$this->email_file();
		return $rowcount;	
	}
	/*@int@*/function create_sku_catalogs()
	{
			return 0;
	}
	/*************************************************************************************//**
	* Generate catalogs from a purchase order
	*
	* 
	*
	*****************************************************************************************/
	//CALLED by label_export_by_PO_Delivery()
	/*@int@*/function create_sku_labels_from_PO( $delivery_no )
	{
		//display_notification( __METHOD__ );
		$this->filename = "delivery_" . $delivery_no . "_labels.csv";
		
		$this->prep_write_file();
		$this->write_file->write_line( $this->hline );

		require_once( '../ksf_qoh/class.ksf_qoh.php' ); 
			
		$result = get_grn_items( $this->delivery_no, "", false, false, 0, "", "" );

		$rowcount=0;
		while ($row = db_fetch($result)) 
		{
			$num = $row['qty_recd'];
			//If we have 6 items instock, we need 6 labels to print so we can put on product
			for( $num; $num > 0; $num-- )
			{
				$this->write_sku_labels_line( $row['item_code'], "", $row['description'], 0 );
				$rowcount++;
			}
		}
		$this->write_file->close();
		if( $rowcount > 0 )
			$this->email_file();
			//$this->email_price_book();	//email_price_book doesn't exist
		return $rowcount;
	}
	function email_file( $subject = "Labels File" )
	{
		if( parent::email_file( $subject ) )
		{
			display_notification( "Download file <a href=" . $this->tmp_dir . "/" . $this->filename . ">" . $this->filename . "</a>" );
		}
		else
		{
			echo "<br /><br />Download file <a href=" . $this->tmp_dir . "/" . $this->filename . ">" . $this->filename . "</a>";
		}
	}
	function form_pricebook()
	{
		$this->create_price_book();
		$this->call_table( '', "OK" );
	}
	function write_file_form()
	{
		$this->call_table( 'gencat', "Create Catalogue File" );
	}
	function pocatalogsfile_form()
	{
	}
	function catalog_export()
	{
	}
	/******************************************************************************//**
	* Given a PO number create the catalogs for the items in that PO
	*
	*
	* @returns bool
	*********************************************************************************/
	/*@bool@*/function catalog_export_by_PO_Delivery()
	{
		return FALSE;
	}

}

?>

