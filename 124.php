<?php
//SAFE CRACKING

session_start();
error_reporting(2047);

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

$codes = array(
	array( 121, 131, 141, 151 ),
	array( 66, 67, 68, 69 ),
	array( 10, 20, 30, 40 ),
	array( 41, 51, 61, 71 )
);
$num_codes = 16;

function maak_foute_codes()
{
	global $num_codes,$codes,$foute_codes,$goede_code;

	for ($i=0;$i<$num_codes-1;$i++)
	{
		maak_foute_code();
	}
	$foute_codes[] = $goede_code;
	sort($foute_codes);
}

function maak_foute_code()
{
	global $codes,$foute_codes,$goede_code;

	$code1 = (INT)$codes[0][rand(0,3)].$codes[1][rand(0,3)].$codes[2][rand(0,3)].$codes[3][rand(0,3)];
	if (!in_array($code1,$foute_codes) && $code1!=$goede_code)
	{
		$foute_codes[] = $code1;
		return true;
	}
	else
	{
		maak_foute_code();
	}
}

if (isset($_POST['check_code']))
{
	echo "<META http-equiv=Refresh content=2>\n";
	if (isset($_POST['de_code']) && $_POST['de_code']==$_SESSION['goede_code'])
	{
		die("You done it!!");
	}
	else
	{
		die("BIG bad error!!");
	}
}

if (isset($_GET['pagina']) && $_GET['pagina']=="voorbeeldcode")
{
	if (isset($_SESSION['code_check']) && count($_SESSION['code_check'])==4 && 1<2)
	{
		?>
<head>
<script>
quickrate = 1;		// Hoeveel volgende interval van deze interval is
spacenotime = 1;	// Als True, spatie wel getypt maar kost geen tijd (dus interval even 0)

function typeit()
{
	mytext = mytext.replace(/<([^<])*>/, "");
	if (it < mytext.length)
	{
		typingBuffer += mytext.charAt(it);
		t.innerHTML = typingBuffer;
		if (mytext.charAt(it)==' ' && spacenotime==1)
		{
			setTimeout("typeit()",0);
		}
		else
		{
			setTimeout("typeit()", interval);
		}
		interval = interval * quickrate;
		it++;
	}
}

function Write(Rtext,Rinterval,Rid)
{
	if (document.getElementById)
	{
		interval = Rinterval;
		t = document.getElementById(Rid);
		if (t.innerHTML)
		{
			typingBuffer = "";
			it = 0;
			mytext = Rtext;
			t.innerHTML = "";
			typeit();
		}
	}
}
</script>
</head>
<?php
		die("<body bgcolor=#116600 style='margin:0px;overflow:auto;' OnLoad=\"Write('Verify!',100,'p');\"><table border=0 cellpadding=0 cellspacing=0 align=center height=100%><tr valign=middle><td style='font-size:10pt;font-family:courier new;'><b><font id='p'>Verify!</font></td></tr></table>");
	}

	$a = (INT)$codes[0][rand(0,3)].$codes[1][rand(0,3)].$codes[2][rand(0,3)].$codes[3][rand(0,3)];
	$deze = explode(" ",substr($a,0,3).' '.substr($a,3,2).' '.substr($a,5,2).' '.substr($a,7,2));
	$goede_code = isset($_SESSION['goede_code']) ? $_SESSION['goede_code'] : '';
	$goede[0] = substr($goede_code,0,3);
	$goede[1] = substr($goede_code,3,2);
	$goede[2] = substr($goede_code,5,2);
	$goede[3] = substr($goede_code,7,2);

	echo '<script>setTimeout("window.location.reload();",'.rand(1000,2500).');</script>';
	echo "<body style='margin:0px;font-size:10pt;font-family:courier new;overflow:auto;' bgcolor=#116600>";
	echo "<table border=0 cellpadding=0 cellspacing=0 align=center height=100%><tr valign=middle><td style='font-size:10pt;font-family:courier new;'>";
	if (isset($_SESSION['code_check']))
		$check = $_SESSION['code_check'];
	else
		$check = Array();

	if ($deze[0] == $goede[0])
	{
		$check[0]=1;
		echo "<font color=lime>";
	}
	echo $deze[0]."</font> ";
	if ($deze[1] == $goede[1])
	{
		$check[1]=1;
		echo "<font color=lime>";
	}
	echo $deze[1]."</font> ";
	if ($deze[2] == $goede[2])
	{
		$check[2]=1;
		echo "<font color=lime>";
	}
	echo $deze[2]."</font> ";
	if ($deze[3] == $goede[3])
	{
		$check[3]=1;
		echo "<font color=lime>";
	}
	echo $deze[3]."</font>";

	$_SESSION['code_check'] = $check;

	exit("</td></tr></table>");	
}

$goede_code = (INT)$codes[0][rand(0,3)].$codes[1][rand(0,3)].$codes[2][rand(0,3)].$codes[3][rand(0,3)];
$_SESSION['goede_code'] = $goede_code;
unset($_SESSION['code_check']);
$foute_codes = Array();
maak_foute_codes();

?>
<html>

<head>
<title>Safe Cracking</title>
</head>

<body bgcolor=black OnLoad="document.forms[0].de_code.focus();">
<table border=0 cellpadding=2 cellspacing=0 style='font-size:10pt;font-family:courier new;'>
<form method=post><input type=hidden name=check_code value=1>
<tr valign=bottom><td><select size=<?php echo $num_codes; ?> name=de_code style='border:0px;font-size:10pt;font-family:courier new;background:#116600;color:lime;' OnKeyDown="if (event.keyCode==13) { document.forms[0].de_code.value=this.value;document.forms[0].submit();return false; } return true;">
<?php

for ($i=0;$i<count($foute_codes);$i++)
{
	$a = $foute_codes[$i];
	echo "<option value='$a'".(($i==floor($num_codes/2))?" selected":"").">".substr($a,0,3).' '.substr($a,3,2).' '.substr($a,5,2).' '.substr($a,7,2)."\n";
}
echo "</select></td>";
echo '<td><iframe src="?pagina=voorbeeldcode" style="overflow:hide;" width=115 height=30 border=0 style="border:0px;"></iframe></td></tr>';
// echo '<tr><td colspan=2><center><input type=submit value="Verify"></td></tr>';
echo "</form></table>";

?>