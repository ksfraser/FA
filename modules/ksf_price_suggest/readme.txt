/****************************************************************************
Name: Calculate Pricing
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module was written because COAST had pricing available in a spreadsheet for their 3000 REMO products.
It had a MSRP and a dealer cost, which we imported into the base_cost and MSRP price books.

We then needed to set our Retail.  Our desired retail then takes a look at SYSTEM settings from base_price + percent (ignores rounding).  It then looks at our Price Type for the percentage for auto_calc to determine what the system would recommend on the SALES PRICE tabs.

We also need to consider MAP pricing where applicable.

We will also let our competition prices be considered.

================================
TODO
----------
	Rules Engine

	Make sure checks for Base Cost (include multi-vendor, setting for choose highest/lowest, shipping add-up)
	Make sure check for registered versus retail
	Ensure rounding to nearest X function for Post-Tax pricing (options as to which price books get altered)

	Have a window similar to Woo showing list of products where a selected pricing type is blank
================================

********************************
WARNING
********************************
As written, this assumes ALL of your pricing is in the same currency!!!


*****************************************************************************
Considerations:
	If MSRP is higher than desired_retail, set proposed_retail to MSRP
		update 0_prices_calculate set proposed_retail = if(MSRP>desired_retail, MSRP, desired_retail)

	If desired_retail is higher than MSRP then
		if desired_retail is less than competition set to desired_retail
			update 0_prices_calculate set proposed_retail = if(MSRP>desired_retail, MSRP, if(desired_retail<competition, desired_retail, competition) )

		if competition < desired_retail, set to competition if > MSRP
			update 0_prices_calculate set proposed_retail = if(MSRP>desired_retail, MSRP, 
						if(desired_retail<competition, desired_retail, if(competition>MSRP, competition, MSRP ) ) )
*****************************************************************************
Other capabilities
	We set some products (i.e. CDs) to be the same retail.  We need the ability to go through and update those prices as a group.

	Audit pricing (so that we are within a definable percentage price of our competition and/or MSRP)

	Also want to add in later the ability to have multiple discounted price books.

	Also want to add in later the capability to have the quantities rounded so that including taxes are a logical whole dollar amount.  This one probably needs to be able to update prices rather than just insert new ones as tax rates change (GST + PST).

*****************************************************************************
New Table
0_prices_calculate

CREATE TABLE `0_prices_calculate` (
  `stock_id` varchar(20) NOT NULL default '',
  `base_cost` double NOT NULL default '0',
  `msrp` double NOT NULL default '0',
  `desired_retail` double NOT NULL default '0',
  `MAP` double NOT NULL default '0',
  `competition` double NOT NULL default '0',
  `proposed_retail` double NOT NULL default '0',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`stock_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1

*****************************************************************************
Queries

insert ignore into 0_prices_calculate (stock_id) SELECT p1.stock_id
 FROM `0_prices` p1

*insert ignore into 0_prices_calculate (stock_id, base_cost, desired_retail) SELECT p1.stock_id, p1.price as base_cost,
*    (
*       (p1.price + (p1.price * (SELECT value FROM `0_sys_prefs` where name in ('add_pct'))))
*        * (SELECT factor FROM `0_sales_types` where sales_type in ( 'Retail' ))
*     ) as desired_retail
* FROM `0_prices` p1
* where p1.sales_type_id in (select id from 0_sales_types where sales_type in ( 'Base Cost' ) )

update 0_prices_calculate p1
join 0_prices p2
on p1.stock_id=p2.stock_id
set p1.base_cost = p2.price 
where p2.sales_type_id = (select id from 0_sales_types where sales_type in ( 'Base Cost' ) ) 

update 0_prices_calculate p1
join 0_prices p2
on p1.stock_id=p2.stock_id
set p1.desired_retail =
       (p2.price + (p2.price * (SELECT value FROM `0_sys_prefs` where name in ('add_pct'))))
        * (SELECT factor FROM `0_sales_types` where sales_type in ( 'Retail' ))
where p2.sales_type_id = (select id from 0_sales_types where sales_type in ( 'Base Cost' ) )


update 0_prices_calculate p1
join 0_prices p2
on p1.stock_id=p2.stock_id
set p1.msrp = p2.price 
where p2.sales_type_id = (select id from 0_sales_types where sales_type in ( 'MSRP' ) ) 

update 0_prices_calculate p1
join 0_prices p2
on p1.stock_id=p2.stock_id
set p1.MAP = p2.price 
where p2.sales_type_id = (select id from 0_sales_types where sales_type in ( 'Min Advertised Price' ) ) 

update 0_prices_calculate p1
join 0_prices p2
on p1.stock_id=p2.stock_id
set p1.competition = p2.price 
where p2.sales_type_id = (select id from 0_sales_types where sales_type in ( 'tartantown' ) ) 

update 0_prices_calculate set msrp = desired_retail where msrp < base_cost


insert ignore into 0_prices (stock_id, sales_type_id, curr_abrev, price) select stock_id, (select id from 0_sales_types where sales_type in ( 'Retail' )), "CAD", proposed_retail from 0_prices_calculate


INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to CALC_PRICING

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for CALC_PRICING -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on CALC_PRICING header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> Banking and General Ledger -> CALC_PRICING 

   Click on button -> Create Table
 

----------------------------------------------------------

