'use strict';

var $ = require('jquery');

var Config = (function () {

	function Config(config) {
		checkConfig(config, $.proxy(function (publicConfig) {
			this.init(publicConfig);
		}, this));
	}

	function checkConfig(config, successCallback) {
		var errorMessage = false;
		if (config && typeof config === 'object') {

			var _config = {};

			$.each(config, function (index, env) {
				if (window.location.protocol + '//' + window.location.hostname === env.baseUrl) {
					_config = {
						env: index
					};
					_config = $.extend(true, _config, config[index]);
				}
			});

			if (!errorMessage) {
				successCallback(_config);
			}
		}
	}

	Config.prototype.init = function (publicConfig) {
		this.config = publicConfig;
		return publicConfig;
	};

	Config.prototype.get = function () {
		return this.config;
	};

	return Config;

})();

module.exports = Config;