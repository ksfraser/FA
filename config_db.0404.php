<?php

/*Connection Information for the database
$def_coy - the default company that is pre-selected on login

'host' - the computer ip address or name where the database is. The default is 'localhost' assuming that the web server is also the sql server.

'dbuser' - the user name under which the company database should be accessed.
  NB it is not secure to use root as the dbuser with no password - a user with appropriate privileges must be set up.

'dbpassword' - the password required for the dbuser to authorise the above database user.

'dbname' - the name of the database as defined in the RDMS being used. Typically RDMS allow many databases to be maintained under the same server.
'tbpref' - prefix on table names, or '' if not used. Always use non-empty prefixes if multiply company use the same database.
*/

if( include_once( $path_to_root . '/../common-config.inc' ) )
{
	//settings should have been declared
	$db_connections = array (
	  0 => 
	  array (
	    'name' => 'ADMIN COMPANY',
	    'host' => $wgDBserver,
	    'dbuser' => $wgDBuser,
	    'dbpassword' => $wgDBpassword,
	    'dbname' => $enviro . '_' . $companyname,
	    'tbpref' => '0_',
	  ),
	  1 => 
	  array (
	    'name' => strtoupper( $companyname ) . ' ' . $softwarename . ' ' . $enviroName,
	    'host' => $wgDBserver,
	    'dbuser' => $wgDBuser,
	    'dbpassword' => $wgDBpassword,
	    'dbname' => $enviro . '_' . $companyname,
	    'tbpref' => '1_',
    	  ),
    	);
	$tb_pref_counter = 2;

}
else
{
	$def_coy = 1;
	$tb_pref_counter = 4;

	$db_connections = array (
	  0 => 
	  array (
	    'name' => 'ADMIN COMPANY',
	    'host' => 'localhost',
	    'dbuser' => 'fhs',
	    'dbpassword' => 'fhs',
	    'dbname' => 'fhs',
	    'tbpref' => '0_',
	  ),
	  1 => 
	  array (
	    'name' => 'FHS Acceptance',
	    'host' => 'fhsws002.ksfraser.com',
	    'dbuser' => 'fhs',
	    'dbpassword' => 'fhs',
	    'dbname' => 'acpt_fhs',
	    'tbpref' => '1_',
	  ),
	  2 => 
	  array (
	    'name' => 'Prod Staging FHS',
	    'host' => 'fhsws002.ksfraser.com',
	    'dbuser' => 'fhs',
	    'dbpassword' => 'fhs',
	    'dbname' => 'acpt_fhs',
	    'tbpref' => '1_',
	  ),
	  3 => 
	  array (
	    'name' => 'DEVEL FHS',
	    'host' => 'fhsws002.ksfraser.com',
	    'dbuser' => 'fhs',
	    'dbpassword' => 'fhs',
	    'dbname' => 'fhs',
	    'tbpref' => '2_',
	  )
	);
}
?>
