/**
 * External dependencies
 */
import { store as aiStore } from '@wp-starter-plugin/ai-store';

/**
 * WordPress dependencies
 */
import { Children, cloneElement } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useChatIdContext } from '../../context';

/**
 * Utility component for the chatbot.
 *
 * @since n.e.x.t
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
		const chatResponse = createChatBotMessage(
			aiResponse?.parts?.[ 0 ]?.text || 'I am sorry, I do not understand.'
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
