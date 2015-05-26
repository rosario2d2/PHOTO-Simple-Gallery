<?php
require_once('../includes/initialize.php');
if (!$session->is_logged_in()) { redirect_to("login.php"); }

if(isset($_POST['submit'])) {
	$user = User::find_by_id($session->user_id);
	$change = 0;
	$pwd = NULL;
	if($user->username != trim($_POST['username'])) {
		if(!empty($_POST['username']) && validate($_POST['username'], "alnum")) {
			$username = User::check_username(trim($_POST['username']));
			if($username) {
				$session->message("Username already in use, choose another.");
				redirect_to('profile.php');
			} else {
				$user->username = trim($_POST['username']);
				$change = 1;
			}
		} else {
			$session->message("Username must be an alphanumeric string.");
			redirect_to('profile.php');
		}
	}
	if(!empty($_POST['password'])) {
		if($_POST['password'] == $_POST['confirmpwd']) {
			$password = trim($_POST['password']);
			if(strlen($password) >= 8) {
				$user->password = $password;
				$change = 1;
				$pwd= "pwd";
			} else {
				$session->message("Password must be at least 8 characters long.");
				redirect_to('profile.php');
			}
		} else {
			$session->message("Password check failed, confirm your password.");
			redirect_to('profile.php');
		}
	}
	if($user->email != trim($_POST['email']) || $user->first_name != trim($_POST['first_name']) || $user->last_name != trim($_POST['last_name'])) {
		$change = 1;
		if(!empty($_POST['email']) && validate($_POST['email'], "email")) { 
			$user->email = $_POST['email']; 
		} elseif(empty($_POST['email'])) {
			$user->email = NULL; 
		} else {
			$session->message("Email address not valid.");
			redirect_to('profile.php');
		}
		if(!empty($_POST['first_name']) && validate($_POST['first_name'], "alnum")) {
			$user->first_name = trim($_POST['first_name']);
		} elseif(empty($_POST['first_name'])) { 
			$user->first_name = NULL;
		} else {
			$session->message("First name must be an alphanumeric string.");
			redirect_to('profile.php');
		}
		if(!empty($_POST['last_name']) && validate($_POST['last_name'], "alnum")) { 
			$user->last_name = trim($_POST['last_name']);
		} elseif(empty($_POST['last_name'])) { 
			$user->last_name = NULL;
		} else {
			$session->message("Last name must be an alphanumeric string.");
			redirect_to('profile.php');
		}
	}
	if($change == 1) {
		if($user->update_user($pwd)) {
			log_action("info", "User \"{$session->username}\" profile updated.");
			$session->message("User profile updated successfully.");
		} else {
			$session->message("Could not update the profile");
		}
	} else {
		$session->message("No changes were made.");
	}
	redirect_to('profile.php');
}

$user = User::find_by_id($session->user_id);

include_layout_template('admin_header.php', "admin"); 
?>
		<div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<img class="h2-icon" src="images/administrator_icon.png"><h2 class="left">User Profile</h2>
					<div id="admin-box-header">
					</div>
					<div class="line"></div>
					<a href="index.php">&laquo; Home</a>
					<div class="info-box">
<?php if(!empty($message)) { echo "					".output_message($message)."\n"; } else { echo "					<p>Update your profile informations.</p>\n"; } ?>
					</div>
				</div>
				<div class="admin-box">
					<div id="center-form-div">
						<form action="profile.php" method="POST">
							Username:
							<input type="text" name="username" value="<?php echo $user->username; ?>">		
							New Password:
							<input type="password" name="password" value="">
							Confirm New Password:
							<input type="password" name="confirmpwd" value="">
							E-Mail:
							<input type="text" name="email" value="<?php echo $user->email; ?>">
							First Name:
							<input type="text" name="first_name" value="<?php echo $user->first_name; ?>">
							Last Name:
							<input type="text" name="last_name" value="<?php echo $user->last_name; ?>">
							<input type="submit" name="submit" value="Update">
						</form>
					</div>
				</div>
			</div>
		</div>
<?php
include_layout_template('admin_footer.php', "admin"); 
?>
