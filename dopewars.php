<?php

error_reporting(2047);

/*///////////////////////////////////////////////////////////////////////////////
// SOME INFO ABOUT DOPEWARS

  == REQUIREMENTS
  * MySQL 4.x
  * PHP 4.x
    - register_globals must be OFF!
      if you don't have access to the php.ini file, use .htaccess, containing the following:
      php_flag register_globals off
  * Players :)

  === DATABASE:

  CREATE TABLE `dopescores` (
    `id` int(11) NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `password` varchar(255) NOT NULL default '',
    `score` bigint(20) NOT NULL default '0',
    `date` datetime NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

  CREATE TABLE `dopewars` (
    `id` int(11) NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `password` varchar(255) NOT NULL default '',
    `score` bigint(20) NOT NULL default '0',
    `onthemove` enum('0','1') NOT NULL default '0',
    `player` text NOT NULL,
    `date` datetime NOT NULL default '0000-00-00 00:00:00',
    `gameoff` enum('0','1') NOT NULL default '0',
    PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;


  === TODO

  * Line 447 BETWEEN LOCATIONS (~~Fight Anoter Player~~)

///////////////////////////////////////////////////////////////////////////////*/


/////////////////////////////////////////////////////////////////////////////////
// PREPARING SCRIPT

mt_srand ((float) microtime() * 1000000);


define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );



/////////////////////////////////////////////////////////////////////////////////
// CONFIG

define( "MAX_USERS",	20 );

define( "MAX_LOAN",		2000000 );
define( "MAX_BITCHES",	25 );




/////////////////////////////////////////////////////////////////////////////////
// MYSQL CONNECT + SESSION START

include("connect.php");

session_start();




/////////////////////////////////////////////////////////////////////////////////
// GET LANGUAGE STUFF

get_language_stuff( );




/////////////////////////////////////////////////////////////////////////////////
// PREPARING USER VARIABLES

$player = Array( );
if ( isset($_SESSION['player']) )	$player	= $_SESSION['player'];
$uid = '';
if ( isset($_SESSION['uid']) )		$uid	= $_SESSION['uid'];







// echo "<pre>";
// print_r($_SESSION);







/////////////////////////////////////////////////////////////////////////////////
// UPDATING

if ( 0 < mysql_result(mysql_query("SELECT COUNT(*) AS a FROM dopewars WHERE gameoff='1' LIMIT 1;"),0,'a') && !($_GET['action'] == "overnight" && $_GET['pwd'] == "aarde") )
{
	die("Updating... Back in a sec!");
}

if ( isset($_GET['action']) && "overnight" == $_GET['action'] && $_GET['pwd'] == "aarde")
{
	// GAME OFF
	echo "setting GAME-OFF<br><br>".EOL.EOL;
	mysql_query("UPDATE dopewars SET gameoff='1';");

	// Opening all players
	echo "Opening all players<br><br>".EOL.EOL;
	$l = mysql_query("SELECT id,name,player FROM dopewars ORDER BY name ASC;") or die(mysql_error());

	// Alle spelers afgaan
	while ($li = mysql_fetch_assoc($l))
	{
		// Player array uitpakken
		$pi = unserialize(stripslashes($li['player']));
		// 2 % rente op bank
		$pi['bank'] *= 1.02;
		// Player array inpakken
		$npi = addslashes(serialize($pi));
		// Updaten in MySQL
		$sql = "UPDATE dopewars SET player='$npi' WHERE id='".$li['id']."';";
		mysql_query($sql) or die(mysql_error());
		// OKAY message
		echo 'Player "'.$li['name'].'" updated...<br>'.EOL.EOL;
	}

	// Ff TABLE optimizen omdat er zoveel veranderd is
	mysql_query("OPTIMIZE TABLE dopewars;") or die(mysql_error());

	// GAME ON!!
	mysql_query("UPDATE dopewars SET gameoff='0';");

	die("-- ALL UPDATES DONE --<br><br>".EOL.EOL.'<a href="'.BASEPAGE.'">:: back ::</a>');
}


/////////////////////////////////////////////////////////////////////////////////
// HIGH SCORE LIST

if ( isset($_GET['action']) && "hiscore" == $_GET['action'] )
{
	echo "<html><head><title>dopewars</title>";
	print_css( );
	echo "</head><body><center><h3>dopewars - high scores</h3>";
	
	echo "<p><b>Active dealers - Top 200</b></p>";
	$hq = mysql_query("SELECT name, score FROM dopewars ORDER BY score DESC LIMIT 200;") or die(mysql_error());
	$n=1;
	while ($val = mysql_fetch_assoc($hq))
	{
			echo ($n++)." - ".htmlspecialchars($val['name']).' ($'.nummertje($val['score']).")<br>\n";
	}
	
	echo "<p><b>Legendary (dead) dealers - All-time-hi-scores - Top 100</p>";
	$hq = mysql_query("SELECT name, score FROM dopescores ORDER BY score DESC LIMIT 100;") or die(mysql_error());
	$n=1;
	while ($val = mysql_fetch_assoc($hq))
	{
			echo ($n++)." - ".htmlentities($val['name']).' ($'.nummertje($val['score']).")<br>\n";
	}
	
	echo '<p><a href="'.BASEPAGE.'">back</a></p>';
	echo "</body></html>";
	exit;
}










/////////////////////////////////////////////////////////////////////////////////
// PRE-LOGIN

if ( ( !isset($_SESSION['player']) || !isset($_SESSION['uid']) ) || isset($_GET['logout']) )
{
	// echo "niet ingelogd...";
	unset( $_SESSION['uid'], $_SESSION['player'] );

	$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
	switch ( $action )
	{
		case "login":
			if ( 0 < count($_POST) )
			{
				$name = trim(str_replace(",", " ",substr($_POST['name'],0,20)));
				$password = trim(substr($_POST['password'],0,15));
				$new = isset($_POST['new']) ? TRUE : FALSE;
				if ((addslashes($name) != $name || strstr($name,"  ")) && $new )
				{
					echo $error = "Invalid characters in username.";
				}
				else if (addslashes($password) != $password)
				{
					echo $error = "Invalid characters in password.";
				}
				else if ( 1 < strlen($name) && 1 < strlen($password) )
				{
					$_SESSION['language'] = $_POST['lang'];
					$result = mysql_query("SELECT * FROM dopewars WHERE name='".$name."';");
					if ( $new )
					{
						if (mysql_num_rows($result))
						{
							echo $error = "There already exists a user named \"$name\"!";
						}
						else
						{
							check_max();
							$player['name']				= $name;
							$player['cash']				= 5000;
							$player['debt']				= 4761;
							$player['bank']				= 0;
							$player['guns']				= 0;
							$player['bitches']			= 2;
							$player['space']			= 20 + $player['bitches']*10;
							$player['held']				= 0;
							$player['life']				= 100;
							$player['guns']				= Array( );
							$player['drugs']			= Array( );
							$player['drugprices']		= Array( );
							$player['prices']			= Array( );
							$player['destination']		= 1;
							$player['location']			= 1;
							$player['snitches']			= Array( );
							$player['currentsnitches']	= Array( );
							$player['snitchreport']		= Array( );
							$player['fighthistory']		= Array( );

							$pl = addslashes(serialize($player));
							$sql = "INSERT INTO dopewars (name,password,player) VALUES ('$name','$password','$pl')";
							mysql_query($sql) or die(mysql_error());
							if ( mysql_affected_rows() )
							{
								session_register("uid");
								session_register("player");
								$_SESSION['uid'] = $name;
								$_SESSION['player'] = $player;
							}
							Header( "Location: ".BASEPAGE."?NEW_ACCOUNT_MADE" );
							exit;
						}											
					}
					else
					{
						if ( !mysql_num_rows($result) )
						{
							echo $error = "No such user";
						}
						else if ( md5(mysql_result($result,0,'password')) != md5($password) )
						{
							echo $error = "Invalid password";
						}
						else
						{
							check_max( );
							session_register("uid");
							session_register("player");
							$_SESSION['uid']	= mysql_result($result,0,'name');
							$_SESSION['player']	= unserialize(mysql_result($result,0,'player'));
							// if ( !isset($_SESSION['player']['life']) ) $_SESSION['player']['life'] = 100;
							// echo "ingelogd...";
							Header( "Location: " . BASEPAGE."?LOGGED_IN" );
							// print_r( $_SESSION['player'] );
							exit;
						}
					}					
				}
			}

			if ( !isset($_SESSION['uid']) || !isset($_SESSION['player']) )
			{
				echo "<html><head><title>dopewars</title>";
				print_css( );
				echo "</head><h3>dopewars - login</h3>";
				echo "<div style=\"height:20px;margin-top:30px;\">";
				if ( isset($error) )
				{
					echo "<p>$error</p>";
				}
				echo "</div>";
				echo "<form method=post action=\"?action=login\">";
				echo "<p>name: ";
				echo "<input name=name type=text size=10 maxlength=20></p>";
				echo "<p>password: ";
				echo "<input name=password type=password size=10 maxlength=15></p>";
				echo "<p>language: ";
				echo "<select name=lang><option value=EN>English<option value=NL".((stristr($_SERVER['HTTP_ACCEPT_LANGUAGE'],"nl"))?" SELECTED":"").">Dutch";
				echo "</select></p>";
				echo "<p><label for=new><input type=checkbox name=new id=new> create new account</label></p>";
				echo "<input type=submit value=\"login\"> ";
				echo "</form>";	
				echo "</body></html>";
				exit;
			}
			break;

		default:
			echo "<html><head><title>dopewars</title>";
			print_css( );
			echo "</head>".EOL.EOL."<body>";
			echo "<h2>dopewars</h2>";
			echo "<p>(deal drugs to make lots and lots of money)</p>";
			echo '<p><a href="?action=login">login</a> | ';
			echo '<a href="?action=hiscore">high scores</a>';
			echo "</body></html>";
			exit;
	}
}


/////////////////////////////////////////////////////////////////////////////////
// READ DATABASE

if ( isset($_SESSION['player']['opponent']) && $_SESSION['player']['opponent'] )
{
	// prevent deadlock by selecting the opponent as well

	$qry = "SELECT * FROM dopewars WHERE name in ('".$uid."','".$player['opponent']."');";
	$listed = mysql_query($qry);
	$result[0] = mysql_result($listed,0);
	$result[1] = mysql_result($listed,1);
	if ($result[0]['name'] == $uid)
	{
		$player = unserialize(stripslashes($result[0]['player']));
		$pass = $result[0]['password'];
		if ($result[1]['name'] == $player['opponent'])
		{
			$opponent =  unserialize(stripslashes($result[1]["player"]));
		}
	}
	else if ($result[1]['name'] == $uid)
	{
		$player = unserialize(stripslashes($result[1]["player"]));
		$pass = $result[1]["password"];
		if ($result[0]["name"] == $player["opponent"])
		{
			$opponent =  unserialize(stripslashes($result[0]["player"]));
		}
	}

}
else
{
	$qry = "SELECT * FROM dopewars WHERE name='".$_SESSION['uid']."' FOR UPDATE;";
	$q = mysql_query($qry) or die(mysql_error());
	$result = mysql_fetch_assoc($q);
	$player = unserialize(stripslashes($result['player']));
	$pass = $result['password'];
}

if ( !isset($_SESSION['player']) || !is_array($_SESSION['player']) )
{
	print_r( $_SESSION );
	die("fout... <a href='?logout=1'>Index</a>");
	// header("Location: ".BASEPAGE);
	exit;
}

$updatedate = 0;
$onthemove = 0;


// ****************************************************************************
// TO ANOTHER LOCATION

if ( isset($_GET['l']) && 0 <= $_GET['l'] && ( !isset($player['prison']) || !$player['prison'] ) && ( !isset($player['fight']) || !$player['fight'] ) )
{
	// echo "<pre>\n";
	// print_r( $player );

	if ( isset($player['noencounter']) && $player['noencounter'] > 5)
	{
		// at least 5 moves between fights with other players
		$qry = "SELECT * FROM dopewars WHERE onthemove='1' LIMIT 1;";
		$result = mysql_query($qry) or die(mysql_error());

		if ( 0 < mysql_num_rows($result) && $uid != ($opponent_name=mysql_result($result,0,'name')) )
		{
			$opponent = mysql_fetch_assoc( $result );
			// start fight with another player
			$player['opponent'] = $opponent_name;
			$player['fight'] = 1;

			$opponent = unserialize(stripslashes($opponent['player']));
			$opponent['opponent'] = $uid;
			$opponent['fight'] = 1;

			$player['destination'] = $l;
			$player['travel']--;

			$player['noencounter'] = 0;
		}
	}

	// redirect to new location
	$player['destination']	= $_GET['l'];
	// $player['location']		= $_GET['l'];
	echo "<html>".EOL.EOL."<head>".EOL."<title>dopewars</title>".EOL;
	echo "<META http-equiv=refresh content=\"1; URL=".BASEPAGE."\">";
	print_css( );
	echo "</head>".EOL.EOL."<body>";
	echo "<font class='onthemove'>".$str['onthemove']." ".$places[$player['destination']]."</font>";

	$player['travel']--;
	if ( isset($player['noencounter']) )
	{
		$player['noencounter']++;
	}
	else
	{
		$player['noencounter']=1;
	}
	if ($player['noencounter'] > 5)
	{
		$onthemove = 1;
	}
	save_exit( );
}


echo "<html>".EOL.EOL."<head>".EOL."<title>dopewars</title>".EOL;
print_css( );
echo "</head>".EOL.EOL."<body>".EOL.EOL;
// print_r($player);

// ****************************************************************************
// PRISON

if ( isset($player['prison']) && $player['prison'] )
{
	echo "<h3>".$str['prison']."</h3>";
//	printmenu(0);

	if ($player['prison'] > time())
	{
		if ($_SESSION['language'] == "NL")
		{
			setlocale(LC_TIME, "nl_NL");
		}
		else
		{
			setlocale(LC_TIME, "en_EN");
		}
		printf("<p>".$str['inprison']."</p>", strftime($str['dateformat'],$player['prison']));
		echo "<form><input type=hidden name=logout value=1><input type=submit value=\"".$str['logout']."\"></form>";
	}
	else
	{
		$player['prison'] = 0;
		$player['travel'] = 0;
		echo $str['released'];
		echo "<form><input type=submit value=\"".$str['continue']."\"></form>";
	}
	save_exit();
}


// ****************************************************************************
// BETWEEN TWO LOCATIONS

if ( isset($player['destination']) && $player['destination'] != "" && $player['location']!=$player['destination'] && isset($player['travel']) && $player['travel'] )
{
	if ( isset($player['fight']) && $player['fight'] )
	{

		if ( isset($player['opponent']) && $player['opponent'] )
		{
			// ****************************************************************************
			// FIGHT ANOTHER PLAYER

			$player["noencounter"] = 0;

			if ($opponent == "")
			{
				$qry = "SELECT * FROM dopewars WHERE name='" . $player["opponent"] . "' FOR UPDATE;";
				$result = $db->qry($qry);
				if ( $result[0] != Array() )
				{
					$opponent = unserialize(stripslashes($result[0]["player"]));
				}
			}

			if ($opponent == "" )
			{
				$player["travel"] = 0;
				$player["fight"] = 0;
				$player["reloading"] = 0;
			}
			else
			{
				echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3><div style=\"height:100px;margin-top:30px;\">";
				
				while (list($key, $event) = each ($player["fighthistory"]))
				{
					printf ($str[$event] . "<br>", htmlentities($opponent["name"]));
				}
	
				check_life( __LINE__ );

				if ( $opponent["opponent"] )
				{
					if ( isset($_get['action']) && $_get['action'] == "fight" && !$player["reloading"])
					{
						$opponent["fighthistory"][] = "op_shoots";
						$damage = 0;
						while (list($gun, $num) = each($player["guns"]))
						{
							$damage += mt_rand(1, sqrt($guns[$gun]['price']) * $num / 10);
						}
						if ($opponent["total"] > 200000000)
						{
							// damage amplification
							$damage *= 1 + ($opponent["total"] - 200000000)/70000000;
						}
						if ($damage >=  5 - $opponent["bitches"]/2)
						{
		
							if (mt_rand(0,$opponent["bitches"]))
							{
								$opponent = lose_bitch($opponent);
								printf ($str["youkilledbitch"]. "<br>", htmlentities($opponent["name"]));
								$opponent["fighthistory"][] = "bitchkilled";
								$player["reloading"] = 1;
							}
							else
							{
								if ($opponent["total"] > 1000000)
								{
									$damage *= .7;
									$damage = min(97,$damage);
									$opponent["life"] -= round($damage);
								}
								else
								{
									// damage reduction
									$opponent["life"] -= round($damage * .4 + 0.3 * max(0,$opponent["total"])/1000000 );
								}
								$opponent["fighthistory"][] = "yourhit";
		
								if ($opponent["life"] <= 0)
								{
									$opponent["life"] = 0;
									$amount =  $opponent["cash"];
									printf ($str["youkilledopponent"]."<br>", htmlentities($opponent["name"]), $amount);
									$player["cash"] += $amount;
									$player["fight"] = 0;
								}
								else
								{
									printf ($str["youshotopponent"]."<br>", htmlentities($opponent["name"]));
									$player["reloading"] = 1;
								}
							}
						}
						else
						{
							echo $str["youmissed"]."<br>";
							$opponent["fighthistory"][] = "missed";
							$player["reloading"] = 1;
						}
					}

					if ( isset($_get['action']) && $_get['action'] == "run")
					{
						if ( (mt_rand(0,100) > 20 + $opponent["bitches"]* 7) || end($player["fighthistory"]) ==  "op_cantescape")
						{
							echo $str['escaped'];
							$opponent['fighthistory'][] = "op_escaped";
							$player['fight'] = 0;
						}
						else
						{
							echo $str['cantescape'];
							$opponent['fighthistory'][] = "op_cantescape";
						}
					}
					if ( isset($_get['action']) && $_get['action'] == "stand")
					{
						echo $str["youstand"];
						$opponent["fighthistory"][] = "op_stands";
					}
					if ( !isset($_get['action']) || $_get['action'] == "" )
					{
						if ($reload)
						{
							echo $str["reloaded"] . "<br>";
							$player["reloading"] = 0;
						}
						else
						{
							printf($str["encounter"] . "<br>", htmlentities($opponent["name"]));
						}
					}
				}
				else
				{
					$player["reloading"] = 0;
					$player["fight"] = 0;
				}

				if ($opponent["life"] <= 0)
				{
					printf ($str["opponentdead"], $player["opponent"]);
					$player["fight"] = 0;
					$player["reloading"] = 0;
				}

				$player["fighthistory"] = array();

				save_player($player["opponent"], $opponent, 1);

				if ($player["reloading"])
				{
					echo  "<meta http-equiv=\"refresh\" content=\"3; URL=?reload=1\">";
					echo $str["reloading"];
					echo "</body></html>";
					save_exit();
				}

				if ( !$player['fight'] )
				{
					$player["travel"] = 0; // to new location
					$player["opponent"] = "";
					echo "</div>";
				//	printmenu(0);
					echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\"></form>";
					echo "</body></html>";
					save_exit();
				}
			}
		}
		else if ( isset($_GET['action']) && $_GET['action'] )
		{
			// ****************************************************************************
			// FIGHT THE POLICE

			echo "<h3>".$str['onthemove']." ".$places[$player['destination']]."</h3>";
			if ( $_GET['action'] == "fight" )
			{
				$damage = 0;
				while (list($gun, $num) = each($player["guns"]))
				{
					$damage += mt_rand(1, sqrt($guns[$gun]["price"]) * $num / 10);
				}
				if ($damage >=  5 - $player["cops"]/2)
				{
					$player['cops']--;
					if ( !isset($player['fightreport']['deadcops']) || 1 > $player['fightreport']['deadcops'] )
					{
						$player['fightreport']['deadcops']=1;
					}
					else
					{
						$player['fightreport']['deadcops']++;
					}
					if ($player["cops"] <= 0) {
						$amount = 0;
						for ($i = 0; $i < $player['fightreport']['deadcops']; $i++)
						{
							$amount += mt_rand(500,20000);
						}
						$player["cash"] += $amount;
						printf ($str['allcopskilled'], $amount);
						$player['fight'] = 0;
					}
					else
					{
						echo $str['youkilledcop'];
					}
				}
				else
				{
					echo $str['youmissed'];
				}
				if ( !isset($player['fightreport']['shots']) || 1 > $player['fightreport']['shots'] )
				{
					$player['fightreport']['shots']=1;
				}
				else
				{
					$player['fightreport']['shots']++;
				}
			}
			if ( $_GET['action'] == "bribe")
			{
				if ( ( !isset($player['fightreport']['shots']) || $player['fightreport']['shots'] == 0 ) && $player['cash'] >= $player['cops']*20000)
				{
					printf($str["bribed"],  $player["cops"] * 20000);
					$player["cash"] -= $player["cops"] * 20000;
					$player["fight"] = 0;
					reset ($player["drugs"]);
					while (list ($key, $val) = each($player["drugs"]))
					{
						$foo = round($val/2.0);
						$player['space'] += $foo;
						$player['drugs'][$key] -= $foo;
					}
				}
				else
				{
					echo $str['nobribe'];
				}
			}
			if ( $_GET['action'] == "run")
			{
				if (mt_rand(0,100) > 20 + $player['cops']* 7)
				{
					echo $str['escaped'];
					$player['fight'] = 0;
				}
				else
				{
					echo $str['cantescape'];
				}
			}
			if ( $_GET['action'] == "surrender")
			{
				$player["fight"] = 0;
				$player["fightreport"]["arrested"]=1;
				$prisontime = 1;
				$prisontime += $player["fightreport"]["shots"];
				$prisontime += $player["fightreport"]["deadcops"]*2;
				$prisontime += array_sum($player['drugs'])/10;

				$player["prison"] = ceil(time() / 3600 + $prisontime) * 3600;
				$player["guns"] = array();
				$player["space"] += array_sum($player["drugs"]);
				$player["drugs"] = array();

				report_snitches();
				echo "<br>" . $str["arrested"];

				if ($player["bank"] > 2000000000 && !mt_rand(0,4)) {
					$foo = mt_rand(15000000, 500000000);
					$player["bank"] -= $foo;
					printf("<br>" . $str["forfeit"], $foo);
				}

								echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\"></form>";
								echo "</body></html>";
								save_exit();
				
			}
			else if ( !isset($player['fight']) || !$player['fight'] )
			{
				$foo = $player["currentsnitches"];
				report_snitches();
				$player["travel"] = 0;	// to new location
				if ($player["total"] > -1000000000 && mt_rand(0,2) && $player["cash"]>0 && $foo != array())
				{
					// mugged
					$player["cash"] = 0;
					echo "<br>" . $str["mugged"];
				}
				echo "</div>";
			//	printmenu(0);
				echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\"></form>";
				echo "</body></html>";
				save_exit();

			}
			else
			{
				// police attacks
				if ( isset($player['cops']) && $player['cops'] > 1)
				{
					printf("<br>".$str['copsshoot']."<br>", $player['cops'] );
				}
				else
				{
					echo "<br>".$str['copshoot']."<br>";
				}
				if (mt_rand(0, $player['cops']))
				{
					if (mt_rand(0,$player["bitches"])) {
						$player = lose_bitch($player);
						if ( !isset($player['fightreport']['bitchlost']) || 1 > $player['fightreport']['bitchlost'] )
						{
							$player['fightreport']['bitchlost']=1;
						}
						else
						{
							$player['fightreport']['bitchlost']++;
						}
						echo $str["bitchkilled"];
					} else {
						$damage = 1;
						for ($i = 0; $i < $player["cops"]; $i++) {
							$damage += mt_rand(0, 4);
						}
						if ($player["total"] > 200000000) {
							$damage *= 1 + ($player["total"] - 200000000)/70000000;
						}
						$damage = round(min(97,$damage));
						$player["life"] -= $damage;
						$player["fightreport"]["life"] = $player["life"];
						echo $str["yourhit"];
						check_life(__LINE__);
					}
				} else {
					echo $str["missed"];
				}
			}


		}
		else
		{
			echo "<h3>".$str['onthemove']." ".$places[$player['destination']]."</h3>";

			printf ("<p>".$str['chase']."</p>", $player['cops']);

		}
	}
	else	// NOT FIGHTING COPS OR PLAYERS
	{
		if ($player['total'] > 0)
		{
			$foo = round((20 - log($player['total']))) ;
		}
		else
		{
			$foo = 20;
		}
		$foo = max($foo, 2);

		$threat = 0;
		if ( !isset($player['threat']) || !is_numeric($player['threat']) || 0 > $player['threat'] || 2 < $player['threat'] )
		{
			$player['threat'] = 0;
		}
		if ( $player['destination'] != 0 && 1 == mt_rand(0,1) )
		{
			if ($player['debt'] > MAX_LOAN && $player['threat'] == 0)
			{
				$threat = 1;	
			}
			else if ($player['debt'] > MAX_LOAN * 1.2 && $player['threat'] == 1)
			{
				$threat = 2;
			}
			else if ($player['debt'] > MAX_LOAN * 1.4 && $player['threat'] == 2)
			{
				$threat = 3;
			}
		}


		if ($player['threat'] < $threat)
		{
			// loanshark threat
			echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";
			echo "<div style=\"height:100px;margin-top:30px;\">";
			echo $str["loanhit" . $threat];
			$player["life"] -= pow($threat, 3) * 4;
			$player["threat"] = $threat;
			$player["travel"] = 0;
		
			check_life(__LINE__);

			echo "</div>";
		//	printmenu(0);
			echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\"></form>";
			
			echo "</body></html>";
			save_exit();						
		}
		else if (mt_rand(0, $foo) == 0 || $player["snitches"] != Array() )
		{
			// start fight with police
			if ($player["snitches"] != array()) {
				$player["cops"] = 6;
				$player["currentsnitches"] = $player["snitches"];
				$player["snitches"] = array();
			} else {
				$player["currentsnitches"] = array();
				$player["cops"] = mt_rand(2, 12 - min(9, $foo-2));
			}
			
			$player["fight"] = time();
	
			echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";
			echo "<div style=\"height:100px;margin-top:30px;\">";

			printf ("<p>" . $str["chase"] . "</p>", $player["cops"]);
		}
		else
		{

			$r = mt_rand(0,100);
			if ($r < 5)
			{
				// random events

				echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";

				if ( 0 < array_sum($player["drugs"]) && $r < 2)
				{
					// LOSES DRUGS IN CHASE
					reset ($player["drugs"]);
					while (list($key, $val) = each($player["drugs"]))
					{
						if ($val)
						{
							$player["space"] += $player["drugs"][$key];
							$player["drugs"][$key] = 0;
							$player["drugprices"][$key] = 0;
							printf($str["lostdrugs"], $drugs[$key]["name"]);
							break;
						}
					}	
				}
				else if ($player["space"] && $r < 4)
				{
					// FINDS DRUGS ON STREET
					$drug = mt_rand(0, count($drugs)-1);
					$quantity = mt_rand(1, min(20, $player["space"]));
					$player["space"] -= $quantity;

					if ( !isset($player["drugs"][$drug]) )		$player["drugs"][$drug] = 0;
					if ( !isset($player["drugprices"][$drug]) )	$player["drugprices"][$drug] = 0;

					$drugamount = round( $player["drugs"][$drug] * $player["drugprices"][$drug] );
					$player["drugs"][$drug] += $quantity;
					$player["drugprices"][$drug] = round($drugamount/$player["drugs"][$drug]);
					printf($str["foundbody"], $quantity, $drugs[$drug]["name"]);
				}
				else
				{
					// GETS MUGGED
					$player["cash"] = 0;
					echo $str["mugged"];
				}

				$player["travel"] = 0;
				echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\"></form>";
				echo "</body></html>";
				save_exit();
			}
			else if ($r < 10 && $player['bitches'] > 7)
			{
				$player = lose_bitch($player);
				echo "<h3>".$str['onthemove']." ".$places[$player['destination']]."</h3>";
				echo $str['bitchgone'];
				echo "<form><input type=submit value=\"".$str['continue']."\"></form>";
				echo "</body></html>";
				$player['travel'] = 0;
				save_exit();
			}
			else
			{
				// to new location
				$player['travel'] = 0;
			}
		}

	}


	if ( isset($player['fight']) && $player['fight'] )
	{
		// fight menu

		if (array_sum($player['guns']))
		{
			echo "<form><input type=\"hidden\" name=\"action\" value=\"fight\"><input type=\"submit\" value=\"" . $str["fight"]. "\"></form>";
		}
		echo "<form><input type=\"hidden\" name=\"action\" value=\"run\"><input type=\"submit\" value=\"" . $str["run"]. "\"></form>";
		if ( !isset($player['opponent']) || !$player['opponent'] )
		{
			echo "<form><input type=\"hidden\" name=\"action\" value=\"surrender\"><input type=\"submit\" value=\"" . $str["surrender"]. "\"></form>";
			if ( !isset($player['fightreport']['shots']) || $player['fightreport']['shots'] == 0 )
			{
				echo "<form><input type=\"hidden\" name=\"action\" value=\"bribe\"><input type=\"submit\" value=\"" . $str["bribe"]. "\"></form>";
			}
		}
		else
		{
			printf("<p>".$str['op_status']."</p>", htmlentities($opponent["name"]), $opponent["bitches"], array_sum($opponent["guns"]), $opponent["life"]);
		}
		echo "</body></html>";
		save_exit();
	}
	
}

// ****************************************************************************
// PRINT SNITCH REPORTS

if ( isset($player['destination']) && $player['destination'] != "" && $player['location'] != $player['destination'])
{
	if ( isset($player['snitchreport']) && $player['snitchreport'] )
	{
	
		echo "<h3>" . $str["onthemove"] . " " . $places[$player["destination"]] . "</h3>";
		echo "<div style=\"height:100px;margin-top:30px;\">";

		while (list($key, $snitchreport) = each($player["snitchreport"])) {
			$snitchreport["player"] = htmlentities($snitchreport["player"]);
			echo "<p>" . $str["report"] . "</p>";

			echo "<p>";
			if ($snitchreport["life"] != "" && $snitchreport["life"] <= 0) {
				printf($str["d_killed"] . "<br>",  $snitchreport["player"]);
			} else {
				if ($snitchreport["life"]) {
					printf($str["d_hit"] . "<br>",  $snitchreport["player"]);
				}
				if ($snitchreport["arrested"]) {
					printf($str["d_arrested"] . "<br>",  $snitchreport["player"]);
				} else {
					printf($str["d_escaped"] . "<br>",  $snitchreport["player"]);
					if ($snitchreport["bitchlost"] == 1) {
						printf($str["d_bitch"] . "<br>");
					} else if ($snitchreport["bitchlost"] > 1) {
						printf($str["d_bitches"] . "<br>", $snitchreport["bitchlost"]);
					}
				}
			}
			if ($snitchreport["deadcops"] == 1) {
				echo $str["d_cop"] . "<br>";
			} else if ($snitchreport["deadcops"] == 6) {
				printf($str["d_allcops"] . "<br>", $snitchreport["player"]);
			} else if ($snitchreport["deadcops"]) {
				printf($str["d_cops"] . "<br>", $snitchreport["deadcops"]);
			}
			echo "</p>";
		}
		$player["snitchreport"] = array();

		echo "</div>";
	//	printmenu(0);
		echo "<form><input type=\"submit\" value=\"" . $str["continue"]. "\"></form>";
		echo "</body></html>";
		
		save_exit();
	}
}

// ****************************************************************************
// PRINT TITEL

echo '<table border="0" cellpadding="0" cellspacing="0" width="100%">'.EOL;
echo '<tr valign="top">'.EOL;
echo '<td colspan="3" class="location">'.EOL;

if ( $player['destination'] != "" && $player['location'] != $player['destination'] )
{
	// moving
	echo $places[$player['destination']];
	$nietgotoinmenu = $player['destination'];
}
else
{
	$nietgotoinmenu = $player['location'];
	if ( isset($s) && $s )	// op locatie en op speciale plek
		echo $places[$player['location']] . ", " . $str['at'] . " " . $special[$player['location']];
	else if ( isset($places[$player['location']]) )
		echo $places[$player['location']];
}

echo '</td>'.EOL;
echo '</tr>'.EOL;
echo '<tr valign=top>'.EOL;
echo '<td width="1">'.EOL;

check_life(__LINE__);

if ( !isset($_GET['action']) || $_GET['action'] != "run")
{
	printmenu("left");
}

echo "</td><td><center>";

if ($player['destination']!="" && $player['location']!=$player['destination'])
{
	// ****************************************************************************
	// GENERATE NEW PRICES

	$updatedate = 1;

	for ($i = 0; $i < count($drugs); $i++)
	{
		$prices[$i] = mt_rand($drugs[$i]['min'], $drugs[$i]['max']);
		$foo = mt_rand(0,30);
		if ($foo == 1)
		{
			// higher
			$prices[$i] *= 3;
		}
		else if ($foo == 0)
		{
			// lower
			$prices[$i] /= 3;
			$prices[$i] = round($prices[$i]);
		}
	}
	
	$drugcnt = mt_rand(count($drugs)/2, count($drugs));
	
	while ($drugcnt < count($prices))
	{
		unset($prices[mt_rand(0,count($drugs))]);
	}
	
	$player['location'] = $player['destination'];
	$player['destination'] = "";
	$player['prices'] = $prices;

	$player['travel'] = 2;

	// interest on debt
	$player['debt'] = ceil($player['debt']*1.05);

	foreach ( $player['drugs'] AS $key => $amount )
	{
		if ( 0 >= $amount )
		{
			unset( $player['drugprices'][$key] );
			unset( $player['drugs'][$key] );
		}
	}
}


// ****************************************************************************
// OVERDOSE

if ( isset($_GET['action']) && $_GET['action'] == "od" )
{
	if ($_GET['confirm'])
	{
		$player['life'] = 0;
		check_life(__LINE__);
	}
}

// ****************************************************************************
// BANK
if ( isset($_GET['action']) && $_GET['action'] == "bank" && $player["location"] == 4)
{
	$amount = str_replace(",", "", $_GET['amount']);

	if ( isset($_GET['deposit']) )
	{
		if ($amount > 0 && $amount <= $player["cash"])
		{
			$player["cash"] -= $amount;
			$player["bank"] += $amount;
		}
		else
		{
			echo "<p>" . $str["invalid"] . "</p>";
		}
	}
	else
	{
		if ($amount > 0 && $amount <= $player["bank"])
		{
			$player["cash"] += $amount;
			$player["bank"] -= $amount;
		}
		else
		{
			echo "<p>" . $str["invalid"] . "</p>";
		}
	}
}

// ****************************************************************************
// DEBT
if ( isset($_GET['action']) && $_GET['action'] == "debt" && $player['location'] == 0)
{
	$amount = 0;
	if ( isset($_GET['amount']) )	$amount = $_GET['amount'];
	$amount = (FLOAT)trim(str_replace(',', '', $_GET['amount']));

	if ( isset($_GET['deposit']) )
	{
		if ($amount > 0 && $amount <= $player['cash'] && $amount <= $player['debt'])
		{
			$player['cash'] -= $amount;
			$player['debt'] -= $amount;
		}
		else
		{
			echo "<p>" . $str["invalid"] . "</p>";
		}
	}
	else
	{
		if ( $amount > 0)
		{
			if ($amount + $player["debt"] > MAX_LOAN)
			{
				printf("<p>" . $str["maxloan"] . "</p>", MAX_LOAN-$player["debt"]);
			}
			else
			{
				$player['cash'] += $amount;
				$player['debt'] += $amount;
			}
		}
		else
		{
			echo "<p>" . $str["invalid"] . "</p>";
		}
	}
	if ($player['debt'] <= MAX_LOAN)
	{
		$player['threat'] = 0;
	}
}


// ****************************************************************************
// OPERATE
if ( isset($_GET['action']) && $_GET['action'] == "operate" && $player['location'] == 2)
{
	$price = (100 - $player['life']) * 1500 + 1500;
	if ($price <= $player["cash"])
	{
		$player['cash'] -= $price;
		$player['life'] = 100;
	}
	else
	{
		echo $str['nomoney']."<br>";
	}
}


// ****************************************************************************
// BITCHES

if ( isset($_GET['action']) && $_GET['action'] == "hire" && $player["location"] == 1)
{
	$price = $bitchactions[$_GET['activity']]['price'];
	if ( $price )
	{
		if ($player['cash'] >= $price)
		{
			switch ($_GET['activity'])
			{
				case 0:
					// SEX

					switch (mt_rand(0,3)) {
						case 0:
							$player["cash"] -= $price;
							$player["life"] -= 10;
							echo $str["disease"];
							check_life(__LINE__);
							break;
						case 1:
							$player["cash"] = 0;
							echo $str["mugged"];
							break;
						default:
							$player["cash"] -= $price;
							echo $str["ooh"];
							break;
					}
					break;						
				case 1:
					// SPY
					if (is_numeric($dealer)) {
						$qry = "select * from dopewars where id = $dealer;";
						$result = $db->qry($qry);
						if ($result[0]) {
							$subject = unserialize(stripslashes($result[0]["player"]));
							if ($subject["prison"]) {
								$loc = $str["prison"];
							} else {
								$loc = $places[$subject["location"]];
							}
							printf($str["spyreport"],
								htmlentities($subject["name"]),
								$loc,
								$subject["cash"],
								$subject["bank"],
								$subject["debt"],
								htmlentities($subject["name"]),
								$subject["bitches"],
								array_sum($subject["guns"]),
								$subject["space"],
								$subject["life"]);
							$player["cash"] -= $price;
						} else {
							echo "<p>" . $str["invalidname"] . "</p>";
						}
						break;
					}
					echo "<form>";
					echo "<input type=hidden name=action value=hire>";
					echo "<input type=hidden name=activity value=1>";
					echo "<input type=hidden name=s value=1>";
					echo "<p>".$str['hirespy']."</p><p>".$str['name'].": ";
					dealer_list();
					echo "</p><p>";
					echo "<input type=submit value=\"".$str['hire']."\">";
					echo "<p><a href=\"".BASEPAGE."\">".$str['leave']."</a></p>";
					echo "</form></td><td width=1>";
					printmenu("right");
					save_exit();
					
				case 2:
					// SNITCH
					if ( isset($_GET['dealer']) && is_numeric($_GET['dealer']) )
					{
						$qry = "SELECT * FROM dopewars WHERE id='".$_GET['dealer']."';";
						$q = mysql_query($qry);
						if (mysql_num_rows($q))
						{
							$result = mysql_fetch_assoc($q);
							$subject = unserialize(stripslashes($result['player']));
							$subject['snitches'][] = $uid;
							save_player($result['name'], $subject);
							printf($str['snitchhired'], htmlentities($subject['name']));
							$player['cash'] -= $price;
						}
						else
							echo "<p>".$str['invalidname']."</p>";
						break;
					}
					echo "<form>";
					echo "<input type=hidden name=action value=hire>";
					echo "<input type=hidden name=activity value=2>";
					echo "<input type=hidden name=s value=1>";
					echo "<p>".$str['hiresnitch']."</p><p>".ucfirst(strtolower($str['name'])).": ";
					dealer_list();
					echo "</p><p>";
					echo "<input type=submit value=\"".ucfirst(strtolower($str['hire']))."\">";
					echo "<p><a href=\"".BASEPAGE."\">".ucfirst(strtolower($str['leave']))."</a></p>";
					echo "</form></td><td width=1>";
					printmenu("right");
					save_exit();
	
				case 3:
					// DRUGS RUNNER

					if ( $player['bitches'] >= MAX_BITCHES )
					{
						echo $str['maxbitch'];
					}
					else
					{
						$player['cash'] -= $price;
						$player['bitches'] ++;
						$player['space'] +=10;
						echo $str['morespace'];
					}
					break;
			}
		}
		else
			echo $str['nomoney'];

	}

}

if ( isset($_GET['action']) && ($_GET['action'] == "buy" || $_GET['action'] == "sell") && isset($_GET['quantity']) && 0 <= $_GET['quantity'] )
{
	// ****************************************************************************
	// BUY / SELL

	if ( isset($_GET['drug']) && $_GET['drug'] != "" )
	{
		$player["prices"][$_GET['drug']] = isset($player["prices"][$_GET['drug']]) ? $player["prices"][$_GET['drug']] : 0;
		$realprice = $_GET['quantity'] * $player["prices"][$_GET['drug']];
		
		if ( $player["prices"][$_GET['drug']] || $_GET['action'] != "buy" )
		{
			if ($_GET['action'] == "buy")
			{
				// buy drugs
				if ($realprice > $player['cash'])
				{
					$maxquantity = floor ($player['cash'] / $player['prices'][$_GET['drug']]);
					echo $str['nomoney'] . "<br>";
				}
				else if ($_GET['quantity'] > $player['space'])
				{
					printf($str['nospace'], $_GET['quantity']) . "<br>";
				}
				else if ( 0 < $_GET['quantity'] )
				{
					$player['cash'] -= $realprice;
					$player['held'] += $_GET['quantity'];
					$player['space'] -= $_GET['quantity'];

					if ( !isset($player['drugs'][$_GET['drug']]) )		 	$player['drugs'][$_GET['drug']] = 0;
					if ( !isset($player['drugprices'][$_GET['drug']]) ) 	$player['drugprices'][$_GET['drug']] = 0;

					$drugamount = round( $player['drugs'][$_GET['drug']] * $player['drugprices'][$_GET['drug']] );
					$drugamount += $realprice;

					$player['drugs'][$_GET['drug']] += $_GET['quantity'];
					$player['drugprices'][$_GET['drug']] = round($drugamount / $player['drugs'][$_GET['drug']]);
	
				}
			}
			else
			{
				// sell drugs
				if ($_GET['quantity'] > $player['drugs'][$_GET['drug']])
				{
					printf($str['nodrug'], $_GET['quantity'], $drugs[$_GET['drug']]['name']) . "<br>";
				}
				else
				{
					$player['cash'] += $realprice;
					$player['held'] -= $_GET['quantity'];
					$player['space'] += $_GET['quantity'];
					$player['drugs'][$_GET['drug']] -= $_GET['quantity'];
				}
			}			
		}
		$_GET['drug'] = '';

	}
	else if ( isset($_GET['gun']) && $_GET['gun'] != '' && $_GET['quantity'] > 0 && $player['location'] == 5)
	{
		$realprice = $_GET['quantity'] * $guns[$_GET['gun']]['price'];
		reset( $player['guns'] );
		$totalguns = array_sum($player["guns"]);

		if ( isset($_GET['action']) && $_GET['action'] == "buy" )
		{
			// BUY guns
			if ($realprice > $player["cash"])
			{
				echo $str["noguncash"] . "<br>";
			}
			else if ($_GET['quantity'] > $player["bitches"] + 2 - $totalguns)
			{
				echo $str["nogunspace"] . "<br>";
			}
			else
			{
				$player["cash"] -= $realprice;
				if ( isset($player["guns"][$_GET['gun']]) )
				{
					$player["guns"][$_GET['gun']] += $_GET['quantity'];
				}
				else
				{
					$player["guns"][$_GET['gun']] = $_GET['quantity'];
				}
			}
		}
		else
		{
			// SELL guns
			if ( $_GET['quantity'] > $player["guns"][$_GET['gun']])
			{
				printf($str["nodrug"], $_GET['quantity'], $guns[$_GET['gun']]["name"]) . "<br>";
			}
			else
			{
				$player["cash"] += $realprice;
				$player["guns"][$_GET['gun']] -= $_GET['quantity'];
			}
		}			
	}
}

if ( isset($_GET['s']) && $_GET['s'] )
{
	switch ($player['location'])
	{
		case 0:
			// ****************************************************************************
			// LOANSHARK

			echo "<form>";
			echo "<input type=hidden name=action value=debt>";
			echo "<p>".$str['amount'].": $currency ";
			echo "<input name=amount type=text value=0 size=8></p><p>";
			echo "<input type=submit name=withdraw value=\"".$str['loan']."\"> ";
			echo "<input type=submit name=deposit value=\"".$str['pay']."\">";
			echo "<input name=s type=hidden value=1>";
		
			echo "</form>";	
			break;
		
		case 1:
			// ****************************************************************************
			// BITCHES


			echo "<form name=hire><input type=hidden name=action value=hire>";
			echo $str['hirebitch'];
			echo "<br><br><select name=activity size=4 style=\"width:200px;\">";
			reset($bitchactions);
			while (list($key, $val) = each($bitchactions))
			{
				echo "<option value=\"$key\">".$val['name']." - $currency ".$val['price']."</option>";
			}
			
			echo "</select><br><br>";
			echo "<input name=s type=hidden value=1>";
			echo "<input type=submit value=\"".$str['hire']."\"></form>";
			
			break;
			
		case 2:
			// ****************************************************************************
			// HOSPITAL

			if ($player["life"] < 100)
			{
				$price = (100 - $player['life']) * 1500 + 1500;
				printf ("<p>".$str['doctor']."</p>", $price);

				echo "<form>";
				echo "<input type=hidden name=action value=\"operate\">";
				echo "<input type=submit value=\"".$str['operate']."\"> ";
				echo "<input name=s type=hidden value=1>";
				echo "</form>";	
			} else {
				echo "<p>".$str['recovered']."</p>";

			}
			break;

		case 4:
			// ****************************************************************************
			// BANK

			echo "<form action=\"\">";
			echo "<input type=\"hidden\" name=\"action\" value=\"bank\">";
			echo "<p>" . $str["amount"] . ": $currency ";
			echo "<input name=\"amount\" type=\"text\" value=\"0\" size=\"8\"></p><p>";
			echo "<input type=\"submit\" name=\"withdraw\" value=\"" . $str["withdraw"] . "\"> ";
			echo "<input type=\"submit\" name=\"deposit\"  value=\"" . $str["deposit"]  . "\">";
			echo "<input name=\"s\" type=\"hidden\" value=\"1\">";
		
			echo "</form>";	
			break;
		case 5:
			// ****************************************************************************
			// GUNSHOP
			
			echo "<table border=1 cellpadding=2 cellspacing=0><tr>";
			echo "<td><center><form name=buy><input type=\"hidden\" name=\"action\" value=\"buy\">";
			echo $str["available"];
			echo "<br><br><select name=\"gun\" size=\"5\" style=\"width:160px;\">";
			reset ($guns);
			while (list($gun, $val) = each($guns))
			{
				$selected = (isset($_GET['gun']) && $_GET['gun'] == $gun && isset($_GET['action']) && $_GET['action'] == 'buy' ) ? ' SELECTED' : '';
				echo '<option value="'.$gun.'"' . $selected . '>' . $val["name"] . ' - '.$currency.' ' . $val["price"] . '</option>';
			}
			
			echo "</select><br><br>";
			echo "<input name=\"quantity\" type=\"hidden\" value=\"1\">";
			echo "<input name=\"s\" type=\"hidden\" value=\"1\">";
			echo "<input type=\"submit\" value=\"". $str["buy"] . " &gt;\"></form></td>";
			
			
			echo "<td><center><form action=\"\" name=\"sell\"><input type=\"hidden\" name=\"action\" value=\"sell\">";
			echo $str["carried"];
			echo "<br><br><select name=\"gun\" size=\"5\" style=\"width:160px;\">";
			reset ($player["guns"]);
			while (list($gun, $val) = each($player["guns"]))
			{
				$gunname = $guns[$gun]["name"];
				if ($val)
				{
					$selected = (isset($_GET['gun']) && $_GET['gun'] == $gun && isset($_GET['action']) && $_GET['action'] == 'sell' ) ? ' SELECTED' : '';
					echo '<option value="'.$gun.'"'.$selected.'>'.$gunname.' - '.$val.'</option>';
				}
			}
			
			echo "</select><br><br>";
			echo "<input name=\"quantity\" type=\"hidden\" value=\"1\">";
			echo "<input name=\"s\" type=\"hidden\" value=\"1\">";
			echo "<input type=\"submit\" value=\"&lt; ". $str["sell"] . "\"></form></td>";
			
			echo "</tr></table>";
			break;
	}

	
	echo "<p><a href=\"".BASEPAGE."\">".$str['leave']."</a></p>";

}
else
{

	// ****************************************************************************
	// OVERDOSE
	if ( isset($_GET['action']) && $_GET['action'] == "od")
	{
		echo "<p>".$str['qod']."</p>";
		echo "<form>";
		echo "<input type=hidden name=action value=od>";
		echo "<input type=submit name=confirm value=\"".$str['yes']."\">";
		echo "<p><a href=\"".BASEPAGE."\">".$str['leave']."</a></p>";

	// ****************************************************************************
	// DEALING DRUGS
	
	}
	else if ( isset($_GET['action']) && ($_GET['action'] == "buy" || $_GET['action'] == "sell") && isset($_GET['drug']) && $_GET['drug'] != '' )
	{
		echo "<form>";
		echo '<input type=hidden name=action value="'.$_GET['action'].'">';
		echo '<input type=hidden name=drug value="'.$_GET['drug'].'">';
		$drugname = $drugs[$_GET['drug']]["name"];
		
		$price = isset($player["prices"][$_GET['drug']]) ? max(1, $player["prices"][$_GET['drug']]) : -1;

		if ($_GET['action'] == "sell")
		{
			$max = $player["drugs"][$_GET['drug']];
		}
		else
		{
			$max = min(floor ($player["cash"] / $price), $player["space"]);
		}

		if ($price == -1)
		{
			$price = 0;
			printf("<p>" . $str["dump"] . "</p>", $drugname, $drugname);
		}
		echo "<p>$drugname @ $currency $price</p>";
		echo "<p>" . $str["quantity"] . ": ";
		echo "<input name=\"quantity\" type=\"text\" value=\"$max\" size=\"3\"><br>";
		echo "(maximum: $max)</p>";
		if ($_GET['action'] == "buy") {
			echo "<input type=\"submit\" value=\"" . $str["buy"] . "\">";
		} else {
			echo "<input type=\"submit\" value=\"" . $str["sell"] . "\">";
		}
	
		echo "</form>";	
	}
	else
	{
		// echo "[optional - drugs-msg]<br>";
		echo '<font class="drugsextremes">';
		while (list($drug, $val) = each($player["prices"]))
		{
			if ($val < $drugs[$drug]['min'])
			{
				if ($drugs[$drug]['minmsg'])
				{
					echo $drugs[$drug]['minmsg']."<br>\n";
				}
				else
				{
					$foo = mt_rand(0, count($minmsgs)-1);
					printf($minmsgs[$foo]."<br>", $drugs[$drug]['name']);
				}
			}
			if ($val > $drugs[$drug]['max'])
			{
				if ($drugs[$drug]['maxmsg'])
				{
					echo $drugs[$drug]['maxmsg']."<br>\n";
				}
				else
				{
					$foo = mt_rand(0, count($maxmsgs)-1);
					printf($maxmsgs[$foo]."<br>", $drugs[$drug]['name']);
				}
			}
		}
		echo '</font><br>'.EOL;
		echo '<table border="1" cellpadding="2" cellspacing="0"><tr valign="top">'.EOL;
		echo "<td><center><form name=buy><input type=hidden name=action value=\"buy\">".EOL;
		echo $str["available"].EOL;
		echo "<br><br><select name=\"drug\" size=\"11\" style=\"width:200px;\">".EOL;
		foreach ( $player['prices'] AS $key => $price )
		{
			$drugname = $drugs[$key]['name'];
			echo '<option value="'.$key.'">'.$drugname.' - '.$currency.' '.nummertje($price).'</option>'.EOL;
		}
		echo "</select><br><br>";
		echo "<input type=\"submit\" value=\"". $str["buy"] . " &gt;\"></form></td>";
		
		
		echo "<td><center><form name=sell><input type=\"hidden\" name=\"action\" value=\"sell\">";
		echo $str["carried"];
		echo "<br><br><select name=\"drug\" size=\"11\" style=\"width:200px;\">";
		foreach ( $player['drugs'] AS $key => $amount )
		{
			$drugname = $drugs[$key]['name'];
			if ( 0 < $amount )
			{
				echo '<option value="'.$key.'">'.$drugname.' - '.$amount.' @ '.$currency.' '.nummertje($player['drugprices'][$key]).'</option>'.EOL;
			}
		}
		echo "</select><br><br>";
		echo "<input type=\"submit\" value=\"&lt; ". $str["sell"] . "\"></form></td>";
		echo "</tr></table>";

		if ( isset($special[$player["location"]]) )
		{
			echo "<p><a class=\"tospecial\" href=\"?s=1\">" . $str["to"] . " ". $special[$player["location"]] . "</a></p>";
		}
	}
}

echo "</td><td width=1>";

if ( !isset($_GET['action']) || $_GET['action'] != "run")
{
	printmenu("right");
}

echo '</td><tr>';
echo '</table>';



$player['fight'] = 0;
$player['reloading'] = 0;
$player['opponent'] = "";

echo "<pre>\n";
print_r( $player );

save_exit( );














///////////////////////////////////////////////////////////////
// SET LANGUAGE STUFF

function get_language_stuff( )
{
	global $str, $places, $special, $drugs, $bitchactions, $fight;
	global $language, $currency, $guns, $maxmsgs, $minmsgs;

	if ( isset($_SESSION['language']) && $_SESSION['language'] == "NL")
	{
		$language = "NL";

		$places = array(
			"Spangen",
			"Tussendijken",
			"Dijkzigt/Cool",
			"Oude Westen",
			"Centraal Station",
			"Tarwewijk",
			"Hillesluis",
			"Zuidplein");

		$special = array(
			0 => "de woekeraar op de Mathenesserdijk",
			1 => "de hero&iuml;nehoeren op de Keileweg",
			2 => "de polikliniek",
			4 => "het GWK",
			5 => "de wapenhandelaar in de Millinxbuurt");

		$drugs = array(
			array("name" => "LSD",			"min" => 1000,	"max"=> 4400,	"minmsg" => "", "maxmsg"=>"LSD is bezig aan een come-back in het party-circuit!"),
			array("name" => "coca&iuml;ne",	"min" => 13000,	"max"=> 40000,	"minmsg" => "Bolletjesslikkers hebben Rotterdam Airport ontdekt: coca&iuml;ne in overvloed.",	"maxmsg" => "In de haven is een lading Columbiaanse coke onderschept."),
			array("name" => "hero&iuml;ne",	"min" => 5500,	"max"=> 13000,	"minmsg" => "In de Pauluskerk wordt gratis methadon verstrekt, de hero&iuml;ne markt is ingestort.",	"maxmsg" => "Hero&iuml;ne-junks komen hier massaal naar toe, er is een tekort aan smack."),
			array("name" => "hash",			"min" => 480,	"max"=> 1280,	"minmsg" => "Een Marokkaans schip heeft grote hoeveelheden hash afgeleverd.",	"maxmsg" => "Een container maroc is door de douane vernietigd."),
			array("name" => "wiet",			"min" => 315,	"max"=> 890,	"minmsg" => "",	"maxmsg" => "Een hennepkwekerij is opgerold, de wietprijzen zijn omhooggeschoten!"),
			array("name" => "speed",		"min" => 90,	"max"=> 250,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "XTC",			"min" => 2800,	"max"=> 3700,	"minmsg" => "Een nieuw XTC laboratorium dumpt pillen voor weinig.", "maxmsg" => "De politie heeft een XTC laboratorium ontmanteld."),
			array("name" => "valium",		"min" => 11,	"max"=> 60,		"minmsg" => "Rivaliserende dealers hebben een apotheek beroofd en verkopen goedkoop valium!",	"maxmsg" => ""),
			array("name" => "paddo's",		"min" => 630,	"max"=> 1300,	"minmsg" => "",	"maxmsg" => "In een proefproces zijn paddo's verboden, de prijzen schieten omhoog."),
			array("name" => "peyote",		"min" => 220,	"max"=> 700,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "PCP",			"min" => 1000,	"max"=> 2500,	"minmsg" => "",	"maxmsg" => ""));

		$bitchactions = array (
			array("name" => "geile neukseks",	"price" => 20),
			array("name" => "spion",			"price" => 6500),
			array("name" => "verklikker",		"price" => 10000),
			array("name" => "drugskoerier",		"price" => 35000));

		$fight		= Array("blijven staan",
							"over geven",
							"vluchten",
							"schieten");	

		$maxmsgs	= Array("%s is in de mode!",
							"Een lading %s is onderschept, er is schaarste!",
							"Verslaafden betalen belachelijke prijzen voor %s!"
							);
		$minmsgs	= Array("De markt wordt overspoeld met %s!",
							"%s is gvd niet duur vandaag!!"
							);

		$str["nospace"]		= "Je hebt geen ruimte voor %s drugs.";
		$str["nodrug"]		= "Je hebt geen  %s eenheden %s.";

		$str["morespace"]	= "Je kunt nu 10 extra units meenemen.";

		$str["doctor"]		= "Je zelf laten opereren kost &euro; %s.";
		$str["recovered"]	= "Je bent kerngezond.";
		$str["disease"]		= "Je hebt een SOA opgelopen.";
		$str["mugged"]		= "Je zakken zijn gerold: je geld is weg!";
		$str["ooh"]			= "'Ooh, was het ook goed voor jou?'";
		$str["operate"]		= "opereren";

		$str["nogunspace"]	= "Je kunt niet meer wapens dragen.";
		$str["noguncash"]	= "Dit wapen is te duur.";
		$str["nomoney"]		= "Je hebt daar geen geld voor.";
		$str["sell"]		= "verkoop";
		$str["buy"]			= "koop";
		$str["available"]	= "markt:";
		$str["carried"]		= "in bezit:";
		$str["to"]			= "naar";
		$str["at"]			= "bij";
		$str["amount"]		= "bedrag";
		$str["quantity"]	= "hoeveelheid";
		$str["withdraw"]	= "opnemen";
		$str["deposit"]		= "storten";
		$str["invalid"]		= "Ongeldige transactie";

		$str["dump"]		= "<b>Let op:</b> er wordt hier geen %s verhandeld!<br>Je dumpt dus je %s als je verkoopt.";

		$str["loan"]		= "lenen";
		$str["pay"]			= "betalen";
		$str["leave"]		= "uitgang";

		$str["hire"]		= "huren";
		$str["hirebitch"]	= "huur hoer voor/als:";
		$str["maxbitch"]	= "Je niet meer dan 10 hoeren huren als drugskoerier.";
		$str["maxloan"]		= "De woekeraar wil nog je maximaal &euro; %s lenen.";	

		$str["cash"]		= "contanten";
		$str["bank"]		= "bank";
		$str["debt"]		= "schuld";
		$str["total"]		= "totale vermogen";
		$str["name"]		= "naam";

		$str["bitches"]		= "hoeren";
		$str["life"]		= "gezondheid";
		$str["space"]		= "ruimte";
		$str["guns"]		= "wapens";

		$str["status"]		= "toestand";
		$str["goto"]		= "ga naar";
		$str["instruct"]	= "instructies";
		$str["logout"]		= "log uit";

		$str["chase"]		= "%s politie-agenten achtervolgen je! Wat doe je?";
		$str["surrender"]	= "overgeven";
		$str["fight"]		= "schieten";
		$str["run"]			= "vluchten";
		$str["bribe"]		= "omkopen";

		$str["nobribe"]		= "Je hebt niet genoeg cash om alle agenten om te kopen (&euro;20.000 per agent).";
		$str["bribed"]		= "Je hebt de agenten voor &euro;%s omgekocht en door ze de helft van je drugs te geven.";

		$str["youkilledcop"]	= "Je hebt een agent doodgeschoten!";
		$str["allcopskilled"]	= "Alle agenten zijn dood! Je vindt &euro; %s in hun portefeuilles.";
		$str["youmissed"]	= "Je schoot en miste.";
		$str["escaped"]		= "Je bent ontsnapt.";
		$str["cantescape"]	= "Je kunt niet wegkomen.";
		$str["copsshoot"]	= "De politie schiet met %s agenten...";
		$str["copshoot"]	= "De laatste agent schiet...";
		$str["bitchkilled"]	= "E&eacute;n van je hoeren is doodgeschoten.";
		$str["yourhit"]		= "Je bent geraakt.";	
		$str["missed"]		= "Niet geraakt!";	
		$str["forfeit"]		= "Door een plukze-maatregel wordt &euro; %s van je bankrekening gevorderd door het <a href=\"http://www.openbaarministerie.nl/over_om/over_om.php#31\" target=\"_blank\">BOOM</a>.";
	
		$str["continue"]	= "verder";	

		$str["onthemove"]	= "Onderweg naar";
		$str["lostdrugs"]	= "Je werd achtervolgd door een <b>stadsmarinier</b>. Onderweg ben je je %s kwijtgeraakt.";
		if (mt_rand(0,1))
			$str["lostdrugs"]	   = "Je werd achtervolgd door de <b>Nachtwacht</b>. Onderweg ben je je %s kwijtgeraakt.";
		$str["foundbody"]	= "Je vindt het lijk van een dode hero&iuml;nehoer met %s x %s.";
		$str["dead"]		= "Je bent <b>dood</b>!";

		$str["invalidname"]	= "Ongeldige naam";
		$str["name"]		= "naam";
		$str["hiresnitch"]	= "Huur een hero&iuml;nehoer om een andere dealer bij de politie aan te geven.<br>De politie zal deze dealer aanpakken.<br>De resultaten krijg je gemeld zodra de politie actie heeft ondernomen.";
		$str["snitchhired"]	= "%s wordt verklikt.";
		$str["snitched"]	= "Je bent verraden door %s.";
		$str["report"]		= "<b>Rapportage verklikker</b>";

		$str["spyreport"]	= "<p><b>Rapportage spion</b></p>Dealer %s bevindt zich in %s,<br>heeft &euro; %s aan contanten, &euro; %s op de bank en een schuld van &euro; %s bij de woekeraar.<br>%s heeft %s hero&iuml;nehoeren, %s wapens en nog ruimte voor %s drugs.<br>De gezondheid is %s%%.";
		$str["hirespy"]		= "Huur een hero&iuml;nehoer om de toestand van een andere dealer te achterhalen.<br>Je krijgt meteen antwoord.";

		$str["reloading"]	= "Je wapens worden opnieuw geladen ...";
		$str["reloaded"]	= "Je wapens zijn opnieuw geladen.";

		$str["loanhit1"]	= "Je komt wat mannetjes van de woekeraar tegen.<br>Ze breken je vingers en maken je duidelijk je schuld af te lossen.";
		$str["loanhit2"]	= "Het is de woekeraar menes!<br>Je wordt met een loden pijp bewerkt.";
		$str["loanhit3"]	= "De woekeraar heeft je te pakken!<br>Je voeten worden in beton gestort en je wordt in de Maas gegooid.";

		$str["d_killed"]	= "Dealer %s is gedood.";
		$str["d_hit"]		= "Dealer %s is beschoten en gewond geraakt.";
		$str["d_escaped"]	= "%s kon ontkomen.";
		$str["d_arrested"]	= "%s is gearresteerd.";
		$str["d_cop"]		= "E&eacute;n agent kwam om.";
		$str["d_cops"]		= "%s agenten kwamen om.";
		$str["d_allcops"]	= "Alle agenten zijn doodgeschoten door %s.";
		$str["d_bitch"]		= "E&eacute;n hoer is doodgeschoten.";
		$str["d_bitches"]	= "%s hoeren zijn doodgeschoten.";

		$str["dateformat"]	= "%d %B, %H:%M";
		$str["arrested"]	= "Je bent gearresteerd.";
		$str["prison"]		= "de gevangenis";
		$str["inprison"]	= "Je zit in de gevangenis tot %s.";
		$str["released"]	= "Je bent vrijgelaten.";

		$str["op_cantescape"]	= "%s probeert te vluchten, maar kan niet wegkomen.";
		$str["op_escaped"]	= "%s is gevlucht.";
		$str["op_stands"]	= "%s staat als een idioot toe te kijken.";
		$str["op_shoots"]	= "%s schiet...";

		$str["youkilledbitch"]	= "Je hebt &eacute;&eacute;n van de hoeren van %s doodgeschoten.";
		$str["youkilledopponent"]	= "Je hebt %s doodgeschoten. Je vindt &euro; %s in de portefeuille.";
		$str["youshotopponent"]	= "Je hebt %s geraakt.";
		$str["opponentdead"]	= "%s is dood.";
	
		$str["bitchgone"]	= "E&eacute;n van je hoeren is er vandoor gegaan.";
	
		$str["encounter"]	= "Je komt %s tegen, wat doe je?";
	
		$str["op_status"]	= "Toestand van %s:<br>hoeren: %s<br>wapens: %s<br>gezondheid: %s%%";

		$str["qod"]		= "Wil je echt een overdosis nemen (en wellicht legendarisch worden)?";
		$str["yes"]		= "ja";
		$str["od"]		= "overdosis";

		$currency = "&euro;";
	}
	else
	{
		$language = "EN";

		$places = array(
			"the Bronx",
			"the Ghetto",
			"Central Park",
			"Coney Island",
			"Manhattan",
			"Brooklyn",
			"Queens",
			"Staten Island");

		$special = array(
			0 => "the loanshark",
			1 => "the pub",
			2 => "the hospital",
			4 => "the bank",
			5 => "Dan's House of Guns");

		$drugs = array(
			array("name" => "acid",		"min" => 1000,	"max"=> 4400,	"minmsg" => "The market is flooded with cheap home-made acid!"),
			array("name" => "cocaine",	"min" => 15000,	"max"=> 29000,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "heroin",	"min" => 5500,	"max"=> 13000,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "hashish",	"min" => 480,	"max"=> 1280,	"minmsg" => "The Marrakesh Express has arrived!",	"maxmsg" => ""),
			array("name" => "weed",		"min" => 315,	"max"=> 890,	"minmsg" => "Columbian freighter dusted the Coast Guard!",	"maxmsg" => "Weed prices have bottomed out!"),
			array("name" => "speed",	"min" => 90,	"max"=> 250,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "ecstacy",	"min" => 2800,	"max"=> 3700,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "ludes",	"min" => 11,	"max"=> 60,		"minmsg" => "Rival drug dealers raided a pharmacy and are selling cheap ludes!",	"maxmsg" => ""),
			array("name" => "shrooms",	"min" => 630,	"max"=> 1300,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "peyote",	"min" => 220,	"max"=> 700,	"minmsg" => "",	"maxmsg" => ""),
			array("name" => "PCP",		"min" => 1000,	"max"=> 2500,	"minmsg" => "",	"maxmsg" => ""));

		   $bitchactions = array (
				array("name" => "sex",		"price" => 20),
				array("name" => "spy",		"price" => 6500),
				array("name" => "snitch",	"price" => 10000),
				array("name" => "drug runner",	"price" => 35000)
				);

		$str["sell"]	= "sell";
		$str["buy"]	= "buy";
		$str["available"]	= "available:";
		$str["carried"]		= "carried:";
		$str["to"]	= "to";
		$str["at"]	= "at";
		$str["amount"]	= "amount";
		$str["quantity"]	= "quantity";
		$str["withdraw"]	= "withdraw";
		$str["loan"]	= "loan";
		$str["pay"]	= "pay";
		$str["invalid"]	= "Invalid transaction";
		$str["operate"]	= "operate";

		$str["instruct"] = "instructions";
		$str["logout"]  = "log out";

		$str["deposit"]	= "deposit";
		$str["onthemove"]	= "jetting to";

		$fight = array("stand", "surrender", "run", "fire");

		$maxmsgs = array("Cops made a big %s bust! Prices are outrageous!", "Addicts are buying %s at ridiculous prices!", "The addicts are going nuts for %s!");
		$minmsgs = array("The market is flooded with cheap %s.");

		$str["nospace"]	= "You can't carry %s more drugs.";
		$str["nodrug"]	= "You're shittin' me right? You don't have %s units of %s.";

		$str["morespace"]	= "Now you can carry 10 more units.";

		$str["doctor"]		= "The doctor can fix you up for \$ %s.";
		$str["recovered"]	= "You are healthy.";

		$str["disease"]	= "You caught a venereal disease.";
		$str["mugged"]	= "You got mugged! Your money is gone.";
		$str["ooh"]	= "'Ooh, was it goof for you too?'";
		$str["operate"]	= "fix me up";

		$str["nogunspace"]	= "You can't carry more guns.";
		$str["noguncash"]	= "You can't afford that gun.";
		$str["nomoney"]	= "You don't have enough money for that.";
		$str["sell"]	= "sell";
		$str["buy"]	= "buy";
		$str["available"]	= "available:";
		$str["carried"]		= "carried:";
		$str["to"]	= "to";
		$str["at"]	= "at";
		$str["amount"]	= "amount";
		$str["quantity"]	= "quantity";
		$str["withdraw"]	= "withdraw";
		$str["deposit"]	= "deposit";
		$str["invalid"]	= "Invalid transaction";

		$str["loan"]	= "loan";
		$str["pay"]	= "pay";
		$str["leave"]	= "leave";

		$str["dump"]	= "<b>Warning:</b> %s is not sold here!<br>You're dumping your %s if you sell.";


		$str["hire"]	= "hire";
		$str["hirebitch"]	= "hire bitch to / for:";
		$str["maxbitch"]	= "You can't hire more than 10 bitches to carry drugs.";
		$str["maxloan"]	= "The loan shark only want to loan you \$; %s more.";	

		$str["cash"]	= "cash";
		$str["bank"]	= "bank";
		$str["debt"]	= "debt";
		$str["total"]	= "score";
		$str["name"]	= "name";

		$str["bitches"]	= "bitches";
		$str["life"]	= "health";
		$str["space"]	= "space";
		$str["guns"]	= "guns";

		$str["status"]	= "status";
		$str["goto"]	= "go to";
		$str["instruct"] = "instructions";
		$str["logout"]	= "log out";

		$str["chase"]	= "%s cops are chasing you! What do you do?";
		$str["surrender"] = "surrender";
		$str["fight"]	= "fire";
		$str["run"]	 = "run";
		$str["bribe"]	= "bribe";

		$str["nobribe"]	= "You don't have enough money to bribe all cops (\$ 20,000 a cop).";
		$str["bribed"]  = "You've bribed the cops (\$ %s and half of your drugs).";

		$str["youkilledcop"]	= "You killed a cop!";
		$str["allcopskilled"]	= "All cops are dead! You find \$ %s on them.";
		$str["youmissed"]	= "You failed to hit.";
		$str["escaped"]		= "You escaped.";
		$str["cantescape"]	= "You can't escape.";
		$str["copsshoot"]	= "%s cops are shooting...";
		$str["copshoot"]	= "The last cop shoots...";
		$str["bitchkilled"]	= "One of your bitches got killed.";
		$str["yourhit"]		= "You've been hit.";	
		$str["missed"]		= "Miss!";	
		$str["forfeit"]		 = "The <a href=\"http://www.usdoj.gov/dea/programs/af.htm\" target=\"_blank\">DEA</a> forfeits $ %s from your bank account.";

		$str["continue"]	= "continue";	

		$str["lostdrugs"]	= "They chased you! You lost the %s.";
		$str["foundbody"]	= "You find the dead body of a bitch with %s x %s.";
		$str["dead"]		= "You're <b>dead</b>!";

		$str["invalidname"]	= "Invalid name";
		$str["name"]		= "name";
		$str["hiresnitch"]	= "Hire a bitch to tip off a dealer to the cops.<br>The cops will attack that dealer.<br>Later you will be informed on the encounter.";
		$str["snitchhired"]	= "%s is being tipped of.";
		$str["snitched"]	= "You were tipped off by %s.";
		$str["report"]		= "<b>Snitch report</b>";

		$str["spyreport"]	= "<p><b>Spy report</b></p>Dealer %s is located in %s,<br>has \$ %s in cash, \$ %s in the bank and a debt of \$ %s.<br>%s has %s bitches, %s guns and space left for %s drugs.<br>Health is %s%%.";
		$str["hirespy"]		= "Hire a bitch to find out the status of another dealer. You receive an answer immediately.";

		$str["reloading"]	= "Your guns are being reloaded ...";
		$str["reloaded"]	= "Your guns are reloaded.";

		$str["loanhit1"]	= "The loan shark send some of his men.<br>They break your fingers and tell you to pay off the debt.";
		$str["loanhit2"]	= "The loan shark is serious!<br>You got beaten up by his men.";
		$str["loanhit3"]	= "The loan shark wasted you!";

		$str["d_killed"]	= "Dealer %s is dead.";
		$str["d_hit"]		= "Dealer %s was shot at and got hit.";
		$str["d_escaped"]	= "%s got away.";
		$str["d_arrested"]	= "%s is arrested.";
		$str["d_cop"]		= "A cop was wasted.";
		$str["d_cops"]		= "%s cops were wasted.";
		$str["d_allcops"]	= "All were wasted by %s.";
		$str["d_bitch"]		= "One bitch got killed.";
		$str["d_bitches"]	= "%s bitches got killed.";

		$str["dateformat"]	= "%A, %B %e, %T";
		$str["arrested"]	= "You are arrested.";
		$str["prison"]		= "prison";
		$str["inprison"]	= "You are in prison until %s CET.";
		$str["released"]	= "You are released.";

		$str["op_cantescape"]	= "%s tries to escape but can't get away.";
		$str["op_escaped"]	= "%s has escaped.";
		$str["op_stands"]	= "%s stands there like an idiot.";
		$str["op_shoots"]	= "%s fires...";

		$str["youkilledbitch"]	= "You wasted one of the bitches of %s.";
		$str["youkilledopponent"]	= "You killed %s. You find \$ %s on him.";
		$str["youshotopponent"]	   = "You hit %s.";
		$str["opponentdead"]	= "%s is dead.";

		$str["bitchgone"]	= "One of your bitches ran away.";

		$str["encounter"]	= "You run into %s, what do you do?";

		$str["op_status"]	= "Status of %s:<br>bitches: %s<br>weapons: %s<br>health: %s%%";

		$str["qod"]		= "Do you really want to overdose (and maybe become legendary)?";
		$str["yes"]		= "yes";
		$str["od"]		= "overdose";

		$currency = "\$";
	}

	$guns = Array(	Array(	"name" => "Ruger MK4",
							"price" => 2500),
					Array(	"name" => "Baretta 8357",
							"price" => 3500),
					Array(	"name" => "S&amp;W Magnum",
							"price" => 4500),
					Array(	"name" => "Glock 21",
							"price" => 6000),
					Array(	"name" => "HK MP5",
							"price" => 15000)
					);
}















///////////////////////////////////////////////////////////////
// FUNCTIONS

function check_max()
{
	global $db;

	$count = mysql_num_rows(mysql_query("SELECT id FROM dopewars WHERE (now()-date)<60"));
	if ($count >= MAX_USERS)
	{
		echo "<html><title>dopewars</title><head></head><body><h2>dopewars - login</h2>";
		echo "<p>Too many users connected.<br>Please try to login again in a minute.</p><p><a href=\"?action=login\">back</a></p></body></html>";
		exit;
	}
}

function save_exit()
{
	global $player, $uid, $onthemove, $updatedate;

	// calculate total
	$player['cash'] = round($player['cash']);
	$player['bank'] = round($player['bank']);
	$player['debt'] = round($player['debt']);

	$player['total'] = $player['cash'] + $player['bank'] - $player['debt'];
	reset($player['drugs']);
	while (list($drug, $val) = each($player['drugs']))
	{
		$player['total'] += $player['drugprices'][$drug] * $val;
	}

	// update database
	// echo "<pre>\n";
	// print_r( $player );
	$pl = addslashes(serialize($player));
	$qry = "UPDATE dopewars SET player='".$pl."',onthemove='".$onthemove."',score='".$player['total']."',date=NOW() WHERE name='$uid';";
	mysql_query($qry) or die("Error (0001): ".mysql_error());

	exit;
}

function save_player( $uid, $player, $stopmoving = 0 )
{
	// calculate total
	$player['total'] = $player['cash'] + $player['bank'] - $player['debt'];
	reset ($player['drugs']);
	while (list($drug, $val) = each($player['drugs']))
	{
		$player['total'] += $player['drugprices'][$drug] * $val;
	}
	
	// update database
	$pl = addslashes(serialize($player));
	$qry = "UPDATE dopewars SET player='$pl'";
	if ( $stopmoving )
	{
		$qry .= ",onthemove=FALSE";
	}
	$qry .= ",score=".$player['total']." where name='$uid'";
	mysql_query($qry) or die("Error (0002): ".mysql_error());
	echo (!mysql_affected_rows()) ? "NIET UITGEVOERD - Save_Player()" : "";
}

function check_life( $line = 0 )
{
	global $player, $uid, $str, $pass;

	// echo "Called from line $line!<br>".EOL;
	// echo "LIFE: ".$player['life']."<br>".EOL;

	if ( isset($player['life']) && $player['life'] > 0)
	{
		$player['life'] = min($player['life'],100);
		return;
	}
	else
	{
		report_snitches( );
		$qry = "INSERT INTO dopescores (name, password, score) values ('".$player['name']."', '$pass', '".$player['total']."');";
		mysql_query($qry) or die("Error (0003): ".mysql_error());
		unset($_SESSION[player]);
		$qry = "DELETE FROM dopewars WHERE name='$uid';";
		mysql_query($qry) or die("Error (0004): ".mysql_error());
		echo "<br>". $str[dead];
		echo "<p><a href=\"?logout=1\">new game</a>";
		echo "</body></html>";
		exit;	
	}
}

function dealer_list()
{
	$qry = "SELECT id,name FROM dopewars ORDER BY name ASC;";
	$r = mysql_query($qry);
	echo "<select name=dealer>";
	echo "<option value=''>-select";
	while ($val = mysql_fetch_assoc($r))
	{
		echo "<option value='".$val['id']."'>".htmlentities($val['name'])."\n";
	}
	echo "</select>";
}

function report_snitches( )
{
	global $db, $uid, $player, $str;

	if ( !isset($player['currentsnitches']) || !is_array($player['currentsnitches']) )	$player['currentsnitches'] = Array( );

	$player['fightreport']['player'] = $uid;
	$foo = array_unique($player['currentsnitches']);
	$foo = implode (", ", $foo);
	if ( 0 < strlen($foo) )
	{
		printf("<br>".$str['snitched'], $foo);
	}

	$qry = implode("','", array_unique($player['currentsnitches']));
	$qry = "SELECT * FROM dopewars WHERE name in ('$qry')";
	$results = mysql_query($qry);
	while ($result = mysql_fetch_assoc($results))
	{	
		$subject = unserialize(stripslashes($result['player']));
		$subject['snitchreport'][] = $player['fightreport'];
		save_player($result['name'], $subject);
	}
	$player['currentsnitches'] = array();
	$player['fightreport'] = array();
}

function lose_bitch( $player )
{
	global $drugs, $guns;

	// subtract proportional amount of drugs
	reset ($player['drugs']);
	while (list($key, $val) = each($player['drugs'])) {
		$num = round($val/($player['bitches'] + 2));
		$player['drugs'][$key] -= $num;
		$player['space'] += $num;
	}

	// remove guns
	if (round(array_sum($player['guns'])/($player['bitches'] + 2))) {
		reset ($player['guns']);
		while (list($key, $val) = each($player['guns'])) {
			if ($val) {
				$player['guns'][$key] -= 1;
				break;
			}
		}
	}

	$player['bitches']--;
	$player['space'] -=10;

	return $player;
}

function printmenu( $side = NULL )
{
	global $places, $str, $currency, $player, $special, $nietgotoinmenu;
	$enabled = 1;

	if ($side == "right")
	{
		// status table
		echo '<table border="1" cellpadding="2" cellspacing="0" width="200">'.EOL;
		echo '<tr><td class="menuheads" colspan="2">'.ucfirst($str['status']).'</td></tr>'.EOL;
		echo '<tr><td class="menu_stats" align="right">'.$str['name'].':</td><td class="menu_stats">'.htmlentities($player['name']).'</td></tr>';
		echo '<tr><td class="menu_stats" align="right">'.$str['cash'].':</td><td class="menu_stats">'.$currency.' '.nummertje($player['cash']).'</td></tr>';
		echo '<tr><td class="menu_stats" align="right">'.$str['bank'].':</td><td class="menu_stats">'.$currency.' '.nummertje($player['bank']).'</td></tr>';
		echo ($player['debt']) ? '<tr><td class="menu_stats" align="right">'.$str['debt'].':</td><td class="menu_stats">'.$currency.' '.nummertje($player['debt']).'</td></tr>' : "";
		echo '<tr><td class="menu_stats" align="right">'.$str['bitches'].':</td><td class="menu_stats">'.$player['bitches'].'</td></tr>';
		echo '<tr><td class="menu_stats" align="right">'.$str['life'].':</td><td class="menu_stats">'.$player['life'].'%</td></tr>';
		echo '<tr><td class="menu_stats" align="right">'.$str['space'].':</td><td class="menu_stats">'.$player['space'].'</td></tr>';
		echo '<tr><td class="menu_stats" align="right">'.$str['guns'].':</td><td class="menu_stats">' . array_sum($player['guns']).'</td></tr>';
		echo '</table>';
	}

	else if ($side == "left")
	{
		// location menu table
		echo '<table border="1" cellpadding="2" cellspacing="0" width="200">'.EOL;
		echo '<tr><td class="menuheads">'.ucfirst($str['goto']).'</td></tr>'.EOL;

		while (list($key, $val) = each($places))
		{
			if ($nietgotoinmenu == $key)
			{
				echo '<tr><td class="menu_locations locationisthislocation">'.$val.'</td></tr>'.EOL;
			}
			else
			{
				echo '<tr><td class="menu_locations"><a href="?l='.$key.'">'.$val.'</a></td></tr>'.EOL;
			}
		}

		echo "<tr><td><br></td></tr>\n";

		echo "<tr><td><a href=\"?action=hiscore\" target=_blank>hi-scores</a></td></tr>".EOL;
		echo "<tr><td><a href=\"?action=od&s=0\">".$str['od']."</a></td></tr>".EOL;
		echo "<tr><td><a href=\"?logout=1\">".$str['logout']."</a></td></tr>".EOL;

		echo "</table>";
	}
}

function nummertje( $nummer )
{
	return number_format($nummer, 0, ".", ",");
}

function print_css( )
{
	echo '<style>'.EOL;
	// Make this a stylesheet
	// You can use an external stylesheet, as follows
	//   @import url("path/to/stylesheet.css");
	
	echo 'BODY, TABLE'.EOL;
	echo '{'.EOL;
	echo '	font-family:		Verdana, Arial;'.EOL;
	echo '	font-size:			11px;'.EOL;
	echo '	color:				#000000;'.EOL;
	echo '	cursor:				default;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo 'BODY'.EOL;
	echo '{'.EOL;
	echo '	margin:				3px;'.EOL;
	echo '	background-color:	#eeeeee;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo 'A'.EOL;
	echo '{'.EOL;
	echo '	text-decoration:	none;'.EOL;
	echo '	color:				#444444;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo 'A:hover'.EOL;
	echo '{'.EOL;
	echo '	text-decoration:	none;'.EOL;
	echo '	color:				#000000;'.EOL;
	echo '	font-weight:		bold;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo 'PRE'.EOL;
	echo '{'.EOL;
	echo '	font-family:		Courier New;'.EOL;
	echo '	font-size:			10pt;'.EOL;
	echo '	color:				#000000;'.EOL;
	echo '	background-color:	#ffffff;'.EOL;
	echo '	border:				solid 1px #000000;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo 'INPUT'.EOL;
	echo '{'.EOL;
	echo '	font-family:		Verdana, Arial;'.EOL;
	echo '	font-size:			11px;'.EOL;
	echo '	padding:			2px;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo '.onthemove'.EOL;
	echo '{'.EOL;
	echo '	font-size:			40px;'.EOL;
	echo '	color:				#000000;'.EOL;
	echo '	font-weight:		bold;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo '.location'.EOL;
	echo '{'.EOL;
	echo '	font-size:			25px;'.EOL;
	echo '	font-weight:		bold;'.EOL;
	echo '	text-align:			center;'.EOL;
	echo '	padding-bottom:		10px;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo '.drugsextremes'.EOL;
	echo '{'.EOL;
	echo '	font-weight:		bold;'.EOL;
	echo '	color:				lime;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo '.tospecial'.EOL;
	echo '{'.EOL;
	echo '	color:				blue;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo '.menuheads'.EOL;
	echo '{'.EOL;
	echo '	font-size:			16px;'.EOL;
	echo '	font-weight:		bold;'.EOL;
	echo '	text-align:			center;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo '.menu_locations'.EOL;
	echo '{'.EOL;
	echo '	'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo '.locationisthislocation'.EOL;
	echo '{'.EOL;
	echo '	font-weight:		bold;'.EOL;
	echo '	padding-left:		8px;'.EOL;
	echo '	color:				#ff0000;'.EOL;
	echo '}'.EOL;
	echo ''.EOL;
	echo '.menu_stats'.EOL;
	echo '{'.EOL;
	echo '	'.EOL;
	echo '}'.EOL;
	
	echo '</style>'.EOL;
}

?>