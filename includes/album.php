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

class Album extends DatabaseObject {
    
    protected static $table_name="albums";
    protected static $db_fields = array('id', 'name', 'created', 'user');
    protected static $format = array('i', 's', 'i', 'i');
    public $id;
    public $name;
    public $created;
    public $user;
    
    public $errors;
    
    // Save Album
    public function save() {
		if($this->create()) {
			return true;
		} else {
			$this->errors = $this->mysql_errors();
            return false;
		}
	}
	
	// Update Album
	public function update_album() {
		$where = array("id" => $this->id);
		$where_format = array('i');
		if($this->update($where, $where_format)) {
			return true;      				
	    } else {
			$this->errors = $this->mysql_errors();
            return false;
        }
	}
	
	// Delete Album
	public function destroy() {
		if($this->delete()) {
			return true;
		} else {
			$this->errors = $this->mysql_errors();
			return false;
		}
	}
	
	// Count Photographs in Album
	public function photos_count() {
		return Photograph::count_by_field_id($this->id, "album");
	}
	
	// Photographs in Album
	public function photographs($per_page="", $offset="") {
        return Photograph::find_by_field_id($this->id, "album", $per_page, $offset);
    }
	
	// Album cover
	public function cover() {
		return Photograph::find_by_field_id($this->id, "album");
	}
	
	// Count Albums
	public static function count($user_id) {
		return self::count_by_field_id($user_id, "user");
	}
	
	// Find Albums 
	public static function find($user_id, $per_page="", $offset="") {
		return self::find_by_field_id($user_id, "user", $per_page, $offset);
	}
	
}

?>
