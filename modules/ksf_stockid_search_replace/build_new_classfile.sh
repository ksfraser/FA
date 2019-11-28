#!/bin/sh

echo "<?php" > class.$1.php

echo "
/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( 'class.generic_fa_interface.php' );

class $1 extends generic_fa_interface {
	function __construct()
	{
	}
}" >> class.$1.php

