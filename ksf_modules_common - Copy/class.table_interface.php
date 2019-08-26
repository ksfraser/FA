<?php

class table_interface
{
	function update_table()
	{
		$sql = "UPDATE `" . $this->table_details['tablename'] . "` set" . "\n";
		$fieldcount = 0;
		foreach( $this->fields_array as $row )
		{
			if( $row['name'] != $this->table_details['index'] )
			{
				if( isset( $this->$row['name'] ) )
				{
					if( $fieldcount > 0 )
						$sql .= ", ";
					$sql .= "`" . $row['name'] . "`='" . $this->$row['name'] . "'";
					$fieldcount++;
				}
			}
		}
		$pri = $this->table_details['primarykey'];
		$sql .= " WHERE '" . $pri . "'='" . $this->$pri . "'";

		//var_dump( $sql );
		db_query( $sql, "Couldn't update table " . $this->table_details['tablename'] . " for index " .  $this->$this->table_details['tablename'] );	
	}
	function check_table_for_id()
	{
		//check to see if we have the id in a record
		$go = false;
		foreach( $this->fields_array as $row )
		{
			if( isset( $this->$row['name'] ) )
			{
				if( $this->$row['name'] == 'id' )
				{
					$go = true;
				}
			}
		}
		if( $go )
		{
			$sql = "select count('id') as count from $this->table_details['tablename']";
			$sql .= " WHERE id = '" . $this->id . "'";
			$res = db_query( $sql, "Couldn't check for count io table " . $this->table_details['tablename'] . " with " .  $sql );	
			$count = db_fetch_assoc( $res );
			if( $count['count'] > 0 )
				return TRUE;
			else
				return FALSE;
		}
		return FALSE;
	}
	/*int index of last insert*/
	/*@int@*/function insert_table()
	{
		$sql = "INSERT IGNORE INTO `" . $this->table_details['tablename'] . "`" . "\n";
		$fieldcount = 0;
		$fields = "(";
		$values = "values(";
		foreach( $this->fields_array as $row )
		{
			if( isset( $this->$row['name'] ) )
			{
				if( $fieldcount > 0 )
				{
					$fields .= ", ";
					$values .= ", ";
				}
				$fields .= "`" . $row['name'] . "`";
				$values .= "'" . $this->$row['name'] . "'";
				$fieldcount++;
			}
		}
		$fields .= ")";
		$values .= ")";
		$sql .= $fields . $values;
		//var_dump( $sql );
		if( $fieldcount > 0 )
			db_query( $sql, "Couldn't insert into table " . $this->table_details['tablename'] . " for " .  $sql );	
		else
			display_error( "No values set so couldn't insert" );

		return db_insert_id();
	}
	function create_table()
	{
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table_details['tablename'] . "` (" . "\n";
		$fieldcount = 0;
		foreach( $this->fields_array as $row )
		{
			if( $fieldcount > 0 )
			{
				$sql .= ",";
			}
			$sql .= "`" . $row['name'] . "` " . $row['type'];
			if( isset( $row['null'] ) )
				$sql .= " " . $row['null'];
			if( isset( $row['auto_increment'] ) )
				$sql .= " AUTO_INCREMENT";
			if( isset( $row['default'] ) )
				$sql .= " DEFAULT " . $row['default'];
			$fieldcount++;
		}
		if( isset( $this->table_details['primarykey'] ) )
		{
			if( $fieldcount > 0 )
			{
				$sql .= ",";
			}
		
			$sql .= " Primary KEY (`" . $this->table_details['primarykey'] . "`)";
		}
		else
		{
			//$sql .= " Primary KEY (`" . $fields_array[0]['name'] . "`)";
		}
		if( isset( $this->table_details['index'] ) )
		{
			foreach( $this->table_details['index'] as $index )
			{
				if( $index['type'] == "unique")
				{
					$sql .= ", UNIQUE KEY `" . $index['keyname'] . "` ( " . $index['columns'] . " )";
				}
				else
					//$sql .= ", INDEX " . $index['keyname'] . "( " . $index['columns'] . " )";
					$sql .= ", UNIQUE KEY `" . $index['keyname'] . "` ( " . $index['columns'] . " )";
			}
		}
		$sql .= " )";
		if( isset( $this->table_details['engine'] ) )
		{
			$sql .= " ENGINE=" . $this->table_details['engine'] . "";
		}
		else
		{
			$sql .= " ENGINE=MyISAM";
		}
		if( isset( $this->table_details['charset'] ) )
		{
			$sql .= " DEFAULT CHARSET=" . $this->table_details['charset'] . ";";
		}
		else
		{
			$sql .= " DEFAULT CHARSET=utf8;";
		}
		//var_dump( $sql );
		display_notification( __FILE__ . " Creating table " . $this->table_details['tablename'] );
		db_query( $sql, "Couldn't create table " . $this->table_details['tablename'] );
		return $this->alter_table();
	}
	function alter_table()
	{
		//Need a function for doing updates/upgrades between versions.
		//ASSUMPTION:
		//	create_table as been run, and if not exist may
		//	or may not have triggered.  Regardless we are
		//	going to ALTER table to ensure all INDEXES are
		//	created and all COLUMNS exist.
		//
		//	ALTER TABLE tablename
		//		ADD COLUMN (colname colspec, colname2 colspec2)
		$sql = "ALTER TABLE `" . $this->table_details['tablename'] . "`" . "\n";
		$fieldcount = 0;
		$col = "ADD COLUMN (";
		$endcol = ")";
		$col_data = "";
		foreach( $this->fields_array as $row )
		{
			if( $fieldcount > 0 )
			{
				$col_data .= ",";
			}
			$col_data .= "`" . $row['name'] . "` " . $row['type'];
			if( isset( $row['null'] ) )
				$col_data .= " " . $row['null'];
			if( isset( $row['auto_increment'] ) )
				$col_data .= " AUTO_INCREMENT";
			if( isset( $row['default'] ) )
				$col_data .= " DEFAULT " . $row['default'];
			$fieldcount++;
		}
		if( $fieldcount > 0 )
			$col .= $col_data . $endcol;
		//ASSUMING the primary key was generated with the table and no changes since.
	/*
		if( isset( $this->table_details['index'] ) )
		{
			foreach( $this->table_details['index'] as $index )
			{
				if( $index['type'] == "unique")
				{
					$sql .= ", UNIQUE KEY `" . $index['keyname'] . "` ( " . $index['columns'] . " )";
				}
				else
					//$sql .= ", INDEX " . $index['keyname'] . "( " . $index['columns'] . " )";
					$sql .= ", UNIQUE KEY `" . $index['keyname'] . "` ( " . $index['columns'] . " )";
			}
		}
		$sql .= " )";
	 */
		//ASSUMING no changes to the engine nor charset
		//var_dump( $sql );
		display_notification( __FILE__ . " Altering table " . $this->table_details['tablename'] );
		return db_query( $sql, "Couldn't alter table " . $this->table_details['tablename'] );
	}

}

?>
