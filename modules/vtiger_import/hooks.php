<?php
define ('SS_VTIGERORDERS', 115<<8);

class hooks_vtiger_import extends hooks {
	var $module_name = 'Import VTiger'; 

	/*
		Install additonal menu options provided by module
	*/
	function install_options($app) {
		global $path_to_root;

		switch($app->id) {
			case 'GL':
				$app->add_rapp_function(2, _('Import VTiger'), 
					$path_to_root.'/modules/vtiger_import/vtiger.php', 'SA_VTIGERIMPORT');
				break;
			case 'orders':
				$app->add_rapp_function(2, _('Import VTiger'), 
					$path_to_root.'/modules/vtiger_import/vtiger.php', 'SA_VTIGERIMPORT');
				break;
		}
	}

	function install_access()
	{
		$security_sections[SS_VTIGERORDERS] = _("Import VTiger");

		$security_areas['SA_VTIGERIMPORT'] = array(SS_VTIGERORDERS|101, _("Import VTiger"));

		return array($security_areas, $security_sections);
	}
}
?>
