<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( '../ksf_modules_common/class.table_interface.php' );

class woo_interface extends table_interface
{
	var $wc_client;
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

	function __construct($serverURL, $key, $secret, $options, $client = null)
	{
		$this->table_details = array();
		$this->fields_array = array();
		$this->client = $client;
		if( isset( $this->client->debug ) )
			$this->debug = $this->client->debug;
		else
			$this->debug = 0;
		$this->serverURL = $serverURL;
		$this->key = $key;
		$this->secret = $secret;
		/*************************************************************
		 *	I want to depreciate the use of WC_API_CLIENT since
		 *	the latest API of WooCommerce uses WP REST interface
		 *	so the WC interface is depreciated
		 * ***********************************************************/
		require_once( 'wc-master/lib/woocommerce-api.php' );
		if( !isset( $options ) OR $options == null )
			$options = array(
				'debug'           => true,
				'return_as_array' => false,
				'validate_url'    => false,
				'timeout'         => 30,
				'ssl_verify'      => false,
			);
		$this->options = $options;
		$this->wc_client = new WC_API_Client( $serverURL, $key, $secret, $options, $client );

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

		$this->define_table();
		$this->write_properties_array = array();
		$this->properties_array = array();
		$this->foreign_objects_array = array();
		$this->build_write_properties_array();
		$this->build_properties_array();
	}
	function notify( $msg, $level = "ERROR" )
	{
		if( "ERROR" == $level )
		{
			display_error( $msg );
		}
		else if( "NOTIFY" == $level )
		{
			display_notification( $msg );
		}
		else if( "WARN" == $level )
		{
			display_notification( $msg );
		}
		else if( "DEBUG" == $level )
		{
			display_notification( $msg );
		}
		else
		{
			display_notification( $msg );
		}

	}
	function define_table()
	{
		//Inheriting class MUST extend
		//	$this->fields_array[] = array('name' => 'billing_address_id', 'type' => 'int(11)', 'comment' => '', 'readwrite' => '');  readwrite can be read or write or undefined.
		//	$this->table_details['tablename'] = $this->company_prefix . "woo_billing_address";
		//	$this->table_details['primarykey'] = "billing_address_id";
	
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
		//Extending class needs to override!!
	}
	function array2var( $data_array )
	{
		foreach( $this->properties_array as $property )
		{
			if( isset( $data_array[$property] ) )
			{
				$this->$property = $data_array[$property];
			}
		}
	}
	function build_data_array()
	{
		foreach( $this->write_properties_array as $property )
		{
			if( isset( $this->$property ) )
			{
				$this->data_array[$property] = $this->$property;
			}
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
		$iam = get_class( $this );
		//Woo sends an array of the objects
		$nextptr = $this;
		$objectcount = 0;
		foreach( $srvobj_array as $obj )
		{
			$newobj = new $iam($this->serverURL, $this->key, $this->secret, $this->options, $this );
			//Do the recursive extract.
			$newobj->extract_data_obj( $obj );
			//Add into Linked List
			$nextptr->next_ptr = $newobj;
			$newobj->prev_ptr = $nextptr;
			$nextptr = $newobj;
			$objectcount++;
			//The next time through the loop does another...
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
		$nextptr = $this->next_ptr;
		while( $nextptr != NULL )
		{
			$nextptr->update_table();
			$nextptr = $nextptr->next_ptr;
		}
	}

}

?>
