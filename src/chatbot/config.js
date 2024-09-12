/**
 * External dependencies
 */
import { createChatBotMessage } from 'react-chatbot-kit';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import ChatbotHeader from './components/ChatbotHeader';
import ChatbotMessage from './components/ChatbotMessage';

const config = {
	initialMessages: [
		createChatBotMessage(
			__( 'How can I help you?', 'wp-starter-plugin' )
		),
	],
	customComponents: {
		header: () => <ChatbotHeader />,
		botChatMessage: ( props ) => <ChatbotMessage { ...props } />,
	},
};

export default config;
