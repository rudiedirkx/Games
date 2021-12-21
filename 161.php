<?php
// BIOSHOCK FLOOD

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>BIOSHOCK FLOOD</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
* {
	box-sizing: border-box;
}
.inside table {
	border-collapse		: collapse;
	border				: 0;
}
.inside th,
.inside td {
	padding				: 0;
	width				: 40px;
	height				: 40px;
	border				: solid 1px #fff;
}
.inside td {
	background-color	: #ddd;
	background-position	: 0 0;
	background-repeat	: no-repeat;
	background-size		: cover;
}
.inside th.io {
	background-position	: 0 0;
	background-repeat	: no-repeat;
	background-size		: cover;
}
.inside tr:first-child th.io {
	background-image 	: url(images/161_io_u.png);
}
.inside tr:last-child th.io {
	background-image 	: url(images/161_io_d.png);
}
.inside th.io:first-child {
	background-image 	: url(images/161_io_l.png);
}
.inside th.io:last-child {
	background-image 	: url(images/161_io_r.png);
}
.inside td.pipe-ud {
	background-image 	: url(images/161_pipe_ud.png);
}
.inside td.pipe-lr {
	background-image 	: url(images/161_pipe_lr.png);
}
.inside td.pipe-ur {
	background-image 	: url(images/161_pipe_ur.png);
}
.inside td.pipe-ul {
	background-image 	: url(images/161_pipe_ul.png);
}
.inside td.pipe-dr {
	background-image 	: url(images/161_pipe_dr.png);
}
.inside td.pipe-dl {
	background-image 	: url(images/161_pipe_dl.png);
}
.with-selection td:not(.selected) {
	opacity: 0.5;
}

.io.start,
.locked {
	background-color: lightblue;
}
.io.end {
	background-color: lightgreen;
}
[data-full="1"] {
	background-color: #b3cde0 !important;
}
[data-full="2"] {
	background-color: #6497b1 !important;
}
[data-full="3"] {
	background-color: #005b96 !important;
}
[data-full="4"] {
	background-color: #03396c !important;
}
[data-full="5"] {
	background-color: #011f4b !important;
}
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('bioshockflood.js') ?>"></script>
</head>

<body>

<table class="outside">
	<tr>
		<td class="inside">
			<table class="inside" id="grid"></table>
		</td>
	</tr>
	<tr>
		<td id="stats"></td>
	</tr>
	<tr>
		<td>
			<button onclick="return objGame.createMap(objGame.m_iSize), false">Restart</button>
			&nbsp;
			<button onclick="return objGame.start(), false">Start</button>
			&nbsp;
			<button onclick="return objGame.finish(), false">Finish/fast</button>
			&nbsp;
			<button onclick="return objGame.tick(), false">Tick</button>
		</td>
	</tr>
</table>

<script>
objGame = new BioshockFlood($('#grid'));
objGame.createMap(6);
objGame.listenControls();
</script>
</body>

</html>
