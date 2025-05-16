export const CLOUD = 'cloud';
export const SERVER = 'server';
export const CLIENT = 'client';

export const _VALUE_MAP = {
	[ CLOUD ]: true,
	[ SERVER ]: true,
	[ CLIENT ]: true,
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
