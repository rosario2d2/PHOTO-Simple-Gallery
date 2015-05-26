<?php
require_once('includes/initialize.php');

$session->save_query_string();
$session->save_location(basename(__FILE__));

// PAGINATION
$page = 1;
$per_page = 15;
if(!empty($_GET['page']) && validate($_GET['page'], "digit")) {
	$page = $_GET['page'];
}

// ALBUM PHOTOS
if(!empty($_GET['id']) && validate($_GET['id'], "digit")) {
	$album = Album::find_by_id($_GET['id']);
	if($album) {
		$total_count = $album->photos_count();
		$pagination = new Pagination($page, $per_page, $total_count);
		$offset = $pagination->offset();	
		$photos = $album->photographs($per_page, $offset);
	} else {
		$session->message("Album not found.");
		redirect_to('error.php');
	}
} else {
    $session->message("No album ID was provided.");
    redirect_to('error.php');
}

$user = User::find_by_id($album->user);

include_layout_template('header.php'); 
?>
		<div id="main">
			<div id="albums-panel">
<?php if($photos): ?>
				<div class="panel-box">
					<ul id="photolist">
<?php foreach($photos as $photo): ?>
						<li>
							<div class="thumbnailsquare">
								<a href="photo.php?id=<?php echo $photo->id; ?>"><img src="showphoto.php?id=<?php echo $photo->id; ?>&amp;type=square" alt=""></a>
							</div>
						</li>
<?php endforeach; ?>
					</ul>
					<div class="nav-details">
						<div class="left">
							<a href="albums.php">&laquo; Back</a>
						</div>
						<div class="right album-infos">
<?php echo "							".$album->name." | ".$user->username." | ".date('F j, Y', $album->created)."\n"; ?>
						</div>
					</div>
				</div>
<?php if($pagination->display()): ?>
				<div id="pagination" style="clear:both;">
<?php echo "				".$pagination->display($getdata="id={$album->id}")."\n"; ?>
				</div>
<?php endif; ?>
<?php else: ?>
				Album not found.
<?php endif; ?>
			</div>
		</div>
<?php
include_layout_template('footer.php'); 
?>
