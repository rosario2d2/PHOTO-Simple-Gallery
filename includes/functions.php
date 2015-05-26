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

function __autoload($class_name) {
    $class_name = strtolower($class_name);
    $path = LIB_PATH.DS."{$class_name}.php";
    if(file_exists($path)) {
        require_once($path);
    } else {
        die("The file {$class_name}.php could not be found.");
    }
}

function strip_zeros_from_date($marked_string="") {
    // First remove the marked zeros
    $no_zeros = str_replace('*0', '', $marked_string);
    // Then remove any remaining marks
    $cleaned_string = str_replace('*', '', $no_zeros);
    return $cleaned_string;
}

function redirect_to($location = NULL) {
    if($location != NULL) {
        header("Location: {$location}");
        exit;
    }
}

function output_message($message = "") {
    if (!empty($message)) {
        return "<p class=\"message\">{$message}</p>";
    } else {
        return "";
    }
}

function include_layout_template($template="", $admin="") {
	if($admin=="admin") {
		include(SITE_ROOT.DS.'admin'.DS.'layouts'.DS.$template);
	} else {
		include(SITE_ROOT.DS.'public'.DS.'layouts'.DS.$template);
	}
}

function log_action($action, $message="") {
    $logfile = SITE_ROOT.DS.'logs'.DS.'system.log';
    $timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
    switch($action) {
		case "logged":
			$action = "<td class=\"logs-loggedin\"></td>";
			break;
		case "loginfail":
			$action = "<td class=\"logs-loginfail\"></td>";
			break;
		case "error":
			$action = "<td class=\"logs-error\"></td>";
			break;
		case "info":
			$action = "<td class=\"logs-info\"></td>";
			break;
	}
    $line = "{$action}<td class=\"logs-timestamp\">{$timestamp}</td><td class=\"logs-message\">{$message}</td>\n";
    file_put_contents($logfile, $line, FILE_APPEND);
}

function datetime_to_text($datetime="") {
    $unixdatetime = strtotime($datetime);
    return strftime("%B %d, %Y at %I:%M %p", $unixdatetime);
}

function validate($data, $type) {
	switch($type) {
		case "date":
			$date = $data;
			$regex = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
			if (preg_match($regex, $date)) {
				return true;
			}
			break;
		case "alnum":
			$convert = array(
                    "à"=>"a", "è"=>"e", "ì"=>"i", "ò"=>"o", "ù"=>"u",
                    "À"=>"A", "È"=>"E", "Ì"=>"I", "Ò"=>"O", "Ù"=>"U", 
                    " "=>"", "_"=>"", "-"=>""
                    );
            $data = strtr($data, $convert);
			if(ctype_alnum($data)) {
				return true;
			}
			break;
		case "digit":
			if(ctype_digit($data)) {
				return true;
			}
			break;
		case "email":
			return filter_var($data, FILTER_VALIDATE_EMAIL);
			break;
	}
	return false;
}

?>
