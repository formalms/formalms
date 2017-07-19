'use strict';

/**
 * Utils js module
 * @module Utils
 * @requires jquery
 */

var $ = require('jquery');

var Utils = (function () {
	
	/**
	 * This contains various utility like loading, image preload, resizeEnd or scrollEnd watcher
	 * @exports Utils
	 * @class
	 * @example
	 * var Utils = require('./path/to/utils.js');
	 * var utils = new Utils();
	 */
	function Utils() {

	}
	
	/**
	 * This add/remove hidden class to $('.js-loading') DOM element
	 * @param {string} action - hide/show to add/remove hidden class to $('.js-loading') DOM element
	 * @memberOf Utils
	 * @example
	 * utils.loading('show');
	 * utils.loading('hide');
	 */
	Utils.prototype.loading = function (action) {
		switch (action) {
			case 'show':
				$('.js-loading').removeClass('hidden');
				break;
			case 'hide':
				$('.js-loading').addClass('hidden');
				break;
			default:
				break;
		}
	};
	
	/**
	 * Preload array of images
	 * @param {array} images - array of images to preload
	 * @param {function} allCallback - function called when all images has been loaded
	 * @param {function} singleCallback - function called when single image has been loaded
	 * @memberOf Utils
	 * @example
	 * var imagesArray = ['a.jpg','b.png','c.gif'];
	 * function allCallback() {
	 *  console.log('all images has been loaded');
	 * }
	 * function singleCallback(res) {
	 *  console.log('index of image just loaded ' + res.index);
	 *  console.log('url of image just loaded ' + res.src);
	 * }
	 * utils.preloadImages(imagesArray, allCallback, singleCallback);
	 */
	Utils.prototype.preloadImages = function (images, allCallback, singleCallback) {
		var _loaded = 0;
		$.each(images, function (index, url) {
			try {
				var _img = new Image();
				_img.onload = function () {
					if (singleCallback && typeof singleCallback === 'function') {
						singleCallback({
							index: index,
							src: url
						});
					}

					if (_loaded++ === images.length - 1 && allCallback && typeof allCallback === 'function') {
						allCallback();
					}
				};
				_img.src = url;
			} catch (err) {
				var errorMessage = 'Utils.preloadImages() - image preload error - index: ' + index + ', src: ' + url;
				console.error(errorMessage);
			}
		});
	};
	
	/**
	 * This detects the end of the resize of window
	 * @param {function} endCallback - function called when resize finish
	 * @param {number} time - delay in millisecond
	 * @memberOf Utils
	 * @example
	 * var time = 500;
	 * function endCallback(res) {
	 *  console.log('window width and height when resize action start ' + res.old.width + ' - ' + res.old.height);
	 *  console.log('window width and height when resize action finish ' + res.new.width + ' - ' + res.new.height);
	 * }
	 * utils.resizeEnd(endCallback, time);
	 */
	Utils.prototype.resizeEnd = function (endCallback, time) {

		var _old = {
			width: window.innerWidth,
			height: window.innerHeight
		};
		var _rtime = new Date('2000-01-01');
		var _timeout = false;
		var _delta = time || 250;

		function _resizeEndInner() {
			if (new Date() - _rtime < _delta) {
				setTimeout(_resizeEndInner, _delta);
			} else {
				_timeout = false;
				endCallback({
					old: _old,
					new: {
						width: window.innerWidth,
						height: window.innerHeight
					}
				});
				_old = {
					width: window.innerWidth,
					height: window.innerHeight
				};
			}
		}

		$(window).resize(function () {
			_rtime = new Date();
			if (_timeout === false) {
				_timeout = true;
				setTimeout(_resizeEndInner, _delta);
			}
		});

	};
	
	/**
	 * This detects the end of the resize of window by default or user defined element
	 * @param {function} endCallback - function called when resize finish
	 * @param {string} element - jQuery element selector
	 * @param {number} time - delay in millisecond
	 * @memberOf Utils
	 * @example
	 * var time = 500;
	 * var element = '.my-element';
	 * function endCallback(res) {
	 *  console.log('scroll position when event start ' + res.old);
	 *  console.log('scroll position when event finish ' + res.new);
	 * }
	 * utils.scrollEnd(endCallback, element, time);
	 */
	Utils.prototype.scrollEnd = function (endCallback, element, time) {

		var _rtime = new Date('2000-01-01');
		var _timeout = false;
		var _delta = time || 250;
		var _el = element || window;

		var _old = $(_el).scrollTop();

		function _scrollEndInner() {
			if (new Date() - _rtime < _delta) {
				setTimeout(_scrollEndInner, _delta);
			} else {
				_timeout = false;
				endCallback({
					old: _old,
					new: $(_el).scrollTop()
				});
				_old = $(_el).scrollTop();
			}
		}

		$(document).on('scroll', _el, function () {
			_rtime = new Date();
			if (_timeout === false) {
				_timeout = true;
				setTimeout(_scrollEndInner, _delta);
			}
		});

	};

	return Utils;

})();

module.exports = Utils;