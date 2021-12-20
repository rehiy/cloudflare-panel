<?php
$starttime = microtime(true);
require_once __DIR__ . '/source/common.php';
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>
		<?php
		if ($action) {
			if ($action != 'login') {
				if (isset($action_translate[$action])) {
					echo $action_translate[$action] . ' | ';
					if (isset($_GET['domain'])) {
						echo $_GET['domain'] . ' | ';
					}
				}
			} else {
				echo $action_translate[$action] . ' | ';
			}
		} else {
			echo l('Console') . ' | ';
		}
		echo l('Cloudflare CNAME/IP/NS Setup');
		?>
	</title>
	<meta name="renderer" content="webkit">
	<link rel="stylesheet" href="vendor/components/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/main.css?ver=<?php echo APP_VERSION ?>">
</head>

<body class="bg-light">
	<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
		<a class="navbar-brand" href="./"><?php echo APP_NAME; ?></a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item active nav-link">
					<?php echo $action_translate[$action] ?? l('Console'); ?>
				</li>
				<li class="nav-item">
					<?php
					if ($adapter && $action != 'login' && $action != 'logout') {
						echo '<a class="nav-link" href="?action=logout">', l('Logout'), '</a>';
					}
					?>
				</li>
			</ul>
		</div>
	</nav>

	<main class="bg-white">
		<?php
		if (preg_match('/^\w+$/', $action)) {
			$action_file = __DIR__ . '/source/actions/' . $action . '.php';
			if (is_file($action_file)) {
				require_once $action_file;
			} else {
				echo l('Cannot find the file:') . $action . '.php';
			}
		} else {
			echo l('Cannot find the file:') . $action . '.php';
		}
		?>
	</main>

	<footer class="footer">
		<?php
		$time = round(microtime(true) - $starttime, 3);
		echo '<small><p>Load time: ' . $time . 's </p>';
		?>
	</footer>

	<script src="vendor/components/jquery/jquery.slim.min.js"></script>
	<script src="vendor/components/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="assets/main.js?ver=<?php echo APP_VERSION ?>"></script>
</body>

</html>