<?php

require_once( 'fa_cust.php' ); 

class generic_customers extends db_base
{
	var $facust;
	var $min_cid;
	var $max_cid;
        var $db_Host;
    	var $db_User;
    	var $db_Password;
    	var $db_Name;
    	var $last_cid;
    	var $last_oid;
    	var $default_TaxGroup;
    	var $default_Currency;
	var $customer_index_name;
	var $customer_table_name;
	var $datasource_name;
	/*********************************************************************
	 *
	 *	This function must be overridden to work correctly
	 *	The inheriting class MUST set customer_index_name,
	 *	customer_table_name and datasource_name
	 *
	 *********************************************************************/
	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		$this->facust = new fa_cust( $host, $user, $pass, $database );
/*
		$this->customer_index_name = "contactid";
		$this->customer_table_name = "vtiger_contactdetails";
		$this->datasource_name = "VTIGER";
*/
	}
	function get_id_range()
	{
       		$sql = "SELECT MIN(`" . $this->customer_index_name . "`) as min_cid, MAX(`" . $this->customer_index_name . "`) as max_cid FROM `" . $this->customer_table_name . "`";
    		$result = db_query($sql, "Couldn't get customer ID range" );
 		$this->min_cid = max((int)$result['min_cid'], $this->last_cid+1);
        	$this->max_cid = max($this->min_cid, (int)$result['max_cid']);

    		return mysql_fetch_assoc($result);
	}
	/*********************************************************************
	 *
	 *	This function must be overridden to work correctly
	 *	The inheriting class MUST set its SQL statement, as well as the
	 *	datasource specific processing into facust.
	 *
	 *********************************************************************/
	function import_customers()
	{
		if( !isset( $this->db_connection ) )
			$this->connect_db();	//connect to DB setting db_connection used below.
		$this->get_id_range();

            $sql = "SELECT d.contactid as contactID, d.contact_no as contactNum, d.accountid as organizationID, d.salutation as salutation, d.firstname as firstname, d.lastname as lastname, d.email as email, d.phone as phone, d.mobile as phone2, d.fax as fax, a.mailingcity as city, a.mailingstreet as street, a.mailingcountry as country, a.mailingstate as state FROM fhs.vtiger_contactdetails d, fhs.vtiger_contactaddress a WHERE d.contactid  >= $this->min_cid AND d.contactid <= $this->max_cid and d.contactid=a.contactaddressid";
            $customers = mysql_query($sql, $this->db_connection);
            display_notification("Found " . db_num_rows($customers) . " new customers");
            $i = $j = $k = 0;
            while ($cust = mysql_fetch_assoc($customers)) {
                $this->facust->set_var( 'name', $cust['firstname'] . ' ' . $cust['lastname'] );
                $this->facust->set_var( 'cust_ref', $cust['firstname'] . ' ' . $cust['lastname'] );
                $this->facust->set_var( 'contact', $cust['firstname'] . ' ' . $cust['lastname'] );
                $this->facust->set_var( 'address', $cust['street'] . "\n\r" . $cust['city'] . "\n\r" . $cust['state'] . "\n\r" . $cust['country'] . "\n\r" );
                $this->facust->set_var( 'tax_id', '' );
                $this->facust->set_var( 'phone', $cust['phone'] );
                $this->facust->set_var( 'phone2', $cust['phone2'] );
                $this->facust->set_var( 'fax', $cust['fax'] );
                $this->facust->set_var( 'email', $cust['email'] );
                $this->facust->set_var( 'area_code', substr( $cust['phone'], 0, 3) );
                $this->facust->set_var( 'curr_code', 'CAD' );
                $this->facust->set_var( 'salesman', '1' );
                $this->facust->set_var( 'tax_group_id', '3' );
                $this->facust->set_var( 'tax_id', '' );
                $this->facust->set_var( 'fulfill_from_location', '1' );
                $this->facust->set_var( 'ship_via', '1' );
                $this->facust->set_var( 'dimension1', '' );
                $this->facust->set_var( 'dimension2', '' );
                $this->facust->set_var( 'credit_status', '1' );
                $this->facust->set_var( 'payment_terms', '5' );
                $this->facust->set_var( 'credit_limit', "1000.00" );
                $this->facust->set_var( 'payment_discount', "0.00" );
                $this->facust->set_var( 'sales_type', $_POST['sales_type'] );

		$ret = $this->facust->insert_customer();

		if( $ret == 1 )
		{
			//success
			$this->set_pref( "lastcid", $cust["contactID"] );
                    	$i++;
		}
		else if ($ret == 0)
		{
			//duplicate
                    	$j++;	//customer ignored (duplicate)
		}
		else if( $ret == -1 )
		{
			//error
			$k++;	//insert failed
		}
            }
            display_notification("$i customers created, $j duplicate customers ignored, $k customer inserts failed.");
	}
	function loadprefs()
	{
    		// Get Host Name
        	 $this->db_Host = $this->get_pref('myhost');

    		// Get User Name
    		$this->db_User = $this->get_pref('myuser');

    		// Get Password
    		$this->db_Password = $this->get_pref('mypassword');

    		// Get DB Name
    		$this->db_Name = $this->get_pref('myname');

    		// Get last cID imported
    		$this->last_cid = $this->get_pref('lastcid');

    		// Get last oID imported
    		$this->last_oid = $this->get_pref('lastoid');

    		// Get Default Tax Group
    		$this->default_TaxGroup = $this->get_pref('taxgroup');

    		//Get Default Currency
    		$this->default_Currency = $this->get_pref('currency');
	}
	function checkprefs()
	{
		if (isset($_POST['dbHost'])) $dbHost = $_POST['dbHost'];
		if (isset($_POST['dbUser'])) $dbUser = $_POST['dbUser'];
		if (isset($_POST['dbPassword'])) $dbPassword = $_POST['dbPassword'];
		if (isset($_POST['dbName'])) $dbName = $_POST['dbName'];
		if (isset($_POST['taxgroup'])) $defaultTaxGroup = $_POST['taxgroup'];
		if (isset($_POST['currency'])) $defaultCurrency = $_POST['currency'];

		if ($dbHost != $this->db_Host) // If it changed
			$this->set_pref('myhost', $dbHost);
		if ($dbUser != $this->db_User) // If it changed
			$this->set_pref('myuser', $dbUser);
		if ($dbPassword != $this->db_Password) // If it changed
			$this->set_pref('mypassword', $dbPassword);
		if ($dbName != $this->db_Name) // If it changed
			$this->set_pref('myname', $dbName);
		if ($defaultTaxGroup != $this->default_TaxGroup) // If it changed
			$this->set_pref('taxgroup', $defaultTaxGroup);
		if ($defaultCurrency != $this->default_Currency) // If it changed
			$this->set_pref('currency', $defaultCurrency);
	}
	function minmax_cids()
	{
		if (isset($_POST['min_cid']))
			$this->set_var( "min_cid", $_POST['min_cid'] );
		if (isset($_POST['max_cid'])) 
			$this->set_var( "max_cid", $_POST['max_cid'] );
	}
	function action_show_form( $found )
	{
		start_form(true);
		 start_table(TABLESTYLE2, "width=40%");

		 $th = array("Function", "Description");
		 table_header($th);

		 $k = 0;

		 alt_table_row_color($k);

		 label_cell("Table Status");
		 if ($found) $table_st = "Found";
		 else $table_st = "<font color=red>Not Found</font>";
		 label_cell($table_st);
		 end_row();

		 text_row("Mysql Host", 'dbHost', $this->db_Host, 20, 40);

		 text_row("User", 'dbUser', $this->db_User, 20, 40);

		 text_row("Password", 'dbPassword', $this->db_Password, 20, 40);

		 text_row("DB Name", 'dbName', $this->db_Name, 20, 40);
		 tax_groups_list_row(_("Default Tax Group:"), 'taxgroup', $this->default_TaxGroup);
		 currencies_list_row(_("Default Currency:"), 'currency', $this->default_Currency);

		 end_table(1);


		 if (!$found) {
		     hidden('action', 'create');
		     submit_center('create', 'Create Table');
		 } else {
		     hidden('action', 'update');
		     submit_center('update', 'Update Mysql');
		 }

		 end_form();
		end_page();
	}
	function action_cimport_form()
	{
		 start_form(true);

		 start_table(TABLESTYLE2, "width=40%");

		 table_section_title("Default GL Accounts");

		 $company_record = get_company_prefs();

		 if (!isset($_POST['sales_account']) || $_POST['sales_account'] == "")
		         $_POST['sales_account'] = $company_record["default_sales_act"];

		 if (!isset($_POST['sales_discount_account']) || $_POST['sales_discount_account'] == "")
		         $_POST['sales_discount_account'] = $company_record["default_sales_discount_act"];

		 if (!isset($_POST['receivables_account']) || $_POST['receivables_account'] == "")
		         $_POST['receivables_account'] = $company_record["debtors_act"];

		 if (!isset($_POST['payment_discount_account']) || $_POST['payment_discount_account'] == "")
		         $_POST['payment_discount_account'] = $company_record["default_prompt_payment_act"];

		 gl_all_accounts_list_row("Sales Account:", 'sales_account', $_POST['sales_account']);
		 gl_all_accounts_list_row("Sales Discount Account:", 'sales_discount_account', $_POST['sales_discount_account']);
		 gl_all_accounts_list_row("Receivables Account:", 'receivables_account', $_POST['receivables_account']);
		 gl_all_accounts_list_row("Payment Discount Account:", 'payment_discount_account', $_POST['payment_discount_account']);

		 table_section_title("Location, Tax Type, Sales Type, Sales Person and Payment Terms");
		 locations_list_row("Location:", 'default_location', 'DEF');
		 tax_groups_list_row(_("Default Tax Group:"), 'tax_group_id', $this->default_TaxGroup);
		 sales_types_list_row("Sales Type:", 'sales_type', null);
		 sales_persons_list_row("Sales Person:", 'salesman', null);
		 sales_areas_list_row("Sales Area:", 'area');
		 currencies_list_row("Customer Currency:", 'currency', $this->default_Currency);
		 payment_terms_list_row("Payment Terms:", 'payment_terms', null);
		 text_row("Starting " . $this->datasource_name . " Customer ID:", 'min_cid', $this->min_cid, 10, 10);
		 text_row("Ending " . $this->datasource_name . " Customer ID:", 'max_cid', $this->max_cid, 10, 10);

		 end_table(1);

		 hidden('action', 'c_import');
		 submit_center('cimport', "Import  " . $this->datasource_name . " Customers");

		 end_form();
		end_page();
	}
	function base_page( $action )
	{
		page(_($help_context = "vtiger Interface"));
		//echo __FILE__ . ":" . __LINE__ . "<br />";
		if ($action == 'show')
		{
		        echo 'Configuration';
		}
		else hyperlink_params($_SERVER['PHP_SELF'], _("Configuration"), "action=show", false);
		echo '&nbsp;|&nbsp;';
		if ($action == 'cimport'){
		        echo 'Customer Import';
		}
		else hyperlink_params($_SERVER['PHP_SELF'], _("&Customer Import"), "action=cimport", false);
		echo '&nbsp;|&nbsp;';
	}
}

?>
