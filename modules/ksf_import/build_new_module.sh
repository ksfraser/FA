#!/bin/sh

#This will built the Hooks, base file and include file

#$1 = module name
#$2 = module HELP text

. ./build_mod_directories.sh

cat > _init/config << EOF
Package: $1
Name: $1
Description: $2
Author: Kevin Fraser <kevin@ksfraser.com>
Maintenance: Kevin Fraser <kevin@ksfraser.com>
Homepage: http://ksfraser.com
Type: extension
InstallPath: modules/$1
Filename: $1
Version: 1.0
EOF

cat > $1.php << EOF
<?php
/**********************************************
Name: 
for FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/
\$path_to_root = '../../';	//Needed by Sessions etc.
\$page_security = 'SA_$1';
include( __DIR__ . "/../../includes/session.inc");
add_access_extensions();
set_ext_domain('modules/$1');
include_once(__DIR__ . "/../../includes/ui.inc");
include_once(__DIR__ . "/../../includes/data_checks.inc");

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

//global \$db; // Allow access to the FA database connection
//\$debug_sql = 0;  // Change to 1 for debug messages

	include_once( "class.$1.php");
	require_once( '$1.inc.php' );
	\$my_mod = new $1( $1_PREFS );
	\$my_mod->set_var( 'help_context', $1_HELP );
	\$my_mod->set_var( 'redirect_to', "$1.php" );
	\$my_mod->run();

EOF

cat > $1.inc.php << EOF
<?php
define('$1_PREFS', '$1_prefs' );
define('$1_HELP', '$2' );
EOF

. ./build_new_classfile_controller.sh $1 $1 $1
. ./build_new_classfile_model.sh $1 $1 $1
. ./build_new_classfile_view.sh $1 $1 $1
. ./build_new_classfile_config.sh $1 $1 $1

. ./build_new_hookfile.sh $1 $2

. ./build_package_module.sh $1 $2

. ./build_documentation.sh $1 $2

. ./build_readme.sh $1 $2

