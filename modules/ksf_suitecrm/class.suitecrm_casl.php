<?php

require_once( 'class.suitecrm.php' );

//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/

class suitecrm_casl extends suitecrm
{
	var $id;
//	protected $casl_id;
	protected $name;
	protected $date_entered;
	protected $date_modified;
	protected $description;
	protected $deleted;
	protected $contact;
	protected $submittedbyemail;
	protected $publishedPublically;
	protected $submittedbyform;
	protected $givenbusinesscard;
	protected $existingrelationship;
	protected $lead;

    function __construct( $url, $username, $password )
    {
	    parent::__construct( $url, $username, $password, "CASL_Consent_Details" );
    }
	function create()
	{
		try {
			$this->name_value_list = $this->objectvars2array();
			$this->casl_id = $this->set_entry();
		} catch( Exception $e )
		{
			throw $e;
		}
		return parent::create();
	
	}

    function update()
    {
		$this->name_value_list = $this->objectvars2array();
	    	$this->name_value_list[] = array("name" => "id", "value" => $this->id );
		return parent::update(); 
    }
}

/*
 * The following code creates a casl within SuiteCRM.  It does not associate it to any
 * Accounts, Contacts or other records!
 * */
/*
$cl = new suitecrm_casl("http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php", "kevin", "Letmein1" );
$cl->set( "name", "Test Note" );
$cl->set( "description", "This is a Test Note description to ensure we can create a casl and attachment." );
$cl->set( "revision", "1" );
$cl->set( "save_filename", "class.suitecrm_casl.php" );
$cl->set( "file_upload_path", "class.suitecrm_casl.php" );
$cl->set( "debug_level", "1" );
$cl->set( "debug_level", "0" );
try
{
	$cl->login();
}
catch( Exception $e )
{
	throw new Exception( "This code is for testing.  Why isn't it commented out? :: " . $e->getMessage() );
}
try
{
	$cl->create();
	$cl->attach();
	echo "Returned ID is " . $cl->get( "id" );
	$resp = $cl->get( "response" );
	var_dump( $resp );
}
catch( Exception $e )
{
	throw new Exception( "This code is for testing.  Why isn't it commented out? :: " . $e->getMessage() );
}
 /**/
