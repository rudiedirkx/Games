<html>

<head>
<title>Tetris</title>

<style type="text/css">
<!--
#blockb0 {position:absolute; left:0; top:0; z-index:1;}
#blockb1 {position:absolute; left:0; top:0; z-index:2;}
#block00 {position:absolute; left:0; top:0; z-index:3;}
#block01 {position:absolute; left:0; top:0; z-index:4;}

#block1 {position:absolute; left:0; top:0; z-index:5;}
#block2 {position:absolute; left:0; top:0; z-index:5;}
#block3 {position:absolute; left:0; top:0; z-index:5;}
#block4 {position:absolute; left:0; top:0; z-index:5;}

#blockx0 {position:absolute; left:0; top:0; z-index:6;}
#blockx1 {position:absolute; left:0; top:0; z-index:6;}
#blockx2 {position:absolute; left:0; top:0; z-index:6;}
#blockx3 {position:absolute; left:0; top:0; z-index:6;}
#blockx4 {position:absolute; left:0; top:0; z-index:6;}
#blockx5 {position:absolute; left:0; top:0; z-index:6;}
#blockx6 {position:absolute; left:0; top:0; z-index:6;}
#blockx7 {position:absolute; left:0; top:0; z-index:6;}

#score    {position:absolute; left:0; top:0; z-index:1;}
#previewx {position:absolute; left:0; top:0; z-index:1;}
#preview  {position:absolute; left:0; top:0; z-index:2;}
#blockp1  {position:absolute; left:0; top:0; z-index:3;}
#blockp2  {position:absolute; left:0; top:0; z-index:3;}
#blockp3  {position:absolute; left:0; top:0; z-index:3;}
#blockp4  {position:absolute; left:0; top:0; z-index:3;}

#blockxx {position:absolute; left:0; top:0; z-index:7;}

p.score1   {text-decoration:underline; font-size:10px; font-weight:bold; color:#00FF00; font-family:"verdana,arial,helvetica";}
p.score2   {text-decoration:underline; font-size:11px; font-weight:bold; color:#FF00FF; font-family:"verdana,arial,helvetica";}
p.score3   {text-decoration:underline; font-size:12px; font-weight:bold; color:#FFFF00; font-family:"verdana,arial,helvetica";}
p.score4   {text-decoration:underline; font-size:14px; font-weight:bold; color:#FF0000; font-family:"verdana,arial,helvetica";}
p.winner   {font-size:22px; font-weight:bold; color:#FF0000; font-family:"times,arial,helvetica";}
p.gameover {font-size:18px; font-weight:bold; color:#FFFFFF; font-family:"times,arial,helvetica";}
p.level    {font-size:12px; font-weight:bold; color:#000000; font-family:"verdana,arial,helvetica";}
p.info     {font-size:12px; color:#000000; font-family:"verdana,arial,times,helvetica";}
p.tableb   {font-size:12px; color:#FFFFFF; font-family:"arial,verdana,times,helvetica";}
p.tablet   {font-size:17px; color:#FFFF00; font-family:"verdana,arial,times,helvetica";}
-->
</style>

<script type="text/javascript">
<!--
var game_name = "Tetris";

var key_up = 73;
var key_UP = 38;
var key_down = 75;
var key_DOWN = 40;
var key_left = 74;
var key_LEFT = 37;
var key_right = 76;
var key_RIGHT = 39;
var key_pause = 80;
var key_PAUSE = 32;

var block_size = 16;
var block_base_width = 400;
var block_base_height = 400;

var block_base_x = 0;
var block_base_y = 0;
var preview_base_x = 0;
var preview_base_y = 0;
var game_tag_offset = 0;
var game_tag_step = 0;
var tag_layer_offset = 0;

var game_tag_width = 120;
var game_tag_height = 40;
var block_table_gap = 30;
var block_preview_gap = 10;
var block_preview_x = 3;
var block_preview_y = 4;

var block_id = "block";

var block_matrix_unit = 8;
var block_matrix = [
	[0,0,1,0,0,1,1,1,0,0,1,0,0,1,1,1,0,0,1,0,0,1,1,1,0,0,1,0,0,1,1,1],
	[1,0,2,0,0,1,1,1,0,0,0,1,1,1,1,2,1,0,2,0,0,1,1,1,0,0,0,1,1,1,1,2],
	[0,0,1,0,1,1,2,1,1,0,0,1,1,1,0,2,0,0,1,0,1,1,2,1,1,0,0,1,1,1,0,2],
	[0,0,0,1,0,2,1,2,2,0,0,1,1,1,2,1,0,0,1,0,1,1,1,2,0,0,1,0,2,0,0,1],
	[1,0,1,1,0,2,1,2,0,0,1,0,2,0,2,1,0,0,1,0,0,1,0,2,0,0,0,1,1,1,2,1],
	[0,0,0,1,0,2,0,3,0,0,1,0,2,0,3,0,0,0,0,1,0,2,0,3,0,0,1,0,2,0,3,0],
	[0,0,1,0,2,0,1,1,0,0,0,1,1,1,0,2,1,0,0,1,1,1,2,1,1,0,0,1,1,1,1,2]
];
var block_unit_dim = [
	[2,2,2,2,2,2,2,2],
	[3,2,2,3,3,2,2,3],
	[3,2,2,3,3,2,2,3],
	[2,3,3,2,2,3,3,2],
	[2,3,3,2,2,3,3,2],
	[1,4,4,1,1,4,4,1],
	[3,2,2,3,3,2,2,3]
];
var block_unit_width = 0;
var block_unit_height = 0;

var block_images = [
	new Image(16,16),
	new Image(16,16),
	new Image(16,16),
	new Image(16,16),
	new Image(16,16),
	new Image(16,16),
	new Image(16,16)
];
block_images[0].src = "block0.gif";
block_images[1].src = "block1.gif";
block_images[2].src = "block2.gif";
block_images[3].src = "block3.gif";
block_images[4].src = "block4.gif";
block_images[5].src = "block5.gif";
block_images[6].src = "block6.gif";

var block1_x = 0;
var block1_y = 0;
var block2_x = 0;
var block2_y = 0;
var block3_x = 0;
var block3_y = 0;
var block4_x = 0;
var block4_y = 0;

var block_type = 7;
var block_component = 4;

var block_next = Math.floor(Math.random() * block_type);
var block_current = 0;
var block_direction = 0;

var block_timer_id = 0;
var block_timer = [480,360,240,120,60];
var movement_timer = 30;

var block_table_width = 10;
var block_table_height = 20;

var block_shown = false;
var block_position_x = 4;
var block_position_y = 0;

function one_column() {
	for (var i = 0; i < block_table_height; i++) {
		this[i] = false;
	}
}
function one_table() {
	for (var i = 0; i < block_table_width; i++) {
		this[i] = new one_column();
	}
}
var block_position = new one_table();

var block_content = "";
var block_content1 = "";
var block_content2 = "";

var game_finished = true;
var game_pause = false;
var game_winner = false;
var game_winner_tag = '<table width="' + game_tag_width + '" height="' + game_tag_height + '" bgcolor="#000000" cellpadding="0" cellspacing="0" border="1"><tr align="center" valign="middle"><td><p class="winner">Game over</p></td></tr></table>';
var game_over_tag = '<table width="' + game_tag_width + '" height="' + game_tag_height + '" bgcolor="#000000" cellpadding="0" cellspacing="0" border="1"><tr align="center" valign="middle"><td><p class="gameover">Game Over</p></td></tr></table>';

var number_timer = 0;

var score_shown = false;
var score_index = 0;
var score_max_index = 7;
var score_tag = [true,true,true,true,true,true,true,true];
var score_step = [-4,-2,-1,-1];
var score_grip = [10,50,100,300];
var score_content1 = '<p class="score';
var score_content2 = '">';
var score_content3 = '</p>';

var high_score = 0;
var game_score = 0;
var game_lines = 0;
var game_level = 0;
var game_max_level = 4;
var game_level_lines = [50,40,30,20,10];
var game_level_tag1 = '<table width="' + game_tag_width + '" height="' + game_tag_height + '" cellpadding="0" cellspacing="0" border="1"><tr align="center" valign="middle"><td><p class="level">Level ';
var game_level_tag2 = '</p></td></tr></table>';

var table_background = new Image(16,16);
table_background.src = "blockb.gif";

var table_intro_dim = "";
var table_intro = [
	'"#000000"><tr align="center" valign="top"><td><br><br><p class="tablet">' + game_name + '</p><p class="tableb">Links: [J / Pijltje links]<br>Rechts: [L / Pijltje rechts]<br>Draaien: [I / Pijltje omhoog]<br>Vallen: [K / Pijltje omlaag]<br>Pauze: [P / SPACE]<br><br>Druk op een toets om te beginnen</p></td></tr></table>',
	'"#C0C0C0"><tr align="center" valign="top"><td><br><br><p class="tablet">' + game_name + '</p><p class="tableb">Je zit nu in level 2</p></td></tr></table>',
	'"#008080"><tr align="center" valign="top"><td><br><br><p class="tablet">' + game_name + '</p><p class="tableb">Je zit nu in level 3</p></td></tr></table>',
	'"#808000"><tr align="center" valign="top"><td><br><br><p class="tablet">' + game_name + '</p><p class="tableb">Je zit nu in level 4</p></td></tr></table>',
	'"#800000"><tr align="center" valign="top"><td><br><br><p class="tablet">' + game_name + '</p><p class="tableb"><br><br><br>Dit is het laatste level!</p></td></tr></table>'
];

var is_NN4 = (document.layers);
var is_IE4 = (document.all);

function G(id) {
	return document.getElementById(id);
}

function Hide_Tag() {
	G('blockxx').style.pixelTop = -game_tag_height;
}

function Show_Level_Tag() {
	var next_step = 0;
	if ((game_tag_step > 0) && (!game_finished)) {
		next_step = Math.ceil(game_tag_step/20);
		G('blockxx').style.pixelTop += next_step;
		game_tag_step -= next_step;
		setTimeout(Show_Level_Tag, movement_timer);
	}
	else {
		Hide_Tag();
	}
}

function Show_Level() {
	Clear_Table();
	G('block00').innerHTML = table_intro_dim + table_intro[game_level];
	if (game_level > 0) {
		G('blockxx').innerHTML = game_level_tag1 + (game_level+1) + game_level_tag2;
		G('blockxx').style.pixelTop = block_base_y;
		game_tag_step = game_tag_offset;
		setTimeout(Show_Level_Tag, movement_timer);
	}
}

function Check_Position(block_x,block_y) {
	return (
		(!block_position[block_x + block1_x][block_y + block1_y]) &&
		(!block_position[block_x + block2_x][block_y + block2_y]) &&
		(!block_position[block_x + block3_x][block_y + block3_y]) &&
		(!block_position[block_x + block4_x][block_y + block4_y])
	);
}

function Check_Rotate() {
	var block_next_x = block_position_x;
	var block_next_dir = (block_direction < 3)?block_direction+1:0;
	Set_Offset(block_current,block_next_dir);
	if (block_position_y < (block_table_height - block_unit_height)) {
		while ((block_next_x + block_unit_width - 1) >= block_table_width) {
			block_next_x--;
		}
		if (Check_Position(block_next_x, block_position_y)) {
			block_direction = block_next_dir;
			return true;
		}
		Set_Offset(block_current,block_direction);
		return false;
	}
	Set_Offset(block_current,block_direction);
	return false;
}

function Check_Direction(x_offset,y_offset) {
	var block_next_x = block_position_x + x_offset;
	var block_next_y = block_position_y + y_offset;
	if ((block_next_x < 0) || (block_next_x > (block_table_width - block_unit_width)) || (block_next_y > (block_table_height - block_unit_height))) {
		return false
	}
	return Check_Position(block_next_x, block_next_y);
}

function Set_Offset(block_num, block_dir) {
	var block_unit_offset = block_dir * 2;
	var block_matrix_offset = block_dir * block_matrix_unit;
	block_unit_width = block_unit_dim[block_num][block_unit_offset+0];
	block_unit_height = block_unit_dim[block_num][block_unit_offset+1];
	block1_x = block_matrix[block_num][block_matrix_offset+0];
	block1_y = block_matrix[block_num][block_matrix_offset+1];
	block2_x = block_matrix[block_num][block_matrix_offset+2];
	block2_y = block_matrix[block_num][block_matrix_offset+3];
	block3_x = block_matrix[block_num][block_matrix_offset+4];
	block3_y = block_matrix[block_num][block_matrix_offset+5];
	block4_x = block_matrix[block_num][block_matrix_offset+6];
	block4_y = block_matrix[block_num][block_matrix_offset+7];
}

function Get_Content(block_num) {
	return block_content1 + block_images[block_num].src + block_content2;
}

function Drop_Block() {
	var drop_step = 1;
	block_shown = false;  
	clearInterval(block_timer_id);
	while (Check_Direction(0,drop_step)) {
		drop_step++;
	}
	Move_Block(0,drop_step-1);
	setTimeout(Check_Block, 1); // setTimeout so that block's final postion can be seen
}

function Move_Block(x_offset,y_offset) {
	var block_offset_x = x_offset * block_size;
	var block_offset_y = y_offset * block_size;
	block_position_x += x_offset;
	block_position_y += y_offset;
	G('block1').style.pixelLeft += block_offset_x;
	G('block1').style.pixelTop += block_offset_y;
	G('block2').style.pixelLeft += block_offset_x;
	G('block2').style.pixelTop += block_offset_y;
	G('block3').style.pixelLeft += block_offset_x;
	G('block3').style.pixelTop += block_offset_y;
	G('block4').style.pixelLeft += block_offset_x;
	G('block4').style.pixelTop += block_offset_y;
	G('block00').innerHTML = ""; // remove inittext
}

function Show_Block() {
	block_position_x = 4;
	block_position_y = 0;
	block_direction = 0;

	block_current = block_next;
	block_next = Math.floor(Math.random() * block_type);

	Set_Offset(block_current,block_direction);
	block_content = Get_Content(block_current);

	G('block1').innerHTML = block_content;
	G('block1').style.pixelLeft = (block1_x + block_position_x) * block_size + block_base_x;
	G('block1').style.pixelTop = block1_y * block_size + block_base_y;

	G('block2').innerHTML = block_content;
	G('block2').style.pixelLeft = (block2_x + block_position_x) * block_size + block_base_x;
	G('block2').style.pixelTop = block2_y * block_size + block_base_y;

	G('block3').innerHTML = block_content;
	G('block3').style.pixelLeft = (block3_x + block_position_x) * block_size + block_base_x;
	G('block3').style.pixelTop = block3_y * block_size + block_base_y;

	G('block4').innerHTML = block_content;
	G('block4').style.pixelLeft = (block4_x + block_position_x) * block_size + block_base_x;
	G('block4').style.pixelTop = block4_y * block_size + block_base_y;

	if (block_next != block_current) {
		Set_Offset(block_next,block_direction);
		block_content = Get_Content(block_next);
	}

	G('blockp1').innerHTML = block_content;
	G('blockp1').style.pixelLeft = block1_x * block_size + preview_base_x;
	G('blockp1').style.pixelTop = block1_y * block_size + preview_base_y;

	G('blockp2').innerHTML = block_content;
	G('blockp2').style.pixelLeft = block2_x * block_size + preview_base_x;
	G('blockp2').style.pixelTop = block2_y * block_size + preview_base_y;

	G('blockp3').innerHTML = block_content;
	G('blockp3').style.pixelLeft = block3_x * block_size + preview_base_x;
	G('blockp3').style.pixelTop = block3_y * block_size + preview_base_y;

	G('blockp4').innerHTML = block_content;
	G('blockp4').style.pixelLeft = block4_x * block_size + preview_base_x;
	G('blockp4').style.pixelTop = block4_y * block_size + preview_base_y;
	  
	if (block_next != block_current) {
		Set_Offset(block_current,block_direction);
		block_content = Get_Content(block_current);
	}

	block_shown = true;
	if (Check_Direction(0,0)) {
		return true
	}
	return false;
}

function Mark_Block() {
  var this_offset_x = 0;
  var this_offset_y = 0;
  var this_block = null;

  block_shown = false;  

  for (var i = 1; i <= block_component; i++) {
    this_offset_x = block_position_x + eval(block_id + i + "_x");
    this_offset_y = block_position_y + eval(block_id + i + "_y");
  
    block_position[this_offset_x][this_offset_y] = true;
    document.images[this_offset_y*block_table_width+this_offset_x].src = block_images[block_current].src;

    this_block = G(block_id + i);

    this_block.style.pixelTop = -block_size; }
}

function Remove_Line(line_num) {
  var this_line = block_position_y + line_num;
  var upper_line = this_line - 1;
  var no_more_line = false;

  while ((upper_line >= 0) && (!no_more_line)) {
    no_more_line = true;

    for (var i = 0; i < block_table_width; i++) {
      if (block_position[i][upper_line]) {
        no_more_line = false;

        document.images[this_line*block_table_width+i].src = document.images[upper_line*block_table_width+i].src;
        block_position[i][this_line] = true;
        block_position[i][upper_line] = false; }
      else {
        document.images[this_line*block_table_width+i].src = table_background.src;
        block_position[i][this_line] = false; } }

    this_line--;
    upper_line--; }
}

function Show_Tag() {
	var next_step = 0;
	if (game_tag_step > 0) {
		next_step = Math.ceil(game_tag_step/10);
		G('blockxx').style.pixelTop += next_step;
		game_tag_step -= next_step;
		setTimeout(Show_Tag, movement_timer);
	}
	else {
		Event_Init();
	}
}

function Clear_Block() {
	G('block1').style.pixelTop = -block_size;
	G('block2').style.pixelTop = -block_size;
	G('block3').style.pixelTop = -block_size;
	G('block4').style.pixelTop = -block_size;
}

function Restart_Game() {
  high_score = (high_score < game_score)?game_score:high_score;

  game_score = 0;
  game_lines = 0;
  game_level = 0;
  game_finished = false;

  Hide_Tag();

  Clear_Block();
  Clear_Num();

  Show_Score();
  Show_Lines();
  Show_Level();

  Start_Block();
}

function Game_Over() {
  Event_Release();

  clearInterval(block_timer_id);

  if (game_winner)
    G('blockxx').innerHTML = game_winner_tag
  else
    G('blockxx').innerHTML = game_over_tag;

  G('blockxx').style.pixelTop = block_base_y;

  game_winner = false;
  game_pause = false;
  game_finished = true;

  game_tag_step = game_tag_offset;

  setTimeout(Show_Tag, movement_timer);
}

function Clear_Table() {
  for (var i = 0; i < block_table_height; i++)
    for (var j = 0; j < block_table_width; j++)
      if (block_position[j][i]) {
        document.images[i*block_table_width+j].src = table_background.src;
        block_position[j][i] = false; }
}

function Clear_Num() {
  var number_id = null;

  clearInterval(number_timer);

  for (var i = 0; i <= score_max_index; i++)
    if (!score_tag[i]) {
      number_id = G(block_id + "x" + i + "");

      number_id.style.pixelTop = -block_size;
      number_id.style.pixelLeft = 0;
      score_tag[i] = true; } 
}

function Move_Num() {
  var number_id = null;
  var score_found = 0;

  for (var i = 0; i <= score_max_index; i++)
    if (!score_tag[i]) {
      number_id = G(block_id + "x" + i + "");

      if (number_id.style.pixelTop > block_base_y) {
        number_id.style.pixelTop += score_step[number_id.lines-1];
        score_found++; }
      else {
        number_id.style.pixelTop = -block_size;
        number_id.style.pixelLeft = 0;
        score_tag[i] = true; } }

  if (score_found == 0) {
    clearInterval(number_timer);
    score_shown = false; }
}

function Show_Num(line_num, line_offset) {
  var number_id = null;
  var this_index = score_index;
  var this_offset = ((block1_x + block_position_x) > 0)?-1:0;
  var score_content = score_content1 + line_num + score_content2 + score_grip[line_num-1] + score_content3;

  while (!score_tag[score_index]) {
    score_index++;

    if (score_index > score_max_index)
      score_index = 0;

    if (score_index == this_index) {
      score_index++;
      if (score_index > score_max_index)
        score_index = 0;
      break; } }

  number_id = G(block_id + "x" + score_index + "");
  number_id.lines = line_num;
  score_tag[score_index] = false;
  
  number_id.style.pixelLeft = (block1_x + block_position_x + this_offset) * block_size + block_base_x;
  number_id.style.pixelTop = (block1_y + block_position_y + line_offset) * block_size + block_base_y;
  number_id.innerHTML = score_content;

  if (!score_shown) {
    score_shown = true;
    number_timer = setInterval(Move_Num, movement_timer); }
}

function Show_Score() {
	G('score').getElementsByTagName('form')[0].High_Score.value = high_score;
	G('score').getElementsByTagName('form')[0].Game_Score.value = game_score;
}

function Show_Lines() {
	if (!game_winner) {
		G('score').getElementsByTagName('form')[0].Lines_Remain.value = game_level_lines[game_level] - game_lines
	} else {
		G('score').getElementsByTagName('form')[0].Lines_Remain.value = game_lines;
	}
}

function Check_Line() {
  var line_done = false;
  var line_found = 0;
  var line_offset = 0;

  for (var i = 0; i < block_unit_height; i++) {
    line_done = true;

    for (var j = 0; j < block_table_width; j++)
      if (!block_position[j][block_position_y+i]) {
        line_done = false;
        break; }

    if (line_done) {
      if (line_found == 0)
        line_offset = i;

      line_found++;
      Remove_Line(i); } }

  if (line_found > 0) {
    Show_Num(line_found,line_offset);

    game_score += score_grip[line_found-1];
    Show_Score();

    game_lines += line_found;
    if ((game_lines >= game_level_lines[game_level]) && (!game_winner)) {
      game_lines -= game_level_lines[game_level];

      if (game_level < game_max_level) {
        game_level++;
        Show_Level(); }
      else
        game_winner = true; }

    Show_Lines(); }

}

function Check_Block() {
  Mark_Block();
  Check_Line();
  Start_Block();
}

function Mov_Block() {
  if (Check_Direction(0,1)) {
    block_position_y++;

    G('block1').style.pixelTop += block_size;
    G('block2').style.pixelTop += block_size;
    G('block3').style.pixelTop += block_size;
    G('block4').style.pixelTop += block_size; }
  else
    Check_Block();
}

function Resume_Game() {
  game_pause = false;
  block_timer_id = setInterval(Mov_Block, block_timer[game_level]);
  number_timer = setInterval(Move_Num, movement_timer);
}

function Pause_Game() {
  game_pause = true;
  clearInterval(block_timer_id);
  clearInterval(number_timer);
}

function Key_Down(e) {
	e = e || event || this.event;
	var key_code = e.keyCode;

	if (game_finished) {
		Restart_Game();
	}
	else {
		if (game_pause) {
			Resume_Game();
		}
		if ( key_code == key_pause || key_code == key_PAUSE ) {
			if (block_shown) {
				Pause_Game();
			}
		}
		if ( key_code == key_down || key_code == key_DOWN ) {
			if (block_shown) {
				Drop_Block();
			}
		}
		if ((key_code == key_up) || (key_code == key_UP)) {
			if (block_shown) {
				if (Check_Rotate()) {
					Rotate_Block();
				}
			}
		}
		if ( key_code == key_left || key_code == key_LEFT ) {
			if (block_shown) {
				if (Check_Direction(-1,0)) {
					Move_Block(-1,0);
				}
			}
		}
		if ( key_code == key_right || key_code == key_RIGHT ) {
			if (block_shown) {
				if (Check_Direction(1,0)) {
					Move_Block(1,0);
				}
			}
		}
	}
	return false;
}

function Rotate_Block() {
  while ((block_position_x + block_unit_width - 1) >= block_table_width) {
    block_position_x--; }

  G('block1').style.pixelLeft = (block1_x  + block_position_x) * block_size + block_base_x;
  G('block1').style.pixelTop = (block1_y + block_position_y) * block_size + block_base_y;

  G('block2').style.pixelLeft = (block2_x + block_position_x) * block_size + block_base_x;
  G('block2').style.pixelTop = (block2_y + block_position_y) * block_size + block_base_y;

  G('block3').style.pixelLeft = (block3_x + block_position_x) * block_size + block_base_x;
  G('block3').style.pixelTop = (block3_y + block_position_y) * block_size + block_base_y;

  G('block4').style.pixelLeft = (block4_x + block_position_x) * block_size + block_base_x;
  G('block4').style.pixelTop = (block4_y + block_position_y) * block_size + block_base_y;
}

function Start_Block() {
  if (Show_Block()) {
    clearInterval(block_timer_id);
    block_timer_id = setInterval(Mov_Block, block_timer[game_level]); }
  else
    Game_Over();
}

function Table_Init() {
  var layer_content = "";
  
  layer_content = '<table width="' + (block_table_width * block_size + block_preview_gap * 2) + '" height="' + (block_table_height * block_size + block_preview_gap * 2) + '" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" border="3"><tr><td>&nbsp;</td></tr></table>';
  G('blockb0').innerHTML = layer_content;

  layer_content = '<table width="' + (block_table_width * block_size) + '" height="' + (block_table_height * block_size) + '" bgcolor="#000000" cellpadding="0" cellspacing="0" border="0"><tr><td>&nbsp;</td></tr></table>';
  G('blockb1').innerHTML = layer_content;

  layer_content = '<table width="' + (block_table_width * block_size) + '" height="' + (block_table_height * block_size) + '" cellpadding="0" cellspacing="0" border="0">';
  for (var i = 0; i < block_table_height; i++) {
    layer_content += '<tr align="center" valign="middle">';
    for (var j = 0; j < block_table_width; j++)
      layer_content += '<td><img src = "blockb.gif" width="' + block_size + '" height="' + block_size + '" border="0"></td>';
    layer_content += '</tr>'; }
  layer_content += '</table>';

  G('block01').innerHTML = layer_content;

  layer_content = '<table width="' + (block_preview_x * block_size + block_preview_gap * 2) +'" height="' + (block_preview_y * block_size + block_preview_gap * 2) + '" bgcolor="#000000" cellpadding="0" cellspacing="0" border="2"><tr><td>&nbsp</td></tr></table>';
  G('previewx').innerHTML = layer_content;

  layer_content = '<table width="' + (block_preview_x * block_size) +'" height="' + (block_preview_y * block_size) + '" bgcolor="#000000" cellpadding="0" cellspacing="0" border="0"><tr><td>&nbsp</td></tr></table>';
  G('preview').innerHTML = layer_content;

  layer_content = '<p class="info"><form>' +
    '<nobr>High Score</nobr>' +
    '<input type="text" name="High_Score" maxlenght="6" size="6" value=0 onfocus="blur();"><br><br>' +
    '<nobr>Punten</nobr>' +
    '<input type="text" name="Game_Score" maxlenght="6" size="6" value=0 onfocus="blur();"><br><br>' +
    '<nobr>Rijen over</nobr>' +
    '<input type="text" name="Lines_Remain" maxlenght="6" size="6" value=0 onfocus="blur();">' +
    '</form></p>';
  G('score').innerHTML = layer_content;
}

function Define_Layer(Layer_ID, Layer_Left, Layer_Top, Layer_Width, Layer_Height) {
	var this_block = null;
	this_block = G(Layer_ID);
	this_block.style.pixelLeft = Layer_Left;
	this_block.style.pixelTop = Layer_Top;
	this_block.style.width = Layer_Width;
	this_block.style.height = Layer_Height;
	this_block.style.visibility = "visible";
}

function Layer_Init() {
  var this_block_id = "";

  // layers for falling blocks
  for (i = 1; i <= block_component; i++) {
    this_block_id = block_id + i + "";
    Define_Layer(this_block_id,0,-block_size,block_size,block_size); }

  // layers for preview blocks
  for (i = 1; i <= block_component; i++) {
    this_block_id = block_id + "p" + i + "";
    Define_Layer(this_block_id,0,-block_size,block_size,block_size); }

  // layers for score points
  for (i = 0; i <= score_max_index; i++) {
    this_block_id = block_id + "x" + i + "";
    Define_Layer(this_block_id,0,-block_size,block_size,block_size); }

  // layer for game tags
  Define_Layer("blockxx",tag_layer_offset,-game_tag_height,game_tag_width,game_tag_height);

  // layer for table outer background
  Define_Layer("blockb0",(block_base_x - block_preview_gap),(block_base_y - block_preview_gap),(block_table_width * block_size + block_preview_gap * 2),(block_table_height * block_size + block_preview_gap * 2));

  // layer for table inner background
  Define_Layer("blockb1",block_base_x,block_base_y,(block_table_width * block_size),(block_table_height * block_size));

  // layer for table intro
  Define_Layer("block00",block_base_x,block_base_y,(block_table_width * block_size),(block_table_height * block_size));

  // layer for table
  Define_Layer("block01",block_base_x,block_base_y,(block_table_width * block_size),(block_table_height * block_size));

  // layer for preview box background
  Define_Layer("previewx",(preview_base_x - block_preview_gap),(preview_base_y - block_preview_gap),(block_preview_x * block_size + block_preview_gap * 2),(block_preview_y * block_size + block_preview_gap * 2));

  // layer for preview box
  Define_Layer("preview",preview_base_x,preview_base_y,(block_preview_x * block_size),(block_preview_y * block_size));

  // layer for score board
  Define_Layer("score",(preview_base_x - block_preview_gap),(preview_base_y + block_preview_y * block_size + block_preview_gap + block_table_gap),1,1);
}

function Para_Init() {
  var block_window_width = document.body.clientWidth;
  var block_window_height = document.body.clientHeight;
  var block_scale = 1;
  var block_table_x_size = 0;
  var block_table_y_size = 0;

  block_size = 16;

  if ((block_window_width > block_base_width) || (block_window_height > block_base_height)) {
    block_scale = Math.min(block_window_width/block_base_width, block_window_height/block_base_height);
    block_scale = Math.max(block_scale, 1); }

  block_size = Math.floor(block_size * block_scale);

  block_table_x_size = block_preview_gap + block_table_width * block_size + block_preview_gap + block_table_gap + block_preview_gap + block_preview_x * block_size + block_preview_gap;
  block_table_y_size = block_preview_gap + block_table_height * block_size + block_preview_gap;
  
  block_window_width = Math.max(block_window_width, block_table_x_size);
  block_window_height = Math.max(block_window_height, block_table_y_size);

  block_base_x = Math.floor((block_window_width - block_table_x_size)/2) + block_preview_gap;
  block_base_y = Math.floor((block_window_height - block_table_y_size)/2) + block_preview_gap;

  preview_base_x = block_base_x + block_table_width * block_size + block_table_gap + block_preview_gap;
  preview_base_y = block_base_y + block_preview_gap;

  game_tag_offset = (block_table_height * block_size - game_tag_height)/2;

  tag_layer_offset = block_base_x + (block_table_width * block_size - game_tag_width)/2;

  block_content1 = '<table width="' + block_size + '" height="' + block_size + '" cellpadding="0" cellspacing="0" border="0"><tr align="center" valign="middle"><td><img src="';
  block_content2 = '" width="' + block_size + '" height="' + block_size + '" border="0"></td></tr></table>';

  table_intro_dim = '<table width="' + (block_table_width * block_size) + '" height="' + (block_table_height * block_size) + '" cellpadding="0" cellspacing="0" border="0" bgcolor=';
}

function Game_Init() {
	focus();
	Para_Init();
	Layer_Init();
	Table_Init();
	Event_Init();
	Show_Score();
	Show_Lines();
	Show_Level();
}

function Event_Release() {
  document.onkeydown = null;
}

function Event_Init() {
  document.onkeydown = Key_Down;
}

function Resizing_Table() {
    Para_Init();
    Layer_Init();
    Table_Init();
    Restart_Game();
    Pause_Game();
}

window.onresize = Resizing_Table;
//-->
</script>
</head>

<body bgcolor="#26CDFF" text="White" link="Gray" alink="Gray" vlink="Gray" onload="Game_Init();">
<div id="blockb0"></div><div id="blockb1"></div><div id="block00"></div><div id="block01"></div>
<div id="score"></div>
<div id="previewx"></div><div id="preview"></div>
<div id="block1"></div><div id="block2"></div><div id="block3"></div><div id="block4"></div>
<div id="blockp1"></div><div id="blockp2"></div><div id="blockp3"></div><div id="blockp4"></div>
<div id="blockx0"></div><div id="blockx1"></div><div id="blockx2"></div><div id="blockx3"></div><div id="blockx4"></div><div id="blockx5"></div><div id="blockx6"></div><div id="blockx7"></div>
<div id="blockxx"></div>

</body>

</html>
