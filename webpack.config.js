const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Cast to variable so we can extend this more later.
 */
const config = {
	...defaultConfig,
	entry: {
		index: './src/index.js',
		frontend: './src/frontend.js',
	},
};

module.exports = config;
