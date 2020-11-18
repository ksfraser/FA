<?php

//Straight from the documentation.  TESTED WORKING

//https://docs.suitecrm.com/developer/api/api-4_1/
//
//Create a new SoapClient
$wsdlURL = "https://mickey.ksfraser.com/ksfii/suitecrm/service/v4_1/soap.php?wsdl";
$client = new SoapClient($wsdlURL);

//Login to the API and get the session id
$userAuth = array(
        'user_name' => 'admin',
        'password' => md5('m1l1ce'),
);
$appName = 'My SuiteCRM SOAP Client';
$nameValueList = array();
$loginResults = $client->login($userAuth, $appName, $nameValueList);

//Get a list of at most 10 accounts with a billing address in Ohio. Along with
//The first and last names of any contacts in that Account.
$results = $client->get_entry_list(
        //Session id - retrieved from login call
        $loginResults->id,
        //Module to get_entry_list for
        'Accounts',
        //Filter query - Added to the SQL where clause
	//"accounts.billing_address_city = 'Airdrie'",
	"",
        //Order by - unused
        '',
        //Start with the first record

        //Return the id and name fields
        array('id','name'),
        //Link to the "contacts" relationship and retrieve the
        //First and last names.
        array(
                array(
                        'name' => 'contacts',
                        'value' => array(
                                'first_name',
                                'last_name',
                        ),
                ),
        ),
        //Show 10 max results

        //Do not show deleted

);
print_r($results);

