/**
 * Ajax library, v1.1.2
 * 
 * CHANGELOG
 * =================
 * DATE				VERSION			AUTHOR					DESCRIPTION
 * 2007-05-16		1.1				Rudie Dirkx				- Removed the busy member of the object and replaced the busy setting by +1 and -1 in Ajax.busy
 * 2007-05-24		1.1.1			Rudie Dirkx				- Target created in the constructor.
 * 															- Target updated if it starts with a ?. Used to fail.
 * 2007-05-24		1.1.2			Rudie Dirkx				- Removed member 'target'. Target is now passed to the request() function directly.
 * 															- Removed member 'asynchronous'. All requests are now always async.
 * 
 */

Function.prototype.bind = function( object )
{
	var __method = this, args = [];
	return function() {
		return __method.apply(object, args.concat(arguments));
	}
}

var Ajax = function( f_szTarget, f_arrOptions )
{
	// C O N S T R U C T O R //
	if ( !f_szTarget )
	{
		f_szTarget = document.location;
	}
	else if ( '?' == f_szTarget.substr(0,1) )
	{
		szCurrentNoQuery = document.location.href.substr(0, document.location.href.length-document.location.search.length);
		f_szTarget = szCurrentNoQuery + "" + f_szTarget;
	}
	szTarget = "" + f_szTarget;

	if ( !this.getTransport() )
	{
		alert('No AJAX supported for this browser!');
	}

	this.setOptions(f_arrOptions);

	this.request(szTarget);
};

Ajax.prototype = {
	// MEMBERS //

	// PROPERTIES
	/**
	 * @brief		xmlhttp
	 * @type		object
	 * @desc		The XmlHttp object that actually does the request. What kind of object this is, depends on the browser used.
	 * 
	 */
	'xmlhttp'		: null,
	/**/

	/**
	 * @brief		method
	 * @type		string
	 * @desc		The HTTP request method.
	 * 
	 */
	'method'		: 'POST',
	/**/

	/**
	 * @brief		params
	 * @type		string
	 * @desc		The query string to act as post body.
	 * 
	 */
	'params'		: null,
	/**/

	/**
	 * @brief		onComplete
	 * @type		function
	 * @desc		The function to execute when the ajax request is made and status 4 is returned.
	 * 
	 */
	'onComplete'	: function(){},
	/**/


	// METHODS //
	'getTransport': function()
	{
		try {
			this.xmlhttp = new XMLHttpRequest();
		} catch (e1) {
			try {
				this.xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e2) {
				try {
					this.xmlhttp = new XMLHttpRequest("Microsoft.XMLHTTP");
				} catch (e3) {
					this.xmlhttp = false;
				}
			}
		}

		return !!this.xmlhttp;
	},

	'setOptions': function( f_arrOptions )
	{
		// Method
		if ( f_arrOptions['method'] )
		{
			this.method = f_arrOptions['method'];
		}

		// Parameters
		if ( f_arrOptions['params'] )
		{
			this.params = f_arrOptions['params'];
		}

		// Completion function
		if ( f_arrOptions['onComplete'] && "function" == typeof f_arrOptions['onComplete'] )
		{
			this.onComplete = function(){ f_arrOptions['onComplete']( this.xmlhttp ) };
		}

		return true;
	},

	'request': function( f_szTarget )
	{
		// One more request is busy
		Ajax.busy += 1;

		// Start request
		this.xmlhttp.open(
			this.method.toUpperCase(),
			f_szTarget,
			true
		);
		if ( 'POST' == this.method.toUpperCase() )
		{
			this.xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		}
		this.xmlhttp.onreadystatechange = this.whileRequestHandler.bind(this);
		this.xmlhttp.send(this.params);
	},

	'whileRequestHandler': function()
	{
		// Ajax statuses:
		// 0 - Means it's ready to go (uninitialized)
		// 1 - Loading
		// 2 - Finished loading
		// 3 - Almost ready for use
		// 4 - Loading is complete and ready to be dealt with.

		if ( 1 == this.xmlhttp.readyState )
		{
			Ajax.arrGlobalHandlers['onStart'](this.xmlhttp); // You might want to _not_ pass this.xmlhttp to the onStart handler function
		}
		else if ( 4 == this.xmlhttp.readyState )
		{
			// Execute the user's onComplete post-ajax function
			this.onComplete();

			Ajax.busy -= 1;

			// Execute the user's onComplete handler
			Ajax.arrGlobalHandlers['onComplete'](this.xmlhttp); // You might want to _not_ pass this.xmlhttp to the onComplete handler function
		}
	}

};

// STATIC METHODS //
Ajax.busy = 0;
Ajax.arrGlobalHandlers	= { 'onStart' : function(){}, 'onComplete' : function(){} };
Ajax.setGlobalHandlers	= function( f_arrHandlers )
{
	if ( "object" != typeof f_arrHandlers ) return false;

	if ( f_arrHandlers['onStart'] && "function" == typeof f_arrHandlers['onStart'] )
	{
		Ajax.arrGlobalHandlers.onStart = f_arrHandlers['onStart'];
	}

	if ( f_arrHandlers['onComplete'] && "function" == typeof f_arrHandlers['onComplete'] )
	{
		Ajax.arrGlobalHandlers.onComplete = f_arrHandlers['onComplete'];
	}

	return true;
};
