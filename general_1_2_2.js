/**
 * General JS library
 * 
 * CHANGELOG
 * =================
 * DATE				VERSION			AUTHOR					DESCRIPTION
 * 2007-08-31		1.2.1			Rudie Dirkx				- Added function: getPosition()
 * 2007-09-13		1.2.2			Rudie Dirkx				- Removed functions: empty(), isset()
 *															- Added function: getFormVars()
 * 
 */

function $( f_src )
{
	if ( 'object' != typeof f_src )
	{
		f_src = document.getElementById(f_src);
	}
	return f_src;
}

function $F( f_src )
{
	f_src = $(f_src);

	if ( !f_src )
	{
		return false;
	}

	switch ( f_src.nodeName.toLowerCase() )
	{
		case "input":
			switch ( f_src.type )
			{
				case "checkbox":
					return f_src.checked ? f_src.value : false;
				break;

				case "radio":
					return f_src.value
				break;

				default:
					return f_src.value;
				break;
			}
		break;

		default:
			return f_src.value;
		break;
	}

	return false;
}

function setCookie( f_szName, f_szValue )
{
	document.cookie = f_szName + '=' + f_szValue;
}

function addEventHandler( a, b, c, d )
{
	if ( a.addEventListener )
	{
		a.addEventListener( b, c, !!d );
	}
	else if ( a.attachEvent )
	{
		a.attachEvent( 'on'+b, c );
	}
	else
	{
		a['on'+b] = c;
	}
}

function getPosition( f_obj )
{
	var curleft = curtop = 0;
	if (f_obj.offsetParent)
	{
		curleft = f_obj.offsetLeft
		curtop = f_obj.offsetTop
		while (f_obj = f_obj.offsetParent)
		{
			curleft += f_obj.offsetLeft
			curtop += f_obj.offsetTop
		}
	}
	return [curleft,curtop];
}

function getFormVars( f_objForm )
{
	f = $(f_objForm);
	v = "";
	for ( i=0; i<f.elements.length; i++ )
	{
		e = f.elements[i];
		// Special handling for checkboxes (we need an array of selected checkboxes..)!
		if ( !e.name || ( e.type == 'checkbox' && !e.checked ) ) {
			continue;
		}
		v += '&' + e.name + '=' + e.value;
	}
	return v.substr(1);
}