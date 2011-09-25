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
 * @category   Core
 * @copyright  Copyright (c) 2011 and Onwards, ForwardFour Innovations
 * @license    http://forwardfour.com/license    [Proprietary/Closed Source]  
 */

/*
 * This script is the super core of the system, which prepares the values from the
 * configuration script for use within the system. Here is an overview of this
 * relatively simple script:
 *  [1] The server checks to see if at least PHP 5 is running, then defines several
 *      constants which will make the major and minor versions avaliable to the
 *      system.
 *  [2] Check to see if the configuration script exists, and display a message if
 *  	it does not.
 *  [3] Instantiate the "Config" class for use through out this script and system.
 *  [4] Windows directory paths use a backslash. However, all other operating systems
 *      use a forward slash. This step uses the configuration file to set whether or
 *      slashes should be forward or back. 
 *  [5] Define several constants which will define local and CDN-based URLs for system-
 *  	wide use.
 *  [6] Include the essential classes within the system's core.
 *  [7] Start a session.
 *  [8] Set several several configurations which will boost performance, improve
 *  	security, and allow certain actions.
 *  [9] Create a function which be used to import additional classes and packages into
 *  	a script for parsing, using ECMAScript standards.
 */

//This system requires a minimum of PHP 5, so ensure that this condition is true before doing else anything!
	$PHPVersionInfo = phpversion();
	!defined("PHP_MAJOR_VERSION") ? define("PHP_MAJOR_VERSION", current(explode(".", $PHPVersionInfo))) : NULL;
	!defined("PHP_MINOR_VERSION") ? define("PHP_MINOR_VERSION", $PHPVersionInfo) : NULL;
	PHP_MAJOR_VERSION < 5 ? die("Please install PHP 5 or greater in order to use this application.") : NULL;

/*
 * This is the only script which does not use the "$installRoot" instance variable from the "Config" class to include necessary files from unknown directories.
 * The "$installRoot" instaince variable will be made avaliable to all other other PHP scripts once they have included "index.php".
*/
	strstr(dirname(__FILE__), "\\") ? $configScript = str_replace("system\server", "", dirname(__FILE__)) . "data\system\config.php" : $configScript = str_replace("system/server", "", dirname(__FILE__)) . "data/system/config.php";
	require_once($configScript);
	
//Instantiate the "Config" class
	$config = new Config();
	
//Detirmine the root address for the entire site, and include the "http://" if SSL is not active and "https://" if SSL is active
	$_SERVER['HTTPS'] == "on" ? define("PROTOCOL", "https://") : define("PROTOCOL", "http://");
	defined("ROOT") ? NULL : define("ROOT", PROTOCOL . $config->installDomain);
	defined("STRIPPED_ROOT") ? NULL : define("STRIPPED_ROOT", $config->installDomain);
	
//Include the rest of the system's core. The order of the files in the "$include" array are important! Do not rearrange the order!
	$include = array("core/logger.php", "core/message.php", "core/database.php");
	
	foreach($include as $script) {
		require_once($config->installRoot . "system/server/" . $script);
	}
	
//Start the session
	session_save_path($config->installRoot . "data/system/sessions");
	session_name("EPOCH_" . $config->sessionSuffix);
	session_start();
	
//Set server configurations
	set_time_limit(3600);
	ini_set("expose_php", "Off");
	
	/*---------------------------------------------------- Developer use ONLY!!!! Disable during production!!!! ----------------------------------------------------*/
	error_reporting(-1);
?>