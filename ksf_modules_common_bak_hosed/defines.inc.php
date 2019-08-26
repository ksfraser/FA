<?php

display_notification( __FILE__ .  "::"  . __LINE__, "WARN" );

$path_to_faroot= dirname ( realpath ( __FILE__ ) ) . "/../..";
//global $path_to_faroot;
//global $path_to_ksfcommon;
//$path_to_faroot = __DIR__ . "/../../";
$path_to_ksfcommon = __DIR__ . "/";

//require_once( $path_to_faroot . '/includes/db/connect_db.inc' ); //db_query, ...
//require_once( $path_to_faroot . '/includes/errors.inc' ); //check_db_error, ...
include_once( 'Log.php' );	//PEAR Logging
//LOG LEVELS
if( !defined( 'PEAR_LOG_CRIT' ))
{
	define( 'PEAR_LOG_EMERG', 0 );
	define( 'PEAR_LOG_ALERT', 1 );
	define( 'PEAR_LOG_CRIT', 2 );
	define( 'PEAR_LOG_ERR ', 3 );
	define( 'PEAR_LOG_WARNING', 4 );
	define( 'PEAR_LOG_NOTICE', 5 );
	define( 'PEAR_LOG_INFO ', 6 );
	define( 'PEAR_LOG_DEBUG ', 7 );
}

//Dream Payments
define( 'DREAM_VARCHAR_SIZE', 255 );

define( 'NOT_SELECTED', -1 );
define( 'PRIMARY_KEY_NOT_SET', 5730 );


//table stock_master
define( 'STOCK_ID_LENGTH_ORIG', 20 );
define( 'STOCK_ID_LENGTH', 64 );
define( 'ITEM_CODE_LENGTH_ORIG', 20 );
define( 'ITEM_CODE_LENGTH', STOCK_ID_LENGTH );
define( 'DESCRIPTION_LENGTH', 32 );
define( 'GL_ACCOUNT_NAME_LENGTH', 32 );

//table stock_category
define( 'CAT_DESCRIPTION_LENGTH', 20 );

//table suppliers
define( 'SUPP_NAME_LENGTH', 60 );
define( 'SUPP_WEBSITE_LENGTH', 100 );
define( 'SUPP_REF_LENGTH', 30 );
define( 'SUPP_ACCOUNT_NO_LENGTH', 40 );
//EVENTLOOP Events
$eventcount = 0;
define( 'WOO_DUMMY_EVENT', $eventcount ); $eventcount++;	//Used by woo_interface:build_interestedin as example
define( 'WOO_PRODUCT_INSERT', $eventcount ); $eventcount++;
define( 'WOO_PRODUCT_UPDATE', $eventcount ); $eventcount++;
define( 'WOO_PRODUCT_PRICE_UPDATE', $eventcount ); $eventcount++;
define( 'WOO_PRODUCT_QOH_UPDATE', $eventcount ); $eventcount++;
define( 'WOO_PRODUCT_SPECIALS_UPDATE', $eventcount ); $eventcount++;
define( 'WOO_PRODUCT_TAXDATA_UPDATE', $eventcount ); $eventcount++;
define( 'WOO_PRODUCT_SHIPDIM_UPDATE', $eventcount ); $eventcount++;
define( 'WOO_PRODUCT_CROSSSELL_UPDATE', $eventcount ); $eventcount++;
define( 'WOO_PRODUCT_CATEGORY_UPDATE', $eventcount ); $eventcount++;
define( 'FA_PRODUCT_PRICE_UPDATE', $eventcount ); $eventcount++;
define( 'FA_PRODUCT_QOH_UPDATE', $eventcount ); $eventcount++;
define( 'FA_PRODUCT_CATEGORY_UPDATE', $eventcount ); $eventcount++;
define( 'FA_CUSTOMER_CREATED', $eventcount ); $eventcount++;


function currentdate()
{
	return date( 'Y-m-d' );
}

function currenttime()
{
	return date( 'Y-m-d H:i:s' );
}

define( 'SUCCESS', TRUE );
define( 'FAILURE', FALSE );

//set_global_stock_item(), get_global_stock_item()
//Need to check following functions
//write_customer_trans_detail_item()
//add_grn_to_trans() 
$stock_id_tables = array();	//stock_id, item_code, stk_code, idx_stock_id, master_stock_id, child_stock_id, sku, barcode, slug, item_img_name
$stock_id_tables[] = array( 'table' => TB_PREF . 'bom', 'column' => 'parent', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );//Need to dbl check this one!
$stock_id_tables[] = array( 'table' => TB_PREF . 'bom', 'column' => 'component', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );//Need to dbl check this one!
$stock_id_tables[] = array( 'table' => TB_PREF . 'debtor_trans_details', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'grn_items', 'column' => 'item_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'item_codes', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'item_codes', 'column' => 'item_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'loc_stock', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'prices', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'purch_data', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH  );
$stock_id_tables[] = array( 'table' => TB_PREF . 'purch_order_details', 'column' => 'item_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'qoh', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'sales_order_details', 'column' => 'stk_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'stock_master', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'stock_moves', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'supp_invoice_items', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'wo_issue_items', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'wo_requirements', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'workorders', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'woo', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
//$stock_id_tables[] = array( 'table' => TB_PREF . '', 'column' => 'stock_id' );


/**************************************************************************//**
 *Error Handling for try/throw/catch/finally
 *
 *
 * ****************************************************************************/
define( 'KSF_FIELD_NOT_SET', 5731 );
define( 'KSF_VALUE_NOT_SET', 5732 );
define( 'KSF_FIELD_NOT_CLASS_VAR', 5733 );

function exceptionErrorHandler($errNumber, $errStr, $errFile, $errLine ) {
        throw new ErrorException($errStr, 0, $errNumber, $errFile, $errLine);
    }
//set_error_handler('exceptionErrorHandler');

interface IException
{
    /* Protected methods inherited from Exception class */
    public function getMessage();                 // Exception message 
    public function getCode();                    // User-defined Exception code
    public function getFile();                    // Source filename
    public function getLine();                    // Source line
    public function getTrace();                   // An array of the backtrace()
    public function getTraceAsString();           // Formated string of trace
    
    /* Overrideable methods inherited from Exception class */
    public function __toString();                 // formated string for display
    public function __construct($message = null, $code = 0);
}

abstract class CustomException extends Exception implements IException
{
    protected $message = 'Unknown exception';     // Exception message
    private   $string;                            // Unknown
    protected $code    = 0;                       // User-defined exception code
    protected $file;                              // Source filename of exception
    protected $line;                              // Source line of exception
    private   $trace;                             // Unknown

    public function __construct($message = null, $code = 0)
    {
        if (!$message) {
            throw new $this('Unknown '. get_class($this));
        }
        parent::__construct($message, $code);
    }
    
    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                                . "{$this->getTraceAsString()}";
    }
}

//Can now create custom Exceptions:
//	class TestException extends CustomException {}


?>

