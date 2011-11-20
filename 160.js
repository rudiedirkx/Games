
Pixelus = LevelableGame.extend({
	stones: 0,
	mapToClass: {
		x: 'wall',
		o: 'target'
	},
	cb_loadMap: function( game, rsp ) {
		game.stones = rsp.stones
		this.parent(game, rsp)
	},
	postBuildField: function( field, x, y ) {
		field.html('<span></span>')
	},
	ui_attachControlEvents: function() {
		var game = this
		$('map-container').addEvent('click', function(e) {
			if ( 'SPAN' == e.target.nodeName ) {
				var field = e.target.parentNode,
					x = field.cellIndex,
					y = field.parentNode.sectionRowIndex
				game.clickField(field, x, y)
			}
		})
	},
	clickField: function( field, x, y ) {
		if ( !field.wall ) {
			if ( !field.hasClass('stone') ) {
				// sling stone here
				this.slingStone(field)
			}
			else {
				// remove stone
				this.removeStone(field)
			}
		}
	},
	slingStone: function( target ) {
		if ( 0 < this.stones ) {
			if ( this.isReachableField(target) ) {
				target.addClass('stone')
				this.stones--
				this.moves++

				this.updateStats()
			}
		}
	},
	isReachableField: function( field ) {
		return true
	},
	removeStone: function( field ) {
		field.removeClass('stone')
		this.stones++
		this.moves++

		this.updateStats()
	},
	updateStats: function() {
		this.parent()
		$('stats-stones').html(this.stones)
		$('map-container')[this.stones ? 'removeClass' : 'addClass']('actionless')
	}
})
