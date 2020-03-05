<?php
// BOX STRAP LENGTH

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>BOX STRAP LENGTH</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<style>
canvas {
	background-color: lightblue;
	touch-action: none;
}
</style>
</head>

<body>

<canvas></canvas>

<p>Strap length: <output id="strap-length"></output>m</p>

<script>
class BoxStrap extends CanvasGame {
	constructor( canvas ) {
		super(canvas);

		this.canvas.width = 500;
		this.canvas.height = 300;

		this._floor = 50;
		this._boxWidth = 200;
		this._boxHeight = 100;
		this._straps = [50, 450];

		this.boxPosition = 100;
	}

	setTime() {
	}

	boxRect() {
		const bottom = this.canvas.height - this._floor;
		const top = bottom - this._boxHeight;
		const left = this.boxPosition;
		const right = left + this._boxWidth;
		return [top, right, bottom, left];
	}

	drawContent() {
		this.drawBox();
		this.drawStraps();
		this.drawFloor();
		this.printStrapLength();
	}

	drawStraps() {
		const [top, right, bottom, left] = this.boxRect();

		const style = {color: '#8d503b'};
		this.drawLine(new Coords2D(this._straps[0], bottom), new Coords2D(left-1, top-1), style);
		this.drawLine(new Coords2D(this._straps[1], bottom), new Coords2D(right+1, top-1), style);
		this.drawLine(new Coords2D(left-1, top-1), new Coords2D(right+1, top-1), style);
	}

	drawBox() {
		const [top, right, bottom, left] = this.boxRect();

		const tl = new Coords2D(left, top);
		const tr = new Coords2D(right, top);
		const br = new Coords2D(right, bottom);
		const bl = new Coords2D(left, bottom);

		this.ctx.fillStyle = '#666';
		this.ctx.fillRect(left, top, this._boxWidth, this._boxHeight);

		this.drawLine(bl, tl);
		this.drawLine(tl, tr);
		this.drawLine(tr, br);

		this.drawText(new Coords2D(left + 7, top + this._boxHeight/2 + 5), `${this._boxHeight/100}m`)
		this.drawText(new Coords2D(left + this._boxWidth/2, top + 25), `${this._boxWidth/100}m`)
	}

	drawFloor() {
		this.ctx.fillStyle = '#bbb';
		this.ctx.fillRect(0, this.canvas.height, this.canvas.width, -this._floor);

		const y = this.canvas.height - this._floor;
		const style = {width: 3};
		this.drawLine(new Coords2D(0, y), new Coords2D(this.canvas.width, y), style);
	}

	getStrapLength() {
		const [top, right, bottom, left] = this.boxRect();
		return this._boxWidth + Math.sqrt(Math.pow(this._straps[0] - left, 2) + Math.pow(this._boxHeight, 2)) + Math.sqrt(Math.pow(right - this._straps[1], 2) + Math.pow(this._boxHeight, 2));
	}

	printStrapLength() {
		document.querySelector('#strap-length').value = Math.round(this.getStrapLength()) / 100;
	}

	listenControls() {
		this.listenDrag();
	}

	listenDrag() {
		this.dragging = null;

		this.canvas.on('mousedown', (e) => {
			const [x, y] = e.subjectXY.toArray();
			const [top, right, bottom, left] = this.boxRect();

			if ( x >= left && x <= right && y >= top && y <= bottom ) {
				this.dragging = e.subjectXY;
			}
		});
		this.canvas.on('mousemove', (e) => {
			if ( this.dragging ) {
				const diff = e.subjectXY.x - this.dragging.x;
				if ( this.boxPosition + diff > this._straps[0] && this.boxPosition + diff + this._boxWidth < this._straps[1] ) {
					this.boxPosition += diff;
					this.changed = true;
				}
				this.dragging = e.subjectXY;
			}
		});
		document.on('mouseup', (e) => {
			this.dragging = null;
		});
	}

}

var objGame = new BoxStrap(document.querySelector('canvas'));
objGame.startPainting();
objGame.listenControls();
</script>
