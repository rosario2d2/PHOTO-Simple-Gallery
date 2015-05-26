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

class User extends DatabaseObject {
   
    protected static $table_name="users";
    protected static $db_fields = array('id', 'username', 'password', 'email', 'privilege_level', 'created', 'first_name', 'last_name');
    protected static $format = array('i', 's', 's', 's', 's', 'i', 's', 's');
    public $id;
    public $username;
    public $password;
    public $email;
    public $privilege_level;
    public $created;
    public $first_name;
    public $last_name;  
    
    public $errors;
    
    // Authenticate the user
    public static function authenticate($username="", $password="") {
		$data = array("username" => $username);
		$format = array('s');		
        $sql  = "SELECT * FROM users ";
        $sql .= "WHERE BINARY username = ? ";
        $sql .= "LIMIT 1";       
        $user = self::select($sql, $data, $format);             
        if(!empty($user)) {
			$user = array_shift($user);
			if(password_verify($password, $user->password)) {
				return $user;
			} else {
				return false;
			}
		} else {
			return false;
		}       
    }
    
    // Check username
    public function check_username($username) {
		$check = self::find_by_field_string($username, "username");
		if($check) {
			return true;
		} else {
			return false;
		}
	}
    
    // Update User
    public function update_user($pwd="") {
		if($pwd == "pwd") {
			$this->password = $this->hash_password($this->password);
		}
		$where = array("id" => $this->id);
		$where_format = array('i');
		if($this->update($where, $where_format)) {
			return true;      				
	    } else {
			$this->errors = $this->mysql_errors();
            return false;
        }
	}
    
    // Create a new User
    public function create_user() {
		$this->password = $this->hash_password($this->password);		
		if($this->create()) {
			return true;
		} else {
			$this->errors = $this->mysql_errors();
            return false;
		}		
	}
	
	// Delete a User
	public function destroy() {
		if($this->delete()) {
			return true;
		} else {
			$this->errors = $this->mysql_errors();
			return false;
		}
	}
	
	// Hash password
	private function hash_password($password) {
		$hash = password_hash($password, PASSWORD_DEFAULT);
		return $hash;
	}

	// Return the full name of the User
    public function full_name() {
        if(isset($this->first_name) && isset($this->last_name)) {
            return $this->first_name . " " . $this->last_name;
        } else {
            return "";
        }
    }
}

?>
