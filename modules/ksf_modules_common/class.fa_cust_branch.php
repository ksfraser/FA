<?php

require_once( 'class.table_interface.php' );

$path_to_root = "../..";
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root."/sales/inquiry/customer_inquiry.php");

require_once( $path_to_root . '/sales/includes/db/customers_db.inc' ); //add_customer
require_once( $path_to_root . '/sales/includes/db/branches_db.inc' ); //add_branch
require_once( $path_to_root . '/includes/db/crm_contacts_db.inc' ); //add_crm_*
require_once( $path_to_root . '/includes/db/connect_db.inc' ); //db_query, ...
require_once( $path_to_root . '/includes/errors.inc' ); //check_db_error, ...


class fa_cust_branch extends table_interface
{
	/************************************************
	 * This is the FA customer branch table
	 * *********************************************/
	/*
| branch_code              | int(11)     | NO   | PRI | NULL    | auto_increment |
| debtor_no                | int(11)     | NO   | PRI | 0       |                |
| br_name                  | varchar(60) | NO   |     |         |                |
| br_address               | tinytext    | NO   |     | NULL    |                |
| area                     | int(11)     | YES  |     | NULL    |                |
| salesman                 | int(11)     | NO   |     | 0       |                |
| contact_name             | varchar(60) | NO   |     |         |                |
| default_location         | varchar(5)  | NO   |     |         |                |
| tax_group_id             | int(11)     | YES  |     | NULL    |                |
| sales_account            | varchar(15) | NO   |     |         |                |
| sales_discount_account   | varchar(15) | NO   |     |         |                |
| receivables_account      | varchar(15) | NO   |     |         |                |
| payment_discount_account | varchar(15) | NO   |     |         |                |
| default_ship_via         | int(11)     | NO   |     | 1       |                |
| disable_trans            | tinyint(4)  | NO   |     | 0       |                |
| br_post_address          | tinytext    | NO   |     | NULL    |                |
| group_no                 | int(11)     | NO   | MUL | 0       |                |
| notes                    | tinytext    | YES  |     | NULL    |                |
| inactive                 | tinyint(1)  | NO   |     | 0       |                |
| branch_ref               | varchar(30) | NO   | MUL | NULL    |                |
	 */
	protected $branch_code;
	protected $debtor_no;		//<@ Also in DEBTORS_MASTER
	protected $br_name;
	protected $br_address;
	protected $area;
	protected $salesman;
	protected $contact_name;
	protected $default_location;
	protected $tax_group_id;
	protected $sales_account;
	protected $sales_discount_account;
	protected $receivables_account;
	protected $payment_discount_account;
	protected $default_ship_via;
	protected $disable_trans;
	protected $br_post_address;
	protected $group_no;
	protected $notes;	//<@ Also in CRM_PERSON, debtors_master
	protected $inactivity;
	protected $branch_ref;
	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . get_class( $this );
		$this->table_details['primarykey'] = "branch_code";

		$this->fields_array[] = array( 'name' => 'branch_code', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'debtor_no', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in DEBTORS_MASTER
		$this->fields_array[] = array( 'name' => 'br_name', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'br_address', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'area', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'salesman', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'contact_name', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'default_location', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'tax_group_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'sales_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'sales_discount_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'receivables_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'payment_discount_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'default_ship_via', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'disable_trans', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'br_post_address', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'group_no', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'notes', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in CRM_PERSON, debtors_master
		$this->fields_array[] = array( 'name' => 'inactivity', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'branch_ref', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
	}
	/*
	private function add_branch()
	{
		//Each Customer (Individual and Multi Branch Headquarters) will have one branch record with debtor_no / debtor_ref / CustName data
    		//Each branch of the Multi Branch customers will have one branch record with branch_code / branch_ref / br_name data 
                
		add_branch(
                       	$this->customer_id,
			$this->CustName,
			$this->cust_ref,
			$this->address,
			$this->salesman,
			$this->area,
			$this->tax_group_id,
			'',
                       	get_company_pref('default_sales_discount_act'),
                       	get_company_pref('debtors_act'),
                       	get_company_pref('default_prompt_payment_act'),
                       	$this->location,
                       	$this->address,
        	        0,
                        0,
                       	$this->default_ship_via,
                       	$this->notes
                );
		$this->branch_id = $selected_branch = db_insert_id();
	}
	 */

}

