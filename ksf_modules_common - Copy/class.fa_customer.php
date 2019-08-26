<?php

$path_to_root = "../..";
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root."/sales/inquiry/customer_inquiry.php");


class fa_crm_persons
{
	var $id;
	var $ref;
	var $name;		//first name?
	var $name2;		//last name?
	var $address;		//address 1&2, city, state, postal, country in 1
	var $phone;		//phone - NA Shipping address
	var $phone2;
	var $fax;
	var $email;		//email - NA Shipping address?
	var $lang;
	var $notes;
	var $inactive;
}

class fa_crm_contacts
{
	var $id;
	var $person_id;
	var $type;
	var $action;
	var $entity_id
}

class fa_debtors_master
{
	var $debtor_no;
	var $name;		//first + last name
	var $address;		//address 1&2, city, state, postal, country in 1
	var $tax_id;
	var $curr_code;
	var $sales_type;
	var $dimension_id;
	var $dimension2_id;
	var $credit_status;
	var $payment_terms;
	var $discount;
	var $pymt_discount;
	var $credit_limit;
	var $notes;
	var $inactive;
	var $debtor_ref;
}

/************************************************************************************************//**
 *
 * 	fa_customer is a class setup to interface to native FA customer routines.
 *
 *	Must Have set:
 *		CustName
 *		cust_ref
 *
 *	Uses the following native FA routines:
 *		add_customer
 *		add_branch
 *		add_crm_person
 *		get_company_currency
 *		update_customer
 *		get_customer
 *
 * **************************************************************************************************/
class fa_customer
{
	var $debtor_id;
	var $customer_id = '';
	var $branch_id;
	var $CustName = "";	
	var $cust_ref = "";	//Customer Short Name
	var $tax_id = "";	//GST No
	var $phone = "";
	var $phone2 = "";
	var $fax = "";	
	var $email = "";
	var $discount;
	var $pymt_discount = "";
	var $credit_limit = "1000";
	var $curr_code = "CAD";		//Customer Currency
	var $sales_type = "1";		//3-Band.  1-Retail, 4-wholesale	//PRICE LIST
	var $salesman = "2";		//Kevin
	var $payment_terms = "4";	//Cash
	var $credit_status = "1";	//Good
	var $dimension_id = "20";	//General Interest
	var $dimension2_id = "4";	//Individual
	var $location = "KSF";
	var $ship_via = "2";		//Canada Post.  1 - Instore.
	var $area = "2";		//CANADA
	var $sales_area;		//country name
	var $tax_group = "GST";		//GST
	var $tax_group_id = "1";	//GST
	var $country_code;
	var $sales_account;
	var $sales_discount_account;
	var $receivables_account;
	var $payment_discount_account;
	var $inactive = 0;

	var $fieldlist = array(
		'popup', '_focus', '_modified', '_token', 'customer_id', 'CustName',
		'cust_ref', 'tax_id', 'phone', 'phone2', 'fax', 'email',
		'discount', 'pymt_discount', 'credit_limit', 'curr_code', 'sales_type', 'salesman',
		'payment_terms', 'credit_status', 'dimension_id', 'dimension2_id', 'location', 'ship_via',
		'area',	'tax_group_id', 'submit',
	);
	/*AntErpFA fields (for contact) */
	var $first_name = "SetMe";
	var $last_name = "SetMe";
	var $street;
	var $city;
	var $postal_code;
	var $state;
	var $address = "SetMe";
	var $notes = "";

	function __construct()
	{
		
	}
	/*@bool@*/function validate_data()
	{
		if (strlen( $this->CustName ) < 1 )
			return FALSE;
		if (strlen( $this->cust_ref ) < 1 )
			return FALSE;
		if( !isset( $this->credit_limit ) )
			$this->credit_limit = price_format($SysPrefs->default_credit_limit());
		if( !$this->is_num( $this->credit_limit ) )
		{
			$this->credit_limit = 0;
		}
		if( !$this->is_num( $this->pymt_discount, 0, 100 ) )
		{
			$this->pymt_discount = 0;
		}
		if( !$this->is_num( $this->discount, 0, 100 ) )
		{
			$this->discount = 0;
		}
		if( !isset( $this->curr_code ) )
			$this->curr_code =  get_company_currency();

		return TRUE;
	}
	/*@bool@*/function is_num( $variable, $min = null, $max = null )
	{
		if( !isset( $variable )
			return FALSE;
		if( !is_numeric( $variable )
			return FALSE;
		if( isset( $min ) )
			if( $variable < $min )
				return FALSE;
		if( isset( $max ) )
			if( $variable > $max )
				return FALSE;
	}	
	/*****************************************************************//**
	 *	update_customer takes data for an existing customer and updates them.
	 *
	 * @return bool
	 *
	 * *******************************************************************/
	/*@bool@*/function update_customer()
	{
		if( !$this->validate_data() )
			return FALSE;
		update_customer($this->customer_id,		
				$this->CustName,
				$this->cust_ref,
				$this->address,
				$this->tax_id,
				$this->curr_code,
				$this->dimension_id,
				$this->dimension2_id,
				$this->credit_status,
				$this->payment_terms,
				$this->discount/100,
				$this->pymt_discount/100,
				$this->credit_limit,
				$this->sales_type,
				$this->notes
		);

		update_record_status($this->customer_id, $this->inactive,
			'debtors_master', 'debtor_no');
		return TRUE;

	}
	/*****************************************************************//**
	 *	add_new_customer takes data for a new customer and inserts them.
	 *
	 *	add_new_customer takes data for a new customer and inserts them.
	 *		Adds the customer
	 *		Adds the default branch
	 *		adds CRM Contact data
	 *	uses validate_data to enforce that a minimal set of data is present
	 *	and ensure that numbers are reasonable.
	 *	
	 * @return bool false indicates bad input data.  True indicates running 
	 * 						through the subroutines
	 *
	 * *******************************************************************/
	/*@bool@*/function add_new_customer()
	{
		//basically cloned from handle_submit within sales/manage/customers.php
		if( !$this->validate_data() )
			return FALSE;
		$this->add_customer();
		$this->add_branch();
		$this->add_crm_person();
		$this->add_crm_contact( 'cust_branch', 'general', $this->branch_id );
		$this->add_crm_contact( 'customer', 'general', $this->customer_id );
		return TRUE;
	}
	private function add_customer()
	{
		add_customer(
				$this->CustName,
				$this->cust_ref,
				$this->address,
				$this->tax_id,
				$this->curr_code,
				$this->dimension_id,
				$this->dimension2_id,
				$this->credit_status,
				$this->payment_terms,
				$this->discount /100,
				$this->pymt_discount /100,
				$this->credit_limit,
				$this->sales_type,
				$this->notes
		);
 		$this->customer_id = $selected_id = $_POST['customer_id'] = db_insert_id();
	}
	private function add_branch()
	{
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
                       	$this->ship_via,
                       	$this->notes
                );
		$this->branch_id = $selected_branch = db_insert_id();
	}
	private function add_crm_person()
	{
		add_crm_person($this->CustName, $this->cust_ref, '', $this->address, $this->phone, $this->phone2, $this->fax, $this->email, '', $this->notes);
		$this->person_id = db_insert_id();
	}
	private function add_crm_contact( $type, $action, $id )
	{
                add_crm_contact( $type, $action, $id, $this->person_id);
	}
	/*****************************************************************//**
	 *	get_customer uses the FA routine of the same name to look up a customer
	 *	id and return the associated data.
	 *
	 *
	 * @return null
	 *
	 * *******************************************************************/
	function get_customer()
	{
		$row = get_customer( $this->customer_id );
		$this->CustName = $row["name"];
		$this->cust_ref = $row["debtor_ref"];
		$this->address  = $row["address"];
		$this->tax_id  = $row["tax_id"];
		$this->dimension_id  = $row["dimension_id"];
		$this->dimension2_id  = $row["dimension2_id"];
		$this->sales_type = $row["sales_type"];
		$this->curr_code  = $row["curr_code"];
		$this->credit_status  = $row["credit_status"];
		$this->payment_terms  = $row["payment_terms"];
		$this->discount  = percent_format($row["discount"] * 100);
		$this->pymt_discount  = percent_format($row["pymt_discount"] * 100);
		$this->credit_limit	= price_format($row["credit_limit"]);
		$this->notes  = $row["notes"];
		$this->inactive = $row["inactive"];
		return;
	}
}

?>
