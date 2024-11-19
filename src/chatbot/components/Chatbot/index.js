/**
 * External dependencies
 */
import clsx from 'clsx';
import PropTypes from 'prop-types';
import { helpers, store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { useEffect, useState, useRef } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useChatbotConfig } from '../../config';
import ChatbotHeader from './chatbot-header';
import ChatbotMessage from './chatbot-message';
import SendIcon from './send-icon';
import './style.scss';

/**
 * Renders the chatbot.
 *
 * @since 0.3.0
 *
 * @param {Object}   props                  Component props.
 * @param {Function} props.onUpdateMessages Function to call when the history of messages is updated.
 * @param {Function} props.onClose          Function to call when the close button is clicked.
 * @param {string}   props.className        Class name to use on the chatbot container.
 * @return {Component} The component to be rendered.
 */
export default function Chatbot( { onUpdateMessages, onClose, className } ) {
	const chatId = useChatbotConfig( 'chatId' );
	const labels = useChatbotConfig( 'labels' );
	const initialBotMessage = useChatbotConfig( 'initialBotMessage' );
	const streaming = useChatbotConfig( 'useStreaming' );
	const getErrorChatResponse = useChatbotConfig( 'getErrorChatResponse' );

	const messagesContainerRef = useRef();
	const inputRef = useRef();

	const scrollIntoView = () => {
		setTimeout( () => {
			if ( messagesContainerRef.current ) {
				messagesContainerRef.current.scrollTop =
					messagesContainerRef?.current?.scrollHeight;
			}
		}, 50 );
	};

	// Scroll to the latest message when the component mounts.
	useEffect( () => {
		scrollIntoView();
	} );

	// Focus on the input when the component mounts.
	useEffect( () => {
		if ( inputRef.current ) {
			inputRef.current.focus();
		}
	}, [ inputRef ] );

	const [ input, setInputValue ] = useState( '' );

	const [ currentMessageGenerator, setCurrentMessageGenerator ] =
		useState( null );

	const { sendMessage, streamSendMessage, receiveContent } =
		useDispatch( aiStore );

	const sendPrompt = async ( message ) => {
		try {
			if ( streaming ) {
				const messageGenerator = await streamSendMessage(
					chatId,
					message
				);
				setCurrentMessageGenerator( messageGenerator );
			} else {
				await sendMessage( chatId, message );
			}
		} catch ( error ) {
			let aiResponseText;
			if ( getErrorChatResponse ) {
				aiResponseText = getErrorChatResponse( error );
			}
			if ( aiResponseText ) {
				/*
				 * Amend chat history to include the error response.
				 * Include a special `type` property to indicate that this is an error.
				 */
				receiveContent(
					chatId,
					helpers.textToContent( message, 'user' )
				);
				receiveContent( chatId, {
					...helpers.textToContent( aiResponseText, 'model' ),
					type: 'error',
				} );
			} else {
				console.error( error ); // eslint-disable-line no-console
			}
		}
	};

	const messages = useSelect( ( select ) =>
		select( aiStore ).getChat( chatId )
	);
	const loading = useSelect( ( select ) =>
		select( aiStore ).isChatLoading( chatId )
	);

	useEffect( () => {
		/*
		 * If streaming is enabled, a message may have been streamed.
		 * Upon receiving the new message in the datastore though, that streaming is done,
		 * so the message generator needs to be cleared.
		 */
		if ( streaming ) {
			setCurrentMessageGenerator( null );
		}

		/*
		 * If onUpdateMessages callback is provided, call it with the updated messages.
		 * Use a timeout to debounce the calls.
		 */
		if ( ! onUpdateMessages ) {
			return;
		}
		const timeout = setTimeout( () => onUpdateMessages( messages ), 500 );
		return () => clearTimeout( timeout );
	}, [ messages, onUpdateMessages, streaming, setCurrentMessageGenerator ] );

	useEffect( () => {
		// While there is a message generator streaming, keep scrolling to the bottom of the chat.
		if ( ! currentMessageGenerator ) {
			return;
		}

		const interval = setInterval( scrollIntoView, 500 );
		return () => clearInterval( interval );
	}, [ currentMessageGenerator ] );

	const handleSubmit = ( event ) => {
		event.preventDefault();

		if ( ! input ) {
			return;
		}

		sendPrompt( input );
		scrollIntoView();
		setInputValue( '' );
	};

	return (
		<div className={ clsx( 'ai-services-chatbot__container', className ) }>
			<div className="ai-services-chatbot__inner-container">
				<ChatbotHeader onClose={ onClose } />
				<div
					className="ai-services-chatbot__messages-container"
					ref={ messagesContainerRef }
				>
					{ !! initialBotMessage && (
						<ChatbotMessage
							content={ {
								role: 'model',
								parts: [ { text: initialBotMessage } ],
							} }
						/>
					) }
					{ messages.map( ( content, index ) => (
						<ChatbotMessage key={ index } content={ content } />
					) ) }
					{ loading && (
						<ChatbotMessage
							content={ {
								role: 'model',
								parts: [ { text: '' } ],
							} }
							loading
						/>
					) }
					{ ! loading && !! currentMessageGenerator && (
						<ChatbotMessage
							content={ {
								role: 'model',
								parts: [ { text: '' } ],
							} }
							contentGenerator={ currentMessageGenerator }
						/>
					) }
				</div>
				<div className="ai-services-chatbot__input-container">
					<form
						className="ai-services-chatbot__input-form"
						onSubmit={ handleSubmit }
					>
						<label
							htmlFor="ai-services-chatbot-input"
							className="screen-reader-text"
						>
							{ labels.inputLabel }
						</label>
						<input
							id="ai-services-chatbot-input"
							className="ai-services-chatbot__input"
							placeholder={ labels.inputPlaceholder }
							value={ input }
							onChange={ ( event ) =>
								setInputValue( event.target.value )
							}
							ref={ inputRef }
						/>
						<button className="ai-services-chatbot__btn-send">
							<SendIcon className="ai-services-chatbot__btn-send-icon" />
							<span className="screen-reader-text">
								{ labels.sendButton }
							</span>
						</button>
					</form>
				</div>
			</div>
		</div>
	);
}

Chatbot.propTypes = {
	onClose: PropTypes.func.isRequired,
	className: PropTypes.string,
};
