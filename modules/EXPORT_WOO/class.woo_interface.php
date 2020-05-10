<?php

/***************************************
****************************************
*
*	Trying to refactor into MVC
*	copied VIEW code into view.woo.php class view_woo
*
****************************************
****************************************/

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

/***********
*
*	TODO: 	Refactor so that not inheriting table_interface
		Refactor table_interface so that it uses INTERFACE standards
*/

/*********************************************************************************************
 *Converting a WOO definition to table definition

 		//CONVERT Woo definition to table definition
		// \t -> ;\t\/\/ 'type' =>
		// \t -> $this->fields_array[] = array('name' => '
		//integer -> 'int(11)',  'comment' => '
		//string -> varchar(" . STOCK_ID_LENGTH . ")',  'comment' => '
		//date-time -> 'timestamp',  'comment' => '
		//boolean -> 'bool',  'comment' => '
		//array -> 'int(11)',  'foreign_obj' => '',  'comment' => '
		//object -> 'int(11)',  'foreign_obj' => '',  'comment' => '
		//read-only -> 'readwrite' = > 'readonly'
		//^ -> \t\$this->fields_array[] = array('name' => '
		//;\t\/\/ -> ',
		// --> 'comment' => '
		// $ -> );
 * *******************************************************************************************/

require_once( '../ksf_modules_common/class.table_interface.php' );
require_once( '../ksf_modules_common/defines.inc.php' );
require_once( 'woo_defines.inc.php' );
require_once( 'class.woo_rest.php' );

class woo_interface extends table_interface
{
	/**********************************************************
	* INHERITS (table_interface)
        function get( $field )
        / * @bool@ *  /function set( $field, $value = null )
        / * @bool@ *  /function validate( $data_value, $data_type )
        / * none *  /function select_row( $set_caller = false )
        / * @mysql_result@ *  /function select_table($fieldlist = " * ", / * @array@ *  /$where = null, / * @array@ *  /$orderby = null, / * @int@ *  /$limit = null)
        function delete_table()
        function update_table()
        / * @bool@ *  /function check_table_for_id()
        / * @int@ *  /function insert_table()
        function create_table()
        function alter_table()
        / * @int@ *  /function count_rows()
        / * @int@ *  /function count_filtered($where = null)
        / * string *  /function getPrimaryKey()
        / * none *  /function getByPrimaryKey()
        function assoc2var( $assoc )            Take an associated array and take returned values and place into the calling MODEL class
        function get( $field )
        function query( $msg )
        function buildLimit()
        function buildSelect( $b_validate_in_table = false)
        function buildFrom()
        function buildWhere( $b_validate_in_table = false)
        function buildOrderBy( $b_validate_in_table = false)
        function buildGroupBy( $b_validate_in_table = false)
        function buildHaving( )
        function buildJoin()
        function buildSelectQuery( $b_validate_in_table = false )
        function clear_sql_vars()
        function assoc2var( $assoc )
        function var2caller()
	************************************************************************/
	/**********************************************************
	* PROVIDES
	 function fuzzy_match( $data )
        function rebuild_woocommerce()
        function backtrace()
        function tell( $msg, $method )
        function tell_eventloop( $caller, $event, $msg )
        function dummy( $obj, $msg )
        function register_with_eventloop()
        function build_interestedin()
        function notified( $obj, $event, $msg )
        function register( $msg, $method )
        function notify( $msg, $level = "ERROR" )
        /  *  @int@ * /function fields_array2var()
        function master_form()
        /  *  @array@ * /function fields_array2entry()
        function display_table_with_edit( $sql, $headers, $index, $return_to = null )
        function form_post_handler()
        function display_edit_form( $form_def, $selected_id = -1, $return_to )
        function combo_list( $sql, $order_by_field, $name, $selected_id=null, $none_option=false, $submit_on_change=false)
        function combo_list_cells( $sql, $order_by_field, $label, $name, $selected_id = null, $none_option=false, $submit_on_change=false )
        function combo_list_row( $sql, $order_by_field, $label, $name, $selected_id = null, $none_option=false, $submit_on_change=false )
        function define_table()
        function build_write_properties_array()
        function build_properties_array()
        function build_foreign_objects_array()
        function array2var( $data_array )
        function build_data_array()
        function reset_values()
        function extract_data_objects( $srvobj_array )
        /  *  @int@ * /function extract_data_array( $assoc_array )
        /  *  @int@ * /function extract_data_obj( $srvobj )
        function build_json_data()
        /  *  @bool@ * /function prep_json_for_send( $func = NULL )
        function ll_walk_insert_fa()
        function ll_walk_update_fa()
        function reset_endpoint()
        function error_handler( /  *  @Exception@ * / $e )
        function retrieve_woo( $search_array = null )
        function log_exception( $e, $client )
	************************************************************************/
	var $wc_client;
	var $woo_rest;
	var $woo_cs;
	var $woo_ck;
	var $write_properties_array;	//The list of WOO product properties (above) that are writeable.
	var $properties_array;
	var $foreign_objects_array;	//External objects that become embedded
	var $data_array;
	var $json_data;
	var $table_details;
	var $fields_array;
	var $company_prefix;
	var $client;	//Who is using us.  should be passed as $this 
	var $next_ptr;	//For Linked list
	var $prev_ptr;	//for Linked List
	var $serverURL;
	var $key;
	var $secret;
	var $options;
	var $debug;
	var $request;
	var $response;
	var $code;
	var $message;
	var $server_data_object;
	var $conn_type;
	var $header_array;
	var $enviro;
	var $entry_array;
	var $selected_id;	//!< find_submit sets this
	var $iam;
	var $interestedin;	//!< array of 'events' we are interested in and their associated data
	var $provides;		//!< array of functions and menu items to pass back to EXPORT_WOO so that
				//	each new module can interactively add it to the list
	var $to_tell_code;	//For once a called routine does something, use this code to ->tell
				//used for woo_rest and other interfaces to notify that something happened
				//e.g. send succeeded or Updated
	var $failed_tell_code;	//Code to use if the action failed i.e. search returned nothing
	var $to_match_array;    //!< array of fields to compare against for fuzzy_match
	var $match_need;	//!< int how many fields needed for fuzzymatch to be a match.
	var $search_array;	//!< array list of vars (fields) to search
	var $match_worth;	//!< array value of match (against need) for a field
	var $need_rest_interface;	//!< do we need to setup the Rest interface.  Most inheriting classes don't
	var $recursive_call;	//!< int for counting recursive calls so we don't end up in a loop


	/******************************************************************************************//**
	 *
	 * @param string WooCommerce Store URL
	 * @param string the OAuth Key
	 * @param string the OAuth secret
	 * @param array options
	 * @return null
	 * *******************************************************************************************/
	/*@void@*/function __construct($serverURL = " ", $key, $secret, $options, $client = null)
	{
		$this->iam = get_class( $this );
		$this->recursive_call = 0;
		$this->table_details = array();
		$this->fields_array = array();
		$this->search_array = array();
		$this->client = $client;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$this->provides = array();
		if( isset( $this->client->debug ) )
			$this->debug = $this->client->debug;
		else
			$this->debug = 0;
		//if( $this->need_rest_interface === true )
		if( isset( $options['need_rest_interface'] ) AND $options['need_rest_interface'] === true )
			$this->build_rest_interface($serverURL, $key, $secret, $options, $client);

		global $db_connections;
		$this->company_prefix = $db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'];
		if( strlen( $this->company_prefix ) < 2 )
		{
			if( isset( $this->client->company_prefix ) )
			{
				$this->company_prefix = $this->client->company_prefix;
			}
			else
			{
				$this->company_prefix = "0_";
			}
		}
		$this->match_need = 2;
		$this->match_worth = array();
		$this->define_table();
		$this->write_properties_array = array();
		$this->properties_array = array();
		$this->foreign_objects_array = array();
		$this->build_write_properties_array();
		$this->build_properties_array();
		$this->fields_array2entry();
		$this->build_interestedin();
		$this->register_with_eventloop();
		$this->reset_endpoint();
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return;
	}
	function update_woo_id( /*int*/ $id )
	{
		//This function MUST be overridden or woo_rest will fail!!
		throw new Exception( "This function MUST be overridden or woo_rest will fail!!", KSF_FCN_NOT_OVERRIDDEN );

	}
	/***************************************************************************//**
	* Build the Woo REST interface if required
	*
	* If we pass in NULL for all var's to constructor we won't setup the interface
	*
	* @param string URL
	* @param string Key
	* @param string Secret
	* @param array Options
	* @param object Caller
	*
	* @return bool Did we setup the interface
	******************************************************************************/
	/*@bool@*/private function build_rest_interface($serverURL = " ", $key, $secret, $options, $client = null)
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( $serverURL == null AND null == $key AND null == $secret )
		{
			//Particular child class doesn't want a REST interface
			$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
			return FALSE;
		}
		$this->serverURL = $serverURL;
		$this->key = $key;
		$this->secret = $secret;
		if( $options == null )
		{
			$options = array(
				'debug'           => true,
				'return_as_array' => false,
				'validate_url'    => false,
				'timeout'         => 30,
				'ssl_verify'      => false,
			);
		}

		$this->options = $options;
		$rest_options = array(
                               'wp_api' => true, // Enable the WP REST API integration
                               'version' => 'wc/v3', // WooCommerce WP REST API version
                               'ssl_verify' => 'false',
                               //'query_string_auth' => true // Force Basic Authentication as query string true and using under HTTPS
                );
		/*************************************************************
		 *	I want to depreciate the use of WC_API_CLIENT since
		 *	the latest API of WooCommerce uses WP REST interface
		 *	so the WC interface is depreciated
		 * ***********************************************************/
		require_once( 'wc-master/lib/woocommerce-api.php' );
		//$this->wc_client = null;
		//Still used by class.woo_product
		if( strlen( $key ) < 10 )
		{
			if( null != $client )
			{
				$this->notify( __METHOD__ . ":" . __LINE__ . "  Build REST interfaces ", "WARN" );
				$this->wc_client = new WC_API_Client( $serverURL, $client->woo_ck, $client->woo_cs, $options, $client );
				$this->woo_rest = new woo_rest( $serverURL, $client->woo_ck, $client->woo_cs, $rest_options, $client );
			}
			else
			{
				$this->notify( __METHOD__ . ":" . __LINE__ . " UNABLE to Build REST interfaces ", "ERROR" );
				$this->wc_client = null;
				$this->woo_rest = null;
			}
		}
		else
		{
			if( strlen( $serverURL) > 10 )
			{
				$this->notify( __METHOD__ . ":" . __LINE__ . "  Build REST interfaces ", "WARN" );
				$this->wc_client = new WC_API_Client( $serverURL, $key, $secret, $options, $client );
				$this->woo_rest = new woo_rest( $serverURL, $key, $secret, $rest_options, $client );
			}
			else
			{
				$this->notify( __METHOD__ . ":" . __LINE__ . " UNABLE to Build REST interfaces ", "ERROR" );
				$this->wc_client = null;
				$this->woo_rest = null;
			}
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return TRUE;
	}
        /************************************************************************************************//**
        * Match a returned product from WooCommerce to our current one.
        *
        *If we find a match, we return the product.  Exits on first match
        * Will search against an array to_match_array
        *
        * @param array array of stdObj holding data from WC
        * @return did we find a match
        ***************************************************************************************************/
        /*@bool@*/ function fuzzy_match( $data )
        {
                $this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );

                if( ! is_array( $data ) )
                        throw new Exception( "fuzzy_match expects a data array.  Not passed in", KSF_INVALID_DATA_TYPE );
		$f1 = $this->to_match_array[0];
		if( isset( $this->$f1 ) )
                	$this->notify( __METHOD__ . ":" . __LINE__ . " Fuzzy Match on " . $this->$f1, "NOTIFY" );
		$from_arr = array( "<p>", "</p>" );	//SKU had / replaced with _
		$to_arr   = array( "",    ""     ); 	//WC returns <p> and other HTML tags on some fields
                foreach( $data as $product )
                {
                        $match=0;
                	//$this->notify( __METHOD__ . ":" . __LINE__ . " Trying to match against returned object " . print_r( $product, true ), "DEBUG" );
                	//$this->notify( __METHOD__ . ":" . __LINE__ . " Trying to match with us: " . print_r( $this->data_array, true ), "DEBUG" );
                        if( is_array( $this->to_match_array ) )
                        {
                                foreach( $this->to_match_array as $find )
                                {
                                        if( ! isset( $this->$find ) )
					{
                				$this->notify( __METHOD__ . ":" . __LINE__ . " Match on THIS Field :" . $find . ": IMPOSSIBLE.  Not Set", "DEBUG" );
					}
                                        else if( ! isset( $product->$find ) )
					{
                				$this->notify( __METHOD__ . ":" . __LINE__ . " Match on RETURNED Field :" . $find . ": IMPOSSIBLE.  Not Returned", "DEBUG" );
					}
                                        else
					{
						$ret = str_replace( $from_arr, $to_arr, $product->$find );
						if(  strcasecmp( $ret, $this->$find ) == 0 OR strcasecmp( $product->$find, $this->$find ) == 0 ) 
	                                        {
	                				$this->notify( __METHOD__ . ":" . __LINE__ . " SUCCESS Matched field " . $find . " with value "  . $this->$find, "DEBUG" );
							if( isset( $this->match_worth[$find] ) )
							{
	                					$this->notify( __METHOD__ . ":" . __LINE__ . " MATCH WORTH " . $this->match_worth[$find], "DEBUG" );
								$match .= $this->match_worth[$find];
							}
	                                                else 
							{
	                					$this->notify( __METHOD__ . ":" . __LINE__ . " MATCH WORTH not set.  Adding 1", "DEBUG" );
								$match++;
							}
	                                        }
						else
						{
                					//$this->notify( __METHOD__ . ":" . __LINE__ . " Match on Field :" . $find . ": FAILED: " . $product->$find . "(" . $ret .  ")::"  . $this->$find, "DEBUG" );
                					$this->notify( __METHOD__ . ":" . __LINE__ . " Match on Field :" . $find . ": FAILED: " . $product->$find . "::"  . $this->$find, "DEBUG" );
						}
					}
                                }
                        }
                        else
                        {
				throw new Exception( "No array to match against", KSF_VAR_NOT_SET );
                        }
                        if( $match >= $this->match_need )
                        {
                                $this->id = $product->id;
                                $this->notify( __METHOD__ . ":" . __LINE__ . " SUCCESS Leaving " . __METHOD__, "WARN" );
                                return TRUE;
                        }
                }
                $this->notify( __METHOD__ . ":" . __LINE__ . " NO MATCHES FOUND", "WARN" );
                $this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
                return FALSE;
        }
	/********************************************//***
	* For when we need to rebuild the WooCommerce store
	*
	*	Each inheriting class will need to implement
	*	its own reset routine.  Chances are it is
	*	a zero/nulling of related Woo IDs.
	*
	***********************************************/
	function rebuild_woocommerce()
	{
	}
	function backtrace()
	{
		echo "<br />";
		array_walk(debug_backtrace(),create_function('$a,$b','print "{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']});<br /> ";'));
	}
	/************************************************************//**
	 *
	 *	tell.  Function to tell the using routine that we took
	 *	an action.  That will let the client pass that data to
	 *	any other plugin routines that are interested in that
	 *	fact.
	 *
	 *	@param msg what event message to pass
	 *	@param method Who triggered that event so that we don't pass back to them into an endless loop
	 *
	 * **************************************************************/
	function tell( $msg, $method )
	{
		//$this->notify( __METHOD__ . "::"  . __LINE__ . " Entering " . __METHOD__, "WARN" );
		if( isset( $this->client ) )	//if not set nobody to tell
			if( isset( $msg ) )	//If not set nothing to pass along...
				if( is_callable( $this->client->eventloop( $msg, $method ) ) )
					$this->client->eventloop( $msg, $method );
		else
		{
			$this->tell_eventloop( $this, $msg, $method );
		}
		//$this->notify( __METHOD__ . "::"  . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	function tell_eventloop( $caller, $event, $msg )
	{
		//$this->notify( __METHOD__ . "::"  . __LINE__ . " Entering " . __METHOD__, "WARN" );
		global $eventloop;
		if( isset( $eventloop ) )
			$eventloop->ObserverNotify( $caller, $event, $msg );
		//$this->notify( __METHOD__ . "::"  . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		
	}
	/***************************************************************//**
	 *dummy   
	 *
	 * 	Dummy function so that build_interestedin has something to
	 * 	put in as an example.
	 *
	 * 	@returns FALSE
	 * ******************************************************************/
	function dummy( $obj, $msg )
	{	
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		return FALSE;
	}
	function register_with_eventloop()
	{
		$this->notify( __METHOD__ . "::"  . __LINE__ . " Entering " . __METHOD__, "WARN" );
		global $eventloop;
		if( null != $eventloop )
		{
			foreach( $this->interestedin as $key => $val )
			{
				if( $key <> WOO_DUMMY_EVENT )
					$eventloop->ObserverRegister( $this, $key );
			}
		}
		$this->notify( __METHOD__ . "::"  . __LINE__ . " Exiting " . __METHOD__, "WARN" );
	}
	/***************************************************************//**
	 *build_interestedin
	 *
	 * 	DEMO function that needs to be overridden
	 * 	This function builds the table of events that we
	 * 	want to react to and what handlers we are passing the
	 * 	data to so we can react.
	 * ******************************************************************/
	function build_interestedin()
	{
		//This NEEDS to be overridden
		$this->interestedin[WOO_DUMMY_EVENT]['function'] = "dummy";
	}
	/***************************************************************//**
	 *notified
	 *
	 * 	When we are notified that an event happened, check to see
	 * 	what we want to do about it
	 *
	 * @param $obj Object of who triggered the event
	 * @param $event what event was triggered
	 * @param $msg what message (data) was passed to us because of the event
	 * ******************************************************************/
	function notified( $obj, $event, $msg )
	{
		if( isset( $this->interestedin[$event] ) )
		{
			$tocall = $this->interestedin[$event]['function'];
			$this->$tocall( $obj, $msg );
		}
	}
	/************************************************************//**
	 *
	 *	register.  Function to tell the using routine that we are
	 *	interested in other plugins having taken an action. 
	 *
	 *	@param msg what event message we are interested in
	 *	@param method Who we are so we can be told
	 *
	 * **************************************************************/
	function register( $msg, $method )
	{
		$this->notify( __METHOD__ . "::"  . __LINE__, "WARN" );
		if( isset( $this->client ) )
			if( is_callable( $this->client->eventregister() ) )
				$this->client->eventregister( $msg, $method );
	}
	/**********************************************************//**
	 * Log to screen depending on level
	 *
	 * Levels
	 * 	ERROR (display error on screen)
	 * 	WARN (debug level 1)
	 * 	NOTIFY (debug level 2)
	 *	DEBUG (debug level 3)
	 *
	 * ***********************************************************/
	function notify( $msg, $level = "ERROR" )
	{
		//$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', "Passed in Logging Level: " . $level );
		if( "ERROR" == $level )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_ERROR', $msg );
			display_error( $msg );
		}
		else if( "WARN" == $level )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_WARN', $msg );
			if( $this->debug >= 1 )
				display_notification( $msg );
		}
		else if( "NOTIFY" == $level )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_NOTIFY', $msg );
			if( $this->debug >= 2 )
				display_notification( $msg );
		}
		else if( "DEBUG" == $level )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', $msg );
			if( $this->debug >= 3 )
				display_notification( $msg );
		}
		else
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_INFO', $msg );
			if( $this->debug >= 2 )
				display_notification( $msg );
		}

	}

	/*********************************************************************************//**
	 *fields_array2var
	 *	Take the data out of POST variables and put them into
	 *	the variables defined as table columns (fields_array)
	 *
	 *	@returns int count of fields set
	 *
	 * ***********************************************************************************/
	/*@int@*/function fields_array2var()
	{
		$count = 0;
		$this->reset_values();
		foreach( $this->fields_array as $row )
		{
			$var = $row['name'];
			if( isset( $_POST[$var] ) )
			{
				$this->$var = $_POST[$var];
				$count++;
			}
		}
		return $count;
	}
	/*********************************************************************************//**
	 *master_form
	 *	Display 2 forms - the summary of items with edit/delete
	 *		The edit/entry form for 1 row of data
	 *	assumes entry_array has been built (constructor)
	 *	assumes table_details has been built (constructor)
	 *	assumes selected_id has been set (constructor?)
	 *	assumes iam has been set (constructor)
	 *
	 * ***********************************************************************************/
	function master_form()
	{
		global $Ajax;
		//var_dump( $_POST );
		//var_dump( $_GET );
		$this->notify( __METHOD__ . "::"  . __LINE__, "WARN" );
		//simple_page_mode();
		div_start('form');
		//$this->notify( __METHOD__ . "::"  . __LINE__ . " Mode: " . $Mode );
		$this->selected_id = find_submit('Edit');
		$count = $this->fields_array2var();
		$key = $this->table_details['primarykey'];
		if( isset( $this->$key ) )
		{
			$this->notify( __METHOD__ . ":" . " Key set.  Updating", "WARN" );
			$this->update_table();
		}
		else if( $count > 0 )
		{
			$this->notify( __METHOD__ . ":" . " Key NOT set.  Inserting", "WARN" );
			$this->insert_table();
		}
		$this->reset_values();
		
		$sql = "SELECT ";
		$rowcount = 0;
		foreach( $this->entry_array as $row )
		{
			if( $rowcount > 0 ) $sql .= ", ";
			$sql .= $row['name'];
			$rowcount++;
		}
		$sql .= " from " . $this->table_details['tablename'];
		if( isset( $this->table_details['orderby'] ) )
			$sql .= " ORDER BY " . $this->table_details['orderby'];
	
		$this->notify( __METHOD__ . ":" . __LINE__ . ":" . $sql, "WARN" );
		$this->notify( __METHOD__ . ":" . __LINE__ . ":" . " Display data", "WARN" );
		$this->display_table_with_edit( $sql, $this->entry_array, $this->table_details['primarykey'] );
		$this->display_edit_form( $this->entry_array, $this->selected_id, "create_" . $this->iam . "_form" );
		div_end();
		//$Ajax->activate('form');
	}
	/*@array@*/function fields_array2entry()
	{
		//debug_print_backtrace();
		//array_walk(debug_backtrace(),create_function('$a,$b','print "{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']});<br /> ";'));
		//$this->notify( __METHOD__ . "::"  . __LINE__ . " Entering " . __METHOD__, "DEBUG" );
		//Take a fields_array definition and conver to the array needed
		//to create edit forms for display_table_with_edit and display_edit_form
		$entry_array = array();
		$count = 0;
		foreach( $this->fields_array as $row )
		{
			$entry_array[$count]['column'] = $row['name'];
			if( !isset( $row['foreign_obj'] ) )
			{
				//$this->notify( __METHOD__ . "::"  . __LINE__, "DEBUG" );
				$open = strpos($row['type'], "(");
				if( false !== $open )
				{
					$type = strstr( $row['type'], 0, $open );
					$close = strpos( $row['type'], ")" );
					$num = strstr( $row['type'], $open, $close );
					$entry_array[$count]['type'] = $type;
					$entry_array[$count]['size'] = $num;
				}
				else
				{
					$entry_array[$count]['type'] = $row['type'];
				}
			}
			else
			{
				//$this->notify( __METHOD__ . "::"  . __LINE__, "DEBUG" );
				//It is an index into another table.  Should be a drop down in edit form
				$entry_array[$count]['type'] = "dropdown";
				$entry_array[$count]['size'] = "11";
				$entry_array[$count]['foreign_obj'] = $row['foreign_obj'];
				if( isset( $row['foreign_column'] ) )
					$entry_array[$count]['foreign_column']= $row['foreign_column'];
				else
					$entry_array[$count]['foreign_column']= $row['name'];

				//
				//Ensure that foreign_object_array contains the table too...
			}
				$entry_array[$count]['name'] =	$row['name'];
			if( isset( $row['label'] ) )
				$entry_array[$count]['label'] =	$row['label'];
			else
			if( isset( $row['comment'] ) )
				$entry_array[$count]['label'] =	$row['comment'];
			else
				$entry_array[$count]['label'] =	$row['name'];
			if( isset( $row['readwrite'] ) )
				$entry_array[$count]['readwrite'] =	$row['readwrite'];
			else
				$entry_array[$count]['readwrite'] = "readwrite";	//ASSUMING no restriction...
			$count++;
		}
		$this->entry_array = $entry_array;
		//$this->notify( __METHOD__ . "::"  . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return $entry_array;
	}
	function display_table_with_edit( $sql, $headers, $index, $return_to = null )
	{
		$this->notify( __METHOD__ . "::"  . __LINE__, "DEBUG" );
		$columncount = 0;
		foreach( $headers as $row )
		{
			$th[$columncount] = $row['label'];
			$datacol[$columncount] = $row['name'];
			$columncount++;
		}
		//Edit
			$th[$columncount] = "";
			$columncount++;
		//Delete
			$th[$columncount] = "";
			//$th[$columncount] = $row[$index];
			$columncount++;
			//$multi=false, $dummy=false, $action="", $name=""
		start_form( );
		//start_form( false, false, "woo_form_handler.php", "" );
		start_table(TABLESTYLE, "width=80%" );
		//inactive_control_column($th);
		table_header( $th );
		$k=0;

		$result = db_query( $sql, __METHOD__ . " Couldn't run query" );
		while( $nextrow = db_fetch( $result ) )
		{
			alt_table_row_color($k);
			for( $c = 0; $c <= $columncount - 3; $c++ )
			{
				label_cell( $nextrow[$c] );
			}
			edit_button_cell("Edit" . $nextrow[$index], _("Edit") );
			delete_button_cell("Delete" . $nextrow[$index], _("Delete") );
			//inactive_control_cell( $nextrow[$index] );
			end_row();
		}
		//inactive_control_row($th);
		hidden( 'table_with_edit', 1 );
		if( null != $return_to )
			hidden( 'return_to', $return_to );
		end_table();
		end_form();
	}
	function form_post_handler()
	{
		$this->notify( __METHOD__ . "::"  . __LINE__, "WARN" );
		$count = 0;
		if( isset( $_POST['table_with_edit'] ) )
		{
			//Need to load that record and then send to display_edit_form
		}
		else if( isset( $_POST['edit_form'] ) )
		{
			//load variables into our values...
			foreach( $this->properties_array as $var )
			{
				if( isset( $_POST[$var] ) )
				{
					$this->$var = $_POST[$var];
					$count++;
				}
			}
		}
		//$count = count( $this->entry_array );	
		$key = $this->table_details['primarykey'];
		if( isset( $this->$key ) )
		{
			$this->notify( __METHOD__ . ":" . " Key set.  Updating", "WARN" );
			$this->update_table();
		}
		else if( $count > 0 )
		{
			$this->notify( __METHOD__ . ":" . " Key NOT set.  Inserting", "WARN" );
			$this->insert_table();
		}
		else
		{
			if( $this->debug > 1 )
			{
				echo "<br />" . __METHOD__ . " No values set.  Why are we here?<br />";
				echo "<br />" . __METHOD__ . " Class is " . get_class( $this ) . "<br />";
				var_dump( $_POST );
				$this->wc_client = null;
				echo "<br /><br />" . __METHOD__ . " Class variables are <br />";
				var_dump( $this );
			}
			throw new Exception( "POST variables not set", KSF_VALUE_NOT_SET );
		}
	}
	function display_edit_form( $form_def, $selected_id = -1, $return_to )
	{
		$this->notify( __METHOD__ . "::"  . __LINE__, "DEBUG" );
		if( $selected_id > -1 )
		{
			//We are editing a row, so need to query for the values
			$sql = "SELECT * from " . $this->table_details['tablename'];
			$sql .= " WHERE " . $this->table_details['primarykey'] . " = '" . $selected_id . "'";
			$res = db_query( $sql, __METHOD__ . " Couldn't query selected" );
			$arr = db_fetch_assoc( $res );
			$this->array2var( $arr );
		}
		start_form(  );
		//start_form(  false, false, "woo_form_handler.php", "" );
		start_table(TABLESTYLE2 );
		foreach( $form_def as $row )
		{
			$var = $row['name'];
			if( $row['readwrite'] == "read" )
			{
				//can't edit this column as it isn't set write nor readwrite
				if( isset( $this->$var ) )
					label_row( _($row['label'] . ":"), $this->$var );
			}
			else
			{
				if( $row['type'] == "varchar" )
					text_row(_($row['label'] . ":"), $row['name'], $this->$var, $row['size'], $row['size']);
				/*
				else if( $row['type'] == "dropdown" )
				{
					$ddsql = "select * from " . $row['foreign_obj'];
					$ddsql .= " ORDER BY " . $row['foreign_column'];
					$this->combo_list_row( $ddsql, $row['foreign_column'], 
								_($row['label'] . ":"), $row['name'], 
								$selected_id, false, false ); 
				}
				 */
				else if( $row['type'] == "bool" )
					check_row(_($row['label'] . ":"), $row['name'] ); 
				else
					text_row(_($row['label'] . ":"), $row['name'], null, $row['size'], $row['size']);
			}
		}


		end_table();
		hidden( 'edit_form', 1 );
		hidden( 'my_class', get_class( $this ) );
		hidden( 'return_to', $return_to );
		hidden( 'action', $return_to );
		submit_center('ADD_ITEM', _("Add Item") );
//		submit_add_or_update_center($selected_id == -1, '', 'both', false);
		end_form();
		if( $this->debug >= 3 ) $this->backtrace();
	}
	function combo_list( $sql, $order_by_field, $name, $selected_id=null, $none_option=false, $submit_on_change=false)
	{
		global $path_to_root;
		include_once( $path_to_root . "/includes/ui/ui_lists.inc" );
		return combo_input($name, $selected_id, $sql, $order_by_field,  'name',
		array(
			'order' => $order_by_field,
			'spec_option' => $none_option,
			'spec_id' => ALL_NUMERIC,
			'select_submit'=> $submit_on_change,
			'async' => false,
		) );
	}
	function combo_list_cells( $sql, $order_by_field, $label, $name, $selected_id = null, $none_option=false, $submit_on_change=false )
	{
		echo "<td>$label</td>";
		echo "<td>";
		$this->combo_list( $sql, $order_by_field, $name, $selected_id, $none_option, $submit_on_change);
		echo "</td>";
	}
	function combo_list_row( $sql, $order_by_field, $label, $name, $selected_id = null, $none_option=false, $submit_on_change=false )
	{
		echo "<tr><td class='label'>$label</td>";
		$this->combo_list_cells( $sql, $order_by_field, $label, $name, $selected_id, $none_option, $submit_on_change);
		echo "</tr>";
	}
	function define_table()
	{
		//Inheriting class MUST extend
		//	$this->fields_array[] = array('name' => 'billing_address_id', 'type' => 'int(11)', 'comment' => '', 'readwrite' => '');  readwrite can be read or write or undefined.
		//	$this->table_details['tablename'] = $this->company_prefix . "woo_billing_address";
		//	$this->table_details['primarykey'] = "billing_address_id";

		//20200302 KSF check for model_ in class name
		//If the class name starts with model_ we want to strip that off.
		if( ! strncasecmp( "model_", $this->iam, 5 ) )
		{
			$tablename = $this->iam;
		}
		else
		{
			$char = stripos( $this->iam, "_" ) + 1;
			$tablename = substr( $this->iam, $char );
		}
		

		//The following should be common to pretty well EVERY table...
		$ind = "id_" . $tablename;
		$this->fields_array[] = array('name' => $ind, 'type' => 'int(11)', 'auto_increment' => 'yes', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read' );
		$this->table_details['tablename'] = $this->company_prefix . $tablename;
		$this->table_details['primarykey'] = $ind;
		//$this->table_details['index'][0]['type'] = 'unique';
		//$this->table_details['index'][0]['columns'] = "variablename";
		//$this->table_details['index'][0]['keyname'] = "variablename";

		//20200302 KSF

	}

	function build_write_properties_array()
	{
				/*Took the list of properties, and removed the RO ones*/
		foreach( $this->fields_array as $row )
		{
			if( isset( $row['foreign_obj'] ) )
			{
				//$this->foreign_objects_array[] = trim( $row['name'] );
			}
			else
			if( isset( $row['readwrite'] ) )
			{
				if( strncmp( $row['readwrite'], "read", 4 ) <> 0 )
				{
					//Not READONLY
					$this->write_properties_array[] = trim( $row['name'] );
				}
			}
			else
			{
				//Assuming NOT set therefore RW
				$this->write_properties_array[] = trim( $row['name'] );
			}
		}
	}
	function build_properties_array()
	{
		/*All properties*/
		foreach( $this->fields_array as $row )
		{
			//echo "<br />" . __LINE__ . "<br />";
			//var_dump( $row );
			if( isset( $row['foreign_obj'] ) )
			{
		//		echo __LINE__ . " Foreign Object " . $row['name'] . "<br />";
				$this->foreign_objects_array[] = $row['name'];
			}
			else
				$this->properties_array[] = trim( $row['name'] );
		}

	}
	function build_foreign_objects_array()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		//Extending class needs to override!!
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		//return;
	}
	function array2var( $data_array )
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		foreach( $this->properties_array as $property )
		{
			if( isset( $data_array[$property] ) )
			{
				$this->$property = $data_array[$property];
			}
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		//return;
	}
	function build_data_array()
	{
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );

		/***20180917 KSF Clean up old data arrays so we quit sending sales data, bad images, etc*/
		if( isset( $this->data_array ) )
		{
			unset( $this->data_array );
			$this->data_array = array();
		}
		/*!20180917 KSF Clean */
		foreach( $this->write_properties_array as $property )
		{
			if( isset( $this->$property ) )
			{
				$this->data_array[$property] = $this->$property;
			}
		}
		$this->notify( __METHOD__ . ":" . __LINE__ . " Exiting " . __METHOD__, "WARN" );
		//return;
	}
	/*******************************************************************//**
	 *
	 * 	reset_values.  unset all variables listed in properties_array
	 *
	 * 	As we cycle through a database result set putting values into
	 * 	the object, we want to ensure we don't have any values left over
	 * 	from the previous row.  This unsets all values so that they
	 * 	are cleared.
	 * 
	 * **********************************************************************/
	function reset_values()
	{
		foreach( $this->properties_array as $val )
		{
			unset( $this->$val );
		}
		if( $this->debug > 1 )
		{
			echo "<br />" . __METHOD__ . ":" . __LINE__ . " Reset values.  Should be nulls for class " . get_class( $this ) . "<br />";
			var_dump( $this );
		}
	}

	/***************************************************************
	 *
	 * Extract Data Objects
	 *
	 * Recursively extracts the data object
	 * Builds a double linked list in the process.
	***************************************************************/
	function extract_data_objects( $srvobj_array )
	{
		//Woo sends an array of the objects
		$nextptr = $this;
		$objectcount = 0;
		foreach( $srvobj_array as $obj )
		{
			$newobj = new $this->iam($this->serverURL, $this->key, $this->secret, $this->options, $this );
			//Do the recursive extract.
			$newobj->extract_data_obj( $obj );
			//Add into Linked List
			$nextptr->next_ptr = $newobj;
			$newobj->prev_ptr = $nextptr;
			$nextptr = $newobj;
			$objectcount++;
			//The next time through the loop does another...
			//Not unsetting the object because it is part of the dbl linked list
		}
		return $objectcount;
	}
	/*int count of properties extracted*/
	/*@int@*/function extract_data_array( $assoc_array )
	{
		$extract_count = 0;
		foreach( $this->properties_array as $property )
		{
			if( isset( $assoc_array[$property] ) )
			{
				$this->$property = $assoc_array[$property];
				$extract_count++;
			}
		}
		//Should also handle FK indexes, but for now...
		return $extract_count;
	}

	/*int count of properties extracted*/
	/*@int@*/function extract_data_obj( $srvobj )
	{
		if( $this->debug >= 3 )
		{
			echo "<br />" . __FILE__ . ":" . __LINE__ . "<br /><br />";
			var_dump( $srvobj );
		}
		$extract_count = 0;
		foreach( $this->properties_array as $property )
		{
			if( isset( $srvobj->$property ) )
			{
				$this->$property = $srvobj->$property;
				$extract_count++;
			}
		}
		if( $this->debug >= 3 )
		{
			echo __FILE__ . ":" . __LINE__ . "<br />Extracted " . $extract_count . " properties<br />";
		}
		//
		//echo __FILE__ . ":" . __LINE__ . "<br /><br />";
		//var_dump( $this->foreign_objects_array );
		foreach( $this->foreign_objects_array as $foa )
		{
			echo __FILE__ . ":" . __LINE__ . "<br />Foreign Object " . $foa . "<br />";
			if( isset( $srvobj->$foa ) )
			{
				if( $this->debug > 0 )
				{
					echo "<br /><br />" . __FILE__ . ":" . __LINE__ . "<br />Extract for class " . $foa . "<br />";
					var_dump( $srvobj->$foa );
				}
				require_once( 'class.woo_' . $foa . '.php' );
				if( is_array( $srvobj->$foa ) )
				{
					foreach( $srvobj->$foa as $obj )
					{
						$newclassname = "woo_" . $foa;
						$newobj = new $newclassname($this->serverURL, $this->key, $this->secret, $this->options, $this);
						$ret = $newobj->extract_data_obj( $obj );
						if( $ret > 0 )
						{
							$newobj->insert_table();
							$this->$foa = $this->$foa + 1;	//Count of the numbers of foreign rows.
							if( $this->debug > 0 )
							{
								echo "<br />" . __FILE__ . ":" . __LINE__ . "<br /><br />";
								var_dump( $newobj );
							}
						}
						unset( $newobj );	//Free the memory
					}
				}
				else
				if( is_object( $srvobj->$foa ) )
				{
					$newclassname = "woo_" . $foa;
					$newobj = new $newclassname($this->serverURL, $this->key, $this->secret, $this->options, $this);
					$ret = $newobj->extract_data_obj( $srvobj->$foa );
					if( $ret > 0 )
					{
						$newobj->insert_table();
						$this->$foa = $this->$foa + 1;	//Count of the numbers of foreign rows.
						if( $this->debug > 0 )
						{
							echo "<br />" . __FILE__ . ":" . __LINE__ . "<br /><br />";
							var_dump( $newobj );
						}
					}
					unset( $newobj );	//Free the memory
				}
			}
		}
		return $extract_count;
	}
	function build_json_data()
	{
		$this->json_data = json_encode( $this->data_array );
		//echo $this->json_data;
	}
	/*@bool@*/function prep_json_for_send( $func = NULL )
	{
		$this->build_data_array();
		$this->build_json_data();
		if( $this->json_data == FALSE )
		{
			$this->notify( __LINE__ . " " . $func . " Failed to build JSON data", "ERROR" );
			return FALSE;
		}
		else
			return TRUE;
	}
	function ll_walk_insert_fa()
	{
		$this->notify( __METHOD__ . "::"  . __LINE__, "WARN" );
		$nextptr = $this->next_ptr;
		while( $nextptr != NULL )
		{
			//if "id" not in the table, insert else update
			if( $this->check_table_for_id() )
				$nextptr->update_table();
			else
				$nextptr->insert_table();
			$nextptr = $nextptr->next_ptr;
		}
	}
	function ll_walk_update_fa()
	{
		$this->notify( __METHOD__ . "::"  . __LINE__, "WARN" );
		$nextptr = $this->next_ptr;
		while( $nextptr != NULL )
		{
			$nextptr->update_table();
			$nextptr = $nextptr->next_ptr;
		}
	}
	function reset_endpoint()
	{
		throw new Exception( "Inheriting class " . get_class( $this ) . " must override " . __METHOD__ . "!", KSF_FCN_NOT_OVERRIDDEN );
	}
	function error_handler( /*@Exception@*/ $e )
	{
		throw new Exception( "Inheriting class must override " . __METHOD__ . "!", KSF_FCN_NOT_OVERRIDDEN );
	}
	function retrieve_woo( $search_array = null )
	{
  		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
                try
                {
			if( !isset( $this->endpoint ) )
				throw new Exception( "Endpoint not set so can't query Woocommerce", KSF_VALUE_NOT_SET );
                        if( isset( $this->woo_rest ) )
                                $response = $this->woo_rest->get( $this->endpoint, $search );
                        else
                                throw new InvalidArgumentException( "WOO_REST not set", KSF_FIELD_NOT_SET );
                        if( $this->debug >= 2 )
                        {
                                echo "<br />" . __FILE__ . ":" . __LINE__ . "<br />";
                                print_r( $response );
                        }
                        $this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
                        return $response;
                }
                catch( Exception $e )
                {
                        $this->error_handler( $e );
                }
	}
	function log_exception( $e, $client )
	{
	  	global $eventloop;
		if( isset( $eventloop ) )
		{
               		$eventloop->ObserverNotify( $client, 'NOTIFY_LOG_ERROR', $e->getCode() . "::" . $e->getMessage() );
			if( $e instanceof HttpClientException )
			{
               			$eventloop->ObserverNotify( $client, 'NOTIFY_LOG_ERROR', $e->getCode() . "::" . $e->getRequest() );
               			$eventloop->ObserverNotify( $client, 'NOTIFY_LOG_ERROR', $e->getCode() . "::" . $e->getRespone() );
			}
		}
		$this->notify( get_class( $client ) . " has raised exception: " . $e->getCode() . "::" . $e->getMessage(), "WARN" );
	}
}

?>
