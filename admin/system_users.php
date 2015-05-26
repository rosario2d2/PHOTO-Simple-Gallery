<?php
require_once('../includes/initialize.php');
if (!$session->is_logged_in() || $session->privilege_level !== "admin") { redirect_to("login.php"); }

$session->save_query_string();

// PAGINATION
$page = 1;
$per_page = 10;
if(!empty($_GET['page']) && validate($_GET['page'], "digit")) {
	$page = $_GET['page'];
}

// DELETE & UPDATE FUNCTIONS
if(!empty($_GET['delete']) && validate($_GET['delete'], "digit")) {
	$albumcount = 0;
	$photoscount = 0;
	$user = User::find_by_id($_GET['delete']);
	if($user) {
		$albums = Album::find($user->id);
		if($albums) {
			foreach($albums as $album) {
				if($album->user == $user->id) {
					if($album->destroy()) {
						$albumcount++;
					} else {
						$session->message("Album {$album->name}, property of user {$user->username}, could not be deleted. User delete stopped.");
						redirect_to('system_users.php'.$session->query_string);
					}
				}
			}
		}
		$photos = Photograph::find($user->id);
		if($photos) {
			foreach($photos as $photo) {
				if($photo->user == $user->id) {
					if($photo->destroy()) {
						$photoscount++;
					} else {
						$session->message("Photo {$photo->photoname}, property of user {$user->username}, could not be deleted. User delete stopped.");
						redirect_to('system_users.php'.$session->query_string);
					}
				}
			}
		}
		if($user->destroy()) {
			log_action("info", "The user \"{$user->username}\", {$albumcount} albums and {$photoscount} photos were deleted.");
			$session->message("The user {$user->username}, {$albumcount} albums and {$photoscount} photos were deleted.");
			redirect_to('system_users.php'.$session->query_string);
		} else {
			log_action("error", "User \"{$session->username}\" - Error deleting user \"{$user->username}\": {$user->errors}");
			$session->message("The user could not be deleted.");
			redirect_to('system_users.php'.$session->query_string);
		}
	} else {
		$session->message("The user could not be deleted, operation not permitted.");
		redirect_to('system_users.php'.$session->query_string);
	}
	if(isset($database)) { $database->close_connection(); }
} elseif(!empty($_POST['update']) && validate($_POST['update'], "digit")) {
	$user = User::find_by_id($_POST['update']);
	if($user) {
		if($user->privilege_level != $_POST['privilege'][$user->id]) {
			if(!empty($_POST['privilege'][$user->id]) && ($_POST['privilege'][$user->id] == "user" || $_POST['privilege'][$user->id] == "admin")) {
				$user->privilege_level = $_POST['privilege'][$user->id];
			} else {
				$session->message("Operation not valid.");
				redirect_to('system_users.php'.$session->query_string);
			}
			if($user->update_user()) {
				log_action("info", "User {$user->username} privilege level was updated to {$user->privilege_level}.");
				$session->message("User {$user->username} privilege level was updated.");
				redirect_to('system_users.php'.$session->query_string);
			} else {
				$session->message("User privilege level could not be updated.");
				redirect_to('system_users.php'.$session->query_string);
			}
		}
	} else {
		$session->message("User privilege level could not be updated, operation not permitted.");
		redirect_to('system_users.php'.$session->query_string);
	}
	if(isset($database)) { $database->close_connection(); }
} elseif(isset($_POST['delselected']) && !empty($_POST['checkbox'])) {
	$albumcount = 0;
	$photoscount = 0;
	$count = 0;
	foreach($_POST['checkbox'] as $userid) {
		$user = User::find_by_id($userid);
		if($user) {
			$albums = Album::find($user->id);
			if($albums) {
				foreach($albums as $album) {
					if($album->user == $user->id) {
						if($album->destroy()) {
							$albumcount++;
						} else {
							$session->message("Album {$album->name}, property of user {$user->username}, could not be deleted. Users delete stopped.");
							redirect_to('system_users.php'.$session->query_string);
						}
					}
				}
			}
			$photos = Photograph::find($user->id);
			if($photos) {
				foreach($photos as $photo) {
					if($photo->user == $user->id) {
						if($photo->destroy()) {
							$photoscount++;
						} else {
							$session->message("Photo {$photo->photoname}, property of user {$user->username}, could not be deleted. Users delete stopped.");
							redirect_to('system_users.php'.$session->query_string);
						}
					}
				}
			}
			if($user->destroy()) {
				$count++;
			} else {
				$session->message("The user {$user->username} could not be deleted. Multiple delete stopped.");
				redirect_to('system_users.php'.$session->query_string);
			}
		} else {
			$session->message("The user could not be deleted, operation not permitted.");
			redirect_to('system_users.php'.$session->query_string);
		}	
	}
	if($count == 1) {
		log_action("info", "The user \"{$user->username}\", {$albumcount} albums and {$photoscount} photos were deleted.");
		$session->message("The user {$user->username}, {$albumcount} albums and {$photoscount} photos were deleted.");
	} else {
		log_action("info", "{$count} Users, {$albumcount} Albums and {$photoscount} Photos were deleted.");
		$session->message("{$count} Users, {$albumcount} Albums and {$photoscount} Photos were deleted.");
	}
	redirect_to('system_users.php'.$session->query_string);
	if(isset($database)) { $database->close_connection(); }
} elseif(isset($_POST['upselected']) && !empty($_POST['checkbox'])) {
	$count = 0;
	foreach($_POST['checkbox'] as $userid) {
		$user = User::find_by_id($userid);
		if($user) {
			if($user->privilege_level != $_POST['privilege'][$user->id]) {
				if(!empty($_POST['privilege'][$user->id]) && ($_POST['privilege'][$user->id] == "user" || $_POST['privilege'][$user->id] == "admin")) {
					$user->privilege_level = $_POST['privilege'][$user->id];
				} else {
					$session->message("Operation not valid. Multiple update stopped.");
					redirect_to('system_users.php'.$session->query_string);
				}
				if($user->update_user()) {
					$count++;
				} else {
					$session->message("User {$user->username} privilege level could not be updated. MUltiple update stopped.");
					redirect_to('system_users.php'.$session->query_string);
				}
			}
		} else {
			$session->message("User privilege level could not be updated, operation not permitted.");
			redirect_to('system_users.php'.$session->query_string);
		}
	}
	if($count == 1) {
		log_action("info", "User {$user->username} privilege level was updated to {$user->privilege_level}.");
		$session->message("User {$user->username} privilege level was updated.");
	} else {
		log_action("info", "{$count} Users privilege level were updated.");
		$session->message("{$count} Users privilege level were updated.");
	}
	redirect_to('system_users.php'.$session->query_string);
	if(isset($database)) { $database->close_connection(); }
}

// SEARCH FUNCTIONS
if(!empty($_GET['search'])) {
	$search = $_GET['search'];
	if(validate($search, "alnum")) {
		$total_count = User::count_by_search("all", $search, array("username", "first_name", "last_name"));
		$pagination = new Pagination($page, $per_page, $total_count);
		$offset = $pagination->offset();
		$find = "search=".$search;
		$users = User::search_by_word("all", $search, array("username", "first_name", "last_name"), $per_page, $offset);
	} else {
		$session->message("Search word must be an alphanumeric string.");
		redirect_to('system_photos.php');
	}	
} elseif(!empty($_GET['datefrom']) && !empty($_GET['dateto'])) {
	$datefrom = $_GET['datefrom'];
	$dateto = $_GET['dateto'];
	if(validate($datefrom, "date") && validate($dateto, "date")) {
		$total_count = User::count_by_date("all", $datefrom, $dateto);
		$pagination = new Pagination($page, $per_page, $total_count);
		$offset = $pagination->offset();
		$find = "datefrom=".$datefrom."&dateto=".$dateto;
		$users = User::search_by_date("all", $datefrom, $dateto, $per_page, $offset);
	} else {
		$session->message("Date format incorrect, must be YYYY-MM-DD.");
		redirect_to('system_photos.php');
	}
// DEFAULT IF NOT VALID GET OR POST
} else {
	$total_count = User::count_all();
	$pagination = new Pagination($page, $per_page, $total_count);
	$offset = $pagination->offset();	
	$users = User::find_all($per_page, $offset);
}

include_layout_template('admin_header.php', "admin");
?>
		<div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<img class="h2-icon" src="images/user_icon.png"><h2 class="left">System Users</h2>
					<div id="admin-box-header">
						<div id="search">
							<select id="search-selector">
								<option value="word">Word</option>
								<option value="date">Date</option>
							</select>
							<form  id="search-by-date" method="get" action="system_users.php">
								<input type="submit" value="From">
								<input class="datepicker" name="datefrom" type="text" value="" placeholder="YYYY-MM-DD">
								<input type="submit" value="To">
								<input class="datepicker" name="dateto" type="text" value="" placeholder="YYYY-MM-DD">
								<input type="submit" value="Search" style="width:80px; margin:0 0 0 10px;">
							</form>							
							<form  id="search-by-keyword" method="get" action="system_users.php">
								<input type="text" name="search" value="<?php echo $search; ?>">							
								<input type="submit" value="Search">
							</form>	
						</div>
					</div>
					<div class="line"></div>
					<a href="index.php">&laquo; Home</a><a class="right" href="new_user.php">Create a new user</a>
					<div class="info-box">
<?php if(!empty($message)) { echo "					".output_message($message)."\n"; } ?>
<?php if(!empty($datefrom) && !empty($dateto)) { echo "					<p>Results from ".$datefrom." to ".$dateto."</p>\n"; } ?>
					</div>
				</div>
<?php if(!empty($users)) { ?>
				<div class="admin-box">
					<form action="system_users.php" method="post">
						<table class="table-list table-list-users">
							<tr>
								<th>Username</th>
								<th class="th-users-created">Created</th>
								<th>Privilege Level</th>
								<th class="th-users-fname">First Name</th>
								<th class="th-users-lname">Last Name</th>
								<th class="th-users-albums">Albums</th>
								<th class="th-users-photos">Photographs</th>
								<th><input type="checkbox" id="selectall"></th>
								<th><input type="submit" name="upselected" value="Update"></th>
								<th><input type="submit" onclick="return confirm('The selected users, albums and photos associated, will be permanently deleted.');" name="delselected" value="Delete"></th>
							</tr>
<?php foreach($users as $user): ?>
<?php if($user->id != $session->user_id): ?>
							<tr>
								<td><?php echo $user->username; ?></td>
								<td class="td-users-created"><?php echo date('Y-m-d H:i', $user->created); ?></td>
								<td>
									<select class="users-privilege-selector" name="privilege[<?php echo $user->id; ?>]">
										<option value="<?php echo $user->privilege_level; ?>"><?php echo ucfirst($user->privilege_level); ?></option>
<?php if($user->privilege_level == "admin"): ?>
										<option value="user">User</option>
<?php else: ?>
										<option value="admin">Admin</option>
<?php endif; ?>
									</select>
								</td>
								<td class="td-users-fname"><?php echo $user->first_name ? $user->first_name : "--"; ?></td>
								<td class="td-users-lname"><?php echo $user->last_name ? $user->last_name : "--"; ?></td>
								<td class="td-users-albums"><?php echo Album::count($user->id); ?></td>
								<td class="td-users-photos"><?php echo Photograph::count($user->id); ?></td>
								<td><input class="checkbox" type="checkbox" name="checkbox[]" value="<?php echo $user->id ?>"></td>
								<td>
									<button class="photos-update-button" type="submit" name="update" value="<?php echo $user->id; ?>" alt="Update" title="Update">
										<img class="update-icon" src="images/update_icon.png" alt="Update" title="Update">
									</button>
								</td>
								<td><a href="system_users.php?delete=<?php echo $user->id; ?>" onclick="return confirm('The selected user, albums and photos associated, will be permanently deleted.');"><img class="delete-icon" src="images/delete_icon.png" alt="Delete" title="Delete"></a></td>
							</tr>
<?php else: ?>
							<tr>
								<td><?php echo $user->username; ?></td>
								<td class="td-users-created"><?php echo date('Y-m-d H:i', $user->created); ?></td>
								<td><div id="userlist-self">Admin</div></td>
								<td class="td-users-fname"><?php echo $user->first_name ? $user->first_name : "--"; ?></td>
								<td class="td-users-lname"><?php echo $user->last_name ? $user->last_name : "--"; ?></td>
								<td class="td-users-albums"><?php echo Album::count($user->id); ?></td>
								<td class="td-users-photos"><?php echo Photograph::count($user->id); ?></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
<?php endif; ?>
<?php endforeach; ?>
							<tr>
								<td id="total-td" colspan="10">Total Records: <?php echo $total_count; ?></td>
							</tr>
						</table>
					</form>
					<div id="pagination" style="clear:both;">
<?php echo "						".$pagination->display($find)."\n"; ?>
					</div>
				</div>
<?php } else { echo "				No users were found\n"; } ?>
			</div>
		</div>
<?php
include_layout_template('admin_footer.php', "admin");
?>
