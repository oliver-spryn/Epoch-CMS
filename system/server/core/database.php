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
 * methods to safely create, read, update, and delete data from the database
 *
 * @category Core
 * @package core
 * @since v0.1 Dev
 */
 
class Database {
/**
 * Create a private instance of the Message class, to display any errors that 
 * occur during database interactions
 *
 * @access     private
 * @var        Message
 */
	private $message;
	
/**
 * This is a reference to the mysqli class which will formally connect to the
 * MySQL server
 *
 * @access     private
 * @var        mysqli
 */
	private $connection;
	
/**
 * Create a connection to the MySQL server
 * 
 * Precondition:  a MySQL server must be avaliable to the system
 * Postcondition: PHP will create a connection to the MySQL server
 *
 * @access     public
 * @return     void
 * @since      v0.1 Dev
 */
	public function __construct() {
	//The "$config" class was instantiated in "index.php"
		global $config;
		
	//Instantiate the Message class
		$this->message = new Message(true);
		
	//Try connecting to the database server and selecting the database, using MySQLi
		try {
		//Instantiate the "mysqli" class and connect to the database
			$this->connection = new mysqli($config->dbHost, $config->dbUserName, $config->dbPassword, $config->dbName, $config->dbPort);
			
		//Check to see if the connection and database selection was successful
			if ($this->connection->connect_error) {
				throw new Exception("<strong>Fatal error:</strong> The system could not connect to the database server. Please ensure that your database login credentials are correct, and that the server is not offline.
<br /><br />
[Error code] " . $this->connection->connect_errno . "
<br />
[Error message] " . $this->connection->connect_error);
			}
		} catch (Exception $e) {
			$this->message->error($e->getMessage());
			exit;
		}
	}
	
/**
 * The destructor method which will close out a connection to the MySQL server
 * at the end of each page
 *
 * Precondition:  the Database class must have been instantiated, and a connection
 *                to a MySQL server must have been present
 * Postcondition: PHP will close the connection to the MySQL server and destroy the 
 *                Database classs such that no further queries may be executed on
 *                the database
 * 
 * @access     public
 * @return     void
 * @since      v0.1 Dev
 */
	public function __destruct() {
		$this->connection->close();
	}
	
/**
 * The most basic way to run an SQL query on the database, without getting the 
 * result back
 *
 * Precondition:  a connection to a MySQL server must have have been made
 * Postcondition: a free-form query will be executed on the database
 *
 * @param      string      $query     The SQL query to execute
 * @access     public
 * @return     resource    $result    The resource returned from the MySQLi query() method
 * @since      v0.1 Dev
 */
	public function query($query) {
	//Run a query on the database an make sure that it is executed successfully
		try {
		//Escape the SQL
			$safeQuery = $this->escape($query);
			
			if ($result = $this->connection->query($safeQuery)) {
				return $result;
			} else {
				$error = debug_backtrace();
				
				throw new Exception("<strong>Warning:</strong> There is an error with your query:
<br /><br />
<strong>[Query]</strong> " . $query . "
<br />
<strong>[MySQL Error]</strong> " . $this->connection->error . "
<br />
<strong>[Error on line]</strong> " . $error['0']['line'] . "
<br />
<strong>[Error in file]</strong> " . $error['0']['file']);
			}
	//Catch the error and close any existing connections to the MySQL server
		} catch (Exception $e) {
			$this->connection->close();
			
			die($e->getMessage());
		}
	}
	
/**
 * Escape an SQL query for safe storage in the database
 *
 * Precondition:  <None>
 * Postcondition: all non-safe SQL characters will be escaped for safe storage in
 *                the database
 *
 * @param      string      $input     The SQL query to escape
 * @access     public
 * @return     void
 * @since      v0.1 Dev
 */
	public function escape($input) {
		return $this->connection->real_escape_string($input);
	}
	
/**
 * Return the primary key generated from last from the last INSERT
 * SQL statement
 * 
 * Precondition:  a connection to a MySQL server must have have been made and an
 *                INSERT query must have been executed
 * Postcondition: the primary key generated from last from the last INSERT
 *                SQL statement will be returned
 * 
 * @access     public
 * @return     mixed
 * @since      v0.1 Dev
 */
	public function insertID() {
		return $this->connection->insert_id;
	}
	
/**
 * Un-escape and clean-up escaped database entries for display
 * 
 * Precondition:  <None>
 * Postcondition: the SQL-safe entries that were extracted from the database
 *                will be cleaned-up for display
 *
 * @param      string      $input        The string value to escape
 * @param      boolean     $htmlEncode   Whether or not to HTML encode the output
 * @param      boolean     $stripSlashes Whether or not the strip the slashes from escaped values
 * @access     public
 * @return     string
 * @since      v0.1 Dev
 */
	private function prepare($input, $htmlEncode = false, $stripSlashes = true) {		
		$stripSlashes == true ? $input = stripslashes($input) : false;
		$htmlEncode == true ? $input = htmlentities($input) : false;
		
		return $input;
	}
	
/**
 * Fetch the result of a database query and clean-up all of the values for
 * display
 * 
 * Precondition:  $this->query() must be have been successfully ran and returned
 *                a resource
 * Postcondition: an array of values will be extracted from the resource that was 
 *                returned by $this->query() 
 *
 * @param      resource    $result    The resource to convert to an array
 * @param      string      $fetchType Determine how the resource should be fetched
 * @access     public
 * @return     mixed
 * @since      v0.1 Dev
 */
	public function fetch($result, $fetchType = MYSQLI_ASSOC) {
	//Fetch the array
		$result = $result->fetch_array($fetchType);
		
		if ($result && is_array($result)) {
		/*
		 * The loop below has several purposes. It will:
		 *  - replace all non-secure links within this site, such as images and URLs, with secure links, if the current
		      page is encrypted
		 *  - clean-up escaped values from the database
		*/
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
	
/**
 * Return the number of rows that was fetched from the given query
 *
 * Precondition:  $this->query() must be have been successfully ran and returned
 *                a resource
 * Postcondition: the number of rows that was fetched from the given query will be
 *                returned
 *
 * @param      resource    $result    The resource from the database on which to perform a numrical analysis
 * @access     public
 * @return     int
 * @since      v0.1 Dev
 */
	public function num($result) {
		return $result->num_rows;
	}
	
/**
 * Run a basic query on the database, and return the result
 *
 * Precondition:  a connection to a MySQL server must have have been made
 * Postcondition: a query will be executed on the database and the results
 *                will be gathered
 *
 * @param      resource    $query     The resource to perform a numrical analysis on
 * @param      string      $fetchType Determine how the resource should be fetched
 * @access     public
 * @return     mixed
 * @since      v0.1 Dev
 */
	public function quick($query, $fetchType = MYSQLI_BOTH) {
		$result = $this->query($query);
		return $this->fetch($result, $fetchType);
	}
	
	
/**
 * Read the last value from a positioning column in a table, and return the
 * position value that will occur next
 *
 * Precondition:  a connection to a MySQL server must have have been made
 * Postcondition: the position value that will occur next in this table will
 *                be returned
 *
 * @param      string      $table     The table to read the "position" value from
 * @param      string      $column    The column representing the position in the above mentioned table
 * @access     public
 * @return     int
 * @since      v0.1 Dev
 */
	public function nextPosition($table, $column = "position") {
		$position = $this->quick("SELECT * FROM `" . $table . "` ORDER BY `" . $column . "` DESC LIMIT 1");
		return $position[$column] + 1;
	}
	
/*
 * The methods beyond this point are highly specialized to perform specific types of
 * queries on a database. The above "query()" method's purpose is more general, and
 * is best suited for queries like: "DROP TABLE `mytable`". The methods below are
 * accustomed to handling more complex input, such as unknown mixture of strings and
 * arrays, and parsing them into a query which is completly safe to create, read,
 * update, or delete entries, with minimial effort for future use. These methods do
 * use the "query()" method when they are ready to execute their query.
*/

/**
 * A generalized method for Reading, Updating, and Deleting (hence, RUD) database
 * entries. This method will be called by more specific methods that are defined
 * below. 
 *
 * Precondition:  a connection to a MySQL server must have have been made
 * Postcondition: an input value will be parsed and converted into an SQL query
 *                which will read, update, or delete a value from the database
 *
 * @param      array       $input     An array strings and arrays which will be converted into SQL to execute
 * @access     private
 * @return     resource
 * @since      v0.1 Dev
 */
	private function RUDBase($input) {
		$query = "";
		
	//Parse the input in this loop
		foreach($input as $argument) {
		//Strings are simple to parse!
			if (is_string($argument)) {
			//Trim any whitespace before appending this string to the query
				$query .= trim($argument) . " ";
			}
			
		//Arrays require more logic
			if (is_array($argument)) {
				$values = "";
				
				foreach($argument as $key => $value) {
					$values .= "`" . $key . "` = '" . $this->escape($value) . "', ";
				}
				
				$query .= rtrim($values, ", ") . " ";
			}
		}
		
	//Finally run the parsed query
		return $this->query(rtrim($query));
	}
	
/**
 * A specialized method for inserting entries into a database, not for modifying a
 * database or table structure. Here is an example of how to run this method:
 * 
 * $db->insert("INSERT INTO `table` WHERE",
 *              array("name" => "John Smith",
 *                    "age" => "50",
 *                    "hometown" => "New York"
 *              ));
 *
 * These values, even if they are complex PHP arrays, will be automatically escaped and safely converted into:
 * INSERT INTO `table` WHERE (`name`, `age`, `hometown`) VALUES ('John Smith', '50', 'New York')
 *
 * Precondition:  a connection to a MySQL server must have have been made
 * Postcondition: a complex mixed will be converted into an SQL query and executed on
 *                the database
 *
 * @param      mixed       <undefined> An unlimited series of strings and arrays which will be converted into SQL to execute
 * @access     public
 * @return     boolean
 * @since      v0.1 Dev
 */
	public function insert() {
		$query = "";
		$insertArrayParsed = false;
		
	//Since there is an unknown number of values, then grab all of the supplied arguments...
		$arguments = func_get_args();
		
	// ... and parse them in this loop
		foreach($arguments as $argument) {
		//Strings are simple to parse!
			if (is_string($argument)) {
			//Trim any whitespace before appending this string to the query
				$query .= trim($argument) . " ";
		//Arrays require more logic
			} else {			
			/*
			 * If there are multiple arrays in the supplied arguments, then the *first* array will contain the values to be inserted,
			 * according to SQL standard conventions. Before parsing the array into the query, check to see if the "$firstArrayParsed"
			 * variable is "true" and process it accordingly.
			*/
				try {
					$keys = "";
					$values = "";
					
				//Has the array containing the INSERT values been parsed already?
					if (!$insertArrayParsed) {
						$insertArrayParsed = true;
					} else {
						throw new Exception("The INSERT portion has been parsed");
					}
						
					foreach($argument as $key => $value) {
						$keys .= "`" . $key . "`, ";
						
					//When converting native PHP arrays into an SQL safe statemtent, use json_encode() which is tad faster than serialize()
						$values .= is_array($value) ? "'" . $this->escape(json_encode($value)) . "', " : "'" . $this->escape($value) . "', ";
					}
					
					$query .= "( " . rtrim($keys, ", ") . " ) VALUES ( " . trim($values, ", ") . ") ";
				} catch (Exception $e) {
					$values = "";
					
					foreach($argument as $key => $value) {
						$values .= "`" . $key . "` = '" . $this->escape($value) . "', ";
					}
					
					$query .= rtrim($values, ", ") . " ";
				}
			}
		}
		
	//Finally run the parsed query
		return $this->query(rtrim($query)) ? true : false;
	}
	
/**
 * A specialized method for reading database values. Here is an example of how to run
 * this method:
 * 
 * $db->read("SELECT * FROM `table` WHERE,
 *            array("name" => "John Smith",
 *                  "age" => "50",
 *                  "hometown" => "New York"
 *            ));
 *
 * These values, even if they are complex PHP arrays, will be automatically escaped and safely converted into:
 * SELECT * FROM `table` WHERE `name` = 'John Smith' AND `age` = '50' AND `hometown` = 'New York'
 *
 * Precondition:  a connection to a MySQL server must have have been made
 * Postcondition: a complex input will be converted into an SQL query and executed on
 *                the database
 *
 * @param      mixed       <undefined> An unlimited series of strings and arrays which will be converted into SQL to execute
 * @access     public
 * @return     resource
 * @since      v0.1 Dev
 */
	public function read() {
	//Since there is an unknown number of values, then grab all of the supplied arguments...
		$arguments = func_get_args();
		
	// ... and execute them in the base method
		$result = $this->RUDBase($arguments);
		
	//Finally return the result
		return $this->escape($result);
	}
	
/**
 * A specialized method for updating database values, not for modifying a database
 * or table structure itself. Here is an example of how to run this method:
 * 
 * $db->update("UPDATE `table` SET,
 *              array("name" => "John Smith",
 *                    "age" => "50",
 *                    "hometown" => "New York"
 *              ),
 *             "WHERE",
 *              array("id" => "1",
 *                    "phoneNumber" => "18005555555"
 *              ));
 *
 * These values, even if they are complex PHP arrays, will be automatically escaped and safely converted into:
 * UPDATE `table` SET `name` = 'John Smith', `age` = '50', `hometown` = 'New York' WHERE `id` = '1' AND `phoneNumber` = '18005555555'
 *
 * Precondition:  a connection to a MySQL server must have have been made
 * Postcondition: a complex input will be converted into an SQL query and executed on
 *                the database
 *
 * @param      mixed       <undefined> An unlimited series of strings and arrays which will be converted into SQL to execute
 * @access     public
 * @return     boolean
 * @since      v0.1 Dev
 */
	public function update() {
	//Since there is an unknown number of values, then grab all of the supplied arguments...
		$arguments = func_get_args();
		
	// ... and execute them in the base method
		return $this->RUDBase($arguments) ? true : false;
	}
	
/**
 * A specialized method for deleting database values, not for modifying a database
 * or table structure
 *
 * @param      mixed       <undefined> An unlimited series of strings and arrays which will be converted into SQL to execute
 * @access     public
 * @return     boolean
 * @since      v0.1 Dev
 */
	public function delete($table, $idNumber, $reorder = true) {
	//Grab the position of the item scheduled for deletion and reorder the position of the items after the deleted one, if required
		if ($reorder) {
			$positionGrabber = $this->quick("SELECT * FROM `" . Validate::required($table) . "` WHERE `id` = '" . Validate::numeric($idNumber, 1) . "'");
			
			if (is_numeric($positionGrabber['position'])) {
				$this->query("UPDATE `" . $table . "` SET `position` = position - 1 WHERE `position` > '" . $positionGrabber['position'] . "'");
			} else {
				die("The ID of the entry you are trying to delete does not exist.");
			}
		}
		
	//Build the string/array combination and execute the query
		return $this->query(array("DELETE FROM `" . Validate::required($table) . "` WHERE `id` = '" . Validate::numeric($idNumber, 1) . "'")) ? true : false;
	}
	
/**
 * A specialized method for deleting database values, not for modifying a database
 * or table structure
 *
 * @param      resource    $query     The SQL query to evaluate
 * @access     public
 * @return     boolean
 * @since      v0.1 Dev
 */
	public function exist($query) {
		$query = $this->query($query);
	
		return $query->num_rows > 0 ? true : false;
	}
}
	
//Instantiate the "Database" class to allow the system easily communicate with the database.
	$database = new Database();
	$db &= $database;
?>