export const CHAT_HISTORY = 'chat_history';
export const IMAGE_GENERATION = 'image_generation';
export const MULTIMODAL_INPUT = 'multimodal_input';
export const TEXT_GENERATION = 'text_generation';

const VALUE_MAP = {
	[ CHAT_HISTORY ]: true,
	[ IMAGE_GENERATION ]: true,
	[ MULTIMODAL_INPUT ]: true,
	[ TEXT_GENERATION ]: true,
};

/**
 * Checks if the given value is valid for the enum.
 *
 * @since 0.2.0
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
 * @since 0.2.0
 *
 * @return {string[]} The list of valid values.
 */
export function getValues() {
	return Object.keys( VALUE_MAP );
}
