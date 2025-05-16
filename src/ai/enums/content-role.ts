export const USER = 'user';
export const MODEL = 'model';
export const SYSTEM = 'system';

export const _VALUE_MAP = {
	[ USER ]: true,
	[ MODEL ]: true,
	[ SYSTEM ]: true,
};

/**
 * Checks if the given value is valid for the enum.
 *
 * @since 0.2.0
 *
 * @param value - The value to check.
 * @returns True if the value is valid, false otherwise.
 */
export function isValidValue( value: string ) {
	return value in _VALUE_MAP;
}

/**
 * Gets the list of valid values for the enum.
 *
 * @since 0.2.0
 *
 * @returns The list of valid values.
 */
export function getValues() {
	return Object.keys( _VALUE_MAP );
}
