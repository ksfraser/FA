<?php

require_once( $path_to_root . "/reporting/includes/pdf_report.inc" );

class ksfFrontReport extends FrontReport
{
	function __construct( $title, $filename, $size = 'LETTER', $fontsize = 9, $orientation = 'P', $margins = NULL, $excelColWidthFactor = NULL)
        {
                global $page_security;

                $this->rep_id = $_POST['REP_ID'];       // FIXME

                if (!$_SESSION["wa_current_user"]->can_access_page($page_security))
                {
                        display_error(_("The security settings on your account do not permit you to print this report"));
                        end_page();
                        exit;
                }
                // Page margins - if user-specified, use those.  Otherwise, use defaults below.
                if (isset($margins))
                {
                        $this->topMargin = $margins['top'];
                        $this->bottomMargin = $margins['bottom'];
                        $this->leftMargin = $margins['left'];
                        $this->rightMargin = $margins['right'];
                }
                // Page orientation - P: portrait, L: landscape
                $orientation = strtoupper($orientation);
                // Page size name
                switch (strtoupper($size))
                {
                        default:
                  case 'A4':
                          // Portrait
                          if ($orientation == 'P')
                          {
                                  $this->pageWidth=595;
                                  $this->pageHeight=842;
                                  if (!isset($margins))
                                  {
                                          $this->topMargin=40;
                                          $this->bottomMargin=30;
                                          $this->leftMargin=40;
                                          $this->rightMargin=30;
                                  }
                          }
                          // Landscape
                          else
                          {
                                  $this->pageWidth=842;
                                  $this->pageHeight=595;
                                  if (!isset($margins))
                                  {
                                          $this->topMargin=30;
                                          $this->bottomMargin=30;
                                          $this->leftMargin=40;
                                          $this->rightMargin=30;
                                  }
                          }
                          break;
                   case 'A3':
                          // Portrait
                          if ($orientation == 'P')
                          {
                                  $this->pageWidth=842;
                                  $this->pageHeight=1190;
                                  if (!isset($margins))
                                  {
                                          $this->topMargin=50;
                                          $this->bottomMargin=50;
                                          $this->leftMargin=50;
                                          $this->rightMargin=40;
                                  }
                          }
                          // Landscape
                          else
                          {
                                  $this->pageWidth=1190;
                                  $this->pageHeight=842;
                                  if (!isset($margins))
                                  {
                                          $this->topMargin=50;
                                          $this->bottomMargin=50;
                                          $this->leftMargin=50;
                                          $this->rightMargin=40;
                                  }
                          }
                          break;
                   case 'LETTER':
                          // Portrait
                          if ($orientation == 'P')
                          {
                                  $this->pageWidth=612;
                                  $this->pageHeight=792;
                                  if (!isset($margins))
                                  {
                                          $this->topMargin=30;
                                          $this->bottomMargin=30;
                                          $this->leftMargin=30;
                                          $this->rightMargin=25;
                                  }
                          }
                          // Landscape
                          else
                          {
                                  $this->pageWidth=792;
                                  $this->pageHeight=612;
                                  if (!isset($margins))
                                  {
                                          $this->topMargin=30;
                                          $this->bottomMargin=30;
                                          $this->leftMargin=30;
                                          $this->rightMargin=25;
                                  }
                          }
                          break;
                   case 'LEGAL':
                          // Portrait
                          if ($orientation == 'P')
                          {
                                  $this->pageWidth=612;
                                  $this->pageHeight=1008;
                                  if (!isset($margins))
                                  {
                                          $this->topMargin=50;
                                          $this->bottomMargin=40;
                                          $this->leftMargin=30;
                                          $this->rightMargin=25;
                                  }
                          }
                          // Landscape
                          else
                          {
                                  $this->pageWidth=1008;
                                  $this->pageHeight=612;
                                  if (!isset($margins))
                                  {
                                          $this->topMargin=50;
                                          $this->bottomMargin=40;
                                          $this->leftMargin=30;
                                          $this->rightMargin=25;
                                  }
                          }
                          break;
                }
                $this->size = array(0, 0, $this->pageWidth, $this->pageHeight);
                $this->title = $title;
                $this->filename = $filename.".pdf";
                $this->pageNumber = 0;
                $this->endLine = $this->pageWidth - $this->rightMargin;
                $this->lineHeight = 12;
                $this->fontSize = $fontsize;
                $this->oldFontSize = 0;
                $this->row = $this->pageHeight - $this->topMargin;
                $this->currency = '';
                $this->scaleLogoWidth = false; // if Logo, scale on width (else height).
                $this->SetHeaderType('Header'); // default

                $this->Cpdf($size, $_SESSION['language']->code, $orientation);
        }
//FrontReport does this redirect for backwards compatibility
        function Font($style = '', $fontname = '')
        {
                 $this->selectFont($fontname, $style);
        }
        function Info($params, $cols, $headers, $aligns, $cols2 = null, $headers2 = null, $aligns2 = null, $companylogoenable = false, $footerenable = false, $footertext = '')
	{
		parent::Info($params, $cols, $headers, $aligns, $cols2 = null, $headers2 = null, $aligns2 = null, $companylogoenable = false, $footerenable = false, $footertext = '');
	}
//	function Header(){}	//Inherited
//	function SetCommonData($myrow, $branch, $sales_order, $bankaccount, $doctype, $contacts){}	//Inherited
//      function SetHeaderType($name) {}
//	function Header2(){}
//      function Header3(){}
//	function DatePrettyPrint($date, $input_format = 0, $output_format = 0){}
// 	function AddImage($logo, $x, $y, $w, $h){}
//	function GetDrawColor(){}
//	function GetCellPadding(){}
//	function SetCellPadding($pad){}
//	function Text($c, $txt, $n=0, $corr=0, $r=0, $align='left', $border=0, $fill=0, $link=NULL, $stretch=1){}
//	...
//
//		NewPage looks for a template for the header under reporting/forms
//	function NewPage(){}


        function Header4()
        {
                global $path_to_root, $print_as_quote,
                        $print_invoice_no, $packing_slip, $dflt_lang; // FIXME should be passed as params

                $doctype = $this->formData['doctype'];
                $header2type = true;

                $this->SetLang(@$this->formData['rep_lang'] ? $this->formData['rep_lang']
                        : ($_SESSION["wa_current_user"]->prefs->language ? $_SESSION["wa_current_user"]->prefs->language : $dflt_lang));

                 // leave layout files names without path to enable including
                 // modified versions from company/x/reporting directory

/*****
*
*       Set document type dependent elements of common page layout.
*
*/
/*
        $Addr1 = array(
                        'title' => _("Charge To"),
                        'name' => @$this->formData['br_name'] ? $this->formData['br_name'] : @$this->formData['DebtorName'],
                        'address' => @$this->formData['br_address'] ? $this->formData['br_address'] : @$this->formData['address']
        );
*/
        $Addr2 = array(
                        'title' => _("Deliver To"),
                        'name' => @$this->formData['deliver_to'],
                        'address' => @$this->formData['delivery_address']
        );

	$this->title = "Address Label";
	//$this->title = ($packing_slip==1 ? _("PACKING SLIP") : _("DELIVERY NOTE"));
	$this->formData['document_name'] = _("Address Label");
	//$this->formData['document_name'] = _("Delivery Note No.");
	$Payment_Terms = '';
	$ref = get_reference(ST_SALESORDER, $this->formData['order_']);
	if (!$ref)
		$ref = $this->formData['order_'];
/*
	$aux_info = array(
		_("Customer's Reference") => $this->formData["customer_ref"],
		_("Sales Person") => get_salesman_name($this->formData['salesman']),
		_("Your VAT no.") => $this->formData['tax_id'],
		_("Our Order No") => $ref,
		_("To Be Invoiced Before") => sql2date($this->formData['due_date']),
	);
*/

        // default values
        if (!isset($this->formData['document_date']))
                $this->formData['document_date'] = $this->formData['tran_date'];

        if (!isset($this->formData['document_number']))
                $this->formData['document_number'] = $print_invoice_no == 0 && isset($this->formData['reference'])
                        ? $this->formData['reference'] : @$this->formData['trans_no'];

        if ($this->params['comments'] != '')
                $Footer[] = $this->params['comments'];

        $this->formData['recipient_name'] = $Addr2['name'];

/**
        Document blueprint use following parameters set in doctext.inc:

        $Addr1, $Addr2 - address info
        $Payment_Terms - payment terms line
        $Footer - footer texts
        $this->company - company info
        $this->title - report title
        $this->formData - some other info
***/

                $this->row = $this->pageHeight - $this->topMargin;

                $upper = $this->row - 2 * $this->lineHeight;
                $lower = $this->bottomMargin + 8 * $this->lineHeight;
                $iline1 = $upper - 7.5 * $this->lineHeight;
                $iline2 = $iline1 - 8 * $this->lineHeight;
                $iline3 = $iline2 - 1.5 * $this->lineHeight;
                $iline4 = $iline3 - 1.5 * $this->lineHeight;
                $iline5 = $iline4 - 3 * $this->lineHeight;
                $iline6 = $iline5 - 1.5 * $this->lineHeight;
                $iline7 = $lower;
                $right = $this->pageWidth - $this->rightMargin;
                $width = ($right - $this->leftMargin) / 5;
                $icol = $this->pageWidth / 2;
                $ccol = $this->cols[0] + 4;
                $c2col = $ccol + 60;
                $ccol2 = $icol / 2;
                //$mcol = $width / 2;
                $mcol = $icol + 8;
                $mcol2 = $this->pageWidth - $ccol2;
                $cols = count($this->cols);

                $this->SetDrawColor(205, 205, 205);
                //$this->Line($iline1, 3);
                $this->SetDrawColor(128, 128, 128);
                //$this->Line($iline1);
		//Add GREY background (->rectangle)
             //   $this->rectangle($this->leftMargin, $iline2, $right - $this->leftMargin, $iline2 - $iline3, "F", null, array(222, 231, 236));
               // $this->Line($iline2);
               // $this->Line($iline3);
               // $this->Line($iline4);
               // $this->rectangle($this->leftMargin, $iline5, $right - $this->leftMargin, $iline5 - $iline6, "F", null, array(222, 231, 236));
               // $this->Line($iline5);
               // $this->Line($iline6);
               // $this->Line($iline7);
               // $this->LineTo($this->leftMargin, $iline2 ,$this->leftMargin, $iline4);
                $col = $this->leftMargin;
                // Company Logo
                $this->NewLine();
                $logo = company_path() . "/images/" . $this->company['coy_logo'];
                if ($this->company['coy_logo'] != '' && file_exists($logo))
                {
                        $this->AddImage($logo, $ccol, $this->row, 0, 40);
                }
                else
                {
                        $this->fontSize += 4;
                        $this->Font('bold');
                        $this->Text($ccol, $this->company['coy_name'], $icol);
                        $this->Font();
                        $this->fontSize -= 4;
                }
/*
                // Document title
                $this->SetTextColor(190, 190, 190);
                $this->fontSize += 10;
                $this->Font('bold');
                $this->TextWrap($mcol, $this->row, $this->pageWidth - $this->rightMargin - $mcol - 20, $this->title, 'right');
                $this->Font();
                $this->fontSize -= 10;
*/
                $this->NewLine();
                $this->SetTextColor(0, 0, 0);
                $adrline = $this->row;

                // Company data
                $this->TextWrapLines($ccol, $icol, $this->company['postal_address']);
                $this->Font('italic');
                if (@$this->company['phone'])
                {
                        $this->Text($ccol, _("Phone"), $c2col);
                        $this->Text($c2col, $this->company['phone'], $mcol);
                        $this->NewLine();
                }
                if (@$this->company['fax'])
                {
                        $this->Text($ccol, _("Fax"), $c2col);
                        $this->Text($c2col, $this->company['fax'], $mcol);
                        $this->NewLine();
                }
                if (@$this->company['email'])
                {
                        $this->Text($ccol, _("Email"), $c2col);

                        $url = "mailto:" . $this->company['email'];
                        $this->SetTextColor(0, 0, 255);
                        $this->Text($c2col, $this->company['email'], $mcol);
                        $this->SetTextColor(0, 0, 0);
                        $this->addLink($url, $c2col, $this->row, $mcol, $this->row + $this->lineHeight);

                        $this->NewLine();
                }
/*
                if (@$this->formData['domicile'])
                {
                        $this->Text($ccol, _("Domicile"), $c2col);
                        $this->Text($c2col, $this->company['domicile'], $mcol);
                        $this->NewLine();
                }
*/
                $this->Font();
                $this->row = $adrline;
                $this->NewLine(3);
/*
                $this->Text($mcol + 100, _("Date"));
                $this->Text($mcol + 180, sql2date($this->formData['document_date']));
*/

                $this->NewLine();

                $this->row = $iline1 - $this->lineHeight;
                $this->fontSize -= 4;
                $this->Text($ccol, "", $icol);
                //$this->Text($ccol, $Addr1['title'], $icol);
                $this->Text($mcol, $Addr2['title']);
                $this->fontSize += 4;

// address1
                $temp = $this->row = $this->row - $this->lineHeight - 5;
                $this->Text($ccol, "", $icol);
                //$this->Text($ccol, $Addr1['name'], $icol);
                $this->NewLine();
                $this->TextWrapLines($ccol, $icol - $ccol, "");
                //$this->TextWrapLines($ccol, $icol - $ccol, $Addr1['address']);

// address2
                $this->row = $temp;
                $this->Text($mcol, $Addr2['name']);
                $this->NewLine();
                $this->TextWrapLines($mcol, $this->rightMargin - $mcol, $Addr2['address']);

                // Auxiliary document information
                $col = $this->leftMargin;

                // Line headers
                $this->row = $iline5 - $this->lineHeight - 1;
                $this->Font('bold');
                $count = count($this->headers);
                $this->cols[$count] = $right - 3;
                for ($i = 0; $i < $count; $i++)
                        $this->TextCol($i, $i + 1, $this->headers[$i], -2);
                $this->Font();

                // Footer
                $this->Font('italic');
                $this->row = $iline7 - $this->lineHeight - 6;

                $this->Font();
                $temp = $iline6 - $this->lineHeight - 2;

                $this->row = $temp;
        }


        function LetterHeader()
        {
                $companyCol = $this->endLine - 150;
                $titleCol = $this->leftMargin + 100;

                $this->row = $this->pageHeight - $this->topMargin;

                $this->SetDrawColor(128, 128, 128);
                $this->Line($this->row + 5, 1);

                $this->NewLine();

                $this->fontSize += 4;
                $this->Font('bold');
                $this->Text($this->leftMargin, $this->title, $companyCol);
                $this->Font();
                $this->fontSize -= 4;
                $this->Text($companyCol, $this->company['coy_name']);
                $this->row -= ($this->lineHeight + 4);
/*
                $str = _("Print Out Date") . ':';
                $this->Text($this->leftMargin, $str, $titleCol);
                $str = Today() . '   ' . Now();
                if ($this->company['time_zone'])
                        $str .= ' ' . date('O') . ' GMT';
*/
                $this->Text($titleCol, $str, $companyCol);
                $this->Text($companyCol, $this->host);

                $this->NewLine();
                $str = _("Fiscal Year") . ':';
                $this->Text($this->leftMargin, $str, $titleCol);
                $str = $this->fiscal_year;
                $this->Text($titleCol, $str, $companyCol);
                $this->Text($companyCol, $this->user);
                for ($i = 1; $i < count($this->params); $i++)
                {
                        if ($this->params[$i]['from'] != '')
                        {
                                $this->NewLine();
                                $str = $this->params[$i]['text'] . ':';
                                $this->Text($this->leftMargin, $str, $titleCol);
                                $str = $this->params[$i]['from'];
                                if ($this->params[$i]['to'] != '')
                                        $str .= " - " . $this->params[$i]['to'];
                                $this->Text($titleCol, $str, $companyCol);
                        }
                }
                if ($this->params[0] != '') // Comments
                {
                        $this->NewLine();
                        $str = _("Comments") . ':';
                        $this->Text($this->leftMargin, $str, $titleCol);
                        $this->Font('bold');
                        $this->Text($titleCol, $this->params[0], $this->endLine - 35);
                        $this->Font();
                }
                $str = _("Page") . ' ' . $this->pageNumber;
                $this->Text($this->endLine - 38, $str);
                $this->Line($this->row - 5, 1);

                $this->row -= ($this->lineHeight + 6);
                $this->Font('italic');
                if ($this->headers2 != null)
                {
                        $count = count($this->headers2);
                        for ($i = 0; $i < $count; $i++)
                                $this->TextCol2($i, $i + 1,     $this->headers2[$i]);
                        $this->NewLine();
                }
                $count = count($this->headers);
                for ($i = 0; $i < $count; $i++)
                        $this->TextCol($i, $i + 1, $this->headers[$i]);
                $this->Font();
                $this->Line($this->row - 5, 1);

                $this->NewLine(2);
        }



}

?>
