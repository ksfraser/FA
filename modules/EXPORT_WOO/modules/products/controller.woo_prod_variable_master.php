<?php

class controller_woo_prod_variable_master extends origin
{
	var $model;
	var $view;
	function __construct()
	{
		require_once( 'model.woo_prod_variable_master.php' );
		require_once( 'view.woo_prod_variable_master.php' );
		$this->model = new model_woo_prod_variable_master( null, null, null, null, $this );
		$this->view = new view_woo_prod_variable_master( null, null, null, null, $this );
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
	function woo_prod_variable_master_form()
	{
		$this->view->woo_prod_variable_master_form();
	}
}

?>
