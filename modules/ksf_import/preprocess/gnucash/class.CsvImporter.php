<?php

/* Author kevin Fraser
*
*	Take GnuCash csv export files and conver to MultiImport csv.
*
*/

class CsvImporter 
{ 
	/**********************//**
	*	Author imyrddin at myrddin dot myrddin
	*	https://www.php.net/manual/en/function.fgetcsv.php
	****************************/
    private $fp; 
    private $parse_header; 
    private $header; 
    private $delimiter; 
    private $length; 
    //-------------------------------------------------------------------- 
    function __construct($file_name, $parse_header=false, $delimiter=",", $length=8000, $quote='"', $escape="\\") 
    { 
	if ( ($this->fp = fopen( $file_name, "r") ) === FALSE) 
		throw new Exception( "File Open Failed" );
        $this->parse_header = $parse_header; 
        $this->delimiter = $delimiter; 
        $this->length = $length; 
        //$this->lines = $lines; 

        if ($this->parse_header) 
        { 
           $this->header = fgetcsv($this->fp, $this->length, $this->delimiter, $quote, $escape); 
        } 

    } 
    //-------------------------------------------------------------------- 
    function __destruct() 
    { 
        if ($this->fp) 
        { 
            fclose($this->fp); 
        } 
    } 
    //-------------------------------------------------------------------- 
    function get($max_lines=0) 
    { 
        //if $max_lines is set to 0, then get all the data 

        $data = array(); 

        if ($max_lines > 0) 
            $line_count = 0; 
        else 
            $line_count = -1; // so loop limit is ignored 

        while ($line_count < $max_lines && ($row = fgetcsv($this->fp, $this->length, $this->delimiter)) !== FALSE) 
        { 
		//var_dump( $row );
            if ($this->parse_header) 
            { 
		$row_new = array();
                foreach ($this->header as $i => $heading_i) 
                { 
			if( isset( $row[$i] ) )
                    		$row_new[$heading_i] = $row[$i]; 
                } 
                $data[] = $row_new; 
		unset( $row_new );
            } 
            else 
            { 
                $data[] = $row; 
            } 

            if ($max_lines > 0) 
                $line_count++; 
        } 
        return $data; 
    } 
    //-------------------------------------------------------------------- 

}
?>
