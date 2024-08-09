/**
 * WordPress dependencies
 */
const config = require( '@wordpress/scripts/config/.eslintrc' );

module.exports = {
	...config,
	rules: {
		...config.rules,
		'import/no-unresolved': [
			'error',
			{ ignore: [ '^@wordpress/', '^@wp-oop-plugin-lib-example/' ] },
		],
	},
};
