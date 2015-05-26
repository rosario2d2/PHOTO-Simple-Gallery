<?php
if(file_exists('install.php')) { header("Location: install.php"); }
require_once('includes/initialize.php');

$session->save_query_string();
$session->save_location(basename(__FILE__));

// PAGINATION
$page = 1;
$per_page = 24;
if(!empty($_GET['page']) && validate($_GET['page'], "digit")) {
	$page = $_GET['page'];
}

// PHOTOGRAPHS
$total_count = Photograph::count_all();
$pagination = new Pagination($page, $per_page, $total_count);
$offset = $pagination->offset();	
$photos = Photograph::find_all($per_page, $offset);

include_layout_template('header.php'); 
?>
		<div id="main">
			<div id="masonry">
				<div class="grid-sizer"></div>
				<div class="gutter-sizer"></div>
				<div id="images">
<?php foreach($photos as $photo): ?>
				<div class="item">
					<a href="photo.php?id=<?php echo $photo->id; ?>"><img src="showphoto.php?id=<?php echo $photo->id; ?>&amp;type=medium" alt=""></a>
				</div>
<?php endforeach; ?>
				</div>
			</div>
			<div id="pagination" style="clear:both;">
<?php echo "				".$pagination->display()."\n"; ?>
			</div>
		</div>
<?php 
include_layout_template('footer.php'); 
?>
