
class Pixelus extends LevelableGame {
	constructor( level = 1 ) {
		super(level);

		this.stones = 0;
		this.mapToClass = {
			x: 'wall',
			o: 'target',
		}
	}

	cb_loadMap( rsp ) {
		this.stones = rsp.stones;
		super.cb_loadMap(rsp);

		this.updateStats();
	}

	postBuildField( field, x, y ) {
		field.setHTML('<span></span>');
	}

	ui_attachControlEvents() {
		$('#map-container').on('click', 'td', (e) => {
			var x = e.subject.cellIndex;
			var y = e.subject.parentNode.sectionRowIndex;
			this.clickField(e.subject, x, y);
		});
	}

	clickField( field, x, y ) {
		if ( !field.hasClass('wall') ) {
			if ( !field.hasClass('stone') ) {
				// sling stone here
				this.slingStone(field);
			}
			else {
				// remove stone
				this.removeStone(field);
			}

			if ( $$('#map-container .target:not(.stone)').length == 0 ) {
				setTimeout(function() {
					alert('You win!');
				}, 100);
			}
		}
	}

	slingStone( target ) {
		if ( 0 < this.stones ) {
			if ( this.isReachableField(target) ) {
				target.addClass('stone');
				this.stones--;
				this.moves++;
			}
		}

		this.updateStats();
	}

	isReachableField( field ) {
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
	}

	pathIsFree( startField, direction ) {
		var cd = BoardGame.nesw[direction];
		var neighbor = startField;
		while ( neighbor = this.getNeighborField(neighbor, cd) ) {
			if ( this.isSolid(neighbor) ) {
				return false;
			}
		}

		return true;
	}

	isSolid( field ) {
		return field.hasClass('wall') || field.hasClass('stone');
	}

	getNeighborField( field, cd ) {
		var grid = field.parentNode.parentNode;
		var x = field.cellIndex,
			y = field.parentNode.sectionRowIndex;
		return grid.rows[ y + cd[1] ] && grid.rows[ y + cd[1] ].cells[ x + cd[0] ];
	}

	removeStone( field ) {
		// @todo Check for pathIsFree

		field.removeClass('stone');
		this.stones++;
		this.moves++;

		this.updateStats();
	}

	updateStats() {
		super.updateStats();

		$('#stats-stones').setText(this.stones);
		$('#map-container').toggleClass('actionless', !this.stones);
	}
}
