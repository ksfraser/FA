<?php

require_once( 'class.woo_product-base.php' );
require_once( 'class.woo_product-simple.php' );
require_once( 'class.woo_product-variable.php' );
require_once( 'view.woo_product.php' );
require_once( dirname( __FILE__ ) . '/../../../ksf_modules_common/class.controller_origin.php' );

/************************************************
 * PREREQ client has an eventloop defined 
 * or a global one is defined!
 *
 * EXPORT_WOO->generic_fa_interface->eventloop then
 * loads this module.
 * **********************************************/
class controller_woo_product extends controller_origin
{
        //var $model;
	var $simple;
	var $variable;
        function __construct( $client = null )
	{
		parent::__construct( null, $client );
           //     $this->model = new model_woo_product_attributes_terms( null, null, null, null, $this );
		$this->view = new view_woo_product( $this );
		$this->simple = new woo_product_simple( null, null, null, null, $this );
		$this->variable = new woo_product_variable( null, null, null, null, $this );
	}
	//Empty form for modules..eventloop...
	function woo_product_form()
	{
	}
	function export_rest_products_form()
	{
		$this->view->export_rest_products_form();
	}
	function exported_rest_products_form()
	{
		$this->tell_eventloop( $this, 'WOO_SEND_PRODUCTS', null );
		$this->view->exported_rest_products_form( $this->simple->products_sent, $this->simple->products_updated );
	}


}


?>
