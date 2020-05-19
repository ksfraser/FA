<?php


//WC official client
require_once __DIR__ . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

/**********************************************************************************************
*
*	Client Library looks like it takes arrays of data instead of JSON
*
**********************************************************************************************/
class woo_rest 
{
	private $wc;
	private $client;
	/******************************************************************************//**
	 * Create REST client including connection to  WC server.
	 *
	 * \msc
	 * 	Sender,A,B,C,D,Receiver;
	 * 	Receiver<-Sender [label="Command()", URL="\ref Command()"];
	 *    	Receiver->Sender [label="Ack()", URL="\ref Sender::Ack()", ID="1"];
	 *    	A abox B [label="abox", textbgcolour="#ff7f7f"];   
	 *    	B rbox C [label="rbox", textbgcolour="#7fff7f"];   
	 *    	C note D [label="note", textbgcolour="#7f7fff"];
	 *    	
	 * \endmsc
	 *	
	 * [Business Requirement 188](http://mickey.ksfraser.com/infra/software-devel/mantis/view.php?id=188)
	 *
	 * @startuml
	 * [Proto Design] lasts 10 days
	 * [Write Tests] lasts 5 days
	 * [Write Tests] starts at [Proto Design]'s end
	 * @enduml
	 *
	 * @param string Server URL
	 * @param string OAuth Key
	 * @param string OAuth Secret
	 * @param array Options for CURL connection
	 * @param object Calling client
	 * @return none
	 * *******************************************************************************/
	function __construct( $serverURL, $key, $secret, $options = null, $client = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( null != $client )
			$this->client = $client;
        	//Need the index.php since .htaccess changes didn't work
		if( null == $options )
		{
			$options = array(
        			'wp_api' => true, // Enable the WP REST API integration
        			'version' => 'wc/v3', // WooCommerce WP REST API version
				'ssl_verify' => 'false',
				'timeout' => '400',
				'connection_timeout' => '40',
        			//'query_string_auth' => true // Force Basic Authentication as query string true and using under HTTPS
			);
		}

		$this->wc = new Client(
        		$serverURL,
        		$key,
        		$secret,
        		$options
		);
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
	}
	/***********************************//***
	 * Use our client to log messages
	 *
	 * @param string Message
	 * @param string Debug Level
	 * @return BOOL do we have a client or not.
	*************************************/
	function notify( $msg, $level )
	{
		if( isset( $this->client ) )
		{
			$this->client->notify( $msg, $level );
			return TRUE;
		}
		else
		{
			//We don't inherit, so can't log ourselves
			return FALSE;
		}
	}
	/***********************************//***
	 * Use our client to log messages
	 *
	 * @param string REST Endpoint
	 * @param array data to send
	 * @param object Client
	 * @return array response from send update/new
	*************************************/
	/*@array@*/ function send( $endpoint, $data = [], $client )
	{
		$exists = 0;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( null == $client )
			throw new Exception( "These functions depend on CLIENT being set and it isn't.", KSF_VAR_NOT_SET );
		else
			$this->client = $client;
		//check to see if record exists
		try {
			if( isset( $client->id ) )
			{
				//try and match the client against the record in WC
				$response = $this->send_update( $endpoint, $data );
			}
			else
			{
				$response = $this->send_new( $endpoint, $data );
			}
		}
		catch (Exception $e)
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
			throw $e;
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	/******************************************************************************************//**
	* Search WC for a matching item
	* @param string endpoint
	* @param object client to search against
	* @return array of object
	***********************************************************************************************/
	/*@array@*/ private function send_search( $endpoint, $client )
	{
		if(  isset( $client->search_array ) AND is_array( $client->search_array ) )
		{
			foreach( $client->search_array as $search_field )
			{
				//If the client doesn't have the field set that we are to search, then we can't match against it's value...
				if( isset( $client->$search_field ) AND strlen( $client->$search_field ) > 1 )
				{
					$this->notify( __METHOD__ . ":" . __LINE__ . " Searching for match on field: " . $search_field . ":: Value: " . $client->$search_field, "DEBUG" );
					$q = array( 'search' => $client->$search_field );
					$response = $this->get( $endpoint, $q, $client );
					if( $client->fuzzy_match( $response ) )
					{
						$this->notify( __METHOD__ . ":" . __LINE__ . " SUCCESS Leaving " . __METHOD__, "WARN" );
						return $response;
					}
				}
				//Can you have a zero length field in the middle of an array?  I suppose you could have a NULL
			}
			//THIS ISN"T AN EXCEPTION for new - there shouldn't be a match!!
			//$this->notify( __METHOD__ . ":" . __LINE__ . " Throwing Exception " . __METHOD__, "WARN" );
			//throw new Exception( "No Match Found", KSF_NO_MATCH_FOUND );
			return array();
		}
		else
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " Throwing Exception " . __METHOD__, "WARN" );
			throw new Exception( "Search Array not set", KSF_FIELD_NOT_SET );
		}
		//Should be impossible to get here!
		throw new Exception( "We should not be able to reach this point so there is a CODING error" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return array();
	}
	/******************************************************************************************//**
	* Send an item to WC that we haven't sent before (lack of WC ID in our table)
	*
	*	This will catch the case where we have items on WC that match items in FA
	*	but FA doesn't know it created the item.
	*
	*	Assumption ->client is set by calling routine
	*
	* @param string REST endpotin
	* @param array data to send to the endpoint
	* @return EXCEPTION|array of response objects (JSON decoded from WC REST API)
	**********************************************************************************************/
	/*@array@*/ private function send_new( $endpoint, $data = [] )
	{
		$exists = 0;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		try {
			$client = $this->client;
			try {
				$response_arr = $this->send_search( $endpoint, $client );
				//This takes care of the case where we are rebuilding a store that has become
				//disconnected (i.e. items created in the store separate from FA)
		/************************************************/
				if( isset( $response_arr[0] ) )
				{
					$response = $response_arr[0];
					//If there is a match we need to update our tables
					//and then UPDATE WC rather than send new
					if( isset( $response->id ) )
					{
						$client->update_woo_id( $response->id );
						$this->notify( __METHOD__ . ":" . __LINE__ . " SKU already exists!! ", "WARN" );
						//throw new Exception( "Send_Update instead of NEW", KSF_FCN_PATH_OVERRIDE );
						$exists = 1;
					}
					else
					{
						$this->notify( __METHOD__ . ":" . __LINE__ . print_r( $response, true ), "DEBUG" );
					}
				}
			}
			catch( Exception $e )
			{
				$this->notify( __METHOD__ . ":" . __LINE__ . " Throwing " . __METHOD__, "WARN" );
				throw $e;
			}
		}
		catch (Exception $e)
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
			throw $e;
		}
		if( $exists > 0 )
			$act = "put";
		else
			$act = "post";
		$response = $this->$act( $endpoint, $data, $client );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	/******************************************************************************************//**
	* Send updates to an item in WC
	*
	*	This will catch the case where we have items on WC that match items in FA
	*	but FA doesn't know it created the item.
	*
	*	Assumption ->client is set by calling routine
	*
	* @param string REST endpotin
	* @param array data to send to the endpoint
	* @return EXCEPTION|array of response objects (JSON decoded from WC REST API)
	**********************************************************************************************/
	/*@array@*/ private function send_update( $endpoint, $data = [] )
	{
		$exists = 0;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		try {
			$client = $this->client;
			if( isset( $client->id ) )
			{
				//try and match the client against the record in WC
				$response = array();	//WC should be sending back 1 object, whereas fuzzymatch is expecting an array of objects
				$response[] = $this->get( $endpoint . "/" . $client->id, null , $client );

				if( $client->fuzzy_match( $response ) )
				{
					$response = $this->put( $endpoint, $data, $client );
					$this->notify( __METHOD__ . ":" . __LINE__ . " MATCH Leaving " . __METHOD__, "WARN" );
					return $response;
				}
			}
			//If it isn't set, we can't update that item!
		}
		catch (Exception $e)
		{
			$msg =  $e->getMessage();
                        $code = $e->getCode();
                        switch( $code )
                        {
                                case '404': if( false !== strstr( $msg, "woocommerce_rest_product_invalid_id" ) )
                                            {
                                                $this->notify( __METHOD__ . ":" . __LINE__ . " Error " . $code . "::" . $msg . " ::: Woo_ID: " . $client->woo_id, "ERROR" );
					//	if( count( $data ) > 0 )
					//		break;
                                            }
						throw $e;
                                             	break;
				case KSF_LOST_CONNECTION:
					throw $e;
					break;
                                default:
					$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
					throw $e;
                                        break;
                        }

		}
		//If we ended up here, the record on the WC ID we have doesn't the data we have (e.g. sku/slug/description)
		try {
			$response_arr = $this->send_search( $endpoint, $client );
						//This takes care of the case where we are rebuilding a store that has become
						//disconnected (i.e. items created in the store separate from FA)
			if( isset( $response_arr[0] ) )
			{
				$response = $response_arr[0];
				//If there is a match we need to update our tables
				//and then UPDATE WC rather than send new
				if( isset( $response->id ) )
				{
					$client->id = $response->id;
					$response = $this->put( $endpoint, $data, $client );
					if( count( $response ) > 0 )
						$client->update_woo_id( $client->id );
					$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
					return $response;
				}
				else
				{
				}
			}
			else
			{
				//No Match.  Should we be sending NEW instaed of UPDATE??
				$this->notify( __METHOD__ . ":" . __LINE__ . " No MATCHING Response for UPDATE " . __METHOD__, "ERROR" );
				$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
				return array();
			}
		}
		catch( Exception $e )
		{
			switch( $e->getCode() )
			{
				case '400':
					if( stristr( $e->getMessage(), "product_invalid_sku" ) )
					{
						$this->notify( __METHOD__ . ":" . __LINE__ . " Invalid or Dupe SKU: " . $this->sku, "ERROR" );
						//Try again without SKU set for update.
						unset( $data['sku'] );
						return $this->send_update( $endpoint, $data );
					}

				case KSF_NO_MATCH_FOUND:
					$response = $this->post( $endpoint, $data, $client );
					$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
					return $response;
				default:
					$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
					throw $e;
			}
		}
	}
	/************************************************************//**
	*
	*@return array JSON Decoded response from WC API
	*****************************************************************/ 
	function post( $endpoint, $data = [], $client = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( ! is_null( $client ) )
			$this->client = $client;
		try {
			$response = $this->wc->post( $endpoint, $data );
		} catch( Exception $e )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
			throw $e;
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	/************************************************************//**
	*
	*@return array JSON Decoded response from WC API
	*****************************************************************/ 
	function put( $endpoint, $data = [], $client = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( !isset( $this->client ) AND ( ! is_null( $client ) ) )
			$this->client = $client;
		try {
			if( ! isset( $this->client->id ) )
				throw new Exception( "Client ID needed for update (put) not set", KSF_FIELD_NOT_SET );
			$end = $endpoint . "/" . $this->client->id;
			$this->notify( __METHOD__ . ":" . __LINE__ . " Using endpoint: " . $end, "DEBUG" );
			$response = $this->wc->put( $end, $data );
			$this->notify( __METHOD__ . ":" . __LINE__ . " Response from PUT: " . print_r( $response, true ), "DEBUG" );
		} catch( Exception $e )
		{
			$code = $e->getCode();
			$msg = $e->getMessage();
			switch( $code )
			{
				case '400':
					if( false !== stristr( $msg, "woocommerce_product_image_upload_error" ) )
					{
						$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $code . ":" . $msg, "WARN" );
						//complaining about invalid URL. GOOGLE suggests it could be a plugin interfering. 
						// Removing plugins and changing to IP address also didn't make a difference.
						// changing to v2 vice v3 didn't help neither.
						//Remote image doesn't exist?
						return false;	//This isn't a fatal error.
					}
					
					if( stristr( $e->getMessage(), "product_invalid_sku" ) )
					{
						$this->notify( __METHOD__ . ":" . __LINE__ . " Invalid or Dupe SKU: " . $this->sku, "ERROR" );
						//Try again without SKU set for update.
						unset( $data['sku'] );	//DATA is JSON encoded.  Does this do what we intend?
						$response = $this->put( $endpoint, $data, $client );
						return $response;
					}
					 
					$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $code . ":" . $msg, "ERROR" );
					throw $e;
					break;
				case '503':	//503:Error: Briefly unavailable for scheduled maintenance. Check back in a minute. [wp_die]
					if( false !== stristr( $msg, "scheduled maintenance" ) )
					{
					/*
						sleep( 600 );
						$this->recursive_call++;
						$response = $this->put( $endpoint, $data, $client );
						$this->recursive_call--;
						return $response;
					*/
					}
					$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $code . ":" . $msg, "ERROR" );
					throw $e;
					break;
				case KSF_LOST_CONNECTION:
					throw $e;
					break;
				default:
					$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $code . ":" . $msg, "ERROR" );
					throw $e;
					break;
			}
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	/************************************************************//**
	*
	*@return array JSON Decoded response from WC API
	*****************************************************************/ 
	/*@array@*/ function get( $endpoint, $data = [], $client = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( null === $data )
			$data = array();
		if( !isset( $this->client ) AND ( ! is_null( $client ) ) )
		{
			$this->client = $client;
			$this->notify( __METHOD__ . ":" . __LINE__ . " ->client wasn't set. (Used for ->notify)", "WARN" );
		}
		try {
			$this->notify( __METHOD__ . ":" . __LINE__ . " USING endpoint: " . $endpoint, "DEBUG" );
			$response = $this->wc->get( $endpoint, $data );
		} catch( Exception $e )
		{
			$code = $e->getCode();
			$msg = $e->getMessage();
			switch( $code )
			{
				case '404':
					// ERROR 404:Error: Invalid ID. [woocommerce_rest_product_invalid_id]^	
					if( false !== stristr( $msg, "woocommerce_rest_product_invalid_id" ) )
					{
						$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $code . ":" . $msg, "WARN" );
						$this->client->update_woo_id( "" );
						//return false;	//This isn't a fatal error.
						return array();
					}
				case '0':
					if( false !== stristr( $msg, "cURL Error: Operation timed out" ) )
					{
						$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $code . ":" . $msg, "WARN" );
						$req = $this->wc->http->getRequest();
						throw new Exception( "CURL packed it in on WC_id " . $this->client->id . ":: URL " . print_r( $req->getUrl(), true), KSF_LOST_CONNECTION );
					}
				default:
					$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $code . ":" . $msg, "NOTIFY" );
					throw $e;
			}
			//$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
			//$this->notify( __METHOD__ . ":" . __LINE__ . " CLIENT " . print_r( $this, true ), "DEBUG" );
		//	$this->notify( __METHOD__ . ":" . __LINE__ . " CLIENT " . print_r( $this->client, true ), "DEBUG" );
			throw $e;
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	/************************************************************//**
	*
	*@return array JSON Decoded response from WC API
	*****************************************************************/ 
	function list_all( $endpoint, $data = [], $client = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( !isset( $this->client ) AND ( ! is_null( $client ) ) )
			$this->client = $client;
		try {
			$response = $this->wc->get( $endpoint, $data );
		} catch( Exception $e )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
			throw $e;
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	/************************************************************//**
	*
	*@return array JSON Decoded response from WC API
	*****************************************************************/ 
	function retreive_one( $endpoint, $data = [], $client = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( !isset( $this->client ) AND ( ! is_null( $client ) ) )
			$this->client = $client;
		if( ! isset( $this->client->id ) )
			throw new Exception( "ID not set so can't search for item", KSF_VALUE_NOT_SET );
		try {
			$response = $this->wc->get( $endpoint . "/" . $this->client->id, $data );
		} catch( Exception $e )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
			throw $e;
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	/************************************************************//**
	*
	*@return array JSON Decoded response from WC API
	*****************************************************************/ 
	function delete( $endpoint, $data = [], $client = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( ! is_null( $client ) )
			$this->client = $client;
		try {
			$response = $this->wc->delete( $endpoint, $data );
		} catch( Exception $e )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " ERROR " . $e->getCode() . ":" . $e->getMessage(), "ERROR" );
			throw $e;
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;

	}
	function data_not_json( $data )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( is_object( $data ) )
			throw new Exception( "Object, not data", KSF_INVALID_DATA_TYPE );
		$decoded = json_decode( $data, true );
		if( JSON_ERROR_NONE == json_last_error() )
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
			return $decoded;
		}
		else
		{
			$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
			return $data;
		}
	}
	function dispatch( $data, $c_type, $client = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		try {
			$data = $this->data_not_json( $data );
		} catch( Exception $e )
		{
			if( $e->code ==  KSF_INVALID_DATA_TYPE )
			{
				//convert the object so we can use it!
			}
		}
		if( strncasecmp( "post", $c_type ) == 0 )
			$response = $this->post(  $data, $c_type, $client = null  );
		else
		if( strncasecmp( "put", $c_type ) == 0 )
			$response = $this->put( $data, $c_type, $client = null  );
		else
		if( strncasecmp( "delete", $c_type ) == 0 )
			$response = $this->delete( $data, $c_type, $client = null  );
		else
		if( strncasecmp( "get", $c_type ) == 0 )
			$response = $this->get( $data, $c_type, $client = null  );
		else
			throw new Exception( "Invalid c_type" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );;
		return $response;
	}
	/************************************************************
	*
	*	The following are for class compatibility in case clients
	*	expect the functions
	************************************************************/
	
 	function set_content_type( $type ){}
        function buildURL(){}
        function write2woo_json( $json_data, $c_type, $client = null ){ $this->dispatch( $json_data, $c_type, $client ); }
        function write2woo_object( $c_obj, $c_type, $client = null ){ $this->dispatch( $c_obj, $c_type, $client ); }
        /*@string@*/function write2woo( $c_type = "POST", $client = null ){ $this->dispatch( null, $c_type, $client ); }
	/***************************************************************
	*	END COMPAT
	************************************************************/
}
?>
