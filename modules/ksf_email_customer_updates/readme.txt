/****************************************************************************
Name: ksf_qoh
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

Allow customer service reps to send a status update to customers about
	sales quotes/orders
	pending deliveries
	pending arrivals of products
	...

	Try to optionally incorporate the PEAR_MAIL_QUEUE module
	Might belong to the customer portal...
	

Steps:
	Init Tables (install/upgrade step)
	All Products Export

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to ksf_generate_catalogue

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for ksf_generate_catalogue -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on ksf_generate_catalogue header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> Banking and General Ledger -> ksf_generate_catalogue

   Click on button -> Create Table
 
   Fill in details for connecting to the VTiger databases -> Update Mysql

----------------------------------------------------------

