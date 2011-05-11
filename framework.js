
(function() {

	function $(what, context) {
		context || (context = document);
		return context.querySelector(what);
	}
	window.$ = $;

	function $$(what, context) {
		context || (context = document);
		return Array.prototype.slice.call(context.querySelectorAll(what), 0);
	}
	window.$$ = $$;

	Node.prototype.find = function(what) {
		return Array.prototype.slice.call(this.querySelectorAll(what), 0);
	};

	Event.prototype.stop = function() {
		this.stopPropagation && this.stopPropagation();
		this.preventDefault && this.preventDefault();
		return false;
	};

	Node.prototype.bind = window.bind = function(type, fn) {
		this.addEventListener(type, fn, false);
	};

	Array.prototype.each = Array.prototype.forEach;
	Array.prototype.invoke = function(fn, args) {
		args || (args = []);
		var r = [];
		this.each(function(el) {
			r.push(el[fn].apply(el, args));
		});
		return r;
	};

	Array.prototype.bind = function(type, fn) {
		this.invoke('bind', [type, fn]);
	};

})();
