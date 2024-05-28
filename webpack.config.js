const config = require( '@wordpress/scripts/config/webpack.config' );

const { sync: glob } = require( 'fast-glob' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils' );

function getEntryPoints() {
	const entryPoints = getWebpackEntryPoints();

	const [ entryFile ] = glob(
		`${ process.env.WP_SRC_DIRECTORY }/index.[jt]s?(x)`,
		{
			absolute: true,
		}
	);
	if ( entryFile ) {
		entryPoints.index = entryFile;
	}

	return entryPoints;
}

module.exports = {
	...config,
	entry: getEntryPoints,
};
