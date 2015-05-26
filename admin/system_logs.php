<?php
require_once('../includes/initialize.php');
if (!$session->is_logged_in() || $session->privilege_level !== "admin") { redirect_to("login.php"); }

// PAGINATION
$page = 1;
$per_page = 50;
if(!empty($_GET['page']) && validate($_GET['page'], "digit")) {
	$page = $_GET['page'];
}

$infobox = "The following log entries were created.";
$show = true;
$logfile = SITE_ROOT.DS.'logs'.DS.'system.log';
$data = array();
$lines = 0;

// DELETE LOGS
if(isset($_POST['submit']) && file_exists($logfile) && is_readable($logfile)) {
	file_put_contents($logfile, '');
    log_action("info", "Logs cleared by user \"{$session->username}\"");
    $session->message("Logs successfully cleared.");
    redirect_to('system_logs.php');
}

if(file_exists($logfile) && is_readable($logfile)) {
	
	$file = new SplFileObject($logfile, "r");
	
	while (!$file->eof()) {
	   $file->fgets();
	   $lines++;
	}
	
	$lines = $lines - 2;

	$total_count = $lines;
	$pagination = new Pagination($page, $per_page, $total_count);
	$offset = $pagination->offset();

	$limit = $lines - $offset;
	$till = $lines - $per_page - $offset;

	for($i = $limit; $i > $till; $i--) {
		$file->seek($i);
		if($i !== 0) {
			if(!empty($file->current())) {
				$data[] = $file->current();
			}
		} else {
			$data[] = $file->current();
			break;
		}
	}	
} else {
	$infobox = "Failed to load logs entries. Make sure system.log exists and it's readable.";
	$show = false;
}

include_layout_template('admin_header.php', "admin"); 
?>
		<div id="main">
			<div id="admin-panel">
				<div class="admin-box">
					<img class="h2-icon" src="images/administrator_icon.png"><h2 class="left">Logs</h2>
					<div id="admin-box-header">
						<div id="logs-clear-button">
							<form onsubmit="return confirm('All logs entries will be deleted.');" action="system_logs.php" method="POST">
								<input type="submit" name="submit" value="Clear">
							</form>
						</div>
					</div>
					<div class="line"></div>
					<a href="index.php">&laquo; Home</a>
					<div class="info-box">
<?php if(!empty($message)) { echo "					".output_message($message)."\n"; } else { echo "					<p>{$infobox}</p>\n"; } ?>
					</div>
				</div>
				<div class="admin-box">
<?php if($show): ?>
					<table id="table-logs">
						<tr>
							<th></th>
							<th>Timestamp</th>
							<th>Message</th>
						</tr>
						<tr class="logs-spacer-tr"></tr>
<?php foreach($data as $line): ?>
						<tr>
							<?php echo $line; ?>
						</tr>
						<tr class="logs-spacer-tr"></tr>
<?php endforeach; ?>
						<tr>
							<td id="total-td" colspan="3">Total: <?php echo $lines + 1; ?></td>
						</tr>
					</table>
					<div id="pagination" style="clear:both;">
<?php echo "						".$pagination->display()."\n"; ?>
					</div>
<?php endif; ?>
				</div>
			</div>
		</div>
<?php
include_layout_template('admin_footer.php', "admin"); 
?>
