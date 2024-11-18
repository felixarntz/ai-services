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
 * @since n.e.x.t
 *
 * @param {Object}   props           Component props.
 * @param {Function} props.onClose   Function to call when the close button is clicked.
 * @param {string}   props.className Class name to use on the chatbot container.
 * @return {Component} The component to be rendered.
 */
export default function Chatbot( { onClose, className } ) {
	const chatId = useChatbotConfig( 'chatId' );
	const labels = useChatbotConfig( 'labels' );
	const initialBotMessage = useChatbotConfig( 'initialBotMessage' );
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

	const { sendMessage, receiveContent } = useDispatch( aiStore );

	const sendPrompt = async ( message ) => {
		try {
			await sendMessage( chatId, message );
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
				</div>
				<div className="ai-services-chatbot__input-container">
					<form
						className="ai-services-chatbot__input-form"
						onSubmit={ handleSubmit }
					>
						<input
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
