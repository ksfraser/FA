<?php

error_reporting( E_ALL );
ini_set("display_errors", 1);
require_once( dirname(__FILE__) . '/class.base.php' );

/**
 * 	20160925 KSF
 *	Added some code inspired by WOOCOMMERCE APIs to handle
 *	OAuth processing
 *	Adding extra info on response from server
 *	Cleaning JSON responses in case plugins add extra crap in body
 * **/

class request extends base
{
	var $curl_handle;	//!< Curl Handle
	var $debug;
	var $URL;
	var $method;
	var $params;
	var $headers;
	var $body;	//!< prep sets this
	var $data;	//!< client (write2woo_object) sets this
	var $json_data;	//!< client (write2woo_json) sets this 
	var $curlopts;	//!< array of options to be set in CURL
	var $duration; //!< How long did the query take

	/**************************************************************************************************//**
	 *	Constructor
	 *
	 *	@param URL
	 *	@param method (POST/PUT/GET/DELETE/OPTION
	 *	@param parameters array
	 *	@param headers (CURL HEADERS) array
	 *	@param data array NOT JSON encoded.
	 *	@return NULL
	 * ****************************************************************************************************/
	function __construct( $URL = '', $method = "POST", $parameters = [], $headers = [], /*NOT JSON*/$data = [] )
	{
	        $this->URL        = $URL;
	        $this->method     = $method;
	        $this->params = $parameters;
	        $this->headers    = $headers;
		$this->data       = $data;
		$this->json_data = null;
		$this->curl_handle = curl_init( $this->URL );
		//save response headers
		$this->curl_setopt( CURLOPT_HEADERFUNCTION, array( $this, 'curl_stream_headers' ) );
		return;
	}
	function encode()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->json_data = json_encode( $this->data );
	}
	/**
	 * Save the cURL response headers for later processing
	 *
	 * @since 2.0
	 * @see WP_Http_Curl::stream_headers()
	 * @param object $_ the cURL resource handle (unused)
	 * @param string $headers the current response headers
	 * @return int the size of the processed headers
	 */
	function curl_stream_headers( $_, $headers ) {

		$this->curl_headers .= $headers;
		return strlen( $headers );
	}
	function param2string()
	{
		$count = 0;
		$str = '';
		foreach ($this->params as $k=>$v)
		{
			if( $count > 0 )
			{
				$str .= "&" . $k . "=" . $v;
			}
			else
			{
				$str .= $k . "=" . $v;
				$count++;
			}
		}
		return $str;
	}
	function prep()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->body = null;
		$this->curl_setopt( CURLOPT_HTTPHEADER, $this->headers );
		$this->curl_setopt( CURLOPT_CUSTOMREQUEST, $this->method );
		switch ( $this->method ) {
			case 'GET':
				if( empty( $this->params ) )
					$this->params = (array) $this->data;
				//$this->body = null;
				$this->curl_setopt( CURLOPT_POST, FALSE ); //from write2woo
				//How do we send data via GET?
				/*params not being attached to URL...
				//do we need to http_build_query before setting body?
				$this->body = $this->params;
				$this->curl_setopt( CURLOPT_POSTFIELDS, http_build_query($this->body) );
				 */
				$this->curl_setopt( CURLOPT_POSTFIELDS, http_build_query( $this->params ) );
				$paramstring = $this->param2string();
				if( strpos( $this->URL, "?" ) > 0 )
					$this->URL .= "&" . $paramstring;
				else
					$this->URL .= "?" . $paramstring;
			break;
			case 'PUT':
				$this->body = json_encode( $this->data );
				$this->curl_setopt( CURLOPT_POSTFIELDS, $this->body );
				$this->curl_setopt(CURLOPT_PUT, 1); //from write2woo
			break;
			case 'POST':
				//do we need to http_build_query on ->data before setting body?
				if( isset( $this->json_data ) )
					$this->body = $this->json_data;
				else
					$this->body = json_encode( $this->data );
				$this->curl_setopt( CURLOPT_POST, TRUE );
				$this->curl_setopt( CURLOPT_POSTFIELDS, $this->body );
			break;
			case 'DELETE':
				$this->body = null;
				$this->params = (array) $this->data;
				if( isset( $this->params['force'] ) )
					if( TRUE === $this->params['force'] )
						$this->params['force'] = 'true';	//Need the string, not the bool
				break;
			case 'OPTIONS':
				break;
			default:
				$this->params = null;
				break;
		}
		$this->curl_setopt(CURLOPT_HEADER, FALSE);	//from write2woo
		$this->request_curlopts();
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
	function request_curlopts()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		//echo "<br /><br />" . __METHOD__  . ":" . __LINE__ . " Curlopts: <br />";
		//var_dump( $this->curlopts );
		foreach( $this->curlopts as $key=>$value )
		{
			$res = $this->curl_setopt( $key, $value );
			if( FALSE == $res )
				display_error( "CURL error for " . $var1 . " with value " . $var2 );
		}
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
	function curl_setopt( $key, $value )
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
			return curl_setopt( $this->curl_handle, $key, $value );
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
	/*@class response@*/function curl_exec()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->prep();
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$response = new response( $this->curl_handle, null, null, null );
		$response->debug = $this->debug;
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		// blank headers
		$this->curl_headers = '';

		$start_time = microtime( true );
		if( ! $ret = curl_exec($this->curl_handle) )
	      	{
			$response->body =  "Error returned by CURL: " . curl_error($this->curl_handle);
			$response->curlinfoarray =  curl_getinfo($this->curl_handle);
			$this->duration = round( microtime( true ) - $start_time, 5 );
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	      	}
	      	else
		{
			$this->duration = round( microtime( true ) - $start_time, 5 );
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
			$response->curlinfoarray =  curl_getinfo($this->curl_handle);
			$response->fullresponse = $ret;
/**20160925 START**/
		       /**20160925 New code within the fcn to return more info**/
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		       $response->decode();

		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
/**20160925 END**/
		}
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		return $response;
	}

}
class response extends base
{
	var $curl_handle;	//!< Curl Handle passed in by REQUEST so we can get info
	var $curlinfoarray;	//!< Curl response info ARRAY
	var $code;
	var $headers;
	var $body;
	var $debug;
	var $http_code;	//!< HTTP Code returned by the server.  Anything not 2XX is an error
	var $fullresponse;	//!< Raw response
	var $cleanedresponse;	//!< Raw response trimmed of crap that PLUGINS may have added
	var $json_decode_as_array;
	function __construct( $curl_handle, $code = 0, $headers = [], $body = [])
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->curl_handle = $curl_handle;
		$this->json_decode_as_array = FALSE;
		$this->code = $code;
		$this->headers = $headers;
		$this->body = $body;
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
	/*@bool@*//*TRUE*/function clean_response()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		//WP plugins modify JSON responses which break the JSON
		$raw = $this->fullresponse;
		$json_start = strpos( $raw, '{' );
		$json_end = strrpos( $raw, '}' ) + 1; // inclusive

		$this->cleanedresponse = substr( $raw, $json_start, ( $json_end - $json_start ) );
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		return TRUE;
	}
	function decode()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->http_code = curl_getinfo( $this->curl_handle, CURLINFO_HTTP_CODE );
		$this->curlinfoarray = curl_getinfo( $this->curl_handle);
		$this->clean_response();
		if( null == $this->cleanedresponse )
		{
		}
		else
		{
			$this->body = json_decode( $this->cleanedresponse, $this->json_decode_as_array );
			$this->get_response_headers();
		}
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
	/**
	 * Parse the raw response headers into an assoc array in format:
	 * {
	 *   'Header-Key' => header value
	 *   'Duplicate-Key' => array(
	 *     0 => value 1
	 *     1 = value 2
	 *   )
	 * }
	 *
	 * @since 2.0
	 * @see WP_HTTP::processHeaders
	 * @return array
	 */
	protected function get_response_headers() {
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);

		// get the raw headers
		$raw_headers = preg_replace('/\n[ \t]/', ' ', str_replace( "\r\n", "\n", $this->fullresponse ) );

		// spit them
		$raw_headers = array_filter( explode( "\n", $raw_headers ), 'strlen' );

		$headers = array();

		// parse into assoc array
		foreach ( $raw_headers as $header ) {

			// skip response codes (appears as HTTP/1.1 200 OK or HTTP/1.1 100 Continue)
			if ( 'HTTP/' === substr( $header, 0, 5 ) ) {
				continue;
			}

			list( $key, $value ) = explode( ':', $header . ":", 2 );

			if ( isset( $headers[ $key ] ) ) {

				// ensure duplicate headers aren't overwritten
				$headers[ $key ] = array( $headers[ $key ] );
				$headers[ $key ][] = $value;

			} else {
				$headers[ $key ] = $value;
			}

		}
		//$this->response->headers = $headers;

		$this->headers = $headers;
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
}

/**********************************************************************************************
 *
 *	Assumes OAUTH for authentication
 *	Assumes JSON for data transport
 *
 *	Equivalent to HttpClient from WooCommerce api
 *
 * *******************************************************************************************/

class rest_client extends base
{
	var $curl_handle;	//!< Curl Handle
	var $curl_headers;
	var $referer_URL;
	var $URL;		//!< Store API URL
	var $fields = array();	//fields that can be sent to the receiving app
	var $data = array();
	var $responseInfo;
	var $responseHeaders;	//!< @var String to hold response Headers
	var $request;		//!< Class to hold request data
	var $response;  	//!< Class to hold response data
	var $consumer_key;	//!< oAuth Consumer Key
	var $consumer_secret; 	//!< oAuth Consumer secret
	var $options;		//!< Options for setting things up.
	var $APIversion;	//!< API version is part of the rest path
	var $WPAPI;		//!< WPAPI is use WP built in REST - affects rest path
	var $curlopts;		//!< array of options to be sent to CURL
	var $params;
	//HASH_ALGORITHM in base

	/********************************************************************************//**
	 *
	 * for oauth we should have the following passed in in the args:
	 * 	consumer_key
	 * 	consumer_secret
	 * 	URL
	 * for REST connection need the following in the args:
	 * 	method
	 * 	data
	 * 	params (get)
	 *
	 *
	 * ********************************************************************************/
	function __construct( /*array*/ $args = null )
	{

		$this->curl_handle = curl_init();
		$this->params = array();
		//Set defaults that can be overridden in the args array
                $this->curlopts[CURLOPT_AUTOREFERER] =TRUE;
        //      $this->curlopts[CURLOPT_FOLLOWLOCATION] =TRUE;
                $this->curlopts[CURLOPT_HEADER] =TRUE;
                $this->curlopts[CURLOPT_RETURNTRANSFER] =true;	//sets response in a $result variable rather than echo
       	//      $this->curlopts[CURLOPT_CONNECTTIMEOUT] =100;
	//	$this->curlopts[CURLOPT_TIMEOUT] =100;
	/*
			CURLOPT_VERBOSE, $level);
	     		CURLOPT_URL, $url );
			CURLOPT_REFERER, $url );
			CURLOPT_HTTPHEADER, http_build_query($data) );
	 */

		//set request header
		//$this->curlopts[CURLOPT_HTTPHEADER] =$this->request->headers;
		//echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " set request curlopts REST_CLIENT<br />";
		//var_dump( $this->curlopts );
		$this->parse_args( $args );
		$this->request = $this->createRequest();
	}
	function createRequest()
	{
		if( empty( $this->headers ) )
		{
			$this->headers = array(
				'Accept: application/json',
				'Content-Type: application/json',
				'User-Agent: KSF API Client-PHP',
			);
			//can set 'Content-Length: ' . strlen($data_string)) in headers
			//but it is auto-set according to user's documentation
		}
					//$URL = '', $method = "POST", $parameters = [], $headers = [], $body = []
		$request = new request( $this->URL, $this->method, $this->params, $this->headers, $this->data );
		$request->debug = $this->debug;
		$request->curlopts = $this->curlopts;
		//echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " set request curlopts REST_CLIENT<br />";
		//var_dump( $this->curlopts );
		//echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " set request curlopts REQUEST<br />";
		//var_dump( $request->curlopts );
		return $request;
	}
	function build_URL_Query( $url, $parameters = [])
	{
		if( !empty( $parameters ) )
			$url .= "?" . http_build_query( $parameters );
		return $url;
	}
	/**********************************************************************
	 *
	 * Options is an array of options so needs to be handled recursively
	 * CURLOPT values need to be passed to curl_setopt later
	 *
	 * *******************************************************************/
	function parse_args( /*array*/$args )
	{
		//if( null == $args OR ! is_array( $args ) )
		//	return;
		//echo "<br />" . __FILE__ . ":" . __LINE__ . ":" . __METHOD__ . "<br />";
		//var_dump( $args );
		foreach( $args as $key=>$value )
		{
			if( $key == "options" )
			{
				$this->parse_args( $value );
			}
			else
			{
				if( strncmp( $key, "CURLOPT", 7 ) == 0 )
				{
					foreach( $value as $k=>$v )
						$this->curlopts[$k] = $v; 
				}
				else
					$this->$key = $value;
			}
		}
		//echo "<br />" . __FILE__ . ":" . __LINE__ . ":" . __METHOD__ . "<br />";
		//var_dump( $this->request );
	}
	/* MOVED
	function request_curlopts()
	{
		$this->request->request_curlopts();
	}
	 */
	function curl_setopt( $handle, $var1, $var2 )
	{
		$res = curl_setopt($handle, $var1, $var2);
		if( FALSE == $res )
			display_error( "CURL error for " . $var1 . " with value " . $var2 );
	}
	function __destruct()
	{
		
		if( null != $this->curl_handle )
		{
			curl_close ($this->curl_handle);
			$this->curl_handle = null;
		}
		 
	}
	function usecookies( $cookiefile )
	{
                curl_setopt($this->curl_handle, CURLOPT_COOKIEJAR, $cookiefile);  //initiates cookie file if needed
                curl_setopt($this->curl_handle, CURLOPT_COOKIEFILE, $cookiefile);  // Uses cookies from previous session if exist
	}
	/**20160925 START**/
	/*********************************************************************************
	 *
	 *	parameters for oAuth 1.0a
	 *	REQUIRES consumer_key
	 *
	 * ********************************************************************************/
	function get_oauth_params( $params, $method )
	{
		$params = array_merge( $params, array(
			'oauth_consumer_key'     => $this->consumer_key,
			'oauth_timestamp'        => time(),
			'oauth_nonce'            => sha1( microtime() ),
			'oauth_signature_method' => 'HMAC-' . self::HASH_ALGORITHM,
		) );

		// the params above must be included in the signature generation
		$params['oauth_signature'] = $this->generate_oauth_signature( $params, $method );

		return $params;

	}
	/*********************************************************************************
	 *
	 *	Generate the oAuth signature
	 *	REQUIRES consumer_secret
	 *
	 * ********************************************************************************/
	function generate_oauth_signature( $params, $http_method )
	{
		$base_request_uri = rawurlencode( $this->URL );
		
		if ( isset( $params['filter'] ) ) {
			$filters = $params['filter'];
			unset( $params['filter'] );
			foreach ( $filters as $filter => $filter_value ) {
				$params['filter[' . $filter . ']'] = $filter_value;
			}
		}
		
		// normalize parameter key/values and sort them
		$params = $this->normalize_parameters( $params );
		uksort( $params, 'strcmp' );

		// form query string
		$query_params = array();
		foreach ( $params as $param_key => $param_value ) {
			$query_params[] = $param_key . '%3D' . $param_value; // join with equals sign
		}

		$query_string = implode( '%26', $query_params ); // join with ampersand

		// form string to sign (first key)
		$string_to_sign = $http_method . '&' . $base_request_uri . '&' . $query_string;

		return base64_encode( hash_hmac( self::HASH_ALGORITHM, $string_to_sign, $this->consumer_key, true ) );
	}
	/***********************************************************************************************
	 * Normalize each parameter by assuming each parameter may have already been
	 * encoded, so attempt to decode, and then re-encode according to RFC 3986
	 *
	 * Note both the key and value is normalized so a filter param like:
	 *
	 * 'filter[period]' => 'week'
	 *
	 * is encoded to:
	 *
	 * 'filter%5Bperiod%5D' => 'week'
	 *
	 * This conforms to the OAuth 1.0a spec which indicates the entire query string
	 * should be URL encoded
	 * @param array $parameters un-normalized pararmeters
	 * @return array normalized parameters
	 */
	private function normalize_parameters( $parameters ) {

		$normalized_parameters = array();

		foreach ( $parameters as $key => $value ) {

			// percent symbols (%) must be double-encoded
			$key   = str_replace( '%', '%25', rawurlencode( rawurldecode( $key ) ) );
			$value = str_replace( '%', '%25', rawurlencode( rawurldecode( $value ) ) );

			$normalized_parameters[ $key ] = $value;
		}

		return $normalized_parameters;
	}
	/*****************************************************************************************
	 *
	 *	Are we connecting to the server via ssl
	 *
	 * ***************************************************************************************/
	public function is_ssl() {
		return substr( $this->URL, 0, 5 ) === 'https';
	}
	/**20160925 END**/
	/**********************************************************************************************
	 *
	 *	In the code we copied from, the AUTH method was called in here as a new class,
	 *	checked for SSL, and returned the params (get_oauth_params below...).  However
	 *	it was written in such a way that ONLY oAuth was used so we refactored.
	 *
	 *	This function could be written so that a caller specified auth method was used.
	 *
	 * *******************************************************************************************/
	/*@URL@*/function curl_setAuth()
	{
		//To make the client able to use future authentication methods, we should set
		//it up to call depending on that method.  Either a class passed in, or at least
		//an indicator...
		//
		//	$authclass = "authenticator_class_" . $auth_type;
		//	$auth = new $authclass( $username, $password, $options, $params)
		//and let the auth class do what it must.

		//Even though the class is designed on the assumption we are using oAuth
		//in case we aren't, or something went wrong in the caller.
		if( isset( $this->consumer_key ) )
		{
			$this->username = $this->consumer_key;
			$this->password = $this->consumer_secret;
		}
		if( isset( $this->username ) && isset( $this->password ) )
		{
			if( $this->is_ssl() )
			{
				$this->params = array_merge( $this->params, array(
					'consumer_key'    => $this->username,
					'consumer_secret' => $this->password,
				) );

			} else {
				$this->params = array_merge( $this->params, 
						$this->get_oauth_params( 
							$this->request->params, 
							$this->request->method ) );
			
			}
			 $query_params = http_build_query( $this->params );

			//curl_setopt($this->curl_handle, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			$this->request->curl_setopt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$this->request->curl_setopt(CURLOPT_USERPWD, $this->username . ":" . $this->password);
		}
		return $this->URL .  '?' . $query_params;
		//return $this->URL;
	}
	/**********!deprec************************************************************/
	function curl_prepHeader( /*$data*/ )
	{
	         //$this->curl_setopt($this->curl_handle, CURLOPT_HTTPHEADER, $data );
	}
	/*************************************************************************************************//**
	 *	Perform the steps to send the request and receive the response
	 *
	 *	@return bool Success or Fail
	 *
	 * ***************************************************************************************************/
	/*@bool@*/function curl_exec()
	{
		if( $this->debug == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		//$this->request_curlopts();
		//$this->request->prep();
		$this->request->URL = $this->curl_setAuth();
		$this->request->curl_setopt( CURLOPT_URL, $this->request->URL );

		$this->response = $this->request->curl_exec();
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		return TRUE;
	}
/**20160925 START**/

/**20160925 END**/
	function array2curlarray( $in_arr )
	{
		$out_array = array();
		foreach( $in_arr as $key => $val )
		{
			$out_array[] = $key . ": " . $val;
		}
		return $out_array;
	}
	
/**20160925 START**/
	/**
	 * JSON decode the response body after stripping any invalid leading or
	 * trailing characters.
	 *
	 * Plugins (looking at you WP Super Cache) or themes
	 * can add output to the returned JSON which breaks decoding.
	 *
	 * @since 2.0
	 * @param string $raw_body raw response body
	 * @return object|array JSON decoded response body
	 */
	function get_parsed_response( $raw_body ) {

		$json_start = strpos( $raw_body, '{' );
		$json_end = strrpos( $raw_body, '}' ) + 1; // inclusive

		$json = substr( $raw_body, $json_start, ( $json_end - $json_start ) );

		return json_decode( $json, $this->json_decode_as_array );
	}


	/**
	 * Build the result object/array
	 *
	 * @since 2.0.0
	 * @param object|array JSON decoded result
	 * @return object|array in format:
	 * {
	 *  <result data>
	 *  'http' =>
	 *   'request' => stdClass(
	 *     'url' => request URL
	 *     'method' => request method
	 *     'body' => JSON encoded request body entity
	 *     'headers' => array of request headers
	 *     'duration' => request duration, in seconds
	 *     'params' => optional raw params
	 *     'data' => optional raw request data
	 *     'duration' =>
	 *    )
	 *   'response' => stdClass(
	 *     'body' => raw response body
	 *     'code' => HTTP response code
	 *     'headers' => HTTP response headers in assoc array
	 *   )
	 * }
	 */
	function build_result( $parsed_response ) {

		// add cURL log, HTTP request/response object
		if ( $this->debug ) {

			if ( $this->json_decode_as_array ) {

				$parsed_response['http'] = array(
					'request'  => json_decode( json_encode( $this->request ), true ),
					'response' => json_decode( json_encode( $this->response ), true ),
				);

			} else {

				$parsed_response->http = new stdClass();
				$parsed_response->debug = $this->debug;
				$parsed_response->http->request = $this->request;
				$parsed_response->http->response = $this->response;
			}
		}

		return $parsed_response;
	}

/**20160925 END**/
	//All of the library we are cloning functions have the same pattern:
	//	Set method (set_request_args)
	//	Set path (can be array)
	//	set Body
	//	call do_request
	//		call make_api_call( method, path, data (body/param) )
}

?>
