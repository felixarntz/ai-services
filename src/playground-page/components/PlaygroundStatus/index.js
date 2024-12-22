/**
 * External dependencies
 */
import { store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';

/**
 * Renders the playground status text in a paragraph.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function PlaygroundStatus() {
	const { service, model, messages, loading, serviceName } = useSelect(
		( select ) => {
			const { getService, getModel, getMessages, isLoading } =
				select( playgroundStore );
			const { getServiceName } = select( aiStore );

			const currentService = getService();

			return {
				service: currentService,
				model: getModel(),
				messages: getMessages(),
				loading: isLoading(),
				serviceName: currentService
					? getServiceName( currentService )
					: '',
			};
		}
	);

	const [ messageCount, setMessageCount ] = useState( false );
	const [ messageStatus, setMessageStatus ] = useState( '' );

	useEffect( () => {
		if ( messages.length === messageCount ) {
			return;
		}

		if ( messages.length === messageCount + 1 ) {
			if ( messages[ messages.length - 1 ].type === 'error' ) {
				setMessageStatus( 'prompt_error' );
			} else {
				setMessageStatus( 'prompt_success' );
			}
		} else if ( messages.length === 0 && messageCount > 0 ) {
			setMessageStatus( 'reset' );
		}
		setMessageCount( messages.length );
	}, [ messages, messageCount ] );

	useEffect( () => {
		if ( ! messageStatus ) {
			return;
		}

		const timeout = setTimeout( () => {
			setMessageStatus( '' );
		}, 5000 );

		return () => clearTimeout( timeout );
	}, [ messageStatus ] );

	if ( ! service ) {
		return <p>{ __( 'Please select an AI service.', 'ai-services' ) }</p>;
	}

	if ( ! model ) {
		return <p>{ __( 'Please select an AI model.', 'ai-services' ) }</p>;
	}

	if ( loading ) {
		return (
			<p>
				{ sprintf(
					/* translators: 1: service name, 2: model name */
					__( 'Sending prompt to %1$s model "%2$s"…', 'ai-services' ),
					serviceName,
					model
				) }
			</p>
		);
	}

	if ( messageStatus === 'prompt_success' ) {
		return (
			<p>
				{ sprintf(
					/* translators: 1: service name, 2: model name */
					__(
						'Received response from %1$s model "%2$s".',
						'ai-services'
					),
					serviceName,
					model
				) }
			</p>
		);
	}

	if ( messageStatus === 'prompt_error' ) {
		return (
			<p>
				{ sprintf(
					/* translators: 1: service name, 2: model name */
					__(
						'Received error from %1$s model "%2$s".',
						'ai-services'
					),
					serviceName,
					model
				) }
			</p>
		);
	}

	if ( messageStatus === 'reset' ) {
		return <p>{ __( 'Messages were reset.', 'ai-services' ) }</p>;
	}

	return (
		<p>
			{ sprintf(
				/* translators: 1: service name, 2: model name */
				__( 'Ready to use %1$s with model "%2$s".', 'ai-services' ),
				serviceName,
				model
			) }
		</p>
	);
}