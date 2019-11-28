<?php
$configArray[] = array( 'ModuleName' => 'Bank_Staging',
                        'loadFile' => 'class.bank_import_staging.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'Bank_Staging', 'action' => 'bank_import_staging', 'form' => 'form_bank_import_staging', 'hidden' => FALSE),
                        'className' => 'bank_import_staging',
                        'objectName' => 'bank_import_staging',   //For multi classes within a module calling each other
                        'tablename' => 'bank_import_staging',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'Bank_Staging',
                        'loadFile' => 'class.bank_import_staging.php',
                        'loadpriority' => 1,
			'taborder' => 1,
			'tabdata' => array('tabtitle' => 'Bank_Staging', 'action' => 'bank_import_staging', 'form' => 'form_bank_import_staging_completed', 'hidden' => TRUE),
                        'className' => 'bank_import_staging',
                        'objectName' => 'bank_import_staging',   //For multi classes within a module calling each other
                        'tablename' => 'bank_import_staging',     //Check to see if the table exists?
                        );

?>
