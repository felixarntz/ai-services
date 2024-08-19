/**
 * Given a string, returns a new string with dash and underscore separators
 * converted to camelCase equivalent.
 *
 * @param {string} input Input dash- or underscore-delimited string.
 * @return {string} Camel-cased string.
 */
export default function camelCase( input ) {
	return input.replace( /-_([a-z])/g, ( _, letter ) => letter.toUpperCase() );
}
