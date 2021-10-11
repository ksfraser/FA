<?php

require_once( 'class.fa_image.php' );
//try {
	$o = new fa_image( "" );
//}
//catch( Exception $e )
//{
//}
$o->object_var_names();
var_dump( $o->object_fields );



