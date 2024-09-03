import { Children, cloneElement } from '@wordpress/element';

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
	return (
		<div>
			{ Children.map( children, ( child ) => {
				return cloneElement( child, {
					actions: {},
				} );
			} ) }
		</div>
	);
}
