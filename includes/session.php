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

class Session {
    
    private $logged_in=false;  
    private $name = "PHOTOSESSID";
    private $last_activity;
    private $ttl = 90;
    
    public $user_id;
    public $username;
    public $privilege_level;   
    public $query_string;
    public $previous_location;
    public $message;
        
    function __construct() { 
		      
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.entropy_file', '/dev/urandom');   
        
        session_set_cookie_params(0, '/', ini_get('session.cookie_domain'), isset($_SERVER['HTTPS']), true);
               
        session_name($this->name);
		session_start();
		
		$this->check_previous_location();
		$this->check_query_string();
		$this->check_message();
        $this->check_login();
        
        $_SESSION['view_images'] = time();
    }

    public function is_logged_in() {
        return $this->logged_in;
    }
    
    public function login($user) {
		// Database should find user based on username/password
        if($user) {	
			if($user->id) {
				session_regenerate_id(true);
				$this->logged_in = true; 			
				$this->user_id = $_SESSION['user_id'] = $user->id;
				$this->username = $_SESSION['username'] = $user->username;
				$this->last_activity = $_SESSION['last_activity'] = time();	
				$this->privilege_level = $_SESSION['privilege_level'] = $user->privilege_level;
			}
        }
    }

    private function check_login() {
		if(isset($_SESSION['user_id'])) {
			if($this->expired($this->ttl)) {
				$this->user_id = $_SESSION['user_id'];
				$this->username = $_SESSION['username'];
				$this->last_activity = $_SESSION['last_activity'];
				$this->privilege_level = $_SESSION['privilege_level'];
				$this->logged_in = true;
			} else {
				$this->destroy();
			}
        } else {
            $this->logged_in = false;          
        }
    }
    
    public function destroy() {		
		if (session_id() !== '' || isset($_COOKIE[$this->name])) {		
			$_SESSION = array();

			$params = session_get_cookie_params();

			setcookie(
				$this->name, '', 
				time() - 2592000,
				$params['path'], 
				$params['domain'],
				$params['secure'], 
				$params['httponly']
			);
				
			$this->logged_in = false;
			return session_destroy();
		} else {
			return false;
		}
	}
		
	// Check if the session is expired through a custom timeout
	public function expired($ttl = 30) {
		$activity = isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : false;	
		if (!$activity || time() - $activity > $ttl * 60) {
			return false;
		} else {
			$_SESSION['last_activity'] = time();
			return true;
		}
	}
	
	// Save Query String in session
	public function save_query_string() {
		$_SESSION['query_string'] = $_SERVER['QUERY_STRING'];
	}
	
	// Save Location in session
	public function save_location($location) {
		$_SESSION['previous_location'] = $location;
	}

    // Set the message or get the message stored in session
    public function message($msg="") {
        if(!empty($msg)) {
            $_SESSION['message'] = $msg;
        } else {
            return $this->message;
        }
    }
    
    private function check_query_string() {
        // Is there a query string stored in the session?
        if(!empty($_SESSION['query_string'])) {
            // Add it as an attribute
            $this->query_string = "?".$_SESSION['query_string'];
        } else {
            $this->query_string = "";
        }
    }
    
    private function check_previous_location() {
        // Is there a previous location stored in the session?
        if(!empty($_SESSION['previous_location'])) {
            // Add it as an attribute
            $this->previous_location = $_SESSION['previous_location'];
        } else {
            $this->previous_location = "";
        }
    }

    private function check_message() {
        // Is there a message stored in the session?
        if(isset($_SESSION['message'])) {
            // Add it as an attribute and erase the stored version
            $this->message = $_SESSION['message'];
            unset($_SESSION['message']);
        } else {
            $this->message = "";
        }
    }

}

$session = new Session();
$message = $session->message();

?>
