<?php
$configArray[] = array( 'ModuleName' => 'OFX_Import',
                        'loadFile' => 'class.ofx_import.php',
                        'loadpriority' => 2,
			'taborder' => 2,
			'tabdata' => array('tabtitle' => 'OFX_Import', 'action' => 'ofx_import', 'form' => 'form_ofx_import', 'hidden' => FALSE),
                        'className' => 'ofx_import',
                        'objectName' => 'ofx_import',   //For multi classes within a module calling each other
                        'tablename' => 'ofx_import',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'OFX_Import',
                        'loadFile' => 'class.ofx_import.php',
                        'loadpriority' => 2,
			'taborder' => 2,
			'tabdata' => array('tabtitle' => 'OFX_Import', 'action' => 'ofx_import', 'form' => 'form_ofx_import_completed', 'hidden' => TRUE),
                        'className' => 'ofx_import',
                        'objectName' => 'ofx_import',   //For multi classes within a module calling each other
                        'tablename' => 'ofx_import',     //Check to see if the table exists?
                        );

?>
