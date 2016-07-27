var CopyWebpackPlugin = require( 'copy-webpack-plugin' );
var config = require( './webpack.config' );

config.plugins = [
	...config.plugins,
	new CopyWebpackPlugin( [
		{ from: 'config/config-dev-hot.php', to: 'config.php' }
	] )
];

module.exports = config;

