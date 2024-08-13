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

		// Require JS docblocks for all functions, just like in PHP.
		'jsdoc/require-jsdoc': [
			'error',
			{
				require: {
					FunctionDeclaration: true,
					MethodDefinition: true,
					ClassDeclaration: true,
					ArrowFunctionExpression: false,
					FunctionExpression: true,
				},
			},
		],
		'jsdoc/require-description': 'error',
		'jsdoc/require-param': 'error',
		'jsdoc/require-param-description': 'error',
		'jsdoc/require-param-name': 'error',
		'jsdoc/require-param-type': 'error',
		'jsdoc/require-returns-check': 'error',
		'jsdoc/require-returns-description': 'error',
		'jsdoc/require-returns-type': 'error',
		'jsdoc/require-returns': 'error',
	},
};
