<?php

require_once( 'Mail.php' );	//PEAR MAIL

class mailmessage
{
	var $from;
	var $to;
	var $subject;
	var $body;
	function __construct( $from, $to, $subject, $body )
	{
		$this->from = $from;
		$this->to = $to;
		$this->subject = $subject;
		$this->body = $body;
	}
}

class mailserver
{
	var $host;
	var $port;
	var $username;
	var $password;
	var $b_auth;
	function __construct( $host, $port, $b_auth, $username = "", $password = "")
	{
		$this->host = $host;
		$this->port = $port;
		$this->b_auth = $b_auth;
		$this->username = $username;
		$this->password = $password;
	}
}

class smtpmessage
{
	var $protocol;
	var $debug;
	function __construct()
	{
		$this->protocol = 'smtp'; //Mail::factory can accept mail, smtp, sendmail.
		$this->debug = false;
	}
	function sendmessage( $server, $message)
	{
		$smtp = Mail::Factory( $this->protocol,
				array( 'host' => $server->host,
					'port' => $server->port,
					'auth' => $server->b_auth,
					'username' => $server->username,
					'password' => $server->password,
					'debug' => $this->debug
				)
			);
		$mail = $smtp->send( $message->to, array(
							'from' => $message->from,
							'to' => $message->to,
							'subject' => $message->subject
						), $message->body );
		if( PEAR::isError( $mail ) )
		{
			echo( $mail->getMessage() );
		}
		else
		{
			echo( "Message sent successfully" );
		}
	}
}
