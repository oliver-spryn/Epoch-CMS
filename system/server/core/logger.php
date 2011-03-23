<?php
/*
LICENSE: http://docs.forwardfour.com/index.php/License

Package: System Core
Dependencies: config.php
Known issues: None

Create and maintain a log file of system errors.
*/
	
	class Logger {
		function __construct(string $message, string $file, string $line) {
		//The "$config" class was instantiated in "index.php"
			global $config;
			
		//Generate the data included in the log message
			$URL = ROOT . ltrim($_SERVER['REQUEST_URI'], "/");
			$date = date("l, F j, Y, g:i a T");
			
		//Open the log file for write-only access
			$logOpen = fopen($config->installRoot . "data/system/errors.log", "a");
			
		//Write to the log file
			fwrite($logOpen, "

----------------------------------------------------------------------------------
URL: " . $URL . "
Date: " . $date . "
File path: " . $file . "
Near line: " . $line . "

" . strip_tags($message));
		
		//Close the log file
			fclose($logOpen);
		}
	}
?>