import_multijournal will generate a series of entries.  We will take GC export CSVs and convert them.
	*It does not import ANY transactions if there is an error in the file
	*This includes duplication of reference numbers


Input (GnuCash CSV export):
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
*In FA 2.4.1 this defaults to XXX/YYYY where X is a number and YYYY is the year the reference was created.

Note 2: It is wise to enclose memos in double quotes ("my memo") so that
        it will be parsed properly in case it contains the field separator.



Type is either T for transaction or S for Split.
Account Name is the account ( i.e. Liablities:Credit Cards: Walmart MC shown as Walmart MC )

We map Categories into GL Accounts through the config.php file (conf array).


psudocode:
	Set TRXCOUNTER=0
	Read Line
	If line is first, check for header.  If header, ignore.
		Parse Line
			If TYPE = T, 
				write previous transaction to outputfile
				unset and rebuild transaction class instance.
				clear T and set TRXCOUNTER++.  
				clear reference
				set date = Y-M-D converted to M/D/Y
				set description
				Ignore rest of line
			IF S - set entryID = TRXCOUNTER
				Date = Y-M-D converted to M/D/Y
				reference = Number.  Clean some of my comments (e.g. FA/FHS/Auto/Med)
					ALSO create a timestamp + file-TRX reference number if there isn't one.  While it isn't strictly needed,
					importing multiple files with the same date and NO reference number ends up with errors as multi-import
					creates a MM/DD-X sequential reference which becomes a duplicate on a second import...
				Account Code = Category converted	(eventually can this match Quick Entries??)
					Dim 1 and Dim 2 from Category
				Amount = To Num or From Num as is set.
				memo = description(T) + notes + memo
				Add as line into transaction instance.
		
