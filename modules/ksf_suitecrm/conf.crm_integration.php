<?php
$configArray[] = array( 'ModuleName' => 'crm_integration',
                        'loadFile' => 'class.crm_integration.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_integration', 'action' => 'crm_integration', 'form' => 'form_crm_integration', 'hidden' => FALSE),
                        'className' => 'crm_integration',
                        'objectName' => 'crm_integration',   //For multi classes within a module calling each other
                        'tablename' => 'crm_integration',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'crm_integration',
                        'loadFile' => 'class.crm_integration.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'crm_integration', 'action' => 'crm_integration', 'form' => 'form_crm_integration_completed', 'hidden' => TRUE),
                        'className' => 'crm_integration',
                        'objectName' => 'crm_integration',   //For multi classes within a module calling each other
                        'tablename' => 'crm_integration',     //Check to see if the table exists?
                        );

?>
