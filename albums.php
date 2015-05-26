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

// ALBUMS
$total_count = Album::count_all();
$pagination = new Pagination($page, $per_page, $total_count);
$offset = $pagination->offset();	
$albums = Album::find_all($per_page, $offset);

include_layout_template('header.php'); 
?>
		<div id="main">
			<div id="albums-panel">
				<div class="panel-box squares-page">
<?php
foreach($albums as $album):
$cover = $album->cover();
$photoscount = $album->photos_count();
?>
					<div class="detailsquare">
<?php if($cover): ?>
						<a href="album.php?id=<?php echo $album->id; ?>"><img src="showphoto.php?id=<?php echo $cover[0]->id; ?>&amp;type=square" alt=""></a>
						<div class="detailsquare-bottom">
							<div class="long-text-hide"><?php echo $album->name; ?></div>
							<div class="numbericon-number"><?php echo $photoscount; ?> photos</div>
						</div>
<?php else: ?>
						<a href="#"><img src="public/images/empty_album.jpg" alt=""></a>
						<div class="detailsquare-bottom">
							<div class="long-text-hide"><?php echo $album->name; ?></div>
							<div class="numbericon-number">0 photos</div>
						</div>
<?php endif; ?>
					</div>
<?php endforeach; ?>
				</div>
<?php if($pagination->display()): ?>
				<div id="pagination" style="clear:both;">
<?php echo "				".$pagination->display()."\n"; ?>
				</div>
<?php endif; ?>
			</div>
		</div>
<?php 
include_layout_template('footer.php'); 
?>
