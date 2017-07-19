'use strict';

var $ = require('jquery');
require('../../../../static/scripts/router/router').pushState(true);

var testObj = {};

addStepDefinitions(function (scenario) {
	
	/*
	 @pushstate - change
	 */
	
	scenario.Given(/^I have a title content$/, function (callback) {
		if ($('h1').length) {
			testObj.pageTitle = $('h1').first();
			testObj.pageTitleContent = testObj.pageTitle.text();
			callback();
		} else {
			callback.fail('Title not found');
		}
		
	});
	
	scenario.Given(/^I have a test button for change url$/, function (callback) {
		if ($('a').length) {
			testObj.pageButton = $('a').last();
			callback();
		} else {
			callback.fail('Button not found');
		}
	});
	
	scenario.When(/^I click button$/, function (callback) {
		try {
			testObj.pageButton.click();
			callback();
		} catch (err) {
			callback.fail('Click error --> ' + err);
		}
	});
	
	scenario.Then(/^I should have a new title content$/, function (callback) {
		var _old = testObj.pageTitleContent;
		var _new = testObj.pageTitle.text();
		
		var _isValid = (_old !== _new);
		
		if (_isValid) {
			callback();
		} else {
			callback.fail('Error content change --> ' + _old + ' is equal to ' + _new);
		}
	});
	
});