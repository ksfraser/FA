<?php

/* Author kevin Fraser
*
*	Take GnuCash csv export files and conver to MultiImport csv.
*
*/

class QuickEntry
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
6 rows in set (0.06 sec)

MySQL [fhs]> desc 1_quick_entry_lines;
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
}

?>
