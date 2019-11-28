<?php
//20141218 This works for deleting modules.
//Need to also delete the modules/$modulename  directory upon success
    $Vtiger_Utils_Log = true;
    include_once('vtlib/Vtiger/Module.php');

	$mods2delete = array ( 	
				//'ModuleBuilder', 
				//'snailmaillog5',
				//'Timesheet',
				//'Businesscase',
				'CobroPago'
	 		);
	foreach( $mods2delete as $mod )
	{

    		$module = Vtiger_Module::getInstance($mod);
    		if($module)
    		{
    		    $module->delete();
    		}
	}
?>
