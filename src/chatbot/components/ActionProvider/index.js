/**
 * External dependencies
 */
import { helpers, store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { Children, cloneElement } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

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
		const aiResponse = await sendMessage( chatId, message );
		const aiResponseText = helpers.contentToText( aiResponse );
		const chatResponse = createChatBotMessage(
			aiResponseText !== ''
				? aiResponseText
				: __(
						'It looks like something went wrong. Please check your browser console for further information.',
						'ai-services'
				  )
		);
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
