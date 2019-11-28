<?php
define ('SS_EXPORTWOO', 111<<8);

class hooks_EXPORT_WOO extends hooks {
	var $module_name = 'Export WOO'; 

	/*
		Install additonal menu options provided by module
	*/
	function install_options($app) {
		global $path_to_root;

		switch($app->id) {
			case 'stock':
				$app->add_rapp_function(2, _('Export WOO'), 
					$path_to_root.'/modules/EXPORT_WOO/EXPORT_WOO.php', 'SA_EXPORTWOO');
		}
	}

	function install_access()
	{
		$security_sections[SS_EXPORTWOO] = _("Export WOO");

		$security_areas['SA_EXPORTWOO'] = array(SS_EXPORTWOO|101, _("Export WOO"));

		return array($security_areas, $security_sections);
	}
}
?>
