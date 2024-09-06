/**
 * WordPress dependencies
 */
import { createContext, useContext } from '@wordpress/element';

const Context = createContext( '' );
const { Provider } = Context;

export { Provider as ChatIdProvider };

/**
 * A hook that returns the chat ID context.
 *
 * @since n.e.x.t
 *
 * @return {string} The chat ID context.
 */
export function useChatIdContext() {
	return useContext( Context );
}
