
var Minesweeper = new Class({
	initialize: function(field, session) {
		this.m_szName = '?';
		this.session = session;
		this.fetchMap(field);
	},

	fetchMap : function(f_field) {
		new Ajax('?fetch&session=' + this.session, {
			element : this,
			data : 'fetch_map=1&field=' + f_field,
			onComplete : function(t) {
				try {
					var rv = eval( "(" + t + ")" );
				} catch (e) {
					alert('Response error: '+t);
					return;
				}
				if ( rv.error ) {
					alert(rv.error);
					return;
				}
				var self = this.element;
				// Save level
				self.m_szField = f_field;
				self.m_bGameOver = false;
				self.m_iMines = rv.mines;
				self.m_arrFlags = [];
				$('mines_to_find').innerHTML = '' + self.m_iMines + '';
				$('flags_left').innerHTML = '' + self.m_iMines + '';
				$('mine_percentage').innerHTML = '' + Math.round(100 * rv.mines / (rv.size.y * rv.size.x)) + '';
				self.m_iFlagsUsed = 0;
				// empty current map
				while ( 0 < $('ms_tbody').childNodes.length ) {
					$('ms_tbody').removeChild($('ms_tbody').firstChild);
				}
				// Save new map
				for ( var y=0; y<rv.size.y; y++ ) {
					var nr = $('ms_tbody').insertRow($('ms_tbody').rows.length);
					for ( var x=0; x<rv.size.x; x++ ) {
						var nc = nr.insertCell(nr.cells.length);
						nc.className = 'c';
					}
				}
			}
		}).request();
		return false;
	},

	handleChanges : function(cs) {
		for ( var i=0; i<cs.length; i++ ) {
			var c = cs[i], f = $('ms_tbody').rows[c[1]].cells[c[0]];
//			if ( 'f' != f.className || !$range(0, 8).contains(c[2]) ) {
				f.className = 'o' + c[2] + '';
//			}
		}
		return false;
	},

	showWrongFlags : function( go ) {
		if ( !go ) { return; }
		for ( var i=0; i<this.m_arrFlags.length; i++ ) {
			var f = this.m_arrFlags[i];
			if ( f.className == 'f' ) {
				f.className = 'ow';
			}
			else {
				f.className = 'f';
			}
		}
	},

	openField : function(o) {
		if ( this.m_bGameOver ) { return this.restart(); }
		if ( 'c' != o.className ) { return false; }
		var self = this;
		new Ajax('?click&session=' + this.session, {
			data : 'click=1&x=' + o.cellIndex + '&y=' + o.parentNode.sectionRowIndex,
			onComplete : function(t) {
				var rv;
				try {
					rv = eval( "(" + t + ")" );
				} catch (e) {
					alert('Response error: '+t);
					return;
				}
				if ( rv.error ) {
					alert(rv.error);
					return;
				}
				if ( rv.gameover ) {
					self.m_bGameOver = true;
					self.m_arrFlags = $$('#ms_tbody td.f');
				}
				self.handleChanges(rv.updates);
				self.showWrongFlags( self.m_bGameOver && 1 < rv.updates.length && rv.updates.last().last() === 'x' );
				if ( rv.msg ) {
					alert(rv.msg);
				}
			}
		}).request();
		return false;
	},

	toggleFlag : function(o) {
		if ( this.m_bGameOver ) { return this.restart(); }
		if ( o.className == 'f' ) {
			o.className = 'c';
			o.flag = false;
			this.m_iFlagsUsed--;
//			this.m_arrFlags.splice(this.m_arrFlags.indexOf(o), 1);
		}
		else if ( o.className == 'c' ) {
			o.className = 'f';
			o.flag = true;
			this.m_iFlagsUsed++;
//			this.m_arrFlags.push(o);
		}
		$('flags_left').innerHTML = '' + ( this.m_iMines-this.m_iFlagsUsed ) + '';
	},

	restart : function() {
		return this.fetchMap(this.m_szField);
	},

	changeName : function(name) {
		name = name || prompt('New name:', this.m_szName);
		if ( !name ) return false;
		new Ajax('?session=' + this.session, {
			data : 'new_name=' + name,
			onComplete : this.setName.bind(this)
		}).request();
		return false;
	},

	setName: function(name) {
		this.m_szName = name;
		$('your_name').innerHTML = name;
	}
});
