/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext, useContext } from '@wordpress/element';

const ChatbotConfigContext = createContext( {} );
const { Provider } = ChatbotConfigContext;

/**
 * Provides the chatbot configuration to child components.
 *
 * @since n.e.x.t
 *
 * @param {Object}  props          Component props.
 * @param {Object}  props.config   The chat configuration object.
 * @param {Element} props.children The children of the component.
 * @return {Component} The component to be rendered.
 */
export function ChatbotConfigProvider( { config, children } ) {
	return <Provider value={ config }>{ children }</Provider>;
}

ChatbotConfigProvider.propTypes = {
	config: PropTypes.shape( {
		chatId: PropTypes.string,
		labels: PropTypes.shape( {
			title: PropTypes.string,
			subtitle: PropTypes.string,
			closeButton: PropTypes.string,
			sendButton: PropTypes.string,
			inputPlaceholder: PropTypes.string,
		} ),
		initialBotMessage: PropTypes.string,
		getErrorChatResponse: PropTypes.func,
		ResponseRenderer: PropTypes.elementType,
	} ).isRequired,
	children: PropTypes.node.isRequired,
};

/**
 * A hook that returns a value from the chat configuration.
 *
 * @since n.e.x.t
 *
 * @param {string} key The configuration key.
 * @return {*} The configuration value, or undefined.
 */
export function useChatbotConfig( key ) {
	const config = useContext( ChatbotConfigContext );
	return config[ key ];
}
