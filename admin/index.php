<?php
require_once("../includes/initialize.php");

if (!$session->is_logged_in()) { redirect_to("login.php"); }

if($_GET['logout'] == 'true') {
    $session->destroy();
    redirect_to('../index.php');
}

$photoscount = Photograph::count_by_field_id($session->user_id, "user");
$albumscount = Album::count($session->user_id);
$photos = Photograph::find($session->user_id, 6, 0);

include_layout_template('admin_header.php', "admin");
?>
        <div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<div class="block-title">
						<h3>Dashboard</h3>
					</div>
					<div id="admin-box-header">
					</div>
				</div>
				<div class="admin-box">
					<div class="dashboard">
						<div class="dashboard-greet">
							<div class="dashboard-border-box">
								Welcome <?php echo $session->username."\n"; ?>
							</div>
						</div>
						<div class="dashboard-stats">
							<div class="dashboard-box">
								<div class="dashboard-border-box">
									<div class="dashboard-box-value">
<?php echo "										".ucfirst($session->privilege_level)."\n"; ?>
									</div>
									<div class="dashboard-box-name">
										Privilege Level
									</div>
								</div>
							</div>
							<div class="dashboard-box">
								<div class="dashboard-border-box">
									<div class="dashboard-box-value">
<?php echo "										".$albumscount."\n"; ?>
									</div>
									<div class="dashboard-box-name">
										Albums
									</div>
								</div>
							</div>
							<div class="dashboard-box">
								<div class="dashboard-border-box">
									<div class="dashboard-box-value">
<?php echo "										".$photoscount."\n"; ?>
									</div>
									<div class="dashboard-box-name">
										Photographs
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
<?php if($session->privilege_level == "admin"): ?>
<?php 
	$totalphotos = Photograph::count_all();
	$totalalbums = Album::count_all();
	$totalusers = User::count_all();
?>
				<div class="admin-box">
					<div class="block-title">
						<h3>Administration</h3>
					</div>
					<div id="admin-box-header">
					</div>
				</div>
				<div class="admin-box">
					<div class="dashboard">
						<div class="dashboard-greet">
							<div id="admin-menu">
								<ul>
									<li><a href="system_photos.php">Photos</a></li>
									<li><a href="system_albums.php">Albums</a></li>
									<li><a href="system_users.php">Users</a></li>
									<li><a href="system_logs.php">Logs</a></li>
								</ul>
							</div>
						</div>
						<div class="dashboard-stats">
							<div class="dashboard-box">
								<div class="dashboard-border-box">
									<div class="dashboard-box-value">
	<?php echo "									".$totalusers."\n"; ?>
									</div>
									<div class="dashboard-box-name">
										Users
									</div>
								</div>
							</div>
							<div class="dashboard-box">
								<div class="dashboard-border-box">
									<div class="dashboard-box-value">
	<?php echo "									".$totalalbums."\n"; ?>
									</div>
									<div class="dashboard-box-name">
										Total Albums
									</div>
								</div>
							</div>
							<div class="dashboard-box">
								<div class="dashboard-border-box">
									<div class="dashboard-box-value">
	<?php echo "									".$totalphotos."\n"; ?>
									</div>
									<div class="dashboard-box-name">
										Total Photographs
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
<?php endif ?>
<?php if(!empty($photos)): ?>
				<div class="admin-box last-photographs">
					<div class="block-title">
						<h3>Latest Photographs</h3>
					</div>
					<div id="admin-box-header">
					</div>
					<ul id="indexphotolist">
<?php foreach($photos as $photo): ?>
						<li>
							<div class="thumbnailsquare">
								<img src="../showphoto.php?id=<?php echo $photo->id; ?>&amp;type=square" alt="">
							</div>
						</li>
<?php endforeach; ?>
					</ul>
				</div>
<?php endif; ?>
            </div>
        </div>
<?php
include_layout_template('admin_footer.php', "admin"); 
?>
