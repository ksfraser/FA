<?php
$configArray[] = array( 'ModuleName' => 'crm_sales_quote',
                        'loadFile' => 'class.crm_sales_quote.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_sales_quote', 'action' => 'crm_sales_quote', 'form' => 'form_crm_sales_quote', 'hidden' => FALSE),
                        'className' => 'crm_sales_quote',
                        'objectName' => 'crm_sales_quote',   //For multi classes within a module calling each other
                        'tablename' => 'crm_sales_quote',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'crm_sales_quote',
                        'loadFile' => 'class.crm_sales_quote.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_sales_quote', 'action' => 'crm_sales_quote', 'form' => 'form_crm_sales_quote_completed', 'hidden' => TRUE),
                        'className' => 'crm_sales_quote',
                        'objectName' => 'crm_sales_quote',   //For multi classes within a module calling each other
                        'tablename' => 'crm_sales_quote',     //Check to see if the table exists?
                        );

?>
