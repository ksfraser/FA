
<?php
define ('SS_ksf_drop_ship', 111<<8);

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
class hooks_ksf_drop_ship extends hooks {
	var $module_name; 
	function __construct()
	{
		$this->module_name = 'ksf_drop_ship';
	}

	/*
		Install additonal menu options provided by module
	*/
	function install_options($app) {
		global $path_to_root;
		$mod_rel_path = '/modules/ksf_drop_ship/';

		switch($app->id) {
			//case 'GL':
			//case 'system':
			//case 'stock':
			//case 'AP':
			//case 'orders':
			case 'stock':
				$app->add_rapp_function(2, _('ksf_drop_ship'), 
				$mod_rel_path . 'ksf_drop_ship.php', 'SA_ksf_drop_ship');	//example doesn't have $path_to_root in from of mod_rel_path but this may then break!
		}
	}

	function install_access()
	{
		$security_sections[SS_ksf_drop_ship] = _("ksf_drop_ship");

		$security_areas['SA_ksf_drop_ship'] = array(SS_ksf_drop_ship|101, _("ksf_drop_ship"));

		return array($security_areas, $security_sections);
	}
	function db_postwrite(&$cart, $trans_type)
	{
		//display_notification( "ksf_drop_ship hooks was told about " . $trans_type );
		//this is called every time a CART is written to a db
		//we could use this to send updates for QOH, or every time
		//a new product is added we could send to WOO
		//type 30 == sales_order
		//type 13 == delivery
		//type 10 == invoice?
		//type 12 == payment?
		display_notification( __FILE__ . "::" . __LINE__ );
		if( $trans_type === ST_SALESORDER )
		{
			echo "<br />Sales Order Shipping by: " . $cart->ship_via . "<br />";
			/*
			require_once( 'class.ksf_drop_ship_config.php' );
			require_once( 'ksf_drop_ship.inc.php' );
			$config = new ksf_drop_ship_config( ksf_drop_ship_PREFS );
			 */
			//Get "name" of shipping method
			/*
*			//if == DROP SHIP
*			if( $cart->ship_via === $config->get_drop_shipper_id() )
*			{
*			
*				$po_array = array();
*				//Purchase Order will need to increment through line_items
*				foreach( $cart->line_items as $item_arr )
*				{
*					//check to see if item is in our drop ship match db
*					//if so add to a PO for the vendor
*					if( isset( $po_array[$vendor] ) )
*						$po_array[$vendor]->add_item();
*					else
*					{
*						$po_array[$vendor] = new PO();
*						$po_array[$vendor]->add_item();
*					}
*					//Generate each PO
*					foreach( $po_array as $vendor )
*					{
*						$vendor->generate_PO();
*						$vendor->email_PO();
*						$vendor->print_PO();
*					}
*				}
*			}
			 */
		}
		return true;
	}
	/*
	function install_tabs($app)
	{
		$app->add_application(new example_class); // add menu tab defined by example_class
	}
	*/
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

	function db_prewrite(&$cart, $trans_type)
	{
		return true;
	}
	*/
	/*

	function db_postwrite(&$cart, $trans_type)
	{
		return true;
		//display_notification( "ksf_drop_ship hooks was told about " . $trans_type );
		//this is called every time a CART is written to a db
		//we could use this to send updates for QOH, 
		//or every time a new product is added we could send to WOO
		//Every time a sales order is placed we could send WOO the order
		//Every time a delivery is done update WOO (this allows reviews?)
		//type 30 == sales_order
		//type 13 == delivery
		//type 12 == invoice?
		//type 10 == payment?
	 	 
	}
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
		return true;
     		global $db_connections;
                $updates = array(
                        //'install_myapp.sql' => array('assets'),
			'sql/ksf_drop_ship_types.sql' => array('ksf_drop_ship_types'),
			'sql/ksf_drop_ship.sql' => array('ksf_drop_ship'),

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
		return true;
  		global $db_connections;
                $updates = array(
                        'drop_myapp.sql' => array('assets')
                );

		return $this->update_databases($company, $updates, $check_only);	
	}

	 */
}
