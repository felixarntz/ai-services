/**
 * Logs an error message to the console.
 *
 * @param error - The error to log.
 */
export default function logError( error: unknown ): void {
	const message = error instanceof Error ? error.message : String( error );
	console.error( message ); // eslint-disable-line no-console
}
