<?php
final class Log {
	private $filename;
	private $debugMode;
	
	public function __construct($filename, $debugMode) {
		$this->filename = $filename;
		$this->debugMode = $debugMode;
	}
	
	public function write($message) {
		
		if ($this->debugMode) {
			$file = $this->filename;
			
			$handle = fopen($file, 'a+'); 
			
			fwrite($handle, date('Y-m-d G:i:s') . ' - ' . $message . "\n");
				
			fclose($handle);
		} 
	}
}
?>