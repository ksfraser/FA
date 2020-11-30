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

/************************************************//**
 * This is an attempt at an abstract SOAP class based upon a working SuiteCRM connection.
 *
 * This class may not actually abstract much away.
 * *************************************************/
class ksfSOAP extends origin
{
	protected $soapClient;
	protected $username;
	protected $password;
	protected $url;
	protected $soapLoginTime;
	protected $soap_auth_array;
	protected $retryCount;
	protected $result;
	protected $appname;
	var $soapParams;		//!<array
	//protected $soapCredential;	//replaced by password
	protected $session_id;
	var $debug_level;

	function __construct()
	{
		parent::__construct();
	    	$this->debug_level = 0;
	    	$this->retryCount = 0;
	    	$this->result = null;
	    	$this->session_id = null;
		$this->module_name = null;
	}
	/*********************************//**
	 * Create a PHP Soap Client, using wsdl
	 *
	 * @param none
	 * @return none 
	 * ************************************/
	function setSoapClient()
	{
		if( ! isset( $this->url ) )
			throw new Exception( "Base SOAP URL must be set", KSF_FIELD_NOT_SET );
	    $this->soapClient = new SoapClient($this->url . '?wsdl'); //Build in library
	}
	/*********************************//**
	 * Build the auth array.  May be SuiteCRM specific
	 *
	 * @param none
	 * @return array
	 * ************************************/
	function build_auth_array()
	{
		if( ! isset( $this->username ) )
			throw new Exception( "SOAP username must be set", KSF_FIELD_NOT_SET );
		if( ! isset( $this->password ) )
			throw new Exception( "SOAP Password must be set", KSF_FIELD_NOT_SET );
		$this->soap_auth_array = array(
			'user_name' => $this->username,
			'password' => $this->password );
		return $this->soap_auth_array;
	}
	/*********************************//**
	 * Reconnect.  May be SuiteCRM specific
	 *
	 * @param none
	 * @return none 
	 * ************************************/
	function soapReconnect()
	{
		if( ! isset( $this->soapLoginTime ) )
			throw new Exception( "SOAP Login time must be set.  Since it is done in Login, why are we here and it isnt?", KSF_FIELD_NOT_SET );
		if (time() - $this->soapLoginTime > 600) // 10 minutes
		{
			$this->soapLogin();
		}
	}
	/*********************************//**
	 * Login.  May be SuiteCRM specific
	 *
	 * @param none
	 * @return string
	 * ************************************/
	function soapLogin()
	{
		if( ! isset( $this->appname ) )
			throw new Exception( "SOAP App Name must be set or blank", KSF_FIELD_NOT_SET );
		global $userGUID;
	    	$this->setSoapClient();
		if( ! isset( $this->soap_auth_array ) OR ! isset( $this->soap_auth_array['user_name'] ) )
			$this->build_auth_array();
		$soapLogin = $this->soapClient->login( $this->soap_auth_array, $this->appname, array() );
		$this->session_id  = $soapLogin->id;
		$this->soapLoginTime = time();
		//print "! Successful SOAP login id=" . $this->session_id  . " user=" . $this->soap_auth_array['user_name']. "\n";
		return $this->session_id;
	}

	/*********************************//**
	 * Logout.  May be SuiteCRM specific
	 *
	 * @param none
	 * @return none 
	 * ************************************/
	function soapLogout()
	{
		$this->soapClient->logout(  $this->get( 'session_id' ) );
		//$this->soapClient->logout( array( $this->get( 'session_id' ) ) );
		unset( $this->session_id );
		unset( $this->soapLoginTime );
	}
	/*********************************//**
	 * Call a SOAP function.
	 *
	 * @param operation string name of function to call
	 * @return result stdClass of results from call
	 * ************************************/
	function soapCall( $operation )
	{
		try {
			if( ! isset( $this->soapParams ) OR ! is_array( $this->soapParams ) )
			{
				throw new Exception( "SoapParams must be set", KSF_VALUE_NOT_SET );
			}
			$this->result = $this->soapClient->__soapCall( 	$operation, 
									 $this->soapParams );
									//array( $this->soapParams ) );
			//var_dump( $this->result );
		} catch( Exception $e )
		{
			//Seeing an invalid session ID error.
			throw $e;
		}
		//$result = $this->soapClient->call( $operation, $this->soapParams );
		//
		//if (isset($this->result->error) && $this->result->error['number'] == 10 && $this->retryCount < 5)
		if (isset($this->result->error) )
		{
			$errno = $this->result->error['number'];
			switch( $errno )
			{
				case 10:
					if( $this->retryCount < 5 )
					{
						// Session lost, try to reconnect and retry
						$this->soapReconnect();
						$this->retryCount++;
						$this->result = $this->soapCall($operation);
						$this->retryCount = 0;
					}
					break;
				default:
					break;
			}
		}
		//What about if retrycount, and/or errnum <>10
		return $this->result;
	}
	/*********************************//**
	 * Get the list of functions available.  May be SuiteCRM specific
	 *
	 * @param none
	 * @return array
	 * ************************************/
	function  getFunctions()
	{
		//var_dump( $this->soapClient->__getFunctions() );
		$funcs = $this->soapClient->__getFunctions();
		return $funcs;
	}

}


