<?php
require_once("../includes/initialize.php");
if ($session->is_logged_in()) { redirect_to("index.php"); }

if (isset($_POST['submit'])) {
	if(!empty($_POST['username']) && !empty($_POST['password'])) {
		$username = trim($_POST['username']);
		$password = trim($_POST['password']);
		if(validate($username, "alnum")) {
			$found_user = User::authenticate($username, $password);
			if ($found_user) {
				$session->login($found_user);
				log_action("logged", "User \"".$found_user->username."\" logged in successfully from IP ".$_SERVER['REMOTE_ADDR']);
				redirect_to("index.php");
			} else {
				$message = "Username/Password combination incorrect.";
				log_action("loginfail", "Login attempt with username \"".htmlentities($username)."\" from IP ".$_SERVER['REMOTE_ADDR']);
			}
		} else {
			$message = "Username/Password combination incorrect.";
			log_action("loginfail", "Login attempt with non alphanumeric username \"".htmlentities($username)."\" from IP ".$_SERVER['REMOTE_ADDR']);
		}
	} else {
	$message = "Insert Usename and Password.";
	}
} 
if(isset($database)) { $database->close_connection(); }

include_layout_template('login_header.php', "admin");
?>
		<div class="login-card">
			<a id="login-logo" href="../index.php"><img src="images/logo.jpg" alt="" /></a>
			<form action="login.php" method="post">
				<input id="login-user" type="text" name="username" placeholder="Username">
				<input id="login-password" type="password" name="password" placeholder="Password">
				<span><?php echo output_message($message); ?></span>
				<input type="submit" name="submit" id="login-submit" value="Login">
			</form>
			<div class="login-help">
			</div>
		</div>
<?php
include_layout_template('login_footer.php', "admin");
?>
