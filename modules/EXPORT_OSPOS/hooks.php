<?php
define ('SS_EXPORTOSPOS', 112<<8);

class hooks_EXPORT_OSPOS extends hooks {
	var $module_name = 'EXPORT OSPOS'; 

	/*
		Install additonal menu options provided by module
	*/
	function install_options($app) {
		global $path_to_root;

		switch($app->id) {
			case 'AP':
				$app->add_rapp_function(2, _('EXPORT OSPOS'), 
					$path_to_root.'/modules/EXPORT_OSPOS/EXPORT_OSPOS.php', 'SA_EXPORTOSPOS');
				break;
			case 'stock':
				$app->add_rapp_function(2, _('EXPORT OSPOS'), 
					$path_to_root.'/modules/EXPORT_OSPOS/EXPORT_OSPOS.php', 'SA_EXPORTOSPOS');
				break;
		}
	}

	function install_access()
	{
		$security_sections[SS_EXPORTOSPOS] = _("EXPORT OSPOS");

		$security_areas['SA_EXPORTOSPOS'] = array(SS_EXPORTOSPOS|101, _("EXPORT OSPOS"));

		return array($security_areas, $security_sections);
	}
}
?>
