<?php
define ('SS_CDNTAX', 116<<8);

global $path_to_common;
$path_to_common = "../ksf_modules_common/";
//$path_to_common = dirname ( realpath ( __FILE__ ) ) . "/../ksf_modules_common/";


//DO NOT USE - or _ in module names/directories.  Apparantly there is some code somewhere that is parsing this
//and not having the info show up in the Role setup (you can't assign access) :(

class hooks_CDNTAX extends hooks {
	var $module_name = 'CDNTAX'; 

	/*
	*	Add a new menu tab into FA
	*/
/*
*	function install_tabs( $apps )
*	{
*		require_once( 'class.my_app.php' );	//MUST extend application
*							//First line of constructor ?must be?
*							//$this->application( "my_app", _($this->help_context = "&My App") );
*		$apps->add_application( new my_app );
*	}
*/
	function install_tabs( $app )
	{
		require_once( 'class.CDNTAX.php' );	
		//$app->add_application( new CRMAPP );
	}

	/*
		Install additonal menu options provided by module
	*/
	function install_options($app) {
		global $path_to_root;

		switch($app->id) {
			case 'GL':
				$app->add_rapp_function(2, _('CDNTAX'), 
					$path_to_root.'/modules/CDNTAX/controller.php', 'SA_CDNTAX', MENU_MAINTENANCE);
				//$app->add_rapp_function(2, _('CDNTAX Profit and Loss'), 
				//	$path_to_root.'/modules/CDNTAX/profit_loss.php', 'SA_CDNTAX', MENU_MAINTENANCE);
				$app->add_rapp_function(2, _('CDNTAX Profit and Loss'), 
					$path_to_root.'/modules/CDNTAX/profit_loss.php', 'SA_CDNTAX');
				break;
			/*
			*	Tie into the install_tabs
			*/
/*
*			case 'my_app':
*				$app->add_lapp_function(1, _('My App'), 
*					$path_to_root.'/modules/my_app/my_app.php', 'SA_MYAPP', MENU_MAINTENANCE);
*				break;
*/
		}
	}

	function install_access()
	{
		$security_sections[SS_CDNTAX] = _("CDNTAX");

		$security_areas['SA_CDNTAX'] = array(SS_CDNTAX|2, _("CDNTAX"));	//First sub access level
		$security_areas['SA_CDNTAX_ADMIN'] = array(SS_CDNTAX|3, _("CDNTAX_ADMIN"));	//For types, categories, outcomes

		return array($security_areas, $security_sections);
	}
        /* This method is called on extension activation for company.   */
        function activate_extension($company, $check_only=true)
        {
                global $db_connections;
/*
                $updates = array(
                        //'install_myapp.sql' => array('assets'),
			'sql/crm_campaign_types.sql' => array('crm_campaign_types'),
			'sql/crm_mailinglist.sql' => array('crm_mailinglist'),

                );

                return $this->update_databases($company, $updates, $check_only);
*/
        }
        /* This method is called on extension deactivation for company.   */
        function deactivate_extension($company, $check_only=true)
        {
                global $db_connections;
/*
                $updates = array(
                        'drop_myapp.sql' => array('assets')
                );

                return $this->update_databases($company, $updates, $check_only);
*/
        }

}
?>
