<?php

$path_to_faroot= dirname ( realpath ( __FILE__ ) ) . "/../..";

require_once( $path_to_faroot . '/includes/db/connect_db.inc' ); //db_query, ...
require_once( $path_to_faroot . '/includes/errors.inc' ); //check_db_error, ...

require_once( 'class.origin.php' );

class db_base extends origin
{
	var $action;
	var $dbHost;
	var $dbUser;
	var $dbPassword;
	var $dbName;
	var $db_connection;
	var $prefs_tablename;
	var $company_prefix;
	function __construct( $host, $user, $pass, $database, $prefs_tablename )
	{
//		echo "Base constructor prefs_tablename: $prefs_tablename";
		$this->set_var( "dbHost", $host );
		$this->set_var( "dbUser", $user );
		$this->set_var( "dbPassword", $pass );
		$this->set_var( "dbName", $database );
		$this->set_var( "prefs_tablename", $prefs_tablename );
		$this->set_prefix();
		$this->connect_db();
	}
	function connect_db()
	{
        //	$this->db_connection = mysql_connect($this->dbHost, $this->dbUser, $this->dbPassword);
        	if (!$this->db_connection) 
		{
			display_notification("Failed to connect to source of import Database");
			return FALSE;
		}
		else
		{
            		mysql_select_db($this->dbName, $this->db_connection);
			return TRUE;
		}
	}
	/*bool*/ function is_installed()
	{
        	global $db_connections;
        
		$cur_prefix = $db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'];

        	$sql = "SHOW TABLES LIKE '%" . $cur_prefix . $this->prefs_tablename . "%'";
        	$result = db_query($sql, __FILE__ . " could not show tables in is_installed: " . $sql);

        	return db_num_rows($result) != 0;
	}
	function set_prefix()
	{
		if( !isset( $this->company_prefix ) )
		{
			if( strlen( TB_PREF ) == 2 )
			{
				$this->set_var( 'company_prefix', TB_PREF );
			}
			else
			{
        			global $db_connections;
				$this->set_var( 'company_prefix',  $db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'] );
			}
			
		}
	}
	function create_prefs_tablename()
	{
	        $sql = "DROP TABLE IF EXISTS " . $this->company_prefix . $this->prefs_tablename;
		        db_query($sql, "Error dropping table");
		
	    	$sql = "CREATE TABLE `" . $this->company_prefix . $this->prefs_tablename ."` (
		         `name` char(32) NOT NULL default \"\",
		         `value` varchar(100) NOT NULL default \"\",
		          PRIMARY KEY  (`name`))
		          ENGINE=MyISAM";
	    	db_query($sql, "Error creating table");
		$this->set_pref('lastcid', 0);
		$this->set_pref('lastoid', 0);
		
	}
	function mysql_query( $sql, $errmsg = NULL )
	{
		//var_dump( $sql );
		$result = db_query( $sql, $errmsg );
		//var_dump( $result );
		$data = db_fetch( $result );
		//var_dump( $data );
		return $data;
	}
	function set_pref( $pref, $value )
	{
	        $sql = "REPLACE " . $this->company_prefix . $this->prefs_tablename . " (name, value) VALUES (".db_escape($pref).", ".db_escape($value).")";
    		db_query($sql, "can't update ". $pref);
	}
	/*string*/ function get_pref( $pref )
	{
        	$pref = db_escape($pref);

    		$sql = "SELECT * FROM " . $this->company_prefix . $this->prefs_tablename . " WHERE name = $pref";
    		$result = db_query($sql, "could not get pref ".$pref);

    		if (!db_num_rows($result))
        		return null;
        	$row = db_fetch_row($result);
    		return $row[1];
	}
	function loadprefs()
	{
		// Get last oID exported
		foreach( $this->config_values as $row )
		{
		     $this->set_var( $row['pref_name'], $this->get_pref( $row['pref_name'] ) );
		}
	}
	function updateprefs()
	{
		foreach( $this->config_values as $row )
		{
			if( isset( $_POST[$row['pref_name']] ) )
			{
				$this->set_var( $row['pref_name'], $_POST[ $row['pref_name'] ] );
				$this->set_pref( $row['pref_name'], $_POST[ $row['pref_name'] ] );
			}
		}
	}
	function create_table( $table_array, $field_array )
	{
		$sql = "CREATE TABLE IF NOT EXISTS `" . $table_array['tablename'] . "` (" . "\n";
		$fieldcount = 0;
		foreach( $field_array as $row )
		{
			$sql .= "`" . $row['name'] . "` " . $row['type'];
			if( isset( $row['null'] ) )
				$sql .= " " . $row['null'];
			if( isset( $row['auto_increment'] ) )
				$sql .= " AUTO_INCREMENT";
			if( isset( $row['default'] ) )
				$sql .= " DEFAULT " . $row['default'];
			$sql .= ",";
			$fieldcount++;
		}
		if( isset( $table_array['primarykey'] ) )
		{
			$sql .= " Primary KEY (`" . $table_array['primarykey'] . "`)";
		}
		else
		{
			$sql .= " Primary KEY (`" . $field_array[0]['name'] . "`)";
		}
		if( isset( $table_array['index'] ) )
		{
			foreach( $table_array['index'] as $index )
			{
				$sql .= ", INDEX " . $index['name'] . "( " . $index['columns'] . " )";
			}
		}
		$sql .= " )";
		if( isset( $table_array['engine'] ) )
		{
			$sql .= " ENGINE=" . $table_array['engine'] . "";
		}
		else
		{
			$sql .= " ENGINE=MyISAM";
		}
		if( isset( $table_array['charset'] ) )
		{
			$sql .= " DEFAULT CHARSET=" . $table_array['charset'] . ";";
		}
		else
		{
			$sql .= " DEFAULT CHARSET=utf8;";
		}
		var_dump( $sql );
		db_query( $sql, "Couldn't create table " . $table_array['tablename'] );
	}

}
?>
