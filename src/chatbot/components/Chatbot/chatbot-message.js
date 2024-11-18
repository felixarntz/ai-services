/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import Markdown from 'markdown-to-jsx';

/**
 * Internal dependencies
 */
import { useChatbotConfig } from '../../config';
import Loader from './loader';
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
 * @param {Object}  props         Component props.
 * @param {Object}  props.content The message content object.
 * @param {boolean} props.loading Whether the message is loading.
 * @return {Component} The component to be rendered.
 */
export default function ChatbotMessage( { content, loading } ) {
	const ResponseRenderer =
		useChatbotConfig( 'ResponseRenderer' ) || DefaultResponseRenderer;
	const classSuffix = content.role === 'user' ? 'user' : 'assistant';
	const errorClass = content.type === 'error' ? ' ai-services-error' : '';

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
			{ loading && <Loader /> }
			{ ! loading && (
				<div
					className={ `ai-services-chatbot__message ai-services-chatbot__message--${ classSuffix }${ errorClass }` }
				>
					{ content.parts.map( ( part, index ) =>
						!! part.text ? (
							<ResponseRenderer
								key={ index }
								text={ part.text }
							/>
						) : null
					) }
					<div
						className={ `ai-services-chatbot__message-arrow ai-services-chatbot__message-arrow--${ classSuffix }${ errorClass }` }
					></div>
				</div>
			) }
		</div>
	);
}

ChatbotMessage.propTypes = {
	content: PropTypes.shape( {
		role: PropTypes.string,
		parts: PropTypes.arrayOf( PropTypes.object ),
	} ).isRequired,
	loading: PropTypes.bool,
};