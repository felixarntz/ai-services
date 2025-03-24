/**
 * External dependencies
 */
import { Parts } from '@ai-services/components';
import { store as interfaceStore } from '@ai-services/interface';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { Flex, Button } from '@wordpress/components';
import { useCallback, useEffect, useMemo, useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { code, upload } from '@wordpress/icons';

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

const findMediaPartsToUpload = ( parts, existingAttachments ) => {
	const mediaParts = [];
	for ( let partIndex = 0; partIndex < parts.length; partIndex++ ) {
		const part = parts[ partIndex ];
		if (
			part.inlineData &&
			part.inlineData.data &&
			! existingAttachments[ partIndex ]
		) {
			mediaParts.push( { partIndex, inlineData: part.inlineData } );
		}
	}
	return mediaParts;
};

/**
 * Renders a single message.
 *
 * @since 0.5.0
 *
 * @param {Object}   props                    The component properties.
 * @param {Object}   props.message            The message object.
 * @param {number}   props.index              The index of the message within the list of messages.
 * @param {Function} props.onUploadAttachment The callback to upload an attachment.
 * @param {Function} props.onViewMessageCode  The callback to view the message code.
 * @return {Component} The component to be rendered.
 */
function Message( { message, index, onUploadAttachment, onViewMessageCode } ) {
	const { type, content, ...additionalData } = message;

	const mediaPartsToUpload = useMemo( () => {
		if ( ! content.parts ) {
			return [];
		}
		return findMediaPartsToUpload(
			content.parts,
			additionalData.attachments || []
		);
	}, [ content.parts, additionalData.attachments ] );
	const allowUploadAttachment = mediaPartsToUpload.length > 0;
	const hasRawData = !! additionalData.rawData;

	const showActions = allowUploadAttachment || hasRawData;

	return (
		<div
			id={ `ai-services-playground-message-${ index }` }
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
				{ showActions && (
					<Flex
						className="ai-services-playground__message-toolbar"
						role="toolbar"
						aria-orientation="horizontal"
						aria-label={ __(
							'Additional message actions',
							'ai-services'
						) }
						justify="flex-end"
						gap={ 2 }
					>
						{ allowUploadAttachment && (
							<Button
								variant="primary"
								size="small"
								icon={ upload }
								iconSize={ 18 }
								onClick={ () => {
									for ( const {
										partIndex,
										inlineData,
									} of mediaPartsToUpload ) {
										onUploadAttachment(
											index,
											partIndex,
											inlineData
										);
									}
								} }
							>
								{ mediaPartsToUpload.length > 1
									? __( 'Save files', 'ai-services' )
									: __( 'Save file', 'ai-services' ) }
							</Button>
						) }
						{ hasRawData && (
							<Button
								variant="secondary"
								size="small"
								icon={ code }
								iconSize={ 18 }
								onClick={ () => {
									onViewMessageCode( message );
								} }
							>
								{ type === 'user'
									? __( 'View code', 'ai-services' )
									: __( 'View raw data', 'ai-services' ) }
							</Button>
						) }
					</Flex>
				) }
			</div>
		</div>
	);
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

	const { uploadAttachment, setActiveMessage } =
		useDispatch( playgroundStore );
	const { openModal } = useDispatch( interfaceStore );

	const messagesContainerRef = useRef();

	const scrollIntoView = () => {
		const interval = setInterval( () => {
			if ( messagesContainerRef.current ) {
				/*
				 * Subtract 5px to account for potential half pixel issues.
				 * These can cause the scroll to not reach the bottom, which can then trigger infinite scroll.
				 */
				if (
					messagesContainerRef.current.scrollTop +
						messagesContainerRef.current.clientHeight >=
					messagesContainerRef.current.scrollHeight - 5
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

	const onUploadAttachment = useCallback(
		async ( index, partIndex, inlineData ) => {
			await uploadAttachment( index, partIndex, inlineData );
		},
		[ uploadAttachment ]
	);

	const onViewMessageCode = useCallback(
		( message ) => {
			setActiveMessage( message );
			openModal( 'message-code' );
		},
		[ setActiveMessage, openModal ]
	);

	return (
		<div
			className="ai-services-playground__messages-container"
			ref={ messagesContainerRef }
		>
			<div className="ai-services-playground__messages" role="log">
				{ messages.map( ( message, index ) => (
					<Message
						key={ index }
						message={ message }
						index={ index }
						onUploadAttachment={ onUploadAttachment }
						onViewMessageCode={ onViewMessageCode }
					/>
				) ) }
			</div>
			<Loader />
		</div>
	);
}
