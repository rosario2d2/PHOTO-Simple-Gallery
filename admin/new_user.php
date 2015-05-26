<?php
require_once('../includes/initialize.php');
if (!$session->is_logged_in() || $session->privilege_level !== "admin") { redirect_to("login.php"); }

if(isset($_POST['submit'])) {

	$user = new User();
	
	if(!empty($_POST['username']) && validate($_POST['username'], "alnum")) {
		$username = User::check_username(trim($_POST['username']));
		if($username) {
			$session->message("Username already in use, choose another.");
			redirect_to('new_user.php');
		} else {
			$user->username = trim($_POST['username']);
		}
	} else {
		$session->message("Username must be an alphanumeric string.");
		redirect_to('new_user.php');
	}
	
	if(!empty($_POST['password'])) {
		if($_POST['password'] == $_POST['confirmpwd']) {
			$password = trim($_POST['password']);
			if(strlen($password) >= 8) {
				$user->password = $password;
			} else {
				$session->message("Password must be at least 8 characters long.");
				redirect_to('new_user.php');
			}
		} else {
			$session->message("Password check failed, confirm your password.");
			redirect_to('new_user.php');
		}
	} else {
		$session->message("Password field can't be empty.");
		redirect_to('new_user.php');
	}
	
	if(!empty($_POST['email']) && validate($_POST['email'], "email")) { 
		$user->email = $_POST['email']; 
	} elseif(empty($_POST['email'])) {
		$user->email = NULL; 
	} else {
		$session->message("Email address not valid.");
		redirect_to('new_user.php');
	}
	
	if(!empty($_POST['privilege']) && ($_POST['privilege'] == "user" || $_POST['privilege'] == "admin")) {
		$user->privilege_level = $_POST['privilege'];
	} else {
		$session->message("Operation not valid.");
		redirect_to('new_user.php');
	}
	
	if(!empty($_POST['first_name']) && validate($_POST['first_name'], "alnum")) {
		$user->first_name = trim($_POST['first_name']);
	} elseif(empty($_POST['first_name'])) { 
		$user->first_name = NULL;
	} else {
		$session->message("First name must be an alphanumeric string.");
		redirect_to('new_user.php');
	}
	
	if(!empty($_POST['last_name']) && validate($_POST['last_name'], "alnum")) { 
		$user->last_name = trim($_POST['last_name']);
	} elseif(empty($_POST['last_name'])) { 
		$user->last_name = NULL;
	} else {
		$session->message("Last name must be an alphanumeric string.");
		redirect_to('new_user.php');
	}
	
	$user->created = time();
	
	if($user->create_user()) {
		log_action("info", "User \"{$session->username}\" - New user \"{$user->username}\" created.");
		$session->message("New user {$user->username} created successfully.");
	} else {
		log_action("error", "User \"{$session->username}\" - Error creating new user: {$user->errors}");
		$session->message("Error creating new user");
	}
	redirect_to('new_user.php');
}

include_layout_template('admin_header.php', "admin"); 
?>
		<div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<img class="h2-icon" src="images/administrator_icon.png"><h2 class="left">Create a new user</h2>
					<div id="admin-box-header">
					</div>
					<div class="line"></div>
					<a href="system_users.php">&laquo; Back</a>
					<div class="info-box">
<?php if(!empty($message)) { echo "					".output_message($message)."\n"; } else { echo "					<p>Fill the required fields and click on \"Create\".</p>\n"; } ?>
					</div>
				</div>
				<div class="admin-box">
					<div id="center-form-div">
						<form action="new_user.php" method="POST">
							Username:
							<input type="text" name="username" value="">
							Password:
							<input type="password" name="password" value="">
							Confirm Password:
							<input type="password" name="confirmpwd" value="">
							E-Mail:
							<input type="text" name="email" value="">
							Privilege:
							<select name="privilege">
								<option value="user">User</option>
								<option value="admin">Admin</option>
							</select>
							First Name:
							<input type="text" name="first_name" value="">
							Last Name:
							<input type="text" name="last_name" value="">
							<input type="submit" name="submit" value="Create">
						</form>
					</div>
				</div>
			</div>
		</div>
<?php
include_layout_template('admin_footer.php', "admin"); 
?>
