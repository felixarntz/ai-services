/**
 * Given a string, returns a new string with dash separators converted to
 * camelCase equivalent. This is not as aggressive as `_.camelCase` in
 * converting to uppercase, where Lodash will also capitalize letters
 * following numbers.
 *
 * @param {string} input Input dash-delimited string.
 * @return {string} Camel-cased string.
 */
export default function camelCaseDash( input ) {
	return input.replace( /-([a-z])/g, ( _, letter ) => letter.toUpperCase() );
}
