/**
 * External dependencies
 */
import { store as aiStore } from '@ai-services/ai-store';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	useChatIdContext,
	useChatbotToggleVisibilityContext,
} from '../../context';
import './style.scss';

/**
 * Renders the chatbot header.
 *
 * @since 0.1.0
 *
 * @return {Component} The component to be rendered.
 */
export default function ChatbotHeader() {
	const chatId = useChatIdContext();
	const toggleVisibility = useChatbotToggleVisibilityContext();

	const serviceName = useSelect( ( select ) => {
		const chatConfig = select( aiStore ).getChatConfig( chatId );
		if ( ! chatConfig.service ) {
			return undefined;
		}

		const services = select( aiStore ).getServices();
		return services?.[ chatConfig.service ]?.name;
	} );

	// TODO: Implement functionality to close the chatbot UI.
	return (
		<div className="react-chatbot-kit-chat-header">
			<div className="chatbot-header-title">
				{ __( 'WordPress Assistant', 'ai-services' ) }
				{ serviceName && (
					<div className="chatbot-header-title__note">
						{ sprintf(
							/* translators: %s: service name */
							__( 'Powered by %s', 'ai-services' ),
							serviceName
						) }
					</div>
				) }
			</div>
			<button
				className="chatbot-header-close-button"
				aria-label={ __( 'Close chatbot', 'ai-services' ) }
				onClick={ toggleVisibility }
			>
				<span className="chatbot-header-close-button__icon" />
				<span className="screen-reader-text">
					{ __( 'Close chatbot', 'ai-services' ) }
				</span>
			</button>
		</div>
	);
}
