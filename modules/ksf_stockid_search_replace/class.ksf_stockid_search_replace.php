<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( '../ksf_modules_common/class.table_interface.php' ); 
require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 

/************************************************************************//**
 * Take a stock_id and replace it with a new one
 * ***********************************************************************/
class ksf_stockid_search_replace extends generic_fa_interface {
	var $lastoid;
	var $debug;
	var $table_interface;
	var $old_stock_id;
	var $new_stock_id;
	function __construct($pref_tablename)
	{
		parent::__construct( null, null, null, null, $pref_tablename );
		/*
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		 */
		$this->tabs[] = array( 'title' => 'STOCKID_SEARCH_REPLACE Updated', 'action' => 'form_STOCKID_SEARCH_REPLACE_completed', 'form' => 'form_STOCKID_SEARCH_REPLACE_completed', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Update STOCKID_SEARCH_REPLACE', 'action' => 'form_STOCKID_SEARCH_REPLACE', 'form' => 'form_STOCKID_SEARCH_REPLACE', 'hidden' => FALSE );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();

		$this->table_interface = null;	//No table on this one.
		//$this->table_interface = new table_interface();
		//$this->define_table();

		return;
	}
	function action_show_form()
	{
		$this->install();
		parent::action_show_form();
	}
	function install()
	{
		//Nothing to install.
		//
		//$this->table_interface->create_table();
		//parent::install();
	}
	function define_table()
	{
		//$this->fields_array[] = array('name' => 'billing_address_id', 'type' => 'int(11)', 'auto_increment' => 'yup');
		/*
		$this->table_interface->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->table_interface->fields_array[] = array('name' => 'stock_id', 'type' => 'varchar(32)' );
		$this->table_interface->fields_array[] = array('name' => 'instock', 'type' => 'int(11)' );

		$this->table_interface->table_details['tablename'] = $this->company_prefix . "ksf_stockid_search_replace";
		$this->table_interface->table_details['primarykey'] = "stock_id";
		 */
		/*
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "order_id,first_name,last_name,address_1,city,state";
		$this->table_details['index'][0]['keyname'] = "order-billing_address_customer";
		$this->table_details['index'][1]['type'] = 'unique';
		$this->table_details['index'][1]['columns'] = "customer_id,first_name,last_name,address_1,city,state";
		$this->table_details['index'][1]['keyname'] = "customer-billing_address_customer";
		 */
	}
	function form_STOCKID_SEARCH_REPLACE()
	{
		$form_array = array();
		$header = array( '', 'value' );
		$form_array['header'] = $header;
		$row_array = array();
		$row_array[] = array( 'row_name' => 'old_stock_id', 'label' => 'Stock_id to be replaced', 'type' => 'text' );
		$row_array[] = array( 'row_name' => 'new_stock_id', 'label' => 'Stock_id to replace with', 'type' => 'text' );
		$form_array['rows'] = $row_array;
		$button = array( 'name' => 'submit', 'label' => 'Replace Stock_id' );
		$form_array['button'] = $button;
		$this->show_generic_form( $form_array );
				//$this->call_table( 'form_STOCKID_SEARCH_REPLACE_completed', "STOCKID_SEARCH_REPLACE" );
	}
	function form_STOCKID_SEARCH_REPLACE_completed()
	{
		require_once( "../ksf_modules_common/defines.inc" );
		begin_transaction(); //includes/db/sql_functions.inc
		foreach( $stock_id_tables as $row )
		{
			//For each table we've identified as having the stock_id field, go through and do a replace.
			//
			$ksf_stockid_search_replace = "update " . TB_PREF . $row['table'] . " set " . $row['column'] . "='" . $this->new_stock_id . 
				"' where " . $row['column'] . "='" . $this->old_stock_id . "'";
			display_notification( __MODULE__ . "::" . __LINE__ . " " . $ksf_stockid_search_replace );
			$res = db_query( $ksf_stockid_search_replace, "Couldn't replace stock_id" );
		}
		commit_transaction();
	}
}

?>
