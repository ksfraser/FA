<?php
$configArray[] = array( 'ModuleName' => 'QIF_Import',
                        'loadFile' => 'class.qif_import.php',
                        'loadpriority' => 2,
			'taborder' => 2,
			'tabdata' => array('tabtitle' => 'QIF_Import', 'action' => 'qif_import', 'form' => 'form_qif_import', 'hidden' => FALSE),
                        'className' => 'qif_import',
                        'objectName' => 'qif_import',   //For multi classes within a module calling each other
                        'tablename' => 'qif_import',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'QIF_Import',
                        'loadFile' => 'class.qif_import.php',
                        'loadpriority' => 2,
			'taborder' => 2,
			'tabdata' => array('tabtitle' => 'QIF_Import', 'action' => 'qif_import', 'form' => 'form_qif_import_completed', 'hidden' => TRUE),
                        'className' => 'qif_import',
                        'objectName' => 'qif_import',   //For multi classes within a module calling each other
                        'tablename' => 'qif_import',     //Check to see if the table exists?
                        );

?>
