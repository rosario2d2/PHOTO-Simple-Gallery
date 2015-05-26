<?php
require_once('../includes/initialize.php');
if (!$session->is_logged_in()) { redirect_to("login.php"); }


if(isset($_FILES['files'])) {
	$count = 0;
	$valid_file_extensions = array(".jpg", ".jpeg", ".gif", ".png");
	
	foreach($_FILES['files']['tmp_name'] as $key => $tmp_name) {
		if(file_exists($tmp_name)) {
			if (getimagesize($tmp_name) !== false) {
				$file_extension = strrchr($_FILES['files']['name'][$key], ".");
				if (in_array(strtolower($file_extension), $valid_file_extensions)) {
					$photo = new Photograph();
					
					if(!empty($_POST['photoname'][$key]) && validate($_POST['photoname'][$key], "alnum")) {
						$photo->photoname = trim($_POST['photoname'][$key]);
					} elseif(empty($_POST['photoname'][$key])) {
						$photo->photoname = "";
					} else {
						header('HTTP/1.1 500 Internal Server Error');
						header('Content-Type: application/json; charset=UTF-8');
						echo json_encode("{$count} photos saved, stopped. Image {$_FILES['files']['name'][$key]} name must be an alphanumeric string.");
						exit;
					}
					if(!empty($_POST['caption'][$key]) && validate($_POST['caption'][$key], "alnum")) {
						$photo->caption = trim($_POST['caption'][$key]);
					} elseif(empty($_POST['caption'][$key])) {
						$photo->caption = "";
					} else {
						header('HTTP/1.1 500 Internal Server Error');
						header('Content-Type: application/json; charset=UTF-8');
						echo json_encode("{$count} photos saved, stopped. Image {$_FILES['files']['name'][$key]} caption must be an alphanumeric string.");
						exit;
					}
					if(!empty($_POST['album']) && validate($_POST['album'], "digit")) {
						$photo->album = $_POST['album'];
					} elseif(empty($_POST['album'])) {
						$photo->album = NULL;
					} else {
						header('HTTP/1.1 500 Internal Server Error');
						header('Content-Type: application/json; charset=UTF-8');
						echo json_encode("{$count} photos saved, stopped. Album ID not valid.");
						exit;
					}
					
					$photo->created = time();
					$photo->user = $session->user_id;
					$photo->attach_files($_FILES['files'], $key);
					
					if($photo->save_photo()) {
						$count++;
						header("HTTP/1.1 200 OK");	   
					} else {
						log_action("error", "User \"{$session->username}\" - Error saving photos: {$photo->errors}");
						header('HTTP/1.1 500 Internal Server Error');
						header('Content-Type: application/json; charset=UTF-8');
						echo json_encode("{$count} photos saved, stopped. The photo {$_FILES['files']['name'][$key]} could not be saved.");
						exit;
					}
				} else {
					header('HTTP/1.1 500 Internal Server Error');
					header('Content-Type: application/json; charset=UTF-8');
					echo json_encode("{$count} photos saved, stopped. Image {$_FILES['files']['name'][$key]} type is not allowed.");
					exit;
				}
			} else {
				header('HTTP/1.1 500 Internal Server Error');
				header('Content-Type: application/json; charset=UTF-8');
				echo json_encode("{$count} photos saved, stopped. File {$_FILES['files']['name'][$key]} is not an image.");
				exit;
			}
		} else {
			header('HTTP/1.1 500 Internal Server Error');
			header('Content-Type: application/json; charset=UTF-8');
			echo json_encode("No file selected.");
			exit;
		}
	}
}

$albums = Album::find($session->user_id);

include_layout_template('admin_header.php', "admin"); 
?>
		<script>
			if (!window.File && !window.FileReader && !window.FileList && !window.Blob) {
				alert('The File APIs are not fully supported in this browser.');
			}
		</script>
		<div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<img class="h2-icon" src="images/photographs_icon.png"/><h2 class="left">Photographs Upload</h2>
					<div id="admin-box-header">
					</div>
					<div class="line"></div>
					<a href="photos.php<?php echo $session->query_string; ?>">&laquo; Back</a>
<?php if(!empty($message)) { echo "					".output_message($message)."\n"; } ?>
				</div>
				<div class="admin-box">
					<div class="info-box">
						<p>Choose a file by clicking "+ Add Photo".</p>
						<p>Multiple images at the same time can be selected.</p>
						<p>Allowed file types: jpg gif png.</p>
						<p>Max upload size: <?php echo ini_get('upload_max_filesize')."B"; ?></p>
					</div>
					<div id="upload">
						<form id="upload-form" action="upload.php" enctype="multipart/form-data" method="POST">
							<div id="input-file-cont">
								<input id="files" type="file" name="uploaded[]" style="width:200px;" multiple>
							</div>
							<input type="submit" name="submit" value="" id="upload-submit">
							<select id="album-select" name="album">
								<option value="">Choose an album</option>
<?php foreach($albums as $album): ?>
								<option value="<?php echo $album->id; ?>"><?php echo $album->name; ?></option>
<?php endforeach; ?>
							</select>
							<div id="progressbar">
								<div id="progresslabel"></div>
							</div>
							<div id="filelist" style="width:100%; margin-top:10px; float:left;">
								<div id="totalfiles" style="font-weight:bold; font-size:14px;"></div>
								<table class="table-list">
									<tbody id="selected-files">
										
									</tbody>
								</table>
							</div>				
						</form>	
					</div>
				</div>
			</div>
		</div>
<?php 
include_layout_template('admin_footer.php', "admin"); 
?>

