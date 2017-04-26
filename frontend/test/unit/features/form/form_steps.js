'use strict';

var Form = require('../../../../static/scripts/components/form');

addStepDefinitions(function (scenario) {
	
	var validator = new Form();
	
	var _address = false;
	
	scenario.Given(/^Email to check is (.*)$/, function (address, callback) {
		_address = address;
		console.log('Email address ' + _address);
		callback();
	});
	
	scenario.Then(/^My email should be (.*)$/, function (isvalid, callback) {
		var _fixValid = (isvalid === 'true');
		var _isValid = validator.validateEmail(_address);
		if (_fixValid === _isValid) {
			callback();
		} else {
			callback.fail('Email validator fail --> ' + _fixValid + ' -- ' + _isValid);
		}
	});
	
	scenario.Given(/^I have an input text$/, function (callback) {
		var _length = $('input[type="text"]').length;
		if (_length) {
			callback();
		} else {
			callback.fail('no input tex found');
		}
	});
	
	scenario.Given(/^I leave it blank$/, function (callback) {
		$('input[type="text"]').first().focus().val('');
		callback();
	});
	
	scenario.When(/^I blur it$/, function (callback) {
		$('input[type="text"]').first().blur();
		callback();
	});
	
	scenario.Then(/^I should have an error$/, function (callback) {
		var _value = $('input[type="text"]').first().prop('value');
		
		if (!_value || _value.length === 0) {
			callback();
		} else {
			callback.fail('error ' + _value);
		}
		
	});
	
});