const path = require('path');
const webpack = require('webpack');

module.exports = {
	entry: path.join(__dirname, 'init.js'),
	output: {
		filename: 'sentry.js',
		path: path.resolve(__dirname, '../js')
	},
	resolve: {
		modules: [path.resolve(__dirname), 'node_modules'],
		alias: {
			'handlebars': 'handlebars/runtime.js'
		}
	},
};
