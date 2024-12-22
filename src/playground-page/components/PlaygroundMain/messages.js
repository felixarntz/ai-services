/**
 * External dependencies
 */
import Markdown from 'markdown-to-jsx';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
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

	return (
		<div className="ai-services-playground__messages-container">
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
									{ content.parts.map(
										( { text }, partIndex ) =>
											!! text && (
												<Markdown
													key={ partIndex }
													options={ {
														forceBlock: true,
														forceWrapper: true,
													} }
												>
													{ text }
												</Markdown>
											)
									) }
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
