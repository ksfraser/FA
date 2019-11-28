<?php

require_once( 'class.suitecrm_product.php' );

//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/

class suitecrm_quote_product extends suitecrm_product
{
	protected $id;
	protected $name;
	protected $quote_id;
	protected $status;

    function __construct( $url, $username, $password )
    {
	    parent::__construct( $url, $username, $password, "Products" );
		$this->obj_var[] = "product_url";
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
/*
$cl = new suitecrm_quote_product("http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php", "admin", "m1l1ce" );
$cl->set( "name", "Test Quote Product via Rest 4" );
$cl->set( "status", "Quotes" );
$cl->set( "quote_id", "9" );	//Need this from a created quote!
$cl->set( "product_url", "http://fhsws001/boo" );
$cl->login();
$cl->create();
echo "Returned ID is " . $cl->get( "id" );
*/

