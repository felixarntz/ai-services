/**
 * External dependencies
 */
import { Parts } from '@ai-services/components';
import { store as interfaceStore } from '@ai-services/interface';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { Toolbar, ToolbarButton } from '@wordpress/components';
import { useEffect, useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { code } from '@wordpress/icons';

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
 * @since 0.4.0
 *
 * @return {Component} The component to be rendered.
 */
export default function Messages() {
	const messages = useSelect( ( select ) =>
		select( playgroundStore ).getMessages()
	);

	const { setActiveRawData } = useDispatch( playgroundStore );
	const { openModal } = useDispatch( interfaceStore );

	const messagesContainerRef = useRef();

	const scrollIntoView = () => {
		const interval = setInterval( () => {
			if ( messagesContainerRef.current ) {
				if (
					messagesContainerRef.current.scrollTop +
						messagesContainerRef.current.clientHeight >=
					messagesContainerRef.current.scrollHeight
				) {
					clearInterval( interval );
					return;
				}
				messagesContainerRef.current.scrollTop =
					messagesContainerRef.current.scrollHeight;
			}
		}, 100 );
		return interval;
	};

	// Scroll to the latest message when the component mounts.
	useEffect( () => {
		const interval = scrollIntoView();

		return () => clearInterval( interval );
	}, [ messages ] );

	return (
		<div
			className="ai-services-playground__messages-container"
			ref={ messagesContainerRef }
		>
			<div className="ai-services-playground__messages" role="log">
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
								{ additionalData.rawData && (
									<Toolbar
										className="ai-services-playground__message-toolbar"
										label={ __(
											'Additional message actions',
											'ai-services'
										) }
									>
										<ToolbarButton
											size="small"
											icon={ code }
											iconSize={ 18 }
											onClick={ () => {
												setActiveRawData(
													additionalData.rawData
												);
												openModal( 'raw-message-data' );
											} }
										>
											{ __(
												'View raw data',
												'ai-services'
											) }
										</ToolbarButton>
									</Toolbar>
								) }
							</div>
						</div>
					)
				) }
			</div>
			<Loader />
		</div>
	);
}
