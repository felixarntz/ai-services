/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Renders the chatbot header.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function ChatbotHeader() {
	// TODO: Implement functionality to close the chatbot UI.
	return (
		<div className="react-chatbot-kit-chat-header">
			<div className="chatbot-header-title">
				{ __( 'WordPress Assistant', 'ai-services' ) }
			</div>
			<button
				className="chatbot-header-close-button"
				aria-label={ __( 'Close chatbot', 'ai-services' ) }
			>
				<span className="chatbot-header-close-button__icon" />
				<span className="screen-reader-text">
					{ __( 'Close chatbot', 'ai-services' ) }
				</span>
			</button>
		</div>
	);
}
