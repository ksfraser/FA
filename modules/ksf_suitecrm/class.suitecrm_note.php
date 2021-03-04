<?php

require_once( 'class.suitecrm.php' );
//require_once( 'class.origin.php' );

//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/

class suitecrm_note extends suitecrm
{
	//var $id;
	protected $note_id;
	protected $name;
	//protected $date_entered;
	//protected $date_modified;
	protected $file_mime_type;
	protected $parent_type;
	protected $portal_flag;
	protected $embed_flag;
	protected $description;
	//protected $deleted;
	protected $revision; //from example.  Might be for attachments
	protected $save_filename; //from example.  Might be for attachments
	protected $upload_path; //from example.  Might be for attachments
	protected $note_params; //NVL to send to Suite

    function __construct( $name, $description = "", $note_id = null  )
    {
	    if( null !== $name )
	    {
		    $this->set( "name", $name );
		    $data_array["name"] = $name;
	    }
	    if( null !== $description )
	    {
		    $this->set( "description", $description );
		    $data_array["description"] = $description;
	    }
	    if( null !== $note_id )
	    {
		    $this->set( "note_id", $note_id );
		    $data_array["note_id"] = $note_id;
	    }

    }
	function __construct( $data_array = null )
	{
		parent::__construct( $data_array );
	}
	/**************************************//**
	 * Prepare the note for sending to Suite
	 * */
	function prepare()
	{
		if( ! isset( $this->name ) )
			throw new Exception( "Note Name not set.", KSF_VALUE_NOT_SET );
		if( ! isset( $this->description ) )
			throw new Exception( "Note Description not set.", KSF_VALUE_NOT_SET );
		parent::prepare();
		$this->set( "note_params", $this->get( 'nvl' ) );

	}
}

