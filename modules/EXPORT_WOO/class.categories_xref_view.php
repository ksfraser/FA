<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

//This is a simple MODEL class.  AFAIK there aren't any bugs nor TODOs.

require_once( 'class.table_interface.php' );

/*************************************************//*****
 * Model class of the categories_xref table
 *
 * Current design is this class updates the table.
 * 
 ******************************************************/
class categories_xref_model extends table_interface {
	var $fa_cat;
	var $woo_cat;
	var $description;
	var $updated_ts;
	function __construct( $caller = null)
	{
		parent::__construct( $caller );
		$this->define_table();
	}
	function define_table()
	{
		//$this->fields_array[] = array('name' => 'categories_xref_id', 'type' => 'int(11)', 'auto_increment' => 'yup');
		$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->fields_array[] = array('name' => 'fa_cat', 'type' => 'int(11)');
		$this->fields_array[] = array('name' => 'woo_cat', 'type' => 'int(11)');
		$this->fields_array[] = array('name' => 'description', 	'type' => 'varchar(64)', 	'comment' => ' 	First name.' );
		$this->table_details['tablename'] = $this->company_prefix . "woo_categories_xref";
		$this->table_details['primarykey'] = "fa_cat";
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "fa_cat,woo_cat";
		$this->table_details['index'][0]['keyname'] = "fa-woo";
	}
	/************************************************************************************************************//**
	 * Update a cross-ref of FA category x WooCommerce Category
	 *
	 * ************************************************************************************************************/
	function update()
	{
		$this->update_table();
	}
	/************************************************************************************************************//**
	 * Insert a cross-ref of FA category x WooCommerce Category
	 *
	 * ************************************************************************************************************/
	function insert()
	{
		$this->insert_table();
	}
	/************************************************************************************************************//**
	 * Get the WooCommerce category_id from the FrontAccounting one
	 *
	 * @returns int WooCommerce Category ID
	 * ************************************************************************************************************/
	/*@int@*/function get_woo_cat()
	{
		$this->clear_sql_vars();
 		$this->select_array = array( '*' );
                $this->where_array = array();
		if( isset( $this->fa_cat ) )
                	$this->where_array['fa_cat'] =  $this->fa_cat;
                $this->groupby_array = array();
		$this->buildSelectQuery();
                $res = $this->query( "Couldn't select woo category", "select");
		$assoc = db_fetch_assoc( $res );
		return (int)$assoc['woo_cat'];
	}
	/************************************************************************************************************//**
	 * Get the FrontAccounting category_id from the WooCommerce one
	 *
	 * @returns int FA Category ID
	 * ************************************************************************************************************/
	/*@int@*/function get_fa_cat()
	{
		$this->clear_sql_vars();
 		$this->select_array = array( '*' );
                $this->where_array = array();
		if( isset( $this->woo_cat ) )
                	$this->where_array['woo_cat'] =  $this->woo_cat;
                $this->groupby_array = array();
		$this->buildSelectQuery();
                $res = $this->query( "Couldn't select fa category", "select");
		$assoc = db_fetch_assoc( $res );
		return (int)$assoc['fa_cat'];
	}
}

?>
