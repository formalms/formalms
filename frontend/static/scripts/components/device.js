'use strict';

/**
 * Device js module
 * @module Device
 */
var Device = (function () {
	
	/**
	 * This get html tag and run some check to detect device specifications then add relative class to the html tag
	 * @exports Device
	 * @class
	 * @example
	 * require('./path/to/device.js');
	 */
	function Device() {
		var _el = document.getElementsByTagName('html')[0];
		checkTouch(_el);
	}
	
	/**
	 * This check if device support ontouchstart event
	 * @param {DOM} el - html tag
	 * @memberOf Device
	 * @inner
	 */
	function checkTouch(el) {
		var isTouch = 'ontouchstart' in document.documentElement;
		
		if (isTouch) {
			el.classList.add('touch');
		} else {
			el.classList.add('no-touch');
		}
	}

	return new Device();

})();

module.exports = Device;