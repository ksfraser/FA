<?php

class controller_woo_product_attributes_types
{
        var $model;
        var $view;
        function __construct()
        {
                require_once( 'model.woo_product_attributes_types.php' );
                require_once( 'view.woo_product_attributes_types.php' );
                $this->model = new model_woo_product_attributes_types( null, null, null, null, $this );
                $this->view = new view_woo_product_attributes_types( null, null, null, null, $this );
        }
        function master_form()
        {
                $this->view->master_form();
        }
        function prod_attributes_types_form()
        {
                $this->view->prod_attributes_types_form();
        }

}


?>
