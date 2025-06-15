/**
 * External dependencies
 */
import Markdown from 'markdown-to-jsx';
import { enums, store as aiStore } from '@ai-services/ai';
import { PluginIcon } from '@ai-services/components';
import type { AvailableServicesArgs } from '@ai-services/ai/types';

/**
 * WordPress dependencies
 */
import {
	useState,
	useEffect,
	useCallback,
	useMemo,
	useRef,
} from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { Button } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { ESCAPE } from '@wordpress/keycodes';

/**
 * Internal dependencies
 */
import Chatbot from '../Chatbot';
import { ChatbotConfigProvider } from '../../config';
import './style.scss';
import type {
	ChatbotConfig,
	ChatbotMessage,
	ResponseRendererProps,
} from '../../types';
import errorToString from '../../../utils/error-to-string';
import logError from '../../../utils/log-error';

const CHAT_ID = 'aisChatbotPrimary';
const SERVICE_ARGS: AvailableServicesArgs = {
	capabilities: [ enums.AiCapability.TEXT_GENERATION ],
};

const retrieveVisibility = (): boolean => {
	const chatbotVisibility = window.sessionStorage.getItem(
		'ai-services-built-in-chatbot-visibility'
	);
	return chatbotVisibility === 'visible';
};

const storeVisibility = ( isVisible: boolean ): void => {
	if ( isVisible ) {
		window.sessionStorage.setItem(
			'ai-services-built-in-chatbot-visibility',
			'visible'
		);
	} else {
		window.sessionStorage.removeItem(
			'ai-services-built-in-chatbot-visibility'
		);
	}
};

const retrieveHistory = (): ChatbotMessage[] => {
	const chatbotHistory = window.sessionStorage.getItem(
		'ai-services-built-in-chatbot-history'
	);
	return chatbotHistory ? JSON.parse( chatbotHistory ) : [];
};

const storeHistory = ( history: ChatbotMessage[] ): void => {
	window.sessionStorage.setItem(
		'ai-services-built-in-chatbot-history',
		JSON.stringify( history )
	);
};

/**
 * Gets the error chat response.
 *
 * @since 0.1.0
 *
 * @param error - The error object.
 * @returns The error message.
 */
const getErrorChatResponse = ( error: unknown ): string => {
	return (
		__(
			'I cannot respond to that due to a technical problem. Please try again.',
			'ai-services'
		) +
		'\n\n' +
		sprintf(
			/* translators: %s: error message */
			__( 'Here is the underlying error message: %s', 'ai-services' ),
			errorToString( error )
		)
	);
};

/**
 * Renders the response from the chatbot.
 *
 * @since 0.1.0
 *
 * @param props - Component props.
 * @returns The rendered response.
 */
const ResponseRenderer = ( props: ResponseRendererProps ) => {
	const { text } = props;

	const textData = useMemo( () => {
		const parts = text.split( '---' );
		if ( parts.length !== 3 ) {
			return {
				text: parts[ 0 ].trim(),
				linkUrl: '',
				linkText: '',
			};
		}

		// If the response includes something after the link text, remove it.
		const cleanLinkText = parts[ 2 ].trim().split( '\n' )[ 0 ];
		return {
			text: parts[ 0 ].trim(),
			linkUrl: parts[ 1 ].trim(),
			linkText: cleanLinkText.trim(),
		};
	}, [ text ] );

	return (
		<>
			<Markdown options={ { forceBlock: true, forceWrapper: true } }>
				{ textData.text }
			</Markdown>
			{ textData.linkUrl && textData.linkText && (
				// Don't use the Button component so that we don't need to load the heavy 'wp-components' stylesheet everywhere.
				<a
					className="button button-secondary"
					href={ textData.linkUrl }
				>
					{ textData.linkText }
				</a>
			) }
		</>
	);
};

/**
 * Gets the chatbot configuration.
 *
 * @since 0.1.0
 *
 * @param serviceName - The name of the service.
 * @returns The chatbot configuration.
 */
const getChatbotConfig = ( serviceName?: string ): ChatbotConfig => {
	return {
		chatId: CHAT_ID,
		labels: {
			title: __( 'WordPress Assistant', 'ai-services' ),
			subtitle: serviceName
				? sprintf(
						/* translators: %s: service name */
						__( 'Powered by %s', 'ai-services' ),
						serviceName
				  )
				: '',
			closeButton: __( 'Close chatbot', 'ai-services' ),
			sendButton: __( 'Send prompt', 'ai-services' ),
			inputLabel: __( 'Chatbot input', 'ai-services' ),
			inputPlaceholder: __( 'Write your message here', 'ai-services' ),
		},
		initialBotMessage: __( 'How can I help you?', 'ai-services' ),
		getErrorChatResponse,
		ResponseRenderer,
		useStreaming: true,
	};
};

/**
 * Renders the chatbot.
 *
 * @since 0.1.0
 *
 * @returns The component to be rendered.
 */
export default function ChatbotApp() {
	const chatbotRef = useRef< HTMLDivElement | null >( null );
	const toggleButtonRef = useRef< HTMLButtonElement | null >( null );

	const [ isVisible, setIsVisible ] = useState< boolean >( false );

	useEffect( () => {
		const initialVisibility = retrieveVisibility();
		if ( initialVisibility ) {
			setIsVisible( true );
		}
	}, [ setIsVisible ] );

	const toggleVisibility = useCallback( () => {
		setIsVisible( ! isVisible );
		storeVisibility( ! isVisible );

		// Focus on the toggle when the chatbot is closed.
		if ( isVisible && toggleButtonRef.current ) {
			toggleButtonRef.current.focus();
		}
	}, [ isVisible, toggleButtonRef ] );

	const { service, hasChat } = useSelect( ( select ) => {
		const aiStoreSelector = select( aiStore );
		return {
			service: aiStoreSelector.getAvailableService( SERVICE_ARGS ),
			hasChat: aiStoreSelector.hasChat( CHAT_ID ) as boolean,
		};
	}, [] );

	const { startChat } = useDispatch( aiStore );

	useEffect( () => {
		if ( ! hasChat && isVisible ) {
			if ( service === null ) {
				logError(
					'No AI service found with the required capabilities!'
				);
			} else if ( service ) {
				startChat( CHAT_ID, {
					service: service.getServiceSlug(),
					modelParams: {
						feature: 'ai-services-chatbot',
					},
					history: retrieveHistory(),
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
		const handleKeyDown = ( event: KeyboardEvent ) => {
			if ( event.keyCode === ESCAPE ) {
				toggleVisibility();
			}
		};

		chatbotReference.addEventListener( 'keydown', handleKeyDown );
		return () => {
			chatbotReference.removeEventListener( 'keydown', handleKeyDown );
		};
	}, [ chatbotRef, toggleVisibility ] );

	const config: ChatbotConfig = useMemo(
		() =>
			getChatbotConfig(
				service ? service.getServiceMetadata()?.name : undefined
			),
		[ service ]
	);

	return (
		<>
			<div
				id="ai-services-assistant-chatbot-container"
				className="ai-services-assistant-chatbot-container"
				hidden={ ! isVisible }
				ref={ chatbotRef }
			>
				{ isVisible && hasChat && (
					<ChatbotConfigProvider config={ config }>
						<Chatbot
							onUpdateMessages={ storeHistory }
							onClose={ toggleVisibility }
						/>
					</ChatbotConfigProvider>
				) }
			</div>
			<Button
				variant="primary"
				onClick={ toggleVisibility }
				className="ai-services-assistant-chatbot-button button button-primary" // Used so that we don't need to load the heavy 'wp-components' stylesheet everywhere.
				aria-controls="ai-services-assistant-chatbot-container"
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
