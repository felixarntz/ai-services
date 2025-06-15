/**
 * External dependencies
 */
import Markdown from 'markdown-to-jsx';
import type { Part } from '@ai-services/ai/types';

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
import type {
	ChatbotMessage as ChatbotMessageType,
	ResponseRendererProps,
	AsyncContentGenerator,
} from '../../types';

const DefaultResponseRenderer = ( props: ResponseRendererProps ) => {
	const { text } = props;
	return (
		<Markdown options={ { forceBlock: true, forceWrapper: true } }>
			{ text }
		</Markdown>
	);
};

const defaultRenderStreamResponse = ( text: string ): string => {
	return text.replaceAll( '\n\n', '</p><p>' ).replaceAll( '\n', '<br>' );
};

type ChatbotMessageProps = {
	/**
	 * The message content object.
	 */
	content: ChatbotMessageType;
	/**
	 * Content generator object, if this message is streaming.
	 */
	contentGenerator?: AsyncContentGenerator;
	/**
	 * Whether the message is loading.
	 */
	loading?: boolean;
};

/**
 * Renders a chatbot message.
 *
 * @since 0.3.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function ChatbotMessage( props: ChatbotMessageProps ) {
	const { content, contentGenerator, loading } = props;

	const ResponseRenderer =
		useChatbotConfig( 'ResponseRenderer' ) || DefaultResponseRenderer;
	const renderStreamResponse =
		useChatbotConfig( 'renderStreamResponse' ) ||
		defaultRenderStreamResponse;

	const classSuffix = content.role === 'user' ? 'user' : 'assistant';
	const errorClass = content.type === 'error' ? ' ai-services-error' : '';

	const streamElementRef = useRef< HTMLDivElement | null >( null );

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
		streamElementRef.current.appendChild(
			streamFragment.body.firstChild as Node
		);

		const readStream = async () => {
			for await ( const chunk of contentGenerator ) {
				if (
					! chunk.parts.length ||
					! ( 'text' in chunk.parts[ 0 ] )
				) {
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
						content.parts.map( ( part: Part, index ) =>
							'text' in part && !! part.text ? (
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
