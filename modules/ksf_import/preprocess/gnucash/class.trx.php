<?php

/* Author kevin Fraser
*
*	Take GnuCash csv export files and conver to MultiImport csv.
*
*/

class trx_line
{
	var $TRX;		
	var $DATE;	
	var $REF;		
	var $account;	
	var $dim1;		
	var $dim2;		
	var $amount;	
	var $memo;
	var $out_fp;
	var $dimsize;
	function __construct( $fp = null, $dimsize = 2 )
	{
		$this->out_fp = $fp;
		$this->dimsize = $dimsize;
	}
	function write()
	{
		$outarray = array( $this->TRX,$this->DATE,$this->REF,$this->account,$this->dim1,$this->dim2,$this->amount,$this->memo );
		if( null != $this->out_fp )
			fputcsv( $this->out_fp, $outarray);
		return $outarray;
	}
	function check_dim( $dim1, $dim2 )
	{
		if( strlen( $this->dim1 ) < $this->dimsize AND strlen( $dim1 ) >= $this->dimsize)
			$this->dim1 = $dim1;
		if( strlen( $this->dim2 ) < $this->dimsize AND strlen( $dim2 ) >= $this->dimsize)
			$this->dim2 = $dim2;
	}
}
class trx
{
	var $line_array = array();
	var $fpo;
	var $dim1_array = array();	//For tracking contributions to Dimensions.
	var $dim2_array = array();	//Bank Accounts won't have dimensions set, so these will be used to choose what to add....
	var $trx;
	var $dim1;
	var $dim2;
	function __construct( $fpo = null, $trx)
	{
		$this->fpo = $fpo;
		$this->trx = $trx;
	}
	function add_line( $trx, $date, $ref, $account, $dim1, $dim2, $amount, $memo )
	{
		if( $trx <> $this->trx )
			throw new Exception( "entityID doesn't match.  Create new transaction class!", 1001 );
		$line = new trx_line( $this->fpo );
		$line->TRX = $trx;		
		$line->DATE = date( "m/d/Y", strtotime( $date ) );	//Need to format as M/D/Y 
		$line->REF = $ref;		
		$line->account = $account;	
		$line->dim1 = $dim1;		
		$line->dim2 = $dim2;		
		$line->amount = $amount;	
		$line->memo = $memo;
		$this->line_array[] = $line;

		if( isset( $this->dim1_array[$dim1] ) )
			$this->dim1_array[$dim1] .= $amount;
		else
			$this->dim1_array[$dim1] = $amount;

		if( isset( $this->dim2_array[$dim2] ) )
			$this->dim2_array[$dim2] .= $amount;
		else
			$this->dim2_array[$dim2] = $amount;
	}
	function sort( $dim )
	{
		$amount = 0;
		$dimr = "";
		foreach( $dim as $key => $value )
		{
			if( $value > $amount )
			{
				$amount = $value;
				$dimr = $key;
			}
		}
		return $dimr;
	}
	function write()
	{
		$this->dim1 = $this->sort( $this->dim1_array );
		$this->dim2 = $this->sort( $this->dim2_array );
		foreach( $this->line_array as $line )
		{
			$line->check_dim( $this->dim1, $this->dim2 );
			$line->write();
		}
	}
}
?>
