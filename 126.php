<?php

error_reporting(2047);

// GET params
$_a = array( 'r' => 0, 'g' => 0, 'b' => 0, 'x' => 0.0, 'y' => 0.0, 'w' => 0.5, 'f' => 0, 'n' => 0 );
foreach ( $_a AS $_k => $_v ) { $$_k = getvar($_k, $_v); }
unset($_a, $_k, $_v);


// If there are NO get-params, this is the top level, so print FRAMES (3*4)
if ( 0 == count($_GET) )
{
	exit(printhtml('
<frameset rows="*,*,*" cols="*,*,*,*" border="0">
	<frame scrolling="no" noresize="noresize" border="0" src="?z" />
	<frame scrolling="no" noresize="noresize" border="0" src="?x=0.5&y=0.5&n=0&g=0" />
	<frame scrolling="no" noresize="noresize" border="0" src="?z" />
	<frame scrolling="no" noresize="noresize" border="0" src="?z" />
	<frame scrolling="no" noresize="noresize" border="0" src="?x=0.5&y=0.5&n=0&r=1" />
	<frame scrolling="no" noresize="noresize" border="0" src="?x=0.5&y=0.5&n=0&b=0" />
	<frame scrolling="no" noresize="noresize" border="0" src="?x=0.5&y=0.5&n=0&r=0" />
	<frame scrolling="no" noresize="noresize" border="0" src="?x=0.5&y=0.5&n=0&b=1" />
	<frame scrolling="no" noresize="noresize" border="0" src="?z" />
	<frame scrolling="no" noresize="noresize" border="0" src="?x=0.5&y=0.5&n=0&g=1" />
	<frame scrolling="no" noresize="noresize" border="0" src="?z" />
	<frame scrolling="no" noresize="noresize" border="0" src="?z" />
</frameset>'));
}

// Colours are defined, make backgroundcolor
else if ( isset($_GET['x'], $_GET['y'], $_GET['n']) && empty($_GET['f']) )
{
	if ( isset($_GET['r']) )
	{
		$bg = float2html( (int)(bool)$r, (float)$x, (float)$y );
		$l = 'r='.$r;
	}
	else if ( isset($_GET['g']) )
	{
		$bg = float2html( (float)$x, (int)(bool)$g, (float)$y );
		$l = 'g='.$g;
	}
	else if ( isset($_GET['b']) )
	{
		$bg = float2html( (float)$x, (float)$x, (int)(bool)$b );
		$l = 'b='.$b;
	}

	echo '<html><body bgcolor="#'.$bg.'"><a title="'.$bg.'" href="?x='.$x.'&y='.$y.'&n='.($n+1).'&w='.($w/2).'&'.$l.'&f=1"><img src="/icons/blank.gif" width="100%" height="100%" /></a></body></html>';
}

// Four frames, just the frameset. Source: one-down
else if ( isset($_GET['x'], $_GET['y'], $_GET['n']) && !empty($_GET['f']) )
{

	$w2 = $w/2;

	exit(printhtml('
<frameset rows="*,*" cols="*,*" border="0">
	<frame scrolling="no" noresize="noresize" border="0" src="'.cr(array('x'=>$x-$w2, 'y'=>$y-$w2)).'" />
	<frame scrolling="no" noresize="noresize" border="0" src="'.cr(array('x'=>$x+$w2, 'y'=>$y-$w2)).'" />
	<frame scrolling="no" noresize="noresize" border="0" src="'.cr(array('x'=>$x-$w2, 'y'=>$y+$w2)).'" />
	<frame scrolling="no" noresize="noresize" border="0" src="'.cr(array('x'=>$x+$w2, 'y'=>$y+$w2)).'" />
</frameset>
'));

}

// White block, always be, no onlclick here
else
{
	exit(printhtml());
}



// F U N C T I O N S //

function cr($l) {
	foreach ( $l AS $k => $v ) {
		$_GET[$k] = $v;
	}
	return '?'.http_build_query($_GET);
}

function printhtml( $f_szHTML = '<body bgcolor="__BGCOLOR___"></body>', $f_szBackgroundColor = 'ffffff' )
{
	return '<html>'.str_replace('__BGCOLOR___', $f_szBackgroundColor, $f_szHTML).'</html>';
}

function getvar( $f_szVar, $f_szSecond = 0.5 )
{
	return isset($_GET[$f_szVar]) ? (float)$_GET[$f_szVar] : $f_szSecond;
}

function float2html( $f_fRed, $f_fGreen, $f_fBlue )
{
	return bin2hex(chr(max(0,min(255,$f_fRed*256)))) . bin2hex(chr(max(0,min(255,$f_fGreen*256)))) . bin2hex(chr(max(0,min(255,$f_fBlue*256))));

	// Get RGB from FLOAT
	$rgbRed		= float2rgb( $f_fRed );
	$rgbGreen	= float2rgb( $f_fGreen );
	$rgbBlue	= float2rgb( $f_fBlue );
	// Return as string
	return rgb2html( $rgbRed, $rgbGreen, $rgbBlue );
}

?>