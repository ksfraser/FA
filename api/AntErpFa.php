<?php
/*******************************************************************************
 * Copyright(c) @2011 ANTERP SOLUTIONS. All rights reserved.
 *
 * Released under the terms of the GNU General Public License, GPL, 
 * as published by the Free Software Foundation, either version 3 
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 *
 * Authors		    tclim
 * Date Created     Mar 16, 2011 1:05:21 PM
 ******************************************************************************/
 
class AntErpFa {

	/* Establish database connection 
	 * $company			- 	FrontAccounting Company Index Id
	 */
	function DBConnection($company) {
		global $db, $db_connections, $tb_pref_counter;
		
		//Reinterate and get the company Index Id
		for ($i=0; $i<$tb_pref_counter; $i++) {
									
			if ($company == $db_connections[$i]["name"]) {
				$company = $i;
				break;
			}
		}

		if (!defined('TB_PREF')) {
			define('TB_PREF', $db_connections[$company]["tbpref"]);
		}
		
		$db = mysql_connect($db_connections[$company]["host"], $db_connections[$company]["dbuser"], $db_connections[$company]["dbpassword"]);
		mysql_select_db($db_connections[$company]["dbname"], $db);

        return $db;
	}

	/* This method is to Retrieve the Debtors Master Information
	 * $company			- 	FrontAccounting Company Index Id
	 * $user_id			-	User ID
	 * $password 		-	Password
	 * $pageIndex		-	Page Index
	 * $totalRec		-	Total record per page
	 */
	function getDebtorsMaster($company, $user_id, $password, $filter_col, $filter_type, $filter_string, $orderBy, $pageIndex, $totalRec) {
		global $db, $db_connections;
		$loginOk = false;

		if (trim($filter_string) != '') {
			$filter = " AND " . $filter_col . " ";
			if( $filter_type == "LIKE" )
			{
				$filter .= "LIKE '%" . $filter_string . "%'";
			}
			else
			{
				$filter .= "='" . $filter_string . "'";
			}
		}

		if (trim($orderBy) != '') {
			$orderBy = " ORDER BY " . $orderBy;
		}

		$resultArray = array ();

		if ($pageIndex == null) {
			$pageIndex = 0;
		}
		if ($totalRec == null) {
			$totalRec = 10;
		}

		$this->DBConnection($company);

		//Authenticate with OpenLDAP Server
		//Set the LDAP_AUTH to 1 for Ldap Authentication, Set 0 for normal login process
		if (_LDAP_AUTH) {
			$loginOk = $this->ldapAuthentication($company, $user_id, $password);
		} else { //Normal Login Process
			$sql = "SELECT id FROM ".TB_PREF."users WHERE user_id = ".$this->db_escape($user_id)." AND" ." password=".$this->db_escape(md5($password));
			$result = mysql_query($sql, $db);
			$num_rows = mysql_num_rows($result);

			if ($num_rows == 0) {
				$loginOk = false;
			} else {
				$loginOk = true;
			}
		}
		
		if ($loginOk) {
			$sql = "SELECT debtor_no, name, debtor_ref, address, tax_id, curr_code, payment_terms, discount, pymt_discount, credit_limit, notes FROM " . TB_PREF . "debtors_master WHERE inactive = 0 " . $filter . " " . $orderBy . " LIMIT " . $pageIndex . "," . $totalRec;
			$sql = "SELECT * FROM " . TB_PREF . "debtors_master WHERE inactive = 0 " . $filter . " " . $orderBy . " LIMIT " . $pageIndex . "," . $totalRec;
				$result = mysql_query($sql) or die(__LINE__ . "Could not select out of " . TB_PREF . "debtors_master with query " . $sql);

				if (!$result) {
					die(__LINE__ . "Could not connect database");
				}
				$cnt = 0;

				while ($objResult = mysql_fetch_assoc($result)) {

					// Result Array
					$resultArray[$cnt] = array (
						'debtor_no' => $objResult["debtor_no"],
						'name' => $objResult["name"],
						'debtor_ref' => $objResult["debtor_ref"],
						'address' => $objResult["address"],
						'tax_id' => $objResult["tax_id"],
						'currency_code' => $objResult["curr_code"],
						'payment_terms' => $objResult["payment_terms"],
						'discount' => $objResult["discount"],
						'pymt_discount' => $objResult["pymt_discount"],
						'credit_limit' => $objResult["credit_limit"],
						'notes' => $objResult["notes"]
					);

					$cnt = $cnt +1;
				}
		} else {
			throw new Exception("Invalid User ID or Password!!!");
		}

		return $resultArray;
	}

	/* To Create/Update Debtors Master Account including Contact,Person, Branch
	 * $company			- 	FrontAccounting Company Index Id
	 * $company_name	-	Customer Company Name
	 * $user_id			-	User ID
	 * $password 		-	Password
	 * $id				-	debtor_no
	 * $first_name		-	First Name
	 * $last_name		-	Last Name
	 * $short_name		-	Short Name
	 * $addr			-	Addresses
	 * $email			-	Email Address (abc@yahoo.com)
	 * $phone			-	Phone No
	 * $mobile			-	Mobile No
	 * $fax				-	Fax No
	 * $tax_group		-	Tax Group Description (TAX, GST Tax, Tax Tax Exempt)
	 * $tax_id			-	Tax ID
	 * $area			-	Area (Asia, Europe)
	 * $country_cd		-	Country Code (MY, US)
	 * $currency		-	Currency Code (MYR, USD)
	 * $sales_type		-	Sales Type Description (Retail/Wholesale)
	 * $payment_terms	-	Payment Terms Description (Cash Only)
	 * $credit_status	-	Credit Status (1)
	 * $notes			-	Notes
	 * $return_id		-	debtor_no
	 * $dimension_id	-	the dimension ID from in the DB - no lookup
	 * $dimension2_id	-	the dimension ID from in the DB - no lookup
	 */
	function createDebtorsMaster($company, $user_id, $password, $id, $company_name, $short_name, $first_name, $last_name, $addr, $email, $phone, $mobile, $fax, $tax_group, $tax_id, $area, $country_cd, $currency, $sales_type, $payment_terms, $credit_status, $notes, $dimension_id = "0", $dimension2_id = "0", $sales_account = "4020", $sales_discount_account = "4510", $receivables_account = "1200", $payment_discount_account = "4500", $salesman = "1", $default_location = "DEF") {
		global $db, $db_connections;
		$loginOk = false;

		$return_id = $id;
		$area_code = -1;
		$tax_group_id = "";

		$contact_name = 'Main Branch';
		//$default_location = 'DEF';
		$br_post_address = $addr;

		//Unknown fields
		//$salesman = '1';
		$sales_group = 'Small';
/*
		$sales_account = get_company_pref('default_sales_act');
		$sales_discount_account = get_company_pref('default_sales_discount_act');
		$receivables_account = get_company_pref('debtors_act');
		$payment_discount_account = get_company_pref('default_prompt_payment_act');
*/


		$lang = 'en_US';
		$customer_contact = 'customer';
		$cust_branch = 'cust_branch';
		$general = 'general';

		$this->DBConnection($company);

		//Authenticate with OpenLDAP Server
		//Set the LDAP_AUTH to 1 for Ldap Authentication, Set 0 for normal login process
				define( '_LDAP_AUTH', '0' );
		if (_LDAP_AUTH) {
			$loginOk = $this->ldapAuthentication($company, $user_id, $password);
		} else { //Normal Login Process
			if( !defined( 'TB_PREF' ) )
				define( 'TB_PREF', '0_' );
			$sql = "SELECT id FROM ".TB_PREF."users WHERE user_id = ".$this->db_escape($user_id)." AND" ." password=".$this->db_escape(md5($password));
			$result = mysql_query($sql, $db);
			$num_rows = mysql_num_rows($result);

			if ($num_rows == 0) {
				$loginOk = false;
			} else {
				$loginOk = true;
			}			
		}
		
		if ($loginOk) {

				//This part is to select the area code
				$sql = "SELECT area_code FROM " . TB_PREF . "areas WHERE inactive = 0 AND LOWER(description)=LOWER(" . $this->db_escape($area) .")";
				$result = mysql_query($sql, $db) or die("could not get area");

				$row = mysql_fetch_row($result);
				if (!$row) { //Add new Area
					$sql = "INSERT INTO " . TB_PREF . "areas (description) VALUES (" . $this->db_escape($area) .")";
					mysql_query($sql, $db) or die("The sales area could not be added");
					$area_code = mysql_insert_id($db);
				} else {
					$area_code = $row[0];
				}

				//Query Currency
				if ($currency == "") {
					$sql = "SELECT value FROM " . TB_PREF . "sys_prefs WHERE name = 'curr_default'";
					$currency = $this->getId($sql);
				} else {
					$curr_abrev = $currency;
					$sql = "SELECT curr_abrev FROM " . TB_PREF . "currencies WHERE inactive = 0 AND curr_abrev=" . $this->db_escape($currency);
					$currency = $this->getId($sql);
					if ($currency == "") //Add New Currency		
						$sql = "INSERT INTO " . TB_PREF . "currencies (curr_abrev, curr_symbol, currency, country, hundreds_name, auto_update) VALUES
						(" . $this->db_escape($curr_abrev) . ",'','', '','','1')";
					mysql_query($sql, $db);
				}

				//Extra Query for Sales Group
				$sql = "SELECT id FROM " . TB_PREF . "groups WHERE inactive = 0 AND LOWER(description)=LOWER(" . $this->db_escape($sales_group) . ")";
				$group_no = $this->getId($sql);

				//Query the Payment Term for Cash Only
				$sql = "SELECT terms_indicator FROM " . TB_PREF . "payment_terms WHERE inactive = 0 AND LOWER(terms) = LOWER(" . $this->db_escape($payment_terms) . ")";
				$payment_terms = $this->getId($sql);

				//Query the Sales Type for Retail/Wholesales
				$sql = "SELECT id FROM " . TB_PREF . "sales_types WHERE inactive = 0 AND LOWER(sales_type) = LOWER(" . $this->db_escape($sales_type) . ")";
				$sales_type = $this->getId($sql);

				//Query Tax Group
				if ($tax_group != "") {
					$sql = "select id from " . TB_PREF . "tax_groups WHERE inactive = 0 AND name=" . $this->db_escape($tax_group);
					$tax_group_id = $this->getId($sql);
				}

				//Query Debtors Master
				if ($id != "") {
					$sql = "SELECT debtor_no FROM " . TB_PREF . "debtors_master WHERE inactive = 0 AND debtor_no=" . $this->db_escape($id);
				} else {
					$sql = "SELECT debtor_no FROM " . TB_PREF . "debtors_master WHERE inactive = 0 AND name= LOWER(" . $this->db_escape($company_name) . ")";
				}

				$result = mysql_query($sql, $db) or die("customer could not be retreived");
				$row = mysql_fetch_row($result);

				//Insert or update Debtors Master
				if (!$row) {
					try {
						//Begin Transaction
						$this->begin_transaction();
						
						//Insert Debtors Master
						$sql = "INSERT INTO " . TB_PREF . "debtors_master (name, debtor_ref, address, tax_id, curr_code, sales_type, payment_terms, credit_status, notes, dimension_id, dimension2_id)
								VALUES (" . $this->db_escape($company_name) . ", " . $this->db_escape($short_name) . ", " . $this->db_escape($addr) . ", " . $this->db_escape($tax_id) . ", " . $this->db_escape($currency) . ", " . $this->db_escape($sales_type) . ", ".  $this->db_escape($payment_terms) . ", " . $this->db_escape($credit_status) . ", " . $this->db_escape($notes) .  ", " . $this->db_escape($dimension_id) . ", " . $this->db_escape($dimension2_id) . ")";
						 
						 if (mysql_query($sql, $db)) {
						  	$return_id = mysql_insert_id($db);
						  }else {
						  	throw new Exception("ERROR while inserting debtors_master: " . mysql_error() . '\n' . $sql);
						  }						
						
						//Insert Customer Branch
						$sql = "INSERT INTO " . TB_PREF . "cust_branch (debtor_no, br_name, branch_ref, br_address, area, salesman, contact_name,
							   default_location, tax_group_id, sales_account, sales_discount_account, receivables_account, payment_discount_account,
							   br_post_address, group_no)
							   VALUES (" . $this->db_escape($return_id) . "," . $this->db_escape($company_name) . "," . $this->db_escape($short_name) . "," . $this->db_escape($addr) . "," . $this->db_escape($area_code) . "," . $this->db_escape($salesman) . "," . $this->db_escape($contact_name) . ", " . $this->db_escape($default_location) . "," . $this->db_escape($tax_group_id) . "," . $this->db_escape($sales_account) . "," . $this->db_escape($sales_discount_account) . "," . $this->db_escape($receivables_account) . ", " . $this->db_escape($payment_discount_account) . "," . $this->db_escape($br_post_address) . "," . $this->db_escape($group_no) . " )";
						
						 if (mysql_query($sql, $db)) {
						  	$cust_branch_id = mysql_insert_id($db);
						 }else {
						  	throw new Exception("ERROR while inserting cust_branch: " . mysql_error() . $sql);
						 }
						
						//CRM Contact Linkage to Debtor Master (entity_id)
						//CRM Contact Linkage to CRM Person (person_id)
						//Insert 2 records:
						//	1) cust_branch (First Person ID)
						//	2) customer (Second Person ID)
		
						//Insert First CRM Person
						$sql = "INSERT INTO " . TB_PREF . "crm_persons (ref, name, name2, address, phone, phone2, fax, email, lang)
								VALUES (" . $this->db_escape($short_name) . "," . $this->db_escape($first_name) . "," . $this->db_escape($last_name) . ", " . $this->db_escape($addr) . ", " . $this->db_escape($phone) . ", " . $this->db_escape($mobile) . ", " . $this->db_escape($fax) . " , " . $this->db_escape($email) . ", " . $this->db_escape($lang) . ")";
	
						if (mysql_query($sql, $db)) {
						  	$crm_person_id = mysql_insert_id($db);
						 }else {
						  	throw new Exception("ERROR while inserting FIRST crm_persons: " . mysql_error());
						 }
						
						//Insert First CRM Contact
						$sql = "INSERT INTO " . TB_PREF . "crm_contacts (person_id, type, action, entity_id)
								VALUES (" . $this->db_escape($crm_person_id) . "," . $this->db_escape($customer_contact) . "," . $this->db_escape($general) . "," . $this->db_escape($return_id) . ")";
	
						if (mysql_query($sql, $db)) {						  
						 }else {
						  	throw new Exception("ERROR while inserting FIRST crm_contacts: " . mysql_error());
						 }
						
						//Insert Second CRM Person
						$sqlCRM = "INSERT INTO " . TB_PREF . "crm_persons (ref, name, name2, address, phone, phone2, fax, email, lang)
								VALUES (" . $this->db_escape($short_name) . "," . $this->db_escape($first_name) . "," . $this->db_escape($last_name) . ", " . $this->db_escape($addr) . ", " . $this->db_escape($phone) . ", " . $this->db_escape($mobile) . ", " . $this->db_escape($fax) . " , " . $this->db_escape($email) . ", " . $this->db_escape($lang) . ")";
						
						if (mysql_query($sqlCRM, $db)) {
						  	$crm_second_person_id = mysql_insert_id($db);
						 }else {
						  	throw new Exception("ERROR while inserting SECOND crm_persons: " . mysql_error());
						 }
						
						//Insert Second CRM Contact
						$sql = "INSERT INTO " . TB_PREF . "crm_contacts (person_id, type, action, entity_id)
								VALUES (" . $this->db_escape($crm_second_person_id) . "," . $this->db_escape($cust_branch) . "," . $this->db_escape($general) . "," . $this->db_escape($cust_branch_id) . ")";
	
						if (mysql_query($sql, $db)) {						  
						 }else {
						  	throw new Exception("ERROR while inserting SECOND crm_contacts: " . mysql_error());
						 }
						
						//Commit All Transaction
						$this->commit_transaction();
						
					} catch (Exception $ex) {
						//Rollback All Transaction						
						$this->cancel_transaction();
						throw new Exception($ex);
					}
					
				} else {
					//Retrieve the debtor_no from Debtors Master 
					if (isset($row[0]))
						$id = $row[0];
					
						try {
							
							//Begin Transaction
							$this->begin_transaction();
							
							//Update Debtors Master					
							$sql = "UPDATE ".TB_PREF."debtors_master SET
									name=" . $this->db_escape($company_name) . ", 
									debtor_ref=" . $this->db_escape($short_name) . ",
									address=".$this->db_escape($addr) . ", 
									tax_id=".$this->db_escape($tax_id) . ", 
									curr_code=".$this->db_escape($currency) . ", 
									credit_status=".$this->db_escape($credit_status) . ", 
									payment_terms=".$this->db_escape($payment_terms) . ", 
									sales_type = ".$this->db_escape($sales_type) . ", 
									notes=".$this->db_escape($notes) ."
									WHERE debtor_no = " . $this->db_escape($id);
							
							mysql_query($sql, $db) or die("ERROR while updating debtors_master");								
							
							$return_id = $id;
						
							//Commit All Transaction
							$this->commit_transaction();
						
						} catch (Exception $ex) {
							//Rollback All Transaction						
							$this->cancel_transaction();
							throw new Exception($ex);
						}
					}
		} else {
			throw new Exception("Invalid User ID or Password!!!" . $sql);
		}

		return array ('debtor_no' => $return_id);
	}

	/* This method is to Retrieve the Debtors Master Information
	 * $company			- 	FrontAccounting Company Index Id
	 * $user_id			-	User ID
	 * $password 		-	Password
	 * $pageIndex		-	Page Index
	 * $totalRec		-	Total record per page
	 */
	function getDebtorTrans($company, $user_id, $password, $filter, $orderBy, $pageIndex, $totalRec) {
		global $db, $db_connections;
		$loginOk = false;

		if (trim($filter) != '') {
			$filter = " AND " . $filter;
		}

		if (trim($orderBy) != '') {
			$orderBy = " ORDER BY " . $orderBy;
		}

		$resultArray = array ();

		if ($pageIndex == null) {
			$pageIndex = 0;
		}
		if ($totalRec == null) {
			$totalRec = 10;
		}

		$this->DBConnection($company);

		//Authenticate with OpenLDAP Server
		//Set the LDAP_AUTH to 1 for Ldap Authentication, Set 0 for normal login process
		if (_LDAP_AUTH) {
			$loginOk = $this->ldapAuthentication($company, $user_id, $password);
		} else { //Normal Login Process
			$sql = "SELECT id FROM ".TB_PREF."users WHERE user_id = ".$this->db_escape($user_id)." AND" ." password=".$this->db_escape(md5($password));
			$result = mysql_query($sql, $db);
			$num_rows = mysql_num_rows($result);

			if ($num_rows == 0) {
				$loginOk = false;
			} else {
				$loginOk = true;
			}				
		}
		
		if ($loginOk) {

				$sql = "SELECT dt.trans_no,dt.debtor_no,dt.branch_code,dt.tran_date,dt.due_date,dt.reference,dt.order_,dt.ov_amount,dt.ov_gst,dt.ov_freight,dt.ov_freight_tax,dt.ov_discount,dt.alloc,dt.rate,dt.ship_via,dt.payment_terms FROM " . TB_PREF . "debtor_trans dt INNER JOIN " . TB_PREF . "debtors_master dm ON dt.debtor_no = dm.debtor_no " . $filter . $orderBy . " LIMIT " . $pageIndex . "," . $totalRec;

				$result = mysql_query($sql, $db) or die(__LINE__ . "Could not connect database");

				if (!$result) {
					die(__LINE__ . "Could not connect database");
				}
				$cnt = 0;

				while ($objResult = mysql_fetch_assoc($result)) {

					// Result Array
					$resultArray[$cnt] = array (
						'trans_no' => $objResult["trans_no"],
						'debtor_no' => $objResult["debtor_no"],
						'branch_code' => $objResult["branch_code"],
						'tran_date' => $objResult["tran_date"],
						'due_date' => $objResult["due_date"],
						'reference' => $objResult["reference"],
						'order_' => $objResult["order_"],
						'ov_amount' => $objResult["ov_amount"],
						'ov_gst' => $objResult["ov_gst"],
						'ov_freight' => $objResult["ov_freight"],
						'ov_freight_tax' => $objResult["ov_freight_tax"]
					);

					$cnt = $cnt +1;
				}
			} else {
				throw new Exception("Invalid User ID or Password!!!");
			}

		return $resultArray;
	}

	/*
	 * This method is to authenticate users login via OpenLDAP Server
	  * $company			- 	FrontAccounting Company Index Id
	 * $username		-	User ID
	 * $password 		-	Password
	 */
	public function ldapAuthentication($company, $user_id, $password) {
		//Added by tclim to authenticate against OpenLDAP Server
		$ldapAuth = false;
		
		//Code remove here

		//Completed Code for auth OpenLDAP

		return $ldapAuth;
	}
	
	/*
	 * To prevent SQL Injection
	 */
	 function db_escape($value) {
	 	
	 	if (is_string($value)) {
	 		
			if (function_exists('mysql_real_escape_string')) {
		  		$value = "'" . mysql_real_escape_string($value) . "'";
			} else {
			  $value = "'" . mysql_escape_string($value) . "'";
			}
		}
		
	 	return $value;
	 }
	
	/*
	 * This method is to Retrieve the Single ID from dynamic sql parameters
	 * @return	-	id 
	 */
	function getId($sql) {
		global $db;
		
		$result = mysql_query($sql, $db) or die(__LINE__ . "Could not connect database");

		$ret = mysql_fetch_array($result);

		if (isset ($ret[0]))
			return $ret[0];

		return null;
	}
	
	/*
	*Start Transaction
	*/
	function begin_transaction()
	{
		@mysql_query("BEGIN");
	}
	
	/*
	*Commit All Transaction
	*/
	function commit_transaction()
	{
		@mysql_query("COMMIT");
	}
	
	/*
	*RollBack Transaction
	*/
	function cancel_transaction()
	{
		@mysql_query("ROLLBACK");
	}
}
?>
