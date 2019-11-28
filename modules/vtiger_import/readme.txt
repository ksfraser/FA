/****************************************************************************
Name: VTiger customer import
Based on osCommerce order import by Tom Moulton, modified for Zen Cart 1.5.1 and FrontAccounting 2.3.15 by ckrosco
and then modified and generalized by K Fraser for VTiger.
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module creates a table in your FrontAccounting database containing information about your VTiger database including the name of the database, the username, and the password.

It may be a good idea to set up a user with readonly permissions on your VTiger database, and use this to access your data.

Once set up, this module will import data from your VTiger database. It checks for duplicate First + Last names.

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

PREFIXES - IMPORTANT

This module assumes your database tables use the out-of-the-box prefixes. 
If you use different prefixes, you will have to alter the code accordingly (class.vtiger_customers.php constructor).
