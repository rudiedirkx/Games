<?php
// LINX builder

header('Content-type: text/html; charset="utf-8"');

$_types = range(1, 6);

$board = (object)array(
	'rows' => 10,
	'cols' => 12,
	'map' => array(),
);
if ( isset($_GET['map'], $_GET['type']) ) {
	$board = (object)$_GET;
	$board->rows = count($board->map);
	$board->cols = strlen($board->map[0]);
}

?>
<!doctype html>
<html lang="en">

<head> 
<meta charset="utf-8" />
<title>Linx</title>
<link rel="stylesheet" href="/145.css" />
<style>
.legend {
	float: right;
	width: 160px;
	padding: 10px;
	text-align: left;
	background: #eee;
}
.legend p:not(:first-child) {
	margin-top: 10px;
}
.legend p:not(:last-child) {
	margin-bottom: 10px;
}
.legend p.pad {
	line-height: 33px;
	padding: 5px;
	margin: 0;
}
.legend p.pad.active {
	background-color: #ddd;
}
.legend .cell {
	cursor: pointer;
}
.legend .title {
	margin-left: 10px;
	float: left;
}
.btn {
	font-weight: bold;
	text-decoration: none;
}
.btn:before,
.btn:after {
	content: " > ";
}
.btn:hover,
.btn:focus {
	color: #c00;
}
.legend p:after {
	content: "";
	display: block;
	clear: both;
	height: 0;
}
</style>
</head>

<body>

<div class="game">
	<div id="map-container" class="m">
		<?php

		echo '<div class="row">' . str_repeat('<a class="cell na"></a>', $board->cols+2) . '</div>' . "\n";

		for ( $y=0; $y<$board->rows; $y++ ) {
			echo '<div class="row">' . "\n";
			echo '<a class="cell na"></a>' . "\n";

			for ( $x=0; $x<$board->cols; $x++ ) {
				$tile = isset($board->map[$y][$x]) ? trim($board->map[$y][$x]) : '';

				$type = '';
				$text = '';

				$classes = array('cell');
				if ( is_numeric($tile) ) {
					$classes[] = 'pad';
					$classes[] = 'type-' . $tile;

					$type = ' data-type="' . $tile . '"';

					$text = $tile;
				}
				else if ( 'x' == $tile ) {
					$classes[] = 'na';
				}

				$class = $classes ? ' class="'.implode(' ', $classes).'"' : '';

				echo '<a' . $type . $class . '>' . $text . '</a>' . "\n";
			}

			echo '<a class="cell na"></a>' . "\n";
			echo '</div>' . "\n";
		}

		echo '<div class="row">' . str_repeat('<a class="cell na"></a>', $board->cols+2) . '</div>' . "\n";

		?>
	</div>

	<div class="legend">
		<p>Click a pad below and then the board to place it:</p>
		<?foreach( $_types AS $t ):?>
			<p class="pad">
				<a data-type="<?=$t?>" class="cell pad type-<?=$t?>"><?=$t?></a>
				<span class="title">Type <?=$t?></span>
			</p>
		<?endforeach?>
		<p>Double click a cell on the board to disable/enable it.</p>
		<p>
			<select id="val-type">
				<?=options(array(
					'' => '-- choose type',
					'singular' => 'singular',
					'symmetric' => 'symmetric',
					'multiple' => 'multiple',
				), @$_GET['type'])?>
			</select>
		</p>
		<p><a id="btn-save" class="btn" href="#" title="Creates a reusable URL that you can share, try and alter">'SAVE'</a></p>
		<p><a id="btn-play" class="btn" href="#" title="REMEMBER TO SAVE FIRST!

Uses the resusable URL on the actual game so you can try it out.

REMEMBER TO SAVE FIRST!">PLAY</a></p>
		<p>Remember to always SAVE!</p>
	</div>
</div>

<img class="preload" src="/images/145-lines.png" alt="preloading lines sprite" />

<script src="//code.jquery.com/jquery-latest.js"></script>
<script>
$(function() {
	// env
	c = $('#map-container'),
		pads = $('.legend a.pad')

	// process
	var type;

	function setType(pad) {
		pad.parents('p').addClass('active')
		type = pad.data('type')
	}
	function unsetType() {
		pads.parents('p').removeClass('active')
		type = null
	}

	// legend events
	pads.on('click', function(e) {
		e.preventDefault()
		var pad = $(this)

		// disable previous
		unsetType()

		// enable new
		setType(pad)
	})

	function outerEdge(cell) {
		var edges = []

		if ( !cell.next().length ) {
			edges.push('xmax')
		}
		else if ( !cell.prev().length ) {
			edges.push('xmin')
		}

		var row = cell.parent()
		if ( !row.next().length ) {
			edges.push('ymax')
		}
		else if ( !row.prev().length ) {
			edges.push('ymin')
		}

		return edges
	}

	var extensions = {
		"cell": function() {
			var cell = document.createElement('a')
			cell.className = 'cell na'
			return cell
		},
		"xmax": function() {
			c.children().each(function(i, row) {
				row.appendChild(extensions.cell())
			})
		},
		"xmin": function() {
			c.children().each(function(i, row) {
				row.insertBefore(extensions.cell(), row.querySelector('.cell'))
			})
		},
		"ymax": function() {
			var row = document.createElement('div')
			row.className = 'row'

			c.first().children().each(function() {
				row.appendChild(extensions.cell())
			})

			c[0].appendChild(row)
		},
		"ymin": function() {
			var row = document.createElement('div')
			row.className = 'row'

			c.first().children().each(function() {
				row.appendChild(extensions.cell())
			})

			c[0].insertBefore(row, c[0].querySelector('.row'))
		}
	}

	function setCell(cell, type) {
		cell
			.attr('data-type', type)
			.data('type', type)
			.addClass('pad')
			.addClass('type-' + type)
			.text(type)
	}
	function unsetCell(cell) {
		var type = cell.data('type')
		cell
			.removeAttr('data-type')
			.data('type', null)
			.removeClass('pad')
			.removeClass('type-' + type)
			.text('')
	}

	// board events
	c.on('mousedown', function(e) {
		e.preventDefault()
	}).on('click', function(e) {
		e.preventDefault()
		var cell = $(e.target)

		if ( cell.hasClass('cell') && !cell.hasClass('na') ) {
			var ct = cell.data('type')
			if ( type ) {
				if ( type != ct ) {
					// unset
					if ( ct ) {
						unsetCell(cell)
					}
					// set
					setCell(cell, type)
				}
				else {
					// unset
					unsetCell(cell)
				}
			}
		}
	}).on('dblclick', function(e) {
		e.preventDefault()
		var cell = $(e.target)

		if ( cell.hasClass('cell') ) {
			unsetCell(cell)
			cell.toggleClass('na')

			var edges = outerEdge(cell)
			if ( !cell.hasClass('na') && edges.length ) {
				edges.forEach(function(edge) {
					extensions[edge]()
				})
			}
		}
	})

	function getMap() {
		var rows = [],
			ch1 = c.children(),
			ch1L = ch1.length
		ch1.each(function(i, row) {
			if ( i && i+1 < ch1L ) {
				var cols = '',
					ch2 = $(row).children(),
					ch2L = ch2.length
				ch2.each(function(i, cell) {
					if ( i && i+1 < ch2L ) {
						var cell = $(cell),
							t = cell.data('type')
						if ( t ) {
							cols += '' + t
						}
						else if ( cell.hasClass('na') ) {
							cols += 'x'
						}
						else {
							cols += ' '
						}
					}
				})
				rows.push(cols)
			}
		})

		return rows
	}

	function stringifyMap(map, params) {
		var qs = [], x

		for ( x in params ) {
			qs.push(x + '=' + params[x])
		}

		map.forEach(function(row) {
			qs.push('map[]=' + row.replace(/ /g, '+'))
		})

		return qs.join('&')
	}

	function getBoard() {
		var map = getMap(),
			board = stringifyMap(map, {
				"type": $('#val-type').val()
			})

		return board
	}

	$('#btn-save').on('click', function(e) {
		e.preventDefault()
		location.search = getBoard()
	})

	$('#btn-play').on('click', function(e) {
		e.preventDefault()
		location = '/145?' + getBoard()
	})
})
</script>

</body>

</html>
<?php

function options( $options, $selected = '' ) {
	$html = '';

	foreach ( $options AS $value => $text ) {
		$html .= '<option value="' . $value . '"' . ( $value == $selected ? ' selected' : '' ) . '>' . htmlspecialchars($text) . '</option>';
	}

	return $html;
}


