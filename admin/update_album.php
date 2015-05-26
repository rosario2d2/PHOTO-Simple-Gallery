<?php
require_once('../includes/initialize.php');
if (!$session->is_logged_in()) { redirect_to("login.php"); }

// PAGINATION
$page = 1;
$per_page = 18;
if(!empty($_GET['page']) && validate($_GET['page'], "digit")) {
	$page = $_GET['page'];
}

// UPDATE FUNCTION
if(isset($_POST['update'])) {
	if(!empty($_GET['id']) && validate($_GET['id'], "digit")) {	
		$album = Album::find_by_id($_GET['id']);
		if($album && ($album->user == $session->user_id || $session->privilege_level == "admin")) {	
			if($album->name != trim($_POST['albumname'])) {
				if(!empty($_POST['albumname']) && validate($_POST['albumname'], "alnum")) {
					$album->name = trim($_POST['albumname']);
				} else {
					$session->message("Name must be an alphanumeric string and not empty.");
					redirect_to('update_album.php?id='.$_GET['id']);
				}
				if($album->update_album()) {
					$session->message("Album Updated successfully.");
					redirect_to($session->previous_location.$session->query_string);
				} else {
					$session->message("Could not update the album.");
					redirect_to($session->previous_location.$session->query_string);
				}
			} else {
				$session->message("No changes were made.");
				redirect_to($session->previous_location.$session->query_string);
			}
		} else {
			$session->message("Could not update the album, operation not permitted.");
			redirect_to($session->previous_location.$session->query_string);
		}		
	} 
} elseif(!empty($_POST['checkbox'])) {
	$count = 0;
	foreach($_POST['checkbox'] as $photoid) {
		$photo = Photograph::find_by_id($photoid);
		if($photo && ($photo->user == $session->user_id || $session->privilege_level == "admin")) {
			$photo->album = NULL;
			if($photo->update_photo()) {
				$count++;
			} else {
				$session->message("The photo/photos could not be removed from the album.");
				redirect_to($session->previous_location.$session->query_string);
			}
		} else {
			$session->message("The photo/photos could not be removed, operation not permitted.");
			redirect_to($session->previous_location.$session->query_string);
		}
	}
	if($count == 1) {
		$session->message("The photo {$photo->photoname} was removed from the album.");
	} else {
		$session->message("{$count} photos were removed from the album.");
	}
	redirect_to('update_album.php?id='.$_GET['id']);
	if(isset($database)) { $database->close_connection(); }
}

// DEFAULT SHOWING THE PHOTO
if(!empty($_GET['id']) && validate($_GET['id'], "digit")) {
	$album = Album::find_by_id($_GET['id']);
	if($album) {
		$total_count = $album->photos_count();
		$pagination = new Pagination($page, $per_page, $total_count);
		$offset = $pagination->offset();	
		$photos = $album->photographs($per_page, $offset);
	} else {
		$session->message("Album not found.");
		redirect_to($session->previous_location.$session->query_string);
	}
} else {
    $session->message("No album ID was provided.");
    redirect_to($session->previous_location);
}

$user = User::find_by_id($album->user);

include_layout_template('admin_header.php', "admin"); 
?>
		<div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<img class="h2-icon" src="images/albums_icon.png"><h2 class="left">Update Album</h2>
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
					<div id="update-album-box">
						<form action="update_album.php?id=<?php echo $album->id; ?>" method="post">
							<input type="text" name="albumname" value="<?php echo $album->name; ?>">
							<input type="submit" name="update" value="Update">
						</form>
					</div>
				</div>
				<div class="admin-box">
					<img class="h2-icon" src="images/photographs_icon.png"><h2 class="left">Photos</h2>
					<div id="admin-box-header">
					</div>
					<div class="line"></div>
				</div>
<?php if(!empty($photos)) { ?>
				<div class="squares-page">
					<form action="update_album.php?id=<?php echo $album->id; ?>" method="post">
						<input type="submit" name="remove" value="Remove selected">
						<ul id="photolist">
<?php foreach($photos as $photo): ?>
							<li>
								<div class="thumbnailsquare">
									<a href="../photo.php?id=<?php echo $photo->id; ?>"><img src="../showphoto.php?id=<?php echo $photo->id; ?>&amp;type=square"></a>
									<input class="thumbnailsquare-checkbox" type="checkbox" name="checkbox[]" value="<?php echo $photo->id ?>">
								</div>
							</li>
<?php endforeach; ?>
						</ul>
					</form>
				</div>
				<div id="pagination" style="clear:both;">
<?php echo "				".$pagination->display($getdata="id={$album->id}")."\n"; ?>
				</div>
<?php } else { echo "				No images were found\n"; } ?>
			</div>
		</div>
<?php
include_layout_template('admin_footer.php', "admin"); 
?>
