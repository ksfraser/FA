<?php

/* Author kevin Fraser
*
*	Take GnuCash csv export files and conver to MultiImport csv.
*
*/

class fa_quickentry
{
	/*
	 desc 1_quick_entries;
	+-------------+----------------------+------+-----+---------+----------------+
	| Field       | Type                 | Null | Key | Default | Extra          |
	+-------------+----------------------+------+-----+---------+----------------+
	| id          | smallint(6) unsigned | NO   | PRI | NULL    | auto_increment |
	| type        | tinyint(1)           | NO   |     | 0       |                |
	| description | varchar(60)          | NO   | MUL | NULL    |                |
	| base_amount | double               | NO   |     | 0       |                |
	| base_desc   | varchar(60)          | YES  |     | NULL    |                |
	| bal_type    | tinyint(1)           | NO   |     | 0       |                |
	+-------------+----------------------+------+-----+---------+----------------+
	*/

	protected $id;		//smallint(6) unsigned | NO   | PRI | NULL    | auto_increment |
	protected $type;	//tinyint(1)           | NO   |     | 0       |                |
	protected $description;	//varchar(60)          | NO   | MUL | NULL    |                |
	protected $base_amount;	//double               | NO   |     | 0       |                |
	protected $base_desc;	//varchar(60)          | YES  |     | NULL    |                |
	protected $bal_type;	//tinyint(1)           | NO   |     | 0       |                |

	function __construct()
	{
	}
}

class fa_quickentry_lines
{
	/*
	desc 1_quick_entry_lines;
	+---------------+----------------------+------+-----+---------+----------------+
	| Field         | Type                 | Null | Key | Default | Extra          |
	+---------------+----------------------+------+-----+---------+----------------+
	| id            | smallint(6) unsigned | NO   | PRI | NULL    | auto_increment |
	| qid           | smallint(6) unsigned | NO   | MUL | NULL    |                |
	| amount        | double               | YES  |     | 0       |                |
	| action        | varchar(2)           | NO   |     | NULL    |                |
	| dest_id       | varchar(15)          | NO   |     |         |                |
	| dimension_id  | smallint(6) unsigned | YES  |     | NULL    |                |
	| dimension2_id | smallint(6) unsigned | YES  |     | NULL    |                |
	+---------------+----------------------+------+-----+---------+----------------+
	*/

	protected $id;			//smallint(6) unsigned | NO   | PRI | NULL    | auto_increment |
	protected $qid;			//smallint(6) unsigned | NO   | MUL | NULL    |                |
	protected $amount;		//double               | YES  |     | 0       |                |
	protected $action;		//varchar(2)           | NO   |     | NULL    |                |
	protected $dest_id;		//varchar(15)          | NO   |     |         |                |
	protected $dimension_id;	//smallint(6) unsigned | YES  |     | NULL    |                |
	protected $dimension2_id;	//smallint(6) unsigned | YES  |     | NULL    |                |

	function __construct()
	{
	}
}

?>
