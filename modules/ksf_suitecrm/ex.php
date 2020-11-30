<?php



$options = array(
        "location" => 'https://mickey.ksfraser.com/ksfii/suitecrm/service/v4_1/soap.php',
        "uri" => 'https://mickey.ksfraser.com/ksfii/suitecrm/service/v4_1/soap.php',
        "trace" => 1
        );
// connect to soap server
$client = new SoapClient(NULL, $options);

/*
// look what modules sugar exposes
$response = $client->get_available_modules($session_id);

// look in more detail at the fields in the Accounts module
$response = $client->get_module_fields($session_id, 'Accounts');

// look for a particular account name and then get its ID
$response = $client->get_entry_list($session_id, 'Accounts', 'name like "%LornaJane%"');
$account_id = $response->entry_list[0]->id;
*/

$soapLogin = $client->login( array(
			'user_name' => "admin",
			'password' => md5( "m1l1ce" ) ),
			"test",
			array() );
$session_id  = $soapLogin->id;

// create a new account record and grab its ID
$response = $client->set_entry($session_id, 'Accounts', array(
            array("name" => 'name', "value" => 'New Company')
            ));
$account_id = $response->id;
var_dump( $response );

// create a new contact record, assigned to this account, and grab the contact ID
$response = $client->set_entry($session_id, 'Contacts', array(
            array("name" => 'first_name',"value" => 'Geek'),
            array("name" => 'last_name',"value" => 'Smith'),
            array("name" => 'email1',"value" => 'a@b.com'),
            array("name" => 'account_id',"value" => $account_id)
            ));
$contact_id = $response->id;
var_dump( $response );
