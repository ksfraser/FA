<?php
define ('SS_COASTORDERS', 111<<8);

class hooks_coast_export extends hooks {
	var $module_name = 'Export COAST'; 

	/*
		Install additonal menu options provided by module
	*/
	function install_options($app) {
		global $path_to_root;

		switch($app->id) {
			case 'AP':
				$app->add_rapp_function(2, _('Export COAST'), 
					$path_to_root.'/modules/coast_export/coast_export.php', 'SA_COASTEXPORT');
		}
	}

	function install_access()
	{
		$security_sections[SS_COASTORDERS] = _("Export COAST");

		$security_areas['SA_COASTEXPORT'] = array(SS_COASTORDERS|101, _("Export COAST"));

		return array($security_areas, $security_sections);
	}
}
?>
