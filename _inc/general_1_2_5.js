/**
 * General JS library
 * 
 * CHANGELOG
 * =================
 * DATE				VERSION			AUTHOR					DESCRIPTION
 * 2007-08-31		1.2.1			Rudie Dirkx				- Added function: getPosition()
 * 2007-09-13		1.2.2			Rudie Dirkx				- Removed functions: empty(), isset()
 *															- Added function: getFormVars()
 * 2007-10-22		1.2.4			Rudie Dirkx				- Updated function getFormVars(), it handles radiobuttons correctly now too
 *															- Added function postForm(), which uses getFormVars() to post a form with Ajax
 * 2007-11-09		1.2.5			Rudie Dirkx				- Updated function getFormVars() so it handles multiple selects now too
 *															- Added function foreach() to fake a PHP foreach construction
 * 
 */

function $( f_src ) {
	if ( 'object' != typeof f_src ) {
		f_src = document.getElementById(f_src);
	}
	return f_src;
}

function $F( f_src ) {
	f_src = $(f_src);
	if ( !f_src ) {
		return false;
	}
	switch ( f_src.nodeName.toLowerCase() ) {
		case "input":
			switch ( f_src.type ) {
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

function setCookie( f_szName, f_szValue ) {
	var e = new Date();
	e.setTime((new Date()).getTime()+3600000*24*999);
	document.cookie = f_szName + '=' + f_szValue + ';expires='+e.toGMTString();
}

function addEventHandler( a, b, c, d ) {
	if ( a.addEventListener ) {
		return a.addEventListener( b, c, !!d );
	}
	else if ( a.attachEvent ) {
		return a.attachEvent( 'on'+b, c );
	}
	a['on'+b] = c;
}

function getPosition( f_obj ) {
	var curleft = curtop = 0;
	if (f_obj.offsetParent) {
		curleft = f_obj.offsetLeft
		curtop = f_obj.offsetTop
		while (f_obj = f_obj.offsetParent) {
			curleft += f_obj.offsetLeft
			curtop += f_obj.offsetTop
		}
	}
	return [curleft,curtop];
}

function getFormVars( f_objForm ) {
	f = $(f_objForm);
	v = "";
	for ( i=0; i<f.elements.length; i++ ) {
		e = f.elements[i];
		t = e.type.toLowerCase();
		// Special handling for checkboxes AND radiobuttons (we need an array of selected checkboxes..)!
		if ( !e.name || ( ( t == 'checkbox' || t == 'radio' ) && !e.checked ) ||  t == 'file' ) {
//			continue;
		}
		else if ( 'select' == e.nodeName.toLowerCase() && e.multiple ) {
			o = e.options;
			j = 0;
			while ( j < o.length ) {
				if ( o[j].selected ) {
					v += '&' + e.name + '=' + o[j].value;
				}
				j++;
			}
//			continue;
		}
		else {
			v += '&' + e.name + '=' + e.value;
		}
	}
	return v.substr(1);
}

function postForm( f_objForm, f_funcHandler )
{
	f = $(f_objForm);
	if ( 'function' != typeof f_funcHandler ) {
		f_funcHandler = function(){};
	}
	q = getFormVars(f);
//alert(q);
	if ( 'function' == typeof Ajax && 'undefined' != Ajax.version ) {
		new Ajax(f.action, {
			method		: f.method,
			params		: q,
			onComplete	: f_funcHandler
		});
	}
	return false;
}

var g_arrJSParams = {};
function JSParams( f_szParams ) {
	if ( !f_szParams ) {
		f_szParams = document.location.hash;
	}
	if ( f_szParams.substr(0,1) == '#' ) {
		f_szParams = f_szParams.substr(1);
	}
	arrParams = f_szParams.split('&');
	objParams = {};
	for ( i=0; i<arrParams.length; i++ ) {
		x = arrParams[i].split('=', 2);
		if ( x.length == 1 ) x[1] = true;
		objParams[x[0]] = x[1];
	}
	g_arrJSParams = objParams;
	return objParams;
}

function foreach(s,f) {
	if ( 'number' != typeof s.length || 0 > s.length ) {
		for ( x in s ) {
			f(x,s[x]);
		}
	}
	else {
		for ( x=0; x<s.length; x++ ) {
			f(x,s[x]);
		}
	}
}