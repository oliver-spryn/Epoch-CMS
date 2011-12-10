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

//Define the configuration class
	class Config {
	//Database connection configuration
		public $dbType = "mysqli";
		public $dbHost = "localhost";
		public $dbPort = "3306";
		public $dbUserName = "spryno724";
		public $dbPassword = "epoch2011";
		public $dbName = "epoch";
		
	/* Installation directory configuration
	 * "$installDomain" is the domain name (without the http://www) followed by the installation path.
	 * "$installRoot" is the installation path relative the root of the server.
	*/
		public $installDomain = "localhost/epoch/";
		public $installRoot = "/Web Development/wamp/www/epoch/";
		public $CDNRoot = "ffstatic-cdn1.appspot.com/";
		
	//Security settings configuration
		public $folderPermissions = "0777";
		public $encryptedSalt = "%(*&NSJ(&jd&81245";
		public $sessionSuffix = "HJF789HF6";
		public $debugMode = false;
	}
?>