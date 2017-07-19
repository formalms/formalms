'use strict';

var autoprefixer = require('autoprefixer');
var OpenBrowserPlugin = require('open-browser-webpack-plugin');
var HtmlWebpackPlugin = require('html-webpack-plugin');
var ExtractTextPlugin = require('extract-text-webpack-plugin');

function newPage(page) {
	return new HtmlWebpackPlugin({
		template: __dirname + '/static/' + page + '.html',
		filename: page + '.html',
		inject: 'body'
	});
}

module.exports = {
	entry: {
		styles: './static/scripts/styles.js',
		scripts: './static/scripts/main.js'
	},
	output: {
		path: './dist',
		filename: '[name].js'
	},
	module: {
		preLoaders: [
			{
				test: /\.js$/,
				loader: 'eslint-loader',
				exclude: './node_modules'
			}
		],
		loaders: [
			{
				test: /\.scss$/,
				loader: ExtractTextPlugin.extract('style-loader', 'css-loader!postcss-loader!sass-loader')
			},
			{
				test: /\.(woff|ttf|eot|svg)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
				loaders: ['base64-font-loader']
			}
		]
	},
	plugins: [
		new OpenBrowserPlugin({
			url: 'http://localhost:8080'
		}),
		new ExtractTextPlugin('[name].css'),
		newPage('index'),
		newPage('example')
	],
	postcss: function () {
		return [autoprefixer];
	},
	eslint: {
		configFile: './.eslintrc'
	}
};