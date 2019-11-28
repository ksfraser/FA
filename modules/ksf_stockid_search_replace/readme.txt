/****************************************************************************
Name: ksf_stockid_search_replace
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module searches through the various tables where a stock_id is used, and replaces one ID with another.  This is meant to be used to "migrate" a stock_id moving the history, as opposed to cloning the product.

Steps:
	Init Tables (install/upgrade step)
	Stock_id S&R.

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to ksf_generate_catalogue

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for ksf_stockid_search_replace -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on stockid_search_replace header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> Banking and General Ledger -> stockid_search_replace

   Click on button -> Create Table
 
   Fill in details for connecting to the VTiger databases -> Update Mysql

----------------------------------------------------------

