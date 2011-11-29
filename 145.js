
var debug = window.console && 'function' == typeof window.console.log;

function log() {
	debug && window.console.log.apply(console, arguments)
}

jQuery.fn.nodirs = function() {
	return this
		.removeClass('dir-h')
		.removeClass('dir-v')
		.removeClass('dir-nw')
		.removeClass('dir-ne')
		.removeClass('dir-se')
		.removeClass('dir-sw')
}

$(function() {
	var o2d = {"n": 'v', "s": 'v', "w": 'h', "e": 'h'},
		o2o = {"n": 's', "s": 'n', "w": 'e', "e": 'w'}

	// env vars
	var c = $('#map-container'),
		cells = c.find('a')
	// process vars
	var type, dragIndex = 0, start, last

	function validOrigin(cell, dragIndex) {
		var cx = cell.data('x'),
			cy = cell.data('y'),
			lx = last.data('x'),
			ly = last.data('y')

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

	function drag(e, cell) {
		cell = $(e.target)

		if ( cell.hasClass('cell') ) {
			// valid dragover
			if ( !cell.hasClass('line') && !cell.hasClass('pad') ) {
				var o = validOrigin(cell, dragIndex), lo
				if ( o ) {
log('drag', o)
					cell.data('origin', o)
						.addClass('drag-' + dragIndex)
						.addClass('line')
						.addClass('type-' + type)
						.addClass('dir-' + o2d[o])
					if ( (lo = last.data('origin')) != o ) {
						if ( lo ) {
							last.nodirs().addClass('dir-' + bend(o2o[lo], o))
						}
						else {
							last.addClass('exit-' + o)
						}
					}
					last = cell
					return
				}
			}

			if ( cell.hasClass('pad') ) {
				// end pad
				if ( cell.data('type') == type && !cell.data('drag') && start[0] != cell[0] ) {
					var o = validOrigin(cell, dragIndex)
					if ( o ) {
						cell.addClass('exit-' + o2o[o])
						if ( last.data('origin') != o ) {
							last.nodirs().addClass('dir-' + bend(o2o[last.data('origin')], o))
						}
						return dragoff(1, cell)
					}
				}
			}
			dragoff()
		}
	}

	c.on('mousedown', function(e, cell) {
		e.preventDefault()
		cell = $(e.target)

		if ( cell.hasClass('cell') ) {
			if ( cell.hasClass('pad') ) {
				if ( !cell.data('drag') ) {
					type = cell.data('type')
					last = start = cell
					dragIndex++

					log('on: drag')
					cells.on('mouseover', drag)
					c.on('mouseleave', function(e) {
						dragoff()
					})
				}
			}
		}
	})

	function dragoff(correctly, end) {
		last = null

		if ( type ) {
			log('off: drag')
			cells.off('mouseover', drag)
			if ( correctly ) {
				// 'disable' pads
				start.data('drag', dragIndex).addClass('done').addClass('drag-' + dragIndex)
				end.data('drag', dragIndex).addClass('done').addClass('drag-' + dragIndex)

				// verify trail/line
				c.find('.line.drag-' + dragIndex).addClass('done')

				// check for open pads
				if ( !c.find('.pad:not(.done)').length ) {
					// game over!
					var energy = c.find('.line').length
					$('#message').html('You\'re done! You spent ' + energy + ' energy. <a href="?board=' + (LEVEL+1) + '">Next board?</a>').addClass('news')
				}
			}
			else {
				$('.drag-' + dragIndex)
					.removeClass('drag-' + dragIndex)
					.removeClass('line')
					.removeClass('type-' + type)
					.nodirs()
			}
			type = start = undefined
		}
	}

	c.on('click', function(e) {
		e.preventDefault()

		cell = $(e.target)
		if ( cell.hasClass('pad') && cell.data('drag') ) {
			var di = cell.data('drag')
			// undo lines
			c.find('.line.drag-' + di)
				.nodirs()
				.removeClass('drag-' + di)
				.removeClass('type-' + cell.data('type'))
				.removeClass('line')
				.removeClass('done')
			// undo pads
			c.find('.pad.drag-' + di)
				.removeClass('drag-' + di)
				.removeClass('done')
				.data('drag', null)
				.removeClass('exit-n')
				.removeClass('exit-e')
				.removeClass('exit-s')
				.removeClass('exit-w')
		}
	})

	c.on('mouseup', function(e, cell) {
		cell = $(e.target)

		if ( cell.hasClass('cell') ) {
			if ( cell.hasClass('pad') ) {
				//log('c.mouseup')
				if ( type == cell.data('type') && start[0] != cell[0] ) {
					dragoff(1, cell)
				}
			}
		}
	})

	$(document).on('mouseup', function(e) {
		//log('doc.mouseup')
		dragoff()
	})
})
