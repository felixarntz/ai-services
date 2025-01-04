/**
 * External dependencies
 */
import Markdown from 'markdown-to-jsx';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useEffect, useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';
import Loader from './loader';

const getModelAuthor = ( additionalData ) => {
	if ( additionalData.service?.name && additionalData.model?.name ) {
		return sprintf(
			/* translators: %1$s: service name, %2$s: model name */
			__( '%1$s: %2$s', 'ai-services' ),
			additionalData.service.name,
			additionalData.model.name
		);
	}

	if ( additionalData.service?.name ) {
		return additionalData.service.name;
	}

	return __( 'AI Model', 'ai-services' );
};

/**
 * Renders a single media element.
 *
 * @since n.e.x.t
 *
 * @param {Object} props          The component props.
 * @param {string} props.mimeType The media MIME type.
 * @param {string} props.src      The media source.
 * @return {Component} The component to be rendered.
 */
function Media( { mimeType, src } ) {
	if ( mimeType.startsWith( 'image' ) ) {
		return <img src={ src } alt="" />;
	}

	if ( mimeType.startsWith( 'audio' ) ) {
		return <audio src={ src } controls />;
	}

	if ( mimeType.startsWith( 'video' ) ) {
		return <video src={ src } controls />;
	}

	return null;
}

/**
 * Renders formatted content parts.
 *
 * @since n.e.x.t
 *
 * @param {Object}   props       Component props.
 * @param {Object[]} props.parts The parts to render.
 * @return {Component} The component to be rendered.
 */
function Parts( { parts } ) {
	return parts.map( ( part, index ) => {
		if ( part.text ) {
			return (
				<div className="ai-services-content-part" key={ index }>
					<Markdown
						options={ {
							forceBlock: true,
							forceWrapper: true,
						} }
					>
						{ part.text }
					</Markdown>
				</div>
			);
		}

		if ( part.inlineData ) {
			const { mimeType, data } = part.inlineData;
			const base64 = /^data:[a-z]+\/[a-z]+;base64,/.test( data )
				? data
				: `data:${ mimeType };base64,${ data }`;
			return (
				<div className="ai-services-content-part" key={ index }>
					<Media mimeType={ mimeType } src={ base64 } />
				</div>
			);
		}

		if ( part.fileData ) {
			const { mimeType, fileUri } = part.fileData;
			return (
				<div className="ai-services-content-part" key={ index }>
					<Media mimeType={ mimeType } src={ fileUri } />
				</div>
			);
		}

		return null;
	} );
}

/**
 * Renders the messages UI.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function Messages() {
	const messages = useSelect( ( select ) =>
		select( playgroundStore ).getMessages()
	);

	const messagesContainerRef = useRef();

	const scrollIntoView = () =>
		setTimeout( () => {
			if ( messagesContainerRef.current ) {
				messagesContainerRef.current.scrollTop =
					messagesContainerRef?.current?.scrollHeight;
			}
		}, 50 );

	// Scroll to the latest message when the component mounts.
	useEffect( () => {
		const timeout = scrollIntoView();

		return () => clearTimeout( timeout );
	}, [ messages ] );

	return (
		<div
			className="ai-services-playground__messages-container"
			ref={ messagesContainerRef }
		>
			<div className="ai-services-playground__messages">
				{ messages.map(
					( { type, content, ...additionalData }, index ) => (
						<div
							key={ index }
							className={ `ai-services-playground__message-container ai-services-playground__message-container--${ type }` }
						>
							<div
								className={ `ai-services-playground__message ai-services-playground__message--${ type }` }
							>
								<div className="ai-services-playground__message-author">
									{ type === 'user'
										? __( 'You', 'ai-services' )
										: getModelAuthor( additionalData ) }
								</div>
								<div className="ai-services-playground__message-content">
									<Parts parts={ content.parts } />
								</div>
							</div>
						</div>
					)
				) }
			</div>
			<Loader />
		</div>
	);
}
