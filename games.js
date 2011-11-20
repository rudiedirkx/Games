function not(a) {return !a}

Player = new Class({
	name: ''
})

Game = new Class({
	code: '', // "160"
	name: '', // "Pixelus"
	player: null, // typeof Player
	score: 0,
	starttime: null,
	playtime: 0,
	initialize: function() {
		this.player = new Player
	},
	saveScore: function() {
		
	},
	startTheTime: function() {
		this.starttime = new Date
	},
	stopTheTime: function() {
		this.playtime = new Date - this.starttime
	}
})

LevelableGame = Game.extend({
	level: 0,
	mapToClass: {},
	moves: 0,
	initialize: function( level ) {
		this.parent()

		this.ui_attachMetaEvents()
		this.ui_attachControlEvents()

		level || (level = 1)
		this.loadLevel(level || 1)
	},
	// loads & builds level # n
	loadLevel: function( n ) {
		this.loadMap(n, this.cb_loadMap)
	},
	// handles map download response
	cb_loadMap: function( game, rsp ) {
		game.level = rsp.level
		game.buildMap(rsp)
	},
	// loadLevel +1
	nextLevel: function() {
		return this.loadLevel(this.level + 1)
	},
	// loadLevel -1
	prevLevel: function() {
		return this.loadLevel(this.level - 1)
	},
	// loadLevel -n
	levelGo: function(n) {
		return this.loadLevel(this.level + n)
	},
	// downloads map
	loadMap: function( level, cb ) {
		var game = this
		$.post('?get_map=' + level, function( t ) {
			try {
				var rsp = JSON.parse(t)
			}
			catch ( ex ) {
				alert('Response error: ' + t)
				return;
			}

			cb(game, rsp)
		})
	},
	// builds map & html
	buildMap: function( rsp ) {
		// stats
		$('stats-level').html(this.level)

		var map = rsp.map,
			container = $('map-container').empty(),
			X, Y = map.length,
			x, y,
			tr, td,
			t, c

		for ( y=0; y<Y; y++ ) {
			tr = container.insertRow(y)
			for ( x=0, X = map[y].length; x<X; x++ ) {
				td = $(tr.insertCell(x))
				t = map[y][x]
				if ( ' ' != t ) {
					td.attr('data-type', t)
					td.type = t
					c = this.mapToClass[t] || t
					td.className = c
					td[c] = true
					if ( 'wall' == td.className ) {
						td.className += 0.5 < Math.random() ? '1' : '2'
					}
				}
				this.postBuildField(td, x, y)
			}
		}
	},
	postBuildField: function( field, x, y ) {},
	ui_attachMetaEvents: function() {
		var game = this
		$('btn-next-level').addEvent('click', function(e) {
			e.preventDefault()

			game.nextLevel()
		})
		$('btn-prev-level').addEvent('click', function(e) {
			e.preventDefault()

			game.prevLevel()
		})
		$('btn-load-level').addEvent('click', function(e) {
			e.preventDefault()

			var level = prompt('Level:', game.level)
			if ( null != level ) {
				game.loadLevel(level)
			}
		})
		$('btn-restart-level').addEvent('click', function(e) {
			e.preventDefault()

			game.loadLevel(game.level)
		})
	},
	ui_attachControlEvents: function() {},
	updateStats: function() {
		$('stats-moves').html(this.moves)
	}
})

/*BoardGame = Game.extend({
	board: [],
	clickField: function() {

	}
})*/

/*CumulariAbsolutus = BoardGame.extend({
	clickField: function() {

	}
})*/

