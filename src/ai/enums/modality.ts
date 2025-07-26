export const TEXT = 'text';
export const IMAGE = 'image';
export const AUDIO = 'audio';

export const _VALUE_MAP = {
	[ TEXT ]: true,
	[ IMAGE ]: true,
	[ AUDIO ]: true,
};

/**
 * Checks if the given value is valid for the enum.
 *
 * @since 0.7.0
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
 * @since 0.7.0
 *
 * @returns The list of valid values.
 */
export function getValues() {
	return Object.keys( _VALUE_MAP );
}
