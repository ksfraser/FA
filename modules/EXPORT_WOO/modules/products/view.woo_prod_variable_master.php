<?php

class view_woo_prod_variable_master
{
	var $model;
	function __construct()
	{
		require_once( 'model.woo_prod_variable_master.php' );
		$this->model = new model_woo_prod_variable_master( null, null, null, null, $this );
	}
	function master_form()
	{
		$this->model->master_form();
	}
        function woo_prod_variable_master_form()
        {
                $this->master_form();
        }

}

?>
