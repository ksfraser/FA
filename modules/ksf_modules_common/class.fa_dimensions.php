<?php

require_once( 'class.fa_table_wrapper.php' );

$path_to_root="../..";


/********************************************************//**
 * Various modules need to be able to get info about purchase order details from FA
 *
 *	This class uses FA specific routines (display_notification etc)
 *	This is a wrapper for the FA table.
 *
 * **********************************************************/
class fa_dimensions extends fa_table_wrapper
{
	var $min_cid;
	var $max_cid;
	var $errors = array();
	var $warnings = array();

	/*
	 *
| id        | int(11)     | NO   | PRI | NULL       | auto_increment |
| reference | varchar(60) | NO   | UNI |            |                |
| name      | varchar(60) | NO   |     |            |                |
| type_     | tinyint(1)  | NO   | MUL | 1          |                |
| closed    | tinyint(1)  | NO   |     | 0          |                |
| date_     | date        | NO   | MUL | 0000-00-00 |                |
| due_date  | date        | NO   | MUL | 0000-00-00 |                |
	 * 
	 * */
	protected $id       ;//int(11)     | NO   | PRI | NULL       | auto_increment |
	protected $reference;//varchar(60) | NO   | UNI |            |                |
	protected $name     ;//varchar(60) | NO   |     |            |                |
	protected $type_    ;//tinyint(1)  | NO   | MUL | 1          |                |
	protected $closed   ;//tinyint(1)  | NO   |     | 0          |                |
	protected $date_    ;//date        | NO   | MUL | 0000-00-00 |                |
	protected $due_date ;//date        | NO   | MUL | 0000-00-00 |                |

	//function __construct( /*$prefs_db*/ )
	function __construct( $caller = null )
	{
		//parent::__construct( $prefs_db );
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$acctl = 'varchar(' . ACCOUNTCODE_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . 'dimensions';

		$this->fields_array[] = array('name' => 'id       ', 'type' => 'int(11)    ', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//PRI | NULL       | auto_increment |
		$this->fields_array[] = array('name' => 'reference', 'type' => 'varchar(60)', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//UNI |            |                |
		$this->fields_array[] = array('name' => 'name     ', 'type' => 'varchar(60)', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//    |            |                |
		$this->fields_array[] = array('name' => 'type_    ', 'type' => 'bool ', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//MUL | 1          |                |
		$this->fields_array[] = array('name' => 'closed   ', 'type' => 'bool ', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//    | 0          |                |
		$this->fields_array[] = array('name' => 'date_    ', 'type' => 'date       ', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//MUL | 0000-00-00 |                |
		$this->fields_array[] = array('name' => 'due_date ', 'type' => 'date       ', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//MUL | 0000-00-00 |                |

		$this->table_details['primarykey'] = "category_id";
	}
	function active_dimensions()
	{
		// select category_id, description, dflt_dim1, dflt_dim2 from 1_stock_category where inactive=0 and dflt_no_sale=0 limit 10;
		$this->select_array[] = 'id';
		$this->select_array[] = 'name';
		$this->select_array[] = 'type_';
		$this->from_array[] = $this->table_details['tablename'];
		$this->where_array['closed'] ='0';
		//$this->orderby_array = array( 'd.item_code', 's.supp_name' );
		$this->buildSelectQuery();
	}
	/*int*/function get_active_dimensions()
	{
		$this->active_dimensions();
		$this->query( __METHOD__ . " couldn't get the list of active dimensions" );
		//Now what LOL!  ->query_result
		//returns an array of results
		return count( $this->query_result );
	}
	/*array*/function get_active_dimensions_array()
	{
		$this->get_active_dimensions();
		foreach( $this->query_result as $row )
		{
			$dim_array[$row['id']]['id'] = $row['id'];
			$dim_array[$row['id']]['name'] = $row['name'];
			$dim_array[$row['id']]['type'] = $row['type_'];
		}
		return $dim_array;
	}
	/**/
}

/**********Testing******************/
class pod_test extends fa_stock_category
{
	function __construct()
	{
		parent::__construct;
		$this->clear_sql_vars();
		$this->order2deliverydays();
			//Expect  SELECT d.item_code as stock_id, s.supp_name as supplier, abs(datediff(d.delivery_date, o.ord_date) ) as days, d.order_no as order_number FROM purch_order_details d, purch_orders o, suppliers s WHERE o.order_no = 'd.order_no'  and o.supplier_id = 's.supplier_id' order by d.item_code, s.supp_name
		var_dump( $this->sql );
		$this->clear_sql_vars();
	}
}
/**************!Testing*************/

?>
