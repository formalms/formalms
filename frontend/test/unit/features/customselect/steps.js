'use strict';

addStepDefinitions(function (scenario) {
	
	scenario.Given(/^I have a select with class js\-customselect$/, function (callback) {
		var _length = $('select.js-customselect').length;
		if (_length) {
			require('../../../../static/scripts/components/customselect');
			callback();
		} else {
			callback.fail('select.js-customselect not found');
		}
	});
	
	scenario.Given(/^My tag has been wrapped$/, function (callback) {
		
		var _el = $('select.js-customselect');
		var _wrap = _el.parent('.form__customselect').length;
		if (_wrap) {
			callback();
		} else {
			callback.fail('the plugin seems to be not active');
		}
	});
	
	scenario.When(/^I change select value$/, function (callback) {
		var _el = $('select.js-customselect').first();
		_el.on('change', function () {
			callback();
		});
	});
	
	scenario.Then(/^My custom element should be filled$/, function (callback) {
		var _el = $('select.js-customselect').first();
		var _text = _el.find('option:selected').text();
		var _customText = _el.siblings('.js-cutomselect--text').text();
		if (_customText === _text) {
			callback();
		} else {
			callback.fail('the custom elementen text ' + _customText + ' should be ' + _text);
		}
	});
	
});
