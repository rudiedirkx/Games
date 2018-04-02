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
<script src="<?= html_asset('traffic.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<div id="cars"></div>

<script>
var world;

window.onload = function() {
	var $cars = document.querySelector('#cars');
	var $canvas = document.querySelector('canvas');

	world = new World([
		[
			new Square('es'),
			new Square('esw'),
			new Square('esw'),
			new Square('sw'),
		],
		[
			new Square('nes'),
			new Square('nesw'),
			new Square('nsw'),
			new Square('n'),
		],
		[
			new Square('ns'),
			new Square('ns'),
			new Square('nes'),
			new Square('sw'),
		],
		[
			new Square('new'),
			new Square('new'),
			new Square('new'),
			new Square('nw'),
		],
	]);

	world.addCar(new Coords2D(0, 0), 'w', 0);
	world.addCar(new Coords2D(0, 1), 'w', 1);
	world.addCar(new Coords2D(0, 2), 'n', 1);
	world.addCar(new Coords2D(0, 3), 'e', 0);

	// Tick 2 will collide the next 2 cars
	world.addCar(new Coords2D(2, 0), 'n', 0).then((car) => car.nextDirections.push('w'));
	world.addCar(new Coords2D(3, 0), 'w', 3);

	// U-turn coming up
	world.addCar(new Coords2D(3, 1), 's', 0);



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



	var drawer = new Drawer($canvas, world);

	function keepDrawing() {
		drawer.redraw();
		requestAnimationFrame(keepDrawing);
	}
	keepDrawing();
};
</script>

</body>

</html>
