<?php
class Conf {

	var $smtphost;
	var $dbhost;
	var $dbport;
	var $dbname;
	var $dbuser;
	var $version;

	function Conf() {

		$this->dbhost	= '127.0.0.1';
		$this->dbport 	= '3306';
		if(defined('ENVIRNOMENT') && ENVIRNOMENT == 'test'){
		$this->dbname    = 'test_bitnami_orangehrm';		
		}else {
		$this->dbname    = 'bitnami_orangehrm';
		}
		$this->dbuser    = 'bn_orangehrm';
		$this->dbpass	= 'bf1f6f568e';
		$this->version = '3.3.2';

		$this->emailConfiguration = dirname(__FILE__).'/mailConf.php';
		$this->errorLog =  realpath(dirname(__FILE__).'/../logs/').'/';
	}
}
?>