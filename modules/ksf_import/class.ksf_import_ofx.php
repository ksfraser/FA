<?php


$path_to_root = "../..";

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

 require_once( '../ksf_modules_common/defines.inc.php' );
require_once( $path_to_ksfcommon . '/class.table_interface.php' ); 
require_once( $path_to_ksfcommon . '/class.generic_fa_interface.php' );

class ofx_request_online
{
	    const REQUEST = <<<'OFX'
OFXHEADER:100
DATA:OFXSGML
VERSION:102
SECURITY:NONE
ENCODING:USASCII
CHARSET:1252
COMPRESSION:NONE
OLDFILEUID:NONE
NEWFILEUID:NONE


<OFX>
    <SIGNONMSGSRQV1>
        <SONRQ>
            <DTCLIENT>${TIMESTAMP}
            <USERID>${USER_ID}
            <USERPASS>${PASSWORD}
            <LANGUAGE>ENG
            <FI>
            <ORG>${ORG}
            <FID>${FID}
            </FI>
            <APPID>QWIN
            <APPVER>0900
        </SONRQ>
    </SIGNONMSGSRQV1>
    <BANKMSGSRQV1>
        <STMTTRNRQ>
            <TRNUID>23382938
            <STMTRQ>
                <BANKACCTFROM>
                    <BANKID>${BANK_ID}
                    <ACCTID>${ACCT_ID}
                    <ACCTTYPE>CHECKING
                </BANKACCTFROM>
                <INCTRAN>
                    <INCLUDE>Y
                </INCTRAN>
            </STMTRQ>
        </STMTTRNRQ>
    </BANKMSGSRQV1>
</OFX>
OFX;

	protected $uri;
	protected $user_id;
	protected $password;
	protected $org;
	protected $fid;
	protected $bank_id;
	protected $acct_id;
	function __construct( $uri = null, $user_id = null, $password = null, $org = null, $fid = null, $bank_id = null, $acct_id = null)
	{
		$this->uri = $uri;
        	$this->user_id = $user_id;
        	$this->password = $password;
        	$this->org = $org;
        	$this->fid = $fid;
        	$this->bank_id = $bank_id;
		$this->acct_id = $acct_id;
	        if (empty($uri) ||empty($user_id) || empty($password) || empty($org)
	            || empty($fid) || empty($bank_id) || empty($acct_id))
        	{
        	    throw new Exception("Did not supply all parameters.");
		}
	} //construct
		function fetch()
		{
			$request = OFX::REQUEST;
		        $tz = strftime("%z", time());
		        $tz = intval($tz) / 100;  // Have to hack off the "00" at the end.
		        if ($tz >= 0) {
		            $tz = "+$tz";
		        }
		        $now = strftime("%Y%m%d%H%M%S.000[$tz:%Z]", time());
		
		        $request = str_replace('${TIMESTAMP}', $now, $request);
		        $request = str_replace('${USER_ID}', $this->_user_id, $request);
		        $request = str_replace('${PASSWORD}', $this->_password, $request);
		        $request = str_replace('${ORG}', $this->_org, $request);
		        $request = str_replace('${FID}', $this->_fid, $request);
		        $request = str_replace('${BANK_ID}', $this->_bank_id, $request);
		        $request = str_replace('${ACCT_ID}', $this->_acct_id, $request);
		
		        // Perform the HTTP request.
		        $curl = curl_init($this->_uri);
		        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		        curl_setopt($curl, CURLOPT_POST, true);
		        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		            "Content-Type: application/x-ofx",
		            "Accept: */*, application/x-ofx",
		        ));
		        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		        $result = curl_exec($curl);
			curl_close($curl);
			return $result;
		} //fetch
} //class

//**************************************************WANTS
//	Match SIC against an expense type (dest GL)
//	Match accountNumber against a Bank Account (GL)
//	Recognize duplicated entries (date, amount, desc?)
//	Record imported entries
//	Generate both sides of a transaction (Expense, CC/bank account) for non dupe entries (use quick entries...)
//	For dupe entries mark for review
//	Recognize where a common entry is a split transaction? (quick entry?)


/*************************************************************//**
 * 
 *
 * Inherits:
 *                 function __construct( $host, $user, $pass, $database, $pref_tablename )
                function eventloop( $event, $method )
                function eventregister( $event, $method )
                function add_submodules()
                function module_install()
                function install()
                function loadprefs()
                function updateprefs()
                function checkprefs()
                function call_table( $action, $msg )
                function action_show_form()
                function show_config_form()
                function form_export()
                function related_tabs()
                function show_form()
                function base_page()
                function display()
                function run()
                function modify_table_column( $tables_array )
                / *@fp@* /function append_file( $filename )
                /*@fp@* /function overwrite_file( $filename )
                /*@fp@* /function open_write_file( $filename )
                function write_line( $fp, $line )
                function close_file( $fp )
                function file_finish( $fp )
                function backtrace()
                function write_sku_labels_line( $stock_id, $category, $description, $price )
		function show_generic_form($form_array)
 * Provides:
        function __construct( $prefs )
        function define_table()
        function form_ksf_import_ofx
        function form_ksf_import_ofx_completed
        function action_show_form()
        function install()
        function master_form()
 * 
 *
 *
 * This class acts as a controller
 * ***************************************************************/


class ksf_import_ofx extends generic_fa_interface_controller {
	var $id_ksf_import_ofx;	//!< Index of table
	var $filename;
	function __construct( $prefs )
	{
		parent::__construct( null, null, null, null, $prefs );	//generic_interface has legacy mysql connection
									//not needed with the $prefs
		$this->set_var( 'found', $this->is_installed() );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		require_once( 'class.ksf_import_ofx_view.php' );
		$this->view = new ksf_import_ofx_view( $prefs, $this );
		$this->tabs = $this->view->tabs;	//Short term work around until VIEW code everywhere

		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
		require_once( 'class.ksf_import_ofx_model.php' );
		$this->model = new ksf_import_ofx_model( ksf_import_ofx_PREFS, $this );	//defines the tabl
		$this->model->set( found, $this->get( found ) );

	}
	function web_fetch()
	{
		$ofx_fetch = new ofx_request_online();
		$data = $ofx_fetch->fetch();
	}
	function loadfile()
	{
		require_once( 'OfxParser/Ofx.php' );
		require_once( 'OfxParser/Parser.php' );
		require_once( 'OfxParser/Entities/AbstractEntity.php' );
		require_once( 'OfxParser/Entities/AccountInfo.php' );
		require_once( 'OfxParser/Entities/BankAccount.php' );
		require_once( 'OfxParser/Entities/Institute.php' );
		require_once( 'OfxParser/Entities/SignOn.php' );
		require_once( 'OfxParser/Entities/Statement.php' );
		require_once( 'OfxParser/Entities/Status.php' );
		require_once( 'OfxParser/Entities/Transaction.php' );
		$ofxParser = new \OfxParser\Parser();
		return $ofxParser->loadFromFile( $this->filename );

	}
	function run()
	{
		$ofx = $this->loadfile();

		$institute = $ofx->signOn->institute;	//ID, Name
		$this->model->set( "bank_id", $institute->id );	//"00024"
		$this->model->set( "bank_name", $institute->name );	//"President's Choice Financial"
		//If we have this module log into a bank account to do the download, we might care about status codes etc
		/*
		 * ["signOn"]=> object(OfxParser\Entities\SignOn)#4 (4) { 
			["status"]=> object(OfxParser\Entities\Status)#8 (3) { 
				["code"]=> object(SimpleXMLElement)#10 (1) { [0]=> string(1) "0" } 
				["severity"]=> object(SimpleXMLElement)#11 (1) { [0]=> string(4) "INFO" } 
				["message"]=> object(SimpleXMLElement)#12 (0) { } 
			} 
			["date"]=> object(DateTime)#13 (3) { 
				["date"]=> string(26) "2016-03-06 13:34:48.000000" 
				["timezone_type"]=> int(3) 
				["timezone"]=> string(3) "UTC" 
			} 
			["language"]=> object(SimpleXMLElement)#7 (1) { [0]=> string(3) "ENG" }
  		}
 		*/
		/***************Bank Account*************************************************/
		$bankAccounts = reset($ofx->bankAccounts);
		//Assuming only 1 bank account in the file...
		$bankAccount = $bankAccounts[0];
		// Get the statement transactions for the account
		$accountNumber = $bankAccount->accountNumber;	//"51811615207772930"
		$accountType = $bankAccount->accountType;	//""
		$balance = $bankAccount->balance;		//"123.45"
		$balanceDateTime = $bankAccount->balanceDate->date;	//"2016-03-06 13:34:48.000000"
		$balanceTimezone = $bankAccount->balanceDate->timezone;	//"UTC"
		$routingNumber = $bankAccount->routingNumber;	//""
		$this->model->set( "account_id", $accountNumber );
		$this->model->set( "source", $this->filename );
		$this->model->set( "currency", $bankAccount->statement->currency);
		// Get the statement start and end dates
		$startDate = $bankAccount->statement->startDate;
		$endDate = $bankAccount->statement->endDate;
		//$this->model->set( "statement_startdate", $startDate);
		//$this->model->set( "statement_enddate", $endDate);
		//$this->model->set( "transactionUid", $bankAccount->statement->transactionUid);
		//$this->model->set( "agencyNumber", $bankAccount->statement->agencyNumber);
		/***********Transactions*****************************************************/
		$transactions = $bankAccount->statement->transactions;
		foreach( $transactions as $transaction )
		{
			$this->model->set( "transaction_type", $transaction->type );			//DEBIT (purchase) or CREDIT (payment on credit card)
			$this->model->set( "", $transaction->date);			//posted date
			$this->model->set( "transaction_date", $transaction->userInitiatedDate->date);	//trans date
			$this->model->set( "total", $transaction->amount);
			$this->model->set( "transaction_id", $transaction->uniqueId);
			$this->model->set( "name", $transaction->name);
			$this->model->set( "memo", $transaction->memo);
			$this->model->set( "vendor_SIC", $transaction->sic[0]);			//Store identifier
			$this->model->set( "check_number", $transaction->checkNumber);
			var_dump( $this->model );
//			$this->model->insert_transaction();
		}
	
		/*
		if( isset( $_POST['ksf_import_ofx'] ) )
		{
			$this->model->insert_data( $_POST );
		}
		else if( $this->delete >= 0 )
		{
			$this->handle_delete();
		}
		else if( $this->edit >= 0 )
		{
			$this->handle_edit();
		}
 		*/

		parent::run();
	}
	function action_show_form()
	{
		$this->install();
		parent::action_show_form();
	}
	function install()
	{
		$this->model->create_table();
		$this->model->install();
		//parent::install();	//_model calls parent::install as well so we shouldn't need to.
	}
	/*********************************************************************************//**
	 *master_form
	 *	Display the summary of items with edit/delete
	 *		
	 *	assumes entry_array has been built (constructor)
	 *	assumes table_details has been built (constructor)
	 *	assumes selected_id has been set (constructor?)
	 *	assumes iam has been set (constructor)
	 *
	 * ***********************************************************************************/
	function master_form()
	{
		$this->view->master_form();
	}

	
}
