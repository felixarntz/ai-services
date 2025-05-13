/**
 * WordPress dependencies
 */
const config = require( '@wordpress/scripts/config/.eslintrc' );

const extraSettings = {
	'import/resolver': require.resolve( './tools/js/eslint-import-resolver' ),
};

const extraRules = {
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
};

const typescriptRules = {
	'jsdoc/require-param-type': 'off',
	'jsdoc/require-returns-type': 'off',
	'no-unused-vars': 'off',
	'@typescript-eslint/no-unused-vars': [
		'error',
		{ ignoreRestSiblings: true },
	],
	'no-shadow': 'off',
	'@typescript-eslint/no-shadow': 'error',
	'@typescript-eslint/method-signature-style': 'error',
	'tsdoc/syntax': 'error',
};

module.exports = {
	...config,
	extends: [
		...( config.extends || [] ),
		'plugin:@wordpress/eslint-plugin/i18n',
	],
	settings: {
		...( config.settings || {} ),
		...extraSettings,
	},
	rules: {
		...( config.rules || [] ),
		...extraRules,
	},
	overrides: [
		...( config.overrides || [] ),
		{
			files: [ '**/*.ts', '**/*.tsx' ],
			extends: [
				...( config.extends || [] ),
				'plugin:@wordpress/eslint-plugin/i18n',
				'plugin:@typescript-eslint/recommended',
			],
			plugins: [
				...( config.plugins || [] ),
				'eslint-plugin-tsdoc',
				'@typescript-eslint',
			],
			parser: '@typescript-eslint/parser',
			parserOptions: {
				tsconfigRootDir: __dirname,
			},
			settings: {
				...( config.settings || {} ),
				...extraSettings,
				jsdoc: {
					mode: 'typescript',
					// TSDoc expects `@returns` and `@yields`.
					tagNamePreference: {
						returns: 'returns',
						yields: 'yields',
					},
				},
			},
			rules: {
				...( config.rules || [] ),
				...extraRules,
				...typescriptRules,
			},
		},
	],
};
