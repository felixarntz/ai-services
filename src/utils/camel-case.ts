/**
 * Given a string, returns a new string with dash and underscore separators
 * converted to camelCase equivalent.
 *
 * @param input - Input dash- or underscore-delimited string.
 * @returns Camel-cased string.
 */
export default function camelCase( input: string ): string {
	return input.replace( /-|_([a-z])/g, ( _, letter ) =>
		letter.toUpperCase()
	);
}
