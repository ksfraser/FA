<?php

/* Author kevin Fraser
*
*	Take GnuCash csv export files and conver to MultiImport csv.
*
*/

/*
Input:
"Date","Account Name","Number","Description","Notes","Memo","Category","Type","Action","Reconcile","To With Sym","From With Sym","To Num.","From Num.","To Rate/Price","From Rate/Price"

Needed output:
entryid,date,reference,accountcode,dimension1,dimension2,amount,memo

- 'entryid' can be any value, so long as it differs from all other entryids
  in the import file.  Every entryid will result in a separate journal entry
  being created with the specified transaction data within it.
  Transactions with the same entryid must be grouped together within the
  import file.
- 'date' and 'reference' should be the same throughout a given entry.
- For proper use of all of these fields (except 'entryid'), see the
  Journal Entry entry dialog within FrontAccounting under Banking and
  General Ledger.
- entryid does *not* correspond to what the journal entry id will be within
  FrontAccounting.

In the case above, the separator is a comma (,) though any separator will work.

Example import file:
entryid,date,reference,accountcode,dimension1,dimension2,amount,memo
1,10/5/2009,TD-1,10002,MyDim,,945.59,"My memo"
1,10/5/2009,TD-1,5600,MyDim2,,-400,""
1,10/5/2009,TD-1,5602,MyDim2,,-545.59,""
2,10/7/2009,TD-2,5602,,,-100,""
2,10/7/2009,TD-2,5602,MyDim,,100,"Reimbursement"

This file will result in two journal entries:
- One dated 10/5/2009 with reference TD-1 and three transactions
- One dated 10/7/2009 with reference TD-2 and two transactions

Note 1: Dimensions are expressed in references, not IDs!

Note 2: It is wise to enclose memos in double quotes ("my memo") so that
        it will be parsed properly in case it contains the field separator.



Type is either T for transaction or S for Split.
Account Name is the account ( i.e. Liablities:Credit Cards: Walmart MC shown as Walmart MC )

We need a way to map Categories into GL Accounts.


psudocode:
	Set TRXCOUNTER=0
	Read Line
	If line is first, check for header.  If header, ignore.
		Parse Line
			If TYPE = T, 
				clear T and set TRXCOUNTER++.  
				clear reference
				set date = Y-M-D converted to M/D/Y
				set description
				Ignore rest of line
			IF S - set entryID = TRXCOUNTER
				Date = Y-M-D converted to M/D/Y
				reference = Number
				Account Code = Category converted	(eventually can this match Quick Entries??)
					Dim 1 and Dim 2 from Category
				Amount = To Num or From Num as is set.
				memo = description(T) + notes + memo

*/

require_once( 'config.php' );
require_once( 'class.CsvImporter.php' );
require_once( 'class.trx.php' );
require_once( 'class.cConvert.php' );



$filename = "test_in.csv";
$csvin = new CsvImporter( $filename, true );
$data = $csvin->get();
$TRX = 0;

if ( ($fpo = fopen("test_out.csv", "w") ) === FALSE) 
{
	throw new Exception( "Can't open output file" );
}
//write the header.  multijournal is expecting it.
fputs( $fpo, "entryid, date, reference, accountcode, dimension1, dimension2, amount, memo" . "\n" );

$output = array();

$clean_search = array( "gbg ", "gbg", "med", "fa/", "fhs", "auto", "/fa", "mar", "marc", "Marc", "FHS", "util", "file", "dep" );
$clean_replace = array( "", "", "", "", "", "", "", "", "", "", "", "", "", "" );

$skip = false;
foreach( $data as $row )
{
	if( isset( $row["Type"] ) )
	{
		//Convert IN to OUT
		if( $row["Type"] == "T" )
		{
			$skip = false;
			if( 
				strncasecmp( $row["Number"], "FA ", 3 ) !== 0 
				AND ( ! ( 
					strlen($row["Number"]) == 2 
					AND strncasecmp( $row["Number"], "FA", 2 ) === 0
					) 
				)
			)
			{
				if( isset( $T ) )
					$T->write();
				unset( $T );
			//Clean the Reference Number.  I've put "gbg" to indicate I saw the paper and tossed it...
				$row["Number"] = str_replace( $clean_search, $clean_replace, $row["Number"] );
				if( strlen( $row["Number"] ) < 4 )
					$row["Number"] = "";
				$TRX++;
				$T = new trx( $fpo, $TRX );
				$DATE = date( "m/d/Y", strtotime( $row["Date"] ) );	//Need to convert from Y-M-D converted to M/D/Y 
				$ACCTNAME = $row["Account Name"];
				if( strlen( $row["Number"] ) == 0 )
					$row["Number"] = strtotime("now") + $TRX;
					//$row["Number"] = date_timestamp_get();
				//If REF is blank, multijournal-import will create a reference number.
				//This can become a duplicate ref error if a second file with the same
				//date ranges is imported.  Create our own timestamp+TRX based ref.
				//	using +TRX because timestamp resolution is too large that
				//	multiple lines are processed within a timestamp change.
				$REF = $row["Number"];
				$DESC = $row["Description"];
				$NOTES = $row["Notes"];
				$MEMO = $row["Memo"];
				$CAT = $row["Category"];
				if( strlen( $row["To Num."] ) > 1 )
					$AMT = $row["To Num."];
				else
				if( strlen( $row["From Num."] ) > 1 )
					$AMT = $row["From Num."];
				else
					$AMT = "0.00";
				$tconv = new cConvert( $row["Category"] );
				$taccount = $tconv->account;
				$tdim1 = $tconv->dim1;
				$tdim2 = $tconv->dim2;
			}
			else
				$skip = true;
		}
		else
		if( $row["Type"] == "S" )
		{
			if( ! $skip )
			{
				if( $row["Category"] == "Reimbursed Expenses" )
				{
					if( FALSE === strpos( $DESC, "COOPERATORS H&D" ) )
					{
						//not substring
						if( FALSE === strpos( $DESC, "SUNLIFE MED" ) )
						{
							//not substring
							if( FALSE === strpos( $DESC, "rescriptions" ) )	//P or p leading char
							{
								//not substring
								if( FALSE === strpos( $DESC, "cpap" ) )
								{
									//not substring
									/*
									if( FALSE === strpos( $DESC, "cpap" ) )
									{
										//not substring
									}
									else
									{
										$row["Category"] = "Medical Expenses";
									}
									*/
								}
								else
								{
									$row["Category"] = "Medical Expenses";
								}
							}
							else
							{
								$row["Category"] = "Medical Expenses";
							}
						}
						else
						{
							$row["Category"] = "Medical Expenses";
						}
					}
					else
					{
						$row["Category"] = "Medical Expenses";
					}
				}
				$conv = new cConvert( $row["Category"] );
				$account = $conv->account;
				$dim1 = $conv->dim1;
				$dim2 = $conv->dim2;
				if( $account == "1001" )
					$memo = $row["Description"] . ":" . $DESC . "::" . $row["Notes"] . ":::" . $NOTES . "::::" . $row["Memo"] . ":::::" . $MEMO . "{ (" . $row['Category'] . ") " . $CAT . "}";
				else
					$memo = $row["Description"] . ":" . $DESC . "::" . $row["Notes"] . ":::" . $NOTES . "::::" . $row["Memo"] . ":::::" . $MEMO . "{" . $CAT . "}";
				if( strlen( $row["To Num."] ) > 1 )
					$amount = str_replace( ',', '', $row["To Num."] );
				else
				if( strlen( $row["From Num."] ) > 1 )
					$amount = str_replace( ',', '', $row["From Num."] );
				else
					$amount = "0.00";
		
				//		entryid,	date,	reference,	accountcode,	dimension1,	dimension2,	amount,	memo
				/*
				$outrow = array( $TRX,		
						$DATE,	
						$REF,		
						$account,	
						$dim1,		
						$dim2,		
						$amount,	
						$memo );
				//var_dump( $outrow );
				$output[] = $outrow;
				//var_dump( $output );
				*/
				if( $amount <> 0 )
					$T->add_line( $TRX, $DATE, $REF, $account, $dim1, $dim2, $amount, $memo );
			}
		}
	}
}

/*
foreach( $output as $row )
{
	fputcsv( $fpo, $row );	
}
*/


fclose($fpo);

?>
