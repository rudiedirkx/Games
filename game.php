<?php

$game = isset($_GET['game']) ? $_GET['game'] : '-';

if ( file_exists($game.'.php') )
{
	// Game exists, create HTML and Frames
	// Fetch the game title to print in top window
	$source = file($game.'.php');
	$title = $game;
	if ( '//' == substr($source[1], 0, 2) )
	{
		$title = trim($source[1], ' /');
	}
	else if ( '//' == substr($source[2], 0, 2) )
	{
		$title = trim($source[2], ' /');
	}
	?>
<html>

<head>
<title><?php echo strtoupper($title); ?></title>
</head>

<frameset rows="78,*" border="0" noresize marginwidth="0" marginheight="0">
	<frame src="headerbar.php?page=games" name="headerbar" border="0" noresize marginwidth="0" marginheight="0">
	<frame src="<?php echo $game.'.php'; ?>" name="game" border="0" noresize marginwidth="0" marginheight="0">
</frameset>

</html>
	<?php
}
else
{
	// Game does not exist or file is called directly
	// Header to game index
	Header("Location: ./");
	exit;
}

?>