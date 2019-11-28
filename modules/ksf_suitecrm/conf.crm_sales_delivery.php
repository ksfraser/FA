<?php
$configArray[] = array( 'ModuleName' => 'crm_sales_delivery',
                        'loadFile' => 'class.crm_sales_delivery.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_sales_delivery', 'action' => 'crm_sales_delivery', 'form' => 'form_crm_sales_delivery', 'hidden' => FALSE),
                        'className' => 'crm_sales_delivery',
                        'objectName' => 'crm_sales_delivery',   //For multi classes within a module calling each other
                        'tablename' => 'crm_sales_delivery',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'crm_sales_delivery',
                        'loadFile' => 'class.crm_sales_delivery.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_sales_delivery', 'action' => 'crm_sales_delivery', 'form' => 'form_crm_sales_delivery_completed', 'hidden' => TRUE),
                        'className' => 'crm_sales_delivery',
                        'objectName' => 'crm_sales_delivery',   //For multi classes within a module calling each other
                        'tablename' => 'crm_sales_delivery',     //Check to see if the table exists?
                        );

?>
