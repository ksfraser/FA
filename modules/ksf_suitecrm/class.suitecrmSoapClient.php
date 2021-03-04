<?php
/******************************************//**
 *	Module based upon SOAP connection stuff
 *	in Asterisk (YAAI) code
 *		asteriskLogger.php
 *
 *	**************************************/

//
// Required libraries

//require_once( "vendor/econea/nusoap/src/nusoap.php" );
require_once( 'class.ksfSOAP.php' );


/*
 "entry_value login(user_auth $user_auth, string $application_name, name_value_list $name_value_list)"
 "void logout(string $session)"
 "get_entry_result_version2 get_entry(string $session, string $module_name, string $id, select_fields $select_fields, link_names_to_fields_array $link_name_to_fields_array, boolean $track_view)"
 "get_entry_result_version2 get_entries(string $session, string $module_name, select_fields $ids, select_fields $select_fields, link_names_to_fields_array $link_name_to_fields_array, boolean $track_view)"
 "get_entry_list_result_version2 get_entry_list(string $session, string $module_name, string $query, string $order_by, int $offset, select_fields $select_fields, link_names_to_fields_array $link_name_to_fields_array, int $max_results, int $deleted, boolean $favorites)"
 "new_set_relationship_list_result set_relationship(string $session, string $module_name, string $module_id, string $link_field_name, select_fields $related_ids, name_value_list $name_value_list, int $delete)"
 "new_set_relationship_list_result set_relationships(string $session, select_fields $module_names, select_fields $module_ids, select_fields $link_field_names, new_set_relationhip_ids $related_ids, name_value_lists $name_value_lists, deleted_array $delete_array)"
 "get_entry_result_version2 get_relationships(string $session, string $module_name, string $module_id, string $link_field_name, string $related_module_query, select_fields $related_fields, link_names_to_fields_array $related_module_link_name_to_fields_array, int $deleted, string $order_by, int $offset, int $limit)"
 "new_set_entry_result set_entry(string $session, string $module_name, name_value_list $name_value_list)"
 "new_set_entries_result set_entries(string $session, string $module_name, name_value_lists $name_value_lists)"
 "get_server_info_result get_server_info()"
 "string get_user_id(string $session)"
 "new_module_fields get_module_fields(string $session, string $module_name, select_fields $fields)"
 "int seamless_login(string $session)"
 "new_set_entry_result set_note_attachment(string $session, new_note_attachment $note)"
 "new_return_note_attachment get_note_attachment(string $session, string $id)"
 "new_set_entry_result set_document_revision(string $session, document_revision $note)"
 "new_return_document_revision get_document_revision(string $session, string $i)"
 "return_search_result search_by_module(string $session, string $search_string, select_fields $modules, int $offset, int $max_results, string $assigned_user_id, select_fields $select_fields, boolean $unified_search_only, boolean $favorites)"
 "module_list get_available_modules(string $session, string $filter)"
 "string get_user_team_id(string $session)"
 "void set_campaign_merge(string $session, select_fields $targets, string $campaign_id)"
 "get_entries_count_result get_entries_count(string $session, string $module_name, string $query, int $deleted)"
 "md5_results get_module_fields_md5(string $session, select_fields $module_names)"
 "last_viewed_list get_last_viewed(string $session, module_names $module_names)"
 "upcoming_activities_list get_upcoming_activities(string $session)"
 "modified_relationship_result get_modified_relationships(string $session, string $module_name, string $related_module, string $from_date, string $to_date, int $offset, int $max_results, int $deleted, string $module_user_id, select_fields $select_fields, string $relationship_name, string $deletion_date)"
 * */


/*
class set_relationship_soapClient extends suitecrmSoapClient
{
	function setSoapParams( $module1, $id1, $module2, $id2 )
	{
		$this->soapParams = array(
			'session' => $this->session_id,
			'set_relationship_value' => array(
				'module1' => $module1,
				'module1_id' => $id1,
				'module2' => $module2,
				'module2_id' => $id2,
			)
		);
	}
	function soapCall( $operation = 'set_relationship' )
	{
		parent::soapCall( $operation );
	}
}
class set_entry_soapClient extends suitecrmSoapClient
{
	function setSoapParams( $module, $nvl_array )
	{
		$this->soapParams = array(
			'session' => $this->session_id,
				'module_name' => $module,
				'name_value_list' => $nvl_array
			);
	}
	function soapCall( $operation = 'set_entry' )
	{
		parent::soapCall( $operation );
	}
}
class get_entry_soapClient extends suitecrmSoapClient
{
	protected $decodedResult;
	function setSoapParams( $module, $id )
	{
		$this->soapParams = array(
			'session' => $this->session_id,
			'module_name' => $module,
			'id' => $id
			);
	}
	function soapCall( $operation = 'get_entry' )
	{
		parent::soapCall( $operation );
		$this->decode_name_value_list( $this->result['entry_list'][0]['name_value_list'] );
	}
	function decode_name_value_list($nvl)
	{
		$this->decodedResult = array();

		if (is_array($nvl) && count($nvl) > 0)
		{
			foreach ($nvl as $nvlEntry)
			{
				$key = $nvlEntry['name'];
				$val = $nvlEntry['value'];
				$this->decodedResult[$key] = $val;
			}
		}
		return $this->decodedResult;
	}
	function getDecodedResult()
	{
		return $this->decodedResult();
	}
}
class get_entry_list_soapClient extends get_entry_soapClient 
{
	function soapCall( $operation = 'get_entry_list' )
	{
		parent::soapCall( $operation );
		$this->decode_name_value_list( $this->result['entry_list'][0]['name_value_list'] );
	}

	function setSoapParams( $module, $query )
	{
		$this->soapParams = array(
			'session' => $this->session_id,
			'module_name' => $module,
			'query' => $query
			);
	}

}
class get_relationships_soapClient extends get_entry_soapClient 
{
	function soapCall( $operation = 'get_relationships' )
	{
		parent::soapCall( $operation );
		$this->decode_name_value_list( $this->result['ids'][0] );
	}

	function setSoapParams( $module, $id, $related, $query = '' )
	{
		$this->soapParams = array(
			'session' => $this->session_id,
			'module_name' => $module,
			'module_id' => $id,
			'related_module' => $related,
			'related_module_query' => $query,
			'deleted' => 0
			);
	}
}
*/
//Search for a Contact via phone number
/*
 * $contactList = new get_entry_list_soapClient();
 * $contactList->setSoapParams( 'Contacts', "((contacts.phone_work LIKE '$searchPattern') 
 * 						OR (contacts.phone_mobile LIKE '$searchPattern') 
 * 						OR (contacts.phone_home LIKE '$searchPattern') 
 * 						OR (contacts.phone_other LIKE '$searchPattern'))" );
 * $contactList->soapCall();
 * $contactList->getDecodedResult();
 *
 * */
//Search for an Account via phone number
/*
 * $accountList = new get_entry_list_soapClient();
 * $accountList->setSoapParams( 'Accounts', "((accounts.phone_office LIKE '$searchPattern') 
 * 						OR (accounts.phone_alternate LIKE '$searchPattern'))" );
 * $accountList->soapCall();
 * $accountList->getDecodedResult();
 *
 * */
//Search for a User by extension
/*
 * $userList = new get_entry_list_soapClient();
 * $query = sprintf("(users_cstm.asterisk_ext_c='%s')", $exten);
 * $userList->setSoapParams( 'Users', $query ); 
 * $userList->soapCall();
 * $userList->getDecodedResult();
 *
 * */


/*************************************************//**
 * Class for querying the SuiteCRM SOAP server
 *
 * @TODO Major refactor since there are many fields which
 * could be the same thing. (i.e. _ids vs _id )
 * @TODO data validation
 *
 *
 * ***************************************************/

require_once( 'class.suitecrm.php' );


class suitecrmSoapClient extends suitecrm
{
	protected $url;			//Used in soapClient
	protected $appname;		//Used in soapClient
	protected $username;		//Used in soapClient
	protected $password;		//Used in soapClient
	protected $userGUID;
	protected $module_name;
	protected $module_names;
	protected $module_ids;
	protected $module_id;
	protected $record_id;		//!< int 
	protected $record_ids;		//!< array
	protected $select_fields;	//!< array
	protected $link_name_to_fields_array;
	protected $link_field_name;
	protected $link_field_names;
	protected $related_ids;
	protected $related_module_query;
	protected $related_fields;
	protected $track_view;
	protected $query;		//!<string
	protected $order_by;		//!<string
	protected $offset;		//!<int
	public    $limit;		//!<int
	protected $max_results;		//!<int
	protected $deleted;		//!<int
	protected $delete;		//!<int
	protected $favorites;		//!<bool
	protected $result;
	protected $nvl;
	protected $soapParams;
	var $test_arr;


    function __construct( $config_array = null )
    {
	    global $sugar_config;
	    parent::__construct();
	 	

	//    $this->url = $sugar_config['site_url'] . "/soap.php";	//$sugarSoapEndpoint 
	    $this->soapClient = new ksfSOAP();
	    //parent::__construct();
	    $this->tell_eventloop( "READ_INI", "soap.ini" );
	    $this->soapParams = array();
	    if( 
		    $this->tell_eventloop( "SETTINGS_QUERY", "soap_url" )
		    AND $this->tell_eventloop( "SETTINGS_QUERY", "appname" )
		    AND $this->tell_eventloop( "SETTINGS_QUERY", "soapuser" )
		    AND $this->tell_eventloop( "SETTINGS_QUERY", "user_hash" )
	    )
	    {
		    //These 4 functions are TESTING procs that tell_eventloop above would
		    //have called had we not dummied up that proc for testing.
		    $this->soap_url( $sugar_config['site_url'] . "/soap.php?wsdl" );
		    $this->appname( $sugar_config['appname'] );
		    $this->soapuser( $sugar_config['soapuser'] );
		    $this->user_hash( $sugar_config['user_hash'] );
	
	    }
	    else
	    {
		    if( !isset( $this->url ) )
		    {
			    if( isset( $config_array['site_url'] ) )
				    $this->soap_url( $config_array['site_url'] );
			    else if( isset( $sugar_config['site_url'] ) )
				    $this->soap_url( $sugar_config['site_url'] );
			    else
		    		throw new Exception( "Couldn't Initialize SOAP client CONFIG" );
		    }
		    if( !isset( $this->appname ) )
		    {
			    if( isset( $config_array['appname'] ) )
				    $this->appname( $config_array['appname'] );
			    else if( isset( $sugar_config['appname'] ) )
				    $this->appname( $sugar_config['appname'] );
			    else
		    		throw new Exception( "Couldn't Initialize SOAP client CONFIG" );
		    }
		    if( !isset( $this->username ) )
		    {
			    if( isset( $config_array['username'] ) )
				    $this->username( $config_array['username'] );
			    else if( isset( $sugar_config['username'] ) )
				    $this->username( $sugar_config['username'] );
			    else
		    		throw new Exception( "Couldn't Initialize SOAP client CONFIG" );
		    }
		    if( !isset( $this->password ) )
		    {
			    if( isset( $config_array['password'] ) )
				    $this->password( $config_array['password'] );
			    else if( isset( $sugar_config['password'] ) )
				    $this->password( $sugar_config['password'] );
			    else
		    		throw new Exception( "Couldn't Initialize SOAP client CONFIG" );
		    }

	    }
	    $this->soapClient->soapLogin();
	    $this->nvl = new name_value_list();
    }
	/*****************************************//**
	 * Callback function from tell_eventloop SETTINGS_QUERY
	 *
	 * @param value string to search for.
	 * @return NONE
	 * *****************************************/
	function soap_url( $value )
	{
		if( $this->soapClient->set( 'url', $value ) )
		{
			//echo __FILE__ . "::" . __LINE__ . "\n";
			//var_dump( $this->soapClient );
			$this->set( 'url', $value );
		}
		else
			throw new Exception( "Why are we here?" );
			
	}
	/*****************************************//**
	 * Callback function from tell_eventloop SETTINGS_QUERY
	 *
	 * @param value string to search for.
	 * @return NONE
	 * *****************************************/
	function appname( $value )
	{
		if( $this->soapClient->set( 'appname', $value ) )
			$this->set( 'appname', $value );
		else
			throw new Exception( "Why are we here?" );
	}
	/*****************************************//**
	 * Callback function from tell_eventloop SETTINGS_QUERY
	 *
	 * @param value string to search for.
	 * @return NONE
	 * *****************************************/
	function username( $value )
	{
		if( $this->soapClient->set( 'username', $value ) )
			$this->set( 'username', $value );
		else
			throw new Exception( "Why are we here?" );
	}
	/*****************************************//**
	 * Callback function from tell_eventloop SETTINGS_QUERY
	 *
	 * @param value string to search for.
	 * @return NONE
	 * *****************************************/
	function password( $value )
	{
		if( $this->soapClient->set( 'password', $value ) )
		{
			$this->set( 'password', $value );
			//echo __FILE__ . "::" . __LINE__ . "\n";
			//var_dump( $this->soapClient );
		}
		else
			throw new Exception( "Why are we here?" );
	}
	/*****************************************//**
	 * Wrapper to the soapCall in the client class. 
	 *
	 * @param operation string function to call on server.
	 * @param soapParams array|Null only if we haven't set otherwise
	 * @return array | stdClass
	 * *****************************************/
	function soapCall( $operation, $soapParams = null )
	{
		$this->result = null;
		if( $soapParams !== null )
		{
			$this->soapClient->set( "soapParams", $soapParams );
		}
		else if( ! isset( $this->soapParams ) )
		{
			throw new Exception( "Soap Params not set", KSF_FIELD_NOT_SET );
		}
		else
		{
			//in ksfSOAPTest it uses nvl->get_nvl instead of this->soapParams
			$this->soapClient->set( "soapParams", $this->soapParams );
		}
		try {
			$this->result = $this->soapClient->soapCall( $operation );
		}
		catch( Exception $e )
		{
			print_r( $e->getMessage(), true );
			throw $e;
		}
		return $this->result;
	}
	//Inherited function get( $name )
	//Inherited function set( $name, $value )
	/**********************************************//**
	 * Get the 0 indexed item of an NAMED array object var
	 *
	 * @param name the name of the field to grab the first (0) element from
	 * @return mixed | null
	 * ***********************************************/
	function get_one( $name )
	{
		if( is_array( $this->$name ) )
		{
			if( isset( $this->$name[0] ) )
				return $this->$name[0];
			else
				throw new Exception( "0 element not set", KSF_VAR_NOT_SET );
		}
		else
			throw new Exception( "Not an array.  Did you mean ->get()", KSF_INVALID_DATA_TYPE );
		return NULL; //It should be impossible to get here, but we are throwing an exception
				//that I can't track down either
	}
	/*********************************************//**
	 * Not sure long term this func will get used
	 *
	 * Since we have an attached object soapClient that needs
	 * its own set of params, and we are setting those before
	 * calling it (see get_entry below) this func might be 
	 * pointless.
	 *
	 * @param module string
	 * @param nvl_array array of name_value_list values
	 * @param a array
	 * @param b array
	 * @param c array
	 * @param d array
	 * @return bool
	 * *********************************************/
	function setSoapParams( $module, $nvl_array, $a = null, $b = null, $c = null, $d = null )
	{
		//throw new Exception( "Must override!" );
		//$this->soapParams = array( $this->get( 'session_id' ), "Accounts", "accounts.name like '%Fraser%'", "", "0", array(), "", "10", "0", "false" ) ;
		unset( $this->soapParams );
		$this->soapParams = array();
		$this->soapParams[] = $this->soapClient->get( 'session_id' ); 
		$this->soapParams[] = $module; 
		foreach( $nvl_array as $row )
		{
			if( is_array( $row ) and count( $row ) > 1 )
			{
				if( isset( $row['value'] ) )
				{
					$this->soapParams[] = $row['value'];
				}
				else
				{
					//Going to assume the keys are row[name] = value
					$this->soapParams[] = $row[0];
				}
			}
			else
			{
				$this->soapParams[] = $row;
			}
		}
		/*
		if( isset( $a ) )
		{
			if( is_array( $a ) )
			{
				foreach( $a as row )
				{
					$this->soapParams[] = $row;
				}
			}
			else
				$this->soapParams[] = $a;
		}
		if( isset( $b ) )
		{
			if( is_array( $b ) )
			{
				foreach( $b as row )
				{
					$this->soapParams[] = $row;
				}
			}
			else
				$this->soapParams[] = $b;
		}
		if( isset( $c ) )
		{
			if( is_array( $c ) )
			{
				foreach( $c as row )
				{
					$this->soapParams[] = $row;
				}
			}
			else
				$this->soapParams[] = $c;
		}
		if( isset( $d ) )
		{
			if( is_array( $d ) )
			{
				foreach( $d as row )
				{
					$this->soapParams[] = $row;
				}
			}
			else
				$this->soapParams[] = $d;
		}
		 */
		//var_dump( $this->soapParams );
		return true;
	}

//get_entry(string $session, string $module_name, string $id, select_fields $select_fields, link_names_to_fields_array $link_name_to_fields_array, boolean $track_view)"
	function get_entry()
	{
		try 
		{
			$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'module_name' ),
						$this->get( 'record_id' ),	//Should this be module_id?
						$this->get( 'select_fields' ),
						$this->get( 'link_name_to_fields_array' ),
						$this->get( 'track_view' ) 
					) ;
			return $this->soapCall( "get_entry" );
		
		}
		catch( Exception $e )
		{
			return new stdClass();
		}
	}
//get_entries(string $session, string $module_name, select_fields $ids, select_fields $select_fields, link_names_to_fields_array $link_name_to_fields_array, boolean $track_view)"
	function get_entries()
	{
		try 
		{
			$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'module_name' ),
						$this->get( 'record_ids' ),	
						$this->get( 'select_fields' ),
						$this->get( 'link_name_to_fields_array' ),
						$this->get( 'track_view' ) 
					) ;
			return $this->soapCall( "get_entries" );
		
		}
		catch( Exception $e )
		{
			return new stdClass();
		}
	}
//get_entry_list(string $session, string $module_name, string $query, string $order_by, int $offset, select_fields $select_fields, link_names_to_fields_array $link_name_to_fields_array, int $max_results, int $deleted, boolean $favorites)"
	function get_entry_list()
	{
		try 
		{
			/*
			 * Tested working
			 * 	$nvl2 = new name_value_list();
			//$nvl2->add_nvl( "module_name", "Accounts" );
			$nvl2->add_nvl( "filter", ""  );
			$nvl2->add_nvl( "order_by", "" );
			$this->connectionA->setSoapParams( "Accounts", $nvl2->get_nvl() );
			$oret = $this->connectionA->soapCall( "get_entry_list" )
			 */
			/*Tested FAIL
			$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'module_name' ),
						$this->get( 'query' ),	
						$this->get( 'order_by' ),	
						$this->get( 'offset' ),	
						$this->get( 'select_fields' ),
						$this->get( 'link_name_to_fields_array' ),
						$this->get( 'max_results' ), 
						$this->get( 'deleted' ),
						$this->get( 'favorites' ),
					) ;
			return $this->soapCall( "get_entry_list" );
			 */
			$nvl2 = new name_value_list();
			$nvl2->add_nvl( "filter", $this->get( 'query' )  );
			$nvl2->add_nvl( "order_by", $this->get( 'order_by' ) );
			$nvl2->add_nvl( "offset", $this->get( 'offset' ) );
			$nvl2->add_nvl( "select_fields", $this->get( 'select_fields' ) );
			$nvl2->add_nvl( "link_fields", $this->get( 'link_name_to_fields_array' ) );
			$nvl2->add_nvl( "max_results", $this->get( 'max_results' ) );
			$nvl2->add_nvl( "deleted", $this->get( 'deleted' ) );
			$this->setSoapParams( $this->get( 'module_name' ), $nvl2->get_nvl() );
			$oret = $this->soapCall( "get_entry_list" );
			return $oret;
		}
		catch( Exception $e )
		{
			return new stdClass();
		}
	}
//set_relationship(string $session, string $module_name, string $module_id, string $link_field_name, select_fields $related_ids, name_value_list $name_value_list, int $delete)"
	function set_relationship()
	{
		try 
		{
			$nvl = new name_value_list();
			//var_dump( $o );
			$nvl->add_nvl( "session_id", $this->soapClient->get( "session_id" ) );
			$nvl->add_nvl( "Module", $this->get( 'module_name' ) );
			$nvl->add_nvl( "Record ID", $this->get( 'record_id' ) );
			$nvl->add_nvl( "Link Field Name", $this->get( 'link_field_name' ) );
			$nvl->add_nvl( "Related IDs",	$this->get( 'related_ids' ) );
			$nvl->add_nvl( "nvl", $this->get( 'nvl' ) );
			$nvl->add_nvl( "Delete", $this->get( 'delete' ) );	
			$this->set( "soapParams", $nvl->get_nvl() );
			/*
			$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'module_name' ),
						$this->get( 'record_id' ),	
						$this->get( 'link_field_name' ),	
						$this->get( 'related_ids' ),	
						$this->get( 'nvl' ),	
						$this->get( 'delete' ),	
					) ;
			 */
			return $this->soapCall( "set_relationship" );
		}
		catch( Exception $e )
		{
			return new stdClass();
		}
		
	}
	
//set_relationships(string $session, select_fields $module_names, select_fields $module_ids, select_fields $link_field_names, new_set_relationhip_ids $related_ids, name_value_lists $name_value_lists, deleted_array $delete_array)"
	function set_relationships()
	{
		/*
		$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'module_names' ),
						$this->get( 'module_ids' ),	
						$this->get( 'link_field_names' ),	
						$this->get( 'related_ids' ),	
						$this->get( 'nvl' ), 
						$this->get( 'delete' ), 
					) ;
		 */
		$nvl = new name_value_list();
		$nvl->add_nvl( "session_id", $this->soapClient->get( "session_id" ) );
		$nvl->add_nvl( "Module", $this->get( 'module_name' ) );
		$nvl->add_nvl( "module IDs", $this->get( 'module_ids' ) );
		$nvl->add_nvl( "Link Fields Names", $this->get( 'link_field_names' ) );

		$nvl->add_nvl( "related IDs", $this->get( 'related_ids' ) );
		$nvl->add_nvl( "NVL", $this->get( 'nvl' ) );
		$nvl->add_nvl( "deleted_array", array() );
		//$nvl->add_nvl( "delete", $this->get( 'delete' ) );
		$this->set( "soapParams", $nvl->get_nvl() );
		return $this->soapCall( "set_relationships" );
	}
	/**
	 * Came from a child class.  Don't know if it will work or not until tested.
	 * */
	function setRelationships( $module1, $id1, $module2, $id2 )
	{
		$this->soapParams = array(	'session' => $this->session_id,
						'set_relationship_value' => array(
							'module1' => $module1,
							'module1_id' => $id1,
							'module2' => $module2,
							'module2_id' => $id2,
						)
					);
		$this->soapCall( 'set_relationship' );
	}


//get_relationships(string $session, string $module_name, string $module_id, string $link_field_name, string $related_module_query, select_fields $related_fields, link_names_to_fields_array $related_module_link_name_to_fields_array, int $deleted, string $order_by, int $offset, int $limit)"
	function get_relationships()
	{
		/*
		$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'module_name' ),
						$this->get( 'module_id' ),	
						$this->get( 'related_module_query' ),	
						$this->get( 'related_fields' ),	
						$this->get( 'link_name_to_fields_array' ),
						$this->get( 'deleted' ), 
						$this->get( 'order_by' ), 
						$this->get( 'offset' ), 
						$this->get( 'limit' ), 
					) ;
		 */
		$nvl = new name_value_list();
		$nvl->add_nvl( "session_id", $this->soapClient->get( "session_id" ) );
		$nvl->add_nvl( "Module", $this->get( 'module_name' ) );
		$nvl->add_nvl( "module ID", $this->get( 'module_id' ) );
		$nvl->add_nvl( "Related Module Queryy", $this->get( 'related_module_query' ) );
		$nvl->add_nvl( "Related Fields", $this->get( 'related_fields' ) );
		$nvl->add_nvl( "Link Name to Fields", $this->get( 'link_name_to_fields_array' ) );
		$nvl->add_nvl( "Deleted", $this->get( 'deleted' ) );
		$nvl->add_nvl( "Order By", $this->get( 'order_by' ) );
		$nvl->add_nvl( "Offset", $this->get( 'offset' ) );
		$nvl->add_nvl( "Limit", $this->get( 'limit' ) );
		$this->setSoapParams( $this->get( 'module_name' ), $nvl->get_nvl() );
		//var_dump( $this->soapParams );
		//var_dump( $this->soapClient->soapParams );
		//
		//Getting a SoapFault: Unknown error in SOAP call: service died unexpectedly
		try
		{
			$ret = $this->soapCall( "get_relationships" );
			return $ret;
		}
		catch( Exception $e )
		{
			//Should be a SoapFault
			//var_dump( $e );
			return new stdClass;
		}
	}
	function set_entry()
	{

		/*
		//The following is tested working with code in client.protect.php
		$ret = $this->soapClient->set_entry( 
				$this->get( 'session_id' ), 
				$this->get( 'module_name' ),
				$this->get( 'nvl' )						
			);
		//var_dump( $ret );
		return $ret;
		 */

		//The following is ALSO tested working with code in client.protect.php
		$this->soapParams = array( 	
			$this->soapClient->get( 'session_id' ), 
			$this->get( 'module_name' ),
			$this->get( 'nvl' )							
			) ;
		$ret = $this->soapCall( "set_entry" );
		//var_dump( $ret );
		return $ret;
	}
	function set_entries()
	{
		$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'module_name' ),
						$this->get( 'nvl' ),
					) ;
		return $this->soapCall( "set_entries" );
	}
	function get_server_info()
	{
		$this->soapParams = array( 
			$this->soapClient->get( 'session_id' ) );
		/*
 		["flavor"]=>
  			string(2) "CE"
  		["version"]=>
  			string(6) "6.5.25"
  		["gmt_time"]=>
  			string(19) "2021-03-02 22:14:50"
		 * 
		 * */
		return $this->soapCall( "get_server_info" );
	}
	function get_user_id()
	{
		$this->soapParams = array( 
			$this->soapClient->get( 'session_id' ) );
		return $this->soapCall( "get_user_id" );
	
	}
	function get_module_fields()
	{
		$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'module_name' ),
						$this->get( 'select_fields' ),
					) ;
			/*
 			[18]=>
			    object(stdClass)#149 (6) {
			      ["name"]=>
			      string(10) "department"
			      ["type"]=>
			      string(7) "varchar"
			      ["group"]=>
			      string(0) ""
			      ["label"]=>
			      string(11) "Department:"
			      ["required"]=>
			      int(0)
			      ["options"]=>
			      array(0) {
			      }
			    }
			 */
		return $this->soapCall( "get_module_fields" );
	}
	function seamless_login()
	{
		$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
					) ;
		return $this->soapCall( "seamless_login" );
	}
	function set_note_attachment()
	{
		//string $session, new_note_attachment $note)"
	}
	function get_note_attachment()
	{
		$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'record_id' ),	
					) ;
		/*
			object(stdClass)#227 (1) {
			  ["note_attachment"]=>
			  object(stdClass)#605 (5) {
			    ["id"]=>
			    string(36) "5b4a3e5d-d6a4-e66d-1ffa-603ebc8716e2"
			    ["filename"]=>
			    string(10) "htpass.txt"
			    ["file"]=>
			    string(236) "a2V2aW46JGFwcjEkUDZPc2tGamokcUlpRGJ2V3RTYnF5bmZDWFBWOGVUMApraW1iZXJseTokYXByMSRUSEZiRkx3USRMVlZGSFR0L1owZHc2RnhtVnZyZksxCmJpbGw6JGFwcjEkOHN1TmNvUWkkQkQ5NVFUNTJWazIuaDdHWXVZT2FsMApnb3JkOiRhcHIxJENUU1pjMG5vJEh6YVJZa1NlR2JlRTRZL2U5cU1abTAK"
			    ["related_module_id"]=>
			    string(36) "9794cbbd-6542-a33e-619b-5ce73122cd9f"
			    ["related_module_name"]=>
			    string(8) "Accounts"
			  }
			}
			 		 * 
		 * */
		return $this->soapCall( "get_note_attachment" );
	}
	function set_document_revision()
	{
		//string $session, document_revision $note)"
	}
	function get_document_revision()
	{
		$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'record_id' ),	
					) ;
		return $this->soapCall( "get_document_revision" );
	}
	function search_by_module()
	{
		//string $session, string $search_string, select_fields $modules, int $offset, int $max_results, string $assigned_user_id, select_fields $select_fields, boolean $unified_search_only, boolean $favorites)"
	}
	function get_available_modules()
	{
		$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'query' ),	
					) ;
		return $this->soapCall( "get_available_modules" );
	}
	/**
	 * Not defined in Sugar 4.1
	 * /
	function get_user_team_id()
	{
		$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
					) ;
		return $this->soapCall( "get_user_team_id" );
	
	}
	 */
	function set_campaign_merge()
	{
		//string $session, select_fields $targets, string $campaign_id)"
		return new stdClass();
	}
	function get_entries_count()
	{
		$this->soapParams = array( 	$this->soapClient->get( 'session_id' ), 
						$this->get( 'module_name' ),
						$this->get( 'query' ),	
						$this->get( 'deleted' ) 
					) ;
		return $this->soapCall( "get_entries_count" );
	}
	function get_module_fields_md5()
	{
		//string $session, select_fields $module_names)"
		return new stdClass();
	}
	function get_last_viewed()
	{
		//string $session, module_names $module_names)"
		return new stdClass();
	}
	/*******************************//**
	 *
	 * @returns array | stdClass on exception
	 */
	function get_upcoming_activities()
	{
		try 
		{
			$this->soapParams = array( $this->soapClient->get( 'session_id' ) ) ;
			//returns an array
			return $this->soapCall( "get_upcoming_activities" );
		}
		catch( Exception $e )
		{
			//Should I return an ARRAY with the error info, or add the error info into the class?
			return new stdClass();
		}
	}
	function get_modified_relationships()
	{
		//Temp measure for TESTING until func actually written
		return new stdClass();
		//string $session, string $module_name, string $related_module, string $from_date, string $to_date, int $offset, int $max_results, int $deleted, string $module_user_id, select_fields $select_fields, string $relationship_name, string $deletion_date)"
	}





/*        $parameters = array(
        	"session" => $this->session_id,
                "module_name" => $this->module_name,
                //Record attributes
		"name_value_list" => $this->name_value_list,
		"module_id" => $this->id,
		"link_field_name" => $this->relate_module,
		"related_ids" => array( $this->related_ids_array ),
		"delete" => 0
	);
 */
	/*
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
	 */
/*	function search()
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
						//var_dump( $varval );
						//should have a name and value
						//What do we need to do now?
					}
				}
			}
		}
 */
	/***************************************************************//**
	* Replace the name of a VAR in our object with the associated field in SuiteCRM
	*
	******************************************************************
	function objectvars2array()
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
    function setNoteAttachment( $filename, $savename = null )
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
	 */

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

//TESTS replace

/*
global $sugar_config;
$sugar_config = array();
//$sugar_config['site_url'] = "http://URL";
//$sugar_config['soapuser'] = "soapuser";
//$sugar_config['user_hash'] = "user_hash";
//$sugar_config['site_url'] = "https://mickey.ksfraser.com/devel/fhs/suitecrm/service/v4_1/rest.php";
$sugar_config['site_url'] = "https://mickey.ksfraser.com/ksfii/suitecrm/service/v4_1/soap.php";
$sugar_config['appname'] = "FA_Integration";
//$sugar_config['site_url'] = "https://mickey.ksfraser.com/devel/fhs/suitecrm/service/v4_1/";
$sugar_config['soapuser'] = "admin";
$sugar_config['user_hash'] = md5('m1l1ce');
//$sugar_config['soapuser'] = "kevin";
//$sugar_config['user_hash'] = md5("Letmein1");
global $userGUID;

$o = new suitecrmSoapClient();
$o->soapLogin();
$nvl = new name_value_list();
$nvl->add_nvl( "session", $o->get( 'session_id' ) );
$nvl->add_nvl( "Module", "Accounts" );
//$nvl->add_nvl( "Filter", "" );
//$nvl->add_nvl( "Order_by", "" );
//$nvl->add_nvl( "Start", "" );
//$nvl->add_nvl( "Return", "" );
//$nvl->add_nvl( "Link", "" );
//$nvl->add_nvl( "Results", "" );
//$nvl->add_nvl( "Deleted", "1" );
//$o->setSoapParams( $nvl->get_nvl() );
////$o->soapParams =  $nvl->get_nvl();
//var_dump( $nvl->get_nvl() );
//var_dump( $o->soapCall( "get_entry_list" ) );
print_r( $o->soapCall( "get_entry_list" ), true );
 */
