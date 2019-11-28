/****************************************************************************
Name: ksf_pear_mail_queue
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module acts as a mail queue so that emailed items (like invoices to customers) can be done while disconnected (i.e. on a laptop at a trade show)

Steps:
	Init Tables (install/upgrade step)
	All Products Export

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to ksf_pear_mail_queue

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for ksf_pear_mail_queue -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on ksf_pear_mail_queue header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> Banking and General Ledger -> ksf_pear_mail_queue

   Click on button -> Create Table
 
   Fill in details for connecting to the VTiger databases -> Update Mysql

----------------------------------------------------------

