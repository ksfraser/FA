<?php
$configArray[] = array( 'ModuleName' => 'crm_sales_invoice',
                        'loadFile' => 'class.crm_sales_invoice.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_sales_invoice', 'action' => 'crm_sales_invoice', 'form' => 'form_crm_sales_invoice', 'hidden' => FALSE),
                        'className' => 'crm_sales_invoice',
                        'objectName' => 'crm_sales_invoice',   //For multi classes within a module calling each other
                        'tablename' => 'crm_sales_invoice',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'crm_sales_invoice',
                        'loadFile' => 'class.crm_sales_invoice.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_sales_invoice', 'action' => 'crm_sales_invoice', 'form' => 'form_crm_sales_invoice_completed', 'hidden' => TRUE),
                        'className' => 'crm_sales_invoice',
                        'objectName' => 'crm_sales_invoice',   //For multi classes within a module calling each other
                        'tablename' => 'crm_sales_invoice',     //Check to see if the table exists?
                        );

?>
