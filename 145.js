
var debug = window.console && 'function' == typeof window.console.log;

function log() {
	debug && window.console.log.apply(console, arguments)
}

Element.extend({
	"nodirs": function() {
		return this
			.removeClass('dir-h')
			.removeClass('dir-v')
			.removeClass('dir-nw')
			.removeClass('dir-ne')
			.removeClass('dir-se')
			.removeClass('dir-sw');
	},
	"noexits": function() {
		return this
			.removeClass('exit-n')
			.removeClass('exit-e')
			.removeClass('exit-s')
			.removeClass('exit-w')
			.removeClass('single-exit')
			.removeClass('multiple-exit');
	}
});

$(function() {
	var o2d = {"n": 'v', "s": 'v', "w": 'h', "e": 'h'},
		o2o = {"n": 's', "s": 'n', "w": 'e', "e": 'w'};

	// board config
	var singular = 'singular' == TYPE;
	var multiple = 'multiple' == TYPE;
	var symmetric = !singular && !multiple;
// log('map cfg:', singular, multiple, symmetric)

	// env vars
	var c = $('#map-container');
	var cells = c.getElements('a');

	// process vars
	var type, dragIndex = 0, start, last, mousedowned;

	function validOrigin(cell, dragIndex) {
		var cx = ~~cell.data('x'),
			cy = ~~cell.data('y'),
			lx = ~~last.data('x'),
			ly = ~~last.data('y')

		if ( ly == cy ) {
			if ( lx + 1 == cx ) {
				return 'e'
			}
			else if ( lx - 1 == cx ) {
				return 'w'
			}
		}
		else if ( lx == cx ) {
			if ( ly + 1 == cy ) {
				return 's'
			}
			else if ( ly - 1 == cy ) {
				return 'n'
			}
		}
	}

	function bend(o1, o2) {
		if ( o1 == 'n' || o1 == 's' ) {
			return o1 + o2
		}

		return o2 + o1
	}

	function drag(e) {
		var cell = e.target;

		if ( cell.hasClass('cell') ) {
			// valid dragover
			if ( !cell.hasClass('line') && !cell.hasClass('pad') && !cell.hasClass('na') ) {
				var o = validOrigin(cell, dragIndex);
				var lo;
				if ( o ) {
					cell.data('origin', o)
						.addClass('drag-' + dragIndex)
						.addClass('line')
						.addClass('type-' + type)
						.addClass('dir-' + o2d[o]);
					if ( (lo = last.data('origin')) != o ) {
						if ( lo ) {
							last.nodirs().addClass('dir-' + bend(o2o[lo], o));
						}
						else {
							last.addClass('single-exit').addClass('exit').addClass('exit-' + o);
						}
					}
					last = cell;
					return;
				}
			}

			// drag target (pad)
			if ( cell.hasClass('pad') ) {
				if ( cell.data('type') == type && !cell.data('drag') ) {
					var sameOrigin = start == cell;
					var padsLeft = c.getElements('.pad.type-' + type + ':not(.drag-' + dragIndex + ')').filter(function(pad) {
						return cell != pad && start != pad;
					}).length;
log('sameOrigin:', sameOrigin, ', padsLeft:', padsLeft);
					if ( ( singular && sameOrigin ) || ( multiple && !sameOrigin && !padsLeft ) || ( symmetric && !sameOrigin ) ) {
						if ( !singular || 1 < $$('.drag-' + dragIndex).length ) {
							var o = validOrigin(cell, dragIndex), lo;
							if ( o ) {
								if ( cell.hasClass('exit') ) {
									cell.noexits().addClass('multiple-exit');
								}
								else {
									cell.addClass('single-exit').addClass('exit').addClass('exit-' + o2o[o]);
								}
								if ( (lo = last.data('origin')) != o ) {
									last.nodirs().addClass('dir-' + bend(o2o[lo], o));
								}
								return dragoff(1, cell);
							}
						}
					}

					if ( multiple && !sameOrigin && padsLeft ) {
						return;
					}
				}
			}
			dragoff();
		}
	}

	c.on('mousedown', function(e) {
		e.preventDefault();
		var cell = e.target;

		mousedowned = Date.now();
log(mousedowned);

		if ( cell.hasClass('cell') ) {
			if ( cell.hasClass('pad') ) {
				if ( !cell.data('drag') ) {
					type = cell.data('type');
					last = start = cell;
					dragIndex++;

log('on: drag');
					cells.on('mouseover', drag);
					c.on('mouseleave', function(e) {
						dragoff();
					})
				}
			}
		}
	})

	function dragoff(correctly, end) {
		last = null;

		if ( type ) {
log('off: drag');
			cells.off('mouseover', drag);
			if ( correctly ) {
				// 'disable' pads
				start.data('drag', dragIndex).addClass('done').addClass('drag-' + dragIndex);
				end.data('drag', dragIndex).addClass('done').addClass('drag-' + dragIndex);

				// verify trail/line
				c.getElements('.line.drag-' + dragIndex).addClass('done');

				// check for open pads
				if ( !c.getElements('.pad:not(.done)').length ) {
					// game over!
					var energy = c.getElements('.line').length;
					$('#message')
						.setHTML('You\'re done! You spent ' + energy + ' energy. <a href="?board=' + (LEVEL+1) + '">Next board?</a>')
						.addClass('news');
				}
			}
			else {
				$$('.drag-' + dragIndex)
					.removeClass('drag-' + dragIndex)
					.removeClass('line')
					.removeClass('type-' + type)
					.nodirs();
			}
			type = start = undefined;
		}
	}

	c.on('click', function(e) {
		e.preventDefault();

		var klicktime = Date.now() - mousedowned;
log(klicktime);
		if ( 250 > klicktime ) {
			cell = e.target;
			if ( cell.hasClass('pad') && cell.data('drag') ) {
				var di = cell.data('drag');
				// undo lines
				c.getElements('.line.drag-' + di)
					.nodirs()
					.removeClass('drag-' + di)
					.removeClass('type-' + cell.data('type'))
					.removeClass('line')
					.removeClass('done');
				// undo pads
				c.getElements('.pad.drag-' + di)
					.removeClass('drag-' + di)
					.removeClass('done')
					.data('drag', null)
					.removeClass('exit-n')
					.removeClass('exit-e')
					.removeClass('exit-s')
					.removeClass('exit-w');
			}
		}
	})

	c.on('mouseup', function(e) {
		var cell = e.target;

		if ( cell.hasClass('cell') ) {
			if ( cell.hasClass('pad') ) {
// log('c.mouseup');
				if ( type == cell.data('type') && start != cell ) {
					// dragoff(1, cell);
				}
			}
		}
	});

	document.on('mouseup', function(e) {
// log('doc.mouseup');
		dragoff();
	});

})
