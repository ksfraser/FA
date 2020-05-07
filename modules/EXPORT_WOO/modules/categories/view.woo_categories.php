<?php

class view_woo_categories extends VIEW
{
	var $model;
	function __construct( $client )
	{
		parent::__construct( $client );
	}
	function master_form()
	{
		$this->model->master_form();
	}


}

?>
