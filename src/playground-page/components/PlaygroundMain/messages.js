/**
 * External dependencies
 */
import Markdown from 'markdown-to-jsx';
import { store as interfaceStore } from '@ai-services/interface';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { Toolbar, ToolbarButton } from '@wordpress/components';
import { useEffect, useMemo, useRef } from '@wordpress/element';
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
 * Renders a single media element.
 *
 * @since 0.4.0
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
 * Renders a textarea with JSON formatted data.
 *
 * @since n.e.x.t
 *
 * @param {Object} props       The component props.
 * @param {Object} props.data  The data to display.
 * @param {string} props.label The textarea label.
 * @return {Component} The component to be rendered.
 */
function JsonTextarea( { data, label } ) {
	const dataJson = useMemo( () => {
		return JSON.stringify( data, null, 2 );
	}, [ data ] );

	return (
		<textarea
			className="code"
			aria-label={ label }
			value={ dataJson }
			rows="5"
			readOnly
		/>
	);
}

/**
 * Renders formatted content parts.
 *
 * @since 0.4.0
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

		if ( part.functionCall ) {
			return (
				<div className="ai-services-content-part" key={ index }>
					<JsonTextarea
						data={ part.functionCall }
						label={ __( 'Function call data', 'ai-services' ) }
					/>
				</div>
			);
		}

		if ( part.functionResponse ) {
			return (
				<div className="ai-services-content-part" key={ index }>
					<JsonTextarea
						data={ part.functionResponse }
						label={ __( 'Function response data', 'ai-services' ) }
					/>
				</div>
			);
		}

		return null;
	} );
}

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
