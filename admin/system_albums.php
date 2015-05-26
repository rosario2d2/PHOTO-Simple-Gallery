<?php
require_once('../includes/initialize.php');
if (!$session->is_logged_in() || $session->privilege_level !== "admin") { redirect_to("login.php"); }

$session->save_query_string();
$session->save_location(basename(__FILE__));

// PAGINATION
$page = 1;
$per_page = 10;
if(!empty($_GET['page']) && validate($_GET['page'], "digit")) {
	$page = $_GET['page'];
}

// DELETE FUNCTIONS
if(!empty($_POST['singledelete']) && validate($_POST['singledelete'], "digit")) {
	$album = Album::find_by_id($_POST['singledelete']);
	if($album) {
		if($_POST['albumphoto'][$_POST['singledelete']] == "album") {
			if($album->destroy()) {
				$session->message("The album {$album->name} was deleted.");
				redirect_to('system_albums.php'.$session->query_string);
			} else {
				$session->message("The album could not be deleted.");
				redirect_to('system_albums.php'.$session->query_string);
			}
		} elseif($_POST['albumphoto'][$_POST['singledelete']] == "all") {
			$count = 0;
			$photos = $album->photographs();
			if($photos) {
				foreach($photos as $photo) {
					if($photo->destroy()) {
						$count++;
					} else {
						$session->message("The photo {$photo->filename} could not be deleted.");
						redirect_to('system_albums.php'.$session->query_string);
					}
				}
			}
			if($album->destroy()) {
				if($count == 0) {
					$session->message("The album {$album->name} was deleted.");
				} else {
					$session->message("The album {$album->name} and {$count} photos were deleted.");
				}
				redirect_to('system_albums.php'.$session->query_string);
			} else {
				$session->message("The album could not be deleted.");
				redirect_to('system_albums.php'.$session->query_string);
			}
		} else {
			$session->message("Operation not valid.");
			redirect_to('system_albums.php');
		}
	} else {
		$session->message("The album could not be deleted, operation not permitted.");
		redirect_to('system_albums.php'.$session->query_string);
	}
	if(isset($database)) { $database->close_connection(); }
} elseif(!empty($_POST['checkbox'])) {
	$count = 0;
	$photosnumber = 0;
	foreach($_POST['checkbox'] as $albumid) {
		$album = Album::find_by_id($albumid);
		if($album) {
			if($_POST['albumphoto'][$albumid] == "album") {
				if($album->destroy()) {
					$count++;
				} else {
					$session->message("The album {$album->name} could not be deleted.");
					redirect_to('system_albums.php'.$session->query_string);
				}
			} elseif($_POST['albumphoto'][$albumid] == "all") {
				$photos = $album->photographs();
				if($photos) {
					foreach($photos as $photo) {
						if($photo->destroy()) {
							$photosnumber++;
						} else {
							$session->message("The photo {$photo->photoname} could not be deleted.");
							redirect_to('system_albums.php'.$session->query_string);
						}
					}
				}
				if($album->destroy()) {
					$count++;
				} else {
					$session->message("The album {$album->name} could not be deleted.");
					redirect_to('system_albums.php'.$session->query_string);
				}
			} else {
				$session->message("Operation not valid.");
				redirect_to('system_albums.php');
			}
		} else {
			$session->message("The album could not be deleted, operation not permitted.");
			redirect_to('system_albums.php'.$session->query_string);
		}
	}
	if($count == 1) {
		if($photosnumber >= 1) {
			$session->message("The album {$album->name} and {$photosnumber} photos were deleted.");
		} else {
			$session->message("The album {$album->name} was deleted.");
		}
	} else {
		if($photosnumber >= 1) {
			$session->message("{$count} albums and {$photosnumber} photos were deleted.");
		} else {
			$session->message("{$count} albums were deleted.");
		}
	}
	redirect_to('system_albums.php'.$session->query_string);
	if(isset($database)) { $database->close_connection(); }
}

// SEARCH FUNCTIONS
if(!empty($_GET['search'])) {
	$search = $_GET['search'];
	if(validate($search, "alnum")) {
		$total_count = Album::count_by_search("all", $search, array("name"));
		$pagination = new Pagination($page, $per_page, $total_count);
		$offset = $pagination->offset();
		$find = "search=".$search;
		$albums = Album::search_by_word("all", $search, array("name"), $per_page, $offset);
	} else {
		$session->message("Search word must be an alphanumeric string.");
		redirect_to('system_albums.php');
	}	
} elseif(!empty($_GET['datefrom']) && !empty($_GET['dateto'])) {
	$datefrom = $_GET['datefrom'];
	$dateto = $_GET['dateto'];
	if(validate($datefrom, "date") && validate($dateto, "date")) {
		$total_count = Album::count_by_date("all", $datefrom, $dateto);
		$pagination = new Pagination($page, $per_page, $total_count);
		$offset = $pagination->offset();
		$find = "datefrom=".$datefrom."&dateto=".$dateto;
		$albums = Album::search_by_date("all", $datefrom, $dateto, $per_page, $offset);
	} else {
		$session->message("Date format incorrect, must be YYYY-MM-DD.");
		redirect_to('system_albums.php');
	}
// DEFAULT IF NOT VALID GET OR POST
} else {
	$total_count = Album::count_all();
	$pagination = new Pagination($page, $per_page, $total_count);
	$offset = $pagination->offset();	
	$albums = Album::find_all($per_page, $offset);
}

$users = User::find_all();

include_layout_template('admin_header.php', "admin"); 
?>
		<div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<img class="h2-icon" src="images/albums_icon.png"><h2 class="left">System Albums</h2>
					<div id="admin-box-header">
						<div id="search">
							<select id="search-selector">
								<option value="word">Word</option>
								<option value="date">Date</option>
							</select>
							<form  id="search-by-date" method="get" action="system_albums.php">
								<input type="submit" value="From">
								<input class="datepicker" name="datefrom" type="text" value="YYYY-MM-DD">
								<input type="submit" value="To">
								<input class="datepicker" name="dateto" type="text" value="YYYY-MM-DD">
								<input type="submit" value="Search" style="width:80px; margin:0 0 0 10px;">
							</form>							
							<form  id="search-by-keyword" method="get" action="system_albums.php">
								<input type="text" name="search" value="<?php echo $search; ?>">							
								<input type="submit" value="Search">
							</form>	
						</div>
					</div>
					<div class="line"></div>
					<a href="index.php">&laquo; Home</a>
					<div class="info-box">
<?php if(!empty($message)) { echo "					".output_message($message)."\n"; } ?>
<?php if(!empty($datefrom) && !empty($dateto)) { echo "					<p>Results from ".$datefrom." to ".$dateto."</p>\n"; } ?>
					</div>
				</div>
<?php if(!empty($albums)) { ?>
				<div class="admin-box">
					<form action="system_albums.php" method="post">
						<table class="table-list">
							<tr>
								<th>Update</th>
								<th class="th-username">User</th>
								<th class="th-albums-created">Created</th>
								<th class="th-albums-name">Full Name</th>
								<th><input type="checkbox" id="selectall"/></th>
								<th class="th-delete-select"><input type="submit" onclick="return confirm('The selected albums will be deleted.');" value="Delete Selected"/></th>
							</tr>
<?php 
foreach($albums as $album):
$cover = $album->cover();
$photoscount = $album->photos_count();
?>
							<tr>
								<td class="table-td-cover">
									<div class="detailsquare">	
<?php if($cover): ?>
										<a href="update_album.php?id=<?php echo $album->id; ?>"><img src="../showphoto.php?id=<?php echo $cover[0]->id; ?>&amp;type=square" alt="Update" title="Update"></a>
										<span class="long-text-hide"><?php echo $album->name; ?></span>						
										<span class="numbericon-number"><img class="numbericon" src="images/albums_img_icon.png" alt=""><?php echo $photoscount; ?></span>
<?php else: ?>
										<a href="update_album.php?id=<?php echo $album->id; ?>"><img src="images/empty_album.jpg" alt=""></a>
										<span class="long-text-hide"><?php echo $album->name; ?></span>						
										<span class="numbericon-number"><img class="numbericon" src="images/albums_img_icon.png" alt="">0</span>
<?php endif; ?>
									</div>
								</td>
<?php
foreach($users as $user) {
	if($album->user == $user->id) {
		echo "									<td class=\"td-username\">".$user->username."</td>\n";
	}
}
?>
								<td class="td-albums-created"><?php echo date('Y-m-d H:i', $album->created); ?></td>
								<td class="td-albums-name"><?php echo $album->name; ?></td>
								<td><input class="checkbox" type="checkbox" name="checkbox[]" value="<?php echo $album->id ?>"></td>
								<td>
									<select class="album-delete-selector" name="albumphoto[<?php echo $album->id; ?>]">
										<option value="album">Album</option>
										<option value="all">Album &amp; Photos</option>
									</select>
									<button class="albums-delete-button" type="submit" onclick="return confirm('The selected album will be deleted.');" name="singledelete" value="<?php echo $album->id; ?>" alt="Delete" title="Delete">
										<img class="delete-icon" src="images/delete_icon.png" alt="Delete" title="Delete">
									</button>
								</td>
							</tr>
<?php endforeach; ?>
							<tr class="spacer-tr"></tr>
							<tr>
								<td id="total-td" colspan="6">Total Records: <?php echo $total_count; ?></td>
							</tr>
						</table>
					</form>
					<div id="pagination" style="clear:both;">
<?php echo "						".$pagination->display($find)."\n"; ?>
					</div>
				</div>
<?php } else { echo "				No Albums found\n"; } ?>
			</div>
		</div>
<?php
include_layout_template('admin_footer.php', "admin");
?>
