<?php

if ( isset($_SERVER['QUERY_STRING']) && file_exists($file=dirname(__FILE__).'/inc.cls.'.$_SERVER['QUERY_STRING'].'.php') ) {
	highlight_file($file);
}


