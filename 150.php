<?php

require __DIR__ . '/inc.bootstrap.php';

$g_arrGrid = [
	['20', '12', '10', '20', '11', '20', '13'],
	['12', '12', '00', '00', '11', '12', '23'],
	['23', '00', '23', '13', '00', '23', '22'],
	['01', '20', '20', '00', '12', '13', '23'],
	['01', '23', '12', '13', '01', '22', '20'],
	['21', '00', '12', '12', '22', '00', '21'],
	['01', '22', '21', '23', '20', '11', '22'],
];

?>
<!doctype html>
<html>

<head>
<title>Switch Board</title>
<style>
table {
	border-collapse: collapse;
}
table th {
	width: 50px;
	height: 50px;
}
table img {
	border: 0;
	width: 50px;
	height: 50px;
	display: block;
}
</style>
</head>

<body>
<table id="switchboard" border="0" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<? foreach ($g_arrGrid[0] as $cell): ?>
				<th><button class="switch-col">o</button></th>
			<? endforeach ?>
			<th></th>
		</tr>
	</thead>
	<tbody id="switchboard_tbody">
		<? foreach ($g_arrGrid as $row): ?>
			<tr>
				<? foreach ($row as $cell): ?>
					<td><img data-type="<?= $cell[0] ?>" data-rotation="<?= $cell[1] ?>" /></td>
				<? endforeach ?>
				<th><button class="switch-row">o</button></th>
			</tr>
		<? endforeach ?>
	</tbody>
</table>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script>
const SWITCHBOARD = {
	types: [
		['150_0_0', '150_0_1', '150_0_2', '150_0_3'],
		['150_1_0', '150_1_1', '150_1_2', '150_1_3'],
		['150_2_0', '150_2_1', '150_2_2', '150_2_3']
	],

	switchCol(index) {
		const s = $$(`#switchboard_tbody tr > td:nth-child(${index+1}) > img`);
		SWITCHBOARD.switchFromSource(s);
	},

	switchRow(index) {
		const s = $$(`#switchboard_tbody tr:nth-child(${index+1}) > td > img`);
		SWITCHBOARD.switchFromSource(s);
	},

	switchFromSource(s) {
		s.forEach((v, k) => {
			const t = parseInt(v.data('type'));
			const r = (parseInt(v.data('rotation')) + 1) % 4;
			v.data('rotation', r);
			v.src = '/images/' + SWITCHBOARD.types[t][r] + '.bmp';
		});
	}

};

$$('#switchboard img').forEach(function(v, k) {
	v.src = '/images/' + SWITCHBOARD.types[v.data('type')][v.data('rotation')] + '.bmp';
});

$$('.switch-col').on('click', function(e) {
	e.preventDefault();
	SWITCHBOARD.switchCol(this.closest('tr > *').cellIndex);
});
$$('.switch-row').on('click', function(e) {
	e.preventDefault();
	SWITCHBOARD.switchRow(this.closest('tr').sectionRowIndex);
});
</script>
</body>

</html>
