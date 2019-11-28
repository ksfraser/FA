<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( '../ksf_modules_common/class.table_interface.php' ); 
require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 

class ksf_customer_portal_admin extends generic_fa_interface {
	var $lastoid;
	var $debug;
	var $table_interface;
	function __construct($pref_tablename)
	{
		return;
		parent::__construct( null, null, null, null, $pref_tablename );
		/*
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		 */
		$this->tabs[] = array( 'title' => 'QOH Updated', 'action' => 'form_QOH_completed', 'form' => 'form_QOH_completed', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Update QOH', 'action' => 'form_QOH', 'form' => 'form_QOH', 'hidden' => FALSE );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
		$this->table_interface = new table_interface();
		$this->define_table();

		return;
	}
	function action_show_form()
	{
		return;
		$this->install();
		parent::action_show_form();
	}
	function install()
	{
		return;
		$this->table_interface->create_table();
		parent::install();
	}
	function define_table()
	{
		return;
		//$this->fields_array[] = array('name' => 'billing_address_id', 'type' => 'int(11)', 'auto_increment' => 'yup');
		$this->table_interface->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->table_interface->fields_array[] = array('name' => 'stock_id', 'type' => 'varchar(32)' );
		$this->table_interface->fields_array[] = array('name' => 'instock', 'type' => 'int(11)' );

		$this->table_interface->table_details['tablename'] = $this->company_prefix . "ksf_customer_portal_admin";
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
	function form_QOH()
	{
		return;
				$this->call_table( 'form_QOH_completed', "QOH" );
	}
	function form_QOH_completed()
	{
		return;
		$oldcount = $this->table_interface->count_rows();	//inherited from table_interface
		$ksf_customer_portal_admin2 = "insert ignore into " . $this->table_interface->table_details['tablename'] . " (stock_id, instock) SELECT 
				stock_id, 0 as instock
			FROM 
				" . TB_PREF . "stock_master
			WHERE
				inactive='0'";
		$res = db_query( $ksf_customer_portal_admin2, "Couldn't populate table stock on hand" );

		$ksf_customer_portal_admin2 = "replace into " . $this->table_interface->table_details['tablename'] . " (stock_id, instock) SELECT 
				stock_id, SUM(qty) as instock
			FROM 
				" . TB_PREF . "stock_moves
			GROUP BY stock_id";
		$res = db_query( $ksf_customer_portal_admin2, "Couldn't populate table stock on hand" );

		$newcount = $this->table_interface->count_rows();	//inherited from table_interface     	
		display_notification( $newcount . " rows of items exist in " . $this->table_interface->table_details['tablename'] . ".  Added " . ($oldcount - $newcount) );
		//$activecount = $stock_master->count_filtered( "inactive='0'" );
		$res = db_query( "select count(*) from " . TB_PREF . "stock_master where inactive='0'", "Couldn't count QOH" );
		$count = db_fetch_row( $res );
            	display_notification("$count[0] rows of active items exist in stock_master.");
	}
}

?>
