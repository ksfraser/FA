<?php

require_once( 'class.categories_xref_model.php' );
require_once( 'class.categories_xref_view.php' );

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

//This is a simple MODEL class.  AFAIK there aren't any bugs nor TODOs.

require_once( 'class.woo_interface.php' );

/*************************************************//*****
 * Controller class of the categories_xref table
 *
 * Current design is this class updates the table.
 * 
 ******************************************************/
class categories_xref_controller extends woo_interface {
	var $fa_cat;
	var $woo_cat;
	var $description;
	var $updated_ts;
	private $model;
	private $view;
	function __construct( $caller = null)
	{
		parent::__construct( null, null, null, null, $caller );
		$this->model = new categories_xref_model( $caller );
		$this->view = new categories_xref_view( $caller );
	}
	function build_interestedin()
	{
		$this->interestedin[WOO_SEND_CATEGORY]['function'] = "update_xref";
		$this->interestedin[WOO_RECV_CATEGORY]['function'] = "update_xref";
		$this->interestedin[WOO_MATCH_CATEGORY]['function'] = "update_xref";
	}
	function reset_endpoint() {}
	function update_xref( $obj, $msg )
	{
	}
	function define_table()
	{
		throw new Exception( "This function should not ahve been called.  " );
	}
	/************************************************************************************************************//**
	 * Update a cross-ref of FA category x WooCommerce Category
	 *
	 * ************************************************************************************************************/
	function update()
	{
		$this->model->update_table( $this );
	}
	/************************************************************************************************************//**
	 * Insert a cross-ref of FA category x WooCommerce Category
	 *
	 * ************************************************************************************************************/
	function insert()
	{
		$this->model->insert_table( $this );
	}
	/************************************************************************************************************//**
	 * Get the WooCommerce category_id from the FrontAccounting one
	 *
	 * @returns int WooCommerce Category ID
	 * ************************************************************************************************************/
	/*@int@*/function get_woo_cat()
	{
		$this->woo_cat = $this->model->get_woo_cat( $this );
	}
	/************************************************************************************************************//**
	 * Get the FrontAccounting category_id from the WooCommerce one
	 *
	 * @returns int FA Category ID
	 * ************************************************************************************************************/
	/*@int@*/function get_fa_cat()
	{
		$this->woo_cat = $this->model->get_fa_cat( $this );
	}
}

?>
