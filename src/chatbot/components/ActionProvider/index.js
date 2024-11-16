/**
 * External dependencies
 */
import { helpers, store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { Children, cloneElement } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useChatIdContext } from '../../context';

/**
 * Utility component for the chatbot.
 *
 * @since 0.1.0
 *
 * @param {Object}   props                      Component props.
 * @param {Function} props.createChatBotMessage Function to create a chat message.
 * @param {Function} props.setState             Function to set the state of the component.
 * @param {Element}  props.children             The children of the component.
 * @return {Component} The component to be rendered.
 */
export default function ActionProvider( {
	createChatBotMessage,
	setState,
	children,
} ) {
	const chatId = useChatIdContext();
	const { sendMessage } = useDispatch( aiStore );

	const respond = async ( message ) => {
		let aiResponseText;
		try {
			const aiResponse = await sendMessage( chatId, message );
			aiResponseText = helpers.contentToText( aiResponse );
		} catch ( error ) {
			aiResponseText =
				__(
					'I cannot respond to that due to a technical problem. Please try again.',
					'ai-services'
				) +
				'\n\n' +
				sprintf(
					/* translators: %s: error message */
					__(
						'Here is the underlying error message: %s',
						'ai-services'
					),
					error?.message || error
				);
		}
		const chatResponse = createChatBotMessage( aiResponseText );
		setState( ( state ) => ( {
			...state,
			messages: [ ...state.messages, chatResponse ],
		} ) );
	};
	return (
		<div>
			{ Children.map( children, ( child ) => {
				return cloneElement( child, {
					actions: { respond },
				} );
			} ) }
		</div>
	);
}
