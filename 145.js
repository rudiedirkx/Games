
var debug = window.console && 'function' == typeof window.console.log;

function log() {
	debug && window.console.log.apply(console, arguments)
}

$(function() {

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
			if ( lx + 1 == cx || lx - 1 == cx ) {
				return 'h'
			}
		}
		else if ( lx == cx ) {
			if ( ly + 1 == cy || ly - 1 == cy ) {
				return 'v'
			}
		}
	}

	function drag(e, cell) {
		cell = $(e.target)

		if ( cell.hasClass('cell') ) {
			// valid dragover
			if ( !cell.hasClass('line') && !cell.hasClass('pad') ) {
				var o = validOrigin(cell, dragIndex)
				if ( o ) {
					cell.addClass('drag-' + dragIndex)
						.addClass('line')
						.addClass('type-' + type)
						.addClass('dir-' + o)
					last = cell
					return
				}
			}

			if ( cell.hasClass('pad') ) {
				// end pad
				if ( cell.data('type') == type && !cell.data('drag') && start[0] != cell[0] ) {
					return dragoff(1, cell)
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
				start.data('drag', dragIndex).addClass('done')
				end.data('drag', dragIndex).addClass('done')
				if ( !c.find('.pad:not(.done)').length ) {
					// game over!
					var energy = c.find('.line').length
					$('#message').text("You're done! You spent " + energy + " energy. Next level?").addClass('news')
				}
			}
			else {
				$('.drag-' + dragIndex)
					.removeClass('drag-' + dragIndex)
					.removeClass('line')
					.removeClass('type-' + type)
			}
			type = start = undefined
		}
	}

	c.on('click', function(e) {
		e.preventDefault()
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
