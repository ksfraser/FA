<?php

/**************************************************************************
*
*	CONTROLLER
*
*	20200708 added functions that class.CONTROLLER.php contain
* 	 for future migration of other classes
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
			if( isset( $_POST['Mode'] ) )
				$this->set_var( "mode", $_POST['Mode'] );
			else
				$this->set_var( "mode", "unknown" );
			if( isset( $_POST['action'] ) )
				$this->set_var( "action", $_POST['action'] );
			else
			if( isset( $_GET['action'] ) )
				$this->set_var( "action", $_GET['action'] );
	
			if( isset( $_POST['selected_id'] ) )
				$this->set_var( "selected_id", $_POST['selected_id'] );
			//$this->view = new VIEW();
			//$this->model = NULL;
			/*********************************
			*	Need to set mode_callbacks
			*	in inheriting classes
			*********************************/
			$this->mode_callbacks["unknown"] = "config_form";
	           
			$this->config_values[] = array( 'pref_name' => 'mode', 'label' => 'Mode' );
	
	                //The forms/actions for this module
	                //Hidden tabs are just action handlers, without accompying GUI elements.
	                //$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
	                $this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'config_form', 'hidden' => FALSE );

		}
		/*********************************************************//**
		*
		*
		* @since 20200708
		* @param prefarr array of preferences
		* @return none
		*************************************************************/
		function loadprefs( $prefarr = NULL )
		{
			if( isset( $this->model ) AND is_callable( $this->model->loadprefs( $prefarr ) ) )
			{
				$this->model->loadprefs( $prefarr );
			}
		}
		/*********************************************************//**
		*
		*
		* @since 20200708
		* @param prefarr array of preferences
		* @return none
		*************************************************************/
		function updateprefs( $prefarr = NULL )
		{
			if( isset( $this->model ) AND is_callable( $this->model->updateprefs( $prefarr ) ) )
			{
				$this->model->updateprefs( $prefarr );
			}
		}
		/*********************************************************//**
		*
		*
		* @since 20200708
		* @param config_var configuration variable for which we are setting the value
		* @return none
		*************************************************************/
		function set_config( $config_var, $config_value )
		{
			//For now until I code this fcn
			throw new Exception( "This function hasn't been coded", KSF_FCN_NOT_EXIST );
			$this->tell_eventloop( $this, 'NOTIFY_CONFIG_UPDATED', $config_var );
		}
		/*********************************************************//**
		*
		*
		* @since 20200708
		* @param config_var configuration variable for which we are seeking the value
		* @return none
		*************************************************************/
		function get_config( $config_var )
		{
			//For now until I code this fcn
			throw new Exception( "This function hasn't been coded", KSF_FCN_NOT_EXIST );
		}
		/************************************************************************************//**
		* Generic INSTALL function
		*
		*	An APP may need a CONFIG/PREFs table
		*		create table
		*		grab the list of config vars
		*		set default values
		*		update the table (store values)
		*		redirect...
		*	A class/module will probably need a model based table
		*		create the table
		*
		* @since 20200708
		* @param none
		* @return bool
		********************************************************************************************/		
		/*@bool@*/function install()
		{
			try
			{
				$get_confirm = $this->get_config( 'install_confirm' );
				if( $get_confirm )
				{
					if( isset( $this->view ) AND is_callable( $this->view->confirm_screen( 'install_confirm' ) ) )
					{
						$this->view->confirm_screen( 'install_confirm' );
					}
				}
				else
				{
					if( isset( $this->model ) AND is_callable( $this->model->install() ) )
					{
						$this->model->install();
					}
				}
			} catch( Exception $e )
			{
				$code = $e->getCode();
				$msg = $e->getMessage();
				switch( $code )
				{
					case KSF_CONFIG_NOT_EXIST:
						if( isset( $this->model ) AND is_callable( $this->model->install() ) )
						{
							$this->model->install();
						}
						break;
					case KSF_FCN_NOT_EXIST:
						return FALSE;
					default:
						throw $e;
				}
			}
		}
		/*
		function __construct( $loglevel = PEAR_LOG_DEBUG, $client = null )
		{
			parent::__construct( $loglevel, $client );
		}
		 */
		/*********************************************************//**
		*
		*
		* @since 20200708
		* @param none
		* @return none
		*************************************************************/
		function config_form()
		{
			if( isset( $this->view ) AND is_callable( $this->view->config_form( $data ) ) )
			{
				$this->view->config_form( $data );
			}
		}
		/*********************************************************//**
		*
		*
		* @since 20200708
		* @param none
		* @return none
		*************************************************************/
		function related_tabs()
		{
			if( isset( $this->view ) AND is_callable( $this->view->related_tabs( $tabs, $action ) ) )
			{
				$this->view->related_tabs( $this->tabs, $this->action );
			}
		}
		/*********************************************************//**
		*
		*
		* @since 20200708
		* @param none
		* @return none
		*************************************************************/
		function show_form()
	        {
	                $action = $this->action;
	                foreach( $this->tabs as $tab )
	                {
	                        if( $action == $tab['action'] )
	                        {
	                                //Call appropriate form
	                                $form = $tab['form'];
					if( isset( $this->view ) AND is_callable( $this->view->$form() ) )
					{
						$this->view->$form();
					}
	                        }
	                }
	        }
		/*********************************************************//**
		*
		*
		* @since 1.0.0   
		* @param none
		* @return none
		*************************************************************/
		function run()
		{
	       	}
	
	}
} //if !class_exists
?>
