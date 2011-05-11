// Settings
var imagedir = "images/"

var passage;
var countSecs;
var i;
var beginTijd;
var eindTijd;
var raceTijd;
var startok;
var letzteRunde;
var TotaleTijd;
var rondes;
var crash = false;

function checkPassage() {
	passage++;
}

function getTime() {
	return (new Date()).getTime();
}

function Stop() {
	// Didnt pass any checkpoints yet, so wrong direction
	if ( passage == 0 ) {
//		$('stoplicht_etc').src="./images/wrong_dir.gif"
		clearTimeout(HETlicht);
	}
	// Didnt pass all checkpoints yet!
	else if ( passage < 4 ) {
		$('stoplicht_etc').src = imagedir+"complete.gif";
	}
	// Got all passages
	else if ( !crash ) {
		passage = 0;

		eindTijd = getTime()
		raceTijd = (eindTijd - beginTijd)/1000

		$('lastlap').value = raceTijd

		if ( toFloat($('frm_lastlap').value) <= toFloat($('frm_hideme').value) ) {
			$('bestlap').value = $('lastlap').value;
			$('hideme').value = $('lastlap').value;
			bestlap = $('hideme').value
		}
		if ( toFloat($('bestlap').value) <= 0 ) {
			$('hideme').value = "";
			$('bestlap').value = "";
			$('lastlap').value = "";
			bestlap = "";
		}
		countSecs=0
		i=0
		timerID=0
		HETlicht=0
		startok=0
		beginTijd = 0
		eindTijd = 0
		raceTijd = 0
	}
}

function ValseStartTest( )
{
	// Didnt have green lights yet, so false start!
	if (startok==0)
	{
		// show false-start image
		$('stoplicht_etc').src="./images/valse_start.gif"
		// reset
		ResetRound( )
	}
}

function Wamba( )
{
	$('stoplicht_etc').src="./images/licht_rood.gif";
	ResetRound();
	setTimeout(Ampelgeel,2000);
}

function Ampelgeel( )
{
	$('stoplicht_etc').src="./images/licht_geel.gif";
	setTimeout(Ampelgroen,2500);
}

function Ampelgroen( )
{
	startok = 1;
	$('stoplicht_etc').src="./images/licht_groen.gif";
	Start();
}

function Start( )
{
	nu = new Date();
	if ( beginTijd == 0 )
	{
		beginTijd = nu.getTime();
	}
	countSecs += 0.1
//	$('thislap').value = Math.round((nu.getTime() - beginTijd) / 1000, 1);
	$('thislap').value = Math.round(countSecs*10)/10;
	if ( !crash )
	{
		setTimeout(Start, 100);
	}
}

function Out()
{
	// If race was started, this is a crash
	if ( 0 < beginTijd )
	{
		crash = true
		$('stoplicht_etc').src="./images/crash.gif"
		ResetRound()
	}
	// If it didnt start yet, ignore it
}

function SetImage( image )
{
	// get image path
	imagepath = imagedir + image + '.gif'
	// set it
	$('stoplicht_etc').src = imagepath
}

function ResetRound( )
{
	$('thislap').value=""
	passage=0
	countSecs=0
	i=0
	beginTijd = 0
	eindTijd = 0
	raceTijd = 0
	startok=0
	letzteRunde=0
	TotaleTijd=0
	rondes=0
	crash = false
//	clearTimeout(timerID)
//	clearTimeout(HETlicht)
}