<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( '../ksf_modules_common/class.table_interface.php' ); 
require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 

class ksf_import extends generic_fa_interface {
	var $lastoid;
	var $debug;
	var $table_interface;
	function __construct($pref_tablename)
	{
		parent::__construct( null, null, null, null, $pref_tablename );
		/*
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		 */
		$this->tabs[] = array( 'title' => 'IMPORT Updated', 'action' => 'form_IMPORT_completed', 'form' => 'form_IMPORT_completed', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Update IMPORT', 'action' => 'form_IMPORT', 'form' => 'form_IMPORT', 'hidden' => FALSE );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
		$this->table_interface = new table_interface();
		$this->define_table();

		return;
	}
	function Import_Type_Form()
	{
				div_start('doc_tbl');
		start_table(TABLESTYLE);
		$th = array(_("Select File(s) and type"), '');
		table_header($th);
	
		label_row(_("Format:"), array_selector('parser', null, $parsers, array('select_submit' => true)));
		foreach($_parsers[$_POST['parser']]['select'] as $param => $label) {
		switch($param) {
			case 'bank_account':
			bank_accounts_list_row($label, 'bank_account', $selected_id=null, $submit_on_change=false);
			break;
	
		}
		}

	}
	function action_show_form()
	{
		$this->install();
		parent::action_show_form();
	}
	function install()
	{
		$this->table_interface->create_table();
		parent::install();
	}
	function define_table()
	{
		//$this->fields_array[] = array('name' => 'billing_address_id', 'type' => 'int(11)', 'auto_increment' => 'yup');
		$this->table_interface->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->table_interface->fields_array[] = array('name' => 'stock_id', 'type' => 'varchar(32)' );
		$this->table_interface->fields_array[] = array('name' => 'instock', 'type' => 'int(11)' );

		$this->table_interface->table_details['tablename'] = $this->company_prefix . "ksf_import";
		$this->table_interface->table_details['primarykey'] = "stock_id";
		/*
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "order_id,first_name,last_name,address_1,city,state";
		$this->table_details['index'][0]['keyname'] = "order-billing_address_customer";
		$this->table_details['index'][1]['type'] = 'unique';
		$this->table_details['index'][1]['columns'] = "customer_id,first_name,last_name,address_1,city,state";
		$this->table_details['index'][1]['keyname'] = "customer-billing_address_customer";
		 */
	}
	function form_IMPORT()
	{
				$this->call_table( 'form_IMPORT_completed', "IMPORT" );
	}
	function form_IMPORT_completed()
	{
		$oldcount = $this->table_interface->count_rows();	//inherited from table_interface
		$ksf_import2 = "insert ignore into " . $this->table_interface->table_details['tablename'] . " (stock_id, instock) SELECT 
				stock_id, 0 as instock
			FROM 
				" . TB_PREF . "stock_master
			WHERE
				inactive='0'";
		$res = db_query( $ksf_import2, "Couldn't populate table stock on hand" );

		$ksf_import2 = "replace into " . $this->table_interface->table_details['tablename'] . " (stock_id, instock) SELECT 
				stock_id, SUM(qty) as instock
			FROM 
				" . TB_PREF . "stock_moves
			GROUP BY stock_id";
		$res = db_query( $ksf_import2, "Couldn't populate table stock on hand" );

		$newcount = $this->table_interface->count_rows();	//inherited from table_interface     	
		display_notification( $newcount . " rows of items exist in " . $this->table_interface->table_details['tablename'] . ".  Added " . ($oldcount - $newcount) );
		//$activecount = $stock_master->count_filtered( "inactive='0'" );
		$res = db_query( "select count(*) from " . TB_PREF . "stock_master where inactive='0'", "Couldn't count IMPORT" );
		$count = db_fetch_row( $res );
            	display_notification("$count[0] rows of active items exist in stock_master.");
	}
}

?>
