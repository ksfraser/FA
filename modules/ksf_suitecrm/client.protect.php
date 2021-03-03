<?php
/******************************************//**
 *	Module based upon SOAP connection stuff
 *	in Asterisk (YAAI) code
 *		asteriskLogger.php
 *
 *	**************************************/

//
// Required libraries

require_once( "../ksf_modules_common/class.origin.php" );
require_once( "class.suitecrmSoapClient.php" );


//TESTS replace

global $sugar_config;
$sugar_config = array();
$sugar_config['site_url2'] = "https://mickey.ksfraser.com/ksfii/suitecrm/service/v4_1/";
$sugar_config['appname'] = "FA_Integration";
$sugar_config['soapuser2'] = "admin";
$sugar_config['user_hash2'] = md5('m1l1ce');
global $userGUID;
$sugar_config['site_url'] = "https://protect.ksfraser.ca/suitecrm/service/v4_1/";
$sugar_config['soapuser'] = "kevin";
$sugar_config['user_hash'] = md5('randomZaqwsx9');


/*
$o = new suitecrmSoapClient();
$o->soapLogin();
$nvl = new name_value_list();
$nvl->add_nvl( "filter", "accounts.name like '%Fraser%'" );
$nvl->add_nvl( "order_by", "date_entered" );
$nvl->add_nvl( "start", "0" );
$nvl->add_nvl( "Return", array() );
$nvl->add_nvl( "Link", "" );
$nvl->add_nvl( "Results", "10" );
$nvl->add_nvl( "Deleted", "0" );
$nvl->add_nvl( "unknown", "false" );

$o->setSoapParams( "Accounts", $nvl->get_nvl() );
$oret = $o->soapCall( "get_entry_list" );
//print "Returned object call 1";
//var_dump( $oret );
$p = new suitecrmSoapClient();
$p->set( 'url', $sugar_config['site_url2']  . "/soap.php" );
$p->set( 'username', $sugar_config['soapuser2'] );
$p->set( 'soapCredential', $sugar_config['user_hash2'] );
$p->setSoapClient();
$p->soapLogin();
$p->setSoapParams( "Accounts", $nvl->get_nvl() );
$pret = $p->soapCall( "get_entry_list" );
//print "Returned object call 2";
//var_dump( $pret );

foreach( $oret->entry_list as $recs )
{
	$success = false;
	//var_dump( $recs );
	$OUID = $recs->id;
	//Get the UID and see if in other list
	print "Searching for ID " . $OUID . "\n";
	$count = 0;
	foreach( $pret->entry_list as $precs )
	{

		if( $precs->id <> $OUID )
		{
			print " NO MATCH $OUID::$precs->id\n";
			$count++;
		}
		else
		{
			print " MATCH $OUID::$precs->id on $count record\n";
			$success = true;
			break;
		}
	}
	if( $success )
	{
		//do nothing, unless we compare fields for changes
	}
	else
	{
		//insert the record
		
	}
		
}

$nvl2 = new name_value_list();
$nvl2->add_nvl( "name", "soap Test Company" );
$nvl2->add_nvl( "phone", "4035272135" );
//$nvl2->add_nvl( "assigned_user_name", "kevin" );
$nvl2->add_nvl( "assigned_user_id", "2" );
//$nvl2->add_nvl( "created_by_name", "admin" );
//$nvl2->add_nvl( "modified_user_id", "1" );
//$nvl2->add_nvl( "description", "1" );
//$nvl2->add_nvl( "account_type", "" );
$nvl2->add_nvl( "phone_fax", "4039121654" );
$nvl2->add_nvl( "billing_address_street", "747 Windridge Road SW" );
//$nvl2->add_nvl( "billing_address_city", "Airdrie" );
//$nvl2->add_nvl( "billing_address_state", "Alberta" );
//$nvl2->add_nvl( "billing_address_postalcode", "T4B2R1" );
//$nvl2->add_nvl( "billing_address_country", "Canada" );
$nvl2->add_nvl( "phone_office", "5876000013" );
$nvl2->add_nvl( "email1", "kevin@ksfraser.com" );
//$o->setSoapParams( "Accounts", "");
var_dump( $nvl2->get_nvl() );

$p->set( "module_name", "Accounts" );
$p->set( "nvl", $nvl2->get_nvl() );
$p->set( "select_fields", array( "name", "last_name", "account_type", "phone_fax", "phone_office", "email1", "assigned_user_name", "assigned_user_id", "description" ) );
//$p->setSoapParams( "Accounts", array( $nvl2->get_nvl() ) );
$ret = $p->soapCall( "set_entry" );
var_dump( $ret );
//$p->set( "nvl", null );
//$p->set( "record_id", "efc604ff-fcd2-7625-65af-5deacd606f8e" );	//WORKS
$p->set( "record_id", $ret->id );
$ret2 = $p->get_entry(	); 
var_dump( $ret2 );

$p->set( "module_name", "Contacts" );
$nvl2->add_nvl( "name", "soap Test Person" );
$nvl2->add_nvl( "account_id", $ret->id );
$nvl2->add_nvl( 'first_name','Geek');
$nvl2->add_nvl( 'last_name','Smith');
$nvl2->add_nvl( 'email1','a@b.com');
var_dump( $nvl2->get_nvl() );
$p->set( "nvl", $nvl2->get_nvl() );
$ret3 = $p->soapCall( "set_entry" );
var_dump( $ret3 );

 */


$p = new suitecrmSoapClient();
/*
 * Taken care of by refactored classes.
$p->set( 'url', $sugar_config['site_url2']  . "/soap.php" );
$p->set( 'username', $sugar_config['soapuser2'] );
$p->set( 'soapCredential', $sugar_config['user_hash2'] );
$p->setSoapClient();
$p->soapLogin();
 */


$nvl = new name_value_list();
$nvl->add_nvl( "name", "soap Test Company 1540" );
$p->set( 'module_name', 'Accounts' );
$p->set( 'nvl', $nvl->get_nvl() );
/*
$p->set( 'soapParams', array( 	
			$p->get( 'session_id' ), 
			$p->get( 'module_name' ), 
			$p->get( 'nvl' ) ) );
 */
//$pret = $p->soapCall( "set_entry" );
$pret = $p->set_entry();
$account_id = $pret->id;

$nvl2 = new name_value_list();
$nvl2->add_nvl( "name", "soap Test Person 1540" );
$nvl2->add_nvl( "account_id", $account_id );
$nvl2->add_nvl( 'first_name','Geek 1540');
$nvl2->add_nvl( 'last_name','Smith 1540');
$nvl2->add_nvl( 'email1','a1540@b.com');

$p->set( 'module_name', 'Contacts' );
$p->set( 'nvl', $nvl2->get_nvl() );
/*
$p->set( 'soapParams', array( 	
			$p->get( 'session_id' ), 
			$p->get( 'module_name' ), 
			$p->get( 'nvl' ) ) );
 */
$pret = $p->set_entry();
