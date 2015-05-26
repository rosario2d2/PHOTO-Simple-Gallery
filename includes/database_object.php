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

require_once(LIB_PATH.DS."database.php");

// Late Static Bindings
// Common Database Metods

class DatabaseObject {
	
	private $errors;
	
	// COUNT ALL
    public static function count_all() {
        global $db;        
        $sql = "SELECT COUNT(*) FROM ".static::$table_name;        
        $result_set = $db->query($sql);        
        $row = $db->fetch_array($result_set);       
        return array_shift($row);
    }
    
    // COUNT BY FIELD ID
    public static function count_by_field_id($id, $field) {
        $data = array("id" => $id);
        $format = array('i');
        $sql = "SELECT COUNT(*) FROM ".static::$table_name." WHERE ".$field."=?";
        return self::select($sql, $data, $format, "result-row");
	}
	
	// COUNT RESULTS OF A SEARCH BY DATE INTERVAL
	public static function count_by_date($user_id, $datefrom, $dateto) {
		$dateto = date('Y-m-d', strtotime($dateto. ' + 1 day'));
		$data = array("user" => $user_id, "datefrom" => $datefrom, "dateto" => $dateto);
		$format = array('i', 's', 's');
		
		if($user_id == "all") {
			$data = array_slice($data, 1);
			$format = array_slice($format, 1);
		}
		
		$sql = "SELECT COUNT(*) FROM ".static::$table_name." WHERE ";
		if($user_id !== "all") {
			$sql .= "user=? AND ";
		}
		$sql .= "(FROM_UNIXTIME(created) BETWEEN ? AND ?)";
		
		return self::select($sql, $data, $format, "result-row");
	}
	
	//COUNT RESULTS OF A SEARCH BY KEYWORD STRING
	public static function count_by_search($user_id, $search, $fields) {
		$search = '%'.$search.'%';
		
		$data = array("user" => $user_id);
		foreach($fields as $field) {
			$data[$field] = $search;
		}
		
		$format = array('i');
		for($i = 1; $i <= count($fields); $i++) {
			$format[] = 's';
		}
		
		if($user_id == "all") {
			$data = array_slice($data, 1);
			$format = array_slice($format, 1);
		}
		
		$sql = "SELECT COUNT(*) FROM ".static::$table_name." WHERE ";
		if($user_id !== "all") {
			$sql .= "user=? AND ";
		}
		$sql .= "(".$fields[0]." LIKE ?";
		if(count($fields) > 1) {
			array_shift($fields);
			foreach($fields as $field) {
				$sql .= " OR ".$field." LIKE ?";
			}
		}
		$sql .= ")";
		
		return self::select($sql, $data, $format, "result-row");
	}
	
	// SEARCH BY DATE INTERVAL
	public static function search_by_date($user_id, $datefrom, $dateto, $per_page="", $offset="") {
		$dateto = date('Y-m-d', strtotime($dateto. ' + 1 day'));
		$data = array("user" => $user_id, "datefrom" => $datefrom, "dateto" => $dateto);
		$format = array('i', 's', 's');
		
		if($user_id == "all") {
			$data = array_slice($data, 1);
			$format = array_slice($format, 1);
		}
		
		$sql  = "SELECT * FROM ".static::$table_name." WHERE ";
		if($user_id !== "all") {
			$sql .= "user = ? AND ";
		}
		$sql .= "(FROM_UNIXTIME(created) BETWEEN ? AND ?) ";
		$sql .= "ORDER BY id DESC";
		if($per_page !== "" && $offset !== "") { $sql .= " LIMIT ".$per_page." OFFSET ".$offset; }
		
		return self::select($sql, $data, $format);
	}
	
	// SEARCH BY KEYWORD STRING
	function search_by_word($user_id, $search, $fields, $per_page="", $offset="") {
		$search = preg_split('/\s+/', $search);
		$data = array($user_id);
		$format = array('i');
		
		foreach($search as $word) {
			for($i = 1; $i <= count($fields); $i++) {
				$data[] = '%'.$word.'%';
				$format[] = 's';
			}
		}
		
		if($user_id == "all") {
			$data = array_slice($data, 1);
			$format = array_slice($format, 1);
		}
		
		$sql  = "SELECT * FROM ".static::$table_name." WHERE ";
		if($user_id !== "all") {
			$sql .= "user = ? AND ";
		}
		for($x = 1; $x <= count($search); $x++) {
			if($x >= 2) {
				$sql .= " OR ";
			}
			$sql .= "(".$fields[0]." LIKE ?";
			if(count($fields) > 1) {
				foreach($fields as $key => $field) {
					if($key != 0) {
						$sql .= " OR ".$field." LIKE ?";
					}
				}
			}
			$sql .= ")";
		}
		if($per_page !== "" && $offset !== "") { $sql .= " LIMIT ".$per_page." OFFSET ".$offset; }
		
		return self::select($sql, $data, $format);	
	}
	
	// FIND ALL
    public static function find_all($per_page="", $offset="") {
        global $db;       
        $sql = "SELECT * FROM ".static::$table_name." ";
        $sql .= "ORDER BY id DESC";
        if($per_page !== "" && $offset !== "") { $sql .= " LIMIT ".$per_page." OFFSET ".$offset; }
        return self::find_by_sql($sql);
    }
    
    // FIND BY FIELD STRING
    public static function find_by_field_string($string, $field, $per_page="", $offset="") {
        $data = array("string" => $string);
        $format = array('s');
        $sql = "SELECT * FROM ".static::$table_name." WHERE ".$field."=? ";
        $sql .= "ORDER BY id DESC";
        if($per_page !== "" && $offset !== "") { $sql .= " LIMIT ".$per_page." OFFSET ".$offset; }
        return self::select($sql, $data, $format);
	}
	
	// FIND BY FIELD ID
    public static function find_by_field_id($id, $field, $per_page="", $offset="") {
        $data = array("id" => $id);
        $format = array('i');
        $sql = "SELECT * FROM ".static::$table_name." WHERE ".$field."=? ";
        $sql .= "ORDER BY id DESC";
        if($per_page !== "" && $offset !== "") { $sql .= " LIMIT ".$per_page." OFFSET ".$offset; }
        return self::select($sql, $data, $format);
	}
	
	// FIND BY ID
    public static function find_by_id($id=0) {
        $data = array("id" => $id);
        $format = array('i');
        $sql = "SELECT * FROM ".static::$table_name." WHERE id=? LIMIT 1";
        return self::select($sql, $data, $format, "object-row");     
    }
	
	// FIND BY SQL
    public static function find_by_sql($sql="") {
        global $db;
        
        // Fetch result
        $result_set = $db->query($sql);
        
        // Create result object
        $object_array = array();
        while ($row = $db->fetch_array($result_set)) {
            $object_array[] = self::instantiate($row);
        }
        
        return $object_array;
    }
     
    // SELECT
    public static function select($query, $data, $format, $return="object-array") {		
		global $db;
		
		//Prepare our query for binding
		$stmt = $db->mysqli->prepare($query);
		
		//Normalize format
		$format = implode('', $format);
		$format = str_replace('%', '', $format);
		
		// Prepend $format onto $values
		array_unshift($data, $format);
		
		//Dynamically bind values
		call_user_func_array( array( $stmt, 'bind_param'), self::ref_values($data));
		
		//Execute the query
		$stmt->execute();
		
		//Fetch results
		$result = $stmt->get_result();
		
		if($return == "result-row") {
			$row = $db->fetch_array($result);       
			return array_shift($row);	
		} else {
			// Create result object
			$object_array = array();
			while ($row = $db->fetch_array($result)) {
				$object_array[] = self::instantiate($row);
			}
			if($return == "object-row") {
				return !empty($object_array) ? array_shift($object_array) : false;
			} elseif($return == "object-array") {
				return !empty($object_array) ? $object_array : false;
			}
		} 
	}
	
	// CREATE
   	public function create() {				
		global $db;

		$attributes = $this->attributes();
		$binding = static::$format;
		
		// Build format string
		$binding = implode('', $binding);
		$binding = str_replace('%', '', $binding);
		
		list( $fields, $placeholders, $values ) = $this->prep_query($attributes);
		
		// Prepend $format onto $values
		array_unshift($values, $binding);
		
		// Prepary our query for binding
		$stmt = $db->mysqli->prepare("INSERT INTO ".static::$table_name." ({$fields}) VALUES ({$placeholders})");
		
		// Dynamically bind values
		call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));
		
		// Execute the query
		$stmt->execute();
		
		// Memorize possible errors
		$this->errors = $stmt->error;
		
		// Check for successful insertion
		return ($stmt->affected_rows == 1) ? true : false;
	} 
    
    // UPDATE
    public function update($where, $where_format) {
		global $db;
		
		$attributes = $this->attributes();
		$binding = static::$format;
				
		// Build format array
		$binding = implode('', $binding);
		$binding = str_replace('%', '', $binding);
		$where_format = implode('', $where_format);
		$where_format = str_replace('%', '', $where_format);
		$binding .= $where_format;		
		
		list( $fields, $placeholders, $values ) = $this->prep_query($attributes, 'update');
		
		//Format where clause
		$where_clause = '';
		$where_values = '';
		$count = 0;
		foreach ( $where as $field => $value ) {
			if ( $count > 0 ) {
				$where_clause .= ' AND ';
			}
			$where_clause .= $field . '=?';
			$where_values[] = $value;
			$count++;
		}
		
		// Prepend $format onto $values
		array_unshift($values, $binding);
		$values = array_merge($values, $where_values);
		
		// Prepary our query for binding
		$stmt = $db->mysqli->prepare("UPDATE ".static::$table_name." SET {$placeholders} WHERE {$where_clause}");
		
		// Dynamically bind values
		call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));
		
		// Execute the query
		$stmt->execute();
		
		// Memorize possible errors
		$this->errors = $stmt->error;
		
		// Check for successful update
		return ($stmt->affected_rows > 0) ? true : false;
	} 
    
	// SAVE
    public function save() {
        return isset($this->id) ? $this->update() : $this->create();
    }
    
    // DELETE
    public function delete() {
		global $db;
		
		$stmt = $db->mysqli->prepare("DELETE FROM ".static::$table_name." WHERE ID = ? LIMIT 1");
		$stmt->bind_param('i', $this->id);
		$stmt->execute();
		
		// Memorize possible errors
		$this->errors = $stmt->error;
		
		// Check for successful delete
		return ($stmt->affected_rows == 1) ? true : false; 
	}
	
	// DELETE BY USER ID
	public static function delete_by_user_id($user) {
		global $db;

		$stmt = $db->mysqli->prepare("DELETE FROM ".static::$table_name." WHERE user = ?");
		$stmt->bind_param('i', $user);
		$stmt->execute();
		
		// Memorize possible errors
		$errors = $stmt->error;
		
		// Check for successful delete
		return ($stmt->affected_rows == 1) ? true : false; 
	}
    
    // RETURN ERRORS
	public function mysql_errors() {
		return $this->errors;
	}
    
    private static function instantiate($record) {
		$class_name = get_called_class();
        $object = new $class_name;
        foreach($record as $attribute=>$value) {
            if($object->has_attribute($attribute)) {
                $object->$attribute = $value;
            }
        }
        return $object;
    }
    
    protected function attributes() {
        // Return an array of attribute names(keys) and their values
        $attributes = array();
        foreach(static::$db_fields as $field) {
            if(property_exists($this, $field)) {
                $attributes[$field] = $this->$field;
            }
        }
        return $attributes;
    }

    protected function sanitized_attributes() {
        global $db;
        $clean_attributes = array();
        // Sanitize the values before submitting
        // Note: does not alter the actual value of each attribute
        foreach($this->attributes() as $key => $value) {
            $clean_attributes[$key] = $db->escape_value($value);
        }
        return $clean_attributes;
    }
	
	private function has_attribute($attribute) {
        // Get_object_vars returns an associative array with all attributes (incl. private ones!)
        // as the keys and their current values as value
        $object_vars = $this->attributes();
        // We don't care about the value, we just want to know if the key exists
        // Will return true or false
        return array_key_exists($attribute, $object_vars);
    }
	
	private function prep_query($data, $type='insert') {
		
		// Instantiate $fields and $placeholders for looping
		$fields = '';
		$placeholders = '';
		$values = array();
		
		// Loop through $data and build $fields, $placeholders, and $values
		foreach ( $data as $field => $value ) {
			$fields .= "{$field},";
			$values[] = $value;
			if ( $type == 'update') {
				$placeholders .= $field . '=?,';
			} else {
				$placeholders .= '?,';
			}
		}
		
		// Normalize $fields and $placeholders for inserting
		$fields = substr($fields, 0, -1);
		$placeholders = substr($placeholders, 0, -1);
		return array( $fields, $placeholders, $values );
	} 
	
	private function ref_values($array) {
		$refs = array();
		foreach ($array as $key => $value) {
			$refs[$key] = &$array[$key];
		}
		return $refs;
	} 
			
}

?>
