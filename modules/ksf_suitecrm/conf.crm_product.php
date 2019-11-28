<?php
$configArray[] = array( 'ModuleName' => 'crm_product',
                        'loadFile' => 'class.crm_product.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_product', 'action' => 'crm_product', 'form' => 'form_crm_product', 'hidden' => FALSE),
                        'className' => 'crm_product',
                        'objectName' => 'crm_product',   //For multi classes within a module calling each other
                        'tablename' => 'crm_product',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'crm_product',
                        'loadFile' => 'class.crm_product.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_product', 'action' => 'crm_product', 'form' => 'form_crm_product_completed', 'hidden' => TRUE),
                        'className' => 'crm_product',
                        'objectName' => 'crm_product',   //For multi classes within a module calling each other
                        'tablename' => 'crm_product',     //Check to see if the table exists?
                        );

?>
