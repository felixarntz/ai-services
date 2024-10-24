/**
 * External dependencies
 */
import Chatbot from 'react-chatbot-kit';
import 'react-chatbot-kit/build/main.css';
import { store as aiStore } from '@ai-services/ai';
import { PluginIcon } from '@ai-services/components';

/**
 * WordPress dependencies
 */
import { useState, useEffect, useCallback, useRef } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ESCAPE } from '@wordpress/keycodes';

/**
 * Internal dependencies
 */
import config from '../../config';
import MessageParser from '../MessageParser';
import ActionProvider from '../ActionProvider';
import { ChatIdProvider, ChatbotToggleVisibilityProvider } from '../../context';
import './style.scss';

const CHAT_ID = 'wpspChatbotPrimary';
const SERVICE_ARGS = { capabilities: [ 'text_generation' ] };

/**
 * Renders the chatbot.
 *
 * @since 0.1.0
 *
 * @return {Component} The component to be rendered.
 */
export default function ChatbotApp() {
	const chatbotRef = useRef( null );
	const toggleButtonRef = useRef( null );

	const [ isVisible, setIsVisible ] = useState( false );

	const toggleVisibility = useCallback( () => {
		setIsVisible( ! isVisible );

		// Focus on the toggle when the chatbot is closed.
		if ( isVisible && toggleButtonRef.current ) {
			toggleButtonRef.current.focus();
		}
	}, [ isVisible, toggleButtonRef ] );

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
					},
				} );
			}
		}
	}, [ isVisible, service, hasChat, startChat ] );

	useEffect( () => {
		const chatbotReference = chatbotRef.current;
		if ( ! chatbotReference ) {
			return;
		}

		// If focus is within the chatbot, close the chatbot when pressing ESC.
		const handleKeyDown = ( event ) => {
			if ( event.keyCode === ESCAPE ) {
				toggleVisibility();
			}
		};

		chatbotReference.addEventListener( 'keydown', handleKeyDown );
		return () => {
			chatbotReference.removeEventListener( 'keydown', handleKeyDown );
		};
	}, [ chatbotRef, toggleVisibility ] );

	return (
		<>
			<div
				id="ai-services-chatbot-container"
				className="chatbot-container"
				hidden={ ! isVisible }
				ref={ chatbotRef }
			>
				{ isVisible && hasChat && (
					<ChatIdProvider value={ CHAT_ID }>
						<ChatbotToggleVisibilityProvider
							value={ toggleVisibility }
						>
							<Chatbot
								config={ config }
								messageParser={ MessageParser }
								actionProvider={ ActionProvider }
							/>
						</ChatbotToggleVisibilityProvider>
					</ChatIdProvider>
				) }
			</div>
			<Button
				variant="primary"
				onClick={ toggleVisibility }
				className="chatbot-button button button-primary" // Used so that we don't need to load the heavy 'wp-components' stylesheet everywhere.
				aria-controls="ai-services-chatbot-container"
				aria-expanded={ isVisible }
				ref={ toggleButtonRef }
			>
				<PluginIcon size={ 72 } hideCircle />
				<span className="screen-reader-text">
					{ __( 'Need help?', 'ai-services' ) }
				</span>
			</Button>
		</>
	);
}
