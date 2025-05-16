export const CHAT_HISTORY = 'chat_history';
export const FUNCTION_CALLING = 'function_calling';
export const IMAGE_GENERATION = 'image_generation';
export const MULTIMODAL_INPUT = 'multimodal_input';
export const MULTIMODAL_OUTPUT = 'multimodal_output';
export const TEXT_GENERATION = 'text_generation';

export const _VALUE_MAP = {
	[ CHAT_HISTORY ]: true,
	[ FUNCTION_CALLING ]: true,
	[ IMAGE_GENERATION ]: true,
	[ MULTIMODAL_INPUT ]: true,
	[ MULTIMODAL_OUTPUT ]: true,
	[ TEXT_GENERATION ]: true,
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
