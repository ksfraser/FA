<?php

$path_to_root="../..";

require_once( $path_to_root . '/sales/includes/db/customers_db.inc' ); //add_customer
require_once( $path_to_root . '/sales/includes/db/branches_db.inc' ); //add_branch
require_once( $path_to_root . '/includes/db/crm_contacts_db.inc' ); //add_crm_*
require_once( $path_to_root . '/includes/db/connect_db.inc' ); //db_query, ...
require_once( $path_to_root . '/includes/errors.inc' ); //check_db_error, ...

require_once( 'db_base.php' );

//class db_base
//{
//	var $action;
//	var $dbHost;
//	var $dbUser;
//	var $dbPassword;
//	var $dbName;
//	var $db_connection;
//	var $import_prefs_tablename;
//	function __construct( $host, $user, $pass, $database, $prefs_tablename )
//	{
//		$this->set_var( "dbHost", $host );
//		$this->set_var( "dbUser", $user );
//		$this->set_var( "dbPassword", $pass );
//		$this->set_var( "dbName", $database );
//		$this->set_var( "import_prefs_tablename", $prefs_tablename );
//		$this->connect_db();
//	}
//	function set_var( $var, $value )
//	{
///*
//		if(!empty($value) && is_string($value)) {
//        		$this->$var = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
//    		}
//		else
//		{
//*/
//			$this->$var = $value ;
///*
//		}
//*/
//	}
//	function get_var( $var )
//	{
//		return $this->$var;
//	}
//	function connect_db()
//	{
//        	$this->db_connection = mysql_connect($this->dbHost, $this->dbUser, $this->dbPassword);
//        	if (!$this->db_connection) 
//		{
//			display_notification("Failed to connect to source of import Database");
//			return FALSE;
//		}
//		else
//		{
//            		mysql_select_db($this->dbName, $this->db_connection);
//			return TRUE;
//		}
//	}
//	/*bool*/ function is_installed()
//	{
//        	global $db_connections;
//        
//		$cur_prefix = $db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'];
//
//        	$sql = "SHOW TABLES LIKE '%" . $cur_prefix . $this->import_prefs_tablename . "%'";
//        	$result = db_query($sql, __FILE__ . " could not show tables in is_installed: " . $sql);
//
//        	return db_num_rows($result) != 0;
//	}
//	function create_prefs_tablename()
//	{
//	        $sql = "DROP TABLE IF EXISTS " . TB_PREF . $this->import_prefs_tablename;
//		        db_query($sql, "Error dropping table");
//		
//	    	$sql = "CREATE TABLE `" . TB_PREF . $this->import_prefs_tablename ."` (
//		         `name` char(15) NOT NULL default \"\",
//		         `value` varchar(100) NOT NULL default \"\",
//		          PRIMARY KEY  (`name`))
//		          ENGINE=MyISAM";
//	    	db_query($sql, "Error creating table");
//		$this->set_pref('lastcid', 0);
//		$this->set_pref('lastoid', 0);
//		
//	}
//	function set_pref( $pref, $value )
//	{
//	        $sql = "REPLACE " . TB_PREF . $this->import_prefs_tablename . " (name, value) VALUES (".db_escape($pref).", ".db_escape($value).")";
//    		db_query($sql, "can't update ". $pref);
//	}
//	/*string*/ function get_pref( $pref )
//	{
//        	$pref = db_escape($pref);
//
//    		$sql = "SELECT * FROM " . TB_PREF . $this->import_prefs_tablename . " WHERE name = $pref";
//    		$result = db_query($sql, "could not get vtiger pref ".$pref);
//
//    		if (!db_num_rows($result))
//        		return null;
//        	$row = db_fetch_row($result);
//    		return $row[1];
//	}
//}

class fa_cust extends db_base
{
	var $min_cid;
	var $max_cid;
	var $name;
	var $BranchName;
	var $Contact;
	var $address;
	var $tax_id;		//''
	var $curr_code;
	var $sales_type;
	var $payment_terms;	//5
	var $credit_status;	//1
	var $debtor_number;
	var $BranchCode;
	var $BranchRef;
	var $debtor_ref;
	var $debtor_id;
	var $cust_ref;
	var $id;
	var $area_code;
	var $phone;
	var $phone2;
	var $fax;
	var $email;
	var $salesman;
	var $contact;
	var $default_location;
	var $tax_group_id;	//3
	var $sales_account;
	var $sales_discount_account;
	var $receivables_account;
	var $payment_discount_account;
	var $payment_discount;
	var $PaymentTerms;
	var $creditlimit;
	var $TaxNumber;
	var $TaxExempt;
	var $Pricing;
	var $NeedPO;
	var $IsMB;
	var $dimension1;
	var $dimentsion2;
	var $selected_branch;
	var $ship_via;
	var $fulfill_from_loc;
	var $crm_person;
	var $errors = array();
	var $warnings = array();
	
	
	function __construct()
	{
        	$this->min_cid = 0;
        	$this->max_cid = 0;
		//parent::__construct( $host, $user, $pass, $database, NULL );
		parent::__construct( "fa_cust_prefs" );
		//These should now be pref variables...
		$this->set_var( "payment_terms", 5 );
		$this->set_var( "credit_status", 1 );
		$this->set_var( "tax_group_id", 3 );
		$this->set_var( "tax_id", "" );
	}
	function validate()
	{
		if( !isset( $this->ship_via ) ) 
		{
			$this->warnings[] = "Ship Via Not Set";
		}

		if( !isset( $this->fulfill_from_location ) ) 
		{
			$this->warnings[] = "Fulfill from  Not Set";
		}

		if( !isset( $this->tax_group_id ) ) 
		{
			$this->warnings[] = "Tax Group Not Set";
		}

		if( !isset( $this->area_code ) ) 
		{
			$this->warnings[] = "Area Code Not Set";
		}

		if( !isset( $this->salesman ) ) 
		{
			$this->warnings[] = "Salesman Not Set";
		}

		if( !isset( $this->payment_terms ) ) 
		{
			$this->errors[] = "Payment Terms Not Set";
		}

		if( !isset( $this->sales_type ) ) 
		{
			$this->warnings[] = "Sales Type Not Set";
		}

		if( !isset( $this->creditlimit ) ) 
		{
			$this->warnings[] = "Credit Limit Not Set";
		}

		if( !isset( $this->credit_status ) ) 
		{
			$this->warnings[] = "Credit Status Not Set";
		}

		if( !isset( $this->dimension2 ) ) 
		{
			$this->warnings[] = "Dimension2 Not Set";
		}

		if( !isset( $this->dimension1 ) ) 
		{
			$this->warnings[] = "Dimension1 Not Set";
		}

		if( !isset( $this->curr_code ) ) 
		{
			$this->errors[] = "Curr_code Not Set";
		}

		if( !isset( $this->tax_id ) ) 
		{
			$this->errors[] = "Tax_id Not Set";
		}
/*
		if( !isset( $this->debtor_id ) ) 
		{
			$this->errors[] = "Debtor_id Not Set";
		}
*/
/*
		if( !isset( $this->crm_person ) ) 
		{
			$this->errors[] = "CRM_Person Not Set";
		}
*/
/*
		if( !isset( $this->selected_branch ) ) 
		{
			$this->errors[] = "Branch Not Set";
		}
*/
		if( !isset( $this->email ) ) 
		{
			$this->errors[] = "Email Not Set";
		}

		if( !isset( $this->fax ) ) 
		{
			$this->warnings[] = "Fax Not Set";
		}

		if( !isset( $this->phone2 ) ) 
		{
			$this->warnings[] = "Phone2 Not Set";
		}

		if( !isset( $this->phone ) ) 
		{
			$this->errors[] = "Phone Not Set";
		}

		if( !isset( $this->address ) ) 
		{
			$this->errors[] = "Address Not Set";
		}

		if( !isset( $this->cust_ref ) ) 
		{
			$this->warnings[] = "Cust_ref Not Set";
		}

		if( !isset( $this->name ) ) 
		{
			$this->errors[] = "Name Not Set";
		}

		if( count( $this->errors ) > 0 )
			return FALSE;
		else
			return TRUE;
	}
	function insert_customer()
	{
  		$sql = "SELECT debtor_no FROM ".TB_PREF."debtors_master WHERE name=" . db_escape($this->name);
                $result = db_query($sql,"customer could not be retrieved meaning it needs to be inserted.");
                $row = db_fetch_assoc($result);

                if (!$row) {
                        $result = $this->insert_all();
                        if( $result )
                        {
				return 1;	//insert succeeded
                        }
                        else
                        {
				return -1;	//insert failed
                        }
                } else {
                    return 0;       //customer ignored (duplicate)
                }

	}
	function insert_all()
	{
		/********************************************************
		 *
		 *	FA does the following:
		 *		data validation
		 *		add_customer
		 *		add_branch
		 *		add_crm_person
		 *		add_crm_contact (cust_branch)
		 *		add_crm_contact (customer)
		 *
		 ********************************************************/
		if( $this->validate() )
		{
			$this->insert_debtor();
			$this->insert_branch();
			$this->insert_crm_persons_person();
			return TRUE;
		}
		else
		{
			display_notification("Error: " . $this->errors[0]);
			return FALSE;
		}
	}
	function insert_crm_persons_person()
	{
		//Each individual customer will have 1 branch (themselves)
		//Each multi-branch customer has 1 branch (HQ) plus 1 branch for each branch entity

//add_crm_person($ref, $name, $name2, $address, $phone, $phone2, $fax, $email, $lang, $notes, $cat_ids=null, $entity=null)
				//To match the function definition :(
                        //add_crm_person($this->cust_ref, $this->name, '', $this->address,
				//To match the shipped code...
                        add_crm_person($this->name, $this->cust_ref, '', $this->address,
                                $this->phone, $this->phone2, $this->fax, $this->email, '', '');
                        $this->crm_person = db_insert_id();
//add_crm_contact($type, $action, $entity_id, $person_id)
                        add_crm_contact('cust_branch', 'general', $this->selected_branch, $this->crm_person);
                        add_crm_contact('customer', 'general', $this->debtor_id, $this->crm_person);
	}
	function insert_debtor()
	{
		//Individuals have 1 debtor
		//Multi-branch customers have the HQ as the debtor
/*
 *$sql = "INSERT INTO ".TB_PREF."debtors_master (name, debtor_ref, address, tax_id,
 *                dimension_id, dimension2_id, curr_code, credit_status, payment_terms, discount,
 *                pymt_discount,credit_limit, sales_type, notes) VALUES ("
 *                .db_escape($CustName) .", " .db_escape($cust_ref) .", "
 *                .db_escape($address) . ", " . db_escape($tax_id) . ","
 *                .db_escape($dimension_id) . ", "
 *                .db_escape($dimension2_id) . ", ".db_escape($curr_code) . ",
 *                " . db_escape($credit_status) . ", ".db_escape($payment_terms) . ", " . $discount . ",
 *                " . $pymt_discount . ", " . $credit_limit
 *                 .", ".db_escape($sales_type).", ".db_escape($notes) . ")";
 *
 */

  		add_customer( $this->name, $this->cust_ref, $this->address,
                        $this->tax_id, $this->curr_code, $this->dimension1, $this->dimension2,
                        $this->credit_status, $this->payment_terms, 0, $this->payment_discount,
                        $this->credit_limit, $this->sales_type, $this->name);
                $this->debtor_id =  db_insert_id();
	}
	function insert_branch()
	{
		//Each Customer (Individual and Multi Branch Headquarters) will have one branch record with debtor_no / debtor_ref / CustName data
    		//Each branch of the Multi Branch customers will have one branch record with branch_code / branch_ref / BranchName data 

/*
 *       $sql = "INSERT INTO ".TB_PREF."cust_branch (debtor_no, br_name, branch_ref, br_address,
 *                salesman, area, tax_group_id, sales_account, receivables_account, payment_discount_account,
 *                sales_discount_account, default_location,
 *                br_post_address, disable_trans, group_no, default_ship_via, notes)
 *                VALUES (".db_escape($customer_id). ",".db_escape($br_name) . ", "
 *                        .db_escape($br_ref) . ", "
 *                        .db_escape($br_address) . ", ".db_escape($salesman) . ", "
 *                        .db_escape($area) . ","
 *                        .db_escape($tax_group_id) . ", "
 *                        .db_escape($sales_account) . ", "
 *                        .db_escape($receivables_account) . ", "
 *                        .db_escape($payment_discount_account) . ", "
 *                        .db_escape($sales_discount_account) . ", "
 *                        .db_escape($default_location) . ", "
 *                        .db_escape($br_post_address) . ","
 *                        .db_escape($disable_trans) . ", "
 *                        .db_escape($group_no) . ", "
 *                        .db_escape($default_ship_via). ", "
 *                        .db_escape($notes).")";
 *
 */

                add_branch($this->debtor_id, $this->name, $this->cust_ref,
                	$this->address, $this->salesman, $this->area_code, $this->tax_group_id, '',
                	get_company_pref('default_sales_discount_act'), get_company_pref('debtors_act'), get_company_pref('default_prompt_payment_act'),
                	$this->fulfill_from_location, $this->address, 0, 0, $this->ship_via, $this->name );

                $this->selected_branch = db_insert_id();
	}
	function insert_contacts()
	{
		/******************************************************************
    		 *	Each Customer (Individual and Multi Branch Headquarters) will have 
		 *	 one customer and one cust_branch contact record for each imported into the debtors_master table
    		 *	Each Multi Branch Customer's non HQ branch will have one cust_branch contact record
    		 *	In the FA Web UI, the individual contacts can be edited for their Contact Names or alter 
		 *	 the earlier (persons) sql to directly put in the Contact into the contact_name field in the #_crm_persons table 
		 ******************************************************************/
	}
	function update_debtor()
	{
/*
                    $sql = "UPDATE ".TB_PREF."debtors_master SET address=$this->address, tax_id='', address=$this->address,
                            curr_code='$this->curr_code',
                            sales_type=$this->sales_type,
                            payment_terms= $this->payment_terms
                            WHERE name=$this->name";
                    if ($debug_sql) display_notification("UPDATE DM " . $sql);
                    db_query($sql, "The customer could not be updated");
*/
	}
	function create_new_customer()
	{
/*
 *	This is from sales/manage/customers.php
 *	function handle_submit($selected_id) where
 *	selected_id is new.

                add_customer($_POST['CustName'], $_POST['cust_ref'], $_POST['address'],
                        $_POST['tax_id'], $_POST['curr_code'], $_POST['dimension_id'], $_POST['dimension2_id'],
                        $_POST['credit_status'], $_POST['payment_terms'], input_num('discount') / 100, input_num('pymt_discount') / 100,
                        input_num('credit_limit'), $_POST['sales_type'], $_POST['notes']);

                $selected_id = $_POST['customer_id'] = db_insert_id();

                if (isset($auto_create_branch) && $auto_create_branch == 1)
                {
			//selected_id == $this->debtor_id
                add_branch($selected_id, $_POST['CustName'], $_POST['cust_ref'],
                $_POST['address'], $_POST['salesman'], $_POST['area'], $_POST['tax_group_id'], '',
                get_company_pref('default_sales_discount_act'), get_company_pref('debtors_act'), get_company_pref('default_prompt_payment_act'),
                $_POST['location'], $_POST['address'], 0, 0, $_POST['ship_via'], $_POST['notes']);

                $selected_branch = db_insert_id();

                        add_crm_person($_POST['CustName'], $_POST['cust_ref'], '', $_POST['address'],
                                $_POST['phone'], $_POST['phone2'], $_POST['fax'], $_POST['email'], '', '');

                        $pers_id = db_insert_id();
                        add_crm_contact('cust_branch', 'general', $selected_branch, $pers_id);

                        add_crm_contact('customer', 'general', $selected_id, $pers_id);
                }
*/
	}
}
?>
