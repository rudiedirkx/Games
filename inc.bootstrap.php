<?php

require 'inc.env.php';
require 'vendor/autoload.php';

if (!is_local()) {
	ini_set('display_errors', 0);
}
