
Pixelus = LevelableGame.extend({
	stones: 0,
	mapToClass: {
		x: 'wall',
		o: 'target',
	},
	cb_loadMap: function( game, rsp ) {
		game.stones = rsp.stones;
		this.parent(game, rsp);

		game.updateStats();
	},
	postBuildField: function( field, x, y ) {
		field.html('<span></span>');
	},
	ui_attachControlEvents: function() {
		var game = this;
		$('map-container').addEvent('click', function(e) {
			if ( 'SPAN' == e.target.nodeName ) {
				var field = e.target.parentNode,
					x = field.cellIndex,
					y = field.parentNode.sectionRowIndex;
				game.clickField(field, x, y);
			}
		})
	},
	clickField: function( field, x, y ) {
		if ( !field.wall ) {
			if ( !field.hasClass('stone') ) {
				// sling stone here
				this.slingStone(field);
			}
			else {
				// remove stone
				this.removeStone(field);
			}
		}
	},
	slingStone: function( target ) {
		if ( 0 < this.stones ) {
			if ( this.isReachableField(target) ) {
				target.addClass('stone');
				this.stones--;
				this.moves++;
			}
		}

		this.updateStats();
	},
	isReachableField: function( field ) {
		for ( var d=0; d<4; d++ ) {
			var cd = BoardGame.nesw[d];
			var nf = this.getNeighborField(field, cd);
			if ( nf && this.isSolid(nf) ) {
				if ( this.pathIsFree(field, (d+2)%4) ) {
					return true;
				}
			}
		}

		return false;
	},
	pathIsFree: function( startField, direction ) {
		var cd = BoardGame.nesw[direction];
		var neighbor = startField;
		while ( neighbor = this.getNeighborField(neighbor, cd) ) {
			if ( this.isSolid(neighbor) ) {
				return false;
			}
		}

		return true;
	},
	isSolid: function( field ) {
		return field.hasClass('wall') || field.hasClass('stone');
	},
	getNeighborField: function( field, cd ) {
		var grid = field.parentNode.parentNode;
		var x = field.cellIndex,
			y = field.parentNode.sectionRowIndex;
		return grid.rows[ y + cd[1] ] && grid.rows[ y + cd[1] ].cells[ x + cd[0] ];
	},
	removeStone: function( field ) {
		// @todo Check for pathIsFree

		field.removeClass('stone');
		this.stones++;
		this.moves++;

		this.updateStats();
	},
	updateStats: function() {
		this.parent();
		$('stats-stones').html(String(this.stones));
		$('map-container')[this.stones ? 'removeClass' : 'addClass']('actionless');
	}
})
