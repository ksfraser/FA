<?php

require_once( 'class.suitecrm.php' );

//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/


/*

array ->call
->logiN			Do we get an exception on bad credentials?
string set_entry
string set_relationshiP
string update		calls set_entry
string create		calls set_entry
string upload_file	calls call
null get_entry_list	no native throws
null search		no native throws.  Looks incomplete.
arrayfunction objectvars2array()
array get_field_list	calls call.
bool setNoteAttachment

set/geT
*/

class test_suitecrm
{
	protected $connection;
	function __construct( $url, $user, $pw, $module = "login" )
	{
		$this->connection = new suitecrm( $url, $user, $pw, $module );
	}
	function test_create_account()
	{
    		$set_entry_parameters = array(
         		"session" => $this->connection->get( 'session_id' ),
         		"module_name" => "Accounts",
         		"name_value_list" => array(
              			array("name" => "name", "value" => "Test Account"),
         		),
    		);
    		$result = $this->connection->call("set_entry", $set_entry_parameters);
    		echo "<pre>";
    		print_r($result);
    		echo "</pre>";
	}
}

$test = new test_suitecrm("http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php", "admin", "m1l1ce", "login" );
var_dump( $test );
$test->test_create_account();


/*
    //create account ------------------------------------- 
    $set_entry_parameters = array(
         //session id
         "session" => $cl->get( 'session_id' ),
         //"session" => $cl->session_id,	PROTECTED

         //The name of the module from which to retrieve records.
         "module_name" => "Accounts",

         //Record attributes
         "name_value_list" => array(
              //to update a record, you will nee to pass in a record id as commented below
              //array("name" => "id", "value" => "9b170af9-3080-e22b-fbc1-4fea74def88f"),
              array("name" => "name", "value" => "Test Account"),
         ),
    );

    $set_entry_result = $cl->call("set_entry", $set_entry_parameters);

    echo "<pre>";
    print_r($set_entry_result);
    echo "</pre>";
 */

