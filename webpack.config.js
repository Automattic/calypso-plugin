var path = require( 'path' );
var webpack = require( 'webpack' );
var ExtractTextPlugin = require( 'extract-text-webpack-plugin' );

module.exports = {
	entry: {
		devServer:          'webpack-dev-server/client?http://0.0.0.0:8090',
		devServerOnly:      'webpack/hot/only-dev-server',
		['calypso-plugin']: './client/index.jsx'
	},
	output: {
		path: path.join( __dirname, 'dist' ),
		publicPath: '/dist/',
		filename: '[name]_bundle.js'
	},
	devServer: {
		outputPath: path.join( __dirname, 'dist' ) /* CopyWebpackPlugin needs this */
	},
	module: {
		loaders: [
			{
				test: /\.jsx?$/,
				include: [
					path.resolve( __dirname, 'client' ),
					path.resolve( __dirname, 'node_modules', 'wp-calypso', 'client' ),
					path.resolve( __dirname, 'node_modules', 'gridicons' ),
				],
				loaders: [ 'react-hot', 'babel' ]
			},
			{
				test: /\.json$/,
				loader: 'json-loader'
			},
			{
				test: /\.scss$/,
				include: [
					path.resolve( __dirname, 'assets', 'stylesheets' ),
					path.resolve( __dirname, 'client' )
				],
				loader: ExtractTextPlugin.extract( 'style', 'css?minimize!sass' )
			},
			{
				test: /\.html$/,
				loader: 'html-loader'
			}
		]
	},
	sassLoader: {
		includePaths: [
			path.resolve( __dirname, 'node_modules', 'wp-calypso', 'client' ),
			path.resolve( __dirname, 'node_modules', 'wp-calypso', 'assets', 'stylesheets' )
		]
	},
	resolve: {
		extensions: [ '', '.js', '.jsx', '.json', '.scss', '.html' ],
		modulesDirectories: [ 'node_modules' ],
		root: [
			path.join( __dirname, 'client' ),
			path.join( __dirname, 'node_modules', 'wp-calypso', 'client' )
		]
	},
	plugins: [
		new webpack.NoErrorsPlugin(),
		new ExtractTextPlugin( '[name].css' )
	],
	node: {
		fs: 'empty'
	},
};
