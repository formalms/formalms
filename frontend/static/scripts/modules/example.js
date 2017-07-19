'use strict';

var Example = (function () {

	function Example() {
		this.init();
	}

	Example.prototype.init = function () {

		console.log('new Example initialiazed');

	};

	return Example;

})();

module.exports = Example;