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
require_once( "class.name_value_list.php" );

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
class name_value_list
{
	protected $nvl;
	function __construct()
	function add_nvl( $name, $value )
	function get_nvl()
	function search_nvl( $name )
	function get_value( $index )
	function get_named_value( $name )
}
 */


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


class suitecrmSoapClient
{
	protected $url;
	protected $username;
	protected $soapCredential;
	protected $session_id;
	protected $userGUID;
	protected $module_name;
	var $debug_level;
	protected $soapLoginTime;
	protected $soapClient;
	protected $soap_auth_array;
	protected $retryCount;
	protected $result;
	protected $appname;
	protected $nvl;
    function __construct()
    {
	global $sugar_config;
	    $this->debug_level = 0;
	    $this->retryCount = 0;
	    $this->result = null;
	    $this->session_id = null;
	    $this->module_name = null;

	    $this->url = $sugar_config['site_url'] . "/soap.php";	//$sugarSoapEndpoint 
	    $this->appname = $sugar_config['appname'];	//$sugarSoapUser
	    $this->username = $sugar_config['soapuser'];	//$sugarSoapUser
	    $this->soapCredential = $sugar_config['user_hash'];		//"select user_hash from users where user_name='$sugarSoapUser'";
	    $this->nvl = new name_value_list();
	    $this->soapClient = new SoapClient($this->url . '?wsdl'); //Build in library
    }
	function get( $name )
	{
		return $this->$name;
	}
	function build_auth_array()
	{
		$this->soap_auth_array = array(
			'user_name' => $this->username,
			'password' => $this->soapCredential );
		return $this->soap_auth_array;
	}
	function soapReconnect()
	{
		if (time() - $this->soapLoginTime > 600) // 10 minutes
		{
			$this->soapLogin();
		}
	}
	function soapLogin()
	{
		global $userGUID;
		if( ! isset( $this->soap_auth_array ) OR ! isset( $this->soap_auth_array['user_name'] ) )
			$this->build_auth_array();
		$soapLogin = $this->soapClient->login( $this->soap_auth_array, $this->appname, array() );
		$this->session_id  = $soapLogin->id;
		$this->soapLoginTime = time();
		print "! Successful SOAP login id=" . $this->session_id  . " user=" . $this->soap_auth_array['user_name']. "\n";
		return $this->session_id;
	}
	function setSoapParams( $module, $nvl_array, $a = null, $b = null, $c = null, $d = null )
	{
		throw new Exception( "Must override!" );
	}
	function soapCall( $operation )
	{
		try {
			//var_dump( $this->soapClient->__getFunctions() );
			if( ! isset( $this->soapParams ) OR ! is_array( $this->soapParams ) )
				$this->soapParams = array( $this->get( 'session_id' ), "Accounts", "accounts.name like '%Fraser%'", "", "0", array(), "", "10", "0", "false" ) ;

			$result = $this->result = $this->soapClient->__soapCall( $operation, $this->soapParams  );
			//$result = $this->soapClient->__soapCall( $operation,  $this->soapParams   );
			//$result = $this->soapClient->$operation(  $this->soapParams  );
			//$result = $this->soapClient->$operation( array( $params ) );	//DOESN"T WORK
			var_dump( $this->result );
		} catch( Exception $e )
		{
			//Seeing an invalid session ID error.
		}
		//$result = $this->soapClient->call( $operation, $this->soapParams );
		if (isset($result->error) && $result->error['number'] == 10 && $this->retryCount < 5)
		{
			// Session lost, try to reconnect and retry
			$this->soapReconnect();
			$this->retryCount++;
			$this->result = $this->soapCall($operation);
		}
		//What about if retrycount, and errnum <>10
		return $result;
	}
/*	    $login_parameters = array(
	         "user_auth" => array(
	              "user_name" => $this->username,
	              //"password" => bin2hex(mcrypt_cbc(MCRYPT_3DES, $ldap_enc_key, $password, MCRYPT_ENCRYPT, 'password')),
		      "password" => md5($this->password),
	              "version" => "1"
	         ),
	         "application_name" => "FrontAccounting",
	         "name_value_list" => array(),
	 );
 */
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
						var_dump( $varval );
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
$sugar_config['site_url'] = "https://mickey.ksfraser.com/ksfii/suitecrm/service/v4_1/";
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
$o->soapParams = $nvl->get_nvl();
//var_dump( $nvl->get_nvl() );
print $o->soapCall( "get_entry_list" );
 */
