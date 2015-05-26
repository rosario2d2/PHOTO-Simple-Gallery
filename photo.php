<?php
require_once('includes/initialize.php');

// SHOWING THE PHOTO
if(!empty($_GET['id']) && validate($_GET['id'], "digit")) {
	$photo = Photograph::find_by_id($_GET['id']);
	if(!$photo) {
		$session->message("The photo could not be located.");
		redirect_to('error.php');
	}
} else {
    $session->message("No photograph ID was provided.");
    redirect_to('error.php');
}

$user = User::find_by_id($photo->user);

include_layout_template('header.php'); 
?>
		<div id="main">
			<div id="photo-page">
				<div id="photo-box">
					<img src="showphoto.php?id=<?php echo $photo->id; ?>" alt="">
				</div>
				<div class="photo-text">
					<div class="photo-text-info">
						<a href="<?php echo $session->previous_location.$session->query_string; ?>" class="left">&laquo; Back</a>
						<span class="right"><?php echo $user->username." | ".date('F j, Y', $photo->created); ?></span>
					</div>
					<div class="photo-text-caption"><?php echo $photo->caption; ?></div>
				</div>
			</div>
		</div>
<?php 
include_layout_template('footer.php'); 
?>

