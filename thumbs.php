<?php

require __DIR__ . '/inc.bootstrap.php';

$thumbs = get_thumbs();
var_dump(count($thumbs));

if (count(get_thumbs_positions($foo, true)) != count($thumbs)) {
	echo "^ INVALID NAMES!\n";
	exit(1);
}

$sprite = imagecreatetruecolor(THUMB_SIZE, count($thumbs) * THUMB_SIZE);

foreach ( $thumbs as $index => $thumb ) {
	$img = @imagecreatefromgif($thumb) ?: imagecreatefrompng($thumb);
	imagecopy($sprite, $img, 0, $index * THUMB_SIZE, 0, 0, THUMB_SIZE, THUMB_SIZE);
}

imagepng($sprite, __DIR__ . '/cached/thumbs.png');
