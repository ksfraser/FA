<?php

/**************************************************************************
*
*	CONTROLLER
*
**************************************************************************/
require_once( 'class.origin.php' );

if( ! class_exists( 'controller_origin' ) )
{
	/****************************************************
	 *
	 *  Inherits
	        function set_var( $var, $value )
	        function get_var( $var )
	        function var2data()
	        function fields2data( $fieldlist )
	        function LogError( $message, $level = PEAR_LOG_ERR )
	        function LogMsg( $message, $level = PEAR_LOG_INFO )
	        __construct( $loglevel = PEAR_LOG_DEBUG, $client = null )
	        function __call($method, $arguments)
	        function __get( $prop ) {
	        function __isset( $prop ) {
	        function is_supported_php() {
	        function object_var_names()
	        function user_access( $action )
	        function set( $field, $value = null, $enforce_only_native_vars = true )
	        function set_var( $var, $value )
	        function get( $field )
	        function get_var( $var )
	        function var2data()
	        function fields2data( $fieldlist )
	        function LogError( $message, $level = PEAR_LOG_ERR )
	        function LogMsg( $message, $level = PEAR_LOG_INFO )
	        function attach_eventloop()
	        function tell( $msg, $method )
	        function tell_eventloop( $caller, $event, $msg )
	        function dummy( $obj, $msg )
	        function register_with_eventloop()
	        function build_interestedin()
	        function notified( $obj, $event, $msg )
	 *  
	 * Provides:
	 * 	run() (stub)
	 * *************************************************/
	class controller_origin extends origin 
	{
		var $mode;
		var $action;
		var $selected_id;
		var $mode_callbacks = array();
		var $view;
		var $model;
		var $endpoint;
		function __construct(  $client = null )
		{
			parent::__construct( null, $client );
		}
		/*
		function __construct( $loglevel = PEAR_LOG_DEBUG, $client = null )
		{
			parent::__construct( $loglevel, $client );
		}
		 */
		function run()
		{
	       	}
	
	}
} //if !class_exists
?>
