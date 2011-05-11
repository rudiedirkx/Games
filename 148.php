<?php

$arrHints = array(
	'The Brit lives in a red house.',
	'The Swede keeps dogs as pets.',
	'The Dane drinks tea.',
	'The Green house is next to, and on the left of the White house.',
	'The owner of the Green house drinks coffee.',
	'The person who smokes Pall Mall rears birds.',
	'The owner of the Yellow house smokes Dunhill.',
	'The man living in the centre house drinks milk.',
	'The Norwegian lives in the first house.',
	'The man who smokes Blends lives next to the one who keeps cats.',
	'The man who keeps horses lives next to the man who smokes Dunhill.',
	'The man who smokes Blue Master drinks beer.',
	'The German smokes Prince.',
	'The Norwegian lives next to the blue house.',
	'The man who smokes Blends has a neighbour who drinks water.',
);

$arrHouses			= array('# 1', '# 2', '# 3', '# 4', '# 5');
$arrColors			= array('Blue', 'Green', 'Red', 'White', 'Yellow');
$arrNationalities	= array('Brit', 'Dane', 'German', 'Norwegian', 'Swede');
$arrBeverages		= array('Beer', 'Coffee', 'Milk', 'Tea', 'Water');
$arrSmokes			= array('Blends', 'BlueMaster', 'Dunhill', 'PallMall', 'Prince');
$arrPets			= array('Birds', 'Cats', 'Dogs', 'Fish', 'Horses');

?>
<html>

<head>
<title></title>
<style type="text/css">
* {
	margin				: 0;
	padding				: 0;
}
body {
	padding				: 10px;
}
body, table {
	font-family			: arial, verdana;
	font-size			: 11px;
}
h1 {
	display				: block;
	margin				: 10px;
	font-size			: 30px;
}
table {
	float				: left;
}
table td,
table th {
	padding				: 3px;
}
table select {
	width				: 100%;
}
#hints {
	float				: right;
	padding				: 10px;
	border				: dotted 1px black;
	background-color	: #eee;
	width				: 35%;
}
#hints legend {
	margin-left			: 25px;
	font-size			: 20px;
	font-weight			: bold;
}
#hints ol {
	margin-left			: 25px;
}
#hints ol li {
	cursor				: pointer;
	color				: #000;
}
#hints ol li.done {
	color				: #ccc;
}
</style>
<script type="text/javascript" src="http://jouwmoeder.nl/js/general_1_2_6.js"></script>
<script type="text/javascript">
<!--//
var _F;
addEventHandler(window, 'load', function() {
	// Hints enabling/disabling
	foreach ( $('hints').getElementsByTagName('li'), function(k, v) {
		v.onclick = function() {
			this.className = 'done' != this.className ? 'done' : '';
		}
	});
	// Assign form and reset selectboxes
	_F = document.forms[0];
	_F.reset();
	// Asign names to selectboxes
	foreach ( $('_tb').rows, function(k, row) {
		foreach ( row.cells, function(k, sb) {
			
		});
	});
});
//-->
</script>
</head>

<body>
<h1 align="center">Whose pet is the fish?</h1>

<form method="get">
<table border="1" cellspacing="0">
<thead>
	<tr><th>House</th><th><?php echo implode('</th><th>', $arrHouses); ?></th></tr>
</thead>
<tbody id="_tb">
	<tr><th>Color</th><?php echo str_repeat('<td><select name="color[]"><option>--</option><option>'.implode('</option><option>', $arrColors).'</option></select></td>', 5); ?></tr>
	<tr><th>Nationality</th><?php echo str_repeat('<td><select name="nationality[]"><option>--</option><option>'.implode('</option><option>', $arrNationalities).'</option></select></td>', 5); ?></tr>
	<tr><th>Beverage</th><?php echo str_repeat('<td><select name="beverage[]"><option>--</option><option>'.implode('</option><option>', $arrBeverages).'</option></select></td>', 5); ?></tr>
	<tr><th>Smoke</th><?php echo str_repeat('<td><select name="smoke[]"><option>--</option><option>'.implode('</option><option>', $arrSmokes).'</option></select></td>', 5); ?></tr>
	<tr><th>Pet</th><?php echo str_repeat('<td><select name="pet[]"><option>--</option><option>'.implode('</option><option>', $arrPets).'</option></select></td>', 5); ?></tr>
</tbody>
<tfoot>
	<tr><td colspan="6" align="center"><a href="#" onclick="_F.reset();return false;">Reset</a><!-- | <a href="#" onclick="_F.submit();return false;">Check</a>--></td></tr>
</tfoot>
</table>
</form>

<fieldset id="hints">
<legend>Hints</legend>
<ol>
<?php

foreach ( $arrHints AS $szHint )
{
	echo '<li>'.$szHint.'</li>';
}

?>
</ol>
</fieldset>

<!-- The German lives in the 4th house, which is Green. He smokes Prince, drinks Coffee and has a Fish. -->
<body>

</html>