<?php
$configArray[] = array( 'ModuleName' => 'Localstore',
                        'loadFile' => 'LocalStore/class.localstore.php',
                        'loadpriority' => 2,
                        'className' => 'search_master',
                        'objectName' => 'search_master',   //For multi classes within a module calling each other
                        'tablename' => 'master',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'Localstore',
                        'loadFile' => 'LocalStore/class.localstore.php',
                        'loadpriority' => 1,
                        'className' => 'search_holding',
                        'objectName' => 'search_holding',   //For multi classes within a module calling each other
                        'tablename' => 'master_holding',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'Localstore',
                        'loadFile' => 'LocalStore/class.mydvds.php',
                        'loadpriority' => 4,
                        'className' => 'search_mydvds',
                        'objectName' => 'search_mydvds',   //For multi classes within a module calling each other
                        'tablename' => 'mydvds',     //Check to see if the table exists?
                        );
$configArray[] = array( 'ModuleName' => 'Localstore',
                        'loadFile' => 'LocalStore/class.localstore.php',
                        'loadpriority' => 1,
                        'className' => 'insertupdate_master',
                        'objectName' => 'insertupdate_master',   //For multi classes within a module calling each other
                        'tablename' => 'master',     //Check to see if the table exists?
                        );
?>
