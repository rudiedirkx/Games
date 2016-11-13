<?php

require 'inc.functions.php';

$thumbs = get_thumbs();

$sprite = imagecreatetruecolor(THUMB_SIZE, count($thumbs) * THUMB_SIZE);

foreach ( $thumbs as $index => $thumb ) {
	$img = imagecreatefromgif($thumb);
	imagecopy($sprite, $img, 0, $index * THUMB_SIZE, 0, 0, THUMB_SIZE, THUMB_SIZE);
}

imagepng($sprite, __DIR__ . '/cached/thumbs.png');
