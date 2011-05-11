function $( f_mixedSource )
{
	if ( 'object' != typeof f_mixedSource ) f_mixedSource = document.getElementById(f_mixedSource);

	return f_mixedSource;
}

function $F( f_mixedSource )
{
	objSource = $(f_mixedSource);

	if ( !objSource ) return false;

	switch ( objSource.nodeName.toLowerCase() )
	{
		case "input":
			switch ( objSource.type )
			{
				case "checkbox":
					return objSource.checked ? objSource.value : false;
				break;

				case "radio":
					return objSource.value
				break;

				case "text":
				case "hidden":
				case "password":
				default:
					return objSource.value;
				break;
			}
		break;

		case "textarea":
			return objSource.value;
		break;

		case "select":
			return objSource.options[objSource.selectedIndex].value;
		break;
	}

	return false;
}

function setCookie( f_szName, f_szValue )
{
	document.cookie = f_szName + '=' + f_szValue;
}

function empty( f_mixedVar )
{
	return !f_mixedVar;
}

function isset( f_mixedVar )
{
	return ("undefined" == typeof f_mixedVar);
}

function addEventHandler( a, b, c, d )
{
	if ( a.addEventListener )	a.addEventListener( b, c, !!d );
	else if ( a.attachEvent )	a.attachEvent( 'on'+b, c );
	else						a['on'+b] = c;
}