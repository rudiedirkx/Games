
function Minesweeper(field, session) {
	this.m_szName = '?';
	this.session = session;
	this.m_bCheating = false;

	this.fetchMap(field);
};
Minesweeper.prototype = {
	fetchMap: function(f_field) {
		var data = 'fetch_map=1&field=' + f_field;
		var options = {execScripts: false}
		var self = this;
		$.post('?fetch&session=' + this.session, data, options).on('load', function(e) {
			var rsp = this.responseJSON;
			if ( !rsp || rsp.error ) {
				alert(rsp ? rsp.error : this.responseText);
				return;
			}

			self.m_szField = f_field;
			self.m_iMines = rsp.mines;
			self.printMap(rsp.size.x, rsp.size.y);
		});
		return false;
	},

	restartMap: function() {
		var data = 'restart=1';
		var options = {execScripts: false}
		var self = this;
		$.post('?restart&session=' + this.session, data, options).on('load', function(e) {
			var rsp = this.responseJSON;
			self.printMap(rsp.size.x, rsp.size.y);
			self.handleChanges(rsp.updates);
		});
		return false;
	},

	printMap: function(w, h) {
		this.m_bGameOver = false;
		this.m_iGameOverTime = 0;
		this.m_arrFlags = [];

		$('#mines_to_find').textContent = String(this.m_iMines);
		$('#flags_left').textContent = String(this.m_iMines);
		$('#mine_percentage').textContent = String(Math.round(100 * this.m_iMines / (w * h)));

		// Save new map
		var html = '';
		for ( var y=0; y<h; y++ ) {
			html += '<tr>';
			for ( var x=0; x<w; x++ ) {
				html += '<td></td>';
			}
			html += '</tr>';
		}

		const tb = $('#ms_tbody');
		tb.innerHTML = html;
		tb.fire('ms:fetch');

		const sizer = tb.closest('.sizer');
		sizer.style.setProperty('--size-x', w);
		sizer.style.setProperty('--size-y', h);
	},

	handleChanges: function(cs) {
		for ( var i=0; i<cs.length; i++ ) {
			var c = cs[i]
			var f = $('#ms_tbody').rows[ c[1] ].cells[ c[0] ];
			f.className = 'o' + c[2];
		}
		return false;
	},

	showWrongFlags: function() {
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

	isOpenableField: function(o) {
		return !o.className.trim() || o.hasClass('ow') || o.hasClass('n');
	},

	openField: function(o, done) {
		if ( this.m_bGameOver ) {
			return this.newGame();
		}

		if ( !this.isOpenableField(o) ) {
			return;
		}

		var data = 'click=1&x=' + o.cellIndex + '&y=' + o.parentNode.sectionRowIndex;
		var options = {execScripts: false}
		var self = this;
		$.post('?click&session=' + this.session, data, options).on('load', function(e) {
			var rsp = this.responseJSON;
			if ( !rsp || rsp.error ) {
				alert(rsp ? rsp.error : this.responseText);
				return;
			}

			if ( rsp.gameover ) {
				self.m_bGameOver = true;
				self.m_iGameOverTime = Date.now();
				self.m_arrFlags = $$('#ms_tbody td.f');
			}

			self.handleChanges(rsp.updates);

			if ( self.m_bGameOver ) {
				if ( rsp.updates.length > 1 && rsp.updates.last().last() === 'x' ) {
					self.showWrongFlags();
				}

				Game.saveScore({
					level: rsp.level,
					time: rsp.time,
					score: (rsp.win ? 1 : 0) + (self.m_bCheating ? 10 : 0),
				});
			}

			if ( rsp.msg ) {
				setTimeout(function() {
					alert(rsp.msg);
				}, 60);
			}

			if ( done ) {
				done.call(self);
			}

			$('#ms_tbody').fire('ms:open');
		});
		return false;
	},

	isFlaggableField: function(o) {
		return this.isOpenableField(o) || o.hasClass('f');
	},

	toggleFlag: function(o) {
		if ( this.m_bGameOver ) {
			return this.newGame();
		}

		if ( !this.isFlaggableField(o) ) {
			return;
		}

		document.body.addClass('blip');
		setTimeout(function() {
			document.body.removeClass('blip');
		}, 100);

		o.toggleClass('f');
		if ( o.hasClass('f') ) {
			o.removeClass('ow').removeClass('n');
		}
	},

	updateFlagCounter: function() {
		var used = $('#ms_tbody').getElements('td.f').length;
		$('#flags_left').textContent = String(this.m_iMines - used);
	},

	newGame: function() {
		if ( !this.m_iGameOverTime || Date.now() - this.m_iGameOverTime > 1000 ) {
			this.fetchMap(this.m_szField);
		}
	},

	export: function(success, error) {
		var rows = [];
		$('#ms_tbody').getChildren().each(function(tr) {
			var row = '';
			tr.getChildren().each(function(cell) {
				var c = ' ';
				if ( cell.className.trim() && !cell.hasClass('f') && !cell.hasClass('n') ) {
					c = cell.className.substr(1);
				}
				row += c;
			});
			rows.push(row);
		});
		success && success.call(this, rows);
		return rows;
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
		$('#your_name').textContent = name;
	}
};

Minesweeper.prototype.constructor = Minesweeper;
