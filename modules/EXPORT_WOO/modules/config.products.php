<?php
//global $configArray;
$configArray[] = array( 'ModuleName' => 'controller.woo_product_attributes.php',
                        'loadFile' => 'products/controller.woo_product_attributes.php',
                        'loadpriority' => 2,
                        'className' => 'controller_woo_product_attributes',
                        'objectName' => 'controller_woo_product_attributes',   //For multi classes within a module calling each other
                        'tablename' => 'woo_product_attributes',     //Check to see if the table exists?
			'tabdata' => array (
				'tabtitle' => 'Product Attributes',
				'action' => 'p_attr',
				'form' => 'prod_attributes_form',
				'hidden' => false,
				'class' => 'controller_woo_product_attributes'
				),
			'taborder' => 5,
                        );
$configArray[] = array( 'ModuleName' => 'controller.woo_product_attributes_types.php',
                        'loadFile' => 'products/controller.woo_product_attributes_types.php',
                        'loadpriority' => 2,
                        'className' => 'controller_woo_product_attributes_types',
                        'objectName' => 'controller_woo_product_attributes_types',   //For multi classes within a module calling each other
                        'tablename' => 'woo_product_attributes_types',     //Check to see if the table exists?
			'tabdata' => array (
				'tabtitle' => 'Product Attribute Types',
				'action' => 'p_attr_type',
				'form' => 'prod_attribute_types_form',
				'hidden' => false,
				'class' => 'controller_woo_product_attributes_types'
				),
			'taborder' => 4,
                        );

?>

