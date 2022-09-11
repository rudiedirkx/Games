<p><b>WHAT TO DO</b></p>

<p>You must find all Atoms! The Atoms are hidden in the grey field.</p>

<? if (isset($ATOMS)): ?>
	<p><b>Atoms to find: <?= $ATOMS ?></b></p>
	<p>Playtime: <b id="playtime">-</b></p>
	<p>Score: <b id="score">-</b></p>
	<p>Hi-score: <b id="hiscore">-</b></p>
<? endif ?>

<p>You can fire beams that might tell you the location of the Atoms.</p>
<p>You do that by clicking on side cells (the lighter grey ones).</p>
<p>A beam turns before it hits an Atom.<br/>If you fire a beam from below and there is an Atom on the left somewhere, the beam will turn to the right:</p>
<p><img src="/images/blackbox_help_1.gif"></p>
<p><b>When the beam reaches another side cell, both cells are colored!</b></p>
<p>If it hits an atom its absorbed:</p>
<p><img src="/images/blackbox_help_2.gif"></p>
<p><b>The side cell (where the beam came from) is then GREY!</b></p>
<p>It's also possible that a beam makes a U-turn and gets right back where it came from.</p>
<p>Either it doesnt get the chance to enter the field (there's an atom right or left of where the beam enters)</p>
<p>or it must make a U-turn:</p>
<p><img src="/images/blackbox_help_3.gif"></p>
<p><b>The side cell is then WHITE!</b></p>
