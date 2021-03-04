<?php

/****************************************************************//**
 * Base class for all SuiteCRM classes to be used in API
 *
 * REFACTORING
 * 20201121
 * 	I've tested SOAP code that makes it fairly easy to do the API
 * 	calls without a lot of specific code.  As we are writing modules
 * 	to interface between apps, we need a way to "map" fields betweem
 * 	systems.
 *
 * 	See client.protect.php for examples of creating an account + contact.
 *
 * 	********************************************************************/

/***********************//**
 * Generic class
 * ***********************/

require_once( '../ksf_modules_common/class.origin.php' );
require_once( "class.name_value_list.php" );


/***********************************************//**
 * Superparent to SUITECRM classes.  
 *
 * This class handles SOAP activities.
 * *************************************************/
class suitecrm extends origin
{
	protected $id;		//!<string each module has an ID field
	protected $date_entered;		//!< datetime
	protected $date_modified;		//!< datetime
	protected $deleted;			//!< checkbox
	protected $params;			//!<array NVL to be sent via SOAP
	protected $this_module_name;
	protected $nvl;				//!<array of data to be added to SOAP msg
	protected $nbl_obj;			//!<object name_value_list object
	protected $related_module_name;		//!<string
	protected $related_module_id;		//!<string
	/**********************************************************//**
	 *
	 * @param data_array array of initialization values 
	 * *********************************************************/	
	function __construct( $data_array = null )
	{
		$this->nvl_obj = new name_value_list();
		if( null !== $data_array )
		{
			foreach( $data_array as $key=>$value )
			{
				$this->set( $key, $value );
			}
		}
	}
	/**********//**
	 * Fake out the Controller process for testing.
	 * *******/
	function tell_eventloop( $a = null, $b = null, $c = null )
	{
		return FALSE;
		//return TRUE;
	}
	function prepare()
	{
		$var_arr = get_object_vars( $this );
		echo __METHOD__ . "::" . __LINE__ . "\n";
		var_dump( $var_arr );
		foreach( $var_arr as $field => $value )
		{
			/*
			if( isset( $this->$field ) )
				$this->nvl_obj->add_nvl( $field, $this->get( $field ) );
			 */
			if( "nvl_obj" != $field )
				$this->nvl_obj->add_nvl( $field, $value );
		}
		$this->set( 'nvl', $this->nvl_obj->get_nvl() );
	}
    	function build_query_string( $fieldlist, $searchPattern )
    	{
		if( ! is_array( $fieldlist ) OR count( $fieldlist ) < 1 )
	    	{
			throw new Exception( "Invalid fields to search" );
	    	}
	    	$query = "(";
	    	$count = 0;
	    	foreach( $fieldlist as $field )
	    	{
			if( $count > 0 )
			{
				$query .= " OR ";
		    	}
		    	$query .= "(" . strtolower( $this->this_module_name ) . "." . $field . " LIKE '" . $searchPattern . "')";
		    	$count++;
	    	}
	    	$query .= ")";
	    	return $query;
    	}
	/*****************************************************//**
	 * Take our base fields and convert them into an NVL for sending.
	 *
	 * @param none
	 * @return none
	 * ******************************************************/
	function fields2nvl()
	{
		throw new Exception( "Depreciated Function.  Use PREPARE instead!" );
	}
}

require_once( 'class.ksfSOAP.php' );

require_once( '../ksf_modules_common/class.curl_handler.php' );

//http://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_6.5/Application_Framework/Web_Services/Examples/REST/PHP/Creating_or_Updating_a_Record/

class suitecrm_old
{
	protected $url;
	protected $username;
	protected $password;
	protected $session_id;
	protected $module_name;
	protected $response;
        protected $obj_var = array( "url", "username", "password",  );
        protected $crm_var = array( "crm_api", "un",   "p",         );
//SEARCH
	protected $search_string;
	protected $search_modules_array;
	protected $search_offset;
	protected $search_max_results;
	protected $search_return_fields_array;
	protected $unified_search_only;
	protected $search_favorites_only;
	protected $search_id;
	protected $isHtaccessProtected;
	protected $htaccessUsername;
	protected $htaccessPassword;
	protected $curl;	//<! Curl object
	protected $name_value_list;	//!< Used during set_entry to create a record
	var $id;
	var $debug_level;
	protected $attach_to_id;	//!< string the ID of the note/document record we are attaching the uploaded file to.
	protected $upload_method;	//!< string the "method" in the call used to upload the file
	protected $save_filename;
	protected $revision;
	protected $file_upload_path;
	protected $related_module;	//!< string module that we are associating this record to. (set_relationship)
	protected $related_ids_array;	//!< array 1D array of IDs in the related module


    function __construct( $url, $username, $password, $module_name )
    {
	    $this->debug_level = 0;
	    $this->url = $url;
	    $this->username = $username;
	    $this->password = $password;
	    $this->module_name = $module_name;
	//SEARCH defaults
		$this->search_favorites_only = false;
		$this->unified_search_only = false;
		$this->search_offset = 0;
		$this->search_max_results = 10;
    }
    //function to make cURL request
    /*@array@*/function call($suite_method, $parameters)
    {
	    ob_start();
	    $this->response = "";
	    $params = array();
	    $headers = array();
	   $jsonEncodedData = json_encode($parameters);
	   $data = array(
	       	     "method" => $suite_method,
	       	     "input_type" => "JSON",
	       	     "response_type" => "JSON",
	       	     "rest_data" => $jsonEncodedData
	       	);

	    	if( !isset( $this->curl ) )
			$this->curl = new curl_handler( $this->debug_level, $this->url, "POST", $params, $headers, $data );
		else
			$this->curl->set( 'data', $data );
   		$this->curl->curlopts = array (
		        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
		        CURLOPT_HEADER => TRUE,	//FALSE for Woo
		        CURLOPT_SSL_VERIFYPEER => FALSE,
		        CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_FOLLOWLOCATION => FALSE, );

		
		if($this->isHtaccessProtected == TRUE)
		{
			$this->curl->set_htaccessProtected( $this->htaccessUsername, $this->htaccessPassword);
		}
	
	        $result = $this->curl->curl_exec();
	
		//$this->curl->curl_close();

		if( false != $result )
		{
			$result = explode("\r\n\r\n", $result, 2);
			//var_dump( $result );
	        	$response = json_decode($result[1]);
	        	ob_end_flush();
			$this->response = $response;
			if( isset( $this->response->id ) )
				$this->id = $this->response->id;
			return $response;
		}
		else
			throw new Exception( "CURL returned False.  Couldn't connect? ::" . $suite_method );
    }
    function login()
    {
    	//$ldap_enc_key = substr(md5($ldap_enc_key), 0, 24);
	    $login_parameters = array(
	         "user_auth" => array(
	              "user_name" => $this->username,
	              //"password" => bin2hex(mcrypt_cbc(MCRYPT_3DES, $ldap_enc_key, $password, MCRYPT_ENCRYPT, 'password')),
		      "password" => md5($this->password),
	              "version" => "1"
	         ),
	         "application_name" => "FrontAccounting",
	         "name_value_list" => array(),
	    );

	    try {
	    	$login_result = $this->call("login", $login_parameters);
	    	//get session id
		$this->session_id = $login_result->id;
	    }
	    catch( Exception $e )
	    {
		    throw $e;
	    }
	    $this->response = null;
    }
    /*string*/function set_entry()
    {
	    if( 0 < $this->debug_level )
    		echo  __LINE__ . "::" . __FILE__ . "::" . __METHOD__ . "\n\r";
        $parameters = array(
        	"session" => $this->session_id,
                "module_name" => $this->module_name,
                //Record attributes
                    "name_value_list" => $this->name_value_list,
	    );
	    	if( 1 < $this->debug_level )
		{
                	echo "<pre>" . __LINE__ . '\n\r';
                	print_r($parameters);
			echo "</pre>". '\n\r';
		}
            $result = $this->call("set_entry", $parameters);
		if( isset( $result->id ) )
            		$this->id = $result->id;
		else
			throw new Exception( $result->description );
	    	if( 1 < $this->debug_level )
		{
                	echo "<pre>" . __LINE__ . "::" . __FILE__ . "::" . __METHOD__ . "\n\r";
                	print_r($result);
			echo "</pre>";
		}
		//throw new Exception( "trying to get a back trace");
		return $result->id;
    }
    /*string*/function set_relationship()
    {
	    if( 0 < $this->debug_level )
    		echo  __LINE__ . "::" . __FILE__ . "::" . __METHOD__ . "\n\r";
        $parameters = array(
        	"session" => $this->session_id,
                "module_name" => $this->module_name,
                //Record attributes
		"name_value_list" => $this->name_value_list,
		"module_id" => $this->id,
		"link_field_name" => $this->relate_module,
		"related_ids" => array( $this->related_ids_array ),
		"delete" => 0
	    );
	    	if( 1 < $this->debug_level )
		{
                	echo "<pre>" . __LINE__ . '\n\r';
                	print_r($parameters);
			echo "</pre>". '\n\r';
		}
		$result = $this->call("set_relationship", $parameters);
		if( 1 < $this->debug_level )
		{
                	echo "<pre>" . __LINE__ . "::" . __FILE__ . "::" . __METHOD__ . "\n\r";
                	print_r($result);
			echo "</pre>";
		}
		return $result->id;
    }
    function update()
    {
	    //the ID parameter needs to be set to update something.
	    $idset = false;
	    foreach( $this->name_value_list as $arr )
	    {
		    if( $arr['name'] == "id" )
			    $idset = true;
	    }
	    if( ! $idset )
	    {
		    throw new Exception( "Can't update without ID of what to update being set" );
	    }

	    //If we got this far we can update the entry
	    return $this->set_entry();

    }
    function create()
    {
	    if( ! isset( $this->name_value_list ) )
		    $this->name_value_list = $this->objectvars2array();
	    return $this->set_entry();

    }
    function upload_file()
    {
	    	$contents = file_get_contents( $this->file_upload_path );
   		$parameters = array(
		        //session id
		        "session" => $this->session_id,
		        //The attachment details
		        "note" => array(
				//The ID of the parent document.
				'id' => $this->attach_to_id,
				//The binary contents of the file.
				'file' => base64_encode($contents),
				//The name of the file
				'filename' => $this->save_filename,
				//The revision number
				'revision' => $this->revision,
		        ),
		 );
	
		$result = $this->call($this->upload_method, $parameters);
		return $result;
    }
    function get_entry_list( $module, $query_where /*The SQL WHERE clause without the word "where" */ )
    {
	    if( 0 < $this->debug_level )
                echo  __LINE__ . "::" . __FILE__ . "::" . __METHOD__ . "\n\r";
	$get_entries_count_parameters = array(
		//Session id
                'session' => $this->session_id,
                //The name of the module from which to retrieve records
                'module_name' => $module,
                //The SQL WHERE clause without the word "where".
                'query' => $query_where,
                //If deleted records should be included in results.
                //           'deleted' => false
	);
        $result = $client->call('get_entry_list', $get_entries_count_parameters);
        $entry_list = $result['entry_list'];
        foreach($entry_list as $entry)
        {
        	foreach($entry['name_value_list'] as $field)
		{
			$this->$field['name'] = $field['value'];
       		}
    	}
    }
	function search()
	{
		$search_param = array(
                 	"session" => $this->session_id,
			"search_string" => $this->search_string,
			"modules" => $this->search_modules_array,
			"offset" => $this->search_offset,
			"max_results" => $this->search_max_results,
			"id" => $this->search_id, 	//Filters records by the assigned user ID.
        						//Leave this empty if no filter should be applied.
			"selected_fields" => $this->search_return_fields_array,    //An array of fields to return.
        						//If empty the default return fields will be from 
							//the active listviewdefs.

			"unified_search_only" => $this->unified_search_only, 	//If the search is to only search 
										//modules participating in the unified search.
			"favorites" => $this->search_favorites_only	//If only records marked as favorites should be returned
		);
		$result = $this->call( "search_by_module", $search_param );
	    	if( 1 < $this->debug_level )
		{
                	echo "<pre>";
                	print_r($result);
			echo "</pre>";
		}
		if( count( $result->entry_list ) > 0 )
		{
			//returns an array of data that can go back into objects.
			foreach( $result->entry_list as $obj_array )
			{
				if( count( $obj_array->records ) > 0 )
				{
					$classtype = $obj_array->name;
				}
				else
					continue;
				foreach( $obj_array->records as $record )
				{
			//		$tmp = new $classtype();
					foreach( $record as $varval )
					{
	    					if( 0 < $this->debug_level )
                					echo  __LINE__ . "::" . __FILE__ . "::" . __METHOD__ . "\n\r";
						var_dump( $varval );
						//should have a name and value
						//What do we need to do now?
					}
				}
			}
		}
		else
		{
			if( 1 < $this->debug_level )
			{
                		echo  __LINE__ . "::" . __FILE__ . "::" . __METHOD__ . "\n\r";
				var_dump( $result );
                		echo  __LINE__ . "::" . __FILE__ . "::" . __METHOD__ . "\n\r";
				var_dump( $this );
			}
		}
	}
    function set( $param, $value )
    {
	    $this->$param = $value;
    }
    function get( $param )
    {
	    return $this->$param;
    }
	/***************************************************************//**
	* Replace the name of a VAR in our object with the associated field in SuiteCRM
	*
	******************************************************************/
 	/*@array@*/function objectvars2array()
        {
                $val = array();
                foreach( get_object_vars( $this ) as $key => $value )
                {
                        $key = str_replace( $this->obj_var, $this->crm_var, $key );
                        //if( $key == "product_url" )
			//      $key = "url";
			if( "id" != $key )	//Not used for CREATE but needed for UPDATE.
				if( isset( $this->$key ) )
		                        $val[] = array( "name" => $key, "value" => $this->$key );
                }
                return $val;
	}
    function getFieldList( $module = "Leads" )
    {
		$set_entry_parameters = array(	 
			"session" => $this->session_id,
			"module_name"	=> $module
		);
		$result = $this->call("get_module_fields", $set_entry_parameters);
		return $result;
    }
    /*@bool@*/function setNoteAttachment( $filename, $savename = null )
    {
	    if( 0 < $this->debug_level )
                echo  __LINE__ . "::" . __FILE__ . "::" . __METHOD__ . "\n\r";
	if( !file_exists( $filename ) )
		return FALSE;
	if( null == $savename )
		$savename = basename( $filename );
	    if( $this->id != '' )
	    {
		    	$attachment=array( 'id' => $this->id,
			    	'filename' => $savename,
				'file_mime_type' => mime_content_type( $filename ),
				'file' => base64_encode( file_get_contents ($filename) )
				);								
			$note_attachment=array( 'session' => $this->session_id,
						'note' 	  => $attachment );
			$result = $this->call('set_note_attachment', $note_attachment);
	    }
	    if( isset( $result->id ) )
		    return TRUE;
	    else
		    return FALSE;
    }
/*****************************
*PERL code generic for creating relationships..

sub create_module_links {
    my ($self, $module, $module_id, $link_field_name, $related_ids, $attributes) = @_;
 
    foreach my $required_attr (@{$self->required_attr->{$module}}) {
        $self->log->logconfess("No $required_attr attribute. Not creating links in $module for: ".Dumper($attributes))
            if (!exists($$attributes{$required_attr}) ||
                !defined($$attributes{$required_attr}) ||
                $$attributes{$required_attr} eq '');
    }
 
    my $rest_data = '{"session": "'.$self->sessionid.'", "module_name": "'.$module
        . '", "module_id": "' . $module_id . '", "link_field_name": "'
        . $link_field_name. '", "related_ids": ' . encode_json($related_ids)
        . ', "name_value_list": '. encode_json($attributes). '}';
 
    my $response = $self->_rest_request('set_relationship', $rest_data);
    $self->log->info( "Successfully created link in <".encode_json($attributes)."> with sessionid ".$self->sessionid."\n");
    $self->log->debug("Link created in module $module was:".Dumper($response));
    #return $response->{id};
    return $response;
*****************************/

}

/*

$cl = new suitecrm("http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php", "admin", "m1l1ce" );
    //create account ------------------------------------- 
    $set_entry_parameters = array(
         //session id
         "session" => $cl->session_id,

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

