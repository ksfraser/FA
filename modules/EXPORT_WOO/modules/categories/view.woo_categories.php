<?php

require_once( dirname( __FILE__ ) . '/../../../ksf_modules_common/class.VIEW.php' );

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

	function export_categories_form()
        {
        }
        function exported_categories_form()
        {
        }

}

?>
