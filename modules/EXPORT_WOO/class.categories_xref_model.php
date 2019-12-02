<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

//This is a simple MODEL class.  AFAIK there aren't any bugs nor TODOs.

require_once( 'class.woo_interface.php' );

/*************************************************//*****
 * Model class of the categories_xref table
 *
 * Current design is this class updates the table.
 * 
 ******************************************************/
class categories_xref_model extends woo_interface {
	var $fa_cat;
	var $woo_cat;
	var $description;
	var $updated_ts;
	function __construct( $caller = null)
	{
		parent::__construct( null, null, null, null, $caller );
	}
	function define_table()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
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
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function reset_endpoint() {}

	/************************************************************************************************************//**
	 * Determine if a record exists.  If it does, update else insert
	 *
	 * ************************************************************************************************************/
	function insert_or_update( $caller )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		try {
			$woo_cat = $this->get_fa_cat( $this );
			if( $woo_cat > 0 )
				$this->update( $this );
			else
				$this->insert( $this );
		}
                catch( Exception $e )
                {
                        $this->notify( __METHOD__ . ":" . __LINE__ . ":" . __METHOD__ . " Exception " . $e->getCode() . "::" . $e->getMessage(), "ERROR" );
                        throw $e;
                }
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/************************************************************************************************************//**
	 * Update a cross-ref of FA category x WooCommerce Category
	 *
	 * ************************************************************************************************************/
	function update( $caller )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		try {
			$this->update_table();
		}
                catch( Exception $e )
                {
                        $this->notify( __METHOD__ . ":" . __LINE__ . ":" . __METHOD__ . " Exception " . $e->getCode() . "::" . $e->getMessage(), "ERROR" );
                        throw $e;
                }
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/************************************************************************************************************//**
	 * Insert a cross-ref of FA category x WooCommerce Category
	 *
	 * ************************************************************************************************************/
	function insert( $caller )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		try {
			$this->insert_table();
		}
                catch( Exception $e )
                {
                        $this->notify( __METHOD__ . ":" . __LINE__ . ":" . __METHOD__ . " Exception " . $e->getCode() . "::" . $e->getMessage(), "ERROR" );
                        throw $e;
                }
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/************************************************************************************************************//**
	 * Get the WooCommerce category_id from the FrontAccounting one
	 *
	 * @returns int WooCommerce Category ID
	 * ************************************************************************************************************/
	/*@int@*/function get_woo_cat( $caller )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		try {
			$this->clear_sql_vars();
			if( isset( $this->fa_cat ) )
	                	$this->where_array['fa_cat'] =  $this->fa_cat;
			else
				throw new Exception( "Required field not set: fa_cat", KSF_FIELD_NOT_SET );
	 		$this->select_array = array( '*' );
			$this->from_array[] = $this->table_details['tablename'];
	                $this->where_array = array();
			$this->buildSelectQuery();
	                $res = $this->query( "Couldn't select woo category", "select");
			$assoc = db_fetch_assoc( $res );
		}
                catch( Exception $e )
                {
                        $this->notify( __METHOD__ . ":" . __LINE__ .  " Exception " . $e->getCode() . "::" . $e->getMessage(), "ERROR" );
                        throw $e;
                }
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return (int)$assoc['woo_cat'];
	}
	/************************************************************************************************************//**
	 * Get the FrontAccounting category_id from the WooCommerce one
	 *
	 * @returns int FA Category ID
	 * ************************************************************************************************************/
	/*@int@*/function get_fa_cat( $caller )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		try {
			$this->clear_sql_vars();
			if( isset( $this->woo_cat ) )
	                	$this->where_array['woo_cat'] =  $this->woo_cat;
			else
				throw new Exception( "Required field not set: woo_cat", KSF_FIELD_NOT_SET );
	 		$this->select_array = array( '*' );
			$this->from_array[] = $this->table_details['tablename'];
	                $this->where_array = array();
			$this->buildSelectQuery();
	                $res = $this->query( "Couldn't select fa category", "select");
			$assoc = db_fetch_assoc( $res );
		}
                catch( Exception $e )
                {
                        $this->notify( __METHOD__ . ":" . __LINE__ . " Exception " . $e->getCode() . "::" . $e->getMessage(), "ERROR" );
                        throw $e;
                }

		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return (int)$assoc['fa_cat'];
	}
}

?>
