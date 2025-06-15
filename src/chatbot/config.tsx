/**
 * External dependencies
 */
import type { ReactNode } from 'react';

/**
 * WordPress dependencies
 */
import { createContext, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import type { ChatbotConfig } from './types';

const ChatbotConfigContext = createContext< ChatbotConfig | undefined >(
	undefined
);
const { Provider } = ChatbotConfigContext;

type ChatbotConfigProviderProps = {
	config: ChatbotConfig;
	children: ReactNode;
};

/**
 * Provides the chatbot configuration to child components.
 *
 * @since 0.3.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export function ChatbotConfigProvider( props: ChatbotConfigProviderProps ) {
	const { config, children } = props;

	return <Provider value={ config }>{ children }</Provider>;
}

/**
 * A hook that returns a value from the chat configuration.
 *
 * @since 0.3.0
 *
 * @param key - The configuration key.
 * @returns The configuration value, or undefined.
 */
export function useChatbotConfig< K extends keyof ChatbotConfig >(
	key: K
): ChatbotConfig[ K ] | undefined {
	const config = useContext( ChatbotConfigContext );
	if ( ! config ) {
		return undefined;
	}
	return config[ key ];
}
