<?php
$configArray[] = array( 'ModuleName' => 'crm_customer_branch',
                        'loadFile' => 'class.crm_customer_branch.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_customer_branch', 'action' => 'crm_customer_branch', 'form' => 'form_crm_customer_branch', 'hidden' => FALSE),
                        'className' => 'crm_customer_branch',
                        'objectName' => 'crm_customer_branch',   //For multi classes within a module calling each other
                        'tablename' => 'crm_customer_branch',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'crm_customer_branch',
                        'loadFile' => 'class.crm_customer_branch.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_customer_branch', 'action' => 'crm_customer_branch', 'form' => 'form_crm_customer_branch_completed', 'hidden' => TRUE),
                        'className' => 'crm_customer_branch',
                        'objectName' => 'crm_customer_branch',   //For multi classes within a module calling each other
                        'tablename' => 'crm_customer_branch',     //Check to see if the table exists?
                        );

?>
