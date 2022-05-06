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
<title>Scores</title>
<style>
table {
	border-collapse: collapse;
}
table td, table th {
	padding: 4px 7px;
	border: solid 0 #aaa;
	border-width: 1px 0;
}
table th {
	text-align: left;
}
</style>

<p>ID = <?= do_html($cookie) ?></p>
<p>IP = <?= do_html($ip) ?></p>

<table>
	<thead>
		<tr>
			<th>Date</th>
			<th>Game</th>
			<th>Level</th>
			<th>Score</th>
			<th>IP</th>
		</tr>
	</thead>
	<tbody>
		<? foreach ($scores as $score): ?>
			<tr>
				<td><?= date('Y-m-d H:i', $score->utc) ?></td>
				<td><?= $score->game ?></td>
				<td><?= $score->level ?></td>
				<td><?= $score->score ?></td>
				<td><?= $score->ip ?></td>
			</tr>
		<? endforeach ?>
	</tbody>
</table>

<? include 'tpl.queries.php' ?>
