/**
 * External dependencies
 */
const resolverNode = require( 'eslint-import-resolver-node' );
const path = require( 'path' );

const PACKAGES_DIR = path.resolve( __dirname, '../../src' );

exports.interfaceVersion = 2;

exports.resolve = ( source, file, config ) => {
	const resolve = ( sourcePath ) =>
		resolverNode.resolve( sourcePath, file, {
			...config,
			extensions: [ '.tsx', '.ts', '.mjs', '.js', '.json', '.node' ],
		} );

	if ( source.startsWith( '@wp-starter-plugin/' ) ) {
		const packageName = source.slice( '@wp-starter-plugin/'.length );

		return resolve( path.join( PACKAGES_DIR, packageName ) );
	}

	return resolve( source );
};
