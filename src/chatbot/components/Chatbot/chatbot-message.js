/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import Markdown from 'markdown-to-jsx';

/**
 * Internal dependencies
 */
import { useChatbotConfig } from '../../config';
import UserIcon from './user-icon';

const DefaultResponseRenderer = ( { text } ) => (
	<Markdown options={ { forceBlock: true, forceWrapper: true } }>
		{ text }
	</Markdown>
);

/**
 * Renders a chatbot message.
 *
 * @since n.e.x.t
 *
 * @param {Object} props         Component props.
 * @param {Object} props.content The message content object.
 * @return {Component} The component to be rendered.
 */
export default function ChatbotMessage( { content } ) {
	const ResponseRenderer =
		useChatbotConfig( 'ResponseRenderer' ) || DefaultResponseRenderer;
	const classSuffix = content.role === 'user' ? 'user' : 'assistant';

	return (
		<div
			className={ `ai-services-chatbot__message-container ai-services-chatbot__message-container--${ classSuffix }` }
		>
			<div
				className={ `ai-services-chatbot__avatar ai-services-chatbot__avatar--${ classSuffix }` }
			>
				<div
					className={ `ai-services-chatbot__avatar-container ai-services-chatbot__avatar-container--${ classSuffix }` }
				>
					{ classSuffix === 'assistant' && (
						<p
							className={ `ai-services-chatbot__avatar-letter ai-services-chatbot__avatar-letter--${ classSuffix }` }
						>
							B
						</p>
					) }
					{ classSuffix !== 'assistant' && (
						<UserIcon
							className={ `ai-services-chatbot__avatar-icon ai-services-chatbot__avatar-icon--${ classSuffix }` }
						/>
					) }
				</div>
			</div>
			<div
				className={ `ai-services-chatbot__message ai-services-chatbot__message--${ classSuffix }` }
			>
				{ content.parts.map( ( part, index ) =>
					!! part.text ? (
						<ResponseRenderer key={ index } text={ part.text } />
					) : null
				) }
				<div
					className={ `ai-services-chatbot__message-arrow ai-services-chatbot__message-arrow--${ classSuffix }` }
				></div>
			</div>
		</div>
	);
}

ChatbotMessage.propTypes = {
	content: PropTypes.shape( {
		role: PropTypes.string,
		parts: PropTypes.arrayOf( PropTypes.object ),
	} ).isRequired,
};
