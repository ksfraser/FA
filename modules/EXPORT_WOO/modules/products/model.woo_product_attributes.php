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

/*****************************************
 * In WC, an attribute is a type of attribute 
 * i.e. COLOR/SIZE
 * their example:
 * slug pa_color
 * name COLOR
 *
 * ***************************************/



/******************************************
* A product attribute has the following characteristic:
*	attribute abbreviation (aka slug)(e.g. XXL)
*	attribute NAME			(e.g. Extra Extra Large)
*	attribute Description
*	attribute TYPE  (i.e. not just SIZE but SHIRT-SIZE 
*		for when the range isn't the same for every product)
*	attribute sort-order	What order does this value sort in...
* https://woocommerce.github.io/woocommerce-rest-api-docs/#create-a-product-attribute
*
*********************************************/

class model_woo_product_attributes extends MODEL
{
	var $id_woo_product_attributes;	//parent defines in define_table
	var $updated_ts;		//parent defines in define_table
	var $wc_id;		//!< Integer WooCommerce's internal (WP) id
	//var $id;		//!< Integer. If a Global Attribute
	var $name;		//!< String. Non Global Attribute.  	xref prod_variables_values::variablename
	var $slug;		//!< String.  Abbreviation.  WC - alpahnumeric, unique to type
	var $description;	//!< string  FA Only
//	var $fk_type;		//!< id pointing to _attributes_type
	var $wc_type;		//!< string WC only supports "select"
	var $sortorder;		//!< Integer
	var $wc_order_by;	//!< String.  Enum( menu_order, name, name_num, id )  default menu_order
	var $wc_has_archives;	//!< bool.  Default false.

	/***********************************************
	 * Woo Interface requires that we override reset_endpoint
	 * However, the controller is really the object that should
	 * be setting/resetting the endpoint, not the MODEL!!
	 * *********************************************/
	function reset_endpoint()
	{
		$this->endpoint = "products/attributes";
	}
	//CREATE an attribute: 
	//$data = [ 'name' => 'Color', 'slug' => 'pa_color', 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => true];
	//print_r($woocommerce->post('products/attributes', $data));
	// 
	//UPDATE an attribute:
	//$data = ['order_by' => 'name'];
	//print_r($woocommerce->put('products/attributes/1', $data));
	//
	//LIST attributes:
	// print_r($woocommerce->get('products/attributes')); 
	//	RETURNS:
	//		[  {	"id": 1,    "name": "Color",    "slug": "pa_color",    "type": "select",    
	//		       	"order_by": "menu_order",    "has_archives": true,    
	//		       	"_links": { "self": [ {  "href": "https://example.com/wp-json/wc/v3/products/attributes/6" } ],      
	//		       		    "collection": [ { "href": "https://example.com/wp-json/wc/v3/products/attributes" } ]
	//		        }  },  
	//		   {    "id": 2, ...
	//	
	function define_table()
	{
		woo_interface::define_table();	//defines tablename and prikey!
						//declares updates_ts  and id_ prikey
		$sidl = 'varchar(' . STOCK_ID_LENGTH . ')';
		//$this->fields_array[] = array('name' => 'id', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite' );
		$this->fields_array[] = array('name' => 'slug', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite' );
		$this->fields_array[] = array('name' => 'name', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite' );
		$this->fields_array[] = array('name' => 'description', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Description of this Attribute' );
		//$this->fields_array[] = array('name' => 'id_woo_product_attributes_types', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'FK to attributes_type table', 'foreign_obj' => 'woo_product_attributes_types' );
		$this->fields_array[] = array('name' => 'sortorder', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Sort Order for this attribute' );
		$this->fields_array[] = array('name' => 'wc_id', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'external_name' => 'id' );
		$this->fields_array[] = array('name' => 'wc_type', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => 'select', 'external_name' => 'type' );
		$this->fields_array[] = array('name' => 'wc_order_by', 'type' => $sidl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => 'menu_order', 'external_name' => 'order_by' );
		$this->fields_array[] = array('name' => 'wc_has_archives', 'type' => 'bool', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => 'FALSE', 'external_name' => 'has_archives' );
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "`slug`";
		$this->table_details['index'][0]['keyname'] = "slug";
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
