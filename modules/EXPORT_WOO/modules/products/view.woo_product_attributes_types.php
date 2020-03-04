<?php

class view_woo_product_attributes_types
{
	var $model;
	function __construct()
	{
		require_once( 'model.woo_product_attributes_types.php' );
		$this->model = new model_woo_product_attributes_types( null, null, null, null, $this );
	}
	function master_form()
	{
		$this->model->master_form();
	}
}

?>
