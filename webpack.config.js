const config = require( '@wordpress/scripts/config/webpack.config' );

const { sync: glob } = require( 'fast-glob' );
const path = require( 'path' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils' );

function getEntryPoints() {
	const getOriginalEntryPoints = getWebpackEntryPoints( 'script' );

	return () => {
		const entryPoints = getOriginalEntryPoints();

		const srcDirectory = path.join(
			__dirname,
			process.env.WP_SRC_DIRECTORY || 'src'
		);
		const entryFiles = glob( `*/index.[jt]s?(x)`, {
			absolute: true,
			cwd: srcDirectory,
		} );
		entryFiles.forEach( ( entryFile ) => {
			const entryName = entryFile
				.replace( path.extname( entryFile ), '' )
				.replace( srcDirectory + path.sep, '' );

			entryPoints[ entryName ] = entryFile;
		} );

		return entryPoints;
	};
}

module.exports = {
	...config,
	entry: getEntryPoints(),
};
