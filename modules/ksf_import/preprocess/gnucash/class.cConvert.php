<?php

/* Author kevin Fraser
*
*	Take GnuCash csv export files and conver to MultiImport csv.
*
*/

class cConvert
{
	function __construct( $cat )
	{
		global $conf;
		//echo $cat . "\n";
		$this->cat = $cat;
		if( isset( $hasAccessFA ) )
		{
			//Use FA to get Dimension Quick Lookup
		}
		else
		if( isset( $conf[$cat] ) )
		{
			$this->dim1 = $conf[$cat]["dim1"];
			$this->dim2 = $conf[$cat]["dim2"];
			$this->account = $conf[$cat]["account"];
		}
		else
		{
			$this->dim1 = "002/2018";
			$this->dim2 = "";
			$this->account = "1001";
		}
	}
	function setDim()
	{
	}
} 

?>
