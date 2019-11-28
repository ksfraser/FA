
<?php
define ('SS_ksf_price_sticker_need_change', 111<<8);

	/**************NOTE**********************************
	 * Using DISPLAY_* causes the next screen (print receipt etc) to not appear.  This is due to the AJAX - going to next screen
	 * using a URL redirect nukes the messages!  See line 465 in sales_order_entry.php
	 * **************************************************/


/***************************************************************************************
 *
 * Hooks is what adds menus, etc to FrontAccounting.
 * It also appears to be called pre and post database transactions
 * for certain modules (see includes/hooks.inc) around line 360
 * 	hook_db_prewrite
 * 	hook_db_postwrite
 * 	hook_db_prevoid
 *
 * Looks like we could also provide our own authentication module
 * 	hook_authenticate (useful for REST?)
 *
 * ***********************************************************************************/


class ksf_price_sticker_need_change_app extends application 
{
	function __construct()
	{
		global $path_to_root;
		parent::__construct( ksf_price_sticker_need_change, _($this->help_context = ksf_price_sticker_need_change ) );
		$this->add_module( _( "Transactions" );
	        $this->add_lapp_function(0, _('Transaction 1'), $path_to_root.'/modules/ksf_price_sticker_need_change/manage/transaction1.php?NewTransaction1=Yes', 'SA_ksf_price_sticker_need_change_TRAN', MENU_TRANSACTION);
        	$this->add_rapp_function(0, _('Transaction 2'), $path_to_root.'/modules/ksf_price_sticker_need_change/manage/transaction2.php?NewTransaction2=Yes', 'SA_ksf_price_sticker_need_change_TRAN', MENU_TRANSACTION);

		$this->add_module( _( "Inquiries and Reports" );
	        $this->add_lapp_function(1, _('Report 1'), $path_to_root.'/modules/ksf_price_sticker_need_change/manage/report1.php?NewAction1=Yes', 'SA_ksf_price_sticker_need_change_REP', MENU_INQUIRY);
        	$this->add_rapp_function(1, _('Report 2'), $path_to_root.'/modules/ksf_price_sticker_need_change/manage/report2.php?NewAction2=Yes', 'SA_ksf_price_sticker_need_change_REP', MENU_INQUIRY);

		$this->add_module( _( "Maintenance" );
	        $this->add_lapp_function(1, _('Maintenance 1'), $path_to_root.'/modules/ksf_price_sticker_need_change/manage/maintenance1.php?NewAction1=Yes', 'SA_ksf_price_sticker_need_change_ADMIN', MENU_MAINTENANCE);
        	$this->add_rapp_function(1, _('Maintenance 2'), $path_to_root.'/modules/ksf_price_sticker_need_change/manage/maintenance2.php?NewAction2=Yes', 'SA_ksf_price_sticker_need_change_ADMIN', MENU_MAINTENANCE);

		$this->add_extensions();
	}
}


class hooks_ksf_price_sticker_need_change extends hooks {
	var $module_name; 
	function __construct()
	{
		$this->module_name = 'ksf_price_sticker_need_change';
	}

	/*
		Install additonal menu options provided by module
	*/
	function install_options($app) {
		switch($app->id) {
			//case 'GL':
			//case 'system':
			//case 'stock':
			//case 'AP':
			//case 'orders':
			case 'stock':
				$app->add_rapp_function(2, _('ksf_price_sticker_need_change'), 
				 'modules/ksf_price_sticker_need_change/ksf_price_sticker_need_change.php', 'SA_ksf_price_sticker_need_change');	//example doesn't have $path_to_root in from of mod_rel_path but this may then break!
		}
	}

	function install_access()
	{
		$security_sections[SS_ksf_price_sticker_need_change] = _("ksf_price_sticker_need_change");

		$security_areas['SA_ksf_price_sticker_need_change'] = array(SS_ksf_price_sticker_need_change|101, _("ksf_price_sticker_need_change"));
		$security_areas['SA_ksf_price_sticker_need_change_ADMIN'] = array(SS_ksf_price_sticker_need_change|101, _("ksf_price_sticker_need_change"));
		$security_areas['SA_ksf_price_sticker_need_change_TRAN'] = array(SS_ksf_price_sticker_need_change|101, _("ksf_price_sticker_need_change"));
		$security_areas['SA_ksf_price_sticker_need_change_REP'] = array(SS_ksf_price_sticker_need_change|101, _("ksf_price_sticker_need_change"));

		return array($security_areas, $security_sections);
	}
	function db_postwrite(&$cart, $trans_type)
	{
		//this is called every time a CART is written to a db
		//var_dump(  );
		//var_dump(  );
	}
	function db_prewrite(&$cart, $trans_type)
	{
		//var_dump( $trans_type );
		//var_dump( $cart );


		//Want to trap item price updates.

		/*
		if( require_once( 'class.ksf_price_sticker_need_change_model.php' ) )
		{
			$model = new ksf_price_sticker_need_change_model( ksf_price_sticker_need_change_PREFS, $this );
			$model->set_var( PRIKEY, $cart->SOURCE_PRIKEY );	//Primary Key
			try {
				$model->select_row();	//Primary Key is set.
				$cart->FIELD2MOD = $model->get( NEWVALUE );
			} catch( Exception $e )
			{
				//var_dump( $model );
				//display_error( __METHOD__ . " " . $e->getMessage() );
			}
		}
		*/
	}
	function install_tabs($app)
	{
		$app->add_application(new ksf_price_sticker_need_change_app); // add menu tab defined by example_class
	}
	/*
	//
	//	Invoked for all modules before page header is displayed
	//
	function pre_header($fun_args)
	{
	}
	*/
	/*
	//
	//	Invoked for all modules before page footer is displayed
	//
	function pre_footer($fun_args)
	{
	}
	*/
	/*

	//
	// Price in words. $doc_type is set to document type and can be used to suppress 
	// price in words printing for selected document types.
	// Used instead of built in simple english price_in_words() function.
	//
	//	Returns: amount in words as string.

	function price_in_words($amount, $doc_type)
	{
	}
	*/
	/*
	//
	// Exchange rate currency $curr as on date $date.
	// Keep in mind FA has internally implemented 3 exrate providers
	// If any of them supports your currency, you can simply use function below
	// with apprioprate provider set, otherwise implement your own.
	// Returns: $curr value in home currency units as a real number.

	function retrieve_exrate($curr, $date)
	{
//	 	$provider = 'ECB'; // 'ECB', 'YAHOO' or 'GOOGLE'
//		return get_extern_rate($curr, $provider, $date);
		return null;
	}
	*/
	/*

	// External authentication
	// If used should return true after successfull athentication, false otherwise.
	function authenticate($login, $password)
	{
		return null;
	}
	*/
	/*
	// Generic function called at the end of Tax Report (report 709)
	// Can be used e.g. for special database updates on every report printing
	// or to print special tax report footer 
	//
	// Returns: nothing
	function tax_report_done()
	{
	}
	*/
	/*
	// Following database transaction hooks akcepts array of parameters:
	// 'cart' => transaction data
	// 'trans_type' => transaction type

	*/
	/*
	function db_prevoid($trans_type, $trans_no)
	{
		return true;
		//Something like:
		/*	$sql = "
	    	 *	UPDATE ".TB_PREF."bi_transactions
	    	 *		SET status=0
	    	 *		WHERE
		 *		fa_trans_no=".db_escape($trans_no)." AND
		 *		fa_trans_type=".db_escape($trans_type)." AND
		 *		status = 1";
		 *		//display_notification($sql);
		 *	db_query($sql, 'Could not void transaction');
	 	 * /
	 }
	*/
	/*
	//
	//	This method is called after module install.
	//
	function install_extension($check_only=true)
	{
		return true;
	}
	*/
	/*
	//
	//	This method is called after module uninstall.
	//
	function uninstall_extension($check_only=true)
	{
		return true;
	}
	*/
	/*
	//
	//	This method is called on extension activation for company.
	//
	function activate_extension($company, $check_only=true)
	{
     		global $db_connections;
                $updates = array(
                        //'install_myapp.sql' => array('assets'),
                        //'sql/update.sql' => array(''),
			'sql/ksf_price_sticker_need_change_types.sql' => array('ksf_price_sticker_need_change_types'),
			'sql/ksf_price_sticker_need_change.sql' => array('ksf_price_sticker_need_change'),

                );

                return $this->update_databases($company, $updates, $check_only);	
	}
	*/
	/*
	//
	//	This method is called when extension is deactivated for company.
	//
	function deactivate_extension($company, $check_only=true)
	{
  		global $db_connections;
                $updates = array(
                        //'sql/drop_myapp.sql' => array(''),
                        'sql/remove.sql' => array(''),
                );

		return $this->update_databases($company, $updates, $check_only);	
	}

	 */
}
