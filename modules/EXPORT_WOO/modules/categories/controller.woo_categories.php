<?php

class controller_woo_categories extends controller_origin
{
	var $model;
	var $view;
	function __construct()
	{
		require_once( 'model.woo_categories.php' );
		require_once( 'view.woo_categories.php' );
		$this->model = new model_woo_categories( null, null, null, null, $this );
		$this->view = new view_woo_categories( null, null, null, null, $this );
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

}

?>
