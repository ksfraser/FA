/****************************************************************************
Name: ksf_product_groups
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module allows you to associate products to product groups.
For example, all single CDs:
	are the same size and weight. (shipping dimensions module)
	same sales price? (suggested pricing module)
	same re-order level ()
	same target markets (CRM/Marketing)
	same return policy
	same warranty
	same or similar long description (Variable products)
	...
Different modules will need to have different attributes that can apply.
Different products might need to have some attributes NOT apply.


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

