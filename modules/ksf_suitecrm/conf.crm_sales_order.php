<?php
$configArray[] = array( 'ModuleName' => 'crm_sales_order',
                        'loadFile' => 'class.crm_sales_order.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_sales_order', 'action' => 'crm_sales_order', 'form' => 'form_crm_sales_order', 'hidden' => FALSE),
                        'className' => 'crm_sales_order',
                        'objectName' => 'crm_sales_order',   //For multi classes within a module calling each other
                        'tablename' => 'crm_sales_order',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'crm_sales_order',
                        'loadFile' => 'class.crm_sales_order.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_sales_order', 'action' => 'crm_sales_order', 'form' => 'form_crm_sales_order_completed', 'hidden' => TRUE),
                        'className' => 'crm_sales_order',
                        'objectName' => 'crm_sales_order',   //For multi classes within a module calling each other
                        'tablename' => 'crm_sales_order',     //Check to see if the table exists?
                        );

?>
