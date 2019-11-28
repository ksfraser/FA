/****************************************************************************
Name: Front Accounting CRM 
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module provides the fields that VTIGER does for customers.
VTiger is heavyweight on older hardware.  In terms of customer data, FA
doesn't have a lot of basic customer data for sales planning and tracking.

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to fa-crm

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for fa-crm -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on FA-CRM header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> Banking and General Ledger -> FA-CRM

   Click on button -> Create Table
 
   Fill in details for connecting to the VTiger databases -> Update Mysql

----------------------------------------------------------

BASE FILES

hooks.php sets up security levels, file names
index.php redirects calls into the dir to home

