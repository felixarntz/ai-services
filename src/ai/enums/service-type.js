export const CLOUD = 'cloud';
export const SERVER = 'server';
export const CLIENT = 'client';

const VALUE_MAP = {
	[ CLOUD ]: true,
	[ SERVER ]: true,
	[ CLIENT ]: true,
};

/**
 * Checks if the given value is valid for the enum.
 *
 * @since n.e.x.t
 *
 * @param {string} value The value to check.
 * @return {boolean} True if the value is valid, false otherwise.
 */
export function isValidValue( value ) {
	return !! VALUE_MAP[ value ];
}

/**
 * Gets the list of valid values for the enum.
 *
 * @since n.e.x.t
 *
 * @return {string[]} The list of valid values.
 */
export function getValues() {
	return Object.keys( VALUE_MAP );
}
