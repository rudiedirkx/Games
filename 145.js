
var debug = 1;

function log() {
	debug && window.console && 'function' == typeof window.console.log && window.console.log.apply(console, arguments)
}

$(function() {
	var directions = ['v', 'h', 'sw', 'nw', 'ne', 'se']

	var c = $('#map-container'),
		cells = c.find('a')
	var type, dragIndex = 0, start

	function drag(e, cell) {
		cell = $(e.target)
		if ( cell.hasClass('cell') ) {
			if ( !cell.hasClass('line') && !cell.hasClass('pad') ) {
				cell.addClass('drag-' + dragIndex).addClass('line').addClass('type-' + type)
			}
			else {
				if ( cell.hasClass('pad') ) {
					if ( cell.data('type') == type && !cell.data('drag') && start[0] != cell[0] ) {
						return dragoff(1, cell)
					}
				}
				dragoff()
			}
		}
	}

	c.on('mousedown', function(e, cell) {
		e.preventDefault()
		cell = $(e.target)

		if ( cell.hasClass('cell') ) {
			if ( cell.hasClass('pad') ) {
				if ( !cell.data('drag') ) {
					type = cell.data('type')
					start = cell
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
