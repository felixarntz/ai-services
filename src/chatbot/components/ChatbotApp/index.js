/**
 * External dependencies
 */
import Chatbot from 'react-chatbot-kit';
import 'react-chatbot-kit/build/main.css';
import { store as aiStore } from '@ai-services/ai-store';

/**
 * WordPress dependencies
 */
import { useState, useEffect } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import config from '../../config';
import MessageParser from '../MessageParser';
import ActionProvider from '../ActionProvider';
import { ChatIdProvider } from '../../context';
import './style.scss';

const CHAT_ID = 'wpspChatbotPrimary';
const SERVICE_ARGS = { capabilities: [ 'text_generation' ] };

/**
 * Renders the chatbot.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function ChatbotApp() {
	const [ isVisible, setIsVisible ] = useState( false );
	const toggleVisibility = () => setIsVisible( ! isVisible );

	const { service, hasChat } = useSelect( ( select ) => {
		return {
			service: select( aiStore ).getAvailableService( SERVICE_ARGS ),
			hasChat: !! select( aiStore ).getChat( CHAT_ID ),
		};
	} );

	const { startChat } = useDispatch( aiStore );

	useEffect( () => {
		if ( ! hasChat && isVisible ) {
			if ( service === null ) {
				// eslint-disable-next-line no-console
				console.error(
					'No AI service found with the required capabilities!'
				);
			} else if ( service ) {
				startChat( CHAT_ID, {
					service: service.slug,
					modelParams: {
						feature: 'ai-services-chatbot',
						useWppsChatbot: true,
					},
				} );
			}
		}
	}, [ isVisible, service, hasChat, startChat ] );

	return (
		<>
			<div
				id="ai-services-chatbot-container"
				className="chatbot-container"
				hidden={ ! isVisible }
			>
				{ isVisible && hasChat && (
					<ChatIdProvider value={ CHAT_ID }>
						<Chatbot
							config={ config }
							messageParser={ MessageParser }
							actionProvider={ ActionProvider }
						/>
					</ChatIdProvider>
				) }
			</div>
			<Button
				variant="primary"
				onClick={ toggleVisibility }
				className="chatbot-button button button-primary" // Used so that we don't need to load the heavy 'wp-components' stylesheet everywhere.
				aria-controls="ai-services-chatbot-container"
				aria-expanded={ isVisible }
			>
				{ __( 'Need help?', 'ai-services' ) }
			</Button>
		</>
	);
}
