function Track_Beam(direction,y,x,field)
{
//	alert('searching direction...');
	if (direction == "naarrechts")
	{	// plus (x)
	//	alert('searching coordinates ('+y+','+x+')...');
		var as = atom[x][y];
		if (as)
			newcolor="#aaaaaa";
		else if (y+1 <= sides && atom[x][y+1])
			newcolor="#ffffff";
		else if (y-1 >= 1 && atom[x][y-1])
			newcolor="#ffffff";
		else
			Track_Beam();
		field.bgColor = newcolor;
	}
	else if (direction == "naaronder")
	{	// plus (y)
		
	}
	else if (direction == "naarlinks")
	{	// min (x)
		
	}
	else if (direction == "naarboven")
	{	// min (y)
		
	}
}


function Change_FieldColor(field)
{
	fieldcolor=field.bgColor;
//	alert(fieldcolor);
	if (fieldcolor == "#aaaaaa")
		newcolor = "#ff0000";
	else if (fieldcolor == "#ff0000")
		newcolor = "#22ff00";
	else if (fieldcolor == "#22ff00")
		newcolor = "#aaaaaa";
	field.bgColor = newcolor;
}