class Player {
	constructor(name = '') {
		this.name = name;
	}
}

class Game {
	constructor() {
		this.player = new Player();
		this.code = '';
		this.name = '';
		this.score = 0;
		this.starttime = null;
		this.playtime = 0;
	}

	saveScore() {

	}

	startTheTime() {
		this.starttime = new Date;
	}

	stopTheTime() {
		this.playtime = new Date - this.starttime;
	}
}

class LevelableGame extends Game {
	constructor( level = 1 ) {
		super();

		this.level = 0;
		this.mapToClass = {};
		this.moves = 0;

		this.ui_attachMetaEvents();
		this.ui_attachControlEvents();

		this.loadLevel(level);
	}

	// loads & builds level # n
	loadLevel( n ) {
		this.loadMap(n, this.cb_loadMap)
	}

	// handles map download response
	cb_loadMap( rsp ) {
		this.level = rsp.level
		this.buildMap(rsp)
	}

	// loadLevel +1
	nextLevel() {
		return this.loadLevel(this.level + 1)
	}

	// loadLevel -1
	prevLevel() {
		return this.loadLevel(this.level - 1)
	}

	// loadLevel -n
	levelGo(n) {
		return this.loadLevel(this.level + n)
	}

	// downloads map
	loadMap( level, cb ) {
		$.get('?get_map=' + level).on('done', (e, rsp) => {
			cb.call(game, rsp);
		});
	}

	// builds map & html
	buildMap( rsp ) {
		// stats
		$('#stats-level').setText(this.level)

		var map = rsp.map;
		var container = $('#map-container').empty();
		var X;
		var Y = map.length;
		var x;
		var y;
		var tr;
		var td;
		var t;
		var c

		for ( y=0; y<Y; y++ ) {
			tr = container.insertRow(y);
			for ( x=0, X = map[y].length; x<X; x++ ) {
				td = tr.insertCell(x);
				t = map[y][x];
				if ( ' ' != t ) {
					td.data('type', t);
					c = this.mapToClass[t] || t;
					td.addClass(c);
					if ( 'wall' == c ) {
						td.addClass('wall' + (0.5 < Math.random() ? '1' : '2'));
					}
				}
				this.postBuildField(td, x, y);
			}
		}
	}

	postBuildField( field, x, y ) {

	}

	ui_attachMetaEvents() {
		$('#btn-next-level').on('click', (e) => {
			e.preventDefault();

			this.nextLevel();
		});
		$('#btn-prev-level').on('click', (e) => {
			e.preventDefault();

			this.prevLevel();
		});
		$('#btn-restart-level').on('click', (e) => {
			e.preventDefault();

			this.loadLevel(this.level);
		});
	}
	ui_attachControlEvents() {

	}
	updateStats() {
		$('#stats-moves').setText(this.moves);
	}
}

class BoardGame extends Game {

}
BoardGame.nesw = [
	[0, -1],
	[1, 0],
	[0, 1],
	[-1, 0]
];
