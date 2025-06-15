/**
 * Transforms an error object into a user-facing error message.
 *
 * @since n.e.x.t
 *
 * @param error - The error to transform.
 * @returns The error message as a string.
 */
export default function errorToString( error: unknown ): string {
	if ( error instanceof Error ) {
		return error.message;
	}
	if ( typeof error === 'object' && error !== null && 'message' in error ) {
		return String( error.message );
	}
	return String( error );
}
