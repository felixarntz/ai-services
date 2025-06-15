/**
 * Internal dependencies
 */
import { useChatbotConfig } from '../../config';

type ChatbotHeaderProps = {
	/**
	 * Function to call when the close button is clicked.
	 */
	onClose: () => void;
};

/**
 * Renders the chatbot header.
 *
 * @since 0.3.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function ChatbotHeader( props: ChatbotHeaderProps ) {
	const { onClose } = props;

	const labels = useChatbotConfig( 'labels' );
	if ( ! labels ) {
		return null;
	}

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
