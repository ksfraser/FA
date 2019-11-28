/****************************************************************************
Name: ksf_coupons
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

*It is designed for creating coupons that can be applied against an order
*it is designed to match up with the data that WOOCommerce expects for its coupons so that they can be exchanged back and forth.  EXPORT_WOO is what will sync them.
*It will apply coupons against a Sales Order (Invoice) on a new screen cloned from the original SO/QI/Invoice screen(s).

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to ksf_coupons

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for ksf_coupons -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on ksf_coupons header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> TAB -> ksf_coupons

   Click on button -> Create Table
 
   Fill in details for connecting to the ksf_coupons databases -> Update Mysql

----------------------------------------------------------

