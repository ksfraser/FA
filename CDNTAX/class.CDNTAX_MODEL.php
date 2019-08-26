<?php
global $path_to_common;
require_once( $path_to_common . '/db_base.php' ); 


//class coast_orders
class CDNTAX_MODEL extends db_base
{
	var $include_header;
	var $maxrowsallowed;
	var $lastoid;
	var $header_row;
	var $col_type;
	var $db_column_name;
	var $db_pager_col_array;
	var $db_pager_tablename;
	var $db_pager_sql;
	var $show_inactive;
	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		//$this->set_var( 'vendor', "Coast" );
		
		//$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
	//	$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		//$this->header_row = array(_("Asset Type"),_("Asset Name"),_("Serial Number"), _("Purchase Date"),_("Purchase Value"), _("Current Value"), "", "", _("A"));
		//$this->col_type = array( "", "", "", "date", "amount", "amount", "edit", "delete", "inactive" );
		//$this->db_column_name = array( "", "", "", "", "", "", "", "", "" );

		//VIEW::$db_table_pager;            // = & new_db_pager( $this->table_name, $this->sql, $this->col_array );
/*
		$this->db_pager_col_array = array(
			_("Id") => array( 'fun'=>'id', 'ord'=>'' ),
			_("Person Id") => array( 'name'=>'person_id' ),		//crm_contacts
			_("Name") => array( 'name'=>'name' ),			//crm_persons
			_("Last Name") => array( 'name'=>'name2' ),			//crm_persons
			_("Person Type") => array( 'name'=>'type' ),		//crm_categories
			_("Action") => array( 'name'=>'action' ),		//crm_categories
			_("Entity Id") => array( 'name'=>'entity_id' ),		//entity_id
			_("E") => array( 'insert'=>true, 'fun'=>'edit_link' ),
			"V" => array( 'insert'=>true, 'fun'=>'view_link' ),
			"D" => array( 'insert'=>true, 'fun'=>'download_link' ),
			"X" => array( 'insert'=>true, 'fun'=>'delete_link' ),
			);
*/
		//$this->db_pager_sql = "SELECT c.id, c.person_id, p.name, p.name2, c.type, c.action, c.entity_id from " . TB_PREF . "crm_contacts c, " . TB_PREF . "crm_persons p where type='customer' and action='General' and c.person_id=p.id";
//		$this->db_pager_tablename = TB_PREF . "crm_persons";
	}
	function get_all_rows()
	{
		//NEED to return an MySQL results...
		
		return NULL;
	}
}
?>
