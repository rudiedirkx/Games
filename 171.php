<?php
// TRAFFIC

require __DIR__ . '/inc.bootstrap.php';

// - reuse nextLocation() in move()

?>
<!doctype html>
<html>

<head>
<title>Traffic</title>
<style>
canvas {
	border: solid 1px black;
}
</style>
<script>window.onerror = function(e) { alert(e); };</script>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('traffic.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<div id="cars"></div>

<script>
"use strict";

var world;

window.onload = function() {
	var $cars = document.querySelector('#cars');
	var $canvas = document.querySelector('canvas');

	world = new Traffic.World([
		[
			new Traffic.Square('rd'),
			new Traffic.Square('rdl', 'd'),
			new Traffic.Square('rdl', 'd'),
			new Traffic.Square('dl'),
		],
		[
			new Traffic.Square('urd'),
			new Traffic.Square('urdl', 'rl'),
			new Traffic.Square('udl'),
			new Traffic.Square('u'),
		],
		[
			new Traffic.Square('ud'),
			new Traffic.Square('ud'),
			new Traffic.Square('urd', 'urd'),
			new Traffic.Square('dl'),
		],
		[
			new Traffic.Square('url', 'ur'),
			new Traffic.Square('url'),
			new Traffic.Square('url'),
			new Traffic.Square('ul'),
		],
	]);

	world.addCar(new Coords2D(0, 0), 'l', 0);
	world.addCar(new Coords2D(0, 1), 'l', 1);
	world.addCar(new Coords2D(0, 2), 'u', 1);
	world.addCar(new Coords2D(0, 3), 'r', 0);

	// Tick 2 will collide the next 2 cars
	world.addCar(new Coords2D(2, 0), 'u', 0).then((car) => car.nextDirections.push('l'));
	world.addCar(new Coords2D(3, 0), 'l', 3);

	// U-turn coming up
	world.addCar(new Coords2D(3, 1), 'd', 0);



	function addCarButton(label, onclick) {
		var btn = document.createElement('button');
		btn.textContent = label;
		btn.onclick = onclick;
		$cars.append(btn);
		$cars.append(' ');
		return btn;
	}

	function moveAllCars() {
		world.cars.forEach((car) => car.move());
		world.change = true;
	}

	addCarButton('All', function(e) {
		moveAllCars();
	}).autofocus = 1;

	var timer = 0;
	addCarButton('Start/stop', function(e) {
		if ( timer ) {
			clearInterval(timer);
			timer = 0;
		}
		else {
			timer = setInterval(() => moveAllCars(), 40);
			moveAllCars();
		}
	});//.click();

	world.cars.forEach(function(car, i) {
		addCarButton('Car ' + (i+1), (e) => {
			car.move();
			world.change = true;
		});
	});



	var drawer = new Traffic.Drawer($canvas, world);

	function keepDrawing() {
		drawer.redraw();
		requestAnimationFrame(keepDrawing);
	}
	keepDrawing();
};
</script>

</body>

</html>
