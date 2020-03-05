<?php

class view_woo_product_attributes
{
	var $model;
	function __construct()
	{
		require_once( 'model.woo_product_attributes.php' );
		$this->model = new model_woo_product_attributes( null, null, null, null, $this );
	}
	function master_form()
	{
		$this->model->master_form();
	}
        function prod_attributes_form()
        {
                $this->master_form();
        }

}

?>
