<?php

require_once( 'class.suitecrm.php' );

//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/

class suitecrm_account extends suitecrm
{
	//var $id;
	protected $name;			//!< name
	//protected $date_entered;		//!< datetime
	//protected $date_modified;		//!< datetime
	protected $description;			//!< text
	//protected $deleted;			//!< checkbox
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

	/**********************************************************//**
	 *
	 * @param data_array array of initialization values 
	 * *********************************************************/
    function __construct( $data_array = null )
    {
	    parent::__construct( $data_array );
    }
	function create()
	{
		try {
			$this->prepare();
			throw new Exception( "The set_entry call should be in a controller.  Not a DATA class" );
			//$this->id = $this->set_entry();
		} catch( Exception $e )
		{
			throw $e;
		}

	}
	// Finds an account by given phone number
	function findAccountByPhoneNumber($aPhoneNumber)
	{
		global $soapSessionId;
		print("# +++ findAccountByPhoneNumber($aPhoneNumber)\n");
		$searchPattern = regexify($aPhoneNumber);
		$query = $this->build_query_string( $this->phone_fields_array, $searchPattern );
		$soapArgs = array(
			'session' => $soapSessionId,
			'module_name' => 'Accounts',
			'query' => $query,
	);

		// print "--- SOAP get_entry_list() ----- ARGS ----------------------------------------\n";
		// var_dump($soapArgs);
		// print "-----------------------------------------------------------------------------\n";

		$soapResult = soapCall('get_entry_list', $soapArgs);

		//     print "--- SOAP get_entry_list() ----- RESULT --------------------------------------\n";
		//     var_dump($soapResult);
		//     print "-----------------------------------------------------------------------------\n";

		if ($soapResult['error']['number'] != 0)
		{
			echo "! Warning: SOAP error " . $soapResult['error']['number'] . " " . $soapResult['error']['string'] . "\n";
		}
		elseif (count($soapResult['entry_list']) > 0)
		{
		//		print "--- SOAP get_entry_list() ----- RESULT --------------------------------------\n";
		//		var_dump($soapResult['entry_list'][0]);
		//		print "-----------------------------------------------------------------------------\n";
		// Return just Account ID
			return $soapResult['entry_list'][0]['id'];
		}
		// Oops nothing found :-(
		return FALSE;
	}

}



/*
 * The following code creates an account within SuiteCRM.  It does not associate it to any
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


