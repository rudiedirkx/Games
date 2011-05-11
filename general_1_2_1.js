/**
 * General JS library
 * 
 * CHANGELOG
 * =================
 * DATE				VERSION			AUTHOR					DESCRIPTION
 * 2007-08-31		1.2.1			Rudie Dirkx				- Added the getPosition() function
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

				case "text":
				case "hidden":
				case "password":
				default:
					return f_src.value;
				break;
			}
		break;

		case "textarea":
			return f_src.value;
		break;

		case "select":
			return f_src.value;
		break;
	}

	return false;
}

function setCookie( f_szName, f_szValue )
{
	document.cookie = f_szName + '=' + f_szValue;
}

function isset( f_mixedVar )
{
	return "undefined" != typeof f_mixedVar;
}

function empty( f_mixedVar )
{
	return !isset(f_mixedVar) || !f_mixedVar;
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