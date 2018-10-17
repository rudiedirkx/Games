<?php

require __DIR__ . '/inc.bootstrap.php';

?>
<!DOCTYPE>
<html>

<head>
<title>Marbles</title>
<script src="<?= html_asset('js/mootools_1_11.js') ?>"></script>
<link rel="stylesheet" href="<?= html_asset('162.css') ?>" />
<script src="<?= html_asset('162.js') ?>"></script>
<script>var level = <?php echo isset($_GET['level']) ? (int)$_GET['level'] : 1; ?>;</script>
</head>

<body>
	<div id="frame"></div>
</body>

</html>
