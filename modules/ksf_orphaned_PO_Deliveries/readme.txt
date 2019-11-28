/****************************************************************************
Name: ksf_Orphaned_PO_Deliveries
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module looks for PO Deliveries that haven't had all of its transactions inserted.  While InnoDB tables are atomic, MyISAM are not, so if a transaction times out there can be lost data,  We will look for that data and fix it.

Steps:
	Init Tables (install/upgrade step)
	Seek missing data  

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to ksf_orphaned_PO_Deliveries

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for ksf_orphaned_PO_Deliveries -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on ksf_orphaned_PO_Deliveries header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> Purchasing -> ksf_orphaned_PO_Deliveries

   Click on button -> Create Table
 

----------------------------------------------------------

