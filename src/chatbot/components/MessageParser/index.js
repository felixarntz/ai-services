/**
 * WordPress dependencies
 */
import { Children, cloneElement } from '@wordpress/element';

/**
 * Utility component for the chatbot.
 *
 * @since n.e.x.t
 *
 * @param {Object}  props          Component props.
 * @param {Element} props.children The children of the component.
 * @param {Object}  props.actions  Actions to be passed to the children.
 * @return {Component} The component to be rendered.
 */
export default function MessageParser( { children, actions } ) {
	const parse = ( message ) => {
		actions.respond( message );
	};

	return (
		<div>
			{ Children.map( children, ( child ) => {
				return cloneElement( child, {
					parse,
					actions,
				} );
			} ) }
		</div>
	);
}
