// Settings
var imagedir = "images/";

var passage;
var i;
var countdownTimer;
var beginTijd;
var clockTimer;
var eindTijd;
var raceTijd;
var startok;
var crash = false;

function CheckPassage()
{
	console.log('CheckPassage');

	passage++;
}

function getTime()
{
	return Date.now();
}

function Stop() {
	console.log('Stop');

	// Didnt pass any checkpoints yet, so wrong direction
	if ( passage == 0 ) {
		SetImage("wrong_dir");
		ResetRound();
	}
	// Didnt pass all checkpoints yet!
	else if ( passage < 4 ) {
		SetImage("complete");
		ResetRound();
	}
	// Got all passages
	else if ( !crash ) {
		passage = 0;

		var racing = getTime() - beginTijd;
		SetTime('lastlap', racing);

		if ( parseFloat($('bestlap').value) == 0 || parseFloat($('lastlap').value) <= parseFloat($('bestlap').value) ) {
			SetTime('bestlap', racing);
		}

		i=0
		startok=0
		beginTijd = 0
		eindTijd = 0
		raceTijd = 0
	}
}

function ValseStartTest()
{
	console.log('ValseStartTest');

	// Didnt have green lights yet, so false start!
	if (startok==0)
	{
		// show false-start image
		SetImage("valse_start");
		// reset
		ResetRound()
	}
}

function Wamba()
{
	console.log('Wamba');

	SetImage("licht_rood");
	ResetRound();
	countdownTimer = setTimeout(Ampelgeel,1000);
}

function Ampelgeel()
{
	console.log('Ampelgeel');

	SetImage("licht_geel");
	countdownTimer = setTimeout(Ampelgroen,1000);
}

function Ampelgroen()
{
	console.log('Ampelgroen');

	startok = 1;
	SetImage("licht_groen");
	Start();
}

function SetTime(id, time)
{
	time = Math.round(time / 1000 * 10) / 10;
	$(id).value = time + (time == parseInt(time) ? '.0' : '');
}

function Clock()
{
	if ( beginTijd > 0 ) {
		SetTime('thislap', getTime() - beginTijd);
	}
}

function Start()
{
	console.log('Start');

	beginTijd = getTime();

	clearTimeout(clockTimer);
	clockTimer = setInterval(Clock, 50);
}

function Out()
{
	console.log('Out');
	return;

	// If race was started, this is a crash
	if ( 0 < beginTijd )
	{
		crash = true
		SetImage("crash");
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

function ResetRound()
{
	console.log('ResetRound');

	$('thislap').value = "0.0"

	clearTimeout(countdownTimer);
	clearTimeout(clockTimer);

	passage=0
	i=0
	beginTijd = 0
	eindTijd = 0
	raceTijd = 0
	startok=0
	crash = false
}
