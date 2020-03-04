<?php

/*****************************************
* There is many common VIEW activities within a module
*
*	Code came from woo_interface.  Trying to refactor.
*
******************************************/

//require_once( '../ksf_modules_common/class.table_interface.php' );		//MODEL
require_once( '../ksf_modules_common/defines.inc.php' );
require_once( 'woo_defines.inc.php' );
//require_once( 'class.woo_rest.php' );						//CONTROLLER?

class view_woo extends table_interface
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
		//$this->table_details = array();	//MODEL
		$this->fields_array = array();
		$this->search_array = array();		//MODEL
		$this->client = $client;
		$this->notify( __METHOD__ . ":" . __LINE__ . " Entering " . __METHOD__, "WARN" );
		$this->provides = array();
		if( isset( $this->client->debug ) )
			$this->debug = $this->client->debug;
		else
			$this->debug = 0;
/* **CONTROLLER
		if( $this->need_rest_interface === true )
			$this->build_rest_interface($serverURL, $key, $secret, $options, $client);
*/

/* **MODEL
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
*/
/* **CONTROLLER
		$this->match_need = 2;
		$this->match_worth = array();
*/
/* **MODEL
		$this->define_table();
		$this->write_properties_array = array();
		$this->properties_array = array();
		$this->foreign_objects_array = array();
		$this->build_write_properties_array();
		$this->build_properties_array();
		$this->fields_array2entry();
*/
		$this->build_interestedin();
		$this->register_with_eventloop();
/* **CONTROLLER/MODEL
		$this->reset_endpoint();
*/
		$this->notify( __METHOD__ . ":" . __LINE__ . " Leaving " . __METHOD__, "WARN" );
		return;
	}
/* **MODEL
	function update_woo_id( /*int*/ $id )
	{
		//This function MUST be overridden or woo_rest will fail!!
		throw new Exception( "This function MUST be overridden or woo_rest will fail!!", KSF_FCN_NOT_OVERRIDDEN );

	}
*/
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
			$tocall = $this->interested[$event]['function'];
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
		$this->notify( __METHOD__ . "::"  . __LINE__, "WARN" );
		//simple_page_mode();
		div_start('form');
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
	function error_handler( /*@Exception@*/ $e )
	{
		throw new Exception( "Inheriting class must override " . __METHOD__ . "!", KSF_FCN_NOT_OVERRIDDEN );
	}
}

?>
