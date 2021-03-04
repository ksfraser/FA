<?php

require_once( 'class.origin.php' );


//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/

/*************************************************//**
 * A class to hold the data for an attachment to a note.
 * Should use a "set_note_attachment" method in the REST call
 *
 * Requires that a NOTE already exists for this to attach to
 * - $note_id
 * **************************************************/
class suitecrm_note_attachment extends origin
{
	var $id;
	protected $note_id;
	protected $filename;	//!<string What name to save the attachment as
	protected $base64_contents;	//!<string Base 64 encoded contents of attachment
	protected $revision;
	protected $path;
	protected $file_mime_type; //!<string
	protected $note_attachment_params; //!<array params for the SOAP call - NVL
	
	function __construct( $note_id, $filename = null, $revision = null, $path = null )
	{
		$data_array = array();
		$this->set( "note_id", $note_id );
		$data_array['note_id'] = $note_id;
		if( null !== $filename ){
			$this->set( "filename", $filename );
			$data_array['filename'] = $filename;
		}
		if( null !== $path ){
			$this->set( "path", $path );
			$data_array['path'] = $path;
		}
		if( null !== $revision ){
			$this->set( "revision", $revision );
			$data_array['revision'] = $revision;
		}
		parent::__construct( $data_array );
	}
	function __construct( $data_array = null )
	{
		parent::__construct( $data_array );
	}

	function encode_file( $path = null )
	{
	    if( null !== $path )
		    $this->set( "path", $path );
	    if( ! isset( $this->path ) )
		    throw new Exception( "Path not set", KSF_VALUE_NOT_SET );
	    $contents = file_get_contents( $this->path );
	    $this->set( "base64_contents", base64_encode( $contents );
	    $this->set( "file_mime_type",  mime_content_type( $this->path ),
	}
	function encode_var( $contents )
	{
	    $this->set( "base64_contents", base64_encode( $contents );
	}
	/**************************************//**
	 * Prepare the note_attachment_params for sending to Suite
	 * */
	function prepare()
	{
		if( ! isset( $this->base64_contents ) )
			throw new Exception( "Encoded Contents not available.  Did you forget to run encode_file", KSF_VALUE_NOT_SET );
		parent::prepare();
		$this->set( "note_attachment_params", $this->get( 'nvl' ) );
	}
}

