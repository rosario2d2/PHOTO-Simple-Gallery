<?php

/*
* PHOTO Simple Gallery
* Copyright (C) 2015 Rosario Prestigiacomo
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(LIB_PATH.DS."config.php");

class MySQLDatabase { 
    
    public $mysqli;
    public $last_query;

    // The connection is open automatically as soon we create the object
    function __construct() {
        $this->open_connection();
    }
   
    // Open Database connection 
    public function open_connection() {
        $this->mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		if($this->mysqli->connect_error) {
				die('Error : ('. $this->mysqli->connect_errno .') '. $this->mysqli->connect_error);
		}
	}
   
    // Close Database connection
    public function close_connection() {
        if(isset($this->mysqli)) {
            $this->mysqli->close;
            unset($this->mysqli);
        }
    }
  
    // Perform a query
    public function query($sql) {
        $this->last_query = $sql;
        $result = $this->mysqli->query($sql);
        $this->confirm_query($result);
        return $result;
    }
   
    // Preparation for text to be inserted into the database (escape characters will be added)
    public function escape_value($value) { 
		$value = $this->mysqli->real_escape_string($value);
		return $value;
    }

    // Memorize the result query into an associative/numeric array
    public function fetch_array($result_set) {
        return $result_set->fetch_array();
    }

    // Return the number of rows of the result query
    public function num_rows($result_set) {
        return $result_set->num_rows;
    }

    // Get the last id inserted over the current database connection
    public function insert_id() {
        return $this->mysqli->insert_id;
    }
    
    // Get number of affected rows in previous MySQL operation
    public function affected_rows() {
        return $this->mysqli->affected_rows;
    }
    
    // Confirm query execution
    private function confirm_query($result) {
        if(!$result) {
            $output = "Database query failed: " . $this->mysqli->error . "<br/><br/>";
            // Uncomment for debug
            $output .= "Last SQL query: " . $this->last_query;
            die($output);
        }
    }

}

$database = new MySQLDatabase();
// Alias - Reference
$db =& $database;

?>
