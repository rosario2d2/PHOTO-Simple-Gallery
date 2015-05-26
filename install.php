<?php
require_once("includes/config.php");
if(empty(DB_SERVER) || empty(DB_USER) || empty(DB_PASSWORD) | empty(DB_NAME)) {
	echo "<b>Error, fill all the required fields in includes/config.php.</b>";
	die;
} else {
	$mysqli = @new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	if($mysqli->connect_error) {
		echo "<b>Database connection error, make sure includes/config.php have the correct credentials.</b>";
		die;
	} else {
		$mysqli->close;
		unset($mysqli);
	}
}

require_once('includes/initialize.php');

if(isset($_POST['submit'])) {
	$user = new User();
	
	if(!empty($_POST['username']) && validate($_POST['username'], "alnum")) {
		$username = User::check_username(trim($_POST['username']));
		if($username) {
			$session->message("Username already in use, choose another.");
			redirect_to('install.php');
		} else {
			$user->username = trim($_POST['username']);
		}
	} else {
		$session->message("Username must be an alphanumeric string.");
		redirect_to('install.php');
	}
	
	if(!empty($_POST['password'])) {
		if($_POST['password'] == $_POST['confirmpwd']) {
			$password = trim($_POST['password']);
			if(strlen($password) >= 8) {
				$user->password = $password;
			} else {
				$session->message("Password must be at least 8 characters long.");
				redirect_to('install.php');
			}
		} else {
			$session->message("Password check failed, confirm your password.");
			redirect_to('install.php');
		}
	} else {
		$session->message("Password field can't be empty.");
		redirect_to('install.php');
	}
	
	if(!empty($_POST['email']) && validate($_POST['email'], "email")) { 
		$user->email = $_POST['email']; 
	} elseif(empty($_POST['email'])) {
		$user->email = NULL; 
	} else {
		$session->message("Email address not valid.");
		redirect_to('install.php');
	}
	
	if(!empty($_POST['first_name']) && validate($_POST['first_name'], "alnum")) {
		$user->first_name = trim($_POST['first_name']);
	} elseif(empty($_POST['first_name'])) { 
		$user->first_name = NULL;
	} else {
		$session->message("First name must be an alphanumeric string.");
		redirect_to('install.php');
	}
	
	if(!empty($_POST['last_name']) && validate($_POST['last_name'], "alnum")) { 
		$user->last_name = trim($_POST['last_name']);
	} elseif(empty($_POST['last_name'])) { 
		$user->last_name = NULL;
	} else {
		$session->message("Last name must be an alphanumeric string.");
		redirect_to('install.php');
	}
	
	$user->privilege_level = "admin";
	$user->created = time();
	
	if($user->create_user()) {
		log_action("info", "Install - New user \"{$user->username}\" created.");
		redirect_to('admin/login.php');
	} else {
		log_action("error", "Install - Error creating new user: {$user->errors}");
		$session->message("Error creating new user");
	}
	redirect_to('install.php');
}

$user = User::count_all();

?>
<!DOCTYPE HTML> 
<html>
    <head>
        <title>Photo Simple Gallery</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0, user-scalable=0">
        <link rel="stylesheet" href="public/stylesheets/main.css" type="text/css">
        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans">
        <link href="favicon.ico" rel="icon" type="image/x-icon" >
    </head>
    <body>
		<style>
			#install-panel {
				max-width:1200px;
				padding:20px;
				margin:40px auto 0;
			}
			
			.install-box {
				margin-bottom:30px;
			}
			
			#logo {
				text-align:center;
			}
			
			#logo img {
				width:250px;
			}
			
			#install-welcome {
				margin-top:30px;
				text-align:center;
			}
			
			#center-div-form {
			}
			
			#info-box {
				text-align:center;
			}
			
			input[type=text], input[type=password]{
				height: 35px;
				font-size: 15px;
				width: 300px;
				-webkit-appearance: none;
				background: #FFFFFF;
				border: 1px solid #D9D9D9;
				border-top: 1px solid #C0C0C0;
				padding: 0 8px;
				box-sizing: border-box;
				-moz-box-sizing: border-box;
				letter-spacing:3px;
				color:#000000;
				vertical-align:middle;
			}

			input[type=submit] {
				height:35px;
				background:#000000;
				border:none;
				color:#FFFFFF;
				vertical-align:middle;
			}
			
			#center-form-div {
				margin:auto;
				padding:10px;
				border:1px solid #ddd;
				max-width:500px;
			}
			
			#center-form-div input[type=text], input[type=password] {
				width:100%;
				margin-bottom:30px;
				height:35px;
			}

			#center-form-div input[type=submit] {
				width:100%;
			}
		</style>
		<div id="main">
			<div id="install-panel">
				<div class="install-box">
					<div id="logo">
						<img src="public/images/logo.jpg">
					</div>
					<div id="install-welcome">
<?php if($user >= 1): ?>
						<h2>Delete install.php from the web server.</h2>
					</div>
				</div>
<?php else: ?>
						<h2>Create the administrator of the photo gallery and complete the installation.</h2>
					</div>
					<div id="info-box">
<?php if(!empty($message)) { echo "					".output_message($message)."\n"; } else { echo "					<p>Fill the required fields and click on \"Create\".</p>\n"; } ?>
					</div>
				</div>
				<div class="install-box">
					<div id="center-form-div">
						<form action="install.php" method="POST">
							*Username:
							<input type="text" name="username" value="">
							*Password:
							<input type="password" name="password" value="">
							*Confirm Password:
							<input type="password" name="confirmpwd" value="">
							E-Mail:
							<input type="text" name="email" value="">
							First Name:
							<input type="text" name="first_name" value="">
							Last Name:
							<input type="text" name="last_name" value="">
							<input type="submit" name="submit" value="Create">
						</form>
					</div>
				</div>
<?php endif; ?>
			</div>
		</div>
	</body>
</html>
