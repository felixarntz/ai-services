/**
 * WordPress dependencies
 */
import { createContext, useContext } from '@wordpress/element';

const ChatIdContext = createContext( '' );
const { Provider: ChatIdProvider } = ChatIdContext;

const ChatbotToggleVisibilityContext = createContext( null );
const { Provider: ChatbotToggleVisibilityProvider } =
	ChatbotToggleVisibilityContext;

export { ChatIdProvider, ChatbotToggleVisibilityProvider };

/**
 * A hook that returns the chat ID context.
 *
 * @since n.e.x.t
 *
 * @return {string} The chat ID context.
 */
export function useChatIdContext() {
	return useContext( ChatIdContext );
}

/**
 * A hook that returns the chatbot visibility context.
 *
 * @since n.e.x.t
 *
 * @return {string} The chatbot visibility context.
 */
export function useChatbotToggleVisibilityContext() {
	return useContext( ChatbotToggleVisibilityContext );
}
