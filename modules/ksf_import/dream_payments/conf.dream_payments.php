<?php
$configArray[] = array( 'ModuleName' => 'Dream_Payments',
                        'loadFile' => 'class.dream_payments.php',
                        'loadpriority' => 3,
			'taborder' => 3,
			'tabdata' => array('tabtitle' => 'Dream_Payments', 'action' => 'dream_payments', 'form' => 'form_dream_payments', 'hidden' => FALSE),
                        'className' => 'dream_payments',
                        'objectName' => 'dream_payments',   //For multi classes within a module calling each other
                        'tablename' => 'dream_payments',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'Dream_Payments',
                        'loadFile' => 'class.dream_payments.php',
                        'loadpriority' => 3,
			'taborder' => 3,
			'tabdata' => array('tabtitle' => 'Dream_Payments', 'action' => 'dream_payments', 'form' => 'form_dream_payments_completed', 'hidden' => TRUE),
                        'className' => 'dream_payments',
                        'objectName' => 'dream_payments',   //For multi classes within a module calling each other
                        'tablename' => 'dream_payments',     //Check to see if the table exists?
                        );

?>
