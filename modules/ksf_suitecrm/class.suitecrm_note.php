<?php

require_once( 'class.suitecrm.php' );

//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/

class suitecrm_note extends suitecrm
{
	var $id;
	protected $note_id;
	protected $name;
	protected $date_entered;
	protected $date_modified;
	protected $file_mime_type;
	protected $parent_type;
	protected $portal_flag;
	protected $embed_flag;
	protected $description;
	protected $deleted;

    function __construct( $url, $username, $password )
    {
	    parent::__construct( $url, $username, $password, "Notes" );
    }
	function create()
	{
		try {
			/*
			$this->name_value_list = array(            
				//to update a record, pass in a record id as commented below
            			//array("name" => "id", "value" => "9b170af9-3080-e22b-fbc1-4fea74def88f"),
            			array("name" => "name", "value" => $this->name ),
            			array("name" => "revision", "value" => $this->revision ),
			);
			 */
			$this->name_value_list = $this->objectvars2array();
			$this->note_id = $this->set_entry();
		} catch( Exception $e )
		{
			throw $e;
		}
	
	}
	/**************************************//**
	 * Attach a document to the note
	 *
	 * Assumption that CREATE has already been run
	 * so that note_ID is set.
	 *
	 * ***************************************/
	function attach()
	{
		if( !isset( $this->file_upload_path ) )
			throw new Exception( "Attachment path not set" );
		$result = $this->upload_file();	
		if( 1 < $this->debug_level )
		{
		    	echo "<pre>" . "::" . __LINE__ . "::" . __METHOD__ . "\n\r";
		    	print_r($result);
			echo "</pre>";
		}
	}
	function upload_file()
	{
		$this->upload_method = "set_note_attachment";
		$this->attach_to_id = $this->note_id;
		return parent::upload_file();
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
$cl = new suitecrm_note("http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php", "kevin", "Letmein1" );
$cl->set( "name", "Test Note" );
$cl->set( "description", "This is a Test Note description to ensure we can create a note and attachment." );
$cl->set( "revision", "1" );
$cl->set( "save_filename", "class.suitecrm_note.php" );
$cl->set( "file_upload_path", "class.suitecrm_note.php" );
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
