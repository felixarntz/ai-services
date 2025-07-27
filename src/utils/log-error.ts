/**
 * Internal dependencies
 */
import errorToString from './error-to-string';

/**
 * Logs an error message to the console.
 *
 * @since 0.7.0
 *
 * @param error - The error to log.
 */
export default function logError( error: unknown ): void {
	const message = errorToString( error );
	console.error( message ); // eslint-disable-line no-console
}
