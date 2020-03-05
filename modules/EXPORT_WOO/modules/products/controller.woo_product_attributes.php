<?php

class controller_woo_product_attributes extends origin
{
	var $model;
	var $view;
	function __construct()
	{
		require_once( 'model.woo_product_attributes.php' );
		require_once( 'view.woo_product_attributes.php' );
		$this->model = new model_woo_product_attributes( null, null, null, null, $this );
		$this->view = new view_woo_product_attributes( null, null, null, null, $this );
	}
	function build_interestedin()
	{
			//calls $this->dummy( $calling_obj, $msg );
		$this->interestedin[KSF_DUMMY_EVENT]['function'] = "dummy";
		$this->interestedin['NOTIFY_INIT_TABLES']['function'] = "create_table";
		//p_attr_
	}
	function create_table()
	{
		$this->model->create_table();
	}
	function master_form()
	{
		$this->view->master_form();
	}
	function prod_attributes_form()
	{
		$this->view->prod_attributes_form();
	}
}

?>
