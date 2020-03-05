<?php
// AIRPLANES

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Airplanes</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<style>
canvas {
	background-color: #eee;
}
</style>
</head>

<body>

<canvas></canvas>

<script>
class Airplane {
	constructor() {
		this.direction = Math.random() * 2 - 1;
		this.location = 0;
	}
}

class Airplanes extends CanvasGame {
	constructor( canvas ) {
		super(canvas);

		this.canvas.width = 500;
		this.canvas.height = 300;

		this.newAirplanePause = 50; // ms
		this.airplaneSpeed = 2; // %/frame

		this.airplanes = [];
	}

	addAirplane() {
		this.airplanes.push(new Airplane());
		this.changed = true;
	}

	moveAirplanes() {
		this.airplanes.forEach(airplane => this.moveAirplane(airplane));
		this.changed = true;
	}

	moveAirplane( airplane ) {
		airplane.location += this.airplaneSpeed / 100;
		if ( airplane.location > 1 ) {
			this.airplanes = this.airplanes.filter(x => x != airplane);
		}
	}

	coord( coord ) {
		return new Coords2D(30 + coord.x * 440, 150 + coord.y * 130);
	}

	getNewAirplanePause() {
		return Number(0.2 * this.newAirplanePause + this.newAirplanePause * 1.3 * Math.random());
	}

	getAirplaneLocation( airplane ) {
		const maxOff = airplane.direction;
		const centerDiff = 1 - Math.abs(0.5 - airplane.location) / 0.5;

		return new Coords2D(
			airplane.location,
			centerDiff * maxOff
		);
	}

	drawAirplane( airplane ) {
		this.drawDot(this.coord(this.getAirplaneLocation(airplane)));
	}

	drawStructure() {
		const options = {radius: 10, color: '#666'};
		this.drawDot(this.coord(new Coords2D(0, 0)), options);
		this.drawDot(this.coord(new Coords2D(1, 0)), options);
	}

	drawContent() {
		this.airplanes.forEach(airplane => this.drawAirplane(airplane));
	}

	startPainting() {
		super.startPainting();

		const moveAirplanes = () => {
			this.moveAirplanes();
			requestAnimationFrame(moveAirplanes);
		};
		requestAnimationFrame(moveAirplanes);

		const addAirplane = () => {
			this.addAirplane();
			setTimeout(addAirplane, this.getNewAirplanePause());
		};
		addAirplane();
	}

	setTime() {
	}
}

var objGame = new Airplanes(document.querySelector('canvas'));
objGame.startPainting();
objGame.listenControls();
</script>

</body>

</html>
