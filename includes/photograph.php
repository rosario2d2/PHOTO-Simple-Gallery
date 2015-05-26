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

class Photograph extends DatabaseObject {
    
    protected static $table_name="photographs";
    protected static $db_fields = array('id', 'filename', 'photoname', 'type', 'size', 'created', 'caption', 'album', 'user');
    protected static $format = array('i', 's', 's', 's', 'i', 'i', 's', 'i', 'i');
    public $id;
    public $filename;
    public $photoname;   
    public $type;
    public $size;
    public $created;
    public $caption;
    public $album;
	public $user;
    
    private $temp_path;
    private $extension;
    protected $upload_dir="photos";
    
    protected $thumbnails_dimension = array(
		"large"	=> 1000,
		"medium"  => 500,
		"small"	=> 200,
		"square" => 200
	);
	
    public $errors;

    protected $upload_errors = array(
        UPLOAD_ERR_OK         => "No errors.",
        UPLOAD_ERR_INI_SIZE   => "Larger than upload_max_filesize.",
        UPLOAD_ERR_FORM_SIZE  => "Larger than form MAX_FILE_SIZE.",
        UPLOAD_ERR_PARTIAL    => "Partial upload.",
        UPLOAD_ERR_NO_FILE    => "No file.",
        UPLOAD_ERR_NO_TMP_DIR => "No temporary directory.",
        UPLOAD_ERR_CANT_WRITE => "Can't write to disk.",
        UPLOAD_ERR_EXTENSION  => "File upload stopped by extension."
    );
	
	// Upload attach files
    public function attach_files($file, $key) {
        // Error checking
        if(!$file || empty($file) || !is_array($file)) {
            // Error: nothing uploaded or wrong argument usage
            $this->errors = "No file was uploaded";
            return false;
        } elseif($file['error'][$key] !=0) {
            // Error: report what PHP says went wrong
            $this->errors = $this->upload_errors[$file['error'][$key]];
            return false;
        } else {
            // Set object attributes to the form parameters
			$path_parts = pathinfo($file['name'][$key]);
			$this->extension = strtolower($path_parts['extension']);
            $this->temp_path = $file['tmp_name'][$key]; 
            $this->filename = uniqid().'.'.$this->extension;
            if(!$this->photoname) {
				$this->photoname = $path_parts['filename'];
			}
            $this->type = $file['type'][$key];
            $this->size = $file['size'][$key];
            if(!$this->album) {
				$this->album = NULL;
			}
            return true;
        }
    }

	// Save Photograph
    public function save_photo() {
		// Make sure there are no errors
        if(!empty($this->errors)) { return false; }
            
        // Can't save without filename and temp location
        if(empty($this->filename) || empty($this->temp_path)) {
			$this->errors = "The file location was not available.";
            return false;
		}
            
        // Determine the target_path
        $target_path = SITE_ROOT .DS. 'public' .DS. $this->upload_dir .DS. $this->filename;
            
        // Make sure a file with the same name doesn't already exist in the target location
        // If a file with the same name is found try to generate a new filename, if it still fails then stop
        $check_filename = 0;    
        while(file_exists($target_path)) {
			if($check_filename == 1) {
				// If generating another unique id failed for some reason then stop
				$this->errors = "The file {$this->filename} already exists.";
				return false;
				break;
			} else {
				// On a Windows system uniqid can't usleep(1) to ensure two identical ids aren't generated in the same microsecond
				// Add the more_entropy parameter, a random number as prefix and then hash the whole thing
				// Get a new filename
				$this->filename = md5(uniqid(mt_rand(), true)).'.'.$this->extension;
				// Reset the target path
				$target_path = SITE_ROOT .DS. 'public' .DS. $this->upload_dir .DS. $this->filename;
				$check_filename++;
			}
		}			
            
        // Attempt to move the file
        if(move_uploaded_file($this->temp_path, $target_path)) {							
			// If the file was successful saved than create the thumbnails
            self::create_thumbnails();
				
			// Save a corresponding entry to the database
			if($this->create()) {
				// We are done with temp_path, the file isn't there anymore
				unset($this->temp_path);
				return true;      				
			}								
		} else {
			// File was not moved
            $this->errors = "Could not save the photo, possibly due to incorrect permissions on the upload folder.";
            return false;
		}
    }
    
    // Update Photograph
    public function update_photo() {
		$where = array("id" => $this->id);
		$where_format = array('i');
		if($this->update($where, $where_format)) {
			return true;      				
	    } else {
            $this->errors = $this->mysql_errors();
            return false;
        }
	}

	// Delete Photograph
    public function destroy() {
        if($this->delete()) {
            // Remove the file
            $target_path_img = SITE_ROOT.DS.$this->image_path();           
            if(unlink($target_path_img)) {
				// Remove thumbnails
				foreach(array_keys($this->thumbnails_dimension) as $type) {
					$target_path_thumbnail = SITE_ROOT.DS.$this->thumbnail_path($type);
					if(file_exists($target_path_thumbnail)) {
						unlink($target_path_thumbnail);
					}
				}
				return true;
			} else {
				// Failed to remove the file
				$this->errors = "Could not delete the photo.";
				return false;
			}			
        } else {
            // Database delete failed
            $this->errors = $this->mysql_errors();
            return false;
        }
    }

	// Image path
    public function image_path() {
        return 'public'.DS.$this->upload_dir.DS.$this->filename;
    }
    
    // Thumbnail path
    public function thumbnail_path($type) {
		return 'public'.DS.$this->upload_dir.DS.'thumbnails'.DS.$type.DS.'thumbnail_'.$type.'_'.$this->filename;
	}
     
    // Thumbnails
    public function create_thumbnails() {
		// Path of the image				
		$original_img = SITE_ROOT .DS. 'public' .DS. $this->upload_dir .DS. $this->filename;
		
		// Get the image extension
		$path_parts = pathinfo($original_img);
		$extension = strtolower($path_parts['extension']);
		
		if ($extension == 'jpg' || $extension == 'jpeg') {
			$img = @imagecreatefromjpeg($original_img)
			or die("Cannot create new JPEG image");
		} elseif ($extension == 'png') {
			$img = @imagecreatefrompng($original_img)
			or die("Cannot create new PNG image");
		} elseif ($extension == 'gif') {
			$img = @imagecreatefromgif($original_img)
			or die("Cannot create new GIF image");
		}
		
		// If we have a valide image 
		if ($img) {
			// Get the original image dimensions		
			$original_width = imagesx($img);
			$original_height = imagesy($img);
			
			// Start creating thumbnails
			foreach($this->thumbnails_dimension as $type => $dimension) {
				
				// For a square thumbnail					
				if ($type == "square") {
					$scale_ratio = max($dimension/$original_width, $dimension/$original_height);
					
					if ($scale_ratio < 1) {
						$new_width = floor($scale_ratio*$original_width);
						$new_height = floor($scale_ratio*$original_height);
						
						$temporary_img_first = imagecreatetruecolor($new_width, $new_height);						
						$temporary_img_second = imagecreatetruecolor($dimension, $dimension);
						
						imagecopyresampled($temporary_img_first, $img, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
						
						if ($new_width == $dimension) {
							$yAxis = ($new_height/2)-($dimension/2);
							$xAxis = 0;
						} elseif ($new_height == $dimension)  {
							$yAxis = 0;
							$xAxis = ($new_width/2)-($dimension/2);
						} 
						
						imagecopyresampled($temporary_img_second, $temporary_img_first, 0, 0, $xAxis, $yAxis, $dimension, $dimension, $dimension, $dimension);
						
						$thumbnail = $temporary_img_second;
						$thumbnail_dir = SITE_ROOT .DS. 'public' .DS. $this->upload_dir .DS.'thumbnails'.DS.$type.DS.'thumbnail_'.$type.'_'.$this->filename;
						
						// Save the thumbnail
						imagejpeg($thumbnail, $thumbnail_dir, 100);
					}	
				// For a scaled thumbnail			
				} else {
					$scale_ratio = min($dimension/$original_width, $dimension/$original_height);						
					
					if ($scale_ratio < 1) {
						$new_width = floor($scale_ratio*$original_width);
						$new_height = floor($scale_ratio*$original_height);
	 
						$temporary_img = imagecreatetruecolor($new_width, $new_height);
			
						imagecopyresampled($temporary_img, $img, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
						
						$thumbnail = $temporary_img;
						$thumbnail_dir = SITE_ROOT .DS. 'public' .DS. $this->upload_dir .DS.'thumbnails'.DS.$type.DS.'thumbnail_'.$type.'_'.$this->filename;
							
						// Save the thumbnail
						imagejpeg($thumbnail, $thumbnail_dir, 100);
					} else {
						// The thumbnail we want is actually bigger than the original image	
						// So just save a thumbnail with the original dimension of the image
						$thumbnail_dir = SITE_ROOT .DS. 'public' .DS. $this->upload_dir .DS.'thumbnails'.DS.$type.DS.'thumbnail_'.$type.'_'.$this->filename;
						copy($original_img, $thumbnail_dir);
					}
				}
			}
		}
	}
    
    // Human readable file size
    public function size_as_text() {
        if($this->size < 1024) {
            return "{$this->size} bytes";
        } elseif($this->size < 1048576) {
            $size_kb = round($this->size/1024);
            return "{$size_kb} KB";
        } else {
            $size_mb = round($this->size/1048576, 1);
            return "{$size_mb} MB";
        }
    }
    
	// Count Photographs
	public static function count($user_id) {
		return self::count_by_field_id($user_id, "user");
	}
    
    // Find User Photographs
	public static function find($user_id, $per_page="", $offset="") {
		return self::find_by_field_id($user_id, "user", $per_page, $offset);
	}
}

?>
