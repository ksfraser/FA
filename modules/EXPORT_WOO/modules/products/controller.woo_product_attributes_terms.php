<?php

class controller_woo_product_attributes_terms
{
        var $model;
        var $view;
        function __construct()
        {
                require_once( 'model.woo_product_attributes_terms.php' );
                require_once( 'view.woo_product_attributes_terms.php' );
                $this->model = new model_woo_product_attributes_terms( null, null, null, null, $this );
                $this->view = new view_woo_product_attributes_terms( null, null, null, null, $this );
        }
        function master_form()
        {
                $this->view->master_form();
        }
        function prod_attributes_terms_form()
        {
                $this->view->prod_attributes_terms_form();
        }

}


?>
