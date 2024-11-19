/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import Markdown from 'markdown-to-jsx';

/**
 * WordPress dependencies
 */
import { useEffect, useRef } from '@wordpress/element';

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

const defaultRenderStreamResponse = ( text ) => {
	return text.replaceAll( '\n\n', '</p><p>' ).replaceAll( '\n', '<br>' );
};

/**
 * Renders a chatbot message.
 *
 * @since n.e.x.t
 *
 * @param {Object}  props                  Component props.
 * @param {Object}  props.content          The message content object.
 * @param {Object}  props.contentGenerator Content generator object, if this message is streaming.
 * @param {boolean} props.loading          Whether the message is loading.
 * @return {Component} The component to be rendered.
 */
export default function ChatbotMessage( {
	content,
	contentGenerator,
	loading,
} ) {
	const ResponseRenderer =
		useChatbotConfig( 'ResponseRenderer' ) || DefaultResponseRenderer;
	const renderStreamResponse =
		useChatbotConfig( 'renderStreamResponse' ) ||
		defaultRenderStreamResponse;

	const classSuffix = content.role === 'user' ? 'user' : 'assistant';
	const errorClass = content.type === 'error' ? ' ai-services-error' : '';

	const streamElementRef = useRef();

	useEffect( () => {
		if ( ! contentGenerator || ! streamElementRef.current ) {
			return;
		}

		/*
		 * Initialize a document fragment to write the stream content to.
		 * This is by far more performant than triggering React state changes for each chunk.
		 * Since the final response will come from the Datastore anyway, it is okay to ignore
		 * React paradigms here in favor of performance.
		 */
		const streamFragment = document.implementation.createHTMLDocument();
		streamFragment.write( '<div><p>' );
		streamElementRef.current.appendChild( streamFragment.body.firstChild );

		const readStream = async () => {
			for await ( const chunk of contentGenerator ) {
				if ( ! chunk.parts?.[ 0 ]?.text ) {
					continue;
				}
				streamFragment.write(
					renderStreamResponse( chunk.parts[ 0 ].text )
				);
			}
			streamFragment.write( '</p></div>' );
		};

		readStream();
	}, [ streamElementRef, contentGenerator, renderStreamResponse ] );

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
					{ !! contentGenerator && <div ref={ streamElementRef } /> }
					{ ! contentGenerator &&
						content.parts.map( ( part, index ) =>
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
	contentGenerator: PropTypes.object,
	loading: PropTypes.bool,
};
