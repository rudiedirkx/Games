<?php
// BIOSHOCK HACKER

define('BOARD_SIZE', 6);

$tiles = array(
	'hor' => array('left', 'right'),
	// 'hor-fast' => array('left', 'right'),
	// 'hor-slow' => array('left', 'right'),
	'ver' => array('top', 'bottom'),
	// 'ver-fast' => array('top', 'bottom'),
	// 'ver-slow' => array('top', 'bottom'),
	'top-left' => array('top', 'left'),
	'top-right' => array('top', 'right'),
	'bottom-right' => array('bottom', 'right'),
	'bottom-left' => array('bottom', 'left'),
	// 'start-top' => array('top'),
	// 'start-right' => array('right'),
	// 'start-bottom' => array('bottom'),
	// 'start-left' => array('left'),
	// 'start' => array(),
	// 'end' => array(),
);
$map = array();
while ( count($map) < BOARD_SIZE * BOARD_SIZE ) $map[] = array_rand($tiles);
$map = array_chunk($map, BOARD_SIZE);
$directions = array('top', 'right', 'bottom', 'left');
$start = array($directions[rand(0, 3)], rand(0, BOARD_SIZE-1));

?>
<!doctype html>
<html lang="en">

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="shortcut icon" href="favicon.ico" />
	<style>
	ul, li { margin: 0; padding: 0; list-style: none; }
	.tile { width: 60px; height: 60px; background-color: lightblue; background-size: cover; }
	ul { padding: 10px; background: #ddd; }
	ul .tile { float: left; margin: 0 20px 0 0; }
	ul:after { content: ''; display: block; clear: both; }
	table { border-collapse: separate; cell-spacing: 1px; background: #ddd; }
	th, td { padding: 0; height: 60px; width: 60px; }
	th { background-color: #eee; }
	.tile.end { background-color: yellow; }
	.tile.selected { outline: solid 5px red; }
	.tile.start, .tile.flowing { background-color: rgba(0, 0, 139, 0.3); }
	.tile.flowing-2 { background-color: rgba(0, 0, 139, 0.5); }
	.tile.flowing-3 { background-color: rgba(0, 0, 139, 0.7); }
	.tile.flowing-4 { background-color: rgba(0, 0, 139, 0.9); }
	</style>
	<style>
	.tile.hor { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9kDBA0xAP+iniEAAABnSURBVFjD7dixDcAwCERRiLIP7D/FMRGpHSk1TvKvw9WTzg14d7dtnMM2D0CA03Ez64hYHiWNYDJzmauKir8PPB/+gg95mooBAgQIECBAgAABAmQnkTRy8brvxVT8C6BzYQUI8OXAC0/MFNjW0bSiAAAAAElFTkSuQmCC); }
	.tile.hor-fast { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9kDBA0yKZY9VY4AAABqSURBVFjD7djBCQAxCERRXdKP6b+KsSL3nIU9a+DPLTk9GJBEr6qywXlseAAC7I6bWUXEcSmpBbP3Ps6ZScUAAQIEOB24fia6N3mKigECBDhhDkpq+St/X9RUDBAgQIA37GbYsAIEeDnwBX8tFNiyUVlqAAAAAElFTkSuQmCC); }
	.tile.hor-slow { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAvpJREFUeNrsWEtoE1EUbUqpFBSFYjGCS3EjJOJCsKCCGxcWBaG6cNFCkeJCC5qoKEELUjWKLoofENQiIqKIv5ULi1/EX7IRUZdKEHRlN7qJ58JJmV7vHWcSMbXMg8OdvHvncd79vZdJVTFapvFobZnmIyGYEGz2SAHVTCYzZbJUKjWFTDabnfK7XC4nIZ75BNucXEjpOeRlF0Q7dJ+sd6BfA914PSTw7mw+fm/Eg93Alno9ARLLBQ65jcCShkIM79yEOIsFdzgmb6G7CqQNErUNdnvLA+djEzTajexyFuYvGjbtwEtgTMJttI8LQD90t4BNAd0ExF3gQ2SCWOB2beEgSfx+BfECWFAjUbOR3ASOY+oRsFeTlPyCfhnkR2Bl0NPM3d44HlyEBUYdkrLYEaAP8wdZHJM2eB6mfrv2IjeyCz87gSvGJiIT7JdweguQ5DPxJPPH0ouXi877fRA/gJ5gJCIThLG44zpwlJ5M63yEzTmIQ8Ba6O4YNgWgA3ND2ot8XgfxBVgV5snWkKoVAve406IONW0qENuArxIyRUC8+ADYqvItaHNM8tGKwp+KZDKfmDMTmHsYQvIS8Bq6AUXgBsQIKzvrOEI8OU/yuZE++A54zmcr3OKt98CgUVhPmS4rJNy6qDgkWnPiEDyjwnIKyGHustdQmRIjsLmmPUydrLfBKaoKIxWZYJduExw57rTg3Bm/AfOlERvtSUI4yhS6b6VLnAvrHoj1QkRfAHgKbAZ+CmHmoH5fqvozdIPsfVov7acDOAxUAvpqpAsrq0vaxP7gsRRI/J1cbMzyAmx6IBZCV3AKK8coPWbo674PyrF1QFcgvZYnyRPOu1LZS7nBtLGJXtoUw0Id1gfHeWzJLWa3cQGosPoWQ5c33hdPnwb2eSS4voT6Sd03ai4yl+enRULCtNohIPk7QBJ5x0Y8/KZ29kcqEs7/tUHvd3JDns0Q9Cd1kfwTgjHGb1Xc5uymKV+8rBtN8rdz5n+bSb6wJgQTgv85wV8CDADgoI63wq6aUAAAAABJRU5ErkJggg==); }
	.tile.ver { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAAAAZiS0dEAC8ALwAvIhI/LgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9kDBA06L7eHerMAAABqSURBVFjD7dkxCsAgDEbhpPQ++e9/itwozi3NZJEM760ifBBd1Kuq7CN3t5M1DLu7DRFhE7pseAB38+6SvJP0663JzGLEAAECBAgQIECAAAECBAgQIECAALdqX7ckHYVkJiPmDD4XhnxDLPrGHcyblpBvAAAAAElFTkSuQmCC); }
	.tile.ver-fast { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAAAAZiS0dEAC8ALwAvIhI/LgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9kDBA05OB95rLcAAABrSURBVFjD7dmxCcAwDAVRKWQf/f2n0EZKHYhCINiouGuN4YHsxvaqKnvI3W1nDcPObkNE2IQOGx7Av3l3Sb4m6fU2ZWYxYoAAAQIECBAgQIAAAQIECBAgQIBLal+3JG2FZCYj5gzeF4Z8Q1w/pR3MCAukRQAAAABJRU5ErkJggg==); }
	.tile.ver-slow { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAwlJREFUeNrsmU+ITVEcx+/VK9TIwuqFUiLN5j0ZiTfGs5iVP6WnTNIMC/mTohh/FoamRDOUNEk2JKXUFM3GwuJRoybUKyl2Is1Eiuxs+Pz43TrdzpE5990zr8ypb+e+c+7t972/8/t7X/yTEVlGHMdRyOGgERVcD5RKpagVxpyoxccswawjdjlJepTLZavXNBqNk0zrwCdwSO9N9uTiFvjA2vbUc/8kt+D7Zgio6uVW8ARcT4jp/gGmnWCc9SO+cgoZtN8FNoIBCNRT5AeYtoBTyR5rkfkCudqgEtgBbqTJ6VgInmcl56VBhI0xfQP7ENpI7R1jWgXaQL8QM20y1BHfBl/S5HQsBu3sdbm0xrpofjw3gggdtQgtMg2zt8d1pKytYXoLlsvxh3CSRHCN6Sj4rkQnLeTE49eCCnuXk5fInaAS2gUeg0GbvSm502CKvb3BwoySGwI/hJzD3iQWrgeXHN6eD0E9VhEu2aXXQU5eYDV450vOKw5qausDr0A3wicd5G6CN6A+HZtrhgY3SWqD2NBfUmCf2uPV4IFaUphLmNrcSrAM7DYDNddL1F7bcyXosDfR2iKwX9PfcYvWekAHaz3BwowxzoHPCO+wHanmbiksekNWM2ZuHkkyjIWcaHcDuGBzqDzjYE2d4aOQSxcGSkyC99RMxMGqZpAXCB60aE3IbwYTrPdnOSGfckuYSD69mGjO0FpRj/MMuJuEmWBNk2rurAbgZxZPlb7jINhmI+cTrKerQQklE5KDLUXBealkpFBNO4MSWyBFLtfSo7zMRYPqqTZyNc0w881CVogZWn4axAYdWWQpeCicTHKGbV5hes/vazMRB4f/8C53m3ZmkPvdIoA7odtOEX5fGnYhZ2uQWHvENFe82idIZw3Ukr6kz+h09CBSlr0GY77ksvTFo1q1dDp6ENHcPCVXN9bbBLl+m0HAiFbKFQuxolbaX+VnilxVP5OILTyQJqvpR6yNuZCtOG45IYLNIG0E58NgBbjXFA1m+RrgaTazHzD/T4Jxq/8N8UuAAQDEPITsKn5TUwAAAABJRU5ErkJggg==); }
	.tile.top-left { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAAAAZiS0dEAC8ALwAvIhI/LgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9kDBA4IIiYA7CYAAACfSURBVFjD7dhLDoAgDATQ1vQ+cP9T9Ea4VzFIP5A4sze+jC0mcGut0UOYmTLTYZD0Hiil0A45aPMAaA33luSaWqvr1qjq0HuFmW8Loar4xP8Ces/fl8huIMyg5xGDBgFcPX9o0NoeGrS2lwacxaUALbhwoBUXCvTAhQG9cCFAT9zrzcJqmBswCjYNjAYNAbMR+Bf/GiiDd0doEEAAd80J6RBHFYX9aI4AAAAASUVORK5CYII=); }
	.tile.top-right { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAAAAZiS0dEAC8ALwAvIhI/LgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9kDBA4JEhnC7csAAACzSURBVFjD7ZdBDgQhCARh43/k/6/AF7HnMWvijsA4pvvmwViBbiJsZkY/xMyUqQEGldGFWivtoA9tLgCuikch6SUirqlR1al3y07VEpHLubUGD74XcNbT5WHP9ZB2botnxwJCAsBIwCd8eF6Ls6t4Zkgyq3i7glmQSy3OgFz2YDSkS0giId1SHAXpOmYiIN3noKqaJ2jYoPYCDf/y95D/7tfTizv2Yvyog7SVB9FiAALwhr5C21QLCt5zwwAAAABJRU5ErkJggg==); }
	.tile.bottom-right { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAAAAZiS0dEAC8ALwAvIhI/LgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9kDBA4JJaF/SMQAAAClSURBVFjD7dlJDoAgDAVQajwQ9z8FnKiuVYZEOpD6u0M3L+UTAYmZOW1cR9q8AATQu86dMDnn27jWiilGBjVyRoPXbA6cgPw6uApTA0rBVBaJNE4UqIETA2rhRICauGWgNm4JaIH7DLTCxdwsWHYvXgetu4cNK4De+cOZZFalFB6di107+MRhFYfMYGPaX8+odwXcCqw1Dhn8BbCbQSLbL1vvb8gFIGIoHnaePZwAAAAASUVORK5CYII=); }
	.tile.bottom-left { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAAAAZiS0dEAC8ALwAvIhI/LgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9kDBA4JL0GqodoAAACqSURBVFjD7ZhJCsVACET1k/u09z+FNzKbbAIRwndoCeXS1aO6HFo2M6PB8aPhAUAA7g4mIltr3ZKqCgUBuNWDV/4xVNVGA3YDhwGrQdMAq0DTi0REeHwVZ0KWtZksyNI+mAFZ3qijkC2TJALZNur+hcSyEFURCkZVhIIA3O3D8Qoeb5bO7B0v/Ym7P0ooklQPDj+wEnsnYBFpBfEOVvDg5wFdDzL39mavWE9b3DpDFEQeWQAAAABJRU5ErkJggg==); }
	.tile.start-top { background-image: url(data:image/png;base64,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); }
	.tile.start-right { background-image: url(data:image/png;base64,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); }
	.tile.start-bottom { background-image: url(data:image/png;base64,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); }
	.tile.start-left { background-image: url(data:image/png;base64,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); }
	</style>
</head>

<body>

<ul>
	<li class="tile hor"></li>
	<!--
	<li class="tile hor-fast"></li>
	<li class="tile hor-slow"></li>
	-->
	<li class="tile ver"></li>
	<!--
	<li class="tile ver-fast"></li>
	<li class="tile ver-slow"></li>
	-->
	<li class="tile top-left"></li>
	<li class="tile top-right"></li>
	<li class="tile bottom-right"></li>
	<li class="tile bottom-left"></li>
	<li class="tile start-top"></li>
	<li class="tile start-right"></li>
	<li class="tile start-bottom"></li>
	<li class="tile start-left"></li>
</ul>

<br>

<table>
	<tbody id="board">
		<?= '<tr>' . str_repeat('<th></th>', BOARD_SIZE+2) . '</tr>' ?>
		<?= str_repeat('<tr><th></th>' . str_repeat('<td class="tile"></td>', BOARD_SIZE) . '<th></th></tr>', BOARD_SIZE) ?>
		<?= '<tr>' . str_repeat('<th></th>', BOARD_SIZE+2) . '</tr>' ?>
	</tbody>
</table>

<p><button id="tick">Tick</button></p>

<script src="rjs.js"></script>
<script>
"use strict";

var tiles = <?= json_encode($tiles) ?>;
var directions = <?= json_encode($directions) ?>;
var BOARD_SIZE = <?= BOARD_SIZE ?>;
var rawMap = <?= json_encode($map) ?>;
var start = <?= json_encode($start) ?>;
var directionDeltas = {top: new Coords2D(0, -1), right: new Coords2D(1, 0), bottom: new Coords2D(0, 1), left: new Coords2D(-1, 0)};

$(function() {

	var game = new BioShockHacker($('board'), rawMap, start);
	window.game = game;

	$('tick').on('click', function(e) {
		var result = game.tick();
		if ( false === result ) {
			alert('Fail =(');
		}
		else if ( true === result ) {
			alert('Great success! =)');
		}
	});

	var time = (Date.now() - performance.timing.requestStart) / 1000;
	console.log('done in ', time);

});

function BioShockHacker(boardElement, rawMap, start) {
	this.debug = true;

	this.map = [];
	this.changes = [];
	this.redraw = false;

	this.startDirection = start[0];
	this.startCoord = new Coords2D(start[1], start[1]);
	this.startCoord[ this.directionAxis(this.startDirection) ] = this.directionCoordEnd(this.startDirection) * (BOARD_SIZE - 1);

	var end = [ this.oppositeDirection(this.startDirection), ~~(Math.random() * BOARD_SIZE) ];
	this.endDirection = end[0];
	this.endCoord = new Coords2D(end[1], end[1]);
	this.endCoord[ this.directionAxis(this.endDirection) ] = this.directionCoordEnd(this.endDirection) * (BOARD_SIZE - 1);

	this.boardElement = boardElement;

	this.init(rawMap);

	this.startTile = this.getTileByCoord(this.startCoord).getNeighborTile(this.startDirection, 'start');
	this.startTile.exit = this.oppositeDirection(this.startDirection);
	this.endTile = this.getTileByCoord(this.endCoord).getNeighborTile(this.endDirection, 'end');

	this.currentTile = this.startTile;
	this.currentTile.flow = 1;

	this.draw();
	this.drawInit();
	this.listen();
}
$extend(BioShockHacker, {
	init: function(rawMap) {
		$each(rawMap, function(row, y) {
			var mapRow = [];
			$each(row, function(type, x) {
				mapRow.push(new Tile(this, type, new Coords2D(x, y)));
			}, this);
			this.map.push(mapRow);
		}, this);
		this.redraw = true;
	},
	drawInit: function() {
		this.startTile.extra = 'start-' + this.oppositeDirection(this.startDirection);
		this.changes.push(this.startTile.location);
		// this.startTile.getCell().addClass('tile').addClass('start').addClass('start-' + this.oppositeDirection(this.startDirection));

		this.endTile.extra = 'start-' + this.oppositeDirection(this.endDirection);
		this.changes.push(this.endTile.location);
		// this.endTile.getCell().addClass('tile').addClass('end').addClass('start-' + this.oppositeDirection(this.endDirection));

		this.draw();
	},
	listen: function() {
		var game = this;
		this.boardElement.on('click', 'td.tile:not(.flowing)', function(e) {
			var cell = this,
				tile = game.getTileByCoord(cell.coord),
				otherCell = game.boardElement.getElement('.selected'),
				otherTile = otherCell ? game.getTileByCoord(otherCell.coord) : null;

			// Unselect
			if ( this.classList.contains('selected') ) {
game.debug && console.log('unselect');
				this.removeClass('selected');
			}
			// Select first
			else if ( !otherCell ) {
game.debug && console.log('select first');
				this.addClass('selected');
			}
			// Switch tiles
			else {
game.debug && console.log('switch tiles');
				game.switchTiles(tile.location, otherTile.location);
				// redraw will remove 'selected'
			}
		});
	},
	tick: function() {
		var flow = this.currentTile.flow;
		if ( flow >= 4 ) {
			var nextTile = this.currentTile.nextTile();
			if ( !nextTile ) {
				return false;
			}
			else if ( nextTile === true ) {
				return true;
			}

			this.currentTile = nextTile;
			// this.currentTile.getCell().addClass('flowing');
		}

		this.currentTile.flow++;
		// this.currentTile.getCell().addClass('flowing-' + this.currentTile.flow);

		this.changes.push(this.currentTile.location);
		this.draw();
	},
	oppositeDirection: function(start) {
		var startIndex = directions.indexOf(start),
			endIndex = (startIndex + 2) % 4,
			end = directions[endIndex];
		return end;
	},
	directionCoordEnd: function(direction) {
		return ['top', 'left'].contains(direction) ? 0 : 1;
	},
	directionAxis: function(direction) {
		return ['top', 'bottom'].contains(direction) ? 'y' : 'x';
	},
	directionOppositeAxis: function(direction) {
		var axis = this.directionAxis(direction);
		return axis == 'x' ? 'y' : 'x';
	},
	switchTiles: function(tileACoord, tileBCoord) {
		var tileA = this.getTileByCoord(tileACoord),
			tileB = this.getTileByCoord(tileBCoord);
		this.setTileByCoord(tileACoord, tileB);
		this.setTileByCoord(tileBCoord, tileA);
		this.draw();
	},
	getTileByCoord: function(coord, meta) {
		var tile = this.map[coord.y] && this.map[coord.y][coord.x];
		if ( !tile && meta ) {
			return coord.equal(this.startTile.location) ? this.startTile : ( coord.equal(this.endTile.location) ? this.endTile : null );
		}
		return tile;
	},
	setTileByCoord: function(coord, tile) {
		tile.location = coord;
		this.map[coord.y][coord.x] = tile;
		this.changes.push(coord);
	},
	draw: function() {
console.log('drawing');
		if ( this.changes.length ) {
			$each(this.changes, function(coord) {
				this.getTileByCoord(coord, true).draw();
			}, this);
		}
		else if ( this.redraw ) {
			$each(this.map, function(row, y) {
				$each(row, function(tile, x) {
					tile.draw();
				}, this);
			}, this);
			this.startTile.draw();
			this.endTile.draw();
		}

		this.changes.length = 0;
		this.redraw = false;
	}
});

function Tile(game, type, coord) {
	this.game = game;
	this.type = type;
	this.tile = tiles[type];
	if ( this.tile ) {
		this[this.tile[0]] = 1;
		this[this.tile[1]] = 1;
		this.flow = 0;
	}
	this.location = coord;
}
$extend(Tile, {
	opposite: function(entry) {
		var entryIndex = this.tile.indexOf(entry),
			exitIndex = entryIndex ? 0 : 1,
			exit = this.tile[exitIndex];
		return exit;
	},
	getCell: function() {
		return this.game.boardElement.rows[this.location.y+1].cells[this.location.x+1];
	},
	draw: function() {
this.game.debug && console.log('drawing tile [' + this.location.join(', ') + ']');
		var cell = this.getCell();
		cell.coord = this.location;
		cell.className = 'tile ' + this.type + ' ' + (this.extra || '');
		if ( this.flow ) {
			cell.addClass('flowing').addClass('flowing-' + Math.floor(this.flow));
		}
	},
	nextTile: function() {
		if ( !this.exit ) {
			if ( !this.entry ) {
				return false;
			}

			this.exit = this.opposite(this.entry);
		}

		var nextTile = this.getNeighborTile(this.exit),
			nextTileEntry = this.game.oppositeDirection(this.exit);
this.game.debug && console.log('nextTile', nextTile);
		if ( !nextTile && this.location.equal(this.game.endCoord) ) {
			return true;
		}
		else if ( !nextTile || !nextTile[nextTileEntry] ) {
			return false;
		}

		nextTile.entry = nextTileEntry;
		return nextTile;
	},
	getNeighborTile: function(direction, create) {
		var delta = directionDeltas[direction],
			neighborCoord = this.location.add(delta),
			neighborTile = this.game.getTileByCoord(neighborCoord);
		if ( !neighborTile && create ) {
			neighborTile = new Tile(this.game, create, neighborCoord);
		}
		return neighborTile;
	}
});

$extend(Coords2D, {
	join: function(glue) {
		glue == null && (glue = ',');
		return [this.x, this.y].join(glue);
	},
	equal: function(coord) {
		return this.join() == coord.join();
	}
});
</script>

</body>

</html>