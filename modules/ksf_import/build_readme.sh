#!/bin/sh

#This will built the README.txt                       

#$1 = module name
#$1 = module HELP text

cat > README.txt << EOF

/****************************************************************************
Name: $1
Free software under GNU GPL
*****************************************************************************/

WHAT DOES THIS MODULE DO?

This module $1

Steps:
	
	

INSTALLATION:

1. FrontAccounting -> Setup -> Install/Activate Extensions

   Click on the icon in the right column corresponding to $1

   Extensions drop down box -> Activated for (name of your business)

   Click on "active" box for $1 -> Update

2. FrontAccounting -> Setup -> Access Setup

   Select appropriate role click on $1 header and entry -> Save Role

   Logout and log back in

3. FrontAccounting -> TAB -> $1

   Click on button -> XXX
 
   Fill in details 

----------------------------------------------------------


EOF

