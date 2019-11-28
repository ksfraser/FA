<?php

require_once( 'class.suitecrm.php' );

//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/

class suitecrm_quote extends suitecrm
{
	protected $id;
	protected $name;
	protected $date_entered;
	protected $date_modified;
	protected $description;
	protected $deleted;
	protected $approval_issue;
	protected $billing_account;
	protected $billing_contact;
i	protected $billing_address_street;	//!< text
	protected $billing_address_city;	//!< text
	protected $billing_address_postalcode;	//!< text
	protected $billing_address_state;	//!< text
	protected $billing_address_country;	//!< text

	protected $shipping_address_street;	//!< text
	protected $shipping_address_city;	//!< text
	protected $shipping_address_postalcode;	//!< text
	protected $shipping_address_state;	//!< text
	protected $shipping_address_country;	//!< text


	protected $team_count;
	protected $team_name;
	protected $date_qupte_expected_closed;
	protected $quote_type;
	protected $quote_stage;
	protected $qupte_num;
	protected $currency_id;
	protected $subtotal;
	protected $subtotal_usdollar;
	//protected $quote_url;	//url in Suite, need to convert for send/update/get
	//protected $contact;

    function __construct( $url, $username, $password )
    {
	    parent::__construct( $url, $username, $password, "Quotes" );
		$this->obj_var[] = "quote_url";
		$this->crm_var[] = "url";
    }
    function update()
    {
	    $parameters = array(
		    "session" => $this->session_id,
		    "module_name" => $this->module_name,
         	//Record attributes
		    "name_value_list" => array(
			    array("name" => "id", "value" => "9b170af9-3080-e22b-fbc1-4fea74def88f"),
			    array("name" => "name", "value" => "Test Account"),
         		),
    		);
    		$result = $this->call("set_entry", $parameters);
		/*echo "<pre>";
    		print_r($set_entry_result);
		echo "</pre>";*/    
    }
}

$cl = new suitecrm_quote("http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php", "admin", "m1l1ce" );
$cl->set( "name", "Test Product via Rest 4" );
$cl->set( "subtotal", "3.99" );
$cl->set( "cost", "2.99" );
$cl->set( "quote_url", "http://fhsws001/boo" );
$cl->login();
$cl->create();
echo "Returned ID is " . $cl->get( "id" );
/*
require_once( 'class.suitecrm_quote_product.php');
$qp = new suitecrm_quote_product("http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php", "admin", "m1l1ce" );
//Assuming if we have a valid session ID we don't need to log in again.
//Need to share the curl handle too?
$qp->set( "session_id", $cl->get( "session_id" ) );
$qp->set( "quote_id", $cl->get( "id" ) );
$qp->set( "name", "Test Quote Product via Rest 4" );
$qp->set( "status", "Quotes" );
$qp->create();
*/


