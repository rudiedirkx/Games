"use strict";

class HitomezashiGenerator {
	constructor(size = 10) {
		this.size = size;
	}
}

class HitomezashiGeneratorRandom extends HitomezashiGenerator {
	next() {
		return Math.random() > 0.5;
	}
}

class HitomezashiGeneratorOneOff extends HitomezashiGenerator {
	constructor(start = true, size = undefined) {
		super(size);
		this.on = !start;
	}

	next() {
		return (this.on = !this.on);
	}
}

class Hitomezashi extends CanvasGame {

	reset() {
		super.reset();

		this.iter = 0;
		this.xgen = null;
		this.ygen = null;
		this.width = 0;
		this.height = 0;
		this.SIZE = 40;
		this.OFFSET = 20;
	}

	scale( source ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return this.OFFSET + source * (this.SIZE);
	}

	loadMap(xgen, ygen) {
		this.xgen = xgen;
		this.ygen = ygen;

		this.width = xgen.size;
		this.height = ygen.size;

		this.canvas.width = this.OFFSET + this.width * this.SIZE + this.OFFSET;
		this.canvas.height = this.OFFSET + this.height * this.SIZE + this.OFFSET;

		this.paint();
	}

	loadMapOneOff() {
		this.iter++;
		const bits = this.iter % 4;
		this.loadMap(new HitomezashiGeneratorOneOff(bits & 1), new HitomezashiGeneratorOneOff(bits & 2));
	}

	loadMapRandom() {
		this.iter++;
		this.loadMap(new HitomezashiGeneratorRandom(), new HitomezashiGeneratorRandom());
	}

	drawContent() {
		for ( let y = 0; y <= this.height; y++ ) {
			for ( let x = 0; x <= this.width; x++ ) {
				const C = this.scale(new Coords2D(x, y));
				this.drawDot(C, {radius: 2});
			}
		}
	}

	drawStructure() {
		for ( let y = 0; y <= this.height; y++ ) {
			this.drawHorLines(y, this.xgen.next());
		}
		for ( let x = 0; x <= this.width; x++ ) {
			this.drawVerLines(x, this.ygen.next());
		}
	}

	drawHorLines(y, on) {
		for ( let x = Number(!on); x < this.width; x += 2 ) {
			this.drawLine(this.scale(new Coords2D(x, y)), this.scale(new Coords2D(x + 1, y)));
		}
	}

	drawVerLines(x, on) {
		for ( let y = Number(!on); y < this.width; y += 2 ) {
			this.drawLine(this.scale(new Coords2D(x, y)), this.scale(new Coords2D(x, y + 1)));
		}
	}

}
