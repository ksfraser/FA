<?php

require_once( 'class.suitecrm.php' );

//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/

class suitecrm_users_cstm extends suitecrm
{
	var $id;

    function __construct( $url, $username, $password )
    {
	    parent::__construct( $url, $username, $password, "Users" );
	    $this->modname = "users_cstm";
	    $this->phone_fields_array = array( "asterisk_ext_c" );
    }
    function update()
    {
		$this->name_value_list = $this->objectvars2array();
	    	$this->name_value_list[] = array("name" => "id", "value" => $this->id );
		return parent::update(); 
    }

    /************************************************//**
     * Find a SuiteCRM user by phone number
     *
     * Inspired by asteriskLogger (YAII) from SuiteCRM
     *
     * THIS FUNCTION IS SIMILAR TO BUT NOT IDENTICAL TO OTHER MODULES
     * SINCE IT USES A CUSTOM VAR
     *
     * @param number Phone Number to search for
     *
     * **************************************************/
    function findUserByExtension( $number )
    {
	global $soapSessionId;
	print("# +++ findSugarObjectByPhoneNumber($number)\n");
	//extensions in Asterisk can be non numberic
	//$searchPattern = $this->regexify($number);
	//
	$query = $this->build_query_string( $this->phone_fields_array, $searchPattern );
	$soapArgs = array(
		'session' => $soapSessionId,
		'module_name' => "Users",
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
		$nvl = new name_value_list();
		$resultDecoded = $nvl->hash_nvl($soapResult['entry_list'][0]['name_value_list']);
		// print "--- SOAP get_entry_list() ----- RESULT --------------------------------------\n";
		// var_dump($resultDecoded);
		// print "-----------------------------------------------------------------------------\n";
		return  isset($resultDecoded['id']) ? $resultDecoded['id'] : FALSE;
	}

	// Oops nothing found :-(
	return FALSE;
    }
}



/*
 * The following code creates a note within SuiteCRM.  It does not associate it to any
 * Accounts, Contacts or other records!
 * */
/*
$cl = new suitecrm_contact("http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php", "admin", "m1l1ce" );
$cl->set( "first_name", "Kevin" );
$cl->set( "last_name", "Fraser" );
$cl->set( "primary_address_city", "Airdrie" );
$cl->login();
$cl->create();
echo "Returned ID is " . $cl->get( "id" );
//$cl->set( "search_string", $cl->get( 'last_name' ) );
$cl->set( "search_string", $cl->get( 'id' ) );
$cl->set( "search_modules_array", array( 'Accounts', 'Contacts' ) );
$cl->search();

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
		$suite->primary_address_street = $crm_persons->address;
	//	$suite->primary_address_city;
	//	$suite->primary_address_postal;
	//	$suite->primary_address_state;
	//	$suite->primary_address_country;
		$suite->alt_address_street = $debtors_master->address;
	//	$suite->alt_address_city;
	//	$suite->alt_address_postal;
	//	$suite->alt_address_state;
	//	$suite->alt_address_country;
		$suite->assistant;
		$suite->assistant_phone;
		$suite->lead_source = "frontaccounting";
	//	$suite->birthdate;
	//	$suite->joomla_account_id;
	//	$suite->portal_user_type;
	}
}
