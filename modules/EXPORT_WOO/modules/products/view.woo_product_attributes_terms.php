<?php

class view_woo_product_attributes_terms
{
	var $model;
	function __construct()
	{
		require_once( 'model.woo_product_attributes_terms.php' );
		$this->model = new model_woo_product_attributes_terms( null, null, null, null, $this );
	}
	function master_form()
	{
		$this->model->master_form();
	}
        function prod_attributes_terms_form()
        {
                $this->master_form();
        }

}

?>
