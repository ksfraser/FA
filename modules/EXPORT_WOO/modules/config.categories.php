<?php
//global $configArray;
/*
$configArray[] = array( 'ModuleName' => 'controller.woo_categories.php',
                        'loadFile' => 'products/controller.woo_categories.php',
                        'loadpriority' => 2,
                        'className' => 'controller_woo_categories',
                        'objectName' => 'controller_woo_categories',   //For multi classes within a module calling each other
                        'tablename' => 'woo_categories',     //Check to see if the table exists?
			'tabdata' => array (
				'tabtitle' => 'Product Attribute',
				'action' => 'p_attr',
				'form' => 'prod_attributes_form',
				'hidden' => false,
				'class' => 'controller_woo_categories'
				),
			'taborder' => 5,
                        );
$configArray[] = array( 'ModuleName' => 'controller.woo_categories_terms.php',
                        'loadFile' => 'products/controller.woo_categories_terms.php',
                        'loadpriority' => 2,
                        'className' => 'controller_woo_categories_terms',
                        'objectName' => 'controller_woo_categories_terms',   //For multi classes within a module calling each other
                        'tablename' => 'woo_categories_terms',     //Check to see if the table exists?
			'tabdata' => array (
				'tabtitle' => 'Product Attribute Terms',
				'action' => 'p_attr_terms',
				'form' => 'prod_attributes_terms_form',
				'hidden' => false,
				'class' => 'controller_woo_categories_terms'
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

$configArray[] = array( 'ModuleName' => 'controller_woo_categories',
                        'loadFile' => 'categories/controller.woo_categories.php',
                        'loadpriority' => 2,
                        'className' => 'controller_woo_categories',
                        'objectName' => 'controller_woo_categories',   //For multi classes within a module calling each other
                        'tablename' => '',     //Check to see if the table exists?
			'tabdata' => array (
				'tabtitle' => 'Sync Categories (REST)',
				'action' => 'export-categories-rest',
				'form' => 'export_categories_form',
				'hidden' => false,
				'class' => 'controller_woo_categories',
				'additional_menus' => array( array( 'title' => 'Categories Sent (REST)',
								'action' => 'exported-categories-rest',
								'form' => 'exported_categories_form',
								'hidden' => true,
								'class' => 'controller_woo_categories'
							) ),
				),
			'taborder' => 6,
                        );;
?>

