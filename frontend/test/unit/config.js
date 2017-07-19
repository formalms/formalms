'use strict';

var cucumberConfig = [{
	pattern: '../../node_modules/karma-cucumberjs/vendor/cucumber-html.css',
	watched: false,
	included: false,
	served: true
}, {
	pattern: './app.template',
	watched: false,
	included: false,
	served: true
}, {
	pattern: './features/**/*.feature',
	watched: true,
	included: false,
	served: true
}, {
	pattern: './features/**/*.js',
	watched: true,
	included: true,
	served: true
}];

module.exports = cucumberConfig;