/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { useChatbotConfig } from '../../config';

/**
 * Renders the chatbot header.
 *
 * @since n.e.x.t
 *
 * @param {Object}   props         Component props.
 * @param {Function} props.onClose Function to call when the close button is clicked.
 * @return {Component} The component to be rendered.
 */
export default function ChatbotHeader( { onClose } ) {
	const labels = useChatbotConfig( 'labels' );

	// TODO: Implement functionality to close the chatbot UI.
	return (
		<div className="ai-services-chatbot__header">
			<div className="ai-services-chatbot__header-title">
				{ labels.title }
				{ !! labels.subtitle && (
					<div className="ai-services-chatbot__header-title__note">
						{ labels.subtitle }
					</div>
				) }
			</div>
			<button
				className="ai-services-chatbot__header-close-button"
				aria-label={ labels.closeButton }
				onClick={ onClose }
			>
				<span className="ai-services-chatbot__header-close-button__icon" />
				<span className="screen-reader-text">
					{ labels.closeButton }
				</span>
			</button>
		</div>
	);
}

ChatbotHeader.propTypes = {
	onClose: PropTypes.func.isRequired,
};
