<?php

/****************************
* We want to be able to view and edit the WOO master table
*
*  Provide 2 'tables':
*	Summary with navigation of records in the table
*	Row editor
*
*****************************/

class view_woo_table extends woo_interface
{
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
		parent::__construct($serverURL = " ", $key, $secret, $options, $client = null);
	}
 	/*********************************************************************************//**
         *master_form
         *      Display 2 forms - the summary of items with edit/delete
         *              The edit/entry form for 1 row of data
         *      assumes entry_array has been built (constructor)
         *      assumes table_details has been built (constructor)
         *      assumes selected_id has been set (constructor?)
         *      assumes iam has been set (constructor)
         *
         * ***********************************************************************************/
        function master_form()
        {
		parent::master_form();
	}


}

?>
