/****************************************************************************
Name: Coast Export (Coast music purchase order export)
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module takes a Purchase Order and exports it into a CSV in the MODEl# - Qty 2 column format
that Coast Music expects for their B2B site.

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to vtiger_import

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for vtiger_import -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on Import VTiger header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> Banking and General Ledger -> Import VTiger

   Click on button -> Create Table
 
   Fill in details for connecting to the VTiger databases -> Update Mysql

----------------------------------------------------------

