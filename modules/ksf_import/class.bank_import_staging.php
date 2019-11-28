<?php

/****************************************************************************************
 * Table and handling class for staging of imported financial data
 *
 * This table will hold each record that we are importing.  That way we can check if
 * we have already seen the record when re-processing the same file, or perhaps one
 * from the same source that overlaps dates so we would have duplicate data.
 *
 * *************************************************************************************/


$path_to_root = "../..";

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

/*
 *
 * Each import type needs to read in the source document, and process line by line placing a record into this class.
 * This class then needs to insert the record.
 *
 * This table should not have any views (forms).
 * */

require_once( '../ksf_modules_commone/class.generic_fa_interface.php' );
require_once( '../ksf_modules_commone/defines.inc.php' );

class bank_import_staging extends generic_fa_interface_model {
	var $id_bank_import_staging;	//!< Index of table

	protected $transaction_id;		//Dream Payments order_num //WooCommerce	//OFX TRNUID
	protected $transaction_date; //!<Date	//Dream Payments				//OFX DTPOSTED
	protected $transaction_time;		//Dream Payments				//OFX DTPOSTED
	//protected $order_number;		
	protected $merchant_user;		//Dream Payments				//OFX 
	protected $gps_location;		//Dream Payments
	protected $ip_address;			//Dream Payments
	protected $transaction_type;		//Dream Payments				//OFX TRNTYPE
	protected $payment_method;		//Dream Payments	//WooCommerc method_id
	protected $payment_method_title;				//WooCommerc method_title
	protected $entry_type;			//Dream Payments
	protected $card_type;			//Dream Payments
	protected $card_number;			//Dream Payments
	protected $subtotal;	//!<float	//Dream Payments
	protected $tax;		//!<float	//Dream Payments
	protected $tip;		//!<float	//Dream Payments
	protected $total;	//!<float	//Dream Payments				//OFX TRNAMT
	protected $tcc;				//Dream Payments
	protected $tcd;				//Dream Payments
	protected $receipt_sent;		//Dream Payments
	protected $receipt_email;		//Dream Payments
	protected $receipt_mobile_number;	//Dream Payments
	protected $order_status;		//Dream Payments	//WooCommerc paid
	protected $bank_id;									//OFX BANKID
	protected $bank_name;
	protected $account_id;									//OFX ACCTID
	protected $FID;										//OFX FITID or FID
	protected $org;										//OFX ORG
	protected $memo;									//OFX MEMO
	protected $name;									//OFX NAME - TRABSFER, CHEQUE, DEPOSIT
	protected $currency;									//OFX CURRDEF
	protected $check_number;								//OFX CHECKNUM
	protected $ledger_balance;								//OFX LEDGERBAL/BALAMT
	protected $ref_number;									//OFX REFNUM
	protected $source;	//!<string what is the source e.g. "dream" or "square" or PCF
	protected $inserted_fa;	//!<bool has this record been added to ledgers?
	protected $vendor_SIC;									//OFX SIC

	function __construct()
	{
	}
	function define_table()
	{
		$ind = "id_" . $this->iam;
		$this->fields_array[] = array('name' => $ind, 'type' => 'int(11)', 'auto_increment' => 'yes', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read' );
		$this->table_details['tablename'] = $this->company_prefix . $this->iam;
		$this->table_details['primarykey'] = $ind;
		$this->table_details['orderby'] = 'transaction_date, transaction_id';
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "transaction_id";
		$this->table_details['index'][0]['keyname'] = "transaction_id";
		//$this->fields_array[] = array('name' => 'stock_id', 'label' => 'SKU', 'type' => 'varchar(256)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite');
		//$sidl = 'varchar(' . STOCK_ID_LENGTH . ')';
		//$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';

		$this->fields_array[] = array('name' => 'inserted_fa', 'label' => 'Inserted into FA', 'type' => 'bool', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'transaction_id', 'label' => 'transaction_id', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'WooCommerce Trans ID ' );
		$this->fields_array[] = array('name' => 'transaction_date', 'label' => 'transaction_date', 'type' => 'DATE', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '	Dream Payments ' );
		$this->fields_array[] = array('name' => 'transaction_time', 'label' => 'transaction_time', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '	Dream Payments ' );
		$this->fields_array[] = array('name' => 'merchant_user', 'label' => 'merchant_user', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '	Dream Payments ' );
		$this->fields_array[] = array('name' => 'gps_location', 'label' => 'gps_location', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '	Dream Payments ' );
		$this->fields_array[] = array('name' => 'ip_address', 'label' => 'ip_address', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '		Dream Payments ' );
		$this->fields_array[] = array('name' => 'transaction_type', 'label' => 'transaction_type', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '	Dream Payments ' );
		$this->fields_array[] = array('name' => 'payment_method', 'label' => 'payment_method', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '	Dream Payments ' );
		$this->fields_array[] = array('name' => 'payment_method_title', 'label' => 'payment_method_title', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'WooCommerce Method Title' );
		$this->fields_array[] = array('name' => 'entry_type', 'label' => 'entry_type', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '		Dream Payments ' );
		$this->fields_array[] = array('name' => 'card_type', 'label' => 'card_type', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '		Dream Payments ' );
		$this->fields_array[] = array('name' => 'card_number', 'label' => 'card_number', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '		Dream Payments ' );
		$this->fields_array[] = array('name' => 'subtotal', 'label' => 'subtotal', 'type' => 'float', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '		Dream Payments ' );
		$this->fields_array[] = array('name' => 'tax', 'label' => 'tax', 'type' => 'float', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '			Dream Payments ' );
		$this->fields_array[] = array('name' => 'tip', 'label' => 'tip', 'type' => 'float', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '			Dream Payments ' );
		$this->fields_array[] = array('name' => 'total', 'label' => 'total', 'type' => 'float', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '		Dream Payments ' );
		$this->fields_array[] = array('name' => 'tcc', 'label' => 'tcc', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '			Dream Payments ' );
		$this->fields_array[] = array('name' => 'tcd', 'label' => 'tcd', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '			Dream Payments ' );
		$this->fields_array[] = array('name' => 'receipt_sent', 'label' => 'receipt_sent', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '	Dream Payments ' );
		$this->fields_array[] = array('name' => 'receipt_email', 'label' => 'receipt_email', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '	Dream Payments ' );
		$this->fields_array[] = array('name' => 'receipt_mobile_number', 'label' => 'receipt_mobile_number', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Dream Payments ' );
		$this->fields_array[] = array('name' => 'order_status', 'label' => 'order_status', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => '	Dream Payments ' );
	
		$this->fields_array[] = array('name' => 'bank_id', 'label' => 'OFX BANKID', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'OFX BANKID' );
		$this->fields_array[] = array('name' => 'bank_name', 'label' => 'Bank Name', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Bank Name' );
		$this->fields_array[] = array('name' => 'account_id', 'label' => 'OFX ACCTID', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'OFX ACCTID ' );			
		$this->fields_array[] = array('name' => 'FID', 'label' => 'OFX FITID or FID', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'OFX FITID or FID ' );	
		$this->fields_array[] = array('name' => 'org', 'label' => 'OFX ORG', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'OFX ORG ' );
		$this->fields_array[] = array('name' => 'memo', 'label' => 'OFX MEMO', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'OFX MEMO ' );
		$this->fields_array[] = array('name' => 'name', 'label' => 'OFX NAME', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'OFX NAME ' );	// - TRABSFER, CHEQUE, DEPOSIT
		$this->fields_array[] = array('name' => 'currency', 'label' => 'OFX CURRDEF', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'OFX CURRDEF ' );
		$this->fields_array[] = array('name' => 'check_number', 'label' => 'OFX CHECKNUM', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'OFX CHECKNUM ' );
		$this->fields_array[] = array('name' => 'ledger_balance', 'label' => 'OFX LEDGERBAL/BALAMT', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'OFX LEDGERBAL/BALAMT ' );
		$this->fields_array[] = array('name' => 'ref_number', 'label' => 'OFX REFNUM', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'OFX REFNUM ' );
		$this->fields_array[] = array('name' => 'source', 'label' => 'Line Source', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Line Source eg Dream ' );
		$this->fields_array[] = array('name' => 'vendor_SIC', 'label' => 'Vendor Store ID Code', 'type' => 'varchar(STOCK_ID_LENGTH)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'comment' => 'Store ID from OFX' );
	}
	function insert_transaction()
	{
		$this->insert_data( get_object_vars($this) );
	}

	
}
