<?php
require_once('../includes/initialize.php');
if (!$session->is_logged_in()) { redirect_to("login.php"); }

$session->save_query_string();
$session->save_location(basename(__FILE__));

// PAGINATION
$page = 1;
$per_page = 10;
if(!empty($_GET['page']) && validate($_GET['page'], "digit")) {
	$page = $_GET['page'];
}

// DELETE & UPDATE FUNCTIONS
if(!empty($_GET['delete']) && validate($_GET['delete'], "digit")) {
	$photo = Photograph::find_by_id($_GET['delete']);
	if($photo && $photo->user == $session->user_id) {
		if($photo->destroy()) {
			$session->message("The photo {$photo->photoname} was deleted.");
			redirect_to('photos.php'.$session->query_string);
		} else {
			$session->message("The photo could not be deleted.");
			redirect_to('photos.php'.$session->query_string);
		}
	} else {
		$session->message("The photo could not be deleted, operation not permitted.");
		redirect_to('photos.php'.$session->query_string);
	}
	if(isset($database)) { $database->close_connection(); }
} elseif(!empty($_POST['update']) && validate($_POST['update'], "digit")) {
	$photo = Photograph::find_by_id($_POST['update']);
	if($photo && $photo->user == $session->user_id) {
		if($photo->album != $_POST['album'][$photo->id]) {
			if(!empty($_POST['album'][$photo->id]) && validate($_POST['album'][$photo->id], "digit")) {
				$photo->album = $_POST['album'][$photo->id];
			} elseif(empty($_POST['album'][$photo->id])) {
				$photo->album = NULL;
			} else {
				$session->message("Album ID not valid.");
				redirect_to('photos.php'.$session->query_string);
			}
			if($photo->update_photo()) {
				$session->message("The photo {$photo->photoname} album was updated.");
				redirect_to('photos.php'.$session->query_string);
			} else {
				$session->message("The photo album could not be updated.");
				redirect_to('photos.php'.$session->query_string);
			}
		}
	} else {
		$session->message("The photo album could not be updated, operation not permitted.");
		redirect_to('photos.php'.$session->query_string);
	}
	if(isset($database)) { $database->close_connection(); }
} elseif(isset($_POST['delselected']) && !empty($_POST['checkbox'])) {
	$count = 0;
	foreach($_POST['checkbox'] as $photoid) {
		$photo = Photograph::find_by_id($photoid);
		if($photo && $photo->user == $session->user_id) {
			if($photo->destroy()) {
				$count++;
			} else {
				$session->message("The photo {$photo->photoname} could not be deleted.");
				redirect_to('photos.php'.$session->query_string);
			}
		} else {
			$session->message("The photo could not be deleted, operation not permitted.");
			redirect_to('photos.php'.$session->query_string);
		}
	}
	if($count == 1) {
		$session->message("The photo {$photo->photoname} was deleted.");
	} else {
		$session->message("{$count} photos were deleted.");
	}
	redirect_to('photos.php'.$session->query_string);
	if(isset($database)) { $database->close_connection(); }
} elseif(isset($_POST['upselected']) && !empty($_POST['checkbox'])) {
	$count = 0;
	foreach($_POST['checkbox'] as $photoid) {
		$photo = Photograph::find_by_id($photoid);
		if($photo && $photo->user == $session->user_id) {
			if($photo->album != $_POST['album'][$photo->id]) {
				if(!empty($_POST['album'][$photo->id]) && validate($_POST['album'][$photo->id], "digit")) {
					$photo->album = $_POST['album'][$photo->id];
				} elseif(empty($_POST['album'][$photo->id])) {
					$photo->album = NULL;
				} else {
					$session->message("Album ID not valid.");
					redirect_to('photos.php'.$session->query_string);
				}
				if($photo->update_photo()) {
					$count++;
				} else {
					$session->message("The photo {$photo->photoname} album could not be updated.");
					redirect_to('photos.php'.$session->query_string);
				}
			}
		} else {
			$session->message("The photo album could not be updated, operation not permitted.");
			redirect_to('photos.php'.$session->query_string);
		}
	}
	if($count == 1) {
		$session->message("The photo {$photo->photoname} album was updated.");
	} else {
		$session->message("{$count} photos albums were updated.");
	}
	redirect_to('photos.php'.$session->query_string);
	if(isset($database)) { $database->close_connection(); }
}

// SEARCH FUNCTIONS
if(!empty($_GET['search'])) {
	$search = $_GET['search'];
	if(validate($search, "alnum")) {
		$total_count = Photograph::count_by_search($session->user_id, $search, array("photoname", "caption"));
		$pagination = new Pagination($page, $per_page, $total_count);
		$offset = $pagination->offset();
		$find = "search=".$search;
		$photos = Photograph::search_by_word($session->user_id, $search, array("photoname", "caption"), $per_page, $offset);
	} else {
		$session->message("Search word must be an alphanumeric string.");
		redirect_to('photos.php');
	}	
} elseif(!empty($_GET['datefrom']) && !empty($_GET['dateto'])) {
	$datefrom = $_GET['datefrom'];
	$dateto = $_GET['dateto'];
	if(validate($datefrom, "date") && validate($dateto, "date")) {
		$total_count = Photograph::count_by_date($session->user_id, $datefrom, $dateto);
		$pagination = new Pagination($page, $per_page, $total_count);
		$offset = $pagination->offset();
		$find = "datefrom=".$datefrom."&dateto=".$dateto;
		$photos = Photograph::search_by_date($session->user_id, $datefrom, $dateto, $per_page, $offset);
	} else {
		$session->message("Date format incorrect, must be YYYY-MM-DD.");
		redirect_to('photos.php');
	}
// DEFAULT IF NOT VALID GET OR POST
} else {
	$total_count = Photograph::count_by_field_id($session->user_id, "user");
	$pagination = new Pagination($page, $per_page, $total_count);
	$offset = $pagination->offset();	
	$photos = Photograph::find($session->user_id, $per_page, $offset);
}

$albums = Album::find($session->user_id);

include_layout_template('admin_header.php', "admin");
?>
		<div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<img class="h2-icon" src="images/photographs_icon.png"><h2 class="left">Photographs</h2>
					<div id="admin-box-header">
						<div id="search">
							<select id="search-selector">
								<option value="word">Word</option>
								<option value="date">Date</option>
							</select>
							<form  id="search-by-date" method="get" action="photos.php">
								<input type="submit" value="From">
								<input class="datepicker" name="datefrom" type="text" value="" placeholder="YYYY-MM-DD">
								<input type="submit" value="To">
								<input class="datepicker" name="dateto" type="text" value="" placeholder="YYYY-MM-DD">
								<input type="submit" value="Search" style="width:80px; margin:0 0 0 10px;">
							</form>							
							<form  id="search-by-keyword" method="get" action="photos.php">
								<input type="text" name="search" value="<?php echo $search; ?>">							
								<input type="submit" value="Search">
							</form>	
						</div>
					</div>
					<div class="line"></div>
					<a href="index.php">&laquo; Home</a><a style="float:right;" href="upload.php">Upload a new photograph</a>
					<div class="info-box">
<?php if(!empty($message)) { echo "					".output_message($message)."\n"; } ?>
<?php if(!empty($datefrom) && !empty($dateto)) { echo "					<p>Results from ".$datefrom." to ".$dateto."</p>\n"; } ?>
					</div>
				</div>
<?php if(!empty($photos)) { ?>
				<div class="admin-box">
					<form action="photos.php" method="post">
						<table class="table-list">
							<tr>
								<th>Image</th>
								<th class="th-size">Size</th>
								<th class="th-uploaded">Uploaded</th>
								<th class="th-album">Album</th>
								<th class="th-name">Name</th>
								<th class="th-caption">Caption</th>
								<th class="th-checkbox"><input type="checkbox" id="selectall"></th>
								<th class="th-update"><input type="submit" name="upselected" value="Update"></th>
								<th><input type="submit" onclick="return confirm('The selected photos will be deleted.');" name="delselected" value="Delete"></th>
							</tr>
<?php foreach($photos as $photo): ?>
							<tr>
								<td class="table-td-img"><div class="thumbnailsquare"><a href="update_photo.php?id=<?php echo $photo->id ?>"><img src="../showphoto.php?id=<?php echo $photo->id; ?>&amp;type=small"></a></div></td>
								<td class="td-size"><?php echo $photo->size_as_text(); ?></td>
								<td class="td-uploaded"><?php echo date('Y-m-d H:i', $photo->created); ?></td>
								<td class="td-album">
									<select class="photos-album-selector" name="album[<?php echo $photo->id; ?>]">
<?php
if(!empty($photo->album)) {
	foreach($albums as $album) {
		if($album->id == $photo->album) {
			echo "										<option selected=\"selected\" value=\"".$album->id."\">".$album->name."</option>\n";
			echo "										<option value=\"\">None</option>\n";
		}
	} 
} else {
	echo "										<option selected=\"selected\" value=\"\">None</option>\n";
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
								</td>
								<td class="td-name"><?php echo $photo->photoname; ?></td>
								<td class="td-caption"><?php echo $photo->caption; ?></td>
								<td class="td-checkbox"><input class="checkbox" type="checkbox" name="checkbox[]" value="<?php echo $photo->id ?>"></td>
								<td class="td-update">
									<button class="photos-update-button" type="submit" name="update" value="<?php echo $photo->id; ?>" alt="Update" title="Update">
										<img class="update-icon" src="images/update_icon.png" alt="Update" title="Update">
									</button>
								</td>
								<td><a href="photos.php?delete=<?php echo $photo->id; ?>"><img class="delete-icon" src="images/delete_icon.png" alt="Delete" title="Delete"></a></td>
							</tr>
<?php endforeach; ?>
							<tr class="spacer-tr"></tr>
							<tr>
								<td id="total-td" colspan="9">Total Records: <?php echo $total_count; ?></td>
							</tr>
						</table>
					</form>
					<div id="pagination" style="clear:both;">
<?php echo "						".$pagination->display($find)."\n"; ?>
					</div>
				</div>
<?php } else { echo "				No images were found\n"; } ?>
			</div>
		</div>
<?php
include_layout_template('admin_footer.php', "admin");
?>
