<?php
define ('SS_GENERATEEAN', 113<<8);

class hooks_generate_EAN extends hooks {
	var $module_name = 'Generate EAN'; 

	/*
		Install additonal menu options provided by module
	*/
	function install_options($app) {
		global $path_to_root;

		switch($app->id) {
			case 'AP':
				$app->add_rapp_function(2, _('Generate EAN'), 
					$path_to_root.'/modules/generate_EAN/generate_EAN.php', 'SA_GENERATEEAN');
				break;
			case 'stock':
				$app->add_rapp_function(2, _('Generate EAN'), 
					$path_to_root.'/modules/generate_EAN/generate_EAN.php', 'SA_GENERATEEAN');
				break;
		}
	}

	function install_access()
	{
		$security_sections[SS_GENERATEEAN] = _("Generate EAN");

		$security_areas['SA_GENERATEEAN'] = array(SS_GENERATEEAN|101, _("Generate EAN"));

		return array($security_areas, $security_sections);
	}
}
?>
