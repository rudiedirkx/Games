<?php

$g_arrLevels = array(
	array(
		'map' => array(
			' xxxxxx',
			'xx  t x',
			'x t x x',
			'x t   x',
			'x  x  x',
			'xx    x',
			' x    x',
			' xxxxxx',
		),
		'pusher' => array(3, 5),
		'boxes' => array(
			array(2, 2),
			array(3, 3),
			array(4, 4),
		),
	),

	array(
	    'map' => array(
			'xxxxxxx',
			'x  t  x',
			'x xtx x',
			'x     x',
			'xt    x',
			'x  xxxx',
			'xxxx   ',
		),
		'pusher' => array(4, 1),
		'boxes' => array(
			array(4, 3),
			array(2, 4),
			array(3, 4),
		),
	),

	array(
		'map' => array(
			'   xxxx',
			'xxxx  x',
			'x  t  x',
			'x     x',
			'xx txxx',
			' x  x  ',
			' x tx  ',
			' xxxx  ',
		),
		'pusher' => array(5, 1),
		'boxes' =>  array(
			array(3, 2),
			array(4, 2),
			array(2, 5),
		),
	),
	array(
		'map' => array(
			'xxx xxx',
			'xtxxxtx',
			'x x  tx',
			'x     x',
			'x     x',
			'x  x  x',
			'x  xxxx',
			'xxxx   ',
		),
		'pusher' => array(5, 3),
		'boxes' => array(
			array(2, 3),
			array(3, 3),
			array(3, 4),
		),
	),
	array(
		'map' => array(
			'   xxxx ',
			'   x  xx',
			'xxxx   x',
			'xt x   x',
			'x     xx',
			'xt   xx ',
			'xxt  x  ',
			' xxxxx  ',
		),
		'pusher' => array(5, 1),
		'boxes' => array(
			array(4, 3),
			array(5, 3),
			array(4, 5),
		),
	),
	array(
		'map' => array(
			'xxxxx   ',
			'x ttxxxx',
			'x      x',
			'x  x x x',
			'x   t  x',
			'xxxxxxxx',
		),
		'pusher' => array(2, 4),
		'boxes' => array(
			array(2, 2),
			array(4, 3),
			array(5, 4),
		),
	),
	array(
		'map' => array(
			'  xxxxx',
			'xxx  tx',
			'x   x x',
			'x t   x',
			'x tx  x',
			'x    xx',
			'x   xx ',
			'xxxxx  ',
		),
		'pusher' => array(4, 4),
		'boxes' => array(
			array(2, 2),
			array(2, 3),
			array(3, 3),
		),
	),
	array(
		'map' => array(
			'xxxxxxxxxxxx  ',
			'xtt  x     xxx',
			'xtt  x       x',
			'xtt  x xxxx  x',
			'xtt      xx  x',
			'xtt  x x     x',
			'xxxxxx xx    x',
			'  x          x',
			'  x    x     x',
			'  xxxxxxxxxxxx',
		),
		'pusher' => array(7, 4),
		'boxes' => array(
			array(7, 2),
			array(10, 2),
			array(6, 3),
			array(10, 5),
			array(9, 6),
			array(11, 6),
			array(4, 7),
			array(7, 7),
			array(9, 7),
			array(11, 7),
		),
	),
	array(
		'map' => array(
			'xxxxx  ',
			'xt txxx',
			'xtx   x',
			'x     x',
			'x  x  x',
			'xx   xx',
			' xxxxx ',
		),
		'pusher' => array(4, 3),
		'boxes' => array(
			array(3, 2),
			array(2, 4),
			array(4, 2),
		),
	),
  array (
    'map' => 
    array (
      0 => 'xxxxx  ',
      1 => 'xt  xxx',
      2 => 'x x   x',
      3 => 'x t x x',
      4 => 'x  t  x',
      5 => 'xx  xxx',
      6 => ' x  x  ',
      7 => ' xxxx  ',
    ),
    'pusher' => 
    array (
      0 => 2,
      1 => 5,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 2,
        1 => 4,
      ),
      1 => 
      array (
        0 => 3,
        1 => 4,
      ),
      2 => 
      array (
        0 => 4,
        1 => 4,
      ),
    ),
  ),
  array (
    'map' => 
    array (
      0 => 'xxxxxxxx',
      1 => 'xt   t x',
      2 => 'x x x  x',
      3 => 'x    tx',
      4 => 'xxxxx  x',
      5 => '    x  x',
      6 => '    xxxx',
    ),
    'pusher' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 2,
        1 => 3,
      ),
      1 => 
      array (
        0 => 5,
        1 => 3,
      ),
      2 => 
      array (
        0 => 6,
        1 => 4,
      ),
    ),
  ),
  array (
    'map' => 
    array (
      0 => 'xxxx    ',
      1 => 'x  x    ',
      2 => 'x  xxxxx',
      3 => 'x tt   x',
      4 => 'xx     x',
      5 => ' x x xxx',
      6 => ' xt  x  ',
      7 => ' xxxxx  ',
    ),
    'pusher' => 
    array (
      0 => 4,
      1 => 6,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 3,
        1 => 3,
      ),
      1 => 
      array (
        0 => 2,
        1 => 4,
      ),
      2 => 
      array (
        0 => 4,
        1 => 5,
      ),
    ),
  ),
  array (
    'map' => 
    array (
      0 => ' xxxxx  ',
      1 => ' x   xxx',
      2 => 'xx t   x',
      3 => 'xt t  x',
      4 => 'xx x xxx',
      5 => ' x   x  ',
      6 => ' xxxxx  ',
    ),
    'pusher' => 
    array (
      0 => 3,
      1 => 1,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 3,
        1 => 3,
      ),
      1 => 
      array (
        0 => 4,
        1 => 3,
      ),
      2 => 
      array (
        0 => 2,
        1 => 4,
      ),
    ),
  ),
  array (
    'map' => 
    array (
      0 => ' xxxxx ',
      1 => 'xx   x ',
      2 => 'x  x x ',
      3 => 'x t  xx',
      4 => 'x t   x',
      5 => 'xx x  x',
      6 => ' xt  xx',
      7 => ' xxxxx ',
    ),
    'pusher' => 
    array (
      0 => 4,
      1 => 3,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 2,
        1 => 2,
      ),
      1 => 
      array (
        0 => 2,
        1 => 4,
      ),
      2 => 
      array (
        0 => 4,
        1 => 5,
      ),
    ),
  ),
  array (
    'map' => 
    array (
      0 => ' xxxx   ',
      1 => 'xx  xxxx',
      2 => 'xtt   tx',
      3 => 'x x    x',
      4 => 'x   x  x',
      5 => 'xxxxx  x',
      6 => '    xxxx',
    ),
    'pusher' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 3,
        1 => 2,
      ),
      1 => 
      array (
        0 => 3,
        1 => 3,
      ),
      2 => 
      array (
        0 => 5,
        1 => 3,
      ),
    ),
  ),
  array (
    'map' => 
    array (
      0 => ' xxxxxx ',
      1 => ' x  t xx',
      2 => ' x   tx',
      3 => ' xxxtx x',
      4 => 'xx     x',
      5 => 'x     xx',
      6 => 'x   xxx ',
      7 => 'xxxxx   ',
    ),
    'pusher' => 
    array (
      0 => 5,
      1 => 1,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 5,
        1 => 2,
      ),
      1 => 
      array (
        0 => 4,
        1 => 3,
      ),
      2 => 
      array (
        0 => 3,
        1 => 5,
      ),
    ),
  ),
  array (
    'map' => 
    array (
      0 => ' xxxx   ',
      1 => ' x  x   ',
      2 => ' x  x   ',
      3 => 'xxt xxxx',
      4 => 'x  t tx',
      5 => 'x    xxx',
      6 => 'xxx  x  ',
      7 => '  xxxx  ',
    ),
    'pusher' => 
    array (
      0 => 2,
      1 => 1,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 2,
        1 => 4,
      ),
      1 => 
      array (
        0 => 3,
        1 => 4,
      ),
      2 => 
      array (
        0 => 3,
        1 => 5,
      ),
    ),
  ),
  array (
    'map' => 
    array (
      0 => 'xxxxx  ',
      1 => 'xt  x  ',
      2 => 'x x xxx',
      3 => 'x t   x',
      4 => 'x  t x',
      5 => 'x   xxx',
      6 => 'xxxxx  ',
    ),
    'pusher' => 
    array (
      0 => 3,
      1 => 5,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 2,
        1 => 3,
      ),
      1 => 
      array (
        0 => 3,
        1 => 3,
      ),
      2 => 
      array (
        0 => 3,
        1 => 4,
      ),
    ),
  ),
  array (
    'map' => 
    array (
      0 => '  xxxxx',
      1 => '  x   x',
      2 => '  x xtx',
      3 => 'xxx  tx',
      4 => 'x     x',
      5 => 'x  t  x',
      6 => 'xxxxxxx',
    ),
    'pusher' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 3,
        1 => 4,
      ),
      1 => 
      array (
        0 => 4,
        1 => 4,
      ),
      2 => 
      array (
        0 => 4,
        1 => 5,
      ),
    ),
  ),
  array (
    'map' => 
    array (
      0 => 'xxxxxx  ',
      1 => 'x    x  ',
      2 => 'x  x xxx',
      3 => 'x t    x',
      4 => 'x   xx x',
      5 => 'xxt  t x',
      6 => ' xx   xx',
      7 => '  xxxxx ',
    ),
    'pusher' => 
    array (
      0 => 1,
      1 => 1,
    ),
    'boxes' => 
    array (
      0 => 
      array (
        0 => 2,
        1 => 2,
      ),
      1 => 
      array (
        0 => 2,
        1 => 3,
      ),
      2 => 
      array (
        0 => 4,
        1 => 3,
      ),
    ),
  ),

	array(
		'map' => array(
			'xxxxxxxxx',
			'x t t t x',
			'x       x',
			'x       x',
			'xxxxxxxxx',
		),
		'pusher' => array(3, 2),
		'boxes' => array(
			array(2, 2),
			array(4, 2),
			array(6, 2),
		),
	),
);

?>