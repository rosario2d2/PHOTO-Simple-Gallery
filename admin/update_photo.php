<?php
require_once('../includes/initialize.php');
if (!$session->is_logged_in()) { redirect_to("login.php"); }

// UPDATE FUNCTION
if(isset($_POST['submit'])) {
	if(!empty($_GET['id']) && validate($_GET['id'], "digit")) {
		$photo = Photograph::find_by_id($_GET['id']);
		if($photo && ($photo->user == $session->user_id || $session->privilege_level == "admin")) {
			if($photo->photoname != trim($_POST['photoname']) || $photo->caption != trim($_POST['caption']) || $photo->album != trim($_POST['album'])) {
				if(!empty($_POST['photoname']) && validate($_POST['photoname'], "alnum")) {
					$photo->photoname = trim($_POST['photoname']);
				} elseif(empty($_POST['photoname'])) {
					$photo->photoname = "";
				} else {
					$session->message("Name must be an alphanumeric string.");
					redirect_to('update_photo.php?id='.$_GET['id']);
				}
				if(!empty($_POST['caption']) && validate($_POST['caption'], "alnum")) {
					$photo->caption = trim($_POST['caption']);
				} elseif(empty($_POST['caption'])) {
					$photo->caption = "";
				} else {
					$session->message("Caption must be an alphanumeric string.");
					redirect_to('update_photo.php?id='.$_GET['id']);
				}
				if(!empty($_POST['album']) && validate($_POST['album'], "digit")) {
					$photo->album = $_POST['album'];
				} elseif(empty($_POST['album'])) {
					$photo->album = NULL;
				} else {
					$session->message("Album ID not valid.");
					redirect_to('update_photo.php?id='.$_GET['id']);
				}
				if($photo->update_photo()) {
					$session->message("Photograph Updated successfully.");
					redirect_to($session->previous_location.$session->query_string);
				} else {
					$session->message("Could not update the photo.");
					redirect_to($session->previous_location.$session->query_string);
				}
			} else {
				$session->message("No changes were made.");
				redirect_to($session->previous_location.$session->query_string);
			}
			
		} else {
			$session->message("Could not update the photo, operation not permitted.");
			redirect_to($session->previous_location.$session->query_string);
		}		
	}
}

// DEFAULT SHOWING THE PHOTO
if(!empty($_GET['id']) && validate($_GET['id'], "digit")) {
	$photo = Photograph::find_by_id($_GET['id']);
	if(!$photo) {
		$session->message("The photo could not be located.");
		redirect_to($session->previous_location.$session->query_string);
	}
} else {
    $session->message("No photograph ID was provided.");
    redirect_to($session->previous_location);
}

$albums = Album::find_by_field_id($photo->user, "user");
$user = User::find_by_id($photo->user);


include_layout_template('admin_header.php', "admin"); 
?>
		<div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<img class="h2-icon" src="images/photographs_icon.png"><h2 class="left">Update Photo</h2>
					<div id="admin-box-header">
<?php if($session->privilege_level == "admin"): ?>
						<h2 class="right"><?php echo $user->username; ?></h2>
<?php endif; ?>
					</div>
					<div class="line"></div>
					<a href="<?php echo $session->previous_location.$session->query_string; ?>">&laquo; Back</a>
					<div class="info-box">
<?php if(!empty($message)) { echo "					".output_message($message)."\n"; } ?>
					</div>
				</div>
				<div class="admin-box">
					<div id="update-img-box">
						<img src="../showphoto.php?id=<?php echo $photo->id; ?>&amp;type=large">
					</div>
					<div id="update-values-box">
						<form action="update_photo.php?id=<?php echo $photo->id; ?>" method="POST"> 
							Name: <input type="text" name="photoname" value="<?php echo $photo->photoname; ?>">
							Caption: <input type="text" name="caption" value="<?php echo $photo->caption; ?>">
							Album: 
							<select name="album">
<?php
if(!empty($photo->album)) {
	foreach($albums as $album) {
		if($album->id == $photo->album) {
			echo "								<option value=\"".$album->id."\">".$album->name."</option>\n";
			echo "								<option value=\"\">None</option>\n";
		}
	} 
} else {
	echo "								<option value=\"\">None</option>\n";
}
?>
<?php if($albums): ?>
<?php foreach($albums as $album): ?>
<?php if($album->id != $photo->album): ?>
								<option value="<?php echo $album->id; ?>"><?php echo $album->name; ?></option>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
							</select>
							<input type="submit" name="submit" value="Update">
						</form>
					</div>
				</div>
			</div>
		</div>
<?php 
include_layout_template('admin_footer.php', "admin"); 
?>
