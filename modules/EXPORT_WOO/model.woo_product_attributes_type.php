<?php

$path_to_root = "../..";

require_once( 'class.woo_interface.php' );

/*************************************************************************//**
 *
 *	A variable product is a product that is identicle except for attributes
 *	such as size/color/etc
 *
 *	We are going to use a SKU that is basesku-attr1-attr2-attr3
 *
 *	We will designate attributes with a sort order from most significant to least
 *	on a sku by sku basis
 *
 *	We will provide a facility for this module to create the appropriate
 *	skus so that we just have to designate the base plus the relevant
 *	attributes.
 *
 *	Attributes will have a name plus a slug.  With the default FA sku size
 *	being 20, we need short slugs for each attribute.
 *
 * ***************************************************************************/


/******************************************
* A product attribute TYPE has the following characteristic:
*	attribute NAME			(e.g. Extra Extra Large)
*	attribute Description
*	attribute sort-order	What order does this value sort in...
*
*********************************************/

class model_woo_product_attributes_type extends woo_interface
{
	//var $id_woo_prod_variation_attributes;
	var $updated_ts;
	var $id;		//!< Integer. If a Global Attribute
	var $name;		//!< String. Non Global Attribute.  	xref prod_variables_values::variablename
	var $description;	//!< string
	var $sortorder;		//!< Integer

	function reset_endpoint()
	{
		$this->endpoint = "";
	}
	function define_table()
	{
		woo_interface::define_table();	//defines tablename and prikey!
						//declares updates_ts  and id_ prikey
		$sidl = 'varchar(' . STOCK_ID_LENGTH . ')';
		$this->fields_array[] = array('name' => 'id', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite' );
		$this->fields_array[] = array('name' => 'name', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite' );
		$this->fields_array[] = array('name' => 'description', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Description of this Attribute' );
		$this->fields_array[] = array('name' => 'sortorder', 'type' => int(11), 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Sort Order for this attribute' );
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "`name`";
		$this->table_details['index'][0]['keyname'] = "name";
	}
	/**********************************************************************************//**
	 *Return the list of attributes for a given name
	 *
	 * This function needs the name passed in ONLY if the object doesn't already
	 * have the name set.
	 *
	 * ************************************************************************************/
	function get_by_name( $name = null )
	{
		if( !isset( $this->name ) )
			$this->name = $name;
		$sql = "select * from " . TB_PREF . get_class( $this ) . " where name = '" . $this->name . "'";
		$result = db_query( $sql, __METHOD__ . " Couldn't run query" );
		$resarray = array();
		while( $nextrow = db_fetch( $result ) )
		{
			$resarray[$nextrow['name']][] = array( $nextrow['sku'] => $nextrow['option'] );
		}
		return $resarray;
	}
}

?>
