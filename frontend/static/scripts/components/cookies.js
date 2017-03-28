'use strict';

/**
 * Cookies js module
 * @module Cookies
 */

var Cookies = (function () {
	
	/**
	 * This initialize new Cookies and assign config to it
	 * @exports Cookies
	 * @class
	 * @param {object} config - config object
	 * @example
	 * var Cookies = require('./path/to/cookies.js');
	 * var myCookie = new Cookies({
	 *     name: 'myCookieName',
	 *     value: 'value',
	 *     expire: '2020-12-12'
	 * });
	 */
	function Cookies(config) {
		this.config = config;
	}
	
	/**
	 * This check cookie expire date
	 * @param {string} customExpire - expire date '2020-12-12'
	 * @memberof Cookies
	 * @inner
	 */
	function checkExpire(customExpire) {
		var date;
		if (!customExpire) {
			date = new Date();
			date.setTime(date.getTime() + (10 * 365 * 24 * 60 * 60)); // expires in 10 years
		} else {
			date = new Date(customExpire);
			date.setTime(date.getTime());
		}
		return '; expires=' + date.toGMTString();
	}
	
	/**
	 * This write or update cookie with config.name, expiration date if is set or default and JSON.stringify of config.value
	 * @memberof Cookies
	 * @example
	 * myCookie.write();
	 */
	Cookies.prototype.write = function () {
		var expires = checkExpire(this.config.expire);
		document.cookie = this.config.name + '=' + JSON.stringify(this.config.value) + expires + '; path=/';
	};
	
	/**
	 * This read cookie with config.name return JSON.parse of content
	 * @memberof Cookies
	 * @example
	 * var myCookyeContent = myCookie.read();
	 */
	Cookies.prototype.read = function () {
		var nameEQ = this.config.name + '=';
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) === ' ') {
				c = c.substring(1, c.length);
			}
			if (c.indexOf(nameEQ) === 0) {
				return JSON.parse(c.substring(nameEQ.length, c.length));
			}
		}
		return null;
	};
	
	/**
	 * This delete cookie with config.name
	 * @memberof Cookies
	 * @example
	 * myCookie.remove();
	 */
	Cookies.prototype.remove = function () {
		var expires = checkExpire('2002-09-11');
		document.cookie = this.config.name + '=' + JSON.stringify(this.config.value) + expires + '; path=/';
	};

	return Cookies;

})();

module.exports = Cookies;