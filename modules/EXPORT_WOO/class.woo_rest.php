<?php


require_once( __DIR__ . '/../ksf_modules_common/class.rest_client.php' ); 

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
	function __construct( $serverURL, $key, $secret, $options = null, $client = null )
	{
		if( null != $client )
			$this->client = $client;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
        	//Need the index.php since .htaccess changes didn't work
		if( null == $options )
		{
			$options = array(
        			'wp_api' => true, // Enable the WP REST API integration
        			'version' => 'wc/v3', // WooCommerce WP REST API version
				'ssl_verify' => 'false',
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
	*************************************/
	function notify( $msg, $level )
	{
		if( isset( $this->client ) )
			$this->client->notify( $msg, $level );
	}
	function send( $endpoint, $data = [], $client )
	{
		$exists = 0;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( null == $client )
			throw new Exception( "These functions depend on CLIENT being set and it isn't.", KSF_FIELD_NOT_SET );
		else
			$this->client = $client;
		//check to see if record exists
		try {
			if( isset( $client->id ) )
			{
				//try and match the client against the record in WC
				//No need to search if we have the ID
				$response = $this->get( $endpoint . "/" . $client->id, null , $client );
				$this->notify( __METHOD__ . ":" . __LINE__ . " Now we need to match the returned item on the CLIENT:" . print_r( $response, true ), "WARN" );
				if( $client->fuzzy_match( $response ) )
				{
					$exists++;
				}
				
			}
			if( ($exists < 1) AND isset( $client->search_array ) AND is_array( $client->search_array ) )
			{
				foreach( $client->search_array as $search_field )
				{
					if( isset( $client->$search_field ) AND strlen( $client->$search_field ) > 1 )
					{
						$this->notify( __METHOD__ . ":" . __LINE__ . " Searching for match on " . $client->$search_field, "WARN" );
						$q = array( 'search' => $client->$search_field );
						$response = $this->get( $endpoint, $q, $client );
						//Does name and description match?
						if( $client->fuzzy_match( $response ) )
						{
							$exists++;
							break; //Don't need to keep searching.  fuzzy sets the ID so put should work
						}
					}
				}
			}
		}
		catch (Exception $e)
		{
			if( $e->getCode() !== KSF_INVALID_DATA_TYPE )
			{
				if( $e->getCode() == KSF_VALUE_NOT_SET )
					var_dump( $response );
				throw $e;
			}
		}
		if( $exists > 0 )
			$act = "put";
		else
			$act = "post";
		//if( isset( $client->system_of_record ) AND ( true == $client->system_of_record ) )
		//
		//Update or Insert
		$response = $this->$act( $endpoint, $data, $client );

		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	function post( $endpoint, $data = [], $client = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( ! is_null( $client ) )
			$this->client = $client;
		try {
			$response = $this->wc->post( $endpoint, $data );
		} catch( Exception $e )
		{
			throw $e;
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	function put( $endpoint, $data = [], $client = null )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( !isset( $this->client ) AND ( ! is_null( $client ) ) )
			$this->client = $client;
		try {
			if( ! isset( $this->client->id ) )
				throw new Exception( "Client ID needed for update (put) not set", KSF_FIELD_NOT_SET );
			$response = $this->wc->put( $endpoint . "/" . $this->client->id, $data );
		} catch( Exception $e )
		{
			throw $e;
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $response;
	}
	function get( $endpoint, $data = [], $client = null )
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

//class EXPORT_WOO
class woo_rest_old extends rest_client
{
	var $loggedin;
	var $login_url;
	var $home_url;
	var $environment;
	var $grant_type;
	var $paypal_user;
	//var $URL;	//In base class
	var $server_url;
	var $transactionID;
	var $refundID;
	var $invoiceID;
	var $payment_path = "/v1/payments/payment";
	var $sale_path;		// /v1/payments/sale/<Transaction-Id>;
	var $refund_path;	// /v1/payments/sale/<Transaction-Id>/refund/;
	var $invoice_path = "/v1/invoicing/invoices";
	var $invoice_send;	// /v1/invoicing/invoices/<Invoice-Id>/send;
	var $invoice_remind;	// /v1/invoicing/invoices/<Invoice-Id>/remind;
	var $include_header;
	var $maxrowsallowed;
	var $lastoid;
	var $mailto;
	var $db;
	var $woo_rest_path;
	var $serverURL;
	var $URL;
	var $subpath;		//!< rest API endpoint
	var $data;
	var $authdata;
	var $conn_type;
	var $content_type;
	var $header_array;	//no longer has effect but can't change interface without breaking all callers.
	var $json_data;
	var $args;	//!< array for the constructor of the parent

	function __construct( $serverURL = "http://fhsws001.ksfraser.com/devel/fhs/wordpress", $subpath = "products", $data_array, $key = "ck_39bb90d3194c955e3ff2f8a0673d5735e3834785", $secret = "cs_c60df3106af131d3698bc6c91392c6ef65e17a53", $conn_type = "POST", $woo_rest_path =  "/wp-json/wc/v1/", /*deprec*/$header_array = null, $environment = "devel", $debug = 0 )
	{
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		//$this->set_var( 'vendor', "EXPORT_WOO" );
		$this->username = $key;
		$this->password = $secret;
		$this->woo_rest_path = $woo_rest_path;	//AFTER server-woocommerce path
		$this->serverURL = $serverURL;
		$this->subpath = $subpath;
		$this->data_array = $data_array;
		$this->conn_type = $conn_type;
		$this->header_array = $header_array;
		$this->environment = $environment;

		$this->loggedin = FALSE;
		$this->environment = $environment;
		$this->grant_type="client_credentials";
		$this->debug = $debug;
		$this->buildURL();	//makes $this->URL

		$args = array();
		$args['URL'] = $this->URL;
		$args['consumer_key'] = $key;
		$args['consumer_secret'] = $secret;
		$args['data'] = $data_array;
		$args['method'] = $conn_type;
		$args['CURLOPT_COOKIEJAR'][CURLOPT_COOKIEJAR] = "my_cookies.txt";
		$args['CURLOPT_COOKIEFILE'][CURLOPT_COOKIEFILE] = "my_cookies.txt";
		if( strncasecmp( $this->environment, "PROD", 4 ) == 0 )
		{
			$args['CURLOPT_VERBOSE'] = 0;
		}
		else
		{
			$args['CURLOPT_VERBOSE'][CURLOPT_VERBOSE] = 1;
			$args['CURLOPT_SSL_VERIFYPEER'][CURLOPT_SSL_VERIFYPEER] = FALSE;
			$args['CURLOPT_SSL_VERIFYHOST'][CURLOPT_SSL_VERIFYHOST] = FALSE;
		}
		$this->args = $args;
		parent::__construct( $args );
		//$this->curl_setAuth();	//parent::__construct
	
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
	}
	/* NOT CALLED internally
	function set_content_type( $type )
	{
		$this->content_type = $type;
	}
	 */
	function buildURL()
	{
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		$this->URL = $this->serverURL . $this->woo_rest_path . $this->subpath;
		//When we are called a second time within the same instance of an object
		//we will need to update the URL of the request.  That is why we would
		//be in this routine again...
		if( isset( $this->request->URL ) )
		{
			if( $this->debug == 1 )
			{
				display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
			}
			$this->request->URL = $this->URL;
		}
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
	}
	/******************************************************************************
	 * 
	 * Inspired by httpclient from woocommerce api client
	 * Should be able to call the following with
	 *   ($endpoint, http_build_query($this), $this)
	 *
	 * **************************************************************************/
	function post( $endpoint, $data = [], $client )
	{
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		$this->subpath = $endpoint;
		$this->buildURL();
		//ASSUMING not JSON
		$this->request->data = $data;
		$this->write2woo( "POST", $client );
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
	}
	function put($endpoint, $data = [], $client )
	{
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		$this->subpath = $endpoint;
		$this->buildURL();
		//ASSUMING not JSON
		$this->request->data = $data;
		$this->write2woo( "PUT", $client );
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
	}
	function get($endpoint, $data = [], $client )
	{
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		$this->subpath = $endpoint;
		$this->buildURL();
		$this->request->params = $data;
		$this->write2woo( "GET", $client );
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
	}
	function delete($endpoint, $data = [], $client )
	{
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		$this->subpath = $endpoint;
		$this->buildURL();
		$this->request->params = $data;
		$this->write2woo( "DELETE", $client );
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
	}
	/*********************************************************************************
	 *  !should be able to call...
	 * ******************************************************************************/
	function write2woo_json( $json_data, $c_type, $client = null )
	{
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		$this->request->json_data = $json_data;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		return $this->write2woo( $c_type, $client );
	}
	function write2woo_object( $c_obj, $c_type, $client = null )
	{
		$this->request->data = http_build_query($c_obj);	//http_build_query would take an object and 
									//build an array of the public variables
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		return $this->write2woo( $c_type, $client );
	}
	/*@string@*/function write2woo( $c_type = "POST", $client = null )
	{
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		$this->request->method = $c_type;

		if( $this->debug >= 2 )
		{
			echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
			var_dump( $this->request );
		}
		if( $this->curl_exec() )	//parent (rest_client)
		{
			if( null != $client )
			{
				$client->request = $this->request;
				if( isset( $this->response->body->code ) )
					$client->code = $this->response->body->code;
				//else
				//	$client->code = $this->response->curlinfoarray->code;
				$client->response = $this->response;
				if( isset(  $this->response->body->message ) )
				{
					$client->message = $this->response->body->message;
				}
			}
			$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
			return $this->response->body;
		}
		else
		{
			display_error( "Curl Exec failed::" . $this->errmsg );
			//return FALSE;
			$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
			return "Curl Exec failed";
		}
	}

}
?>
