/****************************************************************************
Name: ksf_qoh
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module generates a list of 1 day sale items.

Steps:
	Init Tables (install/upgrade step)
	set config variables
	run generator

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to ksf_random_one_day_saleg

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for ksf_random_one_day_saleg -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on ksf_random_one_day_saleg header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> Banking and General Ledger -> ksf_random_one_day_saleg

   Click on button -> Create Table
 
   Fill in details for connecting to the VTiger databases -> Update Mysql

----------------------------------------------------------

