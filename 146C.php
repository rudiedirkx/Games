<!doctype html>
<html>

<head>
	<title>Slither CANVAS</title>
	<meta name="viewport" content="width=device-width, initial-scale=0.5" />
	<style>
	* {
		-webkit-user-select: none;
	}
	canvas {
		background: #bde4a3;
		width: 100%;
		max-width: 500px;
		-webkit-tap-highlight-color: rgba(0, 0, 0, 0);
	}
	#red {
		position: absolute;
		width: 6px;
		height: 6px;
		margin: -3px 0 0 -3px;
		background-color: red;
		pointer-events: none;
	}
	</style>
</head>

<body>

<canvas width="300" height="300">No CANVAS?</canvas>

<script src="js/rjs-custom.js"></script>
<script src="146b.js"></script>
<script>
var _LEVEL = 1;
var LEVEL = location.hash ? (parseInt(location.hash.substr(1)) || _LEVEL) : _LEVEL;


</script>

</body>

</html>
