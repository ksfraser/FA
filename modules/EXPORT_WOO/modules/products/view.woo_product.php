<?php
require_once( dirname( __FILE__ ) . "/../../../ksf_modules_common/class.VIEW.php" );
class view_woo_product extends VIEW
{
	var $controller;
	function __construct( $controller )
	{
		parent::__construct( $controller );
		$this->controller = $controller;
	}
	/**********************************
	 * Add submenus onto the tab
	 * ********************************/
	function add_submenu()
	{
	}
	function master_form()
	{
	}
	function exported_rest_products_form( $sentcount, $updatecount )
	{

            	$this->display_notification( $sentcount . " Products sent and " . $updatecount . " updated.");
		$this->call_table( 'export_rest_product', "Export Another" );
		$this->call_table( 'export_rest_products', "Export ALL Another" );
	}
	/***********************************************************************
	*
	*	Function export_rest_products_form()	
	*	
	*	To present the user with a button to launch the sending of 
	*	ALL Products to WOO.
	*
	************************************************************************/
	function export_rest_products_form()
	{
		$this->call_table( 'exported-products-rest', "Send Products via REST to WOO" );
	}
	/***********************************************************************
	*
	*	Function export_rest_product_form
	*
	*	To show products and send 1 to WOO
	*
	*	Allow user to update the items based upon missing data...
	*
	************************************************************************/
	function export_rest_product_form()
	{
		$missing_sql = "select sm.stock_id, sm.description, c.description, sm.inactive, sm.editable 
				from " . TB_PREF . "stock_master sm, " . TB_PREF . "stock_category c
				where sm.category_id = c.category_id and sm.stock_id in (select stock_id from " . TB_PREF . "woo)";
		 global $all_items;
	  	//stock_items_list($name, $selected_id, $all_option, $submit_on_change,
	        //        array('cells'=>true, 'show_inactive'=>$all), $editkey);
		//function stock_items_list($name, $selected_id=null, $all_option=false,
	        				//$submit_on_change=false, $opts=array(), $editkey = false)
		$selected_id = "0";
		$name = "";
		$editkey = TRUE;
		$opts = array('cells'=>true, 'show_inactive'=>'1');
		$all_option = FALSE;
		$submit_on_change = TRUE;
	        //if ($editkey)
	                set_editor('item', $name, $editkey);
		start_form();

		start_table();
		table_section_title(_("Export a Product to WOO via REST"));
	//	label_row(_("No Transaction History (no inventory movement):"), NULL);
		label_row("&nbsp;", NULL);
		label_row("Press F4 to pop open a window to edit the item details.  The details WON'T make it to WOO until the 'All Products Export' routine is rerun", null);

		table_section(1);
	        $ret = combo_input($name, $selected_id, $missing_sql, 'stock_id', 'sm.description',
	        array_merge(
	          array(
	                'format' => '_format_stock_items',
	                'spec_option' => $all_option===true ?  _("All Items") : $all_option,
	                'spec_id' => $all_items,
			'search_box' => true,
	        	'search' => array("sm.stock_id", "c.description","sm.description"),
	                'search_submit' => get_company_pref('no_item_list')!=0,
	                'size'=>10,
	                'select_submit'=> $submit_on_change,
	                'category' => 2,
	                'order' => array('c.description','sm.stock_id')
	          ), $opts) );
			echo $ret;
	  	//echo stock_items_list($name, $selected_id, $all_option, $submit_on_change,
	        //        array('cells'=>true, 'show_inactive'=>$all), $editkey);
		end_table(); 
		submit_center( "exported_rest_products", "Export" );
		$this->call_table( 'exported_rest_products', "Send Product via REST to WOO" );
		end_form();
		//$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}

}

?>
