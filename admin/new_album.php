<?php
require_once('../includes/initialize.php');
if (!$session->is_logged_in()) { redirect_to("login.php"); }

if(isset($_POST['submit'])) {
	if(!empty($_POST['name']) && validate($_POST['name'], "alnum")) {	
		$album = new Album();
		$album->name = trim($_POST['name']);
		$album->user = $session->user_id;
		$album->created = time();
		if($album->save()) {
			$session->message("New album {$album->name} created successfully.");
			redirect_to('new_album.php');
		} else {
			log_action("error", "User \"{$session->username}\" - Error creating new album: {$album->errors}");
			$session->message("Error creating new album");
			redirect_to('new_album.php');
		}
	} else {
		$session->message("Could not create new album, the name must be an alphamumeric string.");
		redirect_to('new_album.php');
	}
}

include_layout_template('admin_header.php', "admin"); 
?>
		<div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<img class="h2-icon" src="images/albums_icon.png"/><h2 class="left">Create Album</h2>
					<div id="admin-box-header">
					</div>
					<div class="line"></div>
					<a href="albums.php<?php echo $session->query_string; ?>">&laquo; Back</a>
					<div class="info-box">
<?php if(!empty($message)) { echo "					".output_message($message)."\n"; } else { echo "					<p>Choose a name for the new album.</p>\n"; } ?>
					</div>
				</div>
				<div class="admin-box">
					<div id="create-album-box">
						<form action="new_album.php" method="POST">
							<input type="text" name="name" value="">
							<input type="submit" name="submit" value="Create">
						</form>
					</div>
				</div>
			</div>
		</div>
<?php
include_layout_template('admin_footer.php', "admin"); 
?>
