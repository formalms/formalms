'use strict';

/*
 *
 * replace _indexUrl variable with your development index page
 *
 * */
var _indexUrl = 'http://formalms.local/';

var OpenBrowserPlugin = require('open-browser-webpack-plugin');

module.exports = {
	entry: {
		main: './static/scripts/main.js'
	},
	output: {
		path: '../web/static',
		filename: '[name].js'
	},
	devtool: 'eval',
	watch: true,
	module: {
		preLoaders: [
			{
				test: /\.js$/,
				loader: 'eslint-loader',
				exclude: './node_modules'
			}
		]
	},
	plugins: [
		new OpenBrowserPlugin({
			url: _indexUrl
		})
	],
	eslint: {
		configFile: './.eslintrc'
	}
};