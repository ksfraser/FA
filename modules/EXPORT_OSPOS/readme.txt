/****************************************************************************
Name: Generate EAN 
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module will generate EANs (UPCs) for products that don't have them as Foreign Codes.  The assumption is that
you are using an internal stock_id code (e.x. A-BC-DEF).  Also assuming that a product from a supplier DOES NOT
have UPCs associated and you want to be able to use UPCs for things like sales orders (use a barcode scanner to
add items to the sales qote/order/invoice).  The routine will find any stock_id that does not have a foreign code
associated that is also not the same as the stock_id.

Internal Use barcodes begin with 020, 040 or 200.  This code assumes you are using one of those three prefixes 
for searching for foreign codes for printing.

This module will also allow you to print barcodes through a mail-merge.  You can choose a PO to print barcodes for.
The function will grab the list of products on the PO and the quantities that have been recieved.  The module will 
generate and email a CSV with a line for each item's quantity on the PO (i.e. order 6, and there will be 6 lines 
in the CSV) so that you can mail-merge into a doc for printing barcodes on labels using a barcode font such as 3 of 9.
However, it only grabs FOREIGN CODES and they must be an internal use barcode.

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to Generate EAN

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for Generate EAN -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on Generate EAN header and entry -> Save Role

   Logout and log back in

----------------------------------------------------------

