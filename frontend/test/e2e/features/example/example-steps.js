'use strict';

module.exports = function () {
	this.Given(/^I open Ciffi's home page$/, function (client) {
		client.url('http://ciffi.it').waitForElementVisible('body', 1000);
	});
	
	this.Then(/^the title is "([^"]*)"$/, function (client, title) {
		client.assert.title(title);
	});
	
	this.Then(/^the canvas animation exists$/, function (client) {
		client.assert.visible('canvas#bgCanvas');
	});
};