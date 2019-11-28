<?php
$configArray[] = array( 'ModuleName' => 'crm_customer',
                        'loadFile' => 'class.crm_customer.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_customer', 'action' => 'crm_customer', 'form' => 'form_crm_customer', 'hidden' => FALSE),
                        'className' => 'crm_customer',
                        'objectName' => 'crm_customer',   //For multi classes within a module calling each other
                        'tablename' => 'crm_customer',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'crm_customer',
                        'loadFile' => 'class.crm_customer.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_customer', 'action' => 'crm_customer', 'form' => 'form_crm_customer_completed', 'hidden' => TRUE),
                        'className' => 'crm_customer',
                        'objectName' => 'crm_customer',   //For multi classes within a module calling each other
                        'tablename' => 'crm_customer',     //Check to see if the table exists?
                        );

?>
