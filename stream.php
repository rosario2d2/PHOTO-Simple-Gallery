<?php
require_once('includes/initialize.php');

$session->save_query_string();
$session->save_location(basename(__FILE__));

// PAGINATION
$page = 1;
$per_page = 8;
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
			<div id="stream-panel">
<?php foreach($photos as $photo): ?>
				<div class="stream">
					<div class="stream-box">
						<a href="photo.php?id=<?php echo $photo->id; ?>"><img src="showphoto.php?id=<?php echo $photo->id; ?>&amp;type=large" alt=""></a>
					</div>
					<div class="stream-text-container">
						<div class="stream-text">
							<div class="left stream-date"><?php echo date('F j, Y', $photo->created); ?></div>
							<div class="right stream-caption"><?php echo $photo->caption; ?></div>
						</div>
					</div>
				</div>
<?php endforeach; ?>
			</div>
			<div id="pagination" style="clear:both;">
<?php echo "				".$pagination->display()."\n"; ?>
			</div>
		</div>
<?php 
include_layout_template('footer.php'); 
?>

