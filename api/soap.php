<?php

/*******************************************************************************
 * Copyright(c) @2011 ANTERP SOLUTIONS. All rights reserved.
 *
 * Released under the terms of the GNU General Public License, GPL, 
 * as published by the Free Software Foundation, either version 3 
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 *
 * Authors		    tclim
 * Date Created     Mar 16, 2011 1:05:21 PM
 ******************************************************************************/
//Include nusoap libraries
require_once ("lib/nusoap.php");
// Including the created php function file
require_once ("AntErpFa.php");

if(!defined('DIR_FA')) {
	define('DIR_FA', '..');
}

 $_http_protocol = "http://";
 
 if(!empty($_SERVER["HTTPS"])) {
	$_http_protocol = "https://";
 }

 //Get the FrontAccounting Root Directory
 $arr_fa_dir = explode("/", $_SERVER["PHP_SELF"]);
 $_dir_fa_root = "/fhs/frontaccounting/";

/* 
 //FrontAccounting Root Directory
 if (!empty($arr_fa_dir[1])) {
	$_dir_fa_root = "/" . $arr_fa_dir[1];
 }
*/

/*
if (!file_exists(DIR_FA.'/config_db.php')) {
	header("Location: ". DIR_FA ."/install/index.php");
}
*/

include_once (DIR_FA . "/config_ldap.php");
include_once (DIR_FA . "/lib/Ldap.php");
include_once (DIR_FA . "/config_db.php");
include_once (DIR_FA . "/includes/db/connect_db.inc");

global $db_connections;
global $tb_pref_counter;

//define method namespace
$namespace = $_http_protocol . $_SERVER["HTTP_HOST"] . $_dir_fa_root;
$server = new soap_server();
$server->debug_flag = false;
// Configure WSDL
$server->configureWSDL('antERPsoup', $namespace, $namespace . '/api/soap.php');
$server->wsdl->schemaTargetNamespace = $namespace;

/*************************************************************************************************
 * To retrieve Debtor Master Information
 *************************************************************************************************/
$server->wsdl->addComplexType('get_debtors_master', 'complexType', 'struct', 'all', '', array (
	'debtor_no' => array ('name' => 'debtor_no', 'type' => 'xsd:int'), 
	'name' => array ('name' => 'name','type' => 'xsd:string'),
	'debtor_ref' => array ('name' => 'debtor_ref','type' => 'xsd:string'),
	'address' => array ('name' => 'address','type' => 'xsd:string'),
	'tax_id' => array ('name' => 'tax_id','type' => 'xsd:string'),
	'currency_code' => array ('name' => 'currency_code','type' => 'xsd:string'),
	'payment_terms' => array ('name' => 'payment_terms','type' => 'xsd:int'),
	'discount' => array ('name' => 'discount','type' => 'xsd:double'),
	'pymt_discount' => array ('name' => 'pymt_discount','type' => 'xsd:double'),
	'credit_limit' => array ('name' => 'credit_limit','type' => 'xsd:float'),
	'notes' => array ('name' => 'notes','type' => 'xsd:string')
));

// Complex type for multiple record out put.
$server->wsdl->addComplexType('get_debtors_masters', 'complexType', 'array', '', 'SOAP-ENC:Array', array (), array (
	array (
		'ref' => 'SOAP-ENC:arrayType',
		'wsdl:arrayType' => 'tns:get_debtors_master[]'
	)
), 'tns:get_debtors_master');

// Registering get_debtors_masters function for WSDL generation
$server->register('get_debtors_masters', // method name
array('company' => 'xsd:string', 'user_id' => 'xsd:string', 'password' => 'xsd:string', 'filter_col' => 'xsd:string', 'filter_type' => 'xsd:string', 'filter_string' => 'xsd:string',  'order_by' => 'xsd:string', 'page_index' => 'xsd:int', 'total' => 'xsd:int'), 
// input parameters
array (
	'return' => 'tns:get_debtors_masters'
), // output parameters
$namespace, // namespace
$namespace .'#get_debtors_masters', // soapaction
'rpc', // style
'encoded' // use
);

// Registered Debtors Master
function get_debtors_masters($company, $user_id, $password, $filter_col, $filter_type, $filter_string, $order_by, $page_index, $total) {
	$ws = new AntErpFa;
		
	return $ws->getDebtorsMaster($company, $user_id, $password, $filter_col, $filter_type, $filter_string, $order_by, $page_index, $total);
}

/*************************************************************************************************
 * To retrieve Debtor Trans Information
 *************************************************************************************************/
$server->wsdl->addComplexType('get_debtor_tran', 'complexType', 'struct', 'all', '', array (
	'trans_no' => array ('name' => 'trans_no','type' => 'xsd:int'),	
	'debtor_no' => array ('name' => 'debtor_no','type' => 'xsd:int'),
	'branch_code' => array ('name' => 'branch_code','type' => 'xsd:int'),
	'tran_date' => array ('name' => 'tran_date','type' => 'xsd:string'),
	'due_date' => array ('name' => 'due_date','type' => 'xsd:string'),
	'reference' => array ('name' => 'reference','type' => 'xsd:string'),
	'order_' => array ('name' => 'order_','type' => 'xsd:int'),
	'ov_amount' => array ('name' => 'ov_amount','type' => 'xsd:double'),
	'ov_gst' => array ('name' => 'ov_gst','type' => 'xsd:double'),
	'ov_freight' => array ('name' => 'ov_freight','type' => 'xsd:double'),
	'ov_freight_tax' => array ('name' => 'ov_freight_tax','type' => 'xsd:double')
));

// Complex type for multiple record out put.
$server->wsdl->addComplexType('get_debtor_trans', 'complexType', 'array', '', 'SOAP-ENC:Array', array (), array (
	array (
		'ref' => 'SOAP-ENC:arrayType',
		'wsdl:arrayType' => 'tns:get_debtor_tran[]'
	)
), 'tns:get_debtor_tran');

// Registering get_debtor_trans function for WSDL generation
$server->register('get_debtor_trans', // method name
array('company' => 'xsd:string', 'user_id' => 'xsd:string', 'password' => 'xsd:string', 'filter_by' => 'xsd:string', 'order_by' => 'xsd:string', 'page_index' => 'xsd:int', 'total' => 'xsd:int'), 
// input parameters
array (
	'return' => 'tns:get_debtor_trans'
), // output parameters
$namespace, // namespace
$namespace .'#get_debtor_trans', // soapaction
'rpc', // style
'encoded' // use
);

// Registered Debtor Trans
function get_debtor_trans($company, $user_id, $password, $filter_by, $order_by, $page_index, $total) {
	$ws = new AntErpFa;
		
	return $ws->getDebtorTrans($company, $user_id, $password, $filter_by, $order_by, $page_index, $total);
}


/*************************************************************************************************
 * To Create Debtor Master Information
 *************************************************************************************************/
$server->wsdl->addComplexType('create_debtors_masters', 'complexType', 'struct', 'all', '', array (
	'debtor_no' => array ('name' => 'debtor_no', 'type' => 'xsd:string')
));

// Registering create_debtors_masters function for WSDL generation
$server->register('create_debtors_masters', // method name
array('company' => 'xsd:string', 'user_id' => 'xsd:string', 'password' => 'xsd:string', 'id' => 'xsd:string', 'company_name' => 'xsd:string', 'short_name' => 'xsd:string', 'first_name' => 'xsd:string', 'last_name' => 'xsd:string', 'address' => 'xsd:string', 
	  'email' => 'xsd:string', 'phone' => 'xsd:string', 'mobile' => 'xsd:string', 'fax' => 'xsd:string', 'tax_group'=> 'xsd:string', 'tax_id' => 'xsd:string', 'area'=> 'xsd:string', 'country_code'=> 'xsd:string', 'currency_code' => 'xsd:string', 'customer_type' => 'xsd:string', 'payment_terms' => 'xsd:string', 'credit_status_id' => 'xsd:string', 'notes' => 'xsd:string'), 
// input parameters
array (
	'return' => 'tns:create_debtors_masters'
), // output parameters
$namespace, // namespace
$namespace .'#create_debtors_masters', // soapaction
'rpc', // style
'encoded' // use
);

// Registered Debtors Master
function create_debtors_masters($company, $user_id, $password, $id, $company_name, $short_name, $first_name, $last_name, $address, $email, $phone, $mobile, $fax, $tax_group, $tax_id, $area, $country_code, $currency_code, $customer_type, $payment_terms, $credit_status_id, $notes) {
	$ws = new AntErpFa;
		  
	return $ws->createDebtorsMaster($company, $user_id, $password, $id, $company_name, $short_name, $first_name, $last_name, $address, $email, $phone, $mobile, $fax, $tax_group, $tax_id, $area, $country_code, $currency_code, $customer_type, $payment_terms, $credit_status_id, $notes);
}

//=============
// Output return
//=============
$HTTP_RAW_POST_DATA = isset ($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
$HTTP_RAW_POST_DATA = file_get_contents("php://input");
$server->service($HTTP_RAW_POST_DATA);
exit ();
?>
