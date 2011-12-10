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
 * Create a connection to the local database, and provide all of the necessary
 * methods to safely create, read, update, and delete data from the database.
 *
 * @category Core
 * @package core
 * @since v0.1 Dev
 */
 
class Database {
/**
 * Create a private instance of the Message class, to display any errors that 
 * occur during database interactions.
 *
 * @access     private
 * @var        Message
 */
	private $message;
	private $connection;
	
	function __construct() {
	//The "$config" class was instantiated in "index.php"
		global $config;
		
	//Instantiate the Message class
		$this->message = new Message(true);
		
	//Check to see if the server has the MySQLi extension installed, and use it if the configuration is set to do so
		if ($config->dbType == strtolower("mysqli") && class_exists("mysqli")) {
		//Try connecting to the database server and selecting the database, using MySQLi
			$this->connection = new mysqli($config->dbHost, $config->dbUserName, $config->dbPassword, $config->dbName, $config->dbPort);
			
		//Check to see if the connection and database selection was successful
			if ($this->connection->connect_error) {
				$this->message->error("<strong>Fatal error:</strong> The system could not connect to the database server or the database could not be found. Please ensure that your database login credentials are correct, that the server is not offline, and that your database name is correct.\n<br /><br />\n" . $this->connection->connect_error);
				exit;
			}
	//Fall back to the standard MySQL connection methods
		} else {
		//Try connecting to the database server, using the MySQL connection
			$this->connection = mysql_connect($config->dbHost . ":" . $config->dbPort, $config->dbUserName, $config->dbPassword);
			
		//Check to see if the connection was successful
			if (!$this->connection) {
				$this->message->error("<strong>Fatal error:</strong> The system could not connect to the database server. Please ensure that your database login credentials are correct, and that the server is not offline.\n<br /><br />\n" . mysql_error());
				exit;
			}
			
		//Try selecting the database
			$dbSelect = mysql_select_db($config->dbName, $this->connection);
			
		//Check to see if the selection was successful
			if (!$dbSelect) {
				$this->message->error("<strong>Fatal error:</strong> The system could not select the database. Please ensure that your database name is correct.\n<br /><br />\n" . mysql_error());
				exit;
			}
		}
	}
	
//Clean-up stored database values
	public function prepare(string $input, boolean $htmlEncode = NULL, boolean $stripSlashes = NULL) {
		global $message;
		
		if ($stripSlashes == true) {
		//Strip the slashes and return the html entities of a string
			if ($htmlEncode == true) {
				return htmlentities(stripslashes($input));
		//Only strip the slashes of the string, DEFAULT BEHAVIOR
			} else {
				return stripslashes($input);
			}
		} else {
		//Only return the html entities of a string
			if ($htmlEncode == true) {
				return htmlentities($input);
			} else {
				$error = debug_backtrace();
				$message->error("<strong>Warning:</strong> No action was performed on the returned database value on line " .  $error['0']['line']);
			}
		}
	}
	
/*
 * This method completes the following tasks:
 *  - run a "mysql_fetch_array()" command when the query selects all columns, or "mysql_fetch_assoc" when only a few columns are selected
 *  - prepare images and URLs for SSL support
 *  - clean-up escaped database values
*/
	public function fetch($value, $fetchType = "array") {
		if ($fetchType == "array") {
			$result = mysql_fetch_array($value);
		} else {
			$result = mysql_fetch_assoc($value);
		}
		
		if ($result) {
			$return = array();
			
			foreach ($result as $key => $value) {
				if (PROTOCOL == "https://") {	
					$return[$key] = str_replace(str_replace("https://", "http://", ROOT), ROOT, $this->prepare($value));
				} else {
					$return[$key] = $this->prepare($value);
				}
			}
			
			return $return;
		} else {
			return false;
		}
	}
	
/*
 * This method completes the following tasks:
 *  - run a "mysql_query()" command
 *  - ensure the query was run successfully
 *  - fetch an array (using the fetch() method), return a raw resource, count the number of returned values, or return an array when the given query selects only a only a few specific columns (again, using the fetch() method)
*/
	public function query($query, $returnType = false, $showError = true) {
		$action = mysql_query($query, $this->connection);
		
	//If no value was returned from the query
		if (!$action) {
		//If allowed to display an error
			if ($showError == true) {
				$error = debug_backtrace();
				$this->message->error("<strong>Warning:</strong> There is an error with your query: \n" . $query . "\n<br /><br />\n" . mysql_error() . "\n<br /><br />\nError on line: " . $error['0']['line'] . "<br />\nError in file: " . $error['0']['file']);
			} else {
				return false;
			}
		} else {
		//If the following words are used inside of the query, then a command was executed on the database, and no value needs to be returned.
			if (!strstr($query, "INSERT INTO") && !strstr($query, "UPDATE") && !strstr($query, "SET") && !strstr($query, "CREATE TABLE") && !strstr($query, "ALTER TABLE") && !strstr($query, "DROP TABLE")) {
				switch($returnType) {
				//Fetch an array, and clean-up each value for display, DEFAULT BEHAVIOR
					case false : 
					case "array" : 
						if ($result = fetch($action)) {
							//Do nothing, the array was sucessfully extracted
						} else {
							if ($showError == true) {
								$error = debug_backtrace();
								$this->message->error("<strong>Warning:</strong> There is an error with your query: \n" . $query . "\n<br /><br />\nError on line: " . $error['0']['line'] . "<br />\nError in file: " . $error['0']['file']);
							}
						}
						
						if (is_array($result) && !empty($result)) {
							return $result;
						} else {
							return false;
						}
						
						break;
						
				//Return the raw resource
					case "raw" : 
						return $action;
						break;
							
				//Return the number of rows
					case "num" : 
						$result = mysql_num_rows($action);
						return $result;
						break;
						
				//Fetch the array, where only certain rows are selected
					case "selected" : 
						if ($result = fetch($action, "assoc")) {
							//Do nothing, the array was sucessfully extracted
						} else {
							if ($showError == true) {
								$error = debug_backtrace();
								$this->message->error("<strong>Warning:</strong> There is an error with your query: \n" . $query . "\n<br /><br />\nError on line: " . $error['0']['line'] . "<br />\nError in file: " . $error['0']['file']);
							}
						}
						
						if (is_array($result) && !empty($result)) {
							return $result;
						} else {
							return false;
						}
						
						break;
						
				//Return an error if an unsupported return-type is requested
					default : 
						$error = debug_backtrace();
						$this->message->error("<strong>Warning:</strong> An invalid query return-type was requested on line " .  $error['0']['line']);
						break;
				}
			}
		}
	}
}
	
//Instantiate the "Database" class to allow the system easily communicate with the database.
	$database = new Database();
	$db = $database;