<?php
/**
 * Epoch Cloud Management Platform
 * 
 * LICENSE
 * 
 * By viewing, using, or actively developing this application in any way, you are
 * henceforth bound the license agreement, and all of its changes, set forth by
 * ForwardFour Innovations. The license can be found, in its entirety, at this 
 * address: http://forwardfour.com/license.
 * 
 * @copyright  Copyright (c) 2011 and Onwards, ForwardFour Innovations
 * @license    http://forwardfour.com/license    [Proprietary/Closed Source]  
 */

/**
 * Create and maintain a log file of system errors.
 *
 * @category Core
 * @package core
 * @since v0.1 Dev
 */
 
class Logger {
/**
 * Get information about all of the plugins installed on this system
 *
 * @param      string      $message     The message to be included in the log entry
 * @param      string      $file        The path to the file which recorded the log
 * @param      string      $line        A number indicating the line number of the error
 * @return     boolean     An indicator as to whether or not writing the log entry was successful
 * @since      v0.1 Dev
 */
	public function __construct($message, $file, $line) {
	//The "$config" class was instantiated in "index.php"
		global $config;
		
	//Generate the data included in the log message
		$URL = PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
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
		
	//Return that this actions was successful
		return true;
	}
}
?>