<?php
/**********************************************************************
   Print an Invoice and any related receipts for payments. 
***********************************************************************/
$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ?
	'SA_SALESTRANSVIEW' : 'SA_SALESBULKREP';
// ----------------------------------------------------------------
// $ Revision:	1.0 $
// Creator:	Kevin Fraser
// date_:	2018-11-28
// Title:	Print Invoices (107) and related receipts (112)
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/current_user.inc");		//107&112
include_once($path_to_root . "/includes/session.inc");		//107&112
include_once($path_to_root . "/includes/date_functions.inc");	//107&112
include_once($path_to_root . "/includes/data_checks.inc");	//107&112
include_once($path_to_root . "/sales/includes/sales_db.inc");	//107
include_once($path_to_root . "/reporting/includes/pdf_report.inc");	//107&112

//----------------------------------------------------------------------------------------------------
//print_invoices();						//107
//print_receipts();						//112
//----------------------------------------------------------------------------------------------------

echo "Lets do this";
$rep = new print_report();

class print_report
{
	protected $inv_trans_no;
	protected $recp_trans_no;
	protected $debtor_id;
	protected $inv_type;
	protected $recp_type;
	protected $from;
	protected $to;
	protected $currency;
	protected $email;
	protected $pay_service;
	protected $comments;
	protected $customer;
	protected $orientation; 
	protected $dec;
	protected $cols;
	protected $aligns;
	protected $params;
	protected $cur;		//default currency
	protected $report_object;
	protected $SubTotal;
	protected $footer_height;
	protected $invoice_number;
	protected $bank_account;
	protected $cust_trans;	//invoice cust details
	protected $reference;
	protected $branch_code;
	protected $looped;
	function construct()
	{
	 	$fno = explode("-", $_POST['PARAM_0']);				//107
		$tno = explode("-", $_POST['PARAM_1']);				//107
		$this->from = min($fno[0], $tno[0]);				//107
		$this->to = max($fno[0], $tno[0]);				//107
		if (!$this->from || !$this->to) return;				//107
		$this->currency = $_POST['PARAM_2'];				//107
		$this->email = $_POST['PARAM_3'];				//107
		$this->pay_service = $_POST['PARAM_4'];				//107
		$this->comments = $_POST['PARAM_5'];				//107
		$this->customer = $_POST['PARAM_6'];				//107
		$this->orientation = ($_POST['PARAM_7'] ? 'L' : 'P');		//107
		$this->cols = array(4, 60, 225, 300, 325, 385, 450, 515);	//Invoice  (107)
		//$cols = array(4, 85, 150, 225, 275, 360, 450, 515);		//RECEIPT
		if ($this->orientation == 'L')
			recalculate_cols($this->cols);
		// $headers in doctext.inc
		$this->aligns = array('left', 'left', 'right', 'left', 'right', 'right', 'right');	//107
		$this->params = array('comments' => $this->comments);		//107
		$this->cur = get_company_Pref('curr_default');			//107
		$this->dec = user_price_dec();					//107
		$this->looped = false;

		//To show the allocations in sales/view/view_invoice.php
		//	display_allocations_to(PT_CUSTOMER, $myrow['debtor_no'], ST_SALESINVOICE, $trans_id, $myrow['Total']);
		//	includes/ui/ui_view.inc
		//	display_allocations_from($person_type, $person_id, $type, $type_no, $total)
		//		$alloc_result = get_allocatable_to_cust_transactions($person_id, $type_no, $type);
		//			sales/includes/db/custalloc_db.inc
		//			(db_res) get_allocatable_to_cust_transactions($customer_id, $trans_no=null, $type=null)
		//				get_alloc_trans_sql(...)
		//				(sql) get_alloc_trans_sql($extra_fields=null, $extra_conditions=null, $extra_tables=null)
		//		display_allocations($alloc_result, $total);
		for ($inv_num = $this->from; $inv_num <= $this->to; $inv_num++)
		{
			$this->invoice_number = $inv_num;
			try {
				$b_res = $this->process_one_invoice();		
			}
			catch( Exception $e )
			{
				notify( $e->msg );
				echo( $e->msg );
			}
		}			
		$this->report_object->End();
	}
	/********************************************//**
	* Determine which receipts apply to the invoice
	*
	* INTERNAL 
	* CALLS 
	*************************************************/
	function get_receipt_nums_for_invoice()
	{
	}
	/********************************************//**
	* XXXXXX 
	*
	* INTERNAL 
	* CALLS 
	*************************************************/
	function initialize_report_object()
	{
		if( ! isset( $this->aligns ) )
			throw new Exception( "Required var not set", KSF_FIELD_NOT_SET );
		if( ! isset( $this->cols ) )
			throw new Exception( "Required var not set", KSF_FIELD_NOT_SET );
		if( ! isset( $this->params ) )
			throw new Exception( "Required var not set", KSF_FIELD_NOT_SET );
		if( ! isset( $this->cur ) )
			throw new Exception( "Required var not set", KSF_FIELD_NOT_SET );
		if( ! isset( $this->reference ) )
			throw new Exception( "Required var not set", KSF_FIELD_NOT_SET );
		if( ! isset( $this->email ) )
			throw new Exception( "Required var not set", KSF_FIELD_NOT_SET );
		$this->report_object = new FrontReport("", "", user_pagesize(), 9, $this->orientation);
		if ($this->email == 1)
		{
			$this->report_object->filename = "Invoice" . $this->reference . ".pdf";
		}
		else
		{	
			$this->report_object->filename = null;
		}
		$this->report_object->SetHeaderType('Header2');
		$this->report_object->currency = $this->cur;
		$this->report_object->Font();
		$this->report_object->Info( $this->params, $this->cols, null, $this->aligns);
/*===========================================================================
WHERE IS THESE SET?
	Get Customer Transaction...INVOICE
	called before this routine
		$this->debtor_id = $this->cust_trans['debtor_no'];
		if($this->customer && $this->debtor_id != $this->customer) 
			return FALSE;
		$this->reference = $this->cust_trans['reference'];

$sales_order = get_sales_order_header($this->cust_trans["order_"], ST_SALESORDER);
		$contacts = get_branch_contacts($this->branch['branch_code'], 'invoice', $this->branch['debtor_no'], true);
		
=============================================================================*/
		$this->report_object->SetCommonData($this->cust_trans, $this->branch, $sales_order, $this->bank_account, ST_SALESINVOICE, $contacts);
		$this->footer_height = $this->report_object->bottomMargin + (15 * $this->report_object->lineHeight);
	}
	/********************************************//**
	* Sets the page type order for the report.  Calls fcns that generate pages.
	*
	*	If we are sending to someone, we want invoice and then receipts.
	*	If we are printing, I want receipts then invoice since the order 
	*	of pages coming out on the printer this will set it up to be stapled 
	*	without fussed with.
	* INTERNAL invoice_number
	* INTERNAL looped        
	* INTERNAL report_object
	* CALLS exists_customer_trans()
	* CALLS ->get_customer_transaction()
	* CALLS ->initialize_report_object()
	* CALLS ->print_invoices();
	* CALLS ->print_receipts();
	*************************************************/
	/*bool*/function process_one_invoice()
	{
		if( ! isset( $this->invoice_number ) )
			throw new Exception( "Required var not set", KSF_FIELD_NOT_SET );
		if (!exists_customer_trans(ST_SALESINVOICE, $this->invoice_number))
			throw new Exception( "Indicated Invoice Number invalid", KSF_INVALID_DATA_VALUE );
		$b_cust_trans = $this->get_customer_transaction();
		if( ! $b_cust_trans )
			return FALSE;
		//If we are sending to someone, we want invoice and then receipts.
		//If we are printing, I want receipts then invoice since the order 
		//of pages coming out on the printer this will set it up to be stapled 
		//without fussed with.
		if( !$this->looped )
			$this->initialize_report_object();
		else
		{
			//has looped
			if ($this->email == TRUE)
			{
				//We ended the object on last loop
				$this->initialize_report_object();
			}
		}
		$this->get_receipt_nums_for_invoice();
		if ($this->email == TRUE)
		{
			$this->report_object->title = _('INVOICE');
			$this->report_object->NewPage();
			$this->print_invoices();
/*
			$this->report_object->title = _('RECEIPT');
			$this->report_object->NewPage();
			$this->print_receipts();
 */
		}
		else
		{
			/*
			$this->report_object->title = _('RECEIPT');
			$this->report_object->NewPage();
			$this->print_receipts();
			*/
			$this->report_object->title = _('INVOICE');
			$this->report_object->NewPage();
			$this->print_invoices();
		}
		$this->looped = true;
		if ($this->email == TRUE )
			$this->report_object->End();
		return TRUE;
	}
	/********************************************//**
	* Print a line of Invoice detail                                           
	*
	* If the page has too many lines a new
	* page will be started.
	*
	* INTERNAL ->dec
	* INTERNAL ->SubTotal
	* INTERNAL ->report_object
	* INTERNAL ->footer_height
	* CALLS round()
	* CALLS get_qty_dec
	* CALLS is_service
	* @param array array of line item detail.
	*	stock_id
	* 	discount_percent
	*	unit_price
	*	quantity
	*	StockDescription
	*	mb_flag
	*************************************************/
	function Print_inv_item_row( $det_array )
	{
		global $no_zero_lines_amount;

		$Net = round2( (1 - $det_array["discount_percent"]) * $det_array["unit_price"] * $det_array["quantity"], $this->dec);
		$this->SubTotal += $Net;
		$DisplayPrice = number_format2($det_array["unit_price"],$this->dec);
		$DisplayQty = number_format2($det_array["quantity"],get_qty_dec($det_array['stock_id']));
		$DisplayNet = number_format2($Net,$this->dec);
		if ($det_array["discount_percent"]==0)
		 	$DisplayDiscount ="";
		else
		 	$DisplayDiscount = number_format2($det_array["discount_percent"]*100,user_percent_dec()) . "%";
		$this->report_object->TextCol(0, 1,	$det_array['stock_id'], -2);

		$oldrow = $this->report_object->row;
		$this->report_object->TextColLines(1, 2, $det_array['StockDescription'], -2);	//let print more than 1 line (row) ?
		$newrow = $this->report_object->row;	//Post Stock Description
		$this->report_object->row = $oldrow;	//Reset which row to write upon?
		if ($Net != 0.0 || !is_service($det_array['mb_flag']) || !isset($no_zero_lines_amount) || $no_zero_lines_amount == 0)
		{
			$this->report_object->TextCol(2, 3,	$DisplayQty, -2);
			$this->report_object->TextCol(3, 4,	$det_array['units'], -2);
			$this->report_object->TextCol(4, 5,	$DisplayPrice, -2);
			$this->report_object->TextCol(5, 6,	$DisplayDiscount, -2);
			$this->report_object->TextCol(6, 7,	$DisplayNet, -2);
		}	
		$this->report_object->row = $newrow;
		//$this->report_object->NewLine(1);
		if ($this->report_object->row < $this->footer_height )
			$this->report_object->NewPage();
	}
	/**************************************************************//**
	* Get the customer transaction
	*
	* CALLS get_customer_trans
	* INTERNAL ->invoice_number
	* INTERNAL ->cust_trans
	* INTERNAL ->debtor_id
	* INTERNAL ->customer
	* INTERNAL ->reference
	* INTERNAL ->reference
	* INTERNAL ->bank_account
	* INTERNAL ->branch
	*
	******************************************************************/
	function get_customer_transaction()
	{
		$this->cust_trans = get_customer_trans($this->invoice_number, ST_SALESINVOICE);
		$this->debtor_id = $this->cust_trans['debtor_no'];
		if($this->customer && $this->debtor_id != $this->customer) 
			return FALSE;
		$this->reference = $this->cust_trans['reference'];
		$this->bank_account = get_default_bank_account($this->cust_trans['curr_code']);
		$this->branch = get_branch($this->cust_trans["branch_code"]);

	}
	/**************************************************************//**
	* Print invoices              
	*
	* CALLS get_sales_order_header
	* CALLS get_branch_contacts
	* CALLS get_customer_trans_details
	* CALLS ->Print_inv_item_row()
	* CALLS get_comments_string
	* CALLS number_format2()
	* CALLS ->print_invoice_summary()
	* INTERNAL ->report_object
	* INTERNAL ->params
	* INTERAL ->cust_trans
	* INTERNAL ->branch
	* INTERNAL ->invoice_number
	* INTERNAL ->footer_height
	*
	******************************************************************/
	function print_invoices()
	{
		$this->report_object->title = _('INVOICE');
		global $alternative_tax_include_on_docs, $suppress_tax_rates, $no_zero_lines_amount;
		$this->params['bankaccount'] = $this->bank_account['id'];

		$sales_order = get_sales_order_header($this->cust_trans["order_"], ST_SALESORDER);
		$contacts = get_branch_contacts($this->branch['branch_code'], 'invoice', $this->branch['debtor_no'], true);
		$baccount['payment_service'] = $this->pay_service;


   		$result = get_customer_trans_details(ST_SALESINVOICE, $this->invoice_number);
		$this->SubTotal = 0;
		while ($cust_trans2=db_fetch($result))
		{
			if ($cust_trans2["quantity"] == 0)
				continue;
			$this->Print_inv_item_row();	//Will add pages with header as needed.
		}

		$memo = get_comments_string(ST_SALESINVOICE, $this->invoice_number);
		if ($memo != "")
		{
			$this->report_object->NewLine();
			$this->report_object->TextColLines(1, 5, $memo, -2);
		}

   		$this->DisplaySubTot = number_format2($this->SubTotal,$dec);
   		$this->DisplayFreight = number_format2($cust_trans["ov_freight"],$dec);

    		$this->report_object->row = $this->footer_height;
		$this->print_invoice_summary();
		if ($this->email == 1)
		{
			$this->report_object->End();	//This would truncate the report to 1 invoice
			//$this->report_object->End($this->email);
		}
		else
		{
			$this->report_object->End();	//This would truncate the report to 1 invoice
		}
	}
	/**************************************************************//**
	* Print invoice summary lines              
	*
	* CALLS get_trans_tax_details
	* CALLS number_format2
	* CALLS price_in_words
	* INTERNAL ->report_object
	* INTERNAL ->DisplaySubTot
	* INTERNAL ->DisplayFreight
	* INTERNAL ->invoicenumber 
	* INTERNAL ->dec
	* INTERNAL ->cust_trans
	*
	******************************************************************/
	function print_invoice_summary()
	{
		$this->report_object->TextCol(3, 6, _("Sub-total"), -2);
		$this->report_object->TextCol(6, 7,	$this->DisplaySubTot, -2);
		$this->report_object->NewLine();
		$this->report_object->TextCol(3, 6, _("Shipping"), -2);
		$this->report_object->TextCol(6, 7,	$this->DisplayFreight, -2);
		$this->report_object->NewLine();
		//TAX Lines
		$tax_items = get_trans_tax_details(ST_SALESINVOICE, $this->invoice_number);
	    	while ($tax_item = db_fetch($tax_items))
	    	{
	    		if ($tax_item['amount'] == 0)
	    			continue;
	    		$DisplayTax = number_format2($tax_item['amount'], $this->dec);
	    		$tax_type_name = $tax_item['tax_type_name']." (".$tax_item['rate']."%) ";
			$this->report_object->TextCol(3, 6, $tax_type_name, -2);
			$this->report_object->TextCol(6, 7,	$DisplayTax, -2);
			$this->report_object->NewLine();
	    	}
    		$this->report_object->NewLine();
		$DisplayTotal = number_format2(($this->cust_trans["ov_freight"] + $this->cust_trans["ov_gst"] + $this->cust_trans["ov_amount"]+$this->cust_trans["ov_freight_tax"]),$this->dec);
		$this->report_object->Font('bold');
		$this->report_object->TextCol(3, 6, _("TOTAL INVOICE"), - 2);
		$this->report_object->TextCol(6, 7, $DisplayTotal, -2);
		$words = price_in_words($cust_trans['Total'], ST_SALESINVOICE);
		if ($words != "")
		{
			$this->report_object->NewLine(1);
			$this->report_object->TextCol(1, 7, $this->report_object->currency . ": " . $words, - 2);
		}
		$this->report_object->Font();
	}
	/**************************************************************//**
	* Get receipt details                      
	*
	* TODO: Move this routine into ksf_modules classes.
	*
	* CALLS db_query
	* CALLS db_num_rows
	* INTERNAL ->recp_type
	* INTERNAL ->recp_trans_no
	* @returns db_result
	******************************************************************/
	function get_receipt()
	{
		if( ! isset( $this->recp_type ) )
			throw new Exception ("Required variable not set", KSF_FIELD_NOT_SET );
		if( ! isset( $this->recp_trans_no ) )
			throw new Exception ("Required variable not set", KSF_FIELD_NOT_SET );
		$sql = 	"SELECT ".TB_PREF."debtor_trans.*,
				(".TB_PREF."debtor_trans.ov_amount + ".TB_PREF."debtor_trans.ov_gst + ".TB_PREF."debtor_trans.ov_freight + 
				".TB_PREF."debtor_trans.ov_freight_tax) AS Total,
				".TB_PREF."debtor_trans.ov_discount,
	   			".TB_PREF."debtors_master.name AS DebtorName,  ".TB_PREF."debtors_master.debtor_ref,
	   			".TB_PREF."debtors_master.curr_code, ".TB_PREF."debtors_master.payment_terms, "
	   			.TB_PREF."debtors_master.tax_id AS tax_id,
	   			".TB_PREF."debtors_master.address
	    		FROM ".TB_PREF."debtor_trans, ".TB_PREF."debtors_master
				WHERE ".TB_PREF."debtor_trans.debtor_no = ".TB_PREF."debtors_master.debtor_no
				AND ".TB_PREF."debtor_trans.type = ".db_escape( $this->recp_type)."
				AND ".TB_PREF."debtor_trans.trans_no = ".db_escape( $this->recp_trans_no );
	   	$result = db_query($sql, "The remittance cannot be retrieved");
	   	if (db_num_rows($result) == 0)
	   		return false;
	   	return db_fetch($result);
	}
	/**************************************************************//**
	* Print invoice summary lines              
	*
	* TODO: Move into ksf_modules classes
	*
	* CALLS get_alloc_trans_sql
	* CALLS db_query
	* INTERNAL
	* @returns db_result
	******************************************************************/
	function get_allocations_for_receipt($debtor_id, $type, $trans_no)
	{
		$sql = get_alloc_trans_sql("amt, trans.reference, trans.alloc", "trans.trans_no = alloc.trans_no_to
			AND trans.type = alloc.trans_type_to
			AND alloc.trans_no_from=$this->recp_trans_no
			AND alloc.trans_type_from=$this->recp_type
			AND trans.debtor_no=".db_escape($debtor_id),
			TB_PREF."cust_allocations as alloc");
		$sql .= " ORDER BY trans_no";
		return db_query($sql, "Cannot retreive alloc to transactions");
	}
	/**************************************************************//**
	* Print receipt item lines              
	*
	* CALLS 
	* INTERNAL
	* @param array detail_array
	*
	******************************************************************/
	function print_recp_item_row($detail_array)
	{
		$this->report_object->TextCol(0, 1,	$systypes_array[$detail_array['type']], -2);
		$this->report_object->TextCol(1, 2,	$detail_array['reference'], -2);
		$this->report_object->TextCol(2, 3,	sql2date($detail_array['tran_date']), -2);
		$this->report_object->TextCol(3, 4,	sql2date($detail_array['due_date']), -2);
		$this->report_object->AmountCol(4, 5, 	$detail_array['Total'], $this->dec, -2);
		$this->report_object->AmountCol(5, 6, 	$detail_array['Total'] - $detail_array['alloc'], $this->dec, -2);
		$this->report_object->AmountCol(6, 7, 	$detail_array['amt'], $this->dec, -2);
		$this->total_allocated += $detail_array['amt'];
		$this->report_object->NewLine(1);
		if ($this->report_object->row < $this->report_object->bottomMargin + (15 * $this->report_object->lineHeight))
			$this->report_object->NewPage();
	
	}
	/**************************************************************//**
	* Print receipt  
	*
	* CALLS ->get_receipt()
	* CALLS get_bank_trans()
	* CALLS get_branch_contacts
	* CALLS get_allocations_for_receipt()
	* CALLS ->print_recp_item_row(
	* CALLS get_comments_string()
	* CALLS ->receipt_summary()
	* INTERNAL ->report_object
	* INTERNAL ->debtor_id    
	* INTERNAL ->branch_code  
	* INTERNAL ->rTotal       
	* INTERNAL ->rDiscount  
	* INTERNAL ->recp_type  
	* INTERNAL ->recp_trans_no
	* INTERNAL ->params        
	* INTERNAL ->total_allocated
	*
	******************************************************************/
	function print_receipt()
	{
		if( ! isset( $this->recp_type ) )
			throw new Exception ("Required variable not set", KSF_FIELD_NOT_SET );
		if( ! isset( $this->recp_trans_no ) )
			throw new Exception ("Required variable not set", KSF_FIELD_NOT_SET );

		$this->report_object->title = _('RECEIPT');
		//need to set $this->recp_trans_no, $this->recp_type 
		try {
			$myrow = $this->get_receipt();
			if (!$myrow)
				return;			
			$this->debtor_id = $myrow['debtor_no'];
			$this->branch_code = $myrow['branch_code'];
			$this->rTotal = $myrow['Total'];
			$this->rDiscount = $myrow['ov_discount']; 
	
			$res = get_bank_trans($this->recp_type, $this->recp_trans_no);
			$baccount = db_fetch($res);
			$this->params['bankaccount'] = $baccount['bank_act'];
			$contacts = get_branch_contacts( $this->branch_code, 'invoice', $this->debtor_id);
/********************************************************************************
====================================================================
********************************************************************************/
			$this->report_object->SetCommonData($myrow, null, $myrow, $baccount, ST_CUSTPAYMENT, $contacts);
			$this->report_object->NewPage();
			$result = get_allocations_for_receipt( $this->debtor_id, $myrow['type'], $myrow['trans_no']);
			$this->total_allocated = 0;
			$this->report_object->TextCol(0, 4,	_("As advance / full / part / payment towards:"), -2);
			$this->report_object->NewLine(2);
			
			while ($myrow2=db_fetch($result))
			{
				$this->print_recp_item_row( $myrow2 );
			}
			$memo = get_comments_string( $this->recp_type, $this->recp_trans_no);
			if ($memo != "")
			{
				$this->report_object->NewLine();
				$this->report_object->TextColLines(1, 5, $memo, -2);
			}
			$this->receipt_summary();
		}
		catch( Exception $e )
		{
				var_dump( $e );
				notify( $e->msg );
				echo( $e->msg );
		}
	}
	/**************************************************************//**
	* Print invoice summary lines              
	*
	* CALLS floatcmp
	* INTERNAL ->report_object
	* INTERNAL ->rTotal   
	* INTERNAL ->total_allocated   
	* INTERNAL ->rDiscount
	* INTERNAL ->dec 
	*
	******************************************************************/
	function receipt_summary()
	{
		$this->report_object->row = $this->report_object->bottomMargin + (16 * $this->report_object->lineHeight);
		$this->report_object->TextCol(3, 6, _("Total Allocated"), -2);
		$this->report_object->AmountCol(6, 7, $this->total_allocated, $this->dec, -2);
		$this->report_object->NewLine();
		$this->report_object->TextCol(3, 6, _("Left to Allocate"), -2);
		$this->report_object->AmountCol(6, 7, $this->rTotal + $this->rDiscount - $this->total_allocated, $this->dec, -2);
		if (floatcmp($this->rDiscount, 0))
		{
			$this->report_object->NewLine();
			$this->report_object->TextCol(3, 6, _("Discount"), - 2);
			$this->report_object->AmountCol(6, 7, -$this->rDiscount, $this->dec, -2);
		}	
		$this->report_object->NewLine();
		$this->report_object->Font('bold');
		$this->report_object->TextCol(3, 6, _("TOTAL RECEIPT"), - 2);
		$this->report_object->AmountCol(6, 7, $this->rTotal, $this->dec, -2);
		/*
			$words = price_in_words($myrow['Total'], ST_CUSTPAYMENT);
		if ($words != "")
		{
			$this->report_object->NewLine(1);
			$this->report_object->TextCol(0, 7, $myrow['curr_code'] . ": " . $words, - 2);
		}
		*/	
		$this->report_object->Font();
		//$this->receipt_summary_footer();	//Details I don't use

	}
	/**************************************************************//**
	* Print invoice summary lines              
	*
	* CALLS 
	* INTERNAL ->report_object
	*
	******************************************************************/
	function receipt_summary_footer()
	{
		$this->report_object->Font();
		$this->report_object->NewLine();
		$this->report_object->TextCol(6, 7, _("Received / Sign"), - 2);
		$this->report_object->NewLine();
		$this->report_object->TextCol(0, 2, _("By Cash / Cheque* / Draft No."), - 2);
		$this->report_object->TextCol(2, 4, "______________________________", - 2);
		$this->report_object->TextCol(4, 5, _("Dated"), - 2);
		$this->report_object->TextCol(5, 6, "__________________", - 2);
		$this->report_object->NewLine(1);
		$this->report_object->TextCol(0, 2, _("Drawn on Bank"), - 2);
		$this->report_object->TextCol(2, 4, "______________________________", - 2);
		$this->report_object->TextCol(4, 5, _("Branch"), - 2);
		$this->report_object->TextCol(5, 6, "__________________", - 2);
		$this->report_object->TextCol(6, 7, "__________________");
	}

}	//class




function print_receipts()
{
	global $systypes_array;

	 $this->initialize_report_object();

	for ($i = $from; $i <= $to; $i++)
	{
		$this->recp_trans_no = $i;
		if ($fno[0] == $tno[0])
			$types = array($fno[1]);
		else
			$types = array(ST_BANKDEPOSIT, ST_CUSTPAYMENT);
		foreach ($types as $j)
		{
			$this->recp_type = $j;
			$myrow = $this->get_receipt();
			if (!$myrow)
				continue;			
			$res = get_bank_trans($this->recp_type, $this->recp_trans_no);
			$baccount = db_fetch($res);
			$params['bankaccount'] = $baccount['bank_act'];

			$contacts = get_branch_contacts($myrow['branch_code'], 'invoice', $myrow['debtor_no']);
			$rep->SetCommonData($myrow, null, $myrow, $baccount, ST_CUSTPAYMENT, $contacts);
			$rep->NewPage();
			$result = get_allocations_for_receipt($myrow['debtor_no'], $myrow['type'], $myrow['trans_no']);


			$total_allocated = 0;
			$rep->TextCol(0, 4,	_("As advance / full / part / payment towards:"), -2);
			$rep->NewLine(2);
			
			while ($myrow2=db_fetch($result))
			{
				$rep->TextCol(0, 1,	$systypes_array[$myrow2['type']], -2);
				$rep->TextCol(1, 2,	$myrow2['reference'], -2);
				$rep->TextCol(2, 3,	sql2date($myrow2['tran_date']), -2);
				$rep->TextCol(3, 4,	sql2date($myrow2['due_date']), -2);
				$rep->AmountCol(4, 5, $myrow2['Total'], $dec, -2);
				$rep->AmountCol(5, 6, $myrow2['Total'] - $myrow2['alloc'], $dec, -2);
				$rep->AmountCol(6, 7, $myrow2['amt'], $dec, -2);

				$total_allocated += $myrow2['amt'];
				$rep->NewLine(1);
				if ($rep->row < $rep->bottomMargin + (15 * $rep->lineHeight))
					$rep->NewPage();
			}

			$memo = get_comments_string($this->recp_type, $this->recp_trans_no);
			if ($memo != "")
			{
				$rep->NewLine();
				$rep->TextColLines(1, 5, $memo, -2);
			}

			$rep->row = $rep->bottomMargin + (16 * $rep->lineHeight);

			$rep->TextCol(3, 6, _("Total Allocated"), -2);
			$rep->AmountCol(6, 7, $total_allocated, $dec, -2);
			$rep->NewLine();
			$rep->TextCol(3, 6, _("Left to Allocate"), -2);
			$rep->AmountCol(6, 7, $myrow['Total'] + $myrow['ov_discount'] - $total_allocated, $dec, -2);
			if (floatcmp($myrow['ov_discount'], 0))
			{
				$rep->NewLine();
				$rep->TextCol(3, 6, _("Discount"), - 2);
				$rep->AmountCol(6, 7, -$myrow['ov_discount'], $dec, -2);
			}	
			$rep->NewLine();
			$rep->Font('bold');
			$rep->TextCol(3, 6, _("TOTAL RECEIPT"), - 2);
			$rep->AmountCol(6, 7, $myrow['Total'], $dec, -2);

			$words = price_in_words($myrow['Total'], ST_CUSTPAYMENT);
			if ($words != "")
			{
				$rep->NewLine(1);
				$rep->TextCol(0, 7, $myrow['curr_code'] . ": " . $words, - 2);
			}	
			$rep->Font();
			$rep->NewLine();
			$rep->TextCol(6, 7, _("Received / Sign"), - 2);
			$rep->NewLine();
			$rep->TextCol(0, 2, _("By Cash / Cheque* / Draft No."), - 2);
			$rep->TextCol(2, 4, "______________________________", - 2);
			$rep->TextCol(4, 5, _("Dated"), - 2);
			$rep->TextCol(5, 6, "__________________", - 2);
			$rep->NewLine(1);
			$rep->TextCol(0, 2, _("Drawn on Bank"), - 2);
			$rep->TextCol(2, 4, "______________________________", - 2);
			$rep->TextCol(4, 5, _("Branch"), - 2);
			$rep->TextCol(5, 6, "__________________", - 2);
			$rep->TextCol(6, 7, "__________________");
		}	
	}
	$rep->End();
}

?>
