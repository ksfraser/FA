<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array
 * */

require_once( '../ksf_modules_common/class.table_interface.php' ); 
require_once( '../ksf_modules_common/class.generic_fa_interface.php' ); 
require_once( "Mail/Queue.php" );

/************************************************************************//**
 * Class for extension to give mail queueing to FrontAccounting
 *
 * See https://pear.php.net/manual/en/package.mail.mail-queue.mail-queue.tutorial.php
 *
 * *************************************************************************/
class ksf_pear_mail_queue extends generic_fa_interface {
	var $debug;
	var $table_interface;
	var $mail_queue_class;
	var $queue_db_options_type;
		// the others are the options for the used container
		// here are some for db
	var $queue_db_options_dsn;
	var $queue_db_options_mail_table;
		// here are the options for sending the messages themselves
		// these are the options needed for the Mail-Class, especially used for Mail::factory()
	var $queue_mail_options_driver;
	var $queue_mail_options_host;
	var $queue_mail_options_port;
	var $queue_mail_options_localhost;
	var $queue_mail_options_auth;
	var $queue_mail_options_username;
	var $queue_mail_options_password;
	private $db_options;
	private $mail_options;
	function __construct($pref_tablename)
	{
		parent::__construct( null, null, null, null, $pref_tablename );
		/*
		$this->config_values[] = array( 'pref_name' => 'lastoid', 'label' => 'Last Order Exported' );
		$this->config_values[] = array( 'pref_name' => 'debug', 'label' => 'Debug (0,1+)' );
		 */
		$this->config_values[] = array( 'pref_name' => 'queue_db_options_type', 'label' => 'Database Type( db, mdb, mdb2' );
		$this->config_values[] = array( 'pref_name' => 'queue_db_options_dsn', 'label' => 'DSN' );
		//$this->config_values[] = array( 'pref_name' => 'queue_db_options_mail_table', 'label' => '' ); //hardcoding
		$this->queue_db_options_mail_table = TB_PREF . "pear_mail_table";
		$this->config_values[] = array( 'pref_name' => 'queue_mail_options_driver', 'label' => 'Driver (SMTP/...' );
		$this->config_values[] = array( 'pref_name' => 'queue_mail_options_host', 'label' => 'Mail Server name/ip' );
		$this->config_values[] = array( 'pref_name' => 'queue_mail_options_port', 'label' => 'Mail Port (25/...)' );
		//$this->config_values[] = array( 'pref_name' => 'queue_mail_options_localhost', 'label' => '' );
		$this->queue_mail_options_localhost = 'localhost';
		$this->config_values[] = array( 'pref_name' => 'queue_mail_options_auth', 'label' => 'Use Mail Auth (true/false)' );
		$this->config_values[] = array( 'pref_name' => 'queue_mail_options_username', 'label' => 'Mail Auth username' );
		$this->config_values[] = array( 'pref_name' => 'queue_mail_options_password', 'label' => 'Mail Auth Password' );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'checkprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'action_show_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Pear Mail Queue Updated', 'action' => 'send_messages', 'form' => 'send_messages', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Send Pear Mail Queue', 'action' => 'form_PearMailQueue', 'form' => 'form_PearMailQueue', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Install Pear Mail Queue', 'action' => 'install', 'form' => 'install', 'hidden' => FALSE );
		//We could be looking for plugins here, adding menu's to the items.
		$this->add_submodules();
		$this->table_interface = new table_interface();
		$this->define_table();

		return;
	}
	function vars2PMQvar()
	{
		$this->db_options['type']       = $this->queue_db_options_type;
		$this->db_options['dsn']        = 'mysql://user:password@host/database';
		$this->db_options['mail_table'] = $this->queue_db_options_mail_table;
		
		$this->mail_options['driver']    = $this->queue_mail_options_driver;
		$this->mail_options['host']      = $this->queue_mail_options_host;
		$this->mail_options['port']      = $this->queue_mail_options_port;
		$this->mail_options['localhost'] = $this->queue_mail_options_localhost; //optional Mail_smtp parameter
		$this->mail_options['auth']      = $this->queue_mail_options_auth;
		$this->mail_options['username']  = $this->queue_mail_options_username;
		$this->mail_options['password']  = $this->queue_mail_options_password;
	}
	function add_message_to_queue( $from, $to, $subject, $message)
	{
		$this->vars2PMQvar();
		$this->mail_queue_class = & new Mail_Queue( $this->db_options, $this->mail_options );
		$headers = array( 	'From' => $from,
					'To' => $to,
					'Subject' => $subject
				);
		$mime = & new Mime_mail();
		$mime->setTXTBody( $message );
		$body = $mime->get();
		$headers = $mime->headers( $headers, true );
		$this->mail_queue_class->put( $from, $to, $headers, $body );

	}
	function send_messages( $max_at_once = "50" )
	{
		$this->vars2PMQvar();
		$this->mail_queue_class = & new Mail_Queue( $this->db_options, $this->mail_options );
		$this->mail_queue_class->sendMailsInQueue( $max_at_once );
	}
	function action_show_form()
	{
		$this->install();
		parent::action_show_form();
	}
	function install()
	{
		$this->table_interface->create_table();
		parent::install();
	}
	function define_table()
	{
		/*
		$this->table_interface->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->table_interface->fields_array[] = array('name' => 'stock_id', 'type' => 'varchar(32)' );
		$this->table_interface->fields_array[] = array('name' => 'instock', 'type' => 'int(11)' );
		 */
		$this->table_interface->fields_array[] = array('name' => 'id', 'type' => 'int(20)', 'null' => 'NULL', 'default' => '0' );
		$this->table_interface->fields_array[] = array('name' => 'create_time', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP');
		$this->table_interface->fields_array[] = array('name' => 'time_to_send', 'type' => 'datetime', 'null' => 'NOT NULL', 'default' => '0000-00-00 00:00:00');
		$this->table_interface->fields_array[] = array('name' => 'sent_time', 'type' => 'datetime', 'null' => 'NULL', 'default' => 'NULL');
		$this->table_interface->fields_array[] = array('name' => 'id_user', 'type' => 'int(20)', 'null' => 'NOT NULL', 'default' => '0' );
		$this->table_interface->fields_array[] = array('name' => 'ip', 'type' => 'varchar(32)', 'null' => 'NOT NULL', 'default' => 'unknown' );
		$this->table_interface->fields_array[] = array('name' => 'sender', 'type' => 'varchar(255)', 'null' => 'NOT NULL', 'default' => '' );
		$this->table_interface->fields_array[] = array('name' => 'recipient', 'type' => 'varchar(255)', 'null' => 'NOT NULL', 'default' => '' );
		$this->table_interface->fields_array[] = array('name' => 'headers', 'type' => 'varchar(255)', 'null' => 'NOT NULL', 'default' => '' );
		$this->table_interface->fields_array[] = array('name' => 'body', 'type' => 'blob', 'null' => 'NOT NULL', 'default' => '' );
		$this->table_interface->fields_array[] = array('name' => 'try_sent', 'type' => 'int(11)', 'null' => 'NOT NULL', 'default' => '0' );
		$this->table_interface->fields_array[] = array('name' => 'delete_after_send', 'type' => 'int(11)', 'null' => 'NOT NULL', 'default' => '1' );

		$this->table_interface->table_details['tablename'] = $this->company_prefix . "ksf_pear_mail_queue";
		$this->table_interface->table_details['primarykey'] = "id";
		$this->table_details['index'][0]['type'] = 'KEY';
		$this->table_details['index'][0]['columns'] = "id";
		$this->table_details['index'][0]['keyname'] = "id";
		$this->table_details['index'][1]['type'] = 'KEY';
		$this->table_details['index'][1]['columns'] = "time_to_send";
		$this->table_details['index'][1]['keyname'] = "time_to_send";
		$this->table_details['index'][2]['type'] = 'KEY';
		$this->table_details['index'][2]['columns'] = "id_user";
		$this->table_details['index'][2]['keyname'] = "id_user";
		/*
		$this->table_details['index'][0]['type'] = 'unique';
		$this->table_details['index'][0]['columns'] = "order_id,first_name,last_name,address_1,city,state";
		$this->table_details['index'][0]['keyname'] = "order-billing_address_customer";
		$this->table_details['index'][1]['type'] = 'unique';
		$this->table_details['index'][1]['columns'] = "customer_id,first_name,last_name,address_1,city,state";
		$this->table_details['index'][1]['keyname'] = "customer-billing_address_customer";
		 */

		/*
		 */
	}
	function form_PearMailQueue()
	{
		$this->call_table( 'send_messages', "Send Pear Mail Queue" );
	}

}

?>
