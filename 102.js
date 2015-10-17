
function Minesweeper(field, session) {
	this.m_szName = '?';
	this.session = session;
	this.fetchMap(field);
};
Minesweeper.prototype = {
	fetchMap: function(f_field) {
		var data = 'fetch_map=1&field=' + f_field;
		var options = {execScripts: false}
		var self = this;
		$.post('?fetch&session=' + this.session, data, options).on('load', function(e) {
			var rsp = this.responseText;
			try {
				rsp = JSON.parse(rsp);
			}
			catch (ex) {
				alert('Response error: ' + rsp);
			}
			if (rsp.error) {
				alert(rsp.error);
				return;
			}

			self.m_szField = f_field;
			self.m_bGameOver = false;
			self.m_iMines = rsp.mines;
			self.m_arrFlags = [];

			$('mines_to_find').textContent = String(self.m_iMines);
			$('flags_left').textContent = String(self.m_iMines);
			$('mine_percentage').textContent = String(Math.round(100 * rsp.mines / (rsp.size.y * rsp.size.x)));

			self.m_iFlagsUsed = 0;

			// Save new map
			var html = '';
			for ( var y=0; y<rsp.size.y; y++ ) {
				html += '<tr>';
				for ( var x=0; x<rsp.size.x; x++ ) {
					html += '<td></td>';
				}
				html += '</tr>';
			}
			$('ms_tbody').innerHTML = html;
		});
		return false;
	},

	handleChanges: function(cs) {
		for ( var i=0; i<cs.length; i++ ) {
			var c = cs[i]
			var f = $('ms_tbody').rows[ c[1] ].cells[ c[0] ];
			f.className = 'o' + c[2];
		}
		return false;
	},

	showWrongFlags: function( go ) {
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

	openField: function(o, done) {
		if ( this.m_bGameOver ) {
			return this.restart();
		}

		if ( o.hasClass('f') ) {
			return false;
		}

		var data = 'click=1&x=' + o.cellIndex + '&y=' + o.parentNode.sectionRowIndex;
		var options = {execScripts: false}
		var self = this;
		$.post('?click&session=' + this.session, data, options).on('load', function(e) {
			var rsp = this.responseText;
			try {
				rsp = JSON.parse(rsp);
			}
			catch (ex) {
				alert('Response error: ' + rsp);
			}
			if (rsp.error) {
				alert(rsp.error);
				return;
			}

			if ( rsp.gameover ) {
				self.m_bGameOver = true;
				self.m_arrFlags = $$('#ms_tbody td.f');
			}

			self.handleChanges(rsp.updates);

			self.showWrongFlags( self.m_bGameOver && 1 < rsp.updates.length && rsp.updates.last().last() === 'x' );

			if ( rsp.msg ) {
				setTimeout(function() {
					alert(rsp.msg);
				}, 1);
			}

			if ( done ) {
				done.call(self);
			}
		});
		return false;
	},

	toggleFlag: function(o) {
		if ( this.m_bGameOver ) {
			return this.restart();
		}

		var flaggable = !o.className || o.className == 'f';
		if ( !flaggable ) {
			return;
		}

		o.toggleClass('f');
		if ( o.hasClass('f') ) {
			this.m_iFlagsUsed++;
		}
		else {
			this.m_iFlagsUsed--;
		}

		$('flags_left').textContent = String(this.m_iMines-this.m_iFlagsUsed);
	},

	restart: function() {
		return this.fetchMap(this.m_szField);
	},

	changeName: function(name) {
		name || (name = prompt('New name:', this.m_szName));
		if ( !name ) {
			return false;
		}

		var data = 'new_name=' + name;
		var options = {execScripts: false}
		var self = this;
		$.post('?session=' + this.session, data, options).on('load', function(e) {
			self.setName(this.responseText);
		});

		return false;
	},

	setName: function(name) {
		this.m_szName = name;
		$('your_name').textContent = name;
	}
};

Minesweeper.prototype.constructor = Minesweeper;
