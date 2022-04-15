<?php

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/inc.db.php';

$cookie = $_COOKIE['games'];
$ip = get_ip();

$scores = $db->select('scores', "cookie = ? order by id desc limit 500", [$cookie])->all();
if (!count($scores)) {
	$scores = $db->select('scores', "ip = ? order by id desc limit 500", [$ip])->all();
}

?>
<p>ID = <?= do_html($cookie) ?></p>
<p>IP = <?= do_html($ip) ?></p>

<table border="1">
	<thead>
		<tr>
			<th>Date</th>
			<th>Game</th>
			<th>Score</th>
			<th>Level</th>
			<th>IP</th>
		</tr>
	</thead>
	<tbody>
		<? foreach ($scores as $score): ?>
			<tr>
				<td><?= date('Y-m-d H:i', $score->utc) ?></td>
				<td><?= $score->game ?></td>
				<td><?= $score->score ?></td>
				<td><?= $score->level ?></td>
				<td><?= $score->ip ?></td>
			</tr>
		<? endforeach ?>
	</tbody>
</table>

<? include 'tpl.queries.php' ?>
