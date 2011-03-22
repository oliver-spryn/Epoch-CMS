<?php
/*
LICENSE: http://docs.forwardfour.com/index.php/License

Package: System Core
Dependencies: None
Known issues: None

This script is created during the automated setup process, and contains the core configuration and definitions of the system, which will be used globally.
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
		public $installRoot = "/xampp/xampp/htdocs/epoch/";
		
	//Security settings configuration
		public $folderPermissions = "0777";
		public $encryptedSalt = "%(*&NSJ(&jd&81245";
		public $sessionSuffix = "HJF789HF6";
		public $debugMode = false;
	}
?>