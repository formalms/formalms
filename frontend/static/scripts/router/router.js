'use strict';

/* CONFIG */
var UserConfig = require('../config/config');
var RouterConfig = require('./router-config');
var CONFIG = new RouterConfig(UserConfig.env);
/* CONFIG */

/* PAGES */
var Pages = require('../config/pages');
/* PAGES */

/* PUSHSTATE */
var PushState = require('./pushstate');
/* PUSHSTATE */

var $ = require('jquery');
var Router = (function () {
	
	var ALLPAGES = 'allpages';
	
	function Router(pages) {
		checkConfig(pages, $.proxy(function () {
			this.pages = pages;
			this.init();
		}, this));
	}
	
	function checkConfig(pages, successCallback) {
		var errorMessage = false;
		if (pages && typeof pages === 'object') {
			
			if (!errorMessage) {
				successCallback();
			}
			
		}
	}
	
	function checkRoute(routes) {
		var _currentRoute = false;
		$.each(routes, function (index, route) {
			if ($(route).length > 0) {
				_currentRoute = index;
			}
		});
		return _currentRoute;
	}
	
	function onPagesLoaded(pages, currentRoute) {
		
		require('../pages/' + ALLPAGES).setData({
			config: CONFIG.config
		});
		
		require('../pages/' + ALLPAGES).load();
		
		if (pages[currentRoute]) {
			
			require('../pages/' + currentRoute).setData({
				config: CONFIG.config
			});
		}
	}
	
	function onPushStateChange(pages, currentRoute) {
		
		require('../pages/' + ALLPAGES).load({
			config: CONFIG.config
		});
		
		if (pages[currentRoute]) {
			require('../pages/' + currentRoute).load({
				config: CONFIG.config
			});
		}
	}
	
	Router.prototype.init = function () {
		if (CONFIG.config.env === 'local') {
			document.write('<script src="' + CONFIG.config.baseUrl + ':35729/livereload.js?snipver=1" async="" defer=""></script>');
		}
	};
	
	Router.prototype.pushState = function (val) {
		
		if (val) {
			
			require('../pages/' + ALLPAGES).setData({
				config: CONFIG.config
			});
			
			this.isPushStateEnabled = PushState.checkSupport();
			
			if (this.isPushStateEnabled) {
				PushState.watcher(this.pages, $.proxy(function (data) {
					var _routes = this.pages;
					var _currentRoute = data.url;
					onPushStateChange(_routes, _currentRoute);
				}, this));
			}
			
			var _requestRoute = window.location.pathname.replace('/', '');
			if (this.pages.hasOwnProperty(_requestRoute)) {
				PushState.push({url: _requestRoute}, _requestRoute);
			} else {
				var _count = 0;
				$.each(this.pages, function (url) {
					if (_count++ === 0) {
						PushState.push({url: url}, '');
					}
				});
			}
			
			return this.isPushStateEnabled;
		} else {
			
			var _routes = this.pages;
			onPagesLoaded(_routes, checkRoute(_routes));
			
			return 'pushState disabled';
		}
	};
	
	return new Router(Pages);
	
})();

module.exports = Router;