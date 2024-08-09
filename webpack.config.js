/**
 * External dependencies
 */
const { sync: glob } = require( 'fast-glob' );
const path = require( 'path' );

/**
 * WordPress dependencies
 */
const config = require( '@wordpress/scripts/config/webpack.config' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const {
	camelCaseDash,
} = require( '@wordpress/dependency-extraction-webpack-plugin/lib/util' );

const PACKAGE_NAMESPACE = '@wp-oop-plugin-lib-example/';
const LIBRARY_GLOBAL = 'wpOopPluginLibExample';
const HANDLE_PREFIX = 'wpoopple-';

function getEntryPoints() {
	const getOriginalEntryPoints = getWebpackEntryPoints( 'script' );

	return () => {
		// This returns either entry points for each block, or the src/index file.
		const entryPoints = getOriginalEntryPoints();

		// Add entry points for any index files in one level deep directories in src.
		const srcDirectory = path.join(
			__dirname,
			process.env.WP_SRC_DIRECTORY || 'src'
		);
		const entryFiles = glob( `*/index.[jt]s?(x)`, {
			absolute: true,
			cwd: srcDirectory,
		} );

		// For these entries, expose all exports in a global variable.
		entryFiles.forEach( ( entryFile ) => {
			const entryName = entryFile
				.replace( path.extname( entryFile ), '' )
				.replace( srcDirectory + path.sep, '' );

			entryPoints[ entryName ] = {
				import: entryFile,
				library: {
					type: 'window',
					name: [
						LIBRARY_GLOBAL,
						camelCaseDash(
							path.dirname( entryName ).replace( path.sep, '-' )
						),
					],
				},
			};
		} );

		return entryPoints;
	};
}

module.exports = {
	...config,
	output: {
		...config.output,
		enabledLibraryTypes: [ 'window' ],
	},
	plugins: [
		...config.plugins.filter(
			( plugin ) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new DependencyExtractionWebpackPlugin( {
			requestToExternal( request ) {
				if ( request.startsWith( PACKAGE_NAMESPACE ) ) {
					return [
						LIBRARY_GLOBAL,
						camelCaseDash(
							request.substring( PACKAGE_NAMESPACE.length )
						),
					];
				}
			},
			requestToHandle( request ) {
				if ( request.startsWith( PACKAGE_NAMESPACE ) ) {
					return (
						HANDLE_PREFIX +
						request.substring( PACKAGE_NAMESPACE.length )
					);
				}
			},
		} ),
	],
	entry: getEntryPoints(),
};
