<?php
//global $configArray;
/*
$configArray[] = array( 'ModuleName' => 'controller.woo_product_attributes.php',
                        'loadFile' => 'products/controller.woo_product_attributes.php',
                        'loadpriority' => 2,
                        'className' => 'controller_woo_product_attributes',
                        'objectName' => 'controller_woo_product_attributes',   //For multi classes within a module calling each other
                        'tablename' => 'woo_product_attributes',     //Check to see if the table exists?
			'tabdata' => array (
				'tabtitle' => 'Product Attribute',
				'action' => 'p_attr',
				'form' => 'prod_attributes_form',
				'hidden' => false,
				'class' => 'controller_woo_product_attributes'
				),
			'taborder' => 5,
                        );
$configArray[] = array( 'ModuleName' => 'controller.woo_product_attributes_terms.php',
                        'loadFile' => 'products/controller.woo_product_attributes_terms.php',
                        'loadpriority' => 2,
                        'className' => 'controller_woo_product_attributes_terms',
                        'objectName' => 'controller_woo_product_attributes_terms',   //For multi classes within a module calling each other
                        'tablename' => 'woo_product_attributes_terms',     //Check to see if the table exists?
			'tabdata' => array (
				'tabtitle' => 'Product Attribute Terms',
				'action' => 'p_attr_terms',
				'form' => 'prod_attributes_terms_form',
				'hidden' => false,
				'class' => 'controller_woo_product_attributes_terms'
				),
			'taborder' => 4,
                        );
$configArray[] = array( 'ModuleName' => 'controller.woo_prod_variable_master.php',
                        'loadFile' => 'products/controller.woo_prod_variable_master.php',
                        'loadpriority' => 2,
                        'className' => 'controller_woo_prod_variable_master',
                        'objectName' => 'controller_woo_prod_variable_master',   //For multi classes within a module calling each other
                        'tablename' => 'woo_prod_variable_master',     //Check to see if the table exists?
			'tabdata' => array (
				'tabtitle' => 'Variable Product Master',
				'action' => 'vp_master',
				'form' => 'woo_prod_variable_master_form',
				'hidden' => false,
				'class' => 'controller_woo_prod_variable_master'
				),
			'taborder' => 6,
		);
 */
$configArray[] = array( 'ModuleName' => 'controller_woo_product',
                        'loadFile' => 'products/controller.woo_product.php',
                        'loadpriority' => 2,
                        'className' => 'controller_woo_product',
                        'objectName' => 'controller_woo_product',   //For multi classes within a module calling each other
                        'tablename' => '',     //Check to see if the table exists?
			'tabdata' => array (
				'tabtitle' => 'Export Products (REST)',
				'action' => 'export-products-rest',
				'form' => 'export_rest_products_form',
				'hidden' => false,
				'class' => 'controller_woo_product',
				'additional_menus' => array( array( 'title' => 'Products Exported (REST)',
								'action' => 'exported-products-rest',
								'form' => 'exported_rest_products_form',
								'hidden' => true,
								'class' => 'controller_woo_product'
							) ),
				),
			'taborder' => 6,
                        );;

?>

