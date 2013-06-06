
/**
 * Todo:
 * - Asset loading (JS, CSS)
 * - Element.formValues()?
 * - Serialize {} to query
 */

(function(W, D) {

	"use strict";

	// try {

	// Some hideous, very useful globals
	var html = D.documentElement,
		head = html.getElementsByTagName('head')[0];
	W.html = html;
	W.head = head;

	var domReadyAttached = false,
		domIsReady = false,
		cssDisplays = {};

	function $ifsetor(pri, sec) {
		return pri !== undefined ? pri : sec;
	}

	function $arrayish(obj) {
		return typeof obj.length == 'number' && typeof obj != 'string' && obj.constructor != Object;
	}

	function $array(list) {
		var arr = [];
		$each(list, function(el, i) {
			arr.push(el);
		});
		return arr;
	}

	function $class(obj) {
		var code = String(obj.constructor);
		return code.match(/ (.+?)[\(\]]/)[1];
	}

	function $each(source, callback, context) {
		// Assume []
		if ( $arrayish(source) ) {
			for ( var i=0, L=source.length; i<L; i++ ) {
				callback.call(context, source[i], i, source);
			}
		}
		// Assume {}
		else {
			for ( var k in source ) {
				if ( source.hasOwnProperty(k) ) {
					callback.call(context, source[k], k, source);
				}
			}
		}

		return source;
	}

	function $extend(Hosts, proto) {
		if ( !(Hosts instanceof Array) ) {
			Hosts = [Hosts];
		}

		$each(Hosts, function(Host) {
			var methodOwner = Host.prototype ? Host.prototype : Host;
			$each(proto, function(fn, name) {
				methodOwner[name] = fn;

				if ( Host == Element ) {
					Elements.prototype[name] = function() {
						var args = arguments;
						return this.invoke(name, args);
					};
				}
			});
		});
	}

	function $getter(Host, prop, getter) {
		if ( Object.defineProperty ) {
			Object.defineProperty(Host.prototype, prop, {get: getter})
		}
		else {
			Host.prototype.__defineGetter__(prop, getter);
		}
	}

	$extend(Array, {
		contains: function(obj) {
			return this.indexOf(obj) != -1;
		},
		unique: function() {
			var els = [];
			$each(this, function(el) {
				els.contains(el) || els.push(el);
			});
			return els;
		},
		each: function(callback, context) {
			return $each(this, callback, context);
		},
		first: function() {
			return this[0] || null;
		},
		last: function() {
			return this[this.length-1] || null;
		}
	});
	Array.defaultFilterCallback = function(item, i, list) {
		return !!item;
	};

	$extend(String, {
		camel: function() {
			// foo-bar => fooBar, -ms-foo => MsFoo
			return this.replace(/\-([^\-])/g, function(a, m) {
				return m.toUpperCase();
			});
		},
		uncamel: function() {
			return this.replace(/([A-Z])/g, function(a, m) {
				return '-' + m.toLowerCase();
			});
		}
	});

	var indexOf = [].indexOf,
		slice = [].slice,
		push = [].push,
		splice = [].splice,
		join = [].join,
		pop = [].join;

	// Date.now polyfill //
	typeof Date.now == 'function' || (Date.now = function() {
		return +new Date;
	});
	// Date.now polyfill //

	// classList polyfill //
	if (!('classList' in html)) {
		(function() { // Do this just for strict mode?
			function DOMTokenList(el) {
				this._el = el;
				el.$classList = this;
				this._reinit();
			};
			$extend(DOMTokenList, {
				_reinit: function() {
					// Empty
					this.length = 0;

					// Fill
					var classes = this._el.className.trim();
					classes = classes ? classes.split(/\s+/g) : [];
					for ( var i=0, L=classes.length; i<L; i++ ) {
						push.call(this, classes[i]);
					}

					return this;
				},
				set: function() {
					this._el.className = join.call(this, ' ');
				},
				add: function(token) {
					push.call(this, token);
					this.set();
				},
				contains: function(token) {
					return indexOf.call(this, token) !== -1;
				},
				item: function(index) {
					return this[index] || null;
				},
				remove: function(token) {
					var i = indexOf.call(this, token);
					if ( i != -1 ) {
						splice.call(this, i, 1);
						this.set();
					}
				},
				toggle: function(token) {
					if (!this.contains(token)) {
						this.add(token);
					}
					else {
						this.remove(token);
					}
				}
			});

			W.DOMTokenList = DOMTokenList;

			$getter(Element, 'classList', function() {
				return this.$classList ? this.$classList._reinit() : new DOMTokenList(this);
			});
		})();
	}
	// classList polyfill //

	$extend(DOMTokenList, {
		replace: function(before, after) {
			this.remove(before);
			this.add(after);
		}
	});

	function Elements(source) {
		this.length = 0;
		source && $each(source, function(el, i) {
			el.nodeType === 1 && this.push(el);
		}, this);
	}
	Elements.prototype = new Array;
	Elements.prototype.constructor = Elements;
	$extend(Elements, {
		invoke: function(method, args) {
			var returnSelf = false,
				res = [],
				isElements = false;
			$each(this, function(el, i) {
				var retEl = el[method].apply(el, args);
				res.push( retEl );
				if ( retEl == el ) returnSelf = true;
				if ( retEl instanceof Element ) isElements = true;
			});
			return returnSelf ? this : ( isElements || !res.length ? new Elements(res) : res );
		}
	});

	function Coords2D(x, y) {
		this.x = x;
		this.y = y;
	}
	$extend(Coords2D, {
		add: function(coords) {
			return new Coords2D(this.x + coords.x, this.y + coords.y);
		},
		subtract: function(coords) {
			return new Coords2D(this.x - coords.x, this.y - coords.y);
		},
		toCSS: function() {
			return {
				left: this.x + 'px',
				top: this.y + 'px'
			};
		},
		join: function(glue) {
			glue == null && (glue = ',');
			return [this.x, this.y].join(glue);
		},
		equal: function(coord) {
			return this.join() == coord.join();
		}
	});

	function AnyEvent(e) {
		if ( typeof e == 'string' ) {
			this.originalEvent = null;
			e = {"type": e, "target": null};
		}
		else {
			this.originalEvent = e;
		}

		this.type = e.type;
		this.target = e.target || e.srcElement;
		this.relatedTarget = e.relatedTarget;
		this.fromElement = e.fromElement;
		this.toElement = e.toElement;
		//this.which = e.which;
		//this.keyCode = e.keyCode;
		this.key = e.keyCode || e.which;
		this.alt = e.altKey;
		this.ctrl = e.ctrlKey;
		this.shift = e.shiftKey;
		this.button = e.button || e.which;
		this.leftClick = this.button == 1;
		this.rightClick = this.button == 2;
		this.middleClick = this.button == 4 || this.button == 1 && this.key == 2;
		this.leftClick = this.leftClick && !this.middleClick;
		this.which = this.key || this.button;
		this.detail = e.detail;

		this.pageX = e.pageX;
		this.pageY = e.pageY;
		this.clientX = e.clientX;
		this.clientY = e.clientY;

		this.touches = e.touches ? $array(e.touches) : undefined;

		if ( this.touches && this.touches[0] ) {
			this.pageX = this.touches[0].pageX;
			this.pageY = this.touches[0].pageY;
		}

		if ( this.pageX != null && this.pageY != null ) {
			this.pageXY = new Coords2D(this.pageX, this.pageY);
		}
		else if ( this.clientX != null && this.clientY != null ) {
			this.pageXY = new Coords2D(this.clientX, this.clientY).add(W.getScroll());
		}

		this.data = e.clipboardData;
		this.time = e.timeStamp || e.timestamp || e.time || Date.now();

		this.total = e.total || e.totalSize;
		this.loaded = e.loaded || e.position;
	}
	$extend(AnyEvent, {
		summary: function(prefix) {
			prefix || (prefix = '');
			var summary = [];
			$each(this, function(value, name) {
				var original = value;
				if ( original && original instanceof Coords2D ) {
					value = original.join();
				}
				else if ( original && typeof original == 'object' ) {
					value = $class(value);
					if ( original instanceof Event || name == 'touches' || typeof name == 'number' ) {
						value += ":\n" + AnyEvent.prototype.summary.call(original, prefix + '  ');
					}
				}
				summary.push(prefix + name + ' => ' + value);
			});
			return summary.join("\n");
		},
		preventDefault: function() {
			var e = this.originalEvent;
			e.preventDefault && e.preventDefault();
			e.returnValue = false;
		},
		stopPropagation: function() {
			var e = this.originalEvent;
			e.stopPropagation && e.stopPropagation();
			e.cancelBubble = true;
		},
		setSubject: function(subject) {
			this.subject = subject;
			if ( this.pageXY ) {
				this.subjectXY = this.pageXY;
				if ( this.subject.getPosition ) {
					this.subjectXY = this.subjectXY.subtract(this.subject.getPosition());
				}
			}
		}
	});

	Event.Keys = {"enter": 13, "up": 38, "down": 40, "left": 37, "right": 39, "esc": 27, "space": 32, "backspace": 8, "tab": 9, "delete": 46};

	Event.Custom = {
		mouseenter: {
			type: 'mouseover',
			filter: function(e) {
				return e.fromElement != this && !this.contains(e.fromElement);
			}
		},
		mouseleave: {
			type: 'mouseout',
			filter: function(e) {
				return e.toElement != this && !this.contains(e.toElement);
			}
		},
		mousewheel: {
			type: 'onmousewheel' in W ? 'mousewheel' : 'mousescroll'
		},
		directchange: {
			type: 'keyup',
			filter: function(e) {
				var lastValue = this.__lastDCValue == null ? this.defaultValue : this.__lastDCValue,
					currentValue = this.value;
				this.__lastDCValue = currentValue;
				return lastValue == null || lastValue != currentValue;
			}
		}
	};
	'onmouseenter' in html && delete Event.Custom.mouseenter;
	'onmouseleave' in html && delete Event.Custom.mouseleave;

	$each([window, document, Element, Elements], function(Host) {
		Host.extend = function(methods) {
			$extend([this], methods);
		};
	});

	function Eventable(subject) {
		this.subject = subject;
		this.time = Date.now();
	}
	$extend(Eventable, {
		"$cache": function(name, value, defaultValue) {
			this.$$cache || (this.$$cache = {});
			this.$$cache[name] || (this.$$cache[name] = defaultValue || {});

			if ( value != null ) {
				this.$$cache[name] = value;
			}

			return this.$$cache[name];
		},
		_addEventListener: function(eventType, callback) {
			if ( this.addEventListener ) {
				this.addEventListener(eventType, callback);
			}
			return this;
		},
		_removeEventListener: function(eventType, callback) {
			if ( this.removeEventListener ) {
				this.removeEventListener(eventType, callback);
			}
			return this;
		},
		on: function(eventType, matches, callback) {
			callback || (callback = matches) && (matches = null);

			var baseType = eventType,
				customEvent = false;
			if ( Event.Custom[eventType] ) {
				customEvent = Event.Custom[eventType];
				customEvent.type && (baseType = customEvent.type);
			}

			function onCallback(e) {
				e && !(e instanceof AnyEvent) && (e = new AnyEvent(e));

				// Find event subject
				var subject = this;
				if ( e && e.target && matches ) {
					if ( !(subject = e.target.selfOrFirstAncestor(matches)) ) {
						return;
					}
				}

				// Custom event type filter
				if ( customEvent && customEvent.filter ) {
					if ( !customEvent.filter.call(subject, e) ) {
						return;
					}
				}

				e.subject || e.setSubject(subject);
				return callback.call(subject, e);
			}

			if ( customEvent && customEvent.before ) {
				if ( customEvent.before.call(this) === false ) {
					return;
				}
			}

			var events = this.$cache('events');
			events[eventType] || (events[eventType] = []);
			events[eventType].push({type: baseType, original: callback, callback: onCallback});

			return this._addEventListener(baseType, onCallback);
		},
		fire: function(eventType, e) {
			var events = this.$cache('events');
			if ( events[eventType] ) {
				e || (e = new AnyEvent(eventType));
				$each(events[eventType], function(listener) {
					listener.callback.call(this, e);
				}, this);
			}
			return this;
		},
		off: function(eventType, callback) {
			var events = this.$cache('events');
			if ( events[eventType] ) {
				var changed = false;
				$each(events[eventType], function(listener, i) {
					if ( !callback || callback == listener.original ) {
						changed = true;
						delete events[eventType][i];
						this._removeEventListener(listener.type, listener.callback);
					}
				}, this);
				changed && (events[eventType] = events[eventType].filter(Array.defaultFilterCallback));
			}
			return this;
		},
		globalFire: function(globalType, localType, originalEvent) {
			var e = originalEvent ? originalEvent : new AnyEvent(localType),
				eventType = (globalType + '-' + localType).camel();
			e.target = e.subject = this;
			e.type = localType;
			e.globalType = globalType;
			W.fire(eventType, e);
			return this;
		}
	});

	$extend([W, D, Element, XMLHttpRequest], Eventable.prototype);
	W.XMLHttpRequestUpload && $extend([XMLHttpRequestUpload], Eventable.prototype);

	$extend([Element, Text], {
		firstAncestor: function(selector) {
			var el = this;
			while ( (el = el.parentNode) && el != D ) {
				if ( el.is(selector) ) {
					return el;
				}
			}
		},
		getNext: function() {
			if ( this.nextElementSibling !== undefined ) {
				return this.nextElementSibling;
			}

			var sibl = this;
			while ( (sibl = sibl.nextSibling) && sibl.nodeType != 1 );

			return sibl;
		},
		getPrev: function() {
			if ( this.previousElementSibling !== undefined ) {
				return this.previousElementSibling;
			}

			var sibl = this;
			while ( (sibl = sibl.previousSibling) && sibl.nodeType != 1 );

			return sibl;
		},
		remove: function() {
			return this.parentNode.removeChild(this);
		},
		getParent: function() {
			return this.parentNode;
		},
		insertAfter: function(el, ref) {
			var next = ref.nextSibling; // including Text
			if ( next ) {
				return this.insertBefore(el, next);
			}
			return this.appendChild(el);
		}
	});

	$extend(document, {
		el: function(tag, attrs) {
			var el = this.createElement(tag);
			attrs && el.attr(attrs);
			return el;
		}
	});
	Element.attr2method = {
		html: function(value) {
			return value == null ? this.getHTML() : this.setHTML(value);
		},
		text: function(value) {
			return value == null ? this.getText() : this.setText(value);
		}
	};

	$extend(Element, {
		is: Element.prototype.matches || Element.prototype.webkitMatches || Element.prototype.mozMatches || Element.prototype.msMatches || Element.prototype.oMatches || Element.prototype.matchesSelector || Element.prototype.webkitMatchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.msMatchesSelector || Element.prototype.oMatchesSelector || function(selector) {
			return $$(selector).contains(this);
		},
		selfOrFirstAncestor: function(selector) {
			return this.is(selector) ? this : this.firstAncestor(selector);
		},
		contains: Element.prototype.contains || function(child) {
			return this.getElements('*').contains(child);
		},
		getChildren: function() {
			return new Elements(this.children || this.childNodes);
		},
		getFirst: function() {
			if ( this.firstElementChild !== undefined ) {
				return this.firstElementChild;
			}

			return this.getChildren().first();
		},
		getLast: function() {
			if ( this.lastElementChild !== undefined ) {
				return this.lastElementChild;
			}

			return this.getChildren().last();
		},
		attr: function(name, value, prefix) {
			prefix == null && (prefix = '');
			if ( value === undefined ) {
				// Get single attribute
				if ( typeof name == 'string' ) {
					if ( Element.attr2method[prefix + name] ) {
						return Element.attr2method[prefix + name].call(this, value, prefix);
					}
					return this.getAttribute(prefix + name);
				}

				// (un)set multiple attributes
				$each(name, function(value, name) {
					if ( value === null ) {
						this.removeAttribute(prefix + name);
					}
					else {
						if ( Element.attr2method[prefix + name] ) {
							return Element.attr2method[prefix + name].call(this, value, prefix);
						}
						this.setAttribute(prefix + name, value);
					}
				}, this);
			}
			// Unset single attribute
			else if ( value === null ) {
				this.removeAttribute(prefix + name);
			}
			// Set single attribute
			else {
				if ( typeof value == 'function' ) {
					value = value.call(this, this.getAttribute(prefix + name));
				}
				if ( Element.attr2method[prefix + name] ) {
					return Element.attr2method[prefix + name].call(this, value, prefix);
				}
				this.setAttribute(prefix + name, value);
			}

			return this;
		},
		data: function(name, value) {
			return this.attr(name, value, 'data-');
		},
		getHTML: function() {
			return this.innerHTML;
		},
		setHTML: function(html) {
			this.innerHTML = html;
			return this;
		},
		getText: function() {
			return this.innerText || this.textContent;
		},
		setText: function(text) {
			this.textContent = this.innerText = text;
			return this;
		},
		getElement: function(selector) {
			return this.querySelector(selector);
		},
		getElements: function(selector) {
			return $$(this.querySelectorAll(selector));
		},
		removeClass: function(token) {
			this.classList.remove(token);
			return this;
		},
		addClass: function(token) {
			this.classList.add(token);
			return this;
		},
		toggleClass: function(token) {
			this.classList.toggle(token);
			return this;
		},
		replaceClass: function(before, after) {
			this.classList.replace(before, after);
			return this;
		},
		injectBefore: function(ref) {
			ref.parentNode.insertBefore(this, ref);
			return this;
		},
		injectAfter: function(ref) {
			ref.parentNode.insertAfter(this, ref);
			return this;
		},
		inject: function(parent) {
			parent.appendChild(this);
			return this;
		},
		append: function(child) {
			this.appendChild(child);
			return this;
		},
		hover: function(matches, over, out) {
			matches || (out = over) && (over = matches) && (matches = null);
			return this.on('mouseenter', over).on('mouseleave', out);
		},
		getStyle: function(property) {
			return getComputedStyle(this).getPropertyValue(property);
		},
		css: function(property, value) {
			if ( value === undefined ) {
				// Get single property
				if ( typeof property == 'string' ) {
					return this.getStyle(property);
				}

				// Set multiple properties
				$each(property, function(value, name) {
					this.style[name] = value;
				}, this);
				return this;
			}

			// Set single property
			this.style[property] = value;
			return this;
		},
		show: function() {
			if ( !cssDisplays[this.nodeName] ) {
				var el = document.el(this.nodeName).inject(this.ownerDocument.body);
				cssDisplays[this.nodeName] = el.getStyle('display');
				el.remove();
			}
			return this.css('display', cssDisplays[this.nodeName]);
		},
		hide: function() {
			return this.css('display', 'none');
		},
		toggle: function() {
			return this.getStyle('display') == 'none' ? this.show() : this.hide();
		},
		empty: function() {
			try {
				this.innerHTML = '';
			}
			catch (ex) {
				while ( this.firstChild ) {
					this.removeChild(this.firstChild);
				}
			}
			return this;
		},
		getPosition: function() {
			var bcr = this.getBoundingClientRect();
			return new Coords2D(bcr.left, bcr.top).add(W.getScroll());
		},
		getScroll: function() {
			return new Coords2D(this.scrollLeft, this.scrollTop);
		}
	});

	$extend(document, {
		getElement: Element.prototype.getElement,
		getElements: Element.prototype.getElements
	});

	$extend([W, D], {
		getScroll: function() {
			return new Coords2D(
				document.documentElement.scrollLeft || document.body.scrollLeft,
				document.documentElement.scrollTop || document.body.scrollTop
			);
		}
	});

	Event.Custom.ready = {
		before: function() {
			if ( this == document ) {
				domReadyAttached || attachDomReady();
			}
		}
	};

	function onDomReady() {
		var rs = D.readyState;
		if ( !domIsReady && (rs == 'complete' || rs == 'interactive') ) {
			domIsReady = true;
			D.fire('ready');
		}
	}

	function attachDomReady() {
		domReadyAttached = true;

		if ( D.addEventListener ) {
			D.addEventListener('DOMContentLoaded', onDomReady, false);
		}
		else if ( D.attachEvent ) {
			D.attachEvent('onreadystatechange', onDomReady);
		}
	}

	function $(id, selector) {
		if ( typeof id == 'function' ) {
			if ( domIsReady ) {
				setTimeout(id, 1);
				return D;
			}

			return D.on('ready', id);
		}

		// By [id]
		if ( !selector ) {
			return D.getElementById(id);
		}

		// By selector
		return D.getElement(id);
	}

	function $$(selector) {
		return $arrayish(selector) ? new Elements(selector) : D.getElements(selector);
	}

	function XHR(url, options) {
		options || (options = {});
		var xhr = new XMLHttpRequest,
			method = $ifsetor(options.method, 'GET').toUpperCase(),
			async = $ifsetor(options.async, true),
			send = $ifsetor(options.send, true),
			data = options.data || '';
		xhr.open(method, url, async, options.username, options.password);
		xhr.url = url;
		xhr.method = method;
		xhr.on('readystatechange', function(e) {
			if ( this.readyState == 4 ) {
				var success = this.status == 200,
					eventType = success ? 'success' : 'error';
				// Specific events
				this.fire(eventType, e);
				this.fire('done', e);
				// Global events
				this.globalFire('xhr', eventType, e);
				this.globalFire('xhr', 'done', e);
			}
		});
		if ( method == 'POST' ) {
			if ( !data.constructor || data.constructor != window.FormData ) {
				var encoding = options.encoding ? '; charset=' + encoding : '';
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded' + encoding);
			}
		}
		if ( send ) {
			xhr.globalFire('xhr', 'start');
			xhr.fire('start');
			xhr.send(data);
		}
		return xhr;
	}

	function shortXHR(method) {
		return function(url, data, options) {
			options || (options = {});
			options.method = method;
			options.data = data;
			var xhr = XHR(url, options);
			return xhr;
		};
	}

	// Expose
	W.$ifsetor = $ifsetor;
	W.$class = $class;
	W.$ = $;
	W.$$ = $$;
	W.$each = $each;
	W.$extend = $extend;

	W.Elements = Elements;
	W.AnyEvent = AnyEvent;
	W.Eventable = Eventable;
	W.Coords2D = Coords2D;

	W.$.xhr = XHR;
	W.$.get = shortXHR('get');
	W.$.post = shortXHR('post');

	// } catch (ex) { alert(ex); }

})(this, this.document);
