<?php

require_once( 'class.generic_customers.php' ); 


class vtiger_customers extends generic_customers
{
	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		$this->customer_index_name = "contactid";
		$this->customer_table_name = "vtiger_contactdetails";
		$this->datasource_name = "VTIGER";
	}
	function import_customers()
	{
		$inserted = array();
		$ignored = array();
		$failed = array();
		if( !isset( $this->db_connection ) )
			$this->connect_db();	//connect to DB setting db_connection used below.

            $sql = "SELECT d.contactid as contactID, d.contact_no as contactNum, d.accountid as organizationID, d.salutation as salutation, d.firstname as firstname, d.lastname as lastname, d.email as email, d.phone as phone, d.mobile as phone2, d.fax as fax, a.mailingcity as city, a.mailingstreet as street, a.mailingcountry as country, a.mailingstate as state FROM fhs.vtiger_contactdetails d, fhs.vtiger_contactaddress a WHERE d.contactid  >= $this->min_cid " . /*AND d.contactid <= $this->max_cid*/ "and d.contactid=a.contactaddressid";
            $customers = mysql_query($sql, $this->db_connection);
            display_notification("Found " . db_num_rows($customers) . " new customers");
            $i = $j = $k = 0;
            while ($cust = mysql_fetch_assoc($customers)) {
                $this->facust->set_var( 'name', $cust['firstname'] . ' ' . $cust['lastname'] );
                $this->facust->set_var( 'cust_ref', $cust['firstname'] . ' ' . $cust['lastname'] );
                $this->facust->set_var( 'contact', $cust['firstname'] . ' ' . $cust['lastname'] );
                $this->facust->set_var( 'address', $cust['street'] . "\n\r" . $cust['city'] . "\n\r" . $cust['state'] . "\n\r" . $cust['country'] . "\n\r" );
                $this->facust->set_var( 'tax_id', '' );
                $this->facust->set_var( 'phone', $cust['phone'] );
                $this->facust->set_var( 'phone2', $cust['phone2'] );
                $this->facust->set_var( 'fax', $cust['fax'] );
                $this->facust->set_var( 'email', $cust['email'] );
                $this->facust->set_var( 'area_code', substr( $cust['phone'], 0, 3) );
                $this->facust->set_var( 'curr_code', 'CAD' );
                $this->facust->set_var( 'salesman', '1' );
                $this->facust->set_var( 'tax_group_id', '3' );
                $this->facust->set_var( 'tax_id', '' );
                $this->facust->set_var( 'fulfill_from_location', '1' );
                $this->facust->set_var( 'ship_via', '1' );
                $this->facust->set_var( 'dimension1', '' );
                $this->facust->set_var( 'dimension2', '' );
                $this->facust->set_var( 'credit_status', '1' );
                $this->facust->set_var( 'payment_terms', '5' );
                $this->facust->set_var( 'credit_limit', "1000.00" );
                $this->facust->set_var( 'payment_discount', "0.00" );
                $this->facust->set_var( 'sales_type', $_POST['sales_type'] );

		$ret = $this->facust->insert_customer();

		if( $ret == 1 )
		{
			//success
			$this->set_pref( "lastcid", $cust["contactID"] );
			$inserted[] = $this->facust->get_var('name');
                    	$i++;
		}
		else if ($ret == 0)
		{
			//duplicate
                    	$j++;	//customer ignored (duplicate)
			$ignored[] = $this->facust->get_var('name');
		}
		else if( $ret == -1 )
		{
			//error
			$k++;	//insert failed
			$failed[] = $this->facust->get_var('name');
		}
            }
            display_notification("$i customer posts created, $j customer posts ignored, $k customer inserts failed.");
		foreach( $inserted as $name )
		{
            		display_notification("Inserted: " . $name);
		}
		foreach( $ignored as $name )
		{
            		display_notification("Ignored: " . $name);
		}
		foreach( $failed as $name )
		{
            		display_notification("Failed: " . $name);
		}
	}
}
?>
