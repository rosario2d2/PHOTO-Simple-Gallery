<?php

session_name("PHOTOSESSID");
session_start();

if(isset($_SESSION['view_images'])) {
	require_once('includes/initialize.php');

	header('Cache-Control: private');
	header('Pragma: private');
	header('Expires: '.gmdate('r', strtotime("2 day")));

    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
      // 304 Not Modified
      header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
      exit;
	} 

	$id = $_GET['id'];
	$type = $_GET['type'];
	$types = array("small", "medium", "large", "square");

	if(!empty($id) && validate($id, "digit")) {
		$photo = Photograph::find_by_id($id);
        if($photo) {
            // 200 OK
			header('Last-Modified: '.gmdate('r', time()), true, 200);
			header('Content-Type: '.$photo->type);
			if(isset($type) && in_array($type, $types)) {	
				$thumbnail = $photo->thumbnail_path($type);			
				readfile($thumbnail);
			} else {
				$image = $photo->image_path();
				readfile($image);
			}
		} else {
			// 404 Not Found
			header('HTTP/1.1 404 Not Found');
			exit;
		}
	} else {
		// 403 Forbidden
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
} else {
    // 403 Forbidden
	header('HTTP/1.1 403 Forbidden, No soup for you!');
	exit;
}

?>
