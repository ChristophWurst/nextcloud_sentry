var webpack = require('webpack');
const { merge } = require('webpack-merge');
const baseConfig = require('./webpack.base.config.js');

module.exports = merge(baseConfig, {
	mode: 'production',
	plugins: [
		new webpack.optimize.AggressiveMergingPlugin(), // Merge chunks
		new webpack.LoaderOptionsPlugin({
			minimize: true
		})
	]
});
