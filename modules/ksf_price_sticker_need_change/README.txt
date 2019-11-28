
/****************************************************************************
Name: ksf_price_sticker_need_change
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module ksf_price_sticker_need_change will list the stock_ids that have
price changes that require a price sticker update.

Design:
UI
	Conf screen to 
		enable against WOO
		Configure feed to SuiteCRM (update tasks)
		whether we record each product by locations holding inventory so we can track where prices haven't been completely updated.
	Screen to launch a search 
		Compare 1_prices against 1_woo
	Screen to mark a price update (checkbox).  If configured by location, have a location selector/filter
DB
	Table to store list of stock_ids and their new prices, plus change date.  Include updated on timestamp. Include location code
HOOKS
	Trap when an item price is updated.  Compare old against new and record new record.



Steps:
	
	

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to ksf_price_sticker_need_change

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for ksf_price_sticker_need_change -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on ksf_price_sticker_need_change header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> TAB -> ksf_price_sticker_need_change

   Click on button -> XXX
 
   Fill in details 

----------------------------------------------------------


