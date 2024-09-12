/**
 * External dependencies
 */
import { createChatBotMessage } from 'react-chatbot-kit';

/**
 * Internal dependencies
 */
import ChatbotMessage from './components/ChatbotMessage';

const config = {
	initialMessages: [ createChatBotMessage( `How can I help you?` ) ],
	botName: `WordPress Assistant`,
	customComponents: {
		botChatMessage: ( props ) => <ChatbotMessage { ...props } />,
	},
};

export default config;
