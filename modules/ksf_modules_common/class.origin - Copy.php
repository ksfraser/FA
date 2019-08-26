<?php

$path_to_root="../..";


class origin
{
	var $config_values = array();   //What fields to be put on config screen
	var $tabs = array();
        var $help_context;
	var $tb_pref;

	function __construct()
	{
		$cu = $_SESSION['wa_current_user'];
		$compnum = $cu->company;
		$this->tb_pref = $db_connections[$compnum]['tbpref'];
	}
	function set_var( $var, $value )
	{
			$this->$var = $value ;
/*
		if(!empty($value) && is_string($value)) {
        		$this->$var = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
    		}
		else
		{
			$this->$var = $value ;
		}
*/
	}
	function get_var( $var )
	{
		return $this->$var;
	}
}
?>
