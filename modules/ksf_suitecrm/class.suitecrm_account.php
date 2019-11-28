<?php

require_once( 'class.suitecrm.php' );

//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/

class suitecrm_account extends suitecrm
{
	var $id;
	protected $name;			//!< name
	protected $date_entered;		//!< datetime
	protected $date_modified;		//!< datetime
	protected $description;			//!< text
	protected $deleted;			//!< checkbox
	protected $account_type;		//!< dropdown
	protected $industry;			//!< dropdown
	protected $annual_revenue;		//!< text
	protected $phone_fax;			//!< phone
	protected $billing_address_street;	//!< text
	protected $billing_address_city;	//!< text
	protected $billing_address_postalcode;	//!< text
	protected $billing_address_state;	//!< text
	protected $billing_address_country;	//!< text
	protected $rating;			//!< text
	protected $phone_office;		//!< phone
	protected $phone_alternate;		//!< phone
	protected $website;			//!< URL
	protected $ownership;			//!< text
	protected $employees;			//!< text
	protected $ticker_symbol;		//!< text
	protected $shipping_address_street;	//!< text
	protected $shipping_address_city;	//!< text
	protected $shipping_address_postalcode;	//!< text
	protected $shipping_address_state;	//!< text
	protected $shipping_address_country;	//!< text
	protected $email1;			//!< text
	protected $sic_code;			//!< text
	protected $jjwg_maps_address_c;		//!< text
	protected $jjwg_maps_geocode_c;		//!< text
	protected $jjwg_maps_lat_c;		//!< float
	protected $jjwg_maps_lng_c;		//!< float
	
    function __construct( $url, $username, $password )
    {
	    parent::__construct( $url, $username, $password, "Accounts" );
    }
	function create()
	{
		try {
			$this->name_value_list = array(            
				//to update a record, pass in a record id as commented below
            			//array("name" => "id", "value" => "9b170af9-3080-e22b-fbc1-4fea74def88f"),
				array( "name" => "name", "value" => (isset( $this->name ) ) ? $this->name : "" ),		//!< datetime
				array( "name" => "date_entered", "value" => (isset( $this->date_entered ) ) ? $this->date_entered : "" ),		//!< datetime
				array( "name" => "date_modified", "value" => (isset( $this->date_modified ) ) ? $this->date_modified : "" ),		//!< datetime
				array( "name" => "description", "value" => (isset( $this->description ) ) ? $this->description : "" ),			//!< text
				array( "name" => "deleted", "value" => (isset( $this->deleted ) ) ? $this->deleted : "" ),			//!< checkbox
				array( "name" => "account_type", "value" => (isset( $this->account_type ) ) ? $this->account_type : "" ),		//!< dropdown
				array( "name" => "industry", "value" => (isset( $this->industry ) ) ? $this->industry : "" ),			//!< dropdown
				array( "name" => "annual_revenue", "value" => (isset( $this->annual_revenue ) ) ? $this->annual_revenue : "" ),		//!< text
				array( "name" => "phone_fax", "value" => (isset( $this->phone_fax ) ) ? $this->phone_fax : "" ),			//!< phone
				array( "name" => "billing_address_street", "value" => (isset( $this->billing_address_street ) ) ? $this->billing_address_street : "" ),	//!< text
				array( "name" => "billing_address_city", "value" => (isset( $this->billing_address_city ) ) ? $this->billing_address_city : "" ),	//!< text
				array( "name" => "billing_address_postalcode", "value" => (isset( $this->billing_address_postalcode ) ) ? $this->billing_address_postalcode : "" ),	//!< text
				array( "name" => "billing_address_state", "value" => (isset( $this->billing_address_state ) ) ? $this->billing_address_state : "" ),	//!< text
				array( "name" => "billing_address_country", "value" => (isset( $this->billing_address_country ) ) ? $this->billing_address_country : "" ),	//!< text
				array( "name" => "rating", "value" => (isset( $this->rating ) ) ? $this->rating : "" ),			//!< text
				array( "name" => "phone_office", "value" => (isset( $this->phone_office ) ) ? $this->phone_office : "" ),		//!< phone
				array( "name" => "phone_alternate", "value" => (isset( $this->phone_alternate ) ) ? $this->phone_alternate : "" ),		//!< phone
				array( "name" => "website", "value" => (isset( $this->website ) ) ? $this->website : "" ),			//!< URL
				array( "name" => "ownership", "value" => (isset( $this->ownership ) ) ? $this->ownership : "" ),			//!< text
				array( "name" => "employees", "value" => (isset( $this->employees ) ) ? $this->employees : "" ),			//!< text
				array( "name" => "ticker_symbol", "value" => (isset( $this->ticker_symbol ) ) ? $this->ticker_symbol : "" ),		//!< text
				array( "name" => "shipping_address_street", "value" => (isset( $this->shipping_address_street ) ) ? $this->shipping_address_street : "" ),	//!< text
				array( "name" => "shipping_address_city", "value" => (isset( $this->shipping_address_city ) ) ? $this->shipping_address_city : "" ),	//!< text
				array( "name" => "shipping_address_postalcode", "value" => (isset( $this->shipping_address_postalcode ) ) ? $this->shipping_address_postalcode : "" ),	//!< text
				array( "name" => "shipping_address_state", "value" => (isset( $this->shipping_address_state ) ) ? $this->shipping_address_state : "" ),	//!< text
				array( "name" => "shipping_address_country", "value" => (isset( $this->shipping_address_country ) ) ? $this->shipping_address_country : "" ),	//!< text
				array( "name" => "email1", "value" => (isset( $this->email1 ) ) ? $this->email1 : "" ),			//!< text
				array( "name" => "sic_code", "value" => (isset( $this->sic_code ) ) ? $this->sic_code : "" ),			//!< text
				array( "name" => "jjwg_maps_address_c", "value" => (isset( $this->jjwg_maps_address_c ) ) ? $this->jjwg_maps_address_c : "" ),		//!< text
				array( "name" => "jjwg_maps_geocode_c", "value" => (isset( $this->jjwg_maps_geocode_c ) ) ? $this->jjwg_maps_geocode_c : "" ),		//!< text
				array( "name" => "jjwg_maps_lat_c", "value" => (isset( $this->jjwg_maps_lat_c ) ) ? $this->jjwg_maps_lat_c : "" ),		//!< float
				array( "name" => "jjwg_maps_lng_c", "value" => (isset( $this->jjwg_maps_lng_c ) ) ? $this->jjwg_maps_lng_c : "" ),		//!< float
        		);
			$this->id = $this->set_entry();
		} catch( Exception $e )
		{
			throw $e;
		}

	}
    function update()
    {
		$this->name_value_list = $this->objectvars2array();
	    	$this->name_value_list[] = array("name" => "id", "value" => $this->id );
		return parent::update(); 
    }
}



/*
 * The following code creates a note within SuiteCRM.  It does not associate it to any
 * Accounts, Contacts or other records!
 * */
/*
$cl = new suitecrm_account("http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php", "admin", "m1l1ce" );
$cl->set( "name", "Kevin Fraser" );
$cl->set( "billing_address_city", "Airdrie" );
$cl->set( "billing_address_street", "747 Windridge Road SW" );
try {
	$cl->login();
		
}
catch( Exception $e )
{
	throw new Exception( "This code is for testing.  Why isn't it commented out? :: " . $e->getMessage() );
}
try {
	$cl->create();
	echo "Returned ID is " . $cl->get( "id" );
	$resp = $cl->get( "response" );
	var_dump( $resp );
	
}
catch( Exception $e )
{
	throw new Exception( "This code is for testing.  Why isn't it commented out? :: " . $e->getMessage() );
}	
try {
	$cl->set( "search_string", $cl->get( 'name' ) );
	$cl->set( "search_modules_array", array( 'Accounts', 'Contacts' ) );
	$cl->search();
	
}
catch( Exception $e )
{
	throw new Exception( "This code is for testing.  Why isn't it commented out? :: " . $e->getMessage() );
}

/* END OF TEST SECTION*/
//0_crm_persons
//id
//ref
//name
//name2
//address
//phone
//phone2
//fax
//email
//lang
//notes
//inactive
//
//0_debtors_master
//debtor_no
//name
//address
//tax_id
//curr_code
//sales_type
//dimension_id
//dimension2_id
//credit_status
//payment_terms
//discount
//pymy_discout
//credit_limit
//notes
//inactive
//debtor_ref
//
//cust_branch
//branch_code
//debtor_no
//br_name
//br_address
//area
//salesman
//contact_name
//default_location
//tax_group_id
//sales_account
//sales_discount_account
//receivables_account
//payment_discount_account
//default_ship_via
//disable_trans
//br_post_address
//group_no
//notes
//inactivity
//branch_ref
//
//SUITE
/*
*/

class front2suite
{
	var $url;
	var $username;
	var $password;
	function __construct( $url, $username, $password )
	{
		$this->url = $url;
		$this->username = $username;
		$this->password = $password;
	}
	function convert( $crm_persons, $debtors_master )
	{
		$suite = new suitecrm_contact( $this->url, $this->username, $this->password );
		$suite->first_name = $crm_persons->name;
		$suite->last_name = $crm_persons->name2;
	//	$suite->id;
		$suite->date_entered;
	//	$suite->date_modified;
		$suite->description = $crm_persons->notes;
		$suite->deleted= $crm_persons->inactive;
	//	$suite->salutation;
	//	$suite->title;
	//	$suite->photo;
		$suite->department;
	//	$suite->do_not_call;
		$suite->phone_home = $crm_persons->phone;
		$suite->phone_mobile = $crm_persons->phone2;
		$suite->phone_work;
		$suite->phone_other;
		$suite->phone_fax = $crm_persons->fax;
		$suite->email1 = $crm_persons->email;
		$suite->billing_address_street = $crm_persons->address;
	//	$suite->billing_address_city;
	//	$suite->billing_address_postalcode;
	//	$suite->billing_address_state;
	//	$suite->billing_address_country;
		$suite->shipping_address_street = $debtors_master->address;
	//	$suite->shipping_address_city;
	//	$suite->shipping_address_postalcode;
	//	$suite->shipping_address_state;
	//	$suite->shipping_address_country;
		$suite->assistant;
		$suite->assistant_phone;
		$suite->lead_source = "frontaccounting";
	//	$suite->birthdate;
	//	$suite->joomla_account_id;
	//	$suite->portal_user_type;
	}
}
